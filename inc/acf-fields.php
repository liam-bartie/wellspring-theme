<?php
/**
 * ACF field group registration for the home page.
 *
 * Fields are defined in code (not just the admin DB) so they ship
 * with the theme and survive cross-environment moves.
 *
 * @package Wellspring
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return; // ACF not active — bail.
}

add_action(
	'acf/init',
	function () {
		$home_id = (int) get_option( 'page_on_front' );
		if ( ! $home_id ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_wellspring_home',
				'title'    => 'Home page content',
				'fields'   => array(

					// HERO
					array(
						'key'   => 'field_hero_tab',
						'label' => 'Hero',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_hero_eyebrow',
						'name'          => 'hero_eyebrow',
						'label'         => 'Eyebrow',
						'instructions'  => 'Small text above the headline (e.g. "Calgary · Inglewood")',
						'type'          => 'text',
						'default_value' => 'Calgary · Inglewood',
					),
					array(
						'key'           => 'field_hero_title',
						'name'          => 'hero_title',
						'label'         => 'Headline',
						'type'          => 'text',
						'default_value' => 'Calm, considered care for body and mind.',
					),
					array(
						'key'           => 'field_hero_lede',
						'name'          => 'hero_lede',
						'label'         => 'Sub-headline / lede',
						'type'          => 'textarea',
						'rows'          => 3,
						'default_value' => "Acupuncture and Traditional Chinese Medicine for pain relief, women's health, sleep, digestion, and beyond — practised by Dr. Laura Cowburn for over a decade.",
					),
					array(
						'key'           => 'field_hero_btn1_label',
						'name'          => 'hero_primary_button_label',
						'label'         => 'Primary button label',
						'type'          => 'text',
						'default_value' => 'Book an appointment',
					),
					array(
						'key'           => 'field_hero_btn1_url',
						'name'          => 'hero_primary_button_url',
						'label'         => 'Primary button URL',
						'type'          => 'text',
						'instructions'  => 'Accepts full URL, relative path (/book-appointments/), tel:+15876004945, or mailto:hi@example.com',
						'default_value' => '/book-appointments/',
					),
					array(
						'key'           => 'field_hero_btn2_label',
						'name'          => 'hero_secondary_button_label',
						'label'         => 'Secondary button label',
						'type'          => 'text',
						'default_value' => 'See what we treat',
					),
					array(
						'key'           => 'field_hero_btn2_url',
						'name'          => 'hero_secondary_button_url',
						'label'         => 'Secondary button URL',
						'type'          => 'text',
						'instructions'  => 'Accepts full URL, relative path (/book-appointments/), tel:+15876004945, or mailto:hi@example.com',
						'default_value' => '/what-we-treat/',
					),
					array(
						'key'           => 'field_hero_bg_image',
						'name'          => 'hero_background_image',
						'label'         => 'Background image (optional)',
						'instructions'  => 'A subtle, calming image. Will be displayed with a soft overlay so text stays readable. Leave empty for the clean white hero.',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),

					// WHAT WE TREAT
					array(
						'key'   => 'field_wwt_tab',
						'label' => 'What we treat',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_wwt_eyebrow',
						'name'          => 'wwt_eyebrow',
						'label'         => 'Eyebrow',
						'type'          => 'text',
						'default_value' => 'What we treat',
					),
					array(
						'key'           => 'field_wwt_title',
						'name'          => 'wwt_title',
						'label'         => 'Section headline',
						'type'          => 'text',
						'default_value' => 'Six areas of focus, drawn from thousands of years of practice.',
					),
					array(
						'key'           => 'field_wwt_lede',
						'name'          => 'wwt_lede',
						'label'         => 'Section sub-copy',
						'type'          => 'textarea',
						'rows'          => 2,
						'default_value' => 'From acute pain to chronic patterns, hormonal cycles to mental clarity — acupuncture and herbal medicine address the body as a whole, not in parts.',
					),
					array(
						'key'          => 'field_wwt_note',
						'label'        => 'Cards source',
						'type'         => 'message',
						'message'      => 'The six condition cards below this section are pulled automatically from the sub-pages of "What We Treat". Edit each sub-page (title, excerpt, featured image) and the home cards will update.',
					),

					// PRACTITIONER
					array(
						'key'   => 'field_pract_tab',
						'label' => 'Practitioner',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_pract_eyebrow',
						'name'          => 'practitioner_eyebrow',
						'label'         => 'Eyebrow',
						'type'          => 'text',
						'default_value' => 'Meet your practitioner',
					),
					array(
						'key'           => 'field_pract_name',
						'name'          => 'practitioner_name',
						'label'         => 'Name',
						'type'          => 'text',
						'default_value' => 'Dr. Laura Cowburn',
					),
					array(
						'key'           => 'field_pract_credentials',
						'name'          => 'practitioner_credentials',
						'label'         => 'Credentials line',
						'type'          => 'text',
						'default_value' => 'Doctor of Traditional Chinese Medicine · Registered Acupuncturist (Alberta)',
					),
					array(
						'key'           => 'field_pract_bio',
						'name'          => 'practitioner_bio',
						'label'         => 'Bio',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => 'For more than a decade, Dr. Cowburn has practised in Calgary — drawing on acupuncture, herbal medicine, cupping, and patient counsel to help her clients feel themselves again. Her approach combines classical TCM diagnosis with a modern, evidence-aware lens, and a genuine commitment to time spent listening.',
					),
					array(
						'key'           => 'field_pract_link_label',
						'name'          => 'practitioner_link_label',
						'label'         => 'Link label',
						'type'          => 'text',
						'default_value' => 'Read her full story',
					),
					array(
						'key'           => 'field_pract_link_url',
						'name'          => 'practitioner_link_url',
						'label'         => 'Link URL',
						'type'          => 'text',
						'instructions'  => 'Accepts full URL, relative path (/book-appointments/), tel:+15876004945, or mailto:hi@example.com',
						'default_value' => '/about/',
					),
					array(
						'key'           => 'field_pract_portrait',
						'name'          => 'practitioner_portrait',
						'label'         => 'Portrait photo',
						'instructions'  => 'A 4:5 portrait works best. Leave empty to show the LC monogram placeholder.',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),

					// MODALITIES — TCM + Acupuncture explainers
					array(
						'key'   => 'field_mod_tab',
						'label' => 'Our practice',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_mod_eyebrow',
						'name'          => 'modalities_eyebrow',
						'label'         => 'Section eyebrow (optional)',
						'instructions'  => 'Leave blank to skip the section header.',
						'type'          => 'text',
						'default_value' => 'Our practice',
					),
					array(
						'key'           => 'field_mod_title',
						'name'          => 'modalities_title',
						'label'         => 'Section headline (optional)',
						'type'          => 'text',
						'default_value' => 'Two ancient modalities, applied with modern care.',
					),
					array(
						'key'           => 'field_tcm_image',
						'name'          => 'tcm_image',
						'label'         => 'Block 1 — Image',
						'instructions'  => 'Optional image displayed above the heading. Landscape works best (3:2 ratio).',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
					array(
						'key'           => 'field_tcm_title',
						'name'          => 'tcm_title',
						'label'         => 'Block 1 — Heading',
						'type'          => 'text',
						'default_value' => 'What is Traditional Chinese Medicine (TCM)?',
					),
					array(
						'key'           => 'field_tcm_body',
						'name'          => 'tcm_body',
						'label'         => 'Block 1 — Body',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => "Traditional Chinese Medicine (TCM) is a comprehensive healthcare system based on real-world clinical experience. For over 2,000 years, TCM has been used to diagnose, treat and prevent illness, harnessing your body's powers of self-healing.\n\nThe World Health Organization (WHO) recognizes TCM as a formal healthcare system and includes its principles in the International Classification of Diseases (ICD-11). Additionally, the National Institutes of Health (NIH) acknowledges it as a vital complementary and alternative medicine (CAM) modality.\n\nAt Wellspring Health Acupuncture & TCM Clinic, our TCM practice emphasizes acupuncture and herbal medicine as effective therapies to support your journey for a healthy and happy life.",
					),
					array(
						'key'           => 'field_acu_image',
						'name'          => 'acupuncture_image',
						'label'         => 'Block 2 — Image',
						'instructions'  => 'Optional image displayed above the heading. Landscape works best (3:2 ratio).',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
					array(
						'key'           => 'field_acu_title',
						'name'          => 'acupuncture_title',
						'label'         => 'Block 2 — Heading',
						'type'          => 'text',
						'default_value' => 'What is Acupuncture?',
					),
					array(
						'key'           => 'field_acu_body',
						'name'          => 'acupuncture_body',
						'label'         => 'Block 2 — Body',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => "Acupuncture is a core therapy within Traditional Chinese Medicine that supports the body's natural healing response by restoring balance. Your body has a complex network of energy pathways that can be thrown out of balance by internal or external factors.\n\nAt Wellspring Health, your acupuncturist assesses these patterns and helps restore healthier flow by stimulating specific acupuncture points, often by activating areas that may seem unrelated to where symptoms are felt. After the main imbalance is addressed, Chinese herbal medicine is often recommended to help support and stabilize your progress and cure.\n\nAcupuncture is recognized by the National Institutes of Health (NIH) and the World Health Organization (WHO) as an effective treatment for a variety of health conditions.",
					),

					// TESTIMONIALS
					array(
						'key'   => 'field_testi_tab',
						'label' => 'Testimonials',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_testi_eyebrow',
						'name'          => 'testimonials_eyebrow',
						'label'         => 'Eyebrow',
						'type'          => 'text',
						'default_value' => 'Patient stories',
					),
					array(
						'key'           => 'field_testi_title',
						'name'          => 'testimonials_title',
						'label'         => 'Section headline',
						'type'          => 'text',
						'default_value' => 'The work, in their words.',
					),
					array(
						'key'           => 'field_testi_1_quote',
						'name'          => 'testimonial_1_quote',
						'label'         => 'Testimonial 1 — Quote',
						'type'          => 'textarea',
						'rows'          => 3,
					),
					array(
						'key'           => 'field_testi_1_author',
						'name'          => 'testimonial_1_author',
						'label'         => 'Testimonial 1 — Author',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_testi_1_context',
						'name'          => 'testimonial_1_context',
						'label'         => 'Testimonial 1 — Context',
						'instructions'  => 'e.g. "Migraine relief"',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_testi_2_quote',
						'name'          => 'testimonial_2_quote',
						'label'         => 'Testimonial 2 — Quote',
						'type'          => 'textarea',
						'rows'          => 3,
					),
					array(
						'key'           => 'field_testi_2_author',
						'name'          => 'testimonial_2_author',
						'label'         => 'Testimonial 2 — Author',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_testi_2_context',
						'name'          => 'testimonial_2_context',
						'label'         => 'Testimonial 2 — Context',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_testi_3_quote',
						'name'          => 'testimonial_3_quote',
						'label'         => 'Testimonial 3 — Quote',
						'type'          => 'textarea',
						'rows'          => 3,
					),
					array(
						'key'           => 'field_testi_3_author',
						'name'          => 'testimonial_3_author',
						'label'         => 'Testimonial 3 — Author',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_testi_3_context',
						'name'          => 'testimonial_3_context',
						'label'         => 'Testimonial 3 — Context',
						'type'          => 'text',
					),

					// CTA
					array(
						'key'   => 'field_cta_tab',
						'label' => 'Closing CTA',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_cta_title',
						'name'          => 'cta_title',
						'label'         => 'Headline',
						'type'          => 'text',
						'default_value' => 'Ready when you are.',
					),
					array(
						'key'           => 'field_cta_lede',
						'name'          => 'cta_lede',
						'label'         => 'Sub-copy',
						'type'          => 'textarea',
						'rows'          => 2,
						'default_value' => 'New patients welcome. Appointments typically available within the week. Direct billing to most major insurers.',
					),
					array(
						'key'           => 'field_cta_btn1_label',
						'name'          => 'cta_primary_button_label',
						'label'         => 'Primary button label',
						'type'          => 'text',
						'default_value' => 'Book an appointment',
					),
					array(
						'key'           => 'field_cta_btn1_url',
						'name'          => 'cta_primary_button_url',
						'label'         => 'Primary button URL',
						'type'          => 'text',
						'instructions'  => 'Accepts full URL, relative path (/book-appointments/), tel:+15876004945, or mailto:hi@example.com',
						'default_value' => '/book-appointments/',
					),
					array(
						'key'           => 'field_cta_btn2_label',
						'name'          => 'cta_secondary_button_label',
						'label'         => 'Secondary button label',
						'type'          => 'text',
						'default_value' => 'Call (587) 600-4945',
					),
					array(
						'key'           => 'field_cta_btn2_url',
						'name'          => 'cta_secondary_button_url',
						'label'         => 'Secondary button URL',
						'type'          => 'text',
						'instructions'  => 'Accepts full URL, relative path (/book-appointments/), tel:+15876004945, or mailto:hi@example.com',
						'default_value' => 'tel:+15876004945',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'page',
							'operator' => '==',
							'value'    => $home_id,
						),
					),
				),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
			)
		);
	}
);

/**
 * Field group for the "What We Treat" hub page.
 */
add_action(
	'acf/init',
	function () {
		$wwt_page = get_page_by_path( 'what-we-treat' );
		if ( ! $wwt_page ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_wellspring_wwt',
				'title'    => 'What We Treat page',
				'fields'   => array(

					// HERO
					array(
						'key'   => 'field_wwt_hero_tab',
						'label' => 'Hero',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_wwt_hub_eyebrow',
						'name'          => 'hub_eyebrow',
						'label'         => 'Hero eyebrow',
						'type'          => 'text',
						'default_value' => 'What we treat',
					),
					array(
						'key'           => 'field_wwt_hub_lede',
						'name'          => 'hub_lede',
						'label'         => 'Hero sub-headline',
						'instructions'  => 'Short paragraph below the page title in the hero band.',
						'type'          => 'textarea',
						'rows'          => 3,
						'default_value' => "TCM addresses the whole person — body, mind, and the patterns that link them. Browse by category, or get in touch if you don't see what you're looking for.",
					),
					array(
						'key'     => 'field_wwt_hero_note',
						'label'   => 'Hero background image',
						'type'    => 'message',
						'message' => 'The hero background image is the page\'s <strong>Featured image</strong>. Set it via the Featured image panel in the sidebar.',
					),

					// CARDS SECTION
					array(
						'key'   => 'field_wwt_cards_tab',
						'label' => 'Cards section',
						'type'  => 'tab',
					),
					array(
						'key'           => 'field_wwt_cards_eyebrow',
						'name'          => 'cards_eyebrow',
						'label'         => 'Section eyebrow',
						'type'          => 'text',
						'default_value' => 'Conditions we treat',
					),
					array(
						'key'           => 'field_wwt_cards_title',
						'name'          => 'cards_title',
						'label'         => 'Section headline',
						'type'          => 'text',
						'default_value' => 'Six areas of focus.',
					),
					array(
						'key'           => 'field_wwt_cards_intro',
						'name'          => 'cards_intro',
						'label'         => 'Section sub-copy (optional)',
						'type'          => 'textarea',
						'rows'          => 3,
						'default_value' => 'Acupuncture and Traditional Chinese Medicine address the root causes of imbalance, not just the symptoms. The areas below cover the conditions we see most often — but if you don\'t see what you\'re looking for, get in touch. There\'s a good chance we can help.',
					),
					array(
						'key'     => 'field_wwt_cards_note',
						'label'   => 'Where the cards come from',
						'type'    => 'message',
						'message' => 'Cards are pulled automatically from sub-pages of this page (Pain Relief, Women\'s Health, etc.). Edit each sub-page\'s title, excerpt, and featured image to update the matching card.',
					),

					// CONTENT BLOCKS
					array(
						'key'   => 'field_wwt_content_tab',
						'label' => 'Content blocks',
						'type'  => 'tab',
					),
					array(
						'key'     => 'field_wwt_content_note',
						'label'   => 'Add additional sections',
						'type'    => 'message',
						'message' => 'Use the main Gutenberg editor above to add additional sections below the cards grid — callouts, image+text, stats, mini-CTA, etc. Insert any of the Wellspring block patterns from the block inserter.',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'page',
							'operator' => '==',
							'value'    => $wwt_page->ID,
						),
					),
				),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
			)
		);
	}
);

/**
 * Helper to get an ACF field with a fallback default.
 *
 * Usage: ws_field('hero_title', 'Default headline')
 *        ws_field('cta_title', 'Default', $home_id) — read field from a specific post
 */
function ws_field( $name, $default = '', $post_id = false ) {
	$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : '';
	return ! empty( $value ) ? $value : $default;
}

/**
 * Helper to read a field from the static front page (Home), so site-wide
 * partials (like the global CTA banner) can pull from a single source of truth.
 */
function ws_home_field( $name, $default = '' ) {
	static $home_id = null;
	if ( null === $home_id ) {
		$home_id = (int) get_option( 'page_on_front' );
	}
	if ( ! $home_id ) {
		return $default;
	}
	return ws_field( $name, $default, $home_id );
}
