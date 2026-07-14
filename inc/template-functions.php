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
 * Pages whose content is fully ACF-driven, so the native block editor /
 * content canvas is hidden — editing happens only in the ACF field boxes.
 *
 * (What We Treat is intentionally NOT here: it still uses its content area
 * for the "Our Approach", "Your First Visit", and FAQ sections.)
 *
 * @return int[] Page IDs.
 */
function ws_acf_driven_page_ids() {
	$ids   = array();
	$front = (int) get_option( 'page_on_front' );
	if ( $front ) {
		$ids[] = $front;
	}
	$about = get_page_by_path( 'about' );
	if ( $about instanceof WP_Post ) {
		$ids[] = (int) $about->ID;
	}
	return $ids;
}

/**
 * Turn off the block editor on ACF-driven pages.
 *
 * @param bool    $use_block_editor Whether to use the block editor.
 * @param WP_Post $post             The post being edited.
 * @return bool
 */
function wellspring_disable_block_editor( $use_block_editor, $post ) {
	if ( $post instanceof WP_Post && in_array( (int) $post->ID, ws_acf_driven_page_ids(), true ) ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'wellspring_disable_block_editor', 10, 2 );

/**
 * Remove the content editor box entirely on ACF-driven pages, so only the
 * ACF field boxes remain on the edit screen.
 */
function wellspring_remove_editor_support() {
	global $post;
	if ( $post instanceof WP_Post && in_array( (int) $post->ID, ws_acf_driven_page_ids(), true ) ) {
		remove_post_type_support( 'page', 'editor' );
	}
}
add_action( 'add_meta_boxes', 'wellspring_remove_editor_support', 0 );
