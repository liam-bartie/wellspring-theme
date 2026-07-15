<?php
/**
 * Template for the "About" page.
 *
 * Auto-applied by WordPress because the file is named after the page slug
 * (page-about.php). The page body lives entirely in the ACF "Main body"
 * WYSIWYG field — the native block editor is hidden on this page (see
 * inc/template-functions.php). Falls back to the classic post content if the
 * field is ever empty, so nothing disappears.
 *
 * @package Wellspring
 */

get_header();

while ( have_posts() ) :
	the_post();

	$has_thumb    = has_post_thumbnail();
	$thumb_url    = $has_thumb ? get_the_post_thumbnail_url( get_the_ID(), 'wellspring-hero' ) : '';
	$header_class = $has_thumb ? 'ws-page-header ws-page-header--imaged' : 'ws-page-header';
	$main_body    = ws_field( 'about_main_body', '' );
	?>

<main id="primary" class="site-main">

	<section class="<?php echo esc_attr( $header_class ); ?>">
		<?php if ( $has_thumb ) : ?>
			<div class="ws-page-header__bg" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');" aria-hidden="true"></div>
			<div class="ws-page-header__overlay" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<h1 class="ws-page-header__title"><?php the_title(); ?></h1>
			<?php
			$about_sub = ws_field( 'page_subheading', '' );
			if ( ! $about_sub && has_excerpt() ) {
				$about_sub = get_the_excerpt();
			}
			if ( $about_sub ) :
				?>
				<p class="ws-page-header__lede"><?php echo esc_html( $about_sub ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<section class="ws-page-body">
		<div class="ws-container ws-container--narrow">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content' ); ?>>
				<?php
				if ( $main_body ) {
					echo wp_kses_post( $main_body );
				} else {
					// Safety fallback: render classic content if the field is empty.
					the_content();
				}

				// Any extra sections added via the "Page sections" builder render below.
				if ( function_exists( 'have_rows' ) && have_rows( 'page_sections' ) ) {
					get_template_part( 'template-parts/flexible-sections' );
				}
				?>
			</article>
		</div>
	</section>

<?php endwhile; ?>

	<?php get_template_part( 'template-parts/reviews-slider' ); ?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main><!-- #primary -->

<?php
get_footer();
