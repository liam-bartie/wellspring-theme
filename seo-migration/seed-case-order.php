<?php
/**
 * Give every clinic case a starting Order number.
 *
 * The archive now sorts by menu_order then title, so Amber can rearrange the
 * cases by hand. Every case currently has menu_order 0, and with the whole set
 * tied the tiebreak takes over — which would silently re-sort /clinic-cases
 * from newest-first into A-Z the moment the new template goes live.
 *
 * This numbers the cases 10, 20, 30 ... in their existing newest-first order,
 * so the page looks identical after the change and the numbers are already
 * spaced for inserting one between two others.
 *
 * Usage, from the WordPress root:
 *   wp eval-file ~/seed-case-order.php            # dry run, writes nothing
 *   APPLY=1 wp eval-file ~/seed-case-order.php    # write
 *
 * Safe to re-run, but it re-numbers from date order every time, so run it once
 * and then leave it alone — after Amber has set her own order, re-running would
 * throw that away. It refuses to touch anything if any case already has a
 * non-zero Order, which is the guard for exactly that.
 */

$apply = (bool) getenv( 'APPLY' );
$force = (bool) getenv( 'FORCE' );

$cases = get_posts(
	array(
		'post_type'      => 'clinic_case',
		'post_status'    => array( 'publish', 'draft', 'private', 'pending', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if ( ! $cases ) {
	WP_CLI::error( 'No clinic cases found.' );
}

WP_CLI::line( $apply ? "MODE: APPLY\n" : "MODE: DRY RUN (nothing will be written)\n" );

// Guard: never overwrite an order someone has already set by hand.
$already = array();
foreach ( $cases as $case ) {
	if ( (int) $case->menu_order !== 0 ) {
		$already[] = sprintf( '%s (%d)', $case->post_name, (int) $case->menu_order );
	}
}

if ( $already && ! $force ) {
	WP_CLI::line( 'These cases already have an Order set:' );
	foreach ( $already as $line ) {
		WP_CLI::line( '  ' . $line );
	}
	WP_CLI::error(
		count( $already ) . ' case(s) already ordered by hand. Re-numbering would discard that. '
		. 'Run with FORCE=1 only if you intend to reset every case to date order.'
	);
}

$order   = 0;
$written = 0;
$failed  = 0;

foreach ( $cases as $case ) {
	$order += 10;

	WP_CLI::line(
		sprintf(
			'  %s %-4d %-44s %s',
			$apply ? 'WRITE ' : 'would ',
			$order,
			$case->post_name,
			get_the_date( 'Y-m-d', $case )
		)
	);

	if ( ! $apply ) {
		continue;
	}

	wp_update_post(
		array(
			'ID'         => $case->ID,
			'menu_order' => $order,
		)
	);

	// Read back rather than trusting a return value.
	clean_post_cache( $case->ID );
	$check = (int) get_post_field( 'menu_order', $case->ID );

	if ( $check !== $order ) {
		WP_CLI::warning( sprintf( '    %s: wrote %d, reads %d', $case->post_name, $order, $check ) );
		$failed++;
		continue;
	}

	$written++;
}

WP_CLI::line( sprintf( "\n%d case(s) %s, %d problem(s)", $apply ? $written : count( $cases ), $apply ? 'numbered' : 'would be numbered', $failed ) );

if ( $apply ) {
	// Prove the archive's own query now returns the same sequence.
	$check = get_posts(
		array(
			'post_type'      => 'clinic_case',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending', 'future' ),
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);

	$expected = wp_list_pluck( $cases, 'post_name' );
	$got      = wp_list_pluck( $check, 'post_name' );

	if ( $expected === $got ) {
		WP_CLI::success( 'The archive query now returns the cases in exactly the previous order.' );
	} else {
		WP_CLI::warning( 'The archive query order does NOT match the previous order:' );
		foreach ( $expected as $i => $slug ) {
			$other = $got[ $i ] ?? '(missing)';
			if ( $slug !== $other ) {
				WP_CLI::line( sprintf( '  position %d: was %s, now %s', $i + 1, $slug, $other ) );
			}
		}
	}
} else {
	WP_CLI::line( 'Re-run with APPLY=1 to write.' );
}
