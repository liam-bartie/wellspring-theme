<?php
/**
 * Resolved values for the home page's content blocks.
 *
 * ONE source of truth, used by two callers:
 *
 *   - front-page.php, to render the blocks from the page's top-level fields
 *   - inc/home-sections-import.php, to write those same values into
 *     "Page sections" rows
 *
 * That matters because of ws_field(): where a field has never been saved it
 * falls back to a hardcoded default, and the live page renders the default. A
 * migration that read the raw meta instead would write empty rows and blocks
 * would vanish. Reading through the same function means the migration writes
 * precisely what the page was already showing.
 *
 * GENERATED from front-page.php by seo-migration/gen_home_blocks.py — the
 * expressions below were lifted verbatim rather than retyped, so the default
 * strings cannot drift. If you change a default, change it here.
 *
 * @package Wellspring
 */

/**
 * @return array Block key => array of sub-field key => resolved value.
 */
function wellspring_home_block_values() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	// Bound to the front page rather than "the current post": the migration
	// screen runs in wp-admin where there is no current post, and an unbound
	// read returns null, so the DEFAULT would be written instead of the saved
	// value — a silent, content-shaped failure.
	$home_id = (int) get_option( 'page_on_front' );

	$cache = array(
		'intro' => array(
			'eyebrow'     => ws_field( 'intro_eyebrow', '', $home_id ),
			'title'       => ws_field( 'intro_title', '', $home_id ),
			'body'        => ws_field( 'intro_body', "For over a decade, Dr. Laura Cowburn has helped patients in Calgary move through pain, sleep trouble, hormonal shifts, digestive issues, and the everyday patterns that wear them down. Our practice blends acupuncture, herbal medicine, and old-fashioned, careful listening — and we welcome new patients, with or without a referral. Whatever brought you here, we'd like to help.", $home_id ),
		),
		'wwt' => array(
			'eyebrow'     => ws_field( 'wwt_eyebrow', 'What we treat', $home_id ),
			'title'       => ws_field( 'wwt_title', 'A wide range of conditions, drawn from thousands of years of practice.', $home_id ),
			'lede'        => ws_field( 'wwt_lede', 'From acute pain to chronic patterns, hormonal cycles to mental clarity — acupuncture and herbal medicine address the body as a whole, not in parts.', $home_id ),
		),
		'practitioner' => array(
			'eyebrow'     => ws_field( 'practitioner_eyebrow', 'Meet your practitioner', $home_id ),
			'name'        => ws_field( 'practitioner_name', 'Dr. Laura Cowburn', $home_id ),
			'credentials' => ws_field( 'practitioner_credentials', 'Doctor of Traditional Chinese Medicine · Registered Acupuncturist (Alberta)', $home_id ),
			'bio'         => ws_field( 'practitioner_bio', 'For more than a decade, Dr. Cowburn has practised in Calgary — drawing on acupuncture, herbal medicine, cupping, and patient counsel to help her clients feel themselves again. Her approach combines classical TCM diagnosis with a modern, evidence-aware lens, and a genuine commitment to time spent listening.', $home_id ),
			'link_label'  => ws_field( 'practitioner_link_label', 'Read her full story', $home_id ),
			'link_url'    => ws_field( 'practitioner_link_url', wellspring_page_url( 'about' ), $home_id ),
			'portrait'    => function_exists( 'get_field' ) ? get_field( 'practitioner_portrait', $home_id ) : null,
		),
		'modalities' => array(
			'eyebrow'     => ws_field( 'modalities_eyebrow', 'Our practice', $home_id ),
			'title'       => ws_field( 'modalities_title', 'Two ancient modalities, applied with modern care.', $home_id ),
			'tcm_title'   => ws_field( 'tcm_title', 'What is Traditional Chinese Medicine (TCM)?', $home_id ),
			'tcm_body'    => ws_field( 'tcm_body', '', $home_id ),
			'tcm_image'   => function_exists( 'get_field' ) ? get_field( 'tcm_image', $home_id ) : null,
			'acu_title'   => ws_field( 'acupuncture_title', 'What is Acupuncture?', $home_id ),
			'acu_body'    => ws_field( 'acupuncture_body', '', $home_id ),
			'acu_image'   => function_exists( 'get_field' ) ? get_field( 'acupuncture_image', $home_id ) : null,
		),
		'cases' => array(
			'eyebrow'     => ws_field( 'cases_eyebrow', 'Cases from the clinic', $home_id ),
			'title'       => ws_field( 'cases_title', 'Real patients, real outcomes.', $home_id ),
			'lede'        => ws_field( 'cases_lede', '', $home_id ),
		),
	);

	return $cache;
}

/**
 * The featured clinic cases shown on the home page, as WP_Post objects.
 *
 * Kept separate from the text values above because it is a query, not a field
 * read, and front-page.php resolves it before the blocks are rendered.
 *
 * @return array WP_Post objects.
 */
function wellspring_home_featured_case_ids() {
	$home_id = (int) get_option( 'page_on_front' );
	$ids     = function_exists( 'get_field' ) ? get_field( 'cases_featured', $home_id ) : array();

	if ( empty( $ids ) || ! is_array( $ids ) ) {
		return array();
	}

	// The relationship field can return objects or IDs depending on config.
	return array_values(
		array_filter(
			array_map(
				function ( $item ) {
					if ( is_object( $item ) && isset( $item->ID ) ) {
						return (int) $item->ID;
					}
					return (int) $item;
				},
				$ids
			)
		)
	);
}

/**
 * The clinic cases to feature, resolved to WP_Post objects.
 *
 * Carries the fallback front-page.php has always had: when no cases are
 * explicitly chosen, show the three most recent. That fallback is why the home
 * page displayed three cards while 'cases_featured' was empty — and why a
 * migration that recorded "no cases chosen" produced an empty section. Both
 * the template and the section layout now resolve through here.
 *
 * @param array|null $chosen Explicitly chosen IDs/objects, or null to read the
 *                           home page's own field.
 * @return array WP_Post objects, at most three.
 */
function wellspring_home_featured_cases( $chosen = null ) {
	if ( null === $chosen ) {
		$chosen = wellspring_home_featured_case_ids();
	}

	$ids = array();
	foreach ( (array) $chosen as $item ) {
		if ( is_object( $item ) && isset( $item->ID ) ) {
			$ids[] = (int) $item->ID;
		} elseif ( is_numeric( $item ) ) {
			$ids[] = (int) $item;
		}
	}

	if ( $ids ) {
		return get_posts(
			array(
				'post_type'      => 'clinic_case',
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 3,
			)
		);
	}

	return get_posts(
		array(
			'post_type'      => 'clinic_case',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}
