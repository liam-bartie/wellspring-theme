<?php
/**
 * Move each page's hardcoded "Related clinic cases" block into a draggable
 * "Clinic cases" section row.
 *
 * Why a script rather than doing it by hand: every page carries its own filter
 * settings (taxonomy, term, heading, limit, order). Re-entering those across
 * nine pages invites a silent error where a page shows the wrong cases. This
 * copies the stored values rather than retyping them.
 *
 * Non-destructive in the sense that matters: the per-page ws_cases_* fields are
 * left exactly as they are. Only two things change — a section row is appended,
 * and ws_show_cases is switched off so the grid is not rendered twice.
 *
 * Usage, from the WordPress root:
 *   wp eval-file ~/migrate-cases-sections.php            # dry run, writes nothing
 *   APPLY=1 wp eval-file ~/migrate-cases-sections.php    # write
 *
 * Safe to re-run: a page that already has a 'cases' row is skipped.
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
$skipped = 0;
$already = 0;

foreach ( $pages as $page ) {
	$show = get_field( 'ws_show_cases', $page->ID );

	if ( empty( $show ) ) {
		continue;
	}

	$rows = get_field( 'page_sections', $page->ID );
	$rows = is_array( $rows ) ? $rows : array();

	// Idempotency: never append a second cases row.
	foreach ( $rows as $row ) {
		if ( 'cases' === ( $row['acf_fc_layout'] ?? '' ) ) {
			WP_CLI::line( sprintf( '  skip   %-34s already has a Clinic cases section', $page->post_name ) );
			$already++;
			continue 2;
		}
	}

	$taxonomy = (string) get_field( 'ws_cases_taxonomy', $page->ID );
	$heading  = (string) get_field( 'ws_cases_heading', $page->ID );
	$limit    = get_field( 'ws_cases_limit', $page->ID );
	$orderby  = (string) get_field( 'ws_cases_orderby', $page->ID );

	$focus    = (string) get_field( 'ws_cases_focus', $page->ID );
	$symptom  = (string) get_field( 'ws_cases_symptom', $page->ID );
	$modality = (string) get_field( 'ws_cases_modality', $page->ID );

	$new_row = array(
		'acf_fc_layout' => 'cases',
		// 'mist' reproduces the light-green band the hardcoded block rendered.
		'background'    => 'mist',
		// Positional anchors only apply to the home page; inner pages render
		// sections in list order and ignore this.
		'position'      => '',
		'heading'       => $heading,
		'taxonomy'      => $taxonomy,
		'focus'         => $focus,
		'symptom'       => $symptom,
		'modality'      => $modality,
		'limit'         => $limit,
		'orderby'       => $orderby,
	);

	// Appended, because the hardcoded block always rendered after the sections.
	// Same visual order, now draggable.
	$rows[] = $new_row;

	$term = $focus;
	if ( 'case_symptom' === $taxonomy ) {
		$term = $symptom;
	} elseif ( 'case_modality' === $taxonomy ) {
		$term = $modality;
	}

	WP_CLI::line(
		sprintf(
			'  %s %-34s rows %d->%d  tax=%-14s term=%-22s limit=%-3s order=%s',
			$apply ? 'WRITE ' : 'would ',
			$page->post_name,
			count( $rows ) - 1,
			count( $rows ),
			$taxonomy ? $taxonomy : '(focus)',
			$term ? $term : '(auto: page slug)',
			'' === $limit || null === $limit ? '-' : $limit,
			$orderby ? $orderby : '(default)'
		)
	);

	if ( ! $apply ) {
		$acted++;
		continue;
	}

	update_field( 'page_sections', $rows, $page->ID );
	update_field( 'ws_show_cases', 0, $page->ID );

	// Do not trust update_field()'s return value: it proxies update_post_meta(),
	// which reports false when a value is unchanged. Read back instead.
	wp_cache_delete( $page->ID, 'post_meta' );
	$check = get_field( 'page_sections', $page->ID );
	$check = is_array( $check ) ? $check : array();

	$found = false;
	foreach ( $check as $row ) {
		if ( 'cases' === ( $row['acf_fc_layout'] ?? '' ) ) {
			$found = true;
			$mismatch = array();
			foreach ( array( 'heading', 'taxonomy', 'focus', 'symptom', 'modality', 'orderby' ) as $k ) {
				$got = $row[ $k ] ?? '';
				// A term sub-field may read back hydrated; compare loosely on
				// scalars only, and report anything that is not a plain match.
				if ( is_scalar( $got ) && (string) $got !== (string) $new_row[ $k ] ) {
					$mismatch[] = sprintf( '%s: wrote "%s", read "%s"', $k, $new_row[ $k ], $got );
				}
			}
			if ( $mismatch ) {
				WP_CLI::warning( '    ' . implode( ' | ', $mismatch ) );
			} else {
				WP_CLI::line( '    verified: section row reads back as written' );
			}
			break;
		}
	}

	if ( ! $found ) {
		WP_CLI::warning( sprintf( '    FAILED: no cases row found on %s after writing', $page->post_name ) );
		$skipped++;
		continue;
	}

	if ( get_field( 'ws_show_cases', $page->ID ) ) {
		WP_CLI::warning( '    the old toggle is still on — the grid would render twice' );
	}

	$acted++;
}

WP_CLI::line(
	sprintf(
		"\n%d page(s) %s, %d already migrated, %d problem(s)",
		$acted,
		$apply ? 'written' : 'would be written',
		$already,
		$skipped
	)
);

if ( ! $apply ) {
	WP_CLI::line( 'Re-run with APPLY=1 to write.' );
}
