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
		$has_thumb    = has_post_thumbnail();
		$thumb_url    = $has_thumb ? get_the_post_thumbnail_url( get_the_ID(), 'wellspring-hero' ) : '';
		$header_class = $has_thumb ? 'ws-page-header ws-page-header--imaged' : 'ws-page-header';
		?>

		<section class="<?php echo esc_attr( $header_class ); ?>">
			<?php if ( $has_thumb ) : ?>
				<div class="ws-page-header__bg" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');" aria-hidden="true"></div>
				<div class="ws-page-header__overlay" aria-hidden="true"></div>
			<?php endif; ?>
			<div class="ws-container ws-container--narrow ws-page-header__content">
				<?php if ( $parent_id ) : ?>
					<p class="eyebrow ws-page-header__crumb">
						<a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>"><?php echo esc_html( get_the_title( $parent_id ) ); ?></a>
					</p>
				<?php endif; ?>

				<h1 class="ws-page-header__title"><?php the_title(); ?></h1>

				<?php
				$ws_sub = ws_field( 'page_subheading', '' );
				if ( ! $ws_sub && has_excerpt() ) {
					$ws_sub = get_the_excerpt();
				}
				if ( $ws_sub ) :
					?>
					<p class="ws-page-header__lede"><?php echo esc_html( $ws_sub ); ?></p>
				<?php endif; ?>
			</div>
		</section>

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
