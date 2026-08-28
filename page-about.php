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
	$main_body    = ws_field( 'about_main_body', '' );
	?>

<main id="primary" class="site-main">

	<?php get_template_part( 'template-parts/page-hero', null, array( 'eyebrow' => '' ) ); ?>

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
				?>
			</article>
		</div>
	</section>

	<?php
	// Extra sections from the "Page sections" builder render below the main body
	// as full-width bands, so each can carry its own background colour. The
	// .ws-flex-sections wrapper lets the CSS collapse the seam when the last
	// band and the mist reviews section below it share a colour.
	if ( function_exists( 'have_rows' ) && have_rows( 'page_sections' ) ) :
		?>
		<div class="ws-flex-sections">
			<?php get_template_part( 'template-parts/flexible-sections' ); ?>
		</div>
		<?php
	endif;
	?>

<?php endwhile; ?>

	<?php get_template_part( 'template-parts/reviews-slider' ); ?>

	<?php get_template_part( 'template-parts/reviewed-by', null, array( 'position' => 'bottom' ) ); ?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main><!-- #primary -->

<?php
get_footer();
