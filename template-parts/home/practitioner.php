<?php
/**
 * Home block: practitioner.
 *
 * Extracted verbatim from front-page.php — see template-parts/home/intro.php
 * for why.
 *
 * @param string $args['eyebrow']
 * @param string $args['name']
 * @param string $args['credentials']
 * @param string $args['bio']
 * @param string $args['link_label']
 * @param string $args['link_url']
 * @param array  $args['portrait']    ACF image array.
 *
 * @package Wellspring
 */

$pract_eyebrow     = $args['eyebrow'] ?? '';
$pract_name        = $args['name'] ?? '';
$pract_credentials = $args['credentials'] ?? '';
$pract_bio         = $args['bio'] ?? '';
$pract_link_label  = $args['link_label'] ?? '';
$pract_link_url    = $args['link_url'] ?? '';
$pract_portrait    = $args['portrait'] ?? null;
?>
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
