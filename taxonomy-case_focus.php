<?php
/**
 * Focus-area archive — lists every clinic case in a single case_focus term.
 *
 * This is where the "View all … cases" links (from the What We Treat pages and
 * the related-cases shortcode) land. Reuses the case-card grid, scoped to the
 * current focus term. Lives at /clinic-cases/focus/<slug>/.
 *
 * @package Wellspring
 */

get_header();

$term = get_queried_object();
?>

<main id="primary" class="site-main">

	<section class="ws-page-header">
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<p class="eyebrow ws-page-header__crumb">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>">Clinic cases</a>
			</p>
			<h1 class="ws-page-header__title"><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( ! empty( $term->description ) ) : ?>
				<p class="ws-page-header__lede"><?php echo esc_html( $term->description ); ?></p>
			<?php else : ?>
				<p class="ws-page-header__lede">Real cases we've treated in this area. Names are shortened to initials for privacy.</p>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<section class="ws-section ws-cases-archive">
		<div class="ws-container">
			<?php if ( have_posts() ) : ?>
				<div class="ws-cases-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						global $post;
						get_template_part( 'template-parts/case-card', null, array( 'case' => $post ) );
					endwhile;
					?>
				</div>

				<?php
				$pagination = paginate_links( array( 'type' => 'list' ) );
				if ( $pagination ) :
					?>
					<nav class="ws-cases-pagination" aria-label="Clinic cases pagination"><?php echo wp_kses_post( $pagination ); ?></nav>
				<?php endif; ?>
			<?php else : ?>
				<p class="ws-cases-empty">No cases in this area yet. <a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>">Browse all clinic cases →</a></p>
			<?php endif; ?>

			<p class="ws-related-cases__view-all" style="margin-top: var(--ws-space-12);">
				<a class="ws-link-arrow ws-link-arrow--back" href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>">Back to all cases</a>
			</p>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php
get_footer();
