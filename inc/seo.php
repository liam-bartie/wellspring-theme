<?php
/**
 * Theme-native SEO — titles, meta descriptions, Open Graph, Twitter cards,
 * schema, and the canonicals WordPress core does not emit.
 *
 * Deliberately no SEO plugin. Field definitions live in code so they ship with
 * the theme, stay in git, and can be bulk-loaded from migration data instead of
 * hand-typed into a plugin UI.
 *
 * WHAT CORE ALREADY DOES, AND WE LEAVE ALONE:
 *   - rel=canonical on singular pages/posts (rel_canonical)
 *   - the XML sitemap at /wp-sitemap.xml
 * Core does NOT emit canonicals on the posts page, post-type archives or term
 * archives, so those are added here.
 *
 * FALLBACK CHAINS
 *   Title:       SEO field  ->  core's "Page Title – Site Name"
 *   Description: SEO field  ->  page_subheading / case summary  ->  excerpt  ->  none
 * Body content is never scraped: the nine What We Treat sub-pages would end up
 * with near-identical boilerplate descriptions.
 *
 * MIGRATION SAFETY: every resolved value carries its source, so a fallback can
 * never silently masquerade as a ported value. wellspring_seo_resolve() returns
 * that source, the Tools screen shows it, and the verification script treats
 * "expected a ported value, got a fallback" as a failure rather than a match.
 *
 * @package Wellspring
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WELLSPRING_SEO_DESC_LIMIT = 160;

/* -------------------------------------------------------------------------
 * 1. Plugin guard
 *
 * If any SEO plugin is ever activated, this module goes completely silent so
 * the site can never emit two title tags or two descriptions.
 * ---------------------------------------------------------------------- */

/**
 * Is a recognised SEO plugin active?
 *
 * @return bool
 */
function wellspring_seo_plugin_active() {
	static $active = null;
	if ( null !== $active ) {
		return $active;
	}

	$active = false;

	$constants = array(
		'WPSEO_VERSION',             // Yoast SEO.
		'RANK_MATH_VERSION',         // Rank Math.
		'AIOSEO_VERSION',            // All in One SEO.
		'SEOPRESS_VERSION',          // SEOPress.
		'THE_SEO_FRAMEWORK_VERSION', // The SEO Framework.
		'SLIM_SEO_VERSION',          // Slim SEO.
		'SQ_VERSION',                // Squirrly.
	);
	foreach ( $constants as $constant ) {
		if ( defined( $constant ) ) {
			$active = true;
			return $active;
		}
	}

	$classes = array( 'WPSEO_Options', 'RankMath', 'AIOSEO\\Plugin\\AIOSEO', 'SEOPress' );
	foreach ( $classes as $class ) {
		if ( class_exists( $class ) ) {
			$active = true;
			return $active;
		}
	}

	return $active;
}

/**
 * Tell an admin the theme module has stood down, rather than leaving them to
 * wonder why the SEO box stopped having any effect.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! wellspring_seo_plugin_active() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>Wellspring SEO is dormant.</strong> ';
		echo 'An SEO plugin is active, so the theme has stopped emitting titles, meta ';
		echo 'descriptions, Open Graph tags and schema to avoid duplicates. The theme\'s ';
		echo 'stored SEO values are still in the database — deactivate the plugin to use ';
		echo 'them again, or migrate them into the plugin.</p></div>';
	}
);

/* -------------------------------------------------------------------------
 * 2. Fields
 * ---------------------------------------------------------------------- */

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) || wellspring_seo_plugin_active() ) {
			return;
		}

		$fields = array(
			array(
				'key'     => 'field_ws_seo_note',
				'label'   => 'About these fields',
				'type'    => 'message',
				'message' => 'Leave a field blank and the theme falls back automatically — the title becomes “Page name – Wellspring Health”, and the description uses this page’s sub-heading or excerpt. Fill a field in to override that. Values ported from the old site are exact copies and should not be edited until rankings have settled.',
			),
			array(
				'key'          => 'field_ws_seo_title',
				'name'         => 'seo_title',
				'label'        => 'Title tag',
				'instructions' => 'Google usually shows the first ~60 characters. Longer is allowed — a ported title is kept exactly as it was, even if it truncates.',
				'type'         => 'text',
				'maxlength'    => 300,
			),
			array(
				'key'          => 'field_ws_seo_description',
				'name'         => 'seo_description',
				'label'        => 'Meta description',
				'instructions' => 'Aim for 120–160 characters. This does not affect rankings directly — it is the snippet text, and Google may rewrite it anyway.',
				'type'         => 'textarea',
				'rows'         => 3,
				'maxlength'    => 500,
			),
			array(
				'key'           => 'field_ws_seo_og_image',
				'name'          => 'seo_og_image',
				'label'         => 'Social share image (optional)',
				'instructions'  => 'Used when the page is shared on Facebook, LinkedIn or X. Falls back to the featured image, then the site-wide default. 1200×630 is ideal.',
				'type'          => 'image',
				'return_format' => 'array',
				'preview_size'  => 'medium',
			),
			array(
				'key'           => 'field_ws_seo_noindex',
				'name'          => 'seo_noindex',
				'label'         => 'Hide from search engines',
				'instructions'  => 'Adds noindex. Use for thin or duplicate pages. This does not remove the page from the site or the menus.',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
			),
		);

		acf_add_local_field_group(
			array(
				'key'             => 'group_wellspring_seo',
				'title'           => 'SEO',
				'fields'          => $fields,
				'location'        => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'page',
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'post',
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'clinic_case',
						),
					),
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => 'case_focus',
						),
					),
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => 'case_symptom',
						),
					),
					array(
						array(
							'param'    => 'taxonomy',
							'operator' => '==',
							'value'    => 'case_modality',
						),
					),
				),
				'menu_order'      => 20,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'description'     => 'Title tag and meta description for this page. Blank = automatic.',
			)
		);
	}
);

/**
 * Customizer settings for the clinic-cases post-type archive, which has no post
 * to hang ACF fields on. Sits in the existing Clinic Cases section.
 */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		if ( wellspring_seo_plugin_active() ) {
			return;
		}

		$wp_customize->add_setting(
			'clinic_cases_seo_title',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'clinic_cases_seo_title',
			array(
				'label'       => __( 'SEO: title tag', 'wellspring' ),
				'description' => __( 'Archive title tag. Blank = automatic.', 'wellspring' ),
				'section'     => 'wellspring_clinic_cases',
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'clinic_cases_seo_description',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);
		$wp_customize->add_control(
			'clinic_cases_seo_description',
			array(
				'label'       => __( 'SEO: meta description', 'wellspring' ),
				'description' => __( 'Archive meta description. Blank = falls back to the hero sub-headline.', 'wellspring' ),
				'section'     => 'wellspring_clinic_cases',
				'type'        => 'textarea',
			)
		);
	}
);

/* -------------------------------------------------------------------------
 * 3. Helpers
 * ---------------------------------------------------------------------- */

/**
 * Normalise and length-limit description text on a word boundary.
 *
 * @param string $text  Raw text, may contain HTML.
 * @param int    $limit Character limit.
 * @return array{0:string,1:bool} Trimmed text, and whether it was truncated.
 */
function wellspring_seo_trim( $text, $limit = WELLSPRING_SEO_DESC_LIMIT ) {
	$text = wp_strip_all_tags( (string) $text, true );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );

	if ( '' === $text ) {
		return array( '', false );
	}
	if ( mb_strlen( $text ) <= $limit ) {
		return array( $text, false );
	}

	$cut   = mb_substr( $text, 0, $limit );
	$space = mb_strrpos( $cut, ' ' );
	if ( false !== $space && $space > (int) ( $limit * 0.5 ) ) {
		$cut = mb_substr( $cut, 0, $space );
	}

	return array( rtrim( $cut, " \t,;:.-–—" ) . '…', true );
}

/**
 * Read one of this module's fields for a given ACF target.
 *
 * @param string          $name   Field name.
 * @param int|string|null $target ACF post id / term identifier.
 * @return string|array|bool|null
 */
function wellspring_seo_field( $name, $target = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	return ( null === $target ) ? get_field( $name ) : get_field( $name, $target );
}

/**
 * Describe what is currently being viewed, and where its SEO values live.
 *
 * @return array{type:string,acf:int|string|null,object:mixed}
 */
function wellspring_seo_context() {
	if ( is_front_page() && is_home() ) {
		return array( 'type' => 'front_blog', 'acf' => null, 'object' => null );
	}
	if ( is_front_page() ) {
		$id = (int) get_option( 'page_on_front' );
		return array( 'type' => 'front', 'acf' => $id, 'object' => get_post( $id ) );
	}
	if ( is_home() ) {
		$id = (int) get_option( 'page_for_posts' );
		return array( 'type' => 'posts_page', 'acf' => $id ? $id : null, 'object' => $id ? get_post( $id ) : null );
	}
	if ( is_singular() ) {
		$id = get_queried_object_id();
		return array( 'type' => 'singular', 'acf' => $id, 'object' => get_post( $id ) );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return array(
				'type'   => 'term',
				'acf'    => $term->taxonomy . '_' . $term->term_id,
				'object' => $term,
			);
		}
	}
	if ( is_post_type_archive() ) {
		return array( 'type' => 'post_type_archive', 'acf' => null, 'object' => get_queried_object() );
	}
	if ( is_search() ) {
		return array( 'type' => 'search', 'acf' => null, 'object' => null );
	}
	if ( is_404() ) {
		return array( 'type' => 'not_found', 'acf' => null, 'object' => null );
	}
	return array( 'type' => 'other', 'acf' => null, 'object' => null );
}

/**
 * Resolve the SEO values for the current request, recording where each came
 * from so a fallback can never be mistaken for a ported value.
 *
 * @return array
 */
function wellspring_seo_resolve() {
	// Resolved up to four times per request (title filter, robots filter,
	// wp_head, and wp_get_document_title inside wp_head), and each pass hits
	// several get_field() calls. Compute once.
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$ctx = wellspring_seo_context();

	$out = array(
		'context'               => $ctx['type'],
		'title'                 => '',
		'title_source'          => 'core',
		'description'           => '',
		'description_source'    => 'none',
		'description_truncated' => false,
		'description_over_limit' => false,
		'noindex'               => false,
		'canonical'             => '',
		'og_url'                => '',
		'og_image'              => '',
	);

	$acf = $ctx['acf'];

	// --- Title: an explicit value is emitted verbatim, with no site-name suffix.
	$title = null;
	if ( 'post_type_archive' === $ctx['type'] && is_post_type_archive( 'clinic_case' ) ) {
		$title = get_theme_mod( 'clinic_cases_seo_title', '' );
	} elseif ( null !== $acf ) {
		$title = wellspring_seo_field( 'seo_title', $acf );
	}
	if ( is_string( $title ) && '' !== trim( $title ) ) {
		// Stored verbatim, not the trimmed copy: a ported title is byte-exact.
		$out['title']        = $title;
		$out['title_source'] = 'explicit';
	}

	// --- Description.
	$desc        = null;
	$desc_source = 'none';

	if ( 'post_type_archive' === $ctx['type'] && is_post_type_archive( 'clinic_case' ) ) {
		$desc = get_theme_mod( 'clinic_cases_seo_description', '' );
		if ( '' !== trim( (string) $desc ) ) {
			$desc_source = 'explicit';
		} else {
			$desc        = get_theme_mod( 'clinic_cases_lede', '' );
			$desc_source = '' !== trim( (string) $desc ) ? 'archive_lede' : 'none';
		}
	} elseif ( null !== $acf ) {
		$explicit = wellspring_seo_field( 'seo_description', $acf );
		if ( is_string( $explicit ) && '' !== trim( $explicit ) ) {
			$desc        = $explicit;
			$desc_source = 'explicit';
		} else {
			// Fallback chain, in order of editorial quality.
			$candidates = array();

			if ( $ctx['object'] instanceof WP_Post ) {
				$post = $ctx['object'];
				if ( 'clinic_case' === $post->post_type ) {
					$candidates[] = array( 'case_summary', wellspring_seo_field( 'summary', $post->ID ) );
				} else {
					$candidates[] = array( 'page_subheading', wellspring_seo_field( 'page_subheading', $post->ID ) );
				}
				$candidates[] = array( 'excerpt', $post->post_excerpt );
			} elseif ( $ctx['object'] instanceof WP_Term ) {
				$candidates[] = array( 'term_description', $ctx['object']->description );
			}

			foreach ( $candidates as $candidate ) {
				list( $source, $value ) = $candidate;
				if ( is_string( $value ) && '' !== trim( wp_strip_all_tags( $value ) ) ) {
					$desc        = $value;
					$desc_source = $source;
					break;
				}
			}
		}
	}

	if ( is_string( $desc ) && '' !== trim( $desc ) ) {
		if ( 'explicit' === $desc_source ) {
			/*
			 * An explicit value is emitted byte-for-byte: no whitespace
			 * collapsing, no truncation, however long it is. This matters more
			 * than it looks — PHP's \s under /u matches U+00A0, so normalising
			 * here would silently turn a ported no-break space into an ordinary
			 * one and break the byte-exact guarantee. Over-length values are
			 * reported by the Tools screen, not quietly shortened.
			 */
			$out['description']           = $desc;
			$out['description_source']    = 'explicit';
			$out['description_truncated'] = false;
			$out['description_over_limit'] = ( mb_strlen( wp_strip_all_tags( $desc ) ) > WELLSPRING_SEO_DESC_LIMIT );
		} else {
			// Fallback text is derived, not authored, so it is safe to tidy.
			list( $trimmed, $truncated )  = wellspring_seo_trim( $desc );
			$out['description']           = $trimmed;
			$out['description_source']    = $desc_source;
			$out['description_truncated'] = $truncated;
		}
	}

	// --- noindex.
	if ( null !== $acf && wellspring_seo_field( 'seo_noindex', $acf ) ) {
		$out['noindex'] = true;
	}
	if ( in_array( $ctx['type'], array( 'search', 'not_found' ), true ) ) {
		$out['noindex'] = true;
	}

	/*
	 * Canonical vs og:url.
	 *
	 * 'canonical' is only populated where we should EMIT a <link rel=canonical>,
	 * i.e. the views core's rel_canonical() skips. 'og_url' is populated for
	 * every view, because og:url always needs a value — deriving it from
	 * REQUEST_URI would double the path on a subdirectory install.
	 */
	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ), 1 );

	switch ( $ctx['type'] ) {
		case 'front':
		case 'front_blog':
			$out['canonical'] = ( 'front_blog' === $ctx['type'] ) ? home_url( '/' ) : '';
			$out['og_url']    = home_url( '/' );
			break;
		case 'posts_page':
			$url              = (string) get_permalink( (int) get_option( 'page_for_posts' ) );
			$out['canonical'] = $url;
			$out['og_url']    = $url;
			break;
		case 'singular':
			// Core emits the canonical here; we only need og:url.
			$out['og_url'] = (string) get_permalink( $ctx['object'] );
			break;
		case 'term':
			$link             = get_term_link( $ctx['object'] );
			$url              = is_wp_error( $link ) ? '' : (string) $link;
			$out['canonical'] = $url;
			$out['og_url']    = $url;
			break;
		case 'post_type_archive':
			$pt = get_query_var( 'post_type' );
			if ( is_array( $pt ) ) {
				$pt = reset( $pt );
			}
			$url              = $pt ? (string) get_post_type_archive_link( $pt ) : '';
			$out['canonical'] = $url;
			$out['og_url']    = $url;
			break;
		default:
			$out['og_url'] = home_url( '/' );
			break;
	}

	/*
	 * Paged views must be self-referential, not point back at page 1. Let
	 * WordPress build the URL — hand-appending "page/N/" would emit a trailing
	 * slash even once the permalink structure drops them.
	 */
	if ( $paged > 1 && 'singular' !== $ctx['type'] ) {
		$paged_url = (string) get_pagenum_link( $paged );
		if ( $paged_url ) {
			if ( $out['canonical'] ) {
				$out['canonical'] = $paged_url;
			}
			$out['og_url'] = $paged_url;
		}
	}

	// --- Share image.
	$image = ( null !== $acf ) ? wellspring_seo_field( 'seo_og_image', $acf ) : null;
	if ( is_array( $image ) && ! empty( $image['url'] ) ) {
		$out['og_image'] = $image['url'];
	} elseif ( $ctx['object'] instanceof WP_Post && has_post_thumbnail( $ctx['object'] ) ) {
		$out['og_image'] = (string) get_the_post_thumbnail_url( $ctx['object'], 'large' );
	}

	/**
	 * Filter the resolved SEO values.
	 *
	 * @param array $out Resolved values.
	 * @param array $ctx Context descriptor.
	 */
	$cache = apply_filters( 'wellspring_seo_resolved', $out, $ctx );

	return $cache;
}

/* -------------------------------------------------------------------------
 * 4. Output
 * ---------------------------------------------------------------------- */

/**
 * Emit an explicit title verbatim. Returning the untouched value lets core
 * build its usual "Page Title – Site Name" instead.
 *
 * @param string $title Incoming title.
 * @return string
 */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( wellspring_seo_plugin_active() || is_admin() || is_feed() ) {
			return $title;
		}
		$seo = wellspring_seo_resolve();
		return ( 'explicit' === $seo['title_source'] ) ? $seo['title'] : $title;
	}
);

add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( wellspring_seo_plugin_active() ) {
			return $robots;
		}
		$seo = wellspring_seo_resolve();
		if ( $seo['noindex'] ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = false;
			unset( $robots['index'] );
		}
		return $robots;
	}
);

add_action(
	'wp_head',
	function () {
		if ( wellspring_seo_plugin_active() || is_feed() ) {
			return;
		}

		$seo   = wellspring_seo_resolve();
		$title = wp_get_document_title();
		$site  = get_bloginfo( 'name' );

		echo "\n<!-- Wellspring SEO -->\n";

		if ( $seo['description'] ) {
			printf(
				"<meta name=\"description\" content=\"%s\">\n",
				esc_attr( $seo['description'] )
			);
		}

		// Core omits canonicals outside singular views.
		if ( $seo['canonical'] ) {
			printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $seo['canonical'] ) );
		}

		// --- Open Graph.
		$og_type = ( is_singular() && ! is_front_page() ) ? 'article' : 'website';
		$og_url  = $seo['og_url'];

		printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( $site ) );
		printf( "<meta property=\"og:type\" content=\"%s\">\n", esc_attr( $og_type ) );
		printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
		if ( $og_url ) {
			printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $og_url ) );
		}
		printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) );
		if ( $seo['description'] ) {
			printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $seo['description'] ) );
		}
		if ( $seo['og_image'] ) {
			printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $seo['og_image'] ) );
		}

		// --- Twitter. summary_large_image only when there is actually an image.
		printf(
			"<meta name=\"twitter:card\" content=\"%s\">\n",
			esc_attr( $seo['og_image'] ? 'summary_large_image' : 'summary' )
		);
		printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $title ) );
		if ( $seo['description'] ) {
			printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $seo['description'] ) );
		}
		if ( $seo['og_image'] ) {
			printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $seo['og_image'] ) );
		}

		wellspring_seo_schema( $seo, $title );

		echo "<!-- /Wellspring SEO -->\n\n";
	},
	2
);

/**
 * Structured data, limited to what can be derived from WordPress without
 * inventing facts.
 *
 * NOT emitted here: LocalBusiness / MedicalBusiness. That needs a verified
 * postal address, phone, opening hours and geo coordinates — wrong values in
 * structured data are worse than none, so those are supplied deliberately
 * rather than scraped off the contact page.
 *
 * @param array  $seo   Resolved values.
 * @param string $title Document title.
 */
function wellspring_seo_schema( $seo, $title ) {
	$graph = array();

	if ( is_front_page() ) {
		$graph[] = array(
			'@type'       => 'WebSite',
			'@id'         => home_url( '/#website' ),
			'url'         => home_url( '/' ),
			'name'        => get_bloginfo( 'name' ),
			'description' => $seo['description'] ? $seo['description'] : get_bloginfo( 'description' ),
			'inLanguage'  => get_bloginfo( 'language' ),
		);
	} elseif ( is_singular() ) {
		$post  = get_post();
		$crumb = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => __( 'Home', 'wellspring' ),
				'item'     => home_url( '/' ),
			),
		);

		$ancestors = ( $post instanceof WP_Post && 'page' === $post->post_type )
			? array_reverse( get_post_ancestors( $post ) )
			: array();
		$position = 2;
		foreach ( $ancestors as $ancestor_id ) {
			$crumb[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => get_the_title( $ancestor_id ),
				'item'     => get_permalink( $ancestor_id ),
			);
		}
		$crumb[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => get_the_title( $post ),
		);

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $crumb,
		);

		if ( $post instanceof WP_Post && 'clinic_case' === $post->post_type ) {
			$graph[] = array(
				'@type'    => 'MedicalWebPage',
				'@id'      => get_permalink( $post ) . '#webpage',
				'url'      => get_permalink( $post ),
				'name'     => get_the_title( $post ),
				'headline' => get_the_title( $post ),
				'datePublished' => get_the_date( DATE_W3C, $post ),
				'dateModified'  => get_the_modified_date( DATE_W3C, $post ),
			);
		}
	}

	if ( ! $graph ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
