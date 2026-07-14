<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Wellspring
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function wellspring_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'wellspring_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function wellspring_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'wellspring_pingback_header' );

/**
 * The whole site edits page content through ACF fields, not the block editor.
 * So the Gutenberg canvas is switched off for ALL Pages — every page (new ones
 * included) starts with the ACF "Main body" box instead. The Home / About /
 * What We Treat templates render their own dedicated ACF fields; every other
 * page uses the generic "Main body" field (see page.php).
 *
 * To re-enable the block editor on pages, remove these two hooks.
 */
add_filter(
	'use_block_editor_for_post_type',
	function ( $use_block_editor, $post_type ) {
		return ( 'page' === $post_type ) ? false : $use_block_editor;
	},
	10,
	2
);

add_action(
	'init',
	function () {
		remove_post_type_support( 'page', 'editor' );
	},
	100
);
