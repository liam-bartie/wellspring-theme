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

	$cache = array(
		'intro' => array(
			'eyebrow'     => ws_field( 'intro_eyebrow', '' ),
			'title'       => ws_field( 'intro_title', '' ),
			'body'        => ws_field( 'intro_body', "For over a decade, Dr. Laura Cowburn has helped patients in Calgary move through pain, sleep trouble, hormonal shifts, digestive issues, and the everyday patterns that wear them down. Our practice blends acupuncture, herbal medicine, and old-fashioned, careful listening — and we welcome new patients, with or without a referral. Whatever brought you here, we'd like to help." ),
		),
		'wwt' => array(
			'eyebrow'     => ws_field( 'wwt_eyebrow', 'What we treat' ),
			'title'       => ws_field( 'wwt_title', 'A wide range of conditions, drawn from thousands of years of practice.' ),
			'lede'        => ws_field( 'wwt_lede', 'From acute pain to chronic patterns, hormonal cycles to mental clarity — acupuncture and herbal medicine address the body as a whole, not in parts.' ),
		),
		'practitioner' => array(
			'eyebrow'     => ws_field( 'practitioner_eyebrow', 'Meet your practitioner' ),
			'name'        => ws_field( 'practitioner_name', 'Dr. Laura Cowburn' ),
			'credentials' => ws_field( 'practitioner_credentials', 'Doctor of Traditional Chinese Medicine · Registered Acupuncturist (Alberta)' ),
			'bio'         => ws_field( 'practitioner_bio', 'For more than a decade, Dr. Cowburn has practised in Calgary — drawing on acupuncture, herbal medicine, cupping, and patient counsel to help her clients feel themselves again. Her approach combines classical TCM diagnosis with a modern, evidence-aware lens, and a genuine commitment to time spent listening.' ),
			'link_label'  => ws_field( 'practitioner_link_label', 'Read her full story' ),
			'link_url'    => ws_field( 'practitioner_link_url', wellspring_page_url( 'about' ) ),
			'portrait'    => function_exists( 'get_field' ) ? get_field( 'practitioner_portrait' ) : null,
		),
		'modalities' => array(
			'eyebrow'     => ws_field( 'modalities_eyebrow', 'Our practice' ),
			'title'       => ws_field( 'modalities_title', 'Two ancient modalities, applied with modern care.' ),
			'tcm_title'   => ws_field( 'tcm_title', 'What is Traditional Chinese Medicine (TCM)?' ),
			'tcm_body'    => ws_field( 'tcm_body', '' ),
			'tcm_image'   => function_exists( 'get_field' ) ? get_field( 'tcm_image' ) : null,
			'acu_title'   => ws_field( 'acupuncture_title', 'What is Acupuncture?' ),
			'acu_body'    => ws_field( 'acupuncture_body', '' ),
			'acu_image'   => function_exists( 'get_field' ) ? get_field( 'acupuncture_image' ) : null,
		),
		'cases' => array(
			'eyebrow'     => ws_field( 'cases_eyebrow', 'Cases from the clinic' ),
			'title'       => ws_field( 'cases_title', 'Real patients, real outcomes.' ),
			'lede'        => ws_field( 'cases_lede', '' ),
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
	$ids = function_exists( 'get_field' ) ? get_field( 'cases_featured' ) : array();

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
