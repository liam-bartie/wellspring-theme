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

const WELLSPRING_CASES_PAGE_OPTION  = 'wellspring_cases_page_id';
const WELLSPRING_CASES_PAGE_SLUG    = 'clinic-cases-content';
const WELLSPRING_CASES_PAGE_TITLE   = 'Clinic cases';
const WELLSPRING_CASES_SEED_VERSION = '2';

/**
 * What the archive displayed before this page existed.
 *
 * These are the same fallbacks archive-clinic_case.php passes to
 * get_theme_mod(), and that matters: the Customizer settings were never
 * actually saved on this site, so reading the theme mods with an empty default
 * returns nothing and the hero seeds blank. Version 1 of this file did exactly
 * that, which is how the internal page title ended up rendering as the H1.
 *
 * @return array field name => value
 */
function wellspring_cases_page_defaults() {
	return array(
		'page_h1'         => (string) get_theme_mod( 'clinic_cases_title', WELLSPRING_CASES_PAGE_TITLE ),
		'page_subheading' => (string) get_theme_mod( 'clinic_cases_lede', "A curated record of patients we've worked with. Names are shortened to initials for privacy. Search by symptom, or filter by focus area and treatment." ),
	);
}

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
			'post_type'    => 'page',
			'post_title'   => WELLSPRING_CASES_PAGE_TITLE,
			'post_name'    => WELLSPRING_CASES_PAGE_SLUG,
			'post_status'  => 'draft',
			'post_content' => '',
		)
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	update_option( WELLSPRING_CASES_PAGE_OPTION, (int) $id );

	return (int) $id;
}

/**
 * Fill the hero fields with the values the archive was already showing.
 *
 * Writes only where a field is empty, so it can never overwrite an edit, and
 * the caller's version flag means a deliberately cleared field stays cleared.
 *
 * @param int $id Page ID.
 * @return int Fields written.
 */
function wellspring_seed_cases_page( $id ) {
	if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
		return 0;
	}

	$written = 0;

	foreach ( wellspring_cases_page_defaults() as $name => $value ) {
		if ( '' === trim( $value ) ) {
			continue;
		}
		if ( '' !== trim( (string) get_field( $name, $id ) ) ) {
			continue;
		}
		update_field( $name, $value, $id );
		$written++;
	}

	/*
	 * The Customizer stored a hero image as a URL; the ACF field needs an
	 * attachment ID. Only carried across when the URL resolves to something in
	 * the library — a guess here would render a broken background.
	 */
	$mod = (string) get_theme_mod( 'clinic_cases_hero_image', '' );
	if ( '' !== $mod && ! get_field( 'hero_image', $id ) ) {
		$attachment = attachment_url_to_postid( $mod );
		if ( $attachment ) {
			update_field( 'hero_image', $attachment, $id );
			$written++;
		}
	}

	return $written;
}

/**
 * Provision and seed the page on an admin load, once per seed version.
 */
add_action(
	'admin_init',
	function () {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		$id = wellspring_cases_page_id( true );
		if ( ! $id ) {
			return;
		}

		if ( get_option( 'wellspring_cases_page_seeded' ) === WELLSPRING_CASES_SEED_VERSION ) {
			return;
		}

		/*
		 * Version 1 named the page after its purpose. page_h1 falls back to the
		 * page title, so with the seed empty that internal label rendered as the
		 * public H1 on /clinic-cases. Renamed so the fallback is presentable.
		 */
		if ( 'Clinic cases (page content)' === get_post_field( 'post_title', $id ) ) {
			wp_update_post(
				array(
					'ID'         => $id,
					'post_title' => WELLSPRING_CASES_PAGE_TITLE,
				)
			);
		}

		$written = wellspring_seed_cases_page( $id );

		update_option( 'wellspring_cases_page_seeded', WELLSPRING_CASES_SEED_VERSION );

		if ( $written ) {
			set_transient( 'wellspring_cases_page_seeded_notice', $written, 60 );
		}
	}
);

add_action(
	'admin_notices',
	function () {
		$n = get_transient( 'wellspring_cases_page_seeded_notice' );
		if ( false === $n ) {
			return;
		}
		delete_transient( 'wellspring_cases_page_seeded_notice' );

		$id = wellspring_cases_page_id();

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
			esc_html( sprintf( '%d field(s) on the Clinic Cases listing page filled in with the heading and intro the page was already showing.', $n ) ),
			esc_url( (string) get_edit_post_link( $id ) ),
			esc_html__( 'Review it', 'wellspring' )
		);
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
			esc_url( (string) get_edit_post_link( $id ) ),
			esc_html__( 'Clinic cases', 'wellspring' ),
			esc_html__( 'The order the cases appear in comes from the Order number on each one — lowest first. Use Quick Edit to change it.', 'wellspring' )
		);
	}
);

/**
 * Replace the admin bar's Edit link on /clinic-cases.
 *
 * An archive has no post of its own, so core's edit node falls through to the
 * front page — "Edit Home Page", which sends you somewhere you did not ask to
 * go. Swapped for the page that actually holds this listing's content. Runs
 * after core's node is added at priority 80.
 */
add_action(
	'admin_bar_menu',
	function ( $bar ) {
		if ( ! is_post_type_archive( 'clinic_case' ) ) {
			return;
		}

		$bar->remove_node( 'edit' );

		$id = wellspring_cases_page_id();
		if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}

		$bar->add_node(
			array(
				'id'    => 'edit',
				'title' => __( 'Edit Clinic Cases', 'wellspring' ),
				'href'  => (string) get_edit_post_link( $id ),
			)
		);
	},
	100
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
