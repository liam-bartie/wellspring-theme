<?php
/**
 * Shared term archive for the clinic-case facets.
 *
 * Covers case_symptom ("Symptoms") and case_modality ("Treatments used"), and
 * any future case facet. case_focus keeps its own dedicated template
 * (taxonomy-case_focus.php), which WordPress prefers over this one.
 *
 * Without this file these archives fell through to archive.php — the generic
 * blog layout — even though the URLs are public and indexable. The
 * related-cases block links here via "View all … cases".
 *
 * @package Wellspring
 */

get_header();

$ws_term     = get_queried_object();
$ws_taxonomy = ( $ws_term instanceof WP_Term ) ? $ws_term->taxonomy : '';

// Facet-appropriate crumb label and fallback lede.
switch ( $ws_taxonomy ) {
	case 'case_symptom':
		$ws_crumb = __( 'Symptoms', 'wellspring' );
		$ws_lede  = __( 'Real cases where this symptom was the presenting concern. Names are shortened to initials for privacy.', 'wellspring' );
		break;
	case 'case_modality':
		$ws_crumb = __( 'Treatments used', 'wellspring' );
		$ws_lede  = __( 'Real cases treated with this approach. Names are shortened to initials for privacy.', 'wellspring' );
		break;
	default:
		$ws_crumb = __( 'Clinic cases', 'wellspring' );
		$ws_lede  = __( 'Real cases we’ve treated. Names are shortened to initials for privacy.', 'wellspring' );
		break;
}
?>

<main id="primary" class="site-main">

	<section class="ws-page-header">
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<p class="eyebrow ws-page-header__crumb">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>"><?php echo esc_html( $ws_crumb ); ?></a>
			</p>
			<h1 class="ws-page-header__title"><?php echo esc_html( single_term_title( '', false ) ); ?></h1>
			<?php if ( $ws_term instanceof WP_Term && ! empty( $ws_term->description ) ) : ?>
				<p class="ws-page-header__lede"><?php echo esc_html( $ws_term->description ); ?></p>
			<?php else : ?>
				<p class="ws-page-header__lede"><?php echo esc_html( $ws_lede ); ?></p>
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
				$ws_pagination = paginate_links( array( 'type' => 'list' ) );
				if ( $ws_pagination ) :
					?>
					<nav class="ws-cases-pagination" aria-label="<?php esc_attr_e( 'Clinic cases pagination', 'wellspring' ); ?>"><?php echo wp_kses_post( $ws_pagination ); ?></nav>
				<?php endif; ?>
			<?php else : ?>
				<p class="ws-cases-empty">
					<?php esc_html_e( 'No cases here yet.', 'wellspring' ); ?>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>"><?php esc_html_e( 'Browse all clinic cases →', 'wellspring' ); ?></a>
				</p>
			<?php endif; ?>

			<p class="ws-related-cases__view-all" style="margin-top: var(--ws-space-12);">
				<a class="ws-link-arrow ws-link-arrow--back" href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>"><?php esc_html_e( 'Back to all cases', 'wellspring' ); ?></a>
			</p>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php
get_footer();
