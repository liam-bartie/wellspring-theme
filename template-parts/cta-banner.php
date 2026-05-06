<?php
/**
 * Global CTA banner partial.
 *
 * Renders the "Ready when you are." closing CTA on every page except
 * Contact and Book Appointments (where it'd be redundant). Pulls copy
 * from the Home page's ACF fields so editing the home CTA updates the
 * banner site-wide.
 *
 * Usage: get_template_part( 'template-parts/cta-banner' );
 *
 * @package Wellspring
 */

// Skip on pages where a CTA would be redundant.
$excluded_slugs = array( 'contact', 'book-appointments' );
$current_slug   = is_singular() ? get_post_field( 'post_name', get_queried_object_id() ) : '';
if ( in_array( $current_slug, $excluded_slugs, true ) ) {
	return;
}

$cta_title       = ws_home_field( 'cta_title', 'Ready when you are.' );
$cta_lede        = ws_home_field( 'cta_lede', 'New patients welcome. Appointments typically available within the week. Direct billing to most major insurers.' );
$cta_btn1_label  = ws_home_field( 'cta_primary_button_label', 'Book an appointment' );
$cta_btn1_url    = ws_home_field( 'cta_primary_button_url', '/book-appointments/' );
$cta_btn2_label  = ws_home_field( 'cta_secondary_button_label', 'Call (587) 600-4945' );
$cta_btn2_url    = ws_home_field( 'cta_secondary_button_url', 'tel:+15876004945' );
?>

<section class="ws-cta">
	<div class="ws-container ws-container--narrow ws-cta__inner">
		<h2 class="ws-cta__title"><?php echo esc_html( $cta_title ); ?></h2>
		<p class="ws-cta__lede"><?php echo esc_html( $cta_lede ); ?></p>
		<div class="ws-cta__actions">
			<?php if ( $cta_btn1_label ) : ?>
				<a href="<?php echo esc_url( $cta_btn1_url ); ?>" class="ws-btn ws-btn--reversed"><?php echo esc_html( $cta_btn1_label ); ?></a>
			<?php endif; ?>
			<?php if ( $cta_btn2_label ) : ?>
				<a href="<?php echo esc_url( $cta_btn2_url ); ?>" class="ws-btn ws-btn--ghost-light"><?php echo esc_html( $cta_btn2_label ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
