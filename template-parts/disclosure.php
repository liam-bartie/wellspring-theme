<?php
/**
 * Renders one case-study disclosure slot.
 *
 * Usage:
 *   get_template_part( 'template-parts/disclosure', null, array( 'slot' => 'cases_top' ) );
 *   get_template_part( 'template-parts/disclosure', null, array( 'slot' => 'cases_bottom' ) );
 *   get_template_part( 'template-parts/disclosure', null, array( 'slot' => 'case_bottom' ) );
 *
 * Renders nothing at all when the slot resolves empty — no band, no padding.
 * See inc/disclosure.php for where the copy lives.
 *
 * @package Wellspring
 */

$ws_slot = isset( $args['slot'] ) ? (string) $args['slot'] : '';

/*
 * 'bare' emits the copy without its own band or container, for placing
 * inside an existing column — on a single case the disclaimer belongs in
 * the narrow case measure directly under the write-up, not as a
 * full-width band that would break the two-column layout.
 */
$ws_bare = ! empty( $args['bare'] );

if ( '' === $ws_slot || ! function_exists( 'wellspring_disclosure_html' ) ) {
	return;
}

$ws_disclosure = wellspring_disclosure_html( $ws_slot );

if ( '' === $ws_disclosure ) {
	return;
}
?>

<?php if ( $ws_bare ) : ?>
	<div class="ws-disclosure ws-disclosure--inline ws-disclosure--<?php echo esc_attr( str_replace( '_', '-', $ws_slot ) ); ?>">
		<?php echo $ws_disclosure; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- run through wp_kses_post in wellspring_disclosure_html(). ?>
	</div>
<?php else : ?>
	<section class="ws-section ws-disclosure ws-disclosure--<?php echo esc_attr( str_replace( '_', '-', $ws_slot ) ); ?>">
		<div class="ws-container ws-container--narrow">
			<?php echo $ws_disclosure; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- run through wp_kses_post in wellspring_disclosure_html(). ?>
		</div>
	</section>
<?php endif; ?>
