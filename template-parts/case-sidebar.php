<?php
/**
 * Auto-generated sidebar for a single clinic case.
 *
 * Everything here is derived from the case's taxonomy terms and ACF meta —
 * nothing is hand-maintained per case. Renders inside the current loop.
 *
 * @package Wellspring
 */

$pid          = get_the_ID();
$focus_terms  = wp_get_post_terms( $pid, 'case_focus' );
$focus        = ( ! empty( $focus_terms ) && ! is_wp_error( $focus_terms ) ) ? $focus_terms[0] : null;
$modalities   = wp_get_post_terms( $pid, 'case_modality' );
$duration     = function_exists( 'get_field' ) ? get_field( 'duration' ) : '';
$sessions     = function_exists( 'get_field' ) ? get_field( 'sessions' ) : '';
$book_url     = home_url( '/book-appointments/' );

// Related cases in the same focus area (exclude the current one).
$related = array();
if ( $focus ) {
	$related = get_posts(
		array(
			'post_type'      => 'clinic_case',
			'posts_per_page' => 3,
			'post__not_in'   => array( $pid ),
			'orderby'        => 'rand',
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'case_focus',
					'field'    => 'term_id',
					'terms'    => $focus->term_id,
				),
			),
		)
	);
}
?>
<aside class="ws-case-side">

	<div class="ws-case-box ws-case-box--book">
		<h3 class="ws-case-box__title">Book for this concern</h3>
		<p class="ws-case-box__sub"><?php echo $focus ? esc_html( $focus->name ) : esc_html__( 'Acupuncture &amp; TCM', 'wellspring' ); ?></p>
		<a class="ws-btn ws-btn--solid ws-btn--block" href="<?php echo esc_url( $book_url ); ?>">Book an appointment</a>
		<a class="ws-btn ws-btn--ghost ws-btn--block" href="<?php echo esc_url( $book_url ); ?>">Free 15-min consult</a>
	</div>

	<?php if ( $duration || $sessions || ! empty( $modalities ) ) : ?>
		<div class="ws-case-box">
			<p class="ws-case-box__auto"><span aria-hidden="true">⚡</span> From this case</p>
			<h3 class="ws-case-box__title">Treatment at a glance</h3>
			<ul class="ws-case-facts">
				<?php if ( $duration ) : ?>
					<li><span>Duration</span><b><?php echo esc_html( $duration ); ?></b></li>
				<?php endif; ?>
				<?php if ( $sessions ) : ?>
					<li><span>Sessions</span><b><?php echo esc_html( $sessions ); ?></b></li>
				<?php endif; ?>
				<?php if ( ! empty( $modalities ) && ! is_wp_error( $modalities ) ) : ?>
					<li><span>Treatments</span><b><?php echo esc_html( implode( ', ', wp_list_pluck( $modalities, 'name' ) ) ); ?></b></li>
				<?php endif; ?>
				<?php if ( $focus ) : ?>
					<li><span>Focus area</span><b><?php echo esc_html( $focus->name ); ?></b></li>
				<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $related ) ) : ?>
		<div class="ws-case-box">
			<p class="ws-case-box__auto"><span aria-hidden="true">⚡</span> Same focus area</p>
			<h3 class="ws-case-box__title">Related cases</h3>
			<ul class="ws-case-rel">
				<?php foreach ( $related as $rel ) : ?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $rel->ID ) ); ?>">
							<span><?php echo esc_html( $rel->post_title ); ?></span>
							<span class="ws-case-rel__arr" aria-hidden="true">→</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $focus ) : ?>
				<a class="ws-case-box__more" href="<?php echo esc_url( get_term_link( $focus ) ); ?>">View all <?php echo esc_html( $focus->name ); ?> cases →</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $focus ) : ?>
		<div class="ws-case-box">
			<p class="ws-case-box__auto"><span aria-hidden="true">⚡</span> What we treat</p>
			<h3 class="ws-case-box__title">Explore <?php echo esc_html( $focus->name ); ?></h3>
			<p class="ws-case-box__sub">See how we approach this area and what to expect from treatment.</p>
			<a class="ws-btn ws-btn--ghost ws-btn--block" href="<?php echo esc_url( home_url( '/what-we-treat/' . $focus->slug . '/' ) ); ?>"><?php echo esc_html( $focus->name ); ?> →</a>
		</div>
	<?php endif; ?>

</aside>
