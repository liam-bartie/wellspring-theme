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

$intro_eyebrow     = ws_field( 'intro_eyebrow', '' );
$intro_title       = ws_field( 'intro_title', '' );
$intro_body        = ws_field( 'intro_body', "For over a decade, Dr. Laura Cowburn has helped patients in Calgary move through pain, sleep trouble, hormonal shifts, digestive issues, and the everyday patterns that wear them down. Our practice blends acupuncture, herbal medicine, and old-fashioned, careful listening — and we welcome new patients, with or without a referral. Whatever brought you here, we'd like to help." );

$wwt_eyebrow       = ws_field( 'wwt_eyebrow', 'What we treat' );
$wwt_title         = ws_field( 'wwt_title', 'Nine areas of focus, drawn from thousands of years of practice.' );
$wwt_lede          = ws_field( 'wwt_lede', 'From acute pain to chronic patterns, hormonal cycles to mental clarity — acupuncture and herbal medicine address the body as a whole, not in parts.' );

$pract_eyebrow     = ws_field( 'practitioner_eyebrow', 'Meet your practitioner' );
$pract_name        = ws_field( 'practitioner_name', 'Dr. Laura Cowburn' );
$pract_credentials = ws_field( 'practitioner_credentials', 'Doctor of Traditional Chinese Medicine · Registered Acupuncturist (Alberta)' );
$pract_bio         = ws_field( 'practitioner_bio', 'For more than a decade, Dr. Cowburn has practised in Calgary — drawing on acupuncture, herbal medicine, cupping, and patient counsel to help her clients feel themselves again. Her approach combines classical TCM diagnosis with a modern, evidence-aware lens, and a genuine commitment to time spent listening.' );
$pract_link_label  = ws_field( 'practitioner_link_label', 'Read her full story' );
$pract_link_url    = ws_field( 'practitioner_link_url', '/about/' );
$pract_portrait    = function_exists( 'get_field' ) ? get_field( 'practitioner_portrait' ) : null;

$mod_eyebrow       = ws_field( 'modalities_eyebrow', 'Our practice' );
$mod_title         = ws_field( 'modalities_title', 'Two ancient modalities, applied with modern care.' );
$tcm_image         = function_exists( 'get_field' ) ? get_field( 'tcm_image' ) : null;
$tcm_title         = ws_field( 'tcm_title', 'What is Traditional Chinese Medicine (TCM)?' );
$tcm_body          = ws_field( 'tcm_body', '' );
$acu_image         = function_exists( 'get_field' ) ? get_field( 'acupuncture_image' ) : null;
$acu_title         = ws_field( 'acupuncture_title', 'What is Acupuncture?' );
$acu_body          = ws_field( 'acupuncture_body', '' );

$cases_eyebrow     = ws_field( 'cases_eyebrow', 'Cases from the clinic' );
$cases_title       = ws_field( 'cases_title', 'Real patients, real outcomes.' );
$cases_lede        = ws_field( 'cases_lede', '' );
$cases_featured_ids = function_exists( 'get_field' ) ? get_field( 'cases_featured' ) : array();

if ( ! empty( $cases_featured_ids ) && is_array( $cases_featured_ids ) ) {
	$featured_cases = get_posts(
		array(
			'post_type'      => 'clinic_case',
			'post__in'       => $cases_featured_ids,
			'orderby'        => 'post__in',
			'posts_per_page' => 3,
		)
	);
} else {
	$featured_cases = get_posts(
		array(
			'post_type'      => 'clinic_case',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}


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
			<div class="ws-hero__lede"><?php echo wp_kses_post( $hero_lede ); ?></div>
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

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<?php if ( $intro_eyebrow || $intro_title || $intro_body ) : ?>
		<section class="ws-section ws-intro-text">
			<div class="ws-container ws-container--narrow">
				<?php if ( $intro_eyebrow ) : ?>
					<p class="eyebrow"><?php echo esc_html( $intro_eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $intro_title ) : ?>
					<h2 class="ws-intro-text__title"><?php echo esc_html( $intro_title ); ?></h2>
				<?php endif; ?>
				<?php if ( $intro_body ) : ?>
					<div class="ws-intro-text__body"><?php echo wp_kses_post( $intro_body ); ?></div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="ws-section ws-section--mist">
		<div class="ws-container">
			<header class="ws-section-header">
				<p class="eyebrow"><?php echo esc_html( $wwt_eyebrow ); ?></p>
				<h2><?php echo esc_html( $wwt_title ); ?></h2>
				<div class="ws-section-header__lede"><?php echo wp_kses_post( $wwt_lede ); ?></div>
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
	// Modalities section — only render if either block has a heading.
	if ( $tcm_title || $acu_title ) :
		?>
		<section class="ws-section ws-modalities">
			<div class="ws-container">
				<?php if ( $mod_eyebrow || $mod_title ) : ?>
					<header class="ws-section-header ws-section-header--center">
						<?php if ( $mod_eyebrow ) : ?>
							<p class="eyebrow"><?php echo esc_html( $mod_eyebrow ); ?></p>
						<?php endif; ?>
						<?php if ( $mod_title ) : ?>
							<h2><?php echo esc_html( $mod_title ); ?></h2>
						<?php endif; ?>
					</header>
				<?php endif; ?>

				<div class="ws-modalities__grid">
					<?php if ( $tcm_title || $tcm_body || $tcm_image ) : ?>
						<article class="ws-modality">
							<?php if ( $tcm_image && ! empty( $tcm_image['url'] ) ) : ?>
								<div class="ws-modality__image" style="background-image: url('<?php echo esc_url( $tcm_image['sizes']['wellspring-card'] ?? $tcm_image['url'] ); ?>');" role="img" aria-label="<?php echo esc_attr( $tcm_image['alt'] ?: $tcm_title ); ?>"></div>
							<?php endif; ?>
							<?php if ( $tcm_title ) : ?>
								<h3 class="ws-modality__title"><?php echo esc_html( $tcm_title ); ?></h3>
							<?php endif; ?>
							<?php if ( $tcm_body ) : ?>
								<div class="ws-modality__body"><?php echo wp_kses_post( wpautop( $tcm_body ) ); ?></div>
							<?php endif; ?>
						</article>
					<?php endif; ?>

					<?php if ( $acu_title || $acu_body || $acu_image ) : ?>
						<article class="ws-modality">
							<?php if ( $acu_image && ! empty( $acu_image['url'] ) ) : ?>
								<div class="ws-modality__image" style="background-image: url('<?php echo esc_url( $acu_image['sizes']['wellspring-card'] ?? $acu_image['url'] ); ?>');" role="img" aria-label="<?php echo esc_attr( $acu_image['alt'] ?: $acu_title ); ?>"></div>
							<?php endif; ?>
							<?php if ( $acu_title ) : ?>
								<h3 class="ws-modality__title"><?php echo esc_html( $acu_title ); ?></h3>
							<?php endif; ?>
							<?php if ( $acu_body ) : ?>
								<div class="ws-modality__body"><?php echo wp_kses_post( wpautop( $acu_body ) ); ?></div>
							<?php endif; ?>
						</article>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	endif;

	// Featured cases — only render if there's at least one published case.
	if ( ! empty( $featured_cases ) ) :
		?>
		<section class="ws-section ws-home-cases">
			<div class="ws-container">
				<header class="ws-section-header ws-section-header--center">
					<?php if ( $cases_eyebrow ) : ?>
						<p class="eyebrow"><?php echo esc_html( $cases_eyebrow ); ?></p>
					<?php endif; ?>
					<?php if ( $cases_title ) : ?>
						<h2><?php echo esc_html( $cases_title ); ?></h2>
					<?php endif; ?>
					<?php if ( $cases_lede ) : ?>
						<div class="ws-section-header__lede"><?php echo wp_kses_post( $cases_lede ); ?></div>
					<?php endif; ?>
				</header>

				<div class="ws-cases-grid">
					<?php foreach ( $featured_cases as $case ) {
						get_template_part( 'template-parts/case-card', null, array( 'case' => $case ) );
					} ?>
				</div>

				<p class="ws-home-cases__view-all">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>" class="ws-link-arrow">View all cases</a>
				</p>
			</div>
		</section>
		<?php
	endif;

	// Curated Google reviews slider.
	get_template_part( 'template-parts/reviews-slider' );
	?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php
get_footer();
