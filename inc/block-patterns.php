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
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/book/">Book a consult</a></div>
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
		 * 6. FAQ accordion — collapsible questions using native HTML5 details blocks.
		 */
		register_block_pattern(
			'wellspring/faq-accordion',
			array(
				'title'       => __( 'FAQ accordion', 'wellspring' ),
				'description' => __( 'Collapsible FAQ list. Click any question to expand. Native HTML5 details — no JavaScript required, fully accessible.', 'wellspring' ),
				'categories'  => array( 'wellspring' ),
				'keywords'    => array( 'faq', 'questions', 'accordion', 'details', 'frequently asked' ),
				'content'     => '<!-- wp:group {"className":"ws-faq","layout":{"type":"constrained"}} -->
<div class="wp-block-group ws-faq"><!-- wp:heading {"level":2,"className":"ws-faq__title"} -->
<h2 class="wp-block-heading ws-faq__title">Frequently asked questions</h2>
<!-- /wp:heading -->

<!-- wp:details {"summary":"How many acupuncture sessions do I need?"} -->
<details class="wp-block-details"><summary>How many acupuncture sessions do I need?</summary><!-- wp:paragraph -->
<p>The number of acupuncture treatments you need depends on your specific condition, its severity, and how your body responds. Treatment plans are personalized to your medical history, needs, and overall health. In some cases one treatment is enough to address acute pain; in others, you may benefit from 10 or more sessions over a course of treatment.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"Is acupuncture covered by insurance in Alberta?"} -->
<details class="wp-block-details"><summary>Is acupuncture covered by insurance in Alberta?</summary><!-- wp:paragraph -->
<p>Acupuncture is generally not covered by Alberta Health Care (provincial insurance). However, it is commonly covered under private extended health benefits or company group insurance plans, often listed under paramedical services. Coverage typically requires the practitioner to be a registered, licensed acupuncturist in Alberta — which we are.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"Does acupuncture hurt?"} -->
<details class="wp-block-details"><summary>Does acupuncture hurt?</summary><!-- wp:paragraph -->
<p>Most people don\'t report pain during acupuncture, though you may feel some sensations. What you feel depends on your pain tolerance and overall sensitivity. Your first treatment may seem more intense than the ones that follow — partly nerves, partly the body responding to needling for the first time. Most patients become more relaxed and comfortable with each subsequent session.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"How deep do the needles go?"} -->
<details class="wp-block-details"><summary>How deep do the needles go?</summary><!-- wp:paragraph -->
<p>Acupuncture needles generally penetrate from 1/16 of an inch to 1.5 inches (roughly 2mm to 40mm), depending on body area, muscle density, and treatment goals. Delicate areas like the scalp or face receive shallow insertions (1–5 mm), while fleshier areas like the hips or glutes can be treated more deeply.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"Are the needles sterilized or one-time use?"} -->
<details class="wp-block-details"><summary>Are the needles sterilized or one-time use?</summary><!-- wp:paragraph -->
<p>Both. In modern, regulated practices, acupuncture needles are treated as single-use medical devices: sterilized during manufacturing, opened from sealed packaging in front of you, and disposed of immediately after a single treatment.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"What are the needles made from?"} -->
<details class="wp-block-details"><summary>What are the needles made from?</summary><!-- wp:paragraph -->
<p>Modern acupuncture needles are made from high-quality, surgical-grade stainless steel — thin, flexible, hair-thin, and sterile. Handles are typically stainless steel, copper, or plastic, sometimes with a silicone coating for smoother insertion.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"How quickly does acupuncture and TCM work?"} -->
<details class="wp-block-details"><summary>How quickly does acupuncture and TCM work?</summary><!-- wp:paragraph -->
<p>Most patients see improvement within 1–2 sessions for acute conditions, and 2–3 sessions for chronic issues. Some feel relief immediately; others need a series of treatments to achieve lasting results. We\'ll talk through realistic expectations during your first appointment.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"What will my first appointment include?"} -->
<details class="wp-block-details"><summary>What will my first appointment include?</summary><!-- wp:paragraph -->
<p>Your first appointment typically lasts 50 minutes. It includes a comprehensive intake of your health history, a TCM-based pattern diagnosis, and a personalized treatment plan. Expect detailed questions about your sleep, diet, stress, and overall patterns — followed by acupuncture and, if appropriate, an herbal recommendation.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"summary":"How will I feel after treatment?"} -->
<details class="wp-block-details"><summary>How will I feel after treatment?</summary><!-- wp:paragraph -->
<p>Most patients feel a deep sense of relaxation after treatment. Some experience mild soreness at the needle sites or feel a bit tired — both subside within a day. Any mild bruising or discolouration usually clears within a few days. For the first 24 hours, rest, drink plenty of water, eat well, and avoid strenuous activity. Use heat packs rather than ice.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
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
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/book/">Book an appointment</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
			)
		);
	}
);
