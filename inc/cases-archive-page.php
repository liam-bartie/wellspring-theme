<?php
/**
 * An editable page behind the Clinic Cases archive.
 *
 * /clinic-cases is a post type archive, not a page, so there is no post to
 * open and the admin bar offers "Edit Home Page" instead. Its hero used to be
 * editable only through Customizer theme mods, which nobody finds.
 *
 * This designates a real page as the archive's content source. The Hero panel
 * and the Page content section builder already target pages, so they appear on
 * it with no extra registration, and the archive renders from it.
 *
 * The page is kept as a DRAFT on purpose:
 *   - visitors cannot reach it, so there is no second URL competing with
 *     /clinic-cases for the same content
 *   - it stays out of menus and page-select dropdowns
 *   - ACF fields read the same either way, so nothing is lost by it
 *
 * @package Wellspring
 */

const WELLSPRING_CASES_PAGE_OPTION = 'wellspring_cases_page_id';
const WELLSPRING_CASES_PAGE_SLUG   = 'clinic-cases-content';

/**
 * The designated page's ID, or 0.
 *
 * @param bool $create Provision the page when it does not exist yet.
 * @return int
 */
function wellspring_cases_page_id( $create = false ) {
	$id = (int) get_option( WELLSPRING_CASES_PAGE_OPTION, 0 );

	if ( $id && get_post( $id ) ) {
		return $id;
	}

	// An earlier install may have the page without the option recorded.
	$existing = get_page_by_path( WELLSPRING_CASES_PAGE_SLUG, OBJECT, 'page' );
	if ( $existing ) {
		update_option( WELLSPRING_CASES_PAGE_OPTION, (int) $existing->ID );
		return (int) $existing->ID;
	}

	if ( ! $create ) {
		return 0;
	}

	$id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_title'  => 'Clinic cases (page content)',
			'post_name'   => WELLSPRING_CASES_PAGE_SLUG,
			'post_status' => 'draft',
			'post_content' => '',
		)
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	update_option( WELLSPRING_CASES_PAGE_OPTION, (int) $id );

	/*
	 * Carry the existing Customizer values across, so the archive looks
	 * exactly as it did before this page existed. Reading them here rather
	 * than leaving the fields blank is the difference between a silent
	 * migration and the hero emptying out.
	 */
	if ( function_exists( 'update_field' ) ) {
		$heading = get_theme_mod( 'clinic_cases_title', '' );
		$lede    = get_theme_mod( 'clinic_cases_lede', '' );

		if ( $heading ) {
			update_field( 'page_h1', $heading, $id );
		}
		if ( $lede ) {
			update_field( 'page_subheading', $lede, $id );
		}
	}

	return (int) $id;
}

/**
 * Provision the page on an admin load, once.
 */
add_action(
	'admin_init',
	function () {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		wellspring_cases_page_id( true );
	}
);

/**
 * Point the Clinic Cases admin screens at that page, so it is findable rather
 * than something you have to be told about.
 */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-clinic_case' !== $screen->id ) {
			return;
		}

		$id = wellspring_cases_page_id();
		if ( ! $id ) {
			return;
		}

		printf(
			'<div class="notice notice-info"><p>%s <a href="%s">%s</a>. %s</p></div>',
			esc_html__( 'The heading, intro and content on the Clinic Cases listing are edited here:', 'wellspring' ),
			esc_url( get_edit_post_link( $id ) ),
			esc_html__( 'Clinic cases (page content)', 'wellspring' ),
			esc_html__( 'The order the cases appear in comes from the Order number on each one — lowest first. Use Quick Edit to change it.', 'wellspring' )
		);
	}
);

/**
 * Run a callback with the designated page as the current post.
 *
 * The hero part and the section builder both read the current post, and an
 * archive has none. Restores the previous global afterwards rather than calling
 * wp_reset_postdata(), which would re-point $post at the first case in the
 * main query instead of at whatever was there before.
 *
 * @param callable $render Receives the page ID.
 * @return void
 */
function wellspring_with_cases_page( $render ) {
	$id = wellspring_cases_page_id();
	if ( ! $id ) {
		return;
	}

	$page = get_post( $id );
	if ( ! $page ) {
		return;
	}

	global $post;
	$previous = $post;

	$post = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored below.
	setup_postdata( $post );

	$render( $id );

	$post = $previous; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restoring.
	if ( $previous instanceof WP_Post ) {
		setup_postdata( $previous );
	}
}
