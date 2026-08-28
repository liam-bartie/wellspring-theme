<?php
/**
 * The template for displaying all pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Wellspring
 */

get_header();
?>

<main id="primary" class="site-main">

	<?php
	while ( have_posts() ) :
		the_post();
		$parent_id    = wp_get_post_parent_id( get_the_ID() );
		?>

		<?php get_template_part( 'template-parts/page-hero' ); ?>

		<?php get_template_part( 'template-parts/reviewed-by' ); ?>

		<?php
		// Page content is built from the ACF "Page sections" builder. Each section
		// renders as its own full-width band so it can carry a background colour,
		// which is why the sections sit outside the narrow container. Pages with
		// no sections fall back to classic content in the narrow column.
		if ( function_exists( 'have_rows' ) && have_rows( 'page_sections' ) ) :
			?>
			<div id="post-<?php the_ID(); ?>" <?php post_class( 'ws-flex-sections' ); ?>>
				<?php get_template_part( 'template-parts/flexible-sections' ); ?>
			</div>
			<?php
		else :
			?>
			<section class="ws-page-body">
				<div class="ws-container ws-container--narrow">
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content' ); ?>>
						<?php the_content(); ?>
					</article>
				</div>
			</section>
			<?php
		endif;
		?>

		<?php
		/*
		 * The related-cases grid used to be hardcoded here, always after the
		 * page sections — which is precisely why nothing could be placed below
		 * it. It is now a "Clinic cases" section row, so it can sit anywhere in
		 * the list and content can follow it.
		 *
		 * seo-migration/migrate-cases-sections.php moved every page that had
		 * the old toggle on; revert-cases-sections.php puts it back if needed.
		 */

		// "Also explore" — sibling pages, kept in the narrow column.
		$siblings = ( $parent_id )
			? get_pages(
				array(
					'child_of'    => $parent_id,
					'parent'      => $parent_id,
					'sort_column' => 'menu_order',
					'exclude'     => get_the_ID(),
				)
			)
			: array();

		if ( ! empty( $siblings ) ) :
			?>
			<section class="ws-section">
				<div class="ws-container ws-container--narrow">
					<nav class="ws-sibling-nav" aria-label="<?php esc_attr_e( 'Other pages', 'wellspring' ); ?>">
						<p class="eyebrow"><?php esc_html_e( 'Also explore', 'wellspring' ); ?></p>
						<ul class="ws-sibling-nav__list">
							<?php foreach ( $siblings as $sibling ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( $sibling->ID ) ); ?>"><?php echo esc_html( $sibling->post_title ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				</div>
			</section>
			<?php
		endif;
		?>

		<?php
		if ( comments_open() || get_comments_number() ) :
			?>
			<section class="ws-section">
				<div class="ws-container ws-container--narrow">
					<?php comments_template(); ?>
				</div>
			</section>
			<?php
		endif;
	endwhile;

	// Curated Google reviews slider on selected pages, just above the CTA.
	if ( is_page( ws_reviews_page_slugs() ) ) {
		get_template_part( 'template-parts/reviews-slider' );
	}
	?>

	<?php get_template_part( 'template-parts/reviewed-by', null, array( 'position' => 'bottom' ) ); ?>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main><!-- #main -->

<?php
get_footer();
