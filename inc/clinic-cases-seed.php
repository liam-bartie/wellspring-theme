<?php
/**
 * Clinic Cases — content seeder.
 *
 * Reads clinic-cases-content.json (the single source of truth for the case
 * library migrated from the old GoDaddy site) and creates a `clinic_case`
 * post for each entry, populating its ACF fields, focus-area term, and —
 * where a source image exists — a sideloaded featured image.
 *
 * The seeder is idempotent: it skips any case whose slug already exists, so
 * it is safe to run repeatedly and will never clobber manual edits or the
 * two hand-built demo cases. Bump WELLSPRING_CASES_SEED_VERSION to allow a
 * fresh pass after adding new entries to the JSON.
 *
 * @package Wellspring
 */

define( 'WELLSPRING_CASES_SEED_VERSION', '2' );

/**
 * Run the seeder once per seed version, on admin load, for capable users.
 */
add_action(
	'admin_init',
	function () {
		// ACF is required — the case narrative lives entirely in ACF fields.
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}

		if ( get_option( 'wellspring_cases_seeded' ) === WELLSPRING_CASES_SEED_VERSION ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$created = wellspring_seed_clinic_cases();

		update_option( 'wellspring_cases_seeded', WELLSPRING_CASES_SEED_VERSION );

		if ( $created > 0 ) {
			set_transient( 'wellspring_cases_seeded_notice', $created, 60 );
		}
	}
);

/**
 * Admin notice confirming how many cases were created.
 */
add_action(
	'admin_notices',
	function () {
		$count = get_transient( 'wellspring_cases_seeded_notice' );
		if ( false === $count ) {
			return;
		}
		delete_transient( 'wellspring_cases_seeded_notice' );
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sprintf( /* translators: %d: number of cases. */ _n( 'Wellspring: seeded %d clinic case from the content library.', 'Wellspring: seeded %d clinic cases from the content library.', $count, 'wellspring' ), $count ) )
		);
	}
);

/**
 * Read the JSON library and create any missing cases.
 *
 * @return int Number of cases created on this pass.
 */
function wellspring_seed_clinic_cases() {
	$path = get_template_directory() . '/clinic-cases-content.json';
	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$data = json_decode( file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( empty( $data['cases'] ) || ! is_array( $data['cases'] ) ) {
		return 0;
	}

	$created = 0;

	foreach ( $data['cases'] as $case ) {
		if ( empty( $case['slug'] ) || empty( $case['title'] ) ) {
			continue;
		}

		$existing = get_posts(
			array(
				'post_type'      => 'clinic_case',
				'name'           => $case['slug'],
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		// Already created on a previous pass — augment with newer fields only,
		// without clobbering any manual edits, then move on.
		if ( ! empty( $existing ) ) {
			$post_id = (int) $existing[0];
			if ( ! empty( $case['summary'] ) && '' === (string) get_field( 'summary', $post_id ) ) {
				update_field( 'field_case_summary', $case['summary'], $post_id );
			}
			wellspring_assign_case_terms( $post_id, 'case_symptom', $case['symptom'] ?? array(), true );
			wellspring_assign_case_terms( $post_id, 'case_modality', $case['modality'] ?? array(), true );
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'clinic_case',
				'post_status'  => 'publish',
				'post_title'   => $case['title'],
				'post_name'    => $case['slug'],
				'post_content' => '',
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		// Taxonomy terms (create any missing terms from a friendly name).
		if ( ! empty( $case['focus'] ) ) {
			wellspring_assign_case_terms( $post_id, 'case_focus', array( $case['focus'] ), false );
		}
		wellspring_assign_case_terms( $post_id, 'case_symptom', $case['symptom'] ?? array(), false );
		wellspring_assign_case_terms( $post_id, 'case_modality', $case['modality'] ?? array(), false );

		// ACF fields (set by field key for reliability on a fresh post).
		update_field( 'field_case_summary', $case['summary'] ?? '', $post_id );
		update_field( 'field_case_patient_initial', $case['initials'] ?? '', $post_id );
		update_field( 'field_case_patient_context', $case['context'] ?? '', $post_id );
		update_field( 'field_case_presentation', wellspring_paragraphs( $case['presentation'] ?? '' ), $post_id );
		update_field( 'field_case_diagnosis', wellspring_paragraphs( $case['diagnosis'] ?? '' ), $post_id );
		update_field( 'field_case_treatment', wellspring_build_treatment( $case ), $post_id );
		update_field( 'field_case_result', wellspring_build_result( $case ), $post_id );

		if ( ! empty( $case['duration'] ) ) {
			update_field( 'field_case_duration', $case['duration'], $post_id );
		}
		if ( ! empty( $case['sessions'] ) ) {
			update_field( 'field_case_sessions', (int) $case['sessions'], $post_id );
		}

		// Featured image — sideload from the source URL where one exists.
		if ( ! empty( $case['source_image'] ) ) {
			wellspring_sideload_featured_image( $case['source_image'], $post_id, $case['source_image_alt'] ?? $case['title'] );
		}

		++$created;
	}

	return $created;
}

/**
 * Convert a double-newline-delimited string into HTML paragraphs.
 *
 * @param string $text Raw text.
 * @return string HTML.
 */
function wellspring_paragraphs( $text ) {
	$text = trim( (string) $text );
	if ( '' === $text ) {
		return '';
	}
	$paras = preg_split( '/\n\s*\n/', $text );
	$out   = '';
	foreach ( $paras as $p ) {
		$p = trim( $p );
		if ( '' !== $p ) {
			$out .= '<p>' . esc_html( $p ) . "</p>\n";
		}
	}
	return $out;
}

/**
 * Build the Treatment field HTML: intro paragraph, point list, outro.
 *
 * @param array $case Case data.
 * @return string HTML.
 */
function wellspring_build_treatment( $case ) {
	$html = '';

	if ( ! empty( $case['treatment_intro'] ) ) {
		$html .= wellspring_paragraphs( $case['treatment_intro'] );
	}

	if ( ! empty( $case['treatment_points'] ) && is_array( $case['treatment_points'] ) ) {
		$html .= "<ul>\n";
		foreach ( $case['treatment_points'] as $point ) {
			if ( is_array( $point ) && count( $point ) >= 2 ) {
				$html .= '<li><strong>' . esc_html( $point[0] ) . '</strong> — ' . esc_html( $point[1] ) . "</li>\n";
			} elseif ( is_string( $point ) ) {
				$html .= '<li>' . esc_html( $point ) . "</li>\n";
			}
		}
		$html .= "</ul>\n";
	}

	if ( ! empty( $case['treatment_outro'] ) ) {
		$html .= wellspring_paragraphs( $case['treatment_outro'] );
	}

	return $html;
}

/**
 * Build the Result field HTML: narrative paragraphs plus optional quote.
 *
 * @param array $case Case data.
 * @return string HTML.
 */
function wellspring_build_result( $case ) {
	$html = wellspring_paragraphs( $case['result'] ?? '' );

	if ( ! empty( $case['quote'] ) ) {
		$html .= '<blockquote><p><em>' . esc_html( $case['quote'] ) . '</em></p></blockquote>' . "\n";
	}

	return $html;
}

/**
 * Sideload a remote image and set it as the post's featured image.
 *
 * @param string $url     Remote image URL.
 * @param int    $post_id Target post.
 * @param string $alt     Alt text.
 */
function wellspring_sideload_featured_image( $url, $post_id, $alt = '' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_sideload_image( $url, $post_id, $alt, 'id' );

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return;
	}

	if ( $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	}

	set_post_thumbnail( $post_id, $attachment_id );
}

/**
 * Assign taxonomy terms to a case by slug, creating any missing terms.
 *
 * @param int    $post_id       Case ID.
 * @param string $taxonomy      Taxonomy name.
 * @param array  $slugs         Term slugs.
 * @param bool   $only_if_empty If true, skip when the case already has terms in
 *                              this taxonomy (so manual edits aren't overwritten).
 */
function wellspring_assign_case_terms( $post_id, $taxonomy, $slugs, $only_if_empty = false ) {
	$slugs = array_values( array_filter( (array) $slugs ) );
	if ( empty( $slugs ) ) {
		return;
	}

	if ( $only_if_empty ) {
		$current = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $current ) && ! empty( $current ) ) {
			return;
		}
	}

	$ids = array();
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( ! $term ) {
			$insert = wp_insert_term( wellspring_facet_name_from_slug( $slug, $taxonomy ), $taxonomy, array( 'slug' => $slug ) );
			if ( ! is_wp_error( $insert ) ) {
				$term = get_term( $insert['term_id'], $taxonomy );
			}
		}
		if ( $term && ! is_wp_error( $term ) ) {
			$ids[] = (int) $term->term_id;
		}
	}

	if ( $ids ) {
		wp_set_object_terms( $post_id, $ids, $taxonomy, false );
	}
}

/**
 * Friendly fallback name for a facet term slug (used only if a term is missing
 * when the seeder runs — normally the taxonomy seeders create them first).
 *
 * @param string $slug     Term slug.
 * @param string $taxonomy Taxonomy name.
 * @return string Name.
 */
function wellspring_facet_name_from_slug( $slug, $taxonomy = 'case_focus' ) {
	$maps = array(
		'case_focus'    => array(
			'pain-relief'              => 'Pain Relief & Injury Recovery',
			'womens-health'            => "Women's Health",
			'mental-health-sleep'      => 'Mental Health & Sleep',
			'digestive-health'         => 'Digestive Health',
			'respiratory'              => 'Respiratory',
			'heart-circulation'        => 'Heart & Circulation',
			'skin-facial-health'       => 'Skin & Facial Health',
			'allergies-immune-support' => 'Allergies & Immune Support',
			'other-conditions'         => 'Other Conditions',
		),
		'case_symptom'  => array(
			'pain'         => 'Pain',
			'headache'     => 'Headache',
			'sleep'        => 'Sleep',
			'inflammation' => 'Inflammation',
			'bleeding'     => 'Bleeding & periods',
			'urinary'      => 'Urinary',
			'breathing'    => 'Breathing & sinus',
			'circulation'  => 'Circulation',
			'mood'         => 'Mood & anxiety',
			'skin'         => 'Skin',
			'fatigue'      => 'Fatigue & energy',
		),
		'case_modality' => array(
			'acupuncture' => 'Acupuncture',
			'herbal'      => 'Herbal medicine',
			'ear-seeds'   => 'Ear seeds',
			'scalp'       => 'Scalp acupuncture',
			'cosmetic'    => 'Cosmetic acupuncture',
			'home-care'   => 'Lifestyle & home care',
		),
	);
	if ( isset( $maps[ $taxonomy ][ $slug ] ) ) {
		return $maps[ $taxonomy ][ $slug ];
	}
	return ucwords( str_replace( '-', ' ', $slug ) );
}

/**
 * WP-CLI command: `wp wellspring seed-cases [--force]`.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'wellspring seed-cases',
		function ( $args, $assoc_args ) {
			if ( ! empty( $assoc_args['force'] ) ) {
				delete_option( 'wellspring_cases_seeded' );
			}
			$created = wellspring_seed_clinic_cases();
			update_option( 'wellspring_cases_seeded', WELLSPRING_CASES_SEED_VERSION );
			WP_CLI::success( sprintf( 'Seeded %d clinic case(s).', $created ) );
		}
	);
}
