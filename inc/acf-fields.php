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
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
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

					// INTRO (white section between reviewed-by badge and cards)
					array(
						'key'   => 'field_home_intro_tab',
						'label' => 'Intro section',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
					),
					array(
						'key'     => 'field_home_intro_note',
						'label'   => 'About this section',
						'type'    => 'message',
						'message' => 'White-background introduction that sits between the reviewed-by badge and the "What we treat" cards. Leave the body empty to skip the section.',
					),
					array(
						'key'           => 'field_home_intro_eyebrow',
						'name'          => 'intro_eyebrow',
						'label'         => 'Eyebrow (optional)',
						'type'          => 'text',
						'default_value' => '',
					),
					array(
						'key'           => 'field_home_intro_title',
						'name'          => 'intro_title',
						'label'         => 'Headline (optional)',
						'type'          => 'text',
						'default_value' => '',
					),
					array(
						'key'           => 'field_home_intro_body',
						'name'          => 'intro_body',
						'label'         => 'Body',
						'instructions'  => 'A short welcoming paragraph that sets context after the hero.',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => "For over a decade, Dr. Laura Cowburn has helped patients in Calgary move through pain, sleep trouble, hormonal shifts, digestive issues, and the everyday patterns that wear them down. Our practice blends acupuncture, herbal medicine, and old-fashioned, careful listening — and we welcome new patients, with or without a referral. Whatever brought you here, we'd like to help.",
					),

					// WHAT WE TREAT
					array(
						'key'   => 'field_wwt_tab',
						'label' => 'What we treat',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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
						'default_value' => 'A wide range of conditions, drawn from thousands of years of practice.',
					),
					array(
						'key'           => 'field_wwt_lede',
						'name'          => 'wwt_lede',
						'label'         => 'Section sub-copy',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => 'From acute pain to chronic patterns, hormonal cycles to mental clarity — acupuncture and herbal medicine address the body as a whole, not in parts.',
					),
					array(
						'key'          => 'field_wwt_note',
						'label'        => 'Cards source',
						'type'         => 'message',
						'message'      => 'The condition cards below this section are pulled automatically from the sub-pages of "What We Treat". Edit each sub-page (title, excerpt, featured image) and the home cards will update.',
					),

					// PRACTITIONER
					array(
						'key'   => 'field_pract_tab',
						'label' => 'Practitioner',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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

					// FEATURED CASES
					array(
						'key'   => 'field_home_cases_tab',
						'label' => 'Featured cases',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
					),
					array(
						'key'     => 'field_home_cases_note',
						'label'   => 'About this section',
						'type'    => 'message',
						'message' => 'A homepage strip showing 3 clinic cases. By default the 3 most recent published cases appear automatically — or pick specific cases below to feature.',
					),
					array(
						'key'           => 'field_home_cases_eyebrow',
						'name'          => 'cases_eyebrow',
						'label'         => 'Section eyebrow',
						'type'          => 'text',
						'default_value' => 'Cases from the clinic',
					),
					array(
						'key'           => 'field_home_cases_title',
						'name'          => 'cases_title',
						'label'         => 'Section headline',
						'type'          => 'text',
						'default_value' => 'Real patients, real outcomes.',
					),
					array(
						'key'           => 'field_home_cases_lede',
						'name'          => 'cases_lede',
						'label'         => 'Section sub-copy (optional)',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => '',
					),
					array(
						'key'           => 'field_home_cases_featured',
						'name'          => 'cases_featured',
						'label'         => 'Featured cases (optional)',
						'instructions'  => 'Pick up to 3 cases to feature. Leave empty to show the 3 most recent published cases automatically.',
						'type'          => 'relationship',
						'post_type'     => array( 'clinic_case' ),
						'filters'       => array( 'search' ),
						'max'           => 3,
						'return_format' => 'id',
					),

					// TESTIMONIALS
					array(
						'key'   => 'field_testi_tab',
						'label' => 'Testimonials',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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
						'key'           => 'field_reviews_rating',
						'name'          => 'reviews_rating',
						'label'         => 'Google rating',
						'instructions'  => 'Your overall Google star rating, e.g. 4.9. Leave blank to hide the rating badge.',
						'type'          => 'text',
						'default_value' => '5.0',
					),
					array(
						'key'           => 'field_reviews_count',
						'name'          => 'reviews_count',
						'label'         => 'Number of Google reviews',
						'instructions'  => 'Total review count shown next to the rating. Update this as you collect more reviews.',
						'type'          => 'text',
						'default_value' => '12',
					),
					array(
						'key'           => 'field_reviews_url',
						'name'          => 'reviews_url',
						'label'         => 'Google reviews link',
						'instructions'  => 'URL to your Google Business profile / reviews. Used by the rating badge and the "Read all reviews" button.',
						'type'          => 'url',
						'default_value' => 'https://maps.app.goo.gl/wXXxB6TmT6yLELhJ9',
					),
					array(
						'key'           => 'field_reviews_cta_label',
						'name'          => 'reviews_cta_label',
						'label'         => '"Read all reviews" button label',
						'type'          => 'text',
						'default_value' => 'Read all reviews on Google',
					),
					array(
						'key'       => 'field_reviews_message',
						'label'     => 'Review quotes',
						'type'      => 'message',
						'message'   => 'The individual review quotes in the slider are managed in the theme file <code>template-parts/reviews-slider.php</code> (look for the <code>$ws_reviews</code> list, with editing notes at the top). The fields above control the Google rating badge and the "Read all reviews" link.',
						'new_lines' => 'wpautop',
						'esc_html'  => 0,
					),

					// CTA
					array(
						'key'   => 'field_cta_tab',
						'label' => 'Closing CTA',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
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
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => "TCM addresses the whole person — body, mind, and the patterns that link them. Browse by category, or get in touch if you don't see what you're looking for.",
					),
					array(
						'key'     => 'field_wwt_hero_note',
						'label'   => 'Hero background image',
						'type'    => 'message',
						'message' => 'The hero background image is the page\'s <strong>Featured image</strong>. Set it via the Featured image panel in the sidebar.',
					),

					// INTRO TEXT (white section between badge and cards)
					array(
						'key'   => 'field_wwt_intro_tab',
						'label' => 'Intro section',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
					),
					array(
						'key'     => 'field_wwt_intro_note',
						'label'   => 'About this section',
						'type'    => 'message',
						'message' => 'White-background introduction that sits between the reviewed-by badge and the cards grid. Use it to set context before visitors browse the conditions. Leave the body empty to hide the whole section.',
					),
					array(
						'key'           => 'field_wwt_intro_eyebrow',
						'name'          => 'intro_eyebrow',
						'label'         => 'Eyebrow (optional)',
						'type'          => 'text',
						'default_value' => '',
					),
					array(
						'key'           => 'field_wwt_intro_title',
						'name'          => 'intro_title',
						'label'         => 'Headline (optional)',
						'type'          => 'text',
						'default_value' => '',
					),
					array(
						'key'           => 'field_wwt_intro_body',
						'name'          => 'intro_body',
						'label'         => 'Body',
						'instructions'  => 'A paragraph or two of context. Leave empty to skip this section entirely.',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => "Whether you're managing chronic pain, navigating hormonal shifts, recovering from injury, or just struggling to sleep — there's a good chance acupuncture and TCM can help. Below are the areas of focus we see most often. Each links to a dedicated page with conditions, treatment context, and what to expect.",
					),

					// CARDS SECTION
					array(
						'key'   => 'field_wwt_cards_tab',
						'label' => 'Cards section',
						'type'  => 'accordion',
						'open'  => 0,
						'multi_expand' => 1,
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
						'default_value' => 'The areas we treat most often.',
					),
					array(
						'key'           => 'field_wwt_cards_intro',
						'name'          => 'cards_intro',
						'label'         => 'Section sub-copy (optional)',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'default_value' => 'Acupuncture and Traditional Chinese Medicine address the root causes of imbalance, not just the symptoms. The areas below cover the conditions we see most often — but if you don\'t see what you\'re looking for, get in touch. There\'s a good chance we can help.',
					),
					array(
						'key'     => 'field_wwt_cards_note',
						'label'   => 'Where the cards come from',
						'type'    => 'message',
						'message' => 'Each tile is a sub-page of this page (Pain Relief, Women\'s Health, etc.). Edit a sub-page\'s title, excerpt, and featured image to change the matching tile. Use the "Tile order" control below to set the order they appear in.',
					),
					array(
						'key'           => 'field_wwt_cards_order',
						'name'          => 'cards_order',
						'label'         => 'Tile order (optional)',
						'instructions'  => 'Drag the tiles into the order you want them to appear. Leave empty to show every sub-page automatically, in page order.',
						'type'          => 'relationship',
						'post_type'     => array( 'page' ),
						'filters'       => array( 'search' ),
						'elements'      => array( 'featured_image' ),
						'return_format' => 'id',
					),

					// CONTENT BELOW THE TILES
					array(
						'key'          => 'field_wwt_content_tab',
						'label'        => 'Content below the tiles',
						'type'         => 'accordion',
						'open'         => 0,
						'multi_expand' => 1,
					),
					array(
						'key'           => 'field_wwt_below_content',
						'name'          => 'wwt_below_content',
						'label'         => 'Content below the tiles',
						'instructions'  => 'The sections shown beneath the condition tiles — Our Approach and Your First Visit. The FAQ is managed in its own repeater below.',
						'type'          => 'wysiwyg',
						'tabs'          => 'all',
						'toolbar'       => 'full',
						'media_upload'  => 1,
						'default_value' => "<h2>Our Approach</h2>
<p>Acupuncture and TCM don't treat diseases the way Western medicine does — they help the body return to balance, and trust the body's own healing intelligence to do the rest. This is why a single course of treatment can address conditions that span multiple body systems, and why old patterns of recurrence often resolve along with the primary concern.</p>
<h3>Got questions about acupuncture?</h3>
<p>Book a free 15-minute phone consult to talk through your concerns and decide if treatment is right for you. No pressure, no commitment.</p>
<p><a class='ws-btn' href='/book-appointments/'>Book a consult</a></p>
<h2>Your First Visit</h2>
<p>Your first visit lasts about 50 minutes. We'll talk through your full health history, current concerns, sleep patterns, diet, and stress — then move into a treatment session that may include acupuncture, an herbal recommendation, cupping, or tui na (gentle bodywork). Most patients leave feeling deeply relaxed and a little surprised at how thorough the conversation was.</p>
<h3>Ready to book?</h3>
<p>New patients welcome. Direct billing to most major insurers.</p>
<p><a class='ws-btn' href='/book-appointments/'>Book an appointment</a></p>",
					),
					array(
						'key'          => 'field_wwt_faq_acc',
						'label'        => 'Frequently asked questions',
						'type'         => 'accordion',
						'open'         => 0,
						'multi_expand' => 1,
					),
					array(
						'key'          => 'field_wwt_faqs',
						'name'         => 'wwt_faqs',
						'label'        => 'FAQ items',
						'instructions' => 'Each row is one question and answer, shown as a collapsible accordion. Drag rows to reorder; add or remove as needed.',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => 'Add question',
						'sub_fields'   => array(
							array(
								'key'   => 'field_wwt_faq_q',
								'name'  => 'faq_question',
								'label' => 'Question',
								'type'  => 'text',
							),
							array(
								'key'          => 'field_wwt_faq_a',
								'name'         => 'faq_answer',
								'label'        => 'Answer',
								'type'         => 'wysiwyg',
								'tabs'         => 'visual',
								'toolbar'      => 'basic',
								'media_upload' => 0,
							),
						),
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
 * Field group for the "About" page.
 *
 * A single rich-text "Main body" box, pre-filled with the current content so
 * nothing has to be re-typed. The native block editor / content canvas is
 * hidden on this page (see inc/template-functions.php) so all editing happens
 * in this one tidy box.
 */
add_action(
	'acf/init',
	function () {
		$about_page = get_page_by_path( 'about' );
		if ( ! $about_page ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'    => 'group_wellspring_about',
				'title'  => 'About page content',
				'fields' => array(
					array(
						'key'          => 'field_about_body_acc',
						'label'        => 'Page content',
						'type'         => 'accordion',
						'open'         => 1,
						'multi_expand' => 1,
					),
					array(
						'key'           => 'field_about_main_body',
						'name'          => 'about_main_body',
						'label'         => 'Main body',
						'instructions'  => 'The full body of the About page. Use the toolbar to format text and add headings, lists, links, and images.',
						'type'          => 'wysiwyg',
						'tabs'          => 'all',
						'toolbar'       => 'full',
						'media_upload'  => 1,
						'default_value' => "<h2>Meet Dr. Laura Cowburn</h2>
<p>Dr. Laura Cowburn is a Doctor of Traditional Chinese Medicine and a Registered Acupuncturist with the College of Acupuncturists of Alberta (CAA). She brings a rare combination of extensive medical knowledge and deep clinical experience to every treatment, supported by two master's degrees from top Canadian and Chinese universities.</p>
<p>Her lifelong study of Daoism and Qi practice informs a treatment style that is both highly skilled and quietly attentive. Outside the clinic, she is a passionate mountain lover — backpacking, ice climbing, mountaineering — an avid reader, and a mother of two grown children.</p>
<p>Connect with Dr. Cowburn on <a href='https://www.linkedin.com/in/laura19zh/'>LinkedIn</a>.</p>
<h2>Our Clinic</h2>
<p>Wellspring Health Acupuncture &amp; TCM Clinic is a member of the Lochend Clinic Health Collective, located in Inglewood, Calgary. We provide holistic health care through acupuncture, no-needle acupuncture, and TCM herbal medicine.</p>
<p>Our approach is patient-first and grounded in real-world clinical experience. We aim to deliver premium treatments with instant, sustainable, and desirable results — helping the body heal itself rather than masking symptoms.</p>
<h3>Our Services</h3>
<ul>
<li>Premium acupuncture treatments with instant and lasting results</li>
<li>No-needle acupuncture for those with needle aversion (trypanophobia), including auricular medicine and ear seeds for acupoints</li>
<li>Top-quality TCM herbs to address the root cause of health issues</li>
<li>Free educational sessions for the community</li>
</ul>
<h2>Giving Back: Loving Heart Wellness Association</h2>
<p>We actively participate in charity work through our partnership with the Loving Heart Wellness Association (LHWA), a registered charity. Through LHWA we are able to:</p>
<ul>
<li>Donate acupuncture treatments to community contributors such as Mustard Seed workers</li>
<li>Donate top-quality TCM herbs</li>
<li>Donate high-quality ear seeds</li>
<li>Offer free, health-focused educational sessions to the public</li>
</ul>
<p>Learn more at <a href='https://www.lovingheartswellness.org/'>lovingheartswellness.org</a>.</p>
<h2>Independent Pharmacy Partners</h2>
<p>We work alongside a network of trusted independent pharmacies across Calgary to make it easy for our patients to fill prescriptions and access pharmacist support.</p>
<ul>
<li><strong>Sage Plus Clinical Pharmacy</strong><br>860 13 St SE, Calgary, AB T2G 1R2 · (587) 480-0178 · <a href='https://sageplus.ca/'>sageplus.ca</a></li>
<li><strong>Marshall Drugs (Inglewood)</strong><br>1231–1233 9 Ave SE, Calgary, AB T2G 0S9 · (403) 265-5766 · <a href='https://marshalldrugs.com/'>marshalldrugs.com</a></li>
<li><strong>Corner Drugstore (Downtown Border)</strong><br>602 8 Ave SE, Calgary, AB T2G 0M1 · (403) 263-4620 · <a href='https://www.cornerdrugstore.ca/'>cornerdrugstore.ca</a></li>
<li><strong>Downtown Drugmart</strong><br>115 8 Ave SW, Calgary, AB T2P 1B4 · (403) 266-2059 · <a href='https://www.pharmachoice.com/locations/downtown-drugmart/'>pharmachoice.com</a></li>
<li><strong>MediMax Pharmacy</strong><br>240-520 3 Ave SW, Calgary, AB T2P 0R3 · (403) 454-4412 · <a href='https://www.guardian-ida-remedysrx.ca/'>guardian-ida-remedysrx.ca</a></li>
<li><strong>Heathers Pharmacy</strong><br>Unit 104, 305 10 St NW, Calgary, AB · (825) 540-1500 · <a href='https://heatherspharmacy.ca/'>heatherspharmacy.ca</a></li>
<li><strong>Riverside Pharmacy</strong><br>10-630 1st Ave NE, Calgary, AB T2E 0B6 · (403) 457-9033</li>
<li><strong>Bridgedale Pharmacy</strong><br>1010 1st Ave NE, Calgary, AB T2E 7W7 · (403) 269-6440 · <a href='https://www.pharmachoice.com/locations/bridgedale-pharmacy/'>pharmachoice.com</a></li>
<li><strong>Tower Drugs</strong><br>#137, 131 9 Ave SW, Calgary, AB T2P 1K1 · (403) 261-2006 · <a href='https://www.pharmachoice.com/locations/tower-drugs/'>pharmachoice.com</a></li>
<li><strong>Aster Mettra Pharmacy &amp; Travel Clinic</strong><br>931 5 Ave SW, Calgary, AB T2P 0N8 · (587) 702-3434 · <a href='https://asterpharmacy.ca/'>asterpharmacy.ca</a></li>
<li><strong>Midtown Remedy's Rx Pharmacy &amp; Travel Clinic</strong><br>1110 11 Ave SW, Calgary, AB T2R 0G4 · (403) 452-0712 · <a href='https://www.guardian-ida-remedysrx.ca/'>guardian-ida-remedysrx.ca</a></li>
</ul>
<h2>Listen first, treat second.</h2>
<p>Every appointment begins with a real conversation about how you're feeling, what you've tried, and where you want to get to. From there we plan a course of treatment that fits your body and your life.</p>
<blockquote><p>The body has its own intelligence. Our work is to listen carefully, then gently guide it back to balance.</p><cite>Dr. Laura Cowburn</cite></blockquote>
<p><strong>10+</strong> years in practice &nbsp;·&nbsp; <strong>2</strong> master's degrees in TCM &nbsp;·&nbsp; <strong>1,000+</strong> patients treated</p>",
					),
				),
				'location'               => array(
					array(
						array(
							'param'    => 'page',
							'operator' => '==',
							'value'    => $about_page->ID,
						),
					),
				),
				'menu_order'             => 0,
				'position'               => 'normal',
				'style'                  => 'default',
				'label_placement'        => 'top',
				'instruction_placement'  => 'label',
			)
		);
	}
);

/**
 * Scope the What We Treat "Tile order" relationship picker to this page's own
 * sub-pages, so editors only ever see the condition tiles.
 */
add_filter(
	'acf/fields/relationship/query/key=field_wwt_cards_order',
	function ( $args ) {
		$wwt = get_page_by_path( 'what-we-treat' );
		if ( $wwt instanceof WP_Post ) {
			$args['post_parent'] = $wwt->ID;
		}
		return $args;
	}
);

/**
 * Seed the What We Treat FAQ repeater once, so it arrives pre-filled and
 * immediately editable (a repeater can't carry default rows in its field
 * definition). Guarded by an option flag so it only ever runs a single time,
 * and only when the repeater is still empty.
 */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'ws_wwt_faqs_seeded' ) ) {
			return;
		}
		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			return;
		}
		$wwt = get_page_by_path( 'what-we-treat' );
		if ( ! $wwt instanceof WP_Post ) {
			return;
		}

		$existing = get_field( 'wwt_faqs', $wwt->ID );
		if ( empty( $existing ) ) {
			$faqs = array(
				array(
					'faq_question' => 'How many acupuncture sessions do I need?',
					'faq_answer'   => "<p>The number of acupuncture treatments you need depends on your specific condition, its severity, and how your body responds. Treatment plans are personalized to your medical history, needs, and overall health. In some cases one treatment is enough to address acute pain; in others, you may benefit from 10 or more sessions over a course of treatment.</p>",
				),
				array(
					'faq_question' => 'Is acupuncture covered by insurance in Alberta?',
					'faq_answer'   => "<p>Acupuncture is generally not covered by Alberta Health Care (provincial insurance). However, it is commonly covered under private extended health benefits or company group insurance plans, often listed under paramedical services. Coverage typically requires the practitioner to be a registered, licensed acupuncturist in Alberta — which we are.</p>",
				),
				array(
					'faq_question' => 'Does acupuncture hurt?',
					'faq_answer'   => "<p>Most people don't report pain during acupuncture, though you may feel some sensations. What you feel depends on your pain tolerance and overall sensitivity. Your first treatment may seem more intense than the ones that follow — partly nerves, partly the body responding to needling for the first time. Most patients become more relaxed and comfortable with each subsequent session.</p>",
				),
				array(
					'faq_question' => 'How deep do the needles go?',
					'faq_answer'   => "<p>Acupuncture needles generally penetrate from 1/16 of an inch to 1.5 inches (roughly 2mm to 40mm), depending on body area, muscle density, and treatment goals. Delicate areas like the scalp or face receive shallow insertions (1–5 mm), while fleshier areas like the hips or glutes can be treated more deeply.</p>",
				),
				array(
					'faq_question' => 'Are the needles sterilized or one-time use?',
					'faq_answer'   => "<p>Both. In modern, regulated practices, acupuncture needles are treated as single-use medical devices: sterilized during manufacturing, opened from sealed packaging in front of you, and disposed of immediately after a single treatment.</p>",
				),
				array(
					'faq_question' => 'What are the needles made from?',
					'faq_answer'   => "<p>Modern acupuncture needles are made from high-quality, surgical-grade stainless steel — thin, flexible, hair-thin, and sterile. Handles are typically stainless steel, copper, or plastic, sometimes with a silicone coating for smoother insertion.</p>",
				),
				array(
					'faq_question' => 'How quickly does acupuncture and TCM work?',
					'faq_answer'   => "<p>Most patients see improvement within 1–2 sessions for acute conditions, and 2–3 sessions for chronic issues. Some feel relief immediately; others need a series of treatments to achieve lasting results. We'll talk through realistic expectations during your first appointment.</p>",
				),
				array(
					'faq_question' => 'What will my first appointment include?',
					'faq_answer'   => "<p>Your first appointment typically lasts 50 minutes. It includes a comprehensive intake of your health history, a TCM-based pattern diagnosis, and a personalized treatment plan. Expect detailed questions about your sleep, diet, stress, and overall patterns — followed by acupuncture and, if appropriate, an herbal recommendation.</p>",
				),
				array(
					'faq_question' => 'How will I feel after treatment?',
					'faq_answer'   => "<p>Most patients feel a deep sense of relaxation after treatment. Some experience mild soreness at the needle sites or feel a bit tired — both subside within a day. Any mild bruising or discolouration usually clears within a few days. For the first 24 hours, rest, drink plenty of water, eat well, and avoid strenuous activity. Use heat packs rather than ice.</p>",
				),
			);
			update_field( 'wwt_faqs', $faqs, $wwt->ID );
		}

		update_option( 'ws_wwt_faqs_seeded', '1' );
	}
);

/**
 * Helper to get an ACF field with a fallback default.
 *
 * Differentiates between "never saved" (null/false → use default) and
 * "explicitly cleared by editor" (empty string → respect the empty choice).
 * This means clearing a field in WP admin actually hides it on the page.
 *
 * Usage: ws_field('hero_title', 'Default headline')
 *        ws_field('cta_title', 'Default', $home_id) — read field from a specific post
 */
function ws_field( $name, $default = '', $post_id = false ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $name, $post_id );

	// Truly unset / never saved → use default.
	if ( null === $value || false === $value ) {
		return $default;
	}

	// Empty string or anything else (including 0, '0') → respect the editor's choice.
	return $value;
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
