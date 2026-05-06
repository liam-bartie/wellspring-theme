<?php
/**
 * Gutenberg block patterns for the Wellspring theme.
 *
 * Registers a "Wellspring" category in the block-pattern picker and
 * five pre-designed sections editors can insert into any page or post.
 *
 * @package Wellspring
 */

add_action(
	'init',
	function () {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}

		// Custom category in the inserter.
		register_block_pattern_category(
			'wellspring',
			array(
				'label' => __( 'Wellspring', 'wellspring' ),
			)
		);

		/**
		 * 1. Callout box — boxed accent + heading + body + button.
		 */
		register_block_pattern(
			'wellspring/callout',
			array(
				'title'       => __( 'Callout box', 'wellspring' ),
				'description' => __( 'A boxed callout with sage accent, heading, body, and a single CTA button.', 'wellspring' ),
				'categories'  => array( 'wellspring' ),
				'keywords'    => array( 'callout', 'box', 'cta', 'highlight' ),
				'content'     => '<!-- wp:group {"className":"ws-callout","layout":{"type":"constrained"}} -->
<div class="wp-block-group ws-callout"><!-- wp:heading {"level":3,"className":"ws-callout__title"} -->
<h3 class="wp-block-heading ws-callout__title">Got questions about acupuncture?</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Book a free 15-minute phone consult to talk through your concerns and decide if treatment is right for you. No pressure, no commitment.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/book-appointments/">Book a consult</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
			)
		);

		/**
		 * 2. Image + text two-column.
		 */
		register_block_pattern(
			'wellspring/image-text',
			array(
				'title'       => __( 'Image + text two-column', 'wellspring' ),
				'description' => __( 'Side-by-side image and text. Stacks on mobile.', 'wellspring' ),
				'categories'  => array( 'wellspring' ),
				'keywords'    => array( 'image', 'text', 'columns', 'two column', 'feature' ),
				'content'     => '<!-- wp:columns {"verticalAlignment":"center","className":"ws-pattern-imagetext"} -->
<div class="wp-block-columns are-vertically-aligned-center ws-pattern-imagetext"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"sizeSlug":"large","className":"ws-pattern-imagetext__image"} -->
<figure class="wp-block-image size-large ws-pattern-imagetext__image"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow">Our approach</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Listen first, treat second.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every appointment begins with a real conversation about how you\'re feeling, what you\'ve tried, and where you want to get to. From there we plan a course of treatment that fits your body and your life.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
			)
		);

		/**
		 * 3. Stat row — three big numbers + labels.
		 */
		register_block_pattern(
			'wellspring/stats',
			array(
				'title'       => __( 'Stat row', 'wellspring' ),
				'description' => __( 'Three big numbers with labels. Good for credibility moments.', 'wellspring' ),
				'categories'  => array( 'wellspring' ),
				'keywords'    => array( 'stats', 'numbers', 'metrics', 'social proof' ),
				'content'     => '<!-- wp:group {"className":"ws-stats","layout":{"type":"constrained"}} -->
<div class="wp-block-group ws-stats"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"className":"ws-stat"} -->
<div class="wp-block-column ws-stat"><!-- wp:heading {"level":3,"className":"ws-stat__number"} -->
<h3 class="wp-block-heading ws-stat__number">10+</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ws-stat__label"} -->
<p class="ws-stat__label">Years in practice</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"ws-stat"} -->
<div class="wp-block-column ws-stat"><!-- wp:heading {"level":3,"className":"ws-stat__number"} -->
<h3 class="wp-block-heading ws-stat__number">2</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ws-stat__label"} -->
<p class="ws-stat__label">Master\'s degrees in TCM</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"ws-stat"} -->
<div class="wp-block-column ws-stat"><!-- wp:heading {"level":3,"className":"ws-stat__number"} -->
<h3 class="wp-block-heading ws-stat__number">1,000+</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ws-stat__label"} -->
<p class="ws-stat__label">Patients treated</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
			)
		);

		/**
		 * 4. Pull quote — large stylized blockquote.
		 */
		register_block_pattern(
			'wellspring/pullquote',
			array(
				'title'       => __( 'Pull quote', 'wellspring' ),
				'description' => __( 'Large editorial-style quote with attribution.', 'wellspring' ),
				'categories'  => array( 'wellspring' ),
				'keywords'    => array( 'quote', 'pullquote', 'blockquote', 'editorial' ),
				'content'     => '<!-- wp:group {"className":"ws-pullquote","layout":{"type":"constrained"}} -->
<div class="wp-block-group ws-pullquote"><!-- wp:quote {"className":"ws-pullquote__inner"} -->
<blockquote class="wp-block-quote ws-pullquote__inner"><!-- wp:paragraph -->
<p>The body has its own intelligence. Our work is to listen carefully, then gently guide it back to balance.</p>
<!-- /wp:paragraph --><cite>Dr. Laura Cowburn</cite></blockquote>
<!-- /wp:quote --></div>
<!-- /wp:group -->',
			)
		);

		/**
		 * 5. Mini-CTA banner — small dark green CTA for mid-page conversion moments.
		 */
		register_block_pattern(
			'wellspring/mini-cta',
			array(
				'title'       => __( 'Mini CTA banner', 'wellspring' ),
				'description' => __( 'Compact dark-green banner for mid-page CTAs. Different from the global CTA at the bottom of every page.', 'wellspring' ),
				'categories'  => array( 'wellspring' ),
				'keywords'    => array( 'cta', 'banner', 'call to action', 'book' ),
				'content'     => '<!-- wp:group {"className":"ws-mini-cta","layout":{"type":"constrained"}} -->
<div class="wp-block-group ws-mini-cta"><!-- wp:heading {"level":3,"className":"ws-mini-cta__title"} -->
<h3 class="wp-block-heading ws-mini-cta__title">Ready to book?</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ws-mini-cta__lede"} -->
<p class="ws-mini-cta__lede">New patients welcome. Direct billing to most major insurers.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/book-appointments/">Book an appointment</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
			)
		);
	}
);
