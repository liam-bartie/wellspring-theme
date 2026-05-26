<?php
/**
 * Template for the "What We Treat" hub page.
 *
 * Auto-applied by WordPress because the file is named after the page slug
 * (page-what-we-treat.php). Renders a structured hero + condition cards grid
 * + Gutenberg content area below for additional editable blocks.
 *
 * Cards auto-populate from sub-pages of this page (pulled in menu_order).
 * Editable via WP admin → Pages → What We Treat → ACF panel.
 *
 * @package Wellspring
 */

get_header();

while ( have_posts() ) :
	the_post();

	$has_thumb    = has_post_thumbnail();
	$thumb_url    = $has_thumb ? get_the_post_thumbnail_url( get_the_ID(), 'wellspring-hero' ) : '';
	$header_class = $has_thumb ? 'ws-page-header ws-page-header--imaged' : 'ws-page-header';

	$hub_eyebrow   = ws_field( 'hub_eyebrow', 'What we treat' );
	$hub_lede      = ws_field( 'hub_lede', "TCM addresses the whole person — body, mind, and the patterns that link them. Browse by category, or get in touch if you don't see what you're looking for." );
	$cards_eyebrow = ws_field( 'cards_eyebrow', 'Conditions we treat' );
	$cards_title   = ws_field( 'cards_title', 'Six areas of focus.' );
	$cards_intro   = ws_field( 'cards_intro', '' );

	$subpages = get_children(
		array(
			'post_parent' => get_the_ID(),
			'post_type'   => 'page',
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
			'numberposts' => -1,
		)
	);
	?>

<main id="primary" class="site-main">

	<section class="<?php echo esc_attr( $header_class ); ?>">
		<?php if ( $has_thumb ) : ?>
			<div class="ws-page-header__bg" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');" aria-hidden="true"></div>
			<div class="ws-page-header__overlay" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<?php if ( $hub_eyebrow ) : ?>
				<p class="eyebrow"><?php echo esc_html( $hub_eyebrow ); ?></p>
			<?php endif; ?>
			<h1 class="ws-page-header__title"><?php the_title(); ?></h1>
			<?php if ( $hub_lede ) : ?>
				<p class="ws-page-header__lede"><?php echo esc_html( $hub_lede ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<section class="ws-section ws-section--mist">
		<div class="ws-container">
			<?php if ( $cards_eyebrow || $cards_title || $cards_intro ) : ?>
				<header class="ws-section-header">
					<?php if ( $cards_eyebrow ) : ?>
						<p class="eyebrow"><?php echo esc_html( $cards_eyebrow ); ?></p>
					<?php endif; ?>
					<?php if ( $cards_title ) : ?>
						<h2><?php echo esc_html( $cards_title ); ?></h2>
					<?php endif; ?>
					<?php if ( $cards_intro ) : ?>
						<p class="ws-section-header__lede"><?php echo esc_html( $cards_intro ); ?></p>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<?php if ( ! empty( $subpages ) ) : ?>
				<div class="ws-cards">
					<?php
					foreach ( $subpages as $sub ) :
						$sub_thumb = get_the_post_thumbnail_url( $sub->ID, 'wellspring-card' );
						$excerpt   = $sub->post_excerpt;
						if ( ! $excerpt ) {
							$excerpt = wp_trim_words( wp_strip_all_tags( $sub->post_content ), 22, '…' );
						}
						$card_class = $sub_thumb ? 'ws-card ws-card--imaged' : 'ws-card';
						?>
						<a class="<?php echo esc_attr( $card_class ); ?>" href="<?php echo esc_url( get_permalink( $sub->ID ) ); ?>">
							<?php if ( $sub_thumb ) : ?>
								<div class="ws-card__image" style="background-image: url('<?php echo esc_url( $sub_thumb ); ?>');" aria-hidden="true"></div>
							<?php endif; ?>
							<div class="ws-card__body-wrap">
								<h3 class="ws-card__title"><?php echo esc_html( $sub->post_title ); ?></h3>
								<p class="ws-card__body"><?php echo esc_html( $excerpt ); ?></p>
								<span class="ws-card__cta">Learn more</span>
							</div>
						</a>
						<?php
					endforeach;
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	// Render Gutenberg page content if any (so editor can drop in callouts, image+text, etc.)
	$content = trim( wp_strip_all_tags( get_the_content() ) );
	if ( ! empty( $content ) ) :
		?>
		<section class="ws-page-body">
			<div class="ws-container ws-container--narrow">
				<article class="entry-content"><?php the_content(); ?></article>
			</div>
		</section>
		<?php
	endif;
	?>

<?php endwhile; ?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php
get_footer();
