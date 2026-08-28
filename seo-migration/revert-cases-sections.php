<?php
/**
 * Undo migrate-cases-sections.php.
 *
 * Removes the appended "Clinic cases" section row and switches the per-page
 * "Show related clinic cases" toggle back on, restoring the hardcoded block.
 *
 * This is a clean reversal because the migration never touched the ws_cases_*
 * settings — taxonomy, term, heading, limit and order are all still on the page
 * exactly as they were, so turning the toggle back on reproduces the original
 * rendering precisely.
 *
 * Only removes rows whose layout is 'cases'. Any Text, Heading or other section
 * an editor has added is left alone.
 *
 * Usage, from the WordPress root:
 *   wp eval-file ~/revert-cases-sections.php            # dry run
 *   APPLY=1 wp eval-file ~/revert-cases-sections.php    # write
 */

$apply = (bool) getenv( 'APPLY' );

if ( ! function_exists( 'get_field' ) || ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'ACF is not active.' );
}

$pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	)
);

WP_CLI::line( $apply ? "MODE: APPLY\n" : "MODE: DRY RUN (nothing will be written)\n" );

$acted = 0;

foreach ( $pages as $page ) {
	$rows = get_field( 'page_sections', $page->ID );
	$rows = is_array( $rows ) ? $rows : array();

	$kept = array();
	$removed = 0;

	foreach ( $rows as $row ) {
		if ( 'cases' === ( $row['acf_fc_layout'] ?? '' ) ) {
			$removed++;
			continue;
		}
		$kept[] = $row;
	}

	if ( ! $removed ) {
		continue;
	}

	WP_CLI::line(
		sprintf(
			'  %s %-34s rows %d->%d  (removed %d cases section), toggle -> ON',
			$apply ? 'WRITE ' : 'would ',
			$page->post_name,
			count( $rows ),
			count( $kept ),
			$removed
		)
	);

	if ( ! $apply ) {
		$acted++;
		continue;
	}

	update_field( 'page_sections', $kept, $page->ID );
	update_field( 'ws_show_cases', 1, $page->ID );

	// Verify by reading back, not by trusting update_field()'s return value.
	wp_cache_delete( $page->ID, 'post_meta' );
	$check = get_field( 'page_sections', $page->ID );
	$check = is_array( $check ) ? $check : array();

	$still = 0;
	foreach ( $check as $row ) {
		if ( 'cases' === ( $row['acf_fc_layout'] ?? '' ) ) {
			$still++;
		}
	}

	if ( $still ) {
		WP_CLI::warning( sprintf( '    %d cases row(s) still present on %s', $still, $page->post_name ) );
	} elseif ( ! get_field( 'ws_show_cases', $page->ID ) ) {
		WP_CLI::warning( '    the toggle did not switch back on — the grid would not render' );
	} else {
		WP_CLI::line( '    verified: section removed, toggle on' );
	}

	$acted++;
}

WP_CLI::line(
	sprintf(
		"\n%d page(s) %s",
		$acted,
		$apply ? 'reverted' : 'would be reverted'
	)
);

if ( ! $apply ) {
	WP_CLI::line( 'Re-run with APPLY=1 to write.' );
}
