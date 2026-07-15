<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Wellspring
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'wellspring' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="site-header__inner">
			<div class="site-branding">
				<?php
				the_custom_logo();
				// When a logo is set it replaces the visible site name; keep the name
				// in the markup for screen readers and SEO via screen-reader-text.
				$ws_title_class = has_custom_logo() ? 'site-title screen-reader-text' : 'site-title';
				if ( is_front_page() && is_home() ) :
					?>
					<h1 class="<?php echo esc_attr( $ws_title_class ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<?php
				else :
					?>
					<p class="<?php echo esc_attr( $ws_title_class ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
					<?php
				endif;
				?>
			</div><!-- .site-branding -->

			<div class="site-header__nav-wrap">
				<nav id="site-navigation" class="main-navigation">
					<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Menu', 'wellspring' ); ?></button>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'primary-menu',
						)
					);
					?>
				</nav><!-- #site-navigation -->

				<a class="ws-btn ws-header-book" href="<?php echo esc_url( WELLSPRING_BOOKING_URL ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Book now', 'wellspring' ); ?>
				</a>
			</div><!-- .site-header__nav-wrap -->
		</div>
	</header><!-- #masthead -->
