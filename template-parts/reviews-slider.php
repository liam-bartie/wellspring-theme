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
 *  TO EDIT THE REVIEWS: Settings → Wellspring → Patient reviews. The list
 *  lives in an ACF repeater (see inc/reviews.php); the theme's original
 *  reviews were seeded into it, and act as a fallback if it is emptied.
 *
 *  No date is stored or shown, deliberately — a relative date makes a
 *  curated testimonial visibly age.
 *
 *  The rating number, total review count and Google link live in the
 *  WordPress admin: Pages → Home → Testimonials tab.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * @package Wellspring
 */

$ws_reviews = wellspring_reviews();

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
