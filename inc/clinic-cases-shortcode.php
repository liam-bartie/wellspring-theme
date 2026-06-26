<?php
/**
 * Clinic Cases — related-cases on pages.
 *
 * Surfaces clinic_case cards on any page (primarily the "What We Treat"
 * category pages). The primary control is ACF — an editor toggles "Show
 * related clinic cases" on a page, optionally picks a focus area (or lets it
 * auto-match the page slug), sets how many to show, and the cards render
 * automatically beneath the page content. A [wellspring_cases] shortcode is
 * kept as a fallback for manual placement.
 *
 * @package Wellspring
 */

/**
 * Shared renderer — returns the related-cases section HTML for a focus slug.
 *
 * @param string $focus_slug case_focus term slug.
 * @param int    $limit      Number of cases to show.
 * @param string $heading    Section heading. '' = default, 'none' = hidden.
 * @param string $orderby    rand | date | title.
 * @param bool   $view_all   Show the "view all" link.
 * @return string HTML (empty string if nothing to show).
 */
function wellspring_render_related_cases( $focus_slug, $limit = 3, $heading = '', $orderby = 'rand', $view_all = true ) {
	$focus_slug = sanitize_title( $focus_slug );
	if ( ! $focus_slug ) {
		return '';
	}

	$term = get_term_by( 'slug', $focus_slug, 'case_focus' );
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
					'taxonomy' => 'case_focus',
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
		$heading = sprintf( /* translators: %s: focus area name. */ __( 'Clinic cases — %s', 'wellspring' ), $term->name );
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

		<?php if ( $view_all ) : ?>
			<p class="ws-related-cases__view-all">
				<a class="ws-link-arrow" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
					<?php
					/* translators: %s: focus area name. */
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
						'key'               => 'field_ws_cases_focus',
						'name'              => 'ws_cases_focus',
						'label'             => 'Focus area',
						'instructions'      => 'Which area’s cases to show. “Auto” matches this page’s slug (e.g. a page slugged “pain-relief” shows Pain Relief cases).',
						'type'              => 'select',
						'choices'           => array( 'auto' => 'Auto (match this page’s slug)' ),
						'default_value'     => 'auto',
						'allow_null'        => 0,
						'ui'                => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_ws_show_cases',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
					array(
						'key'               => 'field_ws_cases_heading',
						'name'              => 'ws_cases_heading',
						'label'             => 'Heading',
						'instructions'      => 'Optional. Leave blank for a sensible default, or type “none” to hide the heading.',
						'type'              => 'text',
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_ws_show_cases',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
					array(
						'key'               => 'field_ws_cases_limit',
						'name'              => 'ws_cases_limit',
						'label'             => 'How many to show',
						'type'              => 'number',
						'default_value'     => 3,
						'min'               => 1,
						'max'               => 12,
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_ws_show_cases',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
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
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_ws_show_cases',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
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
 * Populate the focus-area select with the live case_focus terms.
 */
add_filter(
	'acf/load_field/key=field_ws_cases_focus',
	function ( $field ) {
		$field['choices'] = array( 'auto' => 'Auto (match this page’s slug)' );
		$terms            = get_terms(
			array(
				'taxonomy'   => 'case_focus',
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
);

/**
 * Append related cases beneath page content when the ACF toggle is on.
 */
add_filter(
	'the_content',
	function ( $content ) {
		if ( is_admin() || ! function_exists( 'get_field' ) ) {
			return $content;
		}
		if ( ! ( is_singular( 'page' ) && in_the_loop() && is_main_query() ) ) {
			return $content;
		}
		if ( ! get_field( 'ws_show_cases' ) ) {
			return $content;
		}

		$post_id = get_the_ID();
		$focus   = wellspring_resolve_focus_for_page( $post_id, get_field( 'ws_cases_focus' ) );
		$limit   = get_field( 'ws_cases_limit' );
		$heading = (string) get_field( 'ws_cases_heading' );
		$orderby = get_field( 'ws_cases_orderby' );

		$content .= wellspring_render_related_cases(
			$focus,
			$limit ? (int) $limit : 3,
			$heading,
			$orderby ? $orderby : 'rand'
		);

		return $content;
	},
	20
);

/**
 * [wellspring_cases] shortcode — manual fallback for placing cases anywhere.
 *
 *   [wellspring_cases]                       auto-detects focus from page slug
 *   [wellspring_cases focus="respiratory" limit="3" heading="Real outcomes"]
 */
add_shortcode(
	'wellspring_cases',
	function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'focus'    => '',
				'limit'    => 3,
				'heading'  => '',
				'orderby'  => 'rand',
				'view_all' => 'yes',
			),
			$atts,
			'wellspring_cases'
		);

		$focus = $atts['focus'];
		if ( ! $focus ) {
			$obj = get_queried_object();
			if ( $obj instanceof WP_Post ) {
				$focus = $obj->post_name;
			}
		}

		return wellspring_render_related_cases(
			$focus,
			(int) $atts['limit'],
			$atts['heading'],
			$atts['orderby'],
			( 'no' !== strtolower( $atts['view_all'] ) )
		);
	}
);
