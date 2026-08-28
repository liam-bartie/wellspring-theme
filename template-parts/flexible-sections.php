<?php
/**
 * Renders the ACF "Page sections" flexible-content field for the current post.
 *
 * Supported layouts: text, heading, image_text, map, text_map, faq.
 *
 * Each section is emitted as its own full-width <section> band so it can carry
 * a background colour (No background / Light green / Beige) that spans the
 * browser width, matching the tinted bands on the home page. Callers must
 * therefore place this OUTSIDE the narrow container — each band opens its own.
 *
 * @package Wellspring
 */

if ( ! function_exists( 'have_rows' ) || ! have_rows( 'page_sections' ) ) {
	return;
}

/*
 * Optional position filter.
 *
 * On inner pages this part is called once with no position, and every section
 * renders in the order the editor arranged them. The home page has nine built-in
 * sections, so front-page.php calls this part once per gap between them, passing
 * the anchor name — each call renders only the sections assigned to that gap.
 * Rows saved before the field existed have no position and fall back to
 * 'before_cta', which is where a single insertion point would have put them.
 */
$ws_position = isset( $args['position'] ) ? (string) $args['position'] : '';

/*
 * Layouts that emit their own complete <section>.
 *
 * The home blocks are rendered by template-parts/home/*.php — the very same
 * files front-page.php uses — so their markup already carries its own band,
 * container and classes. Wrapping them again would nest a section inside a
 * section and change the rendered HTML, which is exactly what the migration
 * must not do. These rows are echoed straight out and never get a background
 * modifier, because the block's own class already sets its colour.
 */
$ws_selfcontained = array(
	'home_intro',
	'home_wwt',
	'home_practitioner',
	'home_modalities',
	'home_cases',
	'home_reviews',
);

// Fallback for the "View on Google Maps" link when a map section leaves it blank.
$ws_maps_default = 'https://www.google.com/maps/place/Wellspring+Health+Acupuncture+%26+TCM+Clinic/data=!4m2!3m1!1s0x0:0x8039f60c08965bb1?sa=X&ved=1t:2428&ictx=111';

// Background choices an editor can pick, mapped to their band modifier.
$ws_backgrounds = array( 'none', 'mist', 'paper' );


while ( have_rows( 'page_sections' ) ) :
	the_row();
	$layout = get_row_layout();

	/*
	 * The home blocks never render through a positional pass.
	 *
	 * front-page.php currently calls this part once per gap between its
	 * built-in sections. A migrated home block has no 'position' sub-field, so
	 * without this guard it would fall through to the 'before_cta' default and
	 * render a second copy of the whole page. Once front-page.php switches to a
	 * single ordered pass (no position argument) this guard stops applying and
	 * the blocks render in their arranged order.
	 */
	if ( '' !== $ws_position && in_array( $layout, $ws_selfcontained, true ) ) {
		continue;
	}

	if ( '' !== $ws_position ) {
		$row_position = (string) get_sub_field( 'position' );
		if ( '' === $row_position ) {
			$row_position = 'before_cta';
		}
		if ( $row_position !== $ws_position ) {
			continue;
		}
	}

	$ws_bg = (string) get_sub_field( 'background' );
	if ( ! in_array( $ws_bg, $ws_backgrounds, true ) ) {
		$ws_bg = 'none';
	}

	/*
	 * A card grid needs the full container. Everything else is running copy and
	 * stays in the narrow measure, which is what keeps it readable.
	 */
	$ws_container = ( 'cases' === $layout ) ? 'ws-container' : 'ws-container ws-container--narrow';

	// Buffer the layout first. A row that renders nothing (a Map with no
	// address, an empty Text block) must not leave a coloured band behind.
	ob_start();
	switch ( $layout ) :

		case 'text':
			echo wp_kses_post( get_sub_field( 'body' ) );
			break;

		case 'heading':
			$eyebrow = get_sub_field( 'eyebrow' );
			$heading = get_sub_field( 'heading' );
			if ( ! $eyebrow && ! $heading ) {
				break;
			}
			?>
			<div class="ws-flex-heading">
				<?php if ( $eyebrow ) : ?>
					<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
			</div>
			<?php
			break;

		case 'image_text':
			$image   = get_sub_field( 'image' );
			$side    = get_sub_field( 'image_side' );
			$eyebrow = get_sub_field( 'eyebrow' );
			$heading = get_sub_field( 'heading' );
			$body    = get_sub_field( 'body' );
			$flip    = ( 'right' === $side ) ? ' ws-flex-imagetext--right' : '';
			if ( ! $image && ! $eyebrow && ! $heading && ! $body ) {
				break;
			}
			?>
			<div class="ws-flex-imagetext<?php echo esc_attr( $flip ); ?>">
				<?php if ( $image && ! empty( $image['url'] ) ) : ?>
					<div class="ws-flex-imagetext__media">
						<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" />
					</div>
				<?php endif; ?>
				<div class="ws-flex-imagetext__text">
					<?php if ( $eyebrow ) : ?>
						<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<?php endif; ?>
					<?php if ( $heading ) : ?>
						<h2><?php echo esc_html( $heading ); ?></h2>
					<?php endif; ?>
					<?php echo wp_kses_post( $body ); ?>
				</div>
			</div>
			<?php
			break;

		case 'map':
			$map_address = get_sub_field( 'address' );
			if ( $map_address ) :
				$map_src = 'https://www.google.com/maps?q=' . rawurlencode( $map_address ) . '&output=embed';
				?>
				<div class="ws-map">
					<iframe src="<?php echo esc_url( $map_src ); ?>" title="<?php echo esc_attr( $map_address ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				</div>
				<?php $map_link = get_sub_field( 'map_link' ) ? get_sub_field( 'map_link' ) : $ws_maps_default; ?>
				<p class="ws-map__link"><a href="<?php echo esc_url( $map_link ); ?>" target="_blank" rel="noopener">View on Google Maps <span aria-hidden="true">&rarr;</span></a></p>
				<?php
			endif;
			break;

		case 'text_map':
			$tm_side    = get_sub_field( 'map_side' );
			$tm_heading = get_sub_field( 'heading' );
			$tm_body    = get_sub_field( 'body' );
			$tm_address = get_sub_field( 'address' );
			$tm_flip    = ( 'left' === $tm_side ) ? ' ws-flex-textmap--map-left' : '';
			if ( ! $tm_heading && ! $tm_body && ! $tm_address ) {
				break;
			}
			?>
			<div class="ws-flex-textmap<?php echo esc_attr( $tm_flip ); ?>">
				<div class="ws-flex-textmap__text">
					<?php if ( $tm_heading ) : ?>
						<h2><?php echo esc_html( $tm_heading ); ?></h2>
					<?php endif; ?>
					<?php echo wp_kses_post( $tm_body ); ?>
				</div>
				<?php if ( $tm_address ) : ?>
					<div class="ws-flex-textmap__map">
						<iframe src="<?php echo esc_url( 'https://www.google.com/maps?q=' . rawurlencode( $tm_address ) . '&output=embed' ); ?>" title="<?php echo esc_attr( $tm_address ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
						<?php $tm_link = get_sub_field( 'map_link' ) ? get_sub_field( 'map_link' ) : $ws_maps_default; ?>
						<p class="ws-map__link"><a href="<?php echo esc_url( $tm_link ); ?>" target="_blank" rel="noopener">View on Google Maps <span aria-hidden="true">&rarr;</span></a></p>
					</div>
				<?php endif; ?>
			</div>
			<?php
			break;

		case 'cases':
			if ( ! function_exists( 'wellspring_render_related_cases' ) ) {
				break;
			}
			$c_tax = function_exists( 'wellspring_normalize_case_taxonomy' )
				? wellspring_normalize_case_taxonomy( (string) get_sub_field( 'taxonomy' ) )
				: 'case_focus';

			switch ( $c_tax ) {
				case 'case_symptom':
					$c_term = (string) get_sub_field( 'symptom' );
					break;
				case 'case_modality':
					$c_term = (string) get_sub_field( 'modality' );
					break;
				default:
					// Focus areas support "auto", meaning this page's own slug.
					$c_term = (string) get_sub_field( 'focus' );
					if ( '' === $c_term || 'auto' === $c_term ) {
						$c_term = (string) get_post_field( 'post_name', get_the_ID() );
					}
					break;
			}

			if ( '' === $c_term ) {
				break;
			}

			$c_limit   = (int) get_sub_field( 'limit' );
			$c_orderby = (string) get_sub_field( 'orderby' );

			// Returns '' when nothing matches, so the band self-suppresses.
			echo wellspring_render_related_cases( // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- pre-escaped markup.
				$c_term,
				$c_limit ? $c_limit : 3,
				(string) get_sub_field( 'heading' ),
				$c_orderby ? $c_orderby : 'rand',
				true,
				$c_tax
			);
			break;

		case 'faq':
			$faq_heading = get_sub_field( 'heading' );
			$faq_items   = (array) get_sub_field( 'items' );
			if ( ! $faq_heading && ! $faq_items ) {
				break;
			}
			?>
			<div class="ws-flex-faq">
				<?php if ( $faq_heading ) : ?>
					<h2><?php echo esc_html( $faq_heading ); ?></h2>
				<?php endif; ?>
				<?php
				foreach ( $faq_items as $faq_item ) :
					$q = isset( $faq_item['question'] ) ? $faq_item['question'] : '';
					$a = isset( $faq_item['answer'] ) ? $faq_item['answer'] : '';
					if ( ! $q ) {
						continue;
					}
					?>
					<details class="wp-block-details"><summary><?php echo esc_html( $q ); ?></summary><?php echo wp_kses_post( $a ); ?></details>
					<?php
				endforeach;
				?>
			</div>
			<?php
			break;

		case 'home_intro':
			get_template_part(
				'template-parts/home/intro',
				null,
				array(
					'eyebrow' => get_sub_field( 'eyebrow' ),
					'title'   => get_sub_field( 'title' ),
					'body'    => get_sub_field( 'body' ),
				)
			);
			break;

		case 'home_wwt':
			get_template_part(
				'template-parts/home/what-we-treat',
				null,
				array(
					'eyebrow'  => get_sub_field( 'eyebrow' ),
					'title'    => get_sub_field( 'title' ),
					'lede'     => get_sub_field( 'lede' ),
					'subpages' => wellspring_wwt_subpages(),
				)
			);
			break;

		case 'home_practitioner':
			get_template_part(
				'template-parts/home/practitioner',
				null,
				array(
					'eyebrow'     => get_sub_field( 'eyebrow' ),
					'name'        => get_sub_field( 'name' ),
					'credentials' => get_sub_field( 'credentials' ),
					'bio'         => get_sub_field( 'bio' ),
					'link_label'  => get_sub_field( 'link_label' ),
					'link_url'    => get_sub_field( 'link_url' ),
					'portrait'    => get_sub_field( 'portrait' ),
				)
			);
			break;

		case 'home_modalities':
			get_template_part(
				'template-parts/home/modalities',
				null,
				array(
					'eyebrow'   => get_sub_field( 'eyebrow' ),
					'title'     => get_sub_field( 'title' ),
					'tcm_title' => get_sub_field( 'tcm_title' ),
					'tcm_body'  => get_sub_field( 'tcm_body' ),
					'tcm_image' => get_sub_field( 'tcm_image' ),
					'acu_title' => get_sub_field( 'acu_title' ),
					'acu_body'  => get_sub_field( 'acu_body' ),
					'acu_image' => get_sub_field( 'acu_image' ),
				)
			);
			break;

		case 'home_cases':
			get_template_part(
				'template-parts/home/featured-cases',
				null,
				array(
					'eyebrow' => get_sub_field( 'eyebrow' ),
					'title'   => get_sub_field( 'title' ),
					'lede'    => get_sub_field( 'lede' ),
					// Falls back to the three most recent when none are chosen,
					// matching what front-page.php has always done.
					'cases'   => wellspring_home_featured_cases( get_sub_field( 'cases' ) ),
				)
			);
			break;

		case 'home_reviews':
			get_template_part( 'template-parts/reviews-slider' );
			break;

	endswitch;
	$ws_inner = trim( (string) ob_get_clean() );

	if ( '' === $ws_inner ) {
		continue;
	}

	if ( in_array( $layout, $ws_selfcontained, true ) ) {
		echo $ws_inner; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- escaped as it was built above.
		continue;
	}
	?>
	<section class="ws-flex-section ws-flex-section--<?php echo esc_attr( $ws_bg ); ?>">
		<div class="<?php echo esc_attr( $ws_container ); ?>">
			<div class="entry-content">
				<?php echo $ws_inner; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- escaped as it was built above. ?>
			</div>
		</div>
	</section>
	<?php
endwhile;
