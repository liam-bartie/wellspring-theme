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

				<?php if ( has_excerpt() ) : ?>
					<p class="ws-page-header__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="ws-page-body">
			<div class="ws-container ws-container--narrow">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content' ); ?>>
					<?php the_content(); ?>
				</article>

				<?php
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
					<nav class="ws-sibling-nav" aria-label="<?php esc_attr_e( 'Other pages', 'wellspring' ); ?>">
						<p class="eyebrow"><?php esc_html_e( 'Also explore', 'wellspring' ); ?></p>
						<ul class="ws-sibling-nav__list">
							<?php foreach ( $siblings as $sibling ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( $sibling->ID ) ); ?>"><?php echo esc_html( $sibling->post_title ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
					<?php
				endif;
				?>
			</div>
		</section>

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
	?>

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>
	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main><!-- #main -->

<?php
get_footer();
