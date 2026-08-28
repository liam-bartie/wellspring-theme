<?php
/**
 * "Reviewed by" credibility badge.
 *
 * Sits in a soft mist-green band above the global CTA banner on every
 * page that displays clinical content. Skipped on Contact and Book
 * Appointments (functional pages, no medical content to review).
 *
 * The wording is editable: see wellspring_badge_text() in inc/acf-fields.php
 * for the resolution order (per-post override -> global default -> theme
 * constant). Clinic cases default to "written by" rather than "reviewed by",
 * because Dr. Cowburn authored them.
 *
 * Inline SVG shield-check icon (no external request, fully themable
 * via currentColor).
 *
 * Usage: get_template_part( 'template-parts/reviewed-by' );
 *
 * @package Wellspring
 */

$excluded_slugs = array( 'contact', 'book' );
$current_slug   = is_singular() ? get_post_field( 'post_name', get_queried_object_id() ) : '';
if ( in_array( $current_slug, $excluded_slugs, true ) ) {
	return;
}

$ws_badge_text = function_exists( 'wellspring_badge_text' )
	? wellspring_badge_text()
	: 'This page was reviewed by <strong>Dr.&nbsp;Laura Cowburn</strong>, Doctor of TCM and Registered Acupuncturist in Alberta.';

// An editor can clear the wording or tick "hide" — render nothing rather than
// an empty green band.
if ( '' === trim( $ws_badge_text ) ) {
	return;
}

$ws_badge_allowed = function_exists( 'wellspring_badge_allowed_html' )
	? wellspring_badge_allowed_html()
	: array( 'strong' => array(), 'em' => array(), 'br' => array() );
?>

<div class="ws-reviewed-by" role="note">
	<div class="ws-reviewed-by__inner">
		<svg class="ws-reviewed-by__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
			<path d="m9 12 2 2 4-4" />
		</svg>
		<p class="ws-reviewed-by__text"><?php echo wp_kses( $ws_badge_text, $ws_badge_allowed ); ?></p>
	</div>
</div>
