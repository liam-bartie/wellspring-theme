<?php
/**
 * Reusable clinic case card markup.
 *
 * Used by the Clinic Cases archive AND the homepage featured-cases section.
 * Pass the case post via:
 *   get_template_part( 'template-parts/case-card', null, array( 'case' => $case_post ) );
 *
 * @package Wellspring
 */

if ( empty( $args['case'] ) ) {
	return;
}

$case            = $args['case'];
$focus_areas     = wp_get_post_terms( $case->ID, 'case_focus' );
$focus_slugs     = wp_list_pluck( $focus_areas, 'slug' );
$focus_names     = wp_list_pluck( $focus_areas, 'name' );
$primary_focus   = $focus_areas[0]->name ?? '';
$initial         = function_exists( 'get_field' ) ? get_field( 'patient_initial', $case->ID ) : '';
$context         = function_exists( 'get_field' ) ? get_field( 'patient_context', $case->ID ) : '';
$result_excerpt  = function_exists( 'get_field' ) ? wp_strip_all_tags( get_field( 'result', $case->ID ) ) : '';
if ( ! $result_excerpt ) {
	$result_excerpt = wp_strip_all_tags( $case->post_content );
}
$result_excerpt  = wp_trim_words( $result_excerpt, 30, '…' );
$monogram        = '';
if ( $initial ) {
	$parts = explode( ' ', trim( $initial ) );
	foreach ( $parts as $part ) {
		$monogram .= strtoupper( substr( $part, 0, 1 ) );
	}
}
$has_image       = has_post_thumbnail( $case->ID );
?>
<article
	class="ws-case-card"
	data-search="<?php echo esc_attr( strtolower( $case->post_title . ' ' . wp_strip_all_tags( $case->post_content ) . ' ' . $context . ' ' . implode( ' ', $focus_names ) ) ); ?>"
	data-tags="<?php echo esc_attr( implode( ',', $focus_slugs ) ); ?>"
>
	<a class="ws-case-card__media<?php echo $has_image ? '' : ' ws-case-card__media--placeholder'; ?>" href="<?php echo esc_url( get_permalink( $case->ID ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( $has_image ) {
			echo get_the_post_thumbnail(
				$case->ID,
				'medium_large',
				array(
					'class'   => 'ws-case-card__img',
					'loading' => 'lazy',
					'alt'     => '',
				)
			);
		} else {
			get_template_part( 'template-parts/case-sprig' );
		}
		?>
	</a>

	<?php if ( $primary_focus ) : ?>
		<p class="ws-case-card__focus"><?php echo esc_html( $primary_focus ); ?></p>
	<?php endif; ?>

	<div class="ws-case-card__patient">
		<?php if ( $monogram ) : ?>
			<span class="ws-case-card__monogram" aria-hidden="true"><?php echo esc_html( substr( $monogram, 0, 2 ) ); ?></span>
		<?php endif; ?>
		<span><?php echo esc_html( $initial ? $initial : 'Patient' ); ?><?php if ( $context ) : ?> &middot; <?php echo esc_html( $context ); ?><?php endif; ?></span>
	</div>

	<h3 class="ws-case-card__title">
		<a href="<?php echo esc_url( get_permalink( $case->ID ) ); ?>"><?php echo esc_html( $case->post_title ); ?></a>
	</h3>

	<?php if ( $result_excerpt ) : ?>
		<p class="ws-case-card__excerpt"><?php echo esc_html( $result_excerpt ); ?></p>
	<?php endif; ?>

	<div class="ws-case-card__divider" aria-hidden="true"></div>

	<a href="<?php echo esc_url( get_permalink( $case->ID ) ); ?>" class="ws-case-card__cta">Read the full case <span aria-hidden="true">→</span></a>
</article>
