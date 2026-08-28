<?php
/**
 * Page hero.
 *
 * One part for every page-level hero: generic pages, About, What We Treat and
 * individual clinic cases. Each of those templates carried its own copy of the
 * background / overlay / heading markup — four places for it to drift, and four
 * places to edit when the hero gains a feature like a focal point.
 *
 * The four are NOT identical above the heading, so the parts that genuinely
 * differ are passed in: What We Treat has its own eyebrow and a rich-text lede,
 * a clinic case shows an archive crumb and the patient line, a child page shows
 * its parent. Everything below that — image resolution, focal point, overlay,
 * H1 — is shared, which is the duplication that mattered.
 *
 * Content comes from the Hero panel (see inc/acf-fields.php):
 *   page_h1          heading, falling back to the page title
 *   page_subheading  sub-heading, falling back to the excerpt
 *   hero_image       background, falling back to the featured image
 *   hero_focal       background-position for that image
 *
 * The background falls back to the featured image so pages set up before this
 * panel existed keep working untouched. They are separate fields because the
 * featured image is also the card thumbnail on parent listings, and a 1920x800
 * hero crop makes a poor card.
 *
 * @param string $args['eyebrow'] Eyebrow HTML. Omit for the parent-page link;
 *                                pass '' for no eyebrow at all.
 * @param string $args['lede']    Lede HTML. Omit for sub-heading / excerpt;
 *                                pass '' for no lede.
 *
 * @package Wellspring
 */

$ws_heading = function_exists( 'wellspring_page_h1' ) ? wellspring_page_h1() : get_the_title();

// ---------------------------------------------------------------- background
$ws_hero_url = '';
$ws_hero_img = function_exists( 'get_field' ) ? get_field( 'hero_image' ) : null;

if ( is_array( $ws_hero_img ) && ! empty( $ws_hero_img['ID'] ) ) {
	$ws_hero_url = (string) wp_get_attachment_image_url( (int) $ws_hero_img['ID'], 'wellspring-hero' );
}

if ( '' === $ws_hero_url && has_post_thumbnail() ) {
	$ws_hero_url = (string) get_the_post_thumbnail_url( get_the_ID(), 'wellspring-hero' );
}

$ws_focal = function_exists( 'get_field' ) ? trim( (string) get_field( 'hero_focal' ) ) : '';
if ( '' === $ws_focal ) {
	$ws_focal = 'center center';
}

// -------------------------------------------------------------------- eyebrow
if ( isset( $args['eyebrow'] ) ) {
	$ws_eyebrow = (string) $args['eyebrow'];
} else {
	$ws_eyebrow    = '';
	$ws_parent_id  = wp_get_post_parent_id( get_the_ID() );
	if ( $ws_parent_id ) {
		$ws_eyebrow = sprintf(
			'<a href="%s">%s</a>',
			esc_url( get_permalink( $ws_parent_id ) ),
			esc_html( get_the_title( $ws_parent_id ) )
		);
	}
}

// ----------------------------------------------------------------------- lede
if ( isset( $args['lede'] ) ) {
	$ws_lede = (string) $args['lede'];
} else {
	$ws_sub = function_exists( 'ws_field' ) ? ws_field( 'page_subheading', '' ) : '';
	if ( ! $ws_sub && has_excerpt() ) {
		$ws_sub = get_the_excerpt();
	}
	$ws_lede = $ws_sub ? esc_html( $ws_sub ) : '';
}

$ws_hero_class = $ws_hero_url ? 'ws-page-header ws-page-header--imaged' : 'ws-page-header';
?>

<section class="<?php echo esc_attr( $ws_hero_class ); ?>">
	<?php if ( $ws_hero_url ) : ?>
		<div class="ws-page-header__bg" style="background-image: url('<?php echo esc_url( $ws_hero_url ); ?>'); background-position: <?php echo esc_attr( $ws_focal ); ?>;" aria-hidden="true"></div>
		<div class="ws-page-header__overlay" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="ws-container ws-container--narrow ws-page-header__content">
		<?php if ( '' !== $ws_eyebrow ) : ?>
			<p class="eyebrow ws-page-header__crumb"><?php echo wp_kses_post( $ws_eyebrow ); ?></p>
		<?php endif; ?>

		<h1 class="ws-page-header__title"><?php echo esc_html( $ws_heading ); ?></h1>

		<?php if ( '' !== $ws_lede ) : ?>
			<div class="ws-page-header__lede"><?php echo wp_kses_post( $ws_lede ); ?></div>
		<?php endif; ?>
	</div>
</section>
