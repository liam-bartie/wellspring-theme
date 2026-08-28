<?php
/**
 * Home block: "What we treat" card grid.
 *
 * Extracted verbatim from front-page.php — see template-parts/home/intro.php
 * for why.
 *
 * The cards are not editable content: each one is a sub-page of "What We
 * Treat", so the grid is derived from those pages' titles, excerpts and
 * featured images. Only the section heading is editable here.
 *
 * @param string $args['eyebrow']
 * @param string $args['title']
 * @param string $args['lede']
 * @param array  $args['subpages'] WP_Post objects, already ordered.
 *
 * @package Wellspring
 */

$wwt_eyebrow  = $args['eyebrow'] ?? '';
$wwt_title    = $args['title'] ?? '';
$wwt_lede     = $args['lede'] ?? '';
$wwt_subpages = $args['subpages'] ?? array();
?>
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
