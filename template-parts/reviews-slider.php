<?php
/**
 * Curated Google reviews slider.
 *
 * A swipeable carousel of hand-picked Google reviews, styled to match the
 * theme. Used on the home page and a few key pages (see functions.php →
 * ws_reviews_page_slugs). Pulls the rating / count / profile link from the
 * Home page's ACF fields (via ws_home_field) so they're editable in one
 * place and shown site-wide.
 *
 * ─────────────────────────────────────────────────────────────────────────
 *  TO EDIT THE REVIEWS: add, remove, or change entries in the $ws_reviews
 *  array below. Each entry takes:
 *    'name'    => reviewer name,
 *    'context' => short tag shown after the name (optional),
 *    'time'    => relative date like "2 weeks ago" (optional),
 *    'rating'  => stars out of 5 (defaults to 5),
 *    'quote'   => the review text.
 *  The rating number, total review count and Google link live in the
 *  WordPress admin: Pages → Home → Testimonials tab.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * @package Wellspring
 */

$ws_reviews = array(
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

// Nothing to show.
if ( empty( $ws_reviews ) ) {
	return;
}

$testi_eyebrow     = ws_home_field( 'testimonials_eyebrow', 'Patient stories' );
$testi_title       = ws_home_field( 'testimonials_title', 'The work, in their words.' );
$reviews_rating    = ws_home_field( 'reviews_rating', '5.0' );
$reviews_count     = ws_home_field( 'reviews_count', '12' );
$reviews_url       = ws_home_field( 'reviews_url', 'https://maps.app.goo.gl/wXXxB6TmT6yLELhJ9' );
$reviews_cta_label = ws_home_field( 'reviews_cta_label', 'Read all reviews on Google' );
?>

<section class="ws-section ws-section--mist ws-reviews">
	<div class="ws-container">
		<header class="ws-section-header ws-section-header--center">
			<p class="eyebrow"><?php echo esc_html( $testi_eyebrow ); ?></p>
			<h2><?php echo esc_html( $testi_title ); ?></h2>
		</header>

		<?php if ( $reviews_rating ) : ?>
			<?php
			$rating_badge = sprintf(
				'<span class="ws-reviews-summary__brand">%1$s<span>Google</span></span>' .
				'<span class="ws-reviews-summary__score">%2$s</span>' .
				'%3$s' .
				'<span class="ws-reviews-summary__count">%4$s</span>',
				ws_google_g_svg( 18 ),
				esc_html( $reviews_rating ),
				ws_star_rating_html( $reviews_rating ),
				$reviews_count
					? esc_html( sprintf( /* translators: %s: review count */ 'Based on %s Google reviews', $reviews_count ) )
					: esc_html__( 'Rated on Google', 'wellspring' )
			);
			?>
			<?php if ( $reviews_url ) : ?>
				<a class="ws-reviews-summary" href="<?php echo esc_url( $reviews_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo $rating_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static markup; dynamic values escaped above ?>
				</a>
			<?php else : ?>
				<div class="ws-reviews-summary">
					<?php echo $rating_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static markup; dynamic values escaped above ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="ws-slider" data-reviews-slider>
			<button type="button" class="ws-slider__nav ws-slider__nav--prev" aria-label="<?php esc_attr_e( 'Previous reviews', 'wellspring' ); ?>" hidden>
				<span aria-hidden="true">&#8249;</span>
			</button>

			<ul class="ws-slider__track" role="list">
				<?php foreach ( $ws_reviews as $rev ) :
					$rev = wp_parse_args(
						$rev,
						array(
							'name'    => '',
							'context' => '',
							'time'    => '',
							'rating'  => 5,
							'quote'   => '',
						)
					);
					if ( ! $rev['quote'] ) {
						continue;
					}
					?>
					<li class="ws-slider__slide">
						<figure class="ws-quote ws-quote--review">
							<div class="ws-quote__stars"><?php echo ws_star_rating_html( $rev['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper ?></div>
							<blockquote class="ws-quote__body"><?php echo esc_html( $rev['quote'] ); ?></blockquote>
							<figcaption class="ws-quote__attr">
								<span class="ws-quote__name">
									<?php echo esc_html( $rev['name'] ); ?>
									<?php if ( $rev['context'] ) : ?>&middot; <span><?php echo esc_html( $rev['context'] ); ?></span><?php endif; ?>
									<?php if ( $rev['time'] ) : ?><span class="ws-quote__time"><?php echo esc_html( $rev['time'] ); ?></span><?php endif; ?>
								</span>
								<span class="ws-quote__source"><?php echo ws_google_g_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static trusted SVG ?>Google</span>
							</figcaption>
						</figure>
					</li>
				<?php endforeach; ?>
			</ul>

			<button type="button" class="ws-slider__nav ws-slider__nav--next" aria-label="<?php esc_attr_e( 'Next reviews', 'wellspring' ); ?>" hidden>
				<span aria-hidden="true">&#8250;</span>
			</button>

			<div class="ws-slider__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose review', 'wellspring' ); ?>"></div>
		</div>

		<?php if ( $reviews_url && $reviews_cta_label ) : ?>
			<p class="ws-reviews__cta">
				<a class="ws-link-arrow" href="<?php echo esc_url( $reviews_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $reviews_cta_label ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</section>
