<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Wellspring
 */

?>

	<footer id="colophon" class="site-footer">
		<div class="ws-footer-grid">

			<div class="ws-footer-brand">
				<p class="ws-footer-mark">Wellspring Health</p>
				<p class="ws-footer-tagline">Acupuncture &amp; Traditional Chinese Medicine</p>
				<p>Calm, grounded, considered care for pain relief, women's health, sleep, digestion, and beyond &mdash; in the heart of Inglewood, Calgary.</p>
			</div>

			<div class="ws-footer-col">
				<h4><?php esc_html_e( 'Visit', 'wellspring' ); ?></h4>
				<ul>
					<li>1004 8 Ave SE<br />Calgary, AB T2G 0M4</li>
					<li><a href="tel:+15876004945">(587) 600-4945</a></li>
					<li><a href="mailto:info@wellspringhealth.ca">info@wellspringhealth.ca</a></li>
				</ul>
			</div>

			<div class="ws-footer-col">
				<h4><?php esc_html_e( 'Explore', 'wellspring' ); ?></h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => '__return_empty_string',
					)
				);
				?>
			</div>

		</div>

		<?php
		// Secondary footer menu (Events, legal pages, etc.). Only renders if a
		// menu is assigned to the "Footer (secondary)" location in Appearance → Menus.
		if ( has_nav_menu( 'menu-footer' ) ) :
			?>
			<nav class="ws-footer-secondary" aria-label="<?php esc_attr_e( 'Secondary footer', 'wellspring' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-footer',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => '__return_empty_string',
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="ws-footer-bottom">
			<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> Wellspring Health Ltd. All rights reserved.</span>
			<span>Acupuncture &middot; Herbal medicine &middot; Cupping &middot; Tui Na</span>
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
