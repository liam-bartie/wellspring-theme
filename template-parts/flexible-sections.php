<?php
/**
 * Renders the ACF "Page sections" flexible-content field for the current post.
 *
 * Supported layouts: text, heading, image_text, faq. Called from page.php
 * (and reusable on any template) inside an .entry-content wrapper.
 *
 * @package Wellspring
 */

if ( ! function_exists( 'have_rows' ) || ! have_rows( 'page_sections' ) ) {
	return;
}

// Fallback for the "View on Google Maps" link when a map section leaves it blank.
$ws_maps_default = 'https://www.google.com/maps/place/Wellspring+Health+Acupuncture+%26+TCM+Clinic/data=!4m2!3m1!1s0x0:0x8039f60c08965bb1?sa=X&ved=1t:2428&ictx=111';

while ( have_rows( 'page_sections' ) ) :
	the_row();
	$layout = get_row_layout();

	switch ( $layout ) :

		case 'text':
			echo wp_kses_post( get_sub_field( 'body' ) );
			break;

		case 'heading':
			$eyebrow = get_sub_field( 'eyebrow' );
			$heading = get_sub_field( 'heading' );
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

		case 'faq':
			$faq_heading = get_sub_field( 'heading' );
			?>
			<div class="ws-flex-faq">
				<?php if ( $faq_heading ) : ?>
					<h2><?php echo esc_html( $faq_heading ); ?></h2>
				<?php endif; ?>
				<?php
				if ( have_rows( 'items' ) ) :
					while ( have_rows( 'items' ) ) :
						the_row();
						$q = get_sub_field( 'question' );
						$a = get_sub_field( 'answer' );
						if ( ! $q ) {
							continue;
						}
						?>
						<details class="wp-block-details"><summary><?php echo esc_html( $q ); ?></summary><?php echo wp_kses_post( $a ); ?></details>
						<?php
					endwhile;
				endif;
				?>
			</div>
			<?php
			break;

	endswitch;
endwhile;
