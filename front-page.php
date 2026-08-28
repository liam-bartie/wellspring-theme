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

// Resolved (with its long-standing fallback) in inc/home-blocks.php so the
// section layout produces the identical list.
$featured_cases = wellspring_home_featured_cases();


$cta_title         = ws_field( 'cta_title', 'Ready when you are.' );
$cta_lede          = ws_field( 'cta_lede', 'New patients welcome. Appointments typically available within the week. Direct billing to most major insurers.' );
$cta_btn1_label    = ws_field( 'cta_primary_button_label', 'Book an appointment' );
$cta_btn1_url      = ws_field( 'cta_primary_button_url', 'https://lochendclinic.janeapp.com/#/staff_member/13/bio' );
$cta_btn2_label    = ws_field( 'cta_secondary_button_label', 'Call (587) 600-4945' );
$cta_btn2_url      = ws_field( 'cta_secondary_button_url', 'tel:+15876004945' );

// Get the What We Treat page so we can pull its sub-pages for cards.
$wwt_page = get_page_by_path( 'what-we-treat' );

// Card order lives in wellspring_wwt_subpages() so the section layout gets the
// identical list. See inc/template-tags.php.
$wwt_subpages = wellspring_wwt_subpages();

// Content block values (with their defaults) come from one shared place so
// the section migration can write exactly what this page renders.
$blocks = wellspring_home_block_values();

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

	<?php
	/*
	 * Everything between the hero and the closing call-to-action is now an
	 * editable, reorderable section row.
	 *
	 * Called once with no 'position' argument, so every row renders in the
	 * order the editor arranged it. The home block layouts render through
	 * template-parts/home/*.php — the same files this template used to include
	 * directly — which is why the markup is unchanged by the switch.
	 *
	 * Hero above and CTA below stay pinned in the template: the page must not
	 * be able to lose its opening or its call to action.
	 *
	 * Falls back to the old fixed rendering when no rows exist, so a site that
	 * has not run Tools > Wellspring Home Sections still has a home page.
	 */
	if ( function_exists( 'have_rows' ) && have_rows( 'page_sections' ) ) {
		get_template_part( 'template-parts/flexible-sections' );
	} else {
		get_template_part(
			'template-parts/home/intro',
			null,
			array(
				'eyebrow' => $blocks['intro']['eyebrow'],
				'title'   => $blocks['intro']['title'],
				'body'    => $blocks['intro']['body'],
			)
		);
		get_template_part(
			'template-parts/home/what-we-treat',
			null,
			array(
				'eyebrow'  => $blocks['wwt']['eyebrow'],
				'title'    => $blocks['wwt']['title'],
				'lede'     => $blocks['wwt']['lede'],
				'subpages' => $wwt_subpages,
			)
		);
		get_template_part(
			'template-parts/home/practitioner',
			null,
			array(
				'eyebrow'     => $blocks['practitioner']['eyebrow'],
				'name'        => $blocks['practitioner']['name'],
				'credentials' => $blocks['practitioner']['credentials'],
				'bio'         => $blocks['practitioner']['bio'],
				'link_label'  => $blocks['practitioner']['link_label'],
				'link_url'    => $blocks['practitioner']['link_url'],
				'portrait'    => $blocks['practitioner']['portrait'],
			)
		);
		get_template_part(
			'template-parts/home/modalities',
			null,
			array(
				'eyebrow'   => $blocks['modalities']['eyebrow'],
				'title'     => $blocks['modalities']['title'],
				'tcm_title' => $blocks['modalities']['tcm_title'],
				'tcm_body'  => $blocks['modalities']['tcm_body'],
				'tcm_image' => $blocks['modalities']['tcm_image'],
				'acu_title' => $blocks['modalities']['acu_title'],
				'acu_body'  => $blocks['modalities']['acu_body'],
				'acu_image' => $blocks['modalities']['acu_image'],
			)
		);
		get_template_part(
			'template-parts/home/featured-cases',
			null,
			array(
				'eyebrow' => $blocks['cases']['eyebrow'],
				'title'   => $blocks['cases']['title'],
				'lede'    => $blocks['cases']['lede'],
				'cases'   => $featured_cases,
			)
		);
		get_template_part( 'template-parts/reviews-slider' );
	}
	?>

	<?php get_template_part( 'template-parts/reviewed-by', null, array( 'position' => 'bottom' ) ); ?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php
get_footer();
