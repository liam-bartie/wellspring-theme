
/**
 * The editable repeater, on the same Settings > Wellspring page as the badge.
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
	add_action(
		'acf/init',
		function () {
			acf_add_local_field_group(
				array(
					'key'      => 'group_wellspring_reviews',
					'title'    => 'Patient reviews',
					'fields'   => array(
						array(
							'key'     => 'field_ws_reviews_note',
							'label'   => '',
							'type'    => 'message',
							'message' => 'Shown by the &quot;Patient reviews&quot; section wherever it is placed. Dates are deliberately not stored, so reviews never look out of date. Empty this list completely and the theme falls back to its built-in reviews rather than showing nothing.',
						),
						array(
							'key'          => 'field_ws_reviews',
							'name'         => 'reviews',
							'label'        => 'Reviews',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => 'Add review',
							'sub_fields'   => array(
								array(
									'key'   => 'field_ws_rev_quote',
									'name'  => 'quote',
									'label' => 'Review',
									'type'  => 'textarea',
									'rows'  => 4,
								),
								array(
									'key'   => 'field_ws_rev_name',
									'name'  => 'name',
									'label' => 'Name',
									'type'  => 'text',
								),
								array(
									'key'          => 'field_ws_rev_context',
									'name'         => 'context',
									'label'        => 'Short tag',
									'instructions' => 'Optional, shown after the name &mdash; e.g. &quot;Holistic, attentive care&quot;.',
									'type'         => 'text',
								),
								array(
									'key'           => 'field_ws_rev_rating',
									'name'          => 'rating',
									'label'         => 'Stars',
									'type'          => 'number',
									'min'           => 1,
									'max'           => 5,
									'default_value' => 5,
								),
							),
						),
					),
					'location' => array(
						array(
							array(
								'param'    => 'options_page',
								'operator' => '==',
								'value'    => 'wellspring-settings',
							),
						),
					),
					'menu_order' => 10,
					'active'     => true,
				)
			);
		}
	);
}

/**
 * Seed the repeater once from the theme's own reviews, so the existing
 * testimonials become editable without anyone retyping them.
 *
 * Writes only when the repeater is empty, so it can never overwrite an edit —
 * including the case where an editor deliberately deletes every row (the seed
 * flag is already set by then, so they stay deleted).
 */
add_action(
	'admin_init',
	function () {
		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			return;
		}
		if ( get_option( 'wellspring_reviews_seeded' ) === WELLSPRING_REVIEWS_SEED_VERSION ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$existing = get_field( 'reviews', 'option' );

		if ( empty( $existing ) || ! is_array( $existing ) ) {
			$rows = array();
			foreach ( wellspring_default_reviews() as $rev ) {
				$rows[] = array(
					'quote'   => $rev['quote'] ?? '',
					'name'    => $rev['name'] ?? '',
					'context' => $rev['context'] ?? '',
					'rating'  => empty( $rev['rating'] ) ? 5 : (int) $rev['rating'],
				);
			}
			update_field( 'reviews', $rows, 'option' );
			set_transient( 'wellspring_reviews_seeded_notice', count( $rows ), 60 );
		}

		update_option( 'wellspring_reviews_seeded', WELLSPRING_REVIEWS_SEED_VERSION );
	}
);

add_action(
	'admin_notices',
	function () {
		$n = get_transient( 'wellspring_reviews_seeded_notice' );
		if ( false === $n ) {
			return;
		}
		delete_transient( 'wellspring_reviews_seeded_notice' );
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sprintf( '%d patient reviews are now editable under Settings &rsaquo; Wellspring.', $n ) )
		);
	}
);
