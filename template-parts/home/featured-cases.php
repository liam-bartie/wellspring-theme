<?php
/**
 * Home block: featured clinic cases.
 *
 * Extracted verbatim from front-page.php — see template-parts/home/intro.php
 * for why.
 *
 * @param string $args['eyebrow']
 * @param string $args['title']
 * @param string $args['lede']
 * @param array  $args['cases'] WP_Post objects (published clinic_case).
 *
 * @package Wellspring
 */

$cases_eyebrow  = $args['eyebrow'] ?? '';
$cases_title    = $args['title'] ?? '';
$cases_lede     = $args['lede'] ?? '';
$featured_cases = $args['cases'] ?? array();

if ( empty( $featured_cases ) ) {
	return;
}
?>
		<section class="ws-section ws-home-cases">
			<div class="ws-container">
				<header class="ws-section-header ws-section-header--center">
					<?php if ( $cases_eyebrow ) : ?>
						<p class="eyebrow"><?php echo esc_html( $cases_eyebrow ); ?></p>
					<?php endif; ?>
					<?php if ( $cases_title ) : ?>
						<h2><?php echo esc_html( $cases_title ); ?></h2>
					<?php endif; ?>
					<?php if ( $cases_lede ) : ?>
						<div class="ws-section-header__lede"><?php echo wp_kses_post( $cases_lede ); ?></div>
					<?php endif; ?>
				</header>

				<div class="ws-cases-grid">
					<?php foreach ( $featured_cases as $case ) {
						get_template_part( 'template-parts/case-card', null, array( 'case' => $case ) );
					} ?>
				</div>

				<p class="ws-home-cases__view-all">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>" class="ws-link-arrow">View all cases</a>
				</p>
			</div>
		</section>
