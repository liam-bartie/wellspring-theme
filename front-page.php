<?php
/**
 * The home page template.
 *
 * Used automatically by WordPress as the front page when set in
 * Settings → Reading → "Your homepage displays" → A static page.
 *
 * Editable from WP admin → Pages → Home (requires ACF plugin).
 *
 * @package Wellspring
 */

get_header();

// Pull ACF values with sensible defaults.
$hero_eyebrow      = ws_field( 'hero_eyebrow', 'Calgary &middot; Inglewood' );
$hero_title        = ws_field( 'hero_title', 'Calm, considered care for body and mind.' );
$hero_lede         = ws_field( 'hero_lede', "Acupuncture and Traditional Chinese Medicine for pain relief, women's health, sleep, digestion, and beyond — practised by Dr. Laura Cowburn for over a decade." );
$hero_btn1_label   = ws_field( 'hero_primary_button_label', 'Book an appointment' );
$hero_btn1_url     = ws_field( 'hero_primary_button_url', '/book-appointments/' );
$hero_btn2_label   = ws_field( 'hero_secondary_button_label', 'See what we treat' );
$hero_btn2_url     = ws_field( 'hero_secondary_button_url', '/what-we-treat/' );
$hero_bg           = function_exists( 'get_field' ) ? get_field( 'hero_background_image' ) : null;

$wwt_eyebrow       = ws_field( 'wwt_eyebrow', 'What we treat' );
$wwt_title         = ws_field( 'wwt_title', 'Six areas of focus, drawn from thousands of years of practice.' );
$wwt_lede          = ws_field( 'wwt_lede', 'From acute pain to chronic patterns, hormonal cycles to mental clarity — acupuncture and herbal medicine address the body as a whole, not in parts.' );

$pract_eyebrow     = ws_field( 'practitioner_eyebrow', 'Meet your practitioner' );
$pract_name        = ws_field( 'practitioner_name', 'Dr. Laura Cowburn' );
$pract_credentials = ws_field( 'practitioner_credentials', 'Doctor of Traditional Chinese Medicine · Registered Acupuncturist (Alberta)' );
$pract_bio         = ws_field( 'practitioner_bio', 'For more than a decade, Dr. Cowburn has practised in Calgary — drawing on acupuncture, herbal medicine, cupping, and patient counsel to help her clients feel themselves again. Her approach combines classical TCM diagnosis with a modern, evidence-aware lens, and a genuine commitment to time spent listening.' );
$pract_link_label  = ws_field( 'practitioner_link_label', 'Read her full story' );
$pract_link_url    = ws_field( 'practitioner_link_url', '/about/' );
$pract_portrait    = function_exists( 'get_field' ) ? get_field( 'practitioner_portrait' ) : null;

$testi_eyebrow     = ws_field( 'testimonials_eyebrow', 'Patient stories' );
$testi_title       = ws_field( 'testimonials_title', 'The work, in their words.' );

$cta_title         = ws_field( 'cta_title', 'Ready when you are.' );
$cta_lede          = ws_field( 'cta_lede', 'New patients welcome. Appointments typically available within the week. Direct billing to most major insurers.' );
$cta_btn1_label    = ws_field( 'cta_primary_button_label', 'Book an appointment' );
$cta_btn1_url      = ws_field( 'cta_primary_button_url', '/book-appointments/' );
$cta_btn2_label    = ws_field( 'cta_secondary_button_label', 'Call (587) 600-4945' );
$cta_btn2_url      = ws_field( 'cta_secondary_button_url', 'tel:+15876004945' );

// Get the What We Treat page so we can pull its sub-pages for cards.
$wwt_page    = get_page_by_path( 'what-we-treat' );
$wwt_subpages = $wwt_page ? get_children(
	array(
		'post_parent' => $wwt_page->ID,
		'post_type'   => 'page',
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
		'numberposts' => -1,
	)
) : array();

// Hero with optional bg image — different class for styling.
$hero_class = $hero_bg ? 'ws-hero ws-hero--imaged' : 'ws-hero';
?>

<main id="primary" class="site-main">

	<section class="<?php echo esc_attr( $hero_class ); ?>">
		<?php if ( $hero_bg && ! empty( $hero_bg['url'] ) ) : ?>
			<div class="ws-hero__bg" style="background-image: url('<?php echo esc_url( $hero_bg['url'] ); ?>');" aria-hidden="true"></div>
			<div class="ws-hero__overlay" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="ws-container ws-container--narrow ws-hero__content">
			<p class="eyebrow"><?php echo wp_kses_post( $hero_eyebrow ); ?></p>
			<h1 class="ws-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
			<p class="ws-hero__lede"><?php echo esc_html( $hero_lede ); ?></p>
			<div class="ws-hero__actions">
				<?php if ( $hero_btn1_label ) : ?>
					<a href="<?php echo esc_url( $hero_btn1_url ); ?>" class="ws-btn"><?php echo esc_html( $hero_btn1_label ); ?></a>
				<?php endif; ?>
				<?php if ( $hero_btn2_label ) : ?>
					<a href="<?php echo esc_url( $hero_btn2_url ); ?>" class="ws-btn ws-btn--ghost"><?php echo esc_html( $hero_btn2_label ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="ws-section ws-section--mist">
		<div class="ws-container">
			<header class="ws-section-header">
				<p class="eyebrow"><?php echo esc_html( $wwt_eyebrow ); ?></p>
				<h2><?php echo esc_html( $wwt_title ); ?></h2>
				<p class="ws-section-header__lede"><?php echo esc_html( $wwt_lede ); ?></p>
			</header>

			<div class="ws-cards">
				<?php
				if ( ! empty( $wwt_subpages ) ) :
					foreach ( $wwt_subpages as $sub ) :
						$thumb_url = get_the_post_thumbnail_url( $sub->ID, 'wellspring-card' );
						$excerpt   = $sub->post_excerpt;
						if ( ! $excerpt ) {
							// Auto-generate excerpt from content if none set.
							$excerpt = wp_trim_words( wp_strip_all_tags( $sub->post_content ), 22, '…' );
						}
						$card_class = $thumb_url ? 'ws-card ws-card--imaged' : 'ws-card';
						?>
						<a class="<?php echo esc_attr( $card_class ); ?>" href="<?php echo esc_url( get_permalink( $sub->ID ) ); ?>">
							<?php if ( $thumb_url ) : ?>
								<div class="ws-card__image" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');" aria-hidden="true"></div>
							<?php endif; ?>
							<div class="ws-card__body-wrap">
								<h3 class="ws-card__title"><?php echo esc_html( $sub->post_title ); ?></h3>
								<p class="ws-card__body"><?php echo esc_html( $excerpt ); ?></p>
								<span class="ws-card__cta">Learn more</span>
							</div>
						</a>
						<?php
					endforeach;
				endif;
				?>
			</div>
		</div>
	</section>

	<section class="ws-section ws-practitioner">
		<div class="ws-container">
			<div class="ws-practitioner__inner">
				<div class="ws-practitioner__portrait">
					<?php if ( $pract_portrait && ! empty( $pract_portrait['url'] ) ) : ?>
						<img src="<?php echo esc_url( $pract_portrait['sizes']['wellspring-portrait'] ?? $pract_portrait['url'] ); ?>" alt="<?php echo esc_attr( $pract_portrait['alt'] ?: $pract_name ); ?>" />
					<?php else : ?>
						<span class="ws-practitioner__monogram" aria-hidden="true">LC</span>
					<?php endif; ?>
				</div>
				<div class="ws-practitioner__body">
					<p class="eyebrow"><?php echo esc_html( $pract_eyebrow ); ?></p>
					<h2><?php echo esc_html( $pract_name ); ?></h2>
					<p class="ws-practitioner__credential"><?php echo esc_html( $pract_credentials ); ?></p>
					<div class="ws-practitioner__bio"><?php echo wp_kses_post( wpautop( $pract_bio ) ); ?></div>
					<?php if ( $pract_link_label ) : ?>
						<p><a href="<?php echo esc_url( $pract_link_url ); ?>" class="ws-link-arrow"><?php echo esc_html( $pract_link_label ); ?></a></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php
	// Only show testimonials section if at least one testimonial has a quote.
	$testi_1_quote = ws_field( 'testimonial_1_quote' );
	$testi_2_quote = ws_field( 'testimonial_2_quote' );
	$testi_3_quote = ws_field( 'testimonial_3_quote' );
	$any_testi     = $testi_1_quote || $testi_2_quote || $testi_3_quote;

	if ( $any_testi ) :
		?>
		<section class="ws-section ws-section--mist">
			<div class="ws-container ws-container--narrow">
				<header class="ws-section-header ws-section-header--center">
					<p class="eyebrow"><?php echo esc_html( $testi_eyebrow ); ?></p>
					<h2><?php echo esc_html( $testi_title ); ?></h2>
				</header>

				<div class="ws-quotes">
					<?php for ( $i = 1; $i <= 3; $i++ ) :
						$q = ws_field( 'testimonial_' . $i . '_quote' );
						$a = ws_field( 'testimonial_' . $i . '_author' );
						$c = ws_field( 'testimonial_' . $i . '_context' );
						if ( ! $q ) {
							continue;
						}
						?>
						<figure class="ws-quote">
							<blockquote class="ws-quote__body"><?php echo esc_html( $q ); ?></blockquote>
							<figcaption class="ws-quote__attr">
								<?php echo esc_html( $a ); ?>
								<?php if ( $c ) : ?>&middot; <span><?php echo esc_html( $c ); ?></span><?php endif; ?>
							</figcaption>
						</figure>
						<?php
					endfor; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php
get_footer();
