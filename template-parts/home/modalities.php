<?php
/**
 * Home block: the two modality cards (TCM and Acupuncture).
 *
 * Extracted verbatim from front-page.php — see template-parts/home/intro.php
 * for why.
 *
 * @param string $args['eyebrow']
 * @param string $args['title']
 * @param string $args['tcm_title']
 * @param string $args['tcm_body']
 * @param array  $args['tcm_image']
 * @param string $args['acu_title']
 * @param string $args['acu_body']
 * @param array  $args['acu_image']
 *
 * @package Wellspring
 */

$mod_eyebrow = $args['eyebrow'] ?? '';
$mod_title   = $args['title'] ?? '';
$tcm_title   = $args['tcm_title'] ?? '';
$tcm_body    = $args['tcm_body'] ?? '';
$tcm_image   = $args['tcm_image'] ?? null;
$acu_title   = $args['acu_title'] ?? '';
$acu_body    = $args['acu_body'] ?? '';
$acu_image   = $args['acu_image'] ?? null;

// Only render if either block has a heading.
if ( ! $tcm_title && ! $acu_title ) {
	return;
}
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
