<?php
/**
 * Clinic Cases — custom post type, taxonomy, and ACF fields.
 *
 * Registers a `clinic_case` CPT with archive at /clinic-cases/, plus a
 * hierarchical `case_focus` taxonomy that maps to the six "What we treat"
 * areas. Each case gets structured ACF fields for the clinical narrative
 * (presentation, diagnosis, treatment, result).
 *
 * @package Wellspring
 */

/**
 * Register the Clinic Case custom post type.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'clinic_case',
			array(
				'labels'        => array(
					'name'                  => __( 'Clinic Cases', 'wellspring' ),
					'singular_name'         => __( 'Clinic Case', 'wellspring' ),
					'add_new'               => __( 'Add new case', 'wellspring' ),
					'add_new_item'          => __( 'Add new clinic case', 'wellspring' ),
					'edit_item'             => __( 'Edit clinic case', 'wellspring' ),
					'new_item'              => __( 'New clinic case', 'wellspring' ),
					'view_item'             => __( 'View clinic case', 'wellspring' ),
					'view_items'            => __( 'View clinic cases', 'wellspring' ),
					'search_items'          => __( 'Search clinic cases', 'wellspring' ),
					'not_found'             => __( 'No clinic cases found', 'wellspring' ),
					'not_found_in_trash'    => __( 'No clinic cases in trash', 'wellspring' ),
					'all_items'             => __( 'All cases', 'wellspring' ),
					'archives'              => __( 'Clinic case archive', 'wellspring' ),
					'menu_name'             => __( 'Clinic Cases', 'wellspring' ),
				),
				'public'        => true,
				'show_in_menu'  => true,
				'show_in_rest'  => true, // Enables Gutenberg.
				'menu_position' => 25,
				'menu_icon'     => 'dashicons-clipboard',
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'has_archive'   => 'clinic-cases',
				'rewrite'       => array(
					'slug'       => 'clinic-case',
					'with_front' => false,
				),
				'hierarchical'  => false,
				'capability_type' => 'post',
			)
		);

		register_taxonomy(
			'case_focus',
			array( 'clinic_case' ),
			array(
				'labels'            => array(
					'name'              => __( 'Focus areas', 'wellspring' ),
					'singular_name'     => __( 'Focus area', 'wellspring' ),
					'search_items'      => __( 'Search focus areas', 'wellspring' ),
					'all_items'         => __( 'All focus areas', 'wellspring' ),
					'edit_item'         => __( 'Edit focus area', 'wellspring' ),
					'update_item'       => __( 'Update focus area', 'wellspring' ),
					'add_new_item'      => __( 'Add new focus area', 'wellspring' ),
					'new_item_name'     => __( 'New focus area', 'wellspring' ),
					'menu_name'         => __( 'Focus areas', 'wellspring' ),
				),
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'         => 'clinic-cases/focus',
					'with_front'   => false,
					'hierarchical' => false,
				),
			)
		);
	}
);

/**
 * Seed the six default focus areas on theme activation (or first admin load).
 * Only inserts terms that don't already exist.
 */
add_action(
	'admin_init',
	function () {
		// Bump this version to add new focus areas on the next admin load.
		$focus_seed_version = '2';
		if ( get_option( 'wellspring_focus_areas_seeded' ) !== $focus_seed_version ) {
			$defaults = array(
				'pain-relief'              => 'Pain Relief & Injury Recovery',
				'womens-health'            => "Women's Health",
				'mental-health-sleep'      => 'Mental Health & Sleep',
				'digestive-health'         => 'Digestive Health',
				'respiratory'              => 'Respiratory',
				'heart-circulation'        => 'Heart & Circulation',
				'skin-facial-health'       => 'Skin & Facial Health',
				'allergies-immune-support' => 'Allergies & Immune Support',
				'other-conditions'         => 'Other Conditions',
			);
			foreach ( $defaults as $slug => $name ) {
				if ( ! term_exists( $slug, 'case_focus' ) ) {
					wp_insert_term( $name, 'case_focus', array( 'slug' => $slug ) );
				}
			}
			update_option( 'wellspring_focus_areas_seeded', $focus_seed_version );
		}
	}
);

/**
 * ACF fields for the clinical narrative on each case.
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_wellspring_clinic_case',
				'title'    => 'Case details',
				'fields'   => array(
					array(
						'key'           => 'field_case_patient_initial',
						'name'          => 'patient_initial',
						'label'         => 'Patient initial',
						'instructions'  => 'e.g. "Damek F." — first name + last initial only, never a full name.',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_case_patient_context',
						'name'          => 'patient_context',
						'label'         => 'Patient context (optional)',
						'instructions'  => 'A short framing line like "Age 45, recurring back pain over 5 years"',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_case_presentation',
						'name'          => 'presentation',
						'label'         => 'Presentation',
						'instructions'  => 'What the patient came in with — symptoms, history, what they\'ve already tried.',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'rows'          => 6,
					),
					array(
						'key'           => 'field_case_diagnosis',
						'name'          => 'diagnosis',
						'label'         => 'Diagnosis',
						'instructions'  => 'TCM pattern diagnosis and assessment.',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'rows'          => 5,
					),
					array(
						'key'           => 'field_case_treatment',
						'name'          => 'treatment',
						'label'         => 'Treatment',
						'instructions'  => 'Course of treatment — acupuncture, herbal medicine, etc. Include cadence (e.g., weekly for 6 weeks).',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'rows'          => 5,
					),
					array(
						'key'           => 'field_case_result',
						'name'          => 'result',
						'label'         => 'Result',
						'instructions'  => 'Outcome and follow-up. If quoting the patient, use blockquote.',
						'type'          => 'wysiwyg',
						'tabs'          => 'visual',
						'toolbar'       => 'basic',
						'media_upload'  => 0,
						'rows'          => 6,
					),
					array(
						'key'           => 'field_case_duration',
						'name'          => 'duration',
						'label'         => 'Treatment duration (optional)',
						'instructions'  => 'e.g. "6 weeks" or "3 months"',
						'type'          => 'text',
					),
					array(
						'key'           => 'field_case_sessions',
						'name'          => 'sessions',
						'label'         => 'Sessions (optional)',
						'instructions'  => 'Total number of acupuncture sessions in the course of treatment.',
						'type'          => 'number',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'clinic_case',
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
 * Customizer settings for the Clinic Cases archive hero.
 *
 * Lets the editor change eyebrow, title, lede, and upload a hero background
 * image via Appearance → Customize → Clinic Cases archive.
 */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'wellspring_clinic_cases',
			array(
				'title'    => __( 'Clinic Cases archive', 'wellspring' ),
				'priority' => 100,
			)
		);

		$wp_customize->add_setting(
			'clinic_cases_eyebrow',
			array(
				'default'           => 'Real outcomes',
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'clinic_cases_eyebrow',
			array(
				'label'   => __( 'Hero eyebrow', 'wellspring' ),
				'section' => 'wellspring_clinic_cases',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'clinic_cases_title',
			array(
				'default'           => 'Clinic cases',
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'clinic_cases_title',
			array(
				'label'   => __( 'Hero title', 'wellspring' ),
				'section' => 'wellspring_clinic_cases',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'clinic_cases_lede',
			array(
				'default'           => "A curated record of patients we've worked with. Names are shortened to initials for privacy. Use the filters below to browse by focus area or search by symptom.",
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		$wp_customize->add_control(
			'clinic_cases_lede',
			array(
				'label'   => __( 'Hero sub-headline', 'wellspring' ),
				'section' => 'wellspring_clinic_cases',
				'type'    => 'textarea',
			)
		);

		$wp_customize->add_setting(
			'clinic_cases_hero_image',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'clinic_cases_hero_image',
				array(
					'label'       => __( 'Hero background image', 'wellspring' ),
					'description' => __( 'Optional. Leave empty for a clean text-only hero.', 'wellspring' ),
					'section'     => 'wellspring_clinic_cases',
				)
			)
		);
	}
);

/**
 * Flush rewrite rules when the theme is activated (so the new CPT URLs work
 * without the user having to visit Settings → Permalinks).
 */
add_action(
	'after_switch_theme',
	function () {
		flush_rewrite_rules();
	}
);
