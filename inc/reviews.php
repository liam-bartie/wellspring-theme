<?php
/**
 * Patient reviews: storage, defaults, and the editable list.
 *
 * The reviews used to live as a PHP array inside
 * template-parts/reviews-slider.php, so changing a testimonial meant editing a
 * template. They now live in an ACF repeater on Settings > Wellspring, seeded
 * once from that original array, so nothing had to be retyped and nothing was
 * lost.
 *
 * Deliberately NOT fetched from Google. Google's Places API policy states you
 * "must not pre-fetch, cache, or store Places API content" beyond narrow
 * exceptions, so writing API reviews into a repeater is not permissible. These
 * are transcribed by hand, which is also why the API's attribution
 * requirements do not apply to them.
 *
 * No date is stored or displayed, by decision: a relative date makes a
 * testimonial visibly age, and this is a curated set rather than a live feed.
 *
 * @package Wellspring
 */

define( 'WELLSPRING_REVIEWS_SEED_VERSION', '1' );

/**
 * The reviews the theme ships with.
 *
 * Moved verbatim out of template-parts/reviews-slider.php. Seeds the editable
 * repeater, and acts as a fallback if that list is ever emptied so the section
 * can never render blank.
 *
 * @return array
 */
function wellspring_default_reviews() {
	return array(

		array(
			'name'    => 'Kara & Sean Duncan',
			'context' => 'Holistic, attentive care',
			'time'    => '2 weeks ago',
			'rating'  => 5,
			'quote'   => "I can't say enough good things about Dr. Laura Cowburn's care and approach. From the moment I walk in, I feel genuinely listened to and supported. She's incredibly kind, attentive, and thoughtful, and never makes me feel rushed.",
		),
		array(
			'name'    => 'Chris Ferguson',
			'context' => 'Chronic back & nerve pain',
			'time'    => 'a month ago',
			'rating'  => 5,
			'quote'   => "Stephanie Yip is quite amazing! She has helped me manage my chronic pain and long-term healed my back. Before I saw Steph I couldn't sleep from nerve pain and had trouble getting my socks on with extreme back pain.",
		),
		array(
			'name'    => 'Jimmy Bordeos',
			'context' => 'Welcoming clinic',
			'time'    => '8 months ago',
			'rating'  => 5,
			'quote'   => "To begin with, the office staff was excellent. Their friendliness and responsiveness was very welcoming. It definitely makes you want to return again and again.",
		),
		array(
			'name'    => 'Zuzana Jurickova',
			'context' => 'Acupuncture care',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "I would highly recommend Dr. Laura Cowburn to anyone who is looking for a caring, knowledgeable and intuitive acupuncture doctor.",
		),
		array(
			'name'    => 'Geraldine Kopeck',
			'context' => 'Long-term pain relief',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "I have been working with Dr. Laura Cowburn for almost two years. I have improved significantly in all areas, and trust me there is a long list. To live now mostly without pain and major discomfort is a blessing thanks to Dr. Cowburn.",
		),
		array(
			'name'    => 'Jack Wills',
			'context' => 'TCM & acupuncture',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "I am profoundly grateful to Dr. Laura Cowburn for the transformative care she has provided me through Traditional Chinese Medicine and acupuncture. For over five years, I struggled with debilitating health issues.",
		),
		array(
			'name'    => 'George P',
			'context' => 'Insomnia & balance',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "I had a wonderful experience with Dr. Laura Cowburn. After just a few sessions, my insomnia improved significantly, and I felt more balanced overall. The care and attention to detail were exceptional. I highly recommend Dr. Cowburn for anyone seeking effective, holistic treatment.",
		),
		array(
			'name'    => 'Connie Sturgess',
			'context' => 'Neuropathy',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "Dr. Cowburn is my go-to acupuncturist. I came to her originally regarding the neuropathy in my toes. My doctor had given me sleeping pills to override the pins and needles when trying to sleep.",
		),
		array(
			'name'    => 'Camille H',
			'context' => 'Allergies, energy & sleep',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "Dr. Cowburn isn't just a regular acupuncture treatment — she looks at the mind and body holistically and offers treatment beyond regular ailments. My seasonal allergies became non-existent after her treatment plan, and after a few sessions I could see the changes in my energy and sleep.",
		),
		array(
			'name'    => 'Mei Koay',
			'context' => 'Life-changing care',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "If you are searching for a knowledgeable, passionate, and truly caring acupuncturist, I wholeheartedly recommend Dr. Laura Cowburn. She has been life-changing for me, and I am sure she will be for many others, too!",
		),
		array(
			'name'    => 'May Tien',
			'context' => 'Treatment results',
			'time'    => 'a year ago',
			'rating'  => 5,
			'quote'   => "Love the beautiful place! Very impressed by the results from the treatments given by Dr. Laura Cowburn!",
		),
	);
}

/**
 * The reviews to display: the editable list when it has entries, else the
 * theme's own.
 *
 * @return array Each entry: name, context, rating, quote.
 */
function wellspring_reviews() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$rows = function_exists( 'get_field' ) ? get_field( 'reviews', 'option' ) : null;

	if ( empty( $rows ) || ! is_array( $rows ) ) {
		$cache = wellspring_default_reviews();
		return $cache;
	}

	$out = array();
	foreach ( $rows as $row ) {
		if ( empty( $row['quote'] ) ) {
			continue;
		}
		$out[] = array(
			'name'    => $row['name'] ?? '',
			'context' => $row['context'] ?? '',
			'rating'  => empty( $row['rating'] ) ? 5 : (int) $row['rating'],
			'quote'   => $row['quote'],
		);
	}

	$cache = $out ? $out : wellspring_default_reviews();
	return $cache;
}

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
