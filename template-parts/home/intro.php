<?php
/**
 * Home block: intro text.
 *
 * Extracted verbatim from front-page.php so the same markup can be driven
 * either by the page's top-level ACF fields (front-page.php) or by a
 * "Page sections" row (template-parts/flexible-sections.php). One copy means
 * the two paths cannot drift apart.
 *
 * @param string $args['eyebrow']
 * @param string $args['title']
 * @param string $args['body']
 *
 * @package Wellspring
 */

$intro_eyebrow = $args['eyebrow'] ?? '';
$intro_title   = $args['title'] ?? '';
$intro_body    = $args['body'] ?? '';

if ( ! $intro_eyebrow && ! $intro_title && ! $intro_body ) {
	return;
}
?>
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
