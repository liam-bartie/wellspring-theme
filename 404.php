<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package Wellspring
 */

get_header();
?>

<main id="primary" class="site-main">

	<section class="ws-page-header">
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<p class="eyebrow">Error 404</p>
			<h1 class="ws-page-header__title">This page wandered off.</h1>
			<p class="ws-page-header__lede">The page you're looking for doesn't exist or may have moved. Let's get you back on track.</p>
		</div>
	</section>

	<section class="ws-section">
		<div class="ws-container ws-container--narrow ws-404">
			<div class="ws-404-actions">
				<a class="ws-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to home</a>
				<a class="ws-btn ws-btn--ghost" href="<?php echo esc_url( home_url( '/what-we-treat/' ) ); ?>">See what we treat</a>
			</div>
			<p class="ws-404-links">
				Or explore
				<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a>,
				<a href="<?php echo esc_url( home_url( '/clinic-cases/' ) ); ?>">Clinic Cases</a>, or
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a>.
			</p>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main><!-- #primary -->

<?php
get_footer();
