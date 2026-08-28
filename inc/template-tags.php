<?php
/**
 * Custom template tags for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Wellspring
 */

if ( ! function_exists( 'ws_google_g_svg' ) ) :
	/**
	 * Returns the multi-colour Google "G" logo as inline SVG.
	 *
	 * @param int $size Width/height in pixels.
	 * @return string
	 */
	function ws_google_g_svg( $size = 18 ) {
		$size = (int) $size;
		return sprintf(
			'<svg class="ws-g" viewBox="0 0 24 24" width="%1$d" height="%1$d" aria-hidden="true" focusable="false"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>',
			$size
		);
	}
endif;

if ( ! function_exists( 'ws_star_rating_html' ) ) :
	/**
	 * Returns a star-rating row. A grey base of five stars with a gold
	 * overlay clipped to the rating width, so fractional scores render
	 * accurately (e.g. 4.9).
	 *
	 * @param float $rating Rating out of 5.
	 * @return string
	 */
	function ws_star_rating_html( $rating ) {
		$rating = (float) $rating;
		$pct    = max( 0, min( 100, ( $rating / 5 ) * 100 ) );
		$label  = rtrim( rtrim( number_format( $rating, 1 ), '0' ), '.' ) . ' out of 5 stars';
		return '<span class="ws-stars" role="img" aria-label="' . esc_attr( $label ) . '">'
			. '<span class="ws-stars__base" aria-hidden="true">★★★★★</span>'
			. '<span class="ws-stars__fill" aria-hidden="true" style="width:' . esc_attr( $pct ) . '%;">★★★★★</span>'
			. '</span>';
	}
endif;

if ( ! function_exists( 'wellspring_posted_on' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time.
	 */
	function wellspring_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'wellspring' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

if ( ! function_exists( 'wellspring_posted_by' ) ) :
	/**
	 * Prints HTML with meta information for the current author.
	 */
	function wellspring_posted_by() {
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'wellspring' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;

if ( ! function_exists( 'wellspring_entry_footer' ) ) :
	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function wellspring_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'wellspring' ) );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'wellspring' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'wellspring' ) );
			if ( $tags_list ) {
				/* translators: 1: list of tags. */
				printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'wellspring' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'wellspring' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'wellspring' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			),
			'<span class="edit-link">',
			'</span>'
		);
	}
endif;

if ( ! function_exists( 'wellspring_post_thumbnail' ) ) :
	/**
	 * Displays an optional post thumbnail.
	 *
	 * Wraps the post thumbnail in an anchor element on index views, or a div
	 * element when on single views.
	 */
	function wellspring_post_thumbnail() {
		if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
			return;
		}

		if ( is_singular() ) :
			?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div><!-- .post-thumbnail -->

		<?php else : ?>

			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
					the_post_thumbnail(
						'post-thumbnail',
						array(
							'alt' => the_title_attribute(
								array(
									'echo' => false,
								)
							),
						)
					);
				?>
			</a>

			<?php
		endif; // End is_singular().
	}
endif;

if ( ! function_exists( 'wp_body_open' ) ) :
	/**
	 * Shim for sites older than 5.2.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12563
	 */
	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
endif;

if ( ! function_exists( 'wellspring_page_url' ) ) :
	/**
	 * Internal link that survives a permalink-structure change.
	 *
	 * Hardcoding "/about/" breaks the day the structure drops trailing slashes,
	 * and hardcoding "/about" breaks today — every such link then 301s. Resolve
	 * the page and let WordPress build the URL instead: get_permalink() always
	 * returns the shape the current structure actually uses.
	 *
	 * Falls back to user_trailingslashit(), which applies the site's own slash
	 * policy, so even a path with no matching page comes out the right shape.
	 *
	 * @param string $path Page path, e.g. 'about' or 'what-we-treat/pain-relief'.
	 * @return string Absolute URL.
	 */
	function wellspring_page_url( $path ) {
		$path = trim( (string) $path, '/' );
		if ( '' === $path ) {
			return home_url( '/' );
		}

		$page = get_page_by_path( $path );
		if ( $page instanceof WP_Post ) {
			$url = get_permalink( $page );
			if ( $url ) {
				return $url;
			}
		}

		return home_url( user_trailingslashit( $path ) );
	}
endif;

if ( ! function_exists( 'wellspring_wwt_subpages' ) ) :
	/**
	 * The "What We Treat" sub-pages, in the order the home page shows them.
	 *
	 * Extracted from front-page.php so the home block can be rendered either
	 * from the page's top-level fields or from a "Page sections" row and get
	 * the identical list either way.
	 *
	 * Order: the manual "Tile order" relationship if an editor has set one,
	 * otherwise every sub-page in menu order.
	 *
	 * @return array WP_Post objects.
	 */
	function wellspring_wwt_subpages() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$ordered = function_exists( 'ws_field' ) ? ws_field( 'home_wwt_order', array() ) : array();

		if ( ! empty( $ordered ) && is_array( $ordered ) ) {
			$cache = array_filter( array_map( 'get_post', $ordered ) );
			return $cache;
		}

		$wwt_page = get_page_by_path( 'what-we-treat' );

		$cache = $wwt_page ? get_children(
			array(
				'post_parent' => $wwt_page->ID,
				'post_type'   => 'page',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'numberposts' => -1,
			)
		) : array();

		return $cache;
	}
endif;

if ( ! function_exists( 'wellspring_page_h1' ) ) :
	/**
	 * The heading to render as this page's H1.
	 *
	 * Deliberately separate from the page title. The title is reused as the
	 * label in the Pages list, the parent dropdown, breadcrumb markup, sibling
	 * "Also explore" links and search results — so making it long to improve
	 * one H1 degrades all of those. This lets the title stay short ("About")
	 * while the visible heading says whatever it needs to.
	 *
	 * Falls back to the page title, so a page with nothing entered behaves
	 * exactly as it did before this existed.
	 *
	 * @param int|null $post_id Optional post ID; defaults to the current post.
	 * @return string Heading text, unescaped.
	 */
	function wellspring_page_h1( $post_id = null ) {
		$post_id = $post_id ? (int) $post_id : get_the_ID();

		if ( function_exists( 'get_field' ) ) {
			$override = trim( (string) get_field( 'page_h1', $post_id ) );
			if ( '' !== $override ) {
				return $override;
			}
		}

		return get_the_title( $post_id );
	}
endif;
