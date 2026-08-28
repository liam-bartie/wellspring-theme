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
$hero_btn1_url     = ws_field( 'hero_primary_button_url', WELLSPRING_BOOKING_URL );
$hero_btn2_label   = ws_field( 'hero_secondary_button_label', 'See what we treat' );
$hero_btn2_url     = ws_field( 'hero_secondary_button_url', wellspring_page_url( 'what-we-treat' ) );
$hero_bg           = function_exists( 'get_field' ) ? get_field( 'hero_background_image' ) : null;

$intro_eyebrow     = ws_field( 'intro_eyebrow', '' );
$intro_title       = ws_field( 'intro_title', '' );
$intro_body        = ws_field( 'intro_body', "For over a decade, Dr. Laura Cowburn has helped patients in Calgary move through pain, sleep trouble, hormonal shifts, digestive issues, and the everyday patterns that wear them down. Our practice blends acupuncture, herbal medicine, and old-fashioned, careful listening — and we welcome new patients, with or without a referral. Whatever brought you here, we'd like to help." );

$wwt_eyebrow       = ws_field( 'wwt_eyebrow', 'What we treat' );
$wwt_title         = ws_field( 'wwt_title', 'A wide range of conditions, drawn from thousands of years of practice.' );
$wwt_lede          = ws_field( 'wwt_lede', 'From acute pain to chronic patterns, hormonal cycles to mental clarity — acupuncture and herbal medicine address the body as a whole, not in parts.' );

$pract_eyebrow     = ws_field( 'practitioner_eyebrow', 'Meet your practitioner' );
$pract_name        = ws_field( 'practitioner_name', 'Dr. Laura Cowburn' );
$pract_credentials = ws_field( 'practitioner_credentials', 'Doctor of Traditional Chinese Medicine · Registered Acupuncturist (Alberta)' );
$pract_bio         = ws_field( 'practitioner_bio', 'For more than a decade, Dr. Cowburn has practised in Calgary — drawing on acupuncture, herbal medicine, cupping, and patient counsel to help her clients feel themselves again. Her approach combines classical TCM diagnosis with a modern, evidence-aware lens, and a genuine commitment to time spent listening.' );
$pract_link_label  = ws_field( 'practitioner_link_label', 'Read her full story' );
$pract_link_url    = ws_field( 'practitioner_link_url', wellspring_page_url( 'about' ) );
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
$cta_btn1_url      = ws_field( 'cta_primary_button_url', 'https://lochendclinic.janeapp.com/#/staff_member/13/bio' );
$cta_btn2_label    = ws_field( 'cta_secondary_button_label', 'Call (587) 600-4945' );
$cta_btn2_url      = ws_field( 'cta_secondary_button_url', 'tel:+15876004945' );

// Get the What We Treat page so we can pull its sub-pages for cards.
$wwt_page = get_page_by_path( 'what-we-treat' );

// Card order: use the manual "Tile order" (ACF relationship) if set, otherwise
// pull every What We Treat sub-page automatically in menu order.
$wwt_ordered = ws_field( 'home_wwt_order', array() );
if ( ! empty( $wwt_ordered ) && is_array( $wwt_ordered ) ) {
	$wwt_subpages = array_filter( array_map( 'get_post', $wwt_ordered ) );
} else {
	$wwt_subpages = $wwt_page ? get_children(
		array(
			'post_parent' => $wwt_page->ID,
			'post_type'   => 'page',
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
			'numberposts' => -1,
		)
	) : array();
}

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

	<?php get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'after_hero' ) ); ?>
	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<?php
	get_template_part(
		'template-parts/home/intro',
		null,
		array(
			'eyebrow' => $intro_eyebrow,
			'title'   => $intro_title,
			'body'    => $intro_body,
		)
	);
	?>

	<?php get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'after_intro' ) ); ?>

	<?php
	get_template_part(
		'template-parts/home/what-we-treat',
		null,
		array(
			'eyebrow'  => $wwt_eyebrow,
			'title'    => $wwt_title,
			'lede'     => $wwt_lede,
			'subpages' => $wwt_subpages,
		)
	);
	?>

	<?php get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'after_wwt' ) ); ?>

	<?php
	get_template_part(
		'template-parts/home/practitioner',
		null,
		array(
			'eyebrow'     => $pract_eyebrow,
			'name'        => $pract_name,
			'credentials' => $pract_credentials,
			'bio'         => $pract_bio,
			'link_label'  => $pract_link_label,
			'link_url'    => $pract_link_url,
			'portrait'    => $pract_portrait,
		)
	);
	?>

	<?php get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'after_practitioner' ) ); ?>

	<?php
	get_template_part(
		'template-parts/home/modalities',
		null,
		array(
			'eyebrow'   => $mod_eyebrow,
			'title'     => $mod_title,
			'tcm_title' => $tcm_title,
			'tcm_body'  => $tcm_body,
			'tcm_image' => $tcm_image,
			'acu_title' => $acu_title,
			'acu_body'  => $acu_body,
			'acu_image' => $acu_image,
		)
	);

	get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'after_modalities' ) );

	get_template_part(
		'template-parts/home/featured-cases',
		null,
		array(
			'eyebrow' => $cases_eyebrow,
			'title'   => $cases_title,
			'lede'    => $cases_lede,
			'cases'   => $featured_cases,
		)
	);

	get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'after_cases' ) );

	// Curated Google reviews slider.
	get_template_part( 'template-parts/reviews-slider' );

	get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'after_reviews' ) );

	get_template_part( 'template-parts/flexible-sections', null, array( 'position' => 'before_cta' ) );
	?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php
get_footer();
