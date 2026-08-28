<?php
/**
 * Clinic Cases — related-cases on pages.
 *
 * Surfaces clinic_case cards on any page (primarily the "What We Treat"
 * category pages). The primary control is ACF — an editor toggles "Show
 * related clinic cases" on a page, chooses which facet to filter by (focus
 * area, symptom, or treatment used), picks a term (or lets a focus area
 * auto-match the page slug), sets how many to show, and the cards render
 * automatically beneath the page content. A [wellspring_cases] shortcode is
 * kept as a fallback for manual placement.
 *
 * @package Wellspring
 */

/**
 * The case taxonomies an editor can filter related cases by.
 *
 * Keys are taxonomy names; values are the admin-facing labels used in the
 * "Filter by" select. Keep this in sync with the taxonomies registered in
 * inc/clinic-cases.php.
 *
 * @return array<string,string>
 */
function wellspring_case_taxonomies() {
	return array(
		'case_focus'    => __( 'Focus area', 'wellspring' ),
		'case_symptom'  => __( 'Symptom', 'wellspring' ),
		'case_modality' => __( 'Treatment used', 'wellspring' ),
	);
}

/**
 * Normalise a taxonomy name, falling back to case_focus.
 *
 * Also accepts the friendly aliases used by the shortcode ("symptom",
 * "treatment", etc.) so editors don't have to type internal names.
 *
 * @param string $taxonomy Raw taxonomy name or alias.
 * @return string A taxonomy name known to wellspring_case_taxonomies().
 */
function wellspring_normalize_case_taxonomy( $taxonomy ) {
	$taxonomy = strtolower( trim( (string) $taxonomy ) );

	$aliases = array(
		''           => 'case_focus',
		'focus'      => 'case_focus',
		'focus_area' => 'case_focus',
		'symptom'    => 'case_symptom',
		'symptoms'   => 'case_symptom',
		'treatment'  => 'case_modality',
		'treatments' => 'case_modality',
		'modality'   => 'case_modality',
	);

	if ( isset( $aliases[ $taxonomy ] ) ) {
		$taxonomy = $aliases[ $taxonomy ];
	}

	return array_key_exists( $taxonomy, wellspring_case_taxonomies() ) ? $taxonomy : 'case_focus';
}

/**
 * Shared renderer — returns the related-cases section HTML for a term.
 *
 * $taxonomy is the last parameter so existing calls that pass only a focus
 * slug keep working unchanged.
 *
 * @param string $term_slug Term slug within $taxonomy.
 * @param int    $limit     Number of cases to show.
 * @param string $heading   Section heading. '' = default, 'none' = hidden.
 * @param string $orderby   rand | date | title.
 * @param bool   $view_all  Show the "view all" link.
 * @param string $taxonomy  case_focus | case_symptom | case_modality.
 * @return string HTML (empty string if nothing to show).
 */
function wellspring_render_related_cases( $term_slug, $limit = 3, $heading = '', $orderby = 'rand', $view_all = true, $taxonomy = 'case_focus' ) {
	$term_slug = sanitize_title( $term_slug );
	if ( ! $term_slug ) {
		return '';
	}

	$taxonomy = wellspring_normalize_case_taxonomy( $taxonomy );

	$term = get_term_by( 'slug', $term_slug, $taxonomy );
	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}

	$orderby = in_array( $orderby, array( 'rand', 'date', 'title' ), true ) ? $orderby : 'rand';

	$query = new WP_Query(
		array(
			'post_type'      => 'clinic_case',
			'posts_per_page' => max( 1, (int) $limit ),
			'orderby'        => $orderby,
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	if ( '' === $heading ) {
		$heading = sprintf( /* translators: %s: term name (focus area, symptom, or treatment). */ __( 'Clinic cases — %s', 'wellspring' ), $term->name );
	}
	$show_heading = ( 'none' !== strtolower( (string) $heading ) );

	ob_start();
	?>
	<section class="ws-related-cases">
		<?php if ( $show_heading ) : ?>
			<header class="ws-related-cases__head">
				<p class="eyebrow"><?php esc_html_e( 'Real outcomes', 'wellspring' ); ?></p>
				<h2 class="ws-related-cases__title"><?php echo esc_html( $heading ); ?></h2>
			</header>
		<?php endif; ?>

		<div class="ws-cases-grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				global $post;
				get_template_part( 'template-parts/case-card', null, array( 'case' => $post ) );
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<?php
		$term_link = get_term_link( $term );
		if ( $view_all && ! is_wp_error( $term_link ) ) :
			?>
			<p class="ws-related-cases__view-all">
				<a class="ws-link-arrow" href="<?php echo esc_url( $term_link ); ?>">
					<?php
					/* translators: %s: term name (focus area, symptom, or treatment). */
					echo esc_html( sprintf( __( 'View all %s cases', 'wellspring' ), $term->name ) );
					?>
					<span aria-hidden="true"> →</span>
				</a>
			</p>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Resolve the focus slug for a page: explicit ACF value, "auto" (page slug),
 * or empty.
 *
 * Only meaningful for case_focus — the What We Treat page slugs mirror the
 * focus-area slugs by design. There is no equivalent convention for symptoms
 * or treatments, so those facets require an explicit term.
 *
 * @param int    $post_id   Page ID.
 * @param string $acf_value Stored ACF focus value.
 * @return string Slug.
 */
function wellspring_resolve_focus_for_page( $post_id, $acf_value ) {
	if ( $acf_value && 'auto' !== $acf_value ) {
		return $acf_value;
	}
	return get_post_field( 'post_name', $post_id );
}

/**
 * Shared conditional-logic rule: only show when the toggle is on, and (when
 * $taxonomy is given) only for that "Filter by" choice.
 *
 * @param string $taxonomy Optional taxonomy to gate on.
 * @return array ACF conditional_logic structure.
 */
function wellspring_cases_conditional( $taxonomy = '' ) {
	$rules = array(
		array(
			'field'    => 'field_ws_show_cases',
			'operator' => '==',
			'value'    => '1',
		),
	);

	if ( $taxonomy ) {
		$rules[] = array(
			'field'    => 'field_ws_cases_taxonomy',
			'operator' => '==',
			'value'    => $taxonomy,
		);
	}

	return array( $rules );
}

/**
 * ACF field group: per-page related-cases controls.
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_wellspring_related_cases',
				'title'    => 'Related clinic cases',
				'fields'   => array(
					array(
						'key'           => 'field_ws_show_cases',
						'name'          => 'ws_show_cases',
						'label'         => 'Show related clinic cases',
						'instructions'  => 'Display a grid of matching clinic cases beneath this page’s content.',
						'type'          => 'true_false',
						'ui'            => 1,
						'default_value' => 0,
					),
					array(
						'key'               => 'field_ws_cases_taxonomy',
						'name'              => 'ws_cases_taxonomy',
						'label'             => 'Filter by',
						'instructions'      => 'Which facet to match cases on. Pick the facet first, then choose a value below.',
						'type'              => 'select',
						'choices'           => array(
							'case_focus'    => 'Focus area',
							'case_symptom'  => 'Symptom',
							'case_modality' => 'Treatment used',
						),
						'default_value'     => 'case_focus',
						'allow_null'        => 0,
						'ui'                => 1,
						'conditional_logic' => wellspring_cases_conditional(),
					),
					array(
						'key'               => 'field_ws_cases_focus',
						'name'              => 'ws_cases_focus',
						'label'             => 'Focus area',
						'instructions'      => 'Which area’s cases to show. “Auto” matches this page’s slug (e.g. a page slugged “pain-relief” shows Pain Relief cases).',
						'type'              => 'select',
						'choices'           => array( 'auto' => 'Auto (match this page’s slug)' ),
						'default_value'     => 'auto',
						'allow_null'        => 0,
						'ui'                => 1,
						'conditional_logic' => wellspring_cases_conditional( 'case_focus' ),
					),
					array(
						'key'               => 'field_ws_cases_symptom',
						'name'              => 'ws_cases_symptom',
						'label'             => 'Symptom',
						'instructions'      => 'Show cases tagged with this symptom. Manage the list under Clinic cases → Symptoms.',
						'type'              => 'select',
						'choices'           => array(),
						'allow_null'        => 1,
						'ui'                => 1,
						'conditional_logic' => wellspring_cases_conditional( 'case_symptom' ),
					),
					array(
						'key'               => 'field_ws_cases_modality',
						'name'              => 'ws_cases_modality',
						'label'             => 'Treatment used',
						'instructions'      => 'Show cases that used this treatment. Manage the list under Clinic cases → Treatments used.',
						'type'              => 'select',
						'choices'           => array(),
						'allow_null'        => 1,
						'ui'                => 1,
						'conditional_logic' => wellspring_cases_conditional( 'case_modality' ),
					),
					array(
						'key'               => 'field_ws_cases_heading',
						'name'              => 'ws_cases_heading',
						'label'             => 'Heading',
						'instructions'      => 'Optional. Leave blank for a sensible default, or type “none” to hide the heading.',
						'type'              => 'text',
						'conditional_logic' => wellspring_cases_conditional(),
					),
					array(
						'key'               => 'field_ws_cases_limit',
						'name'              => 'ws_cases_limit',
						'label'             => 'How many to show',
						'type'              => 'number',
						'default_value'     => 3,
						'min'               => 1,
						'max'               => 12,
						'conditional_logic' => wellspring_cases_conditional(),
					),
					array(
						'key'               => 'field_ws_cases_orderby',
						'name'              => 'ws_cases_orderby',
						'label'             => 'Order',
						'type'              => 'select',
						'choices'           => array(
							'rand'  => 'Random (feels fresh each visit)',
							'date'  => 'Newest first',
							'title' => 'A–Z by title',
						),
						'default_value'     => 'rand',
						'conditional_logic' => wellspring_cases_conditional(),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'page',
						),
					),
				),
				'menu_order'      => 5,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
			)
		);
	}
);

/**
 * Populate a term select with the live terms for one taxonomy.
 *
 * @param array  $field    ACF field array.
 * @param string $taxonomy Taxonomy to read terms from.
 * @param array  $prefix   Choices to place above the terms (e.g. the "auto" option).
 * @return array Modified field.
 */
function wellspring_load_case_term_choices( $field, $taxonomy, $prefix = array() ) {
	$field['choices'] = $prefix;

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$field['choices'][ $term->slug ] = $term->name;
		}
	}

	return $field;
}

add_filter(
	'acf/load_field/key=field_ws_cases_focus',
	function ( $field ) {
		return wellspring_load_case_term_choices(
			$field,
			'case_focus',
			array( 'auto' => 'Auto (match this page’s slug)' )
		);
	}
);

add_filter(
	'acf/load_field/key=field_ws_cases_symptom',
	function ( $field ) {
		return wellspring_load_case_term_choices( $field, 'case_symptom' );
	}
);

add_filter(
	'acf/load_field/key=field_ws_cases_modality',
	function ( $field ) {
		return wellspring_load_case_term_choices( $field, 'case_modality' );
	}
);

/*
 * Same three pickers again, for the "Clinic cases" section layout in the page
 * builder. Separate field keys because ACF keys must be unique, but the same
 * loader, so the choices can never drift between the two places.
 */
add_filter(
	'acf/load_field/key=field_sec_cases_focus',
	function ( $field ) {
		return wellspring_load_case_term_choices(
			$field,
			'case_focus',
			array( 'auto' => 'Auto (match this page’s slug)' )
		);
	}
);

add_filter(
	'acf/load_field/key=field_sec_cases_symptom',
	function ( $field ) {
		return wellspring_load_case_term_choices( $field, 'case_symptom' );
	}
);

add_filter(
	'acf/load_field/key=field_sec_cases_modality',
	function ( $field ) {
		return wellspring_load_case_term_choices( $field, 'case_modality' );
	}
);

/**
 * Return the related-cases section for the current page when the ACF toggle is
 * on. Rendered by page.php in a full-width container so the grid matches the
 * homepage, rather than appended inside the narrow content column.
 *
 * @return string Section markup, or '' when there is nothing to show.
 */
function wellspring_page_related_cases() {
	if ( ! function_exists( 'get_field' ) || ! is_singular( 'page' ) ) {
		return '';
	}
	if ( ! get_field( 'ws_show_cases' ) ) {
		return '';
	}

	$post_id  = get_the_ID();
	$taxonomy = wellspring_normalize_case_taxonomy( (string) get_field( 'ws_cases_taxonomy' ) );

	switch ( $taxonomy ) {
		case 'case_symptom':
			$term = (string) get_field( 'ws_cases_symptom' );
			break;
		case 'case_modality':
			$term = (string) get_field( 'ws_cases_modality' );
			break;
		default:
			// Focus areas support "auto", which falls back to the page slug.
			$term = wellspring_resolve_focus_for_page( $post_id, get_field( 'ws_cases_focus' ) );
			break;
	}

	if ( ! $term ) {
		return '';
	}

	$limit   = get_field( 'ws_cases_limit' );
	$heading = (string) get_field( 'ws_cases_heading' );
	$orderby = get_field( 'ws_cases_orderby' );

	return wellspring_render_related_cases(
		$term,
		$limit ? (int) $limit : 3,
		$heading,
		$orderby ? $orderby : 'rand',
		true,
		$taxonomy
	);
}

/**
 * [wellspring_cases] shortcode — manual fallback for placing cases anywhere.
 *
 *   [wellspring_cases]                              auto-detects focus from page slug
 *   [wellspring_cases focus="respiratory" limit="3" heading="Real outcomes"]
 *   [wellspring_cases taxonomy="symptom" term="insomnia"]
 *   [wellspring_cases taxonomy="treatment" term="cupping" limit="4"]
 */
add_shortcode(
	'wellspring_cases',
	function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'taxonomy' => 'case_focus',
				'term'     => '',
				'focus'    => '', // Legacy alias for term, kept for existing shortcodes.
				'limit'    => 3,
				'heading'  => '',
				'orderby'  => 'rand',
				'view_all' => 'yes',
			),
			$atts,
			'wellspring_cases'
		);

		$taxonomy = wellspring_normalize_case_taxonomy( $atts['taxonomy'] );
		$term     = $atts['term'] ? $atts['term'] : $atts['focus'];

		// Only focus areas auto-detect from the current page slug.
		if ( ! $term && 'case_focus' === $taxonomy ) {
			$obj = get_queried_object();
			if ( $obj instanceof WP_Post ) {
				$term = $obj->post_name;
			}
		}

		return wellspring_render_related_cases(
			$term,
			(int) $atts['limit'],
			$atts['heading'],
			$atts['orderby'],
			( 'no' !== strtolower( $atts['view_all'] ) ),
			$taxonomy
		);
	}
);
