<?php
/**
 * Single Clinic Case template.
 *
 * Renders an individual case as a clinical chart — labelled sections
 * for Presentation / Diagnosis / Treatment / Result, plus optional
 * duration and session count metadata.
 *
 * @package Wellspring
 */

get_header();

while ( have_posts() ) :
	the_post();

	$focus_areas = wp_get_post_terms( get_the_ID(), 'case_focus' );
	$initial     = function_exists( 'get_field' ) ? get_field( 'patient_initial' ) : '';
	$context     = function_exists( 'get_field' ) ? get_field( 'patient_context' ) : '';
	$presentation = function_exists( 'get_field' ) ? get_field( 'presentation' ) : '';
	$diagnosis   = function_exists( 'get_field' ) ? get_field( 'diagnosis' ) : '';
	$treatment   = function_exists( 'get_field' ) ? get_field( 'treatment' ) : '';
	$result      = function_exists( 'get_field' ) ? get_field( 'result' ) : '';
	$duration    = function_exists( 'get_field' ) ? get_field( 'duration' ) : '';
	$sessions    = function_exists( 'get_field' ) ? get_field( 'sessions' ) : '';
	$summary     = function_exists( 'get_field' ) ? get_field( 'summary' ) : '';
	$case_notes  = function_exists( 'get_field' ) ? get_field( 'case_notes' ) : '';
	$modalities  = wp_get_post_terms( get_the_ID(), 'case_modality' );
	$symptoms    = wp_get_post_terms( get_the_ID(), 'case_symptom' );
	?>

<main id="primary" class="site-main">

	<?php
	$case_has_thumb = has_post_thumbnail();
	$case_thumb_url = $case_has_thumb ? get_the_post_thumbnail_url( get_the_ID(), 'wellspring-hero' ) : '';
	$case_ph_class  = $case_has_thumb ? 'ws-page-header ws-page-header--imaged' : 'ws-page-header';
	?>
	<section class="<?php echo esc_attr( $case_ph_class ); ?>">
		<?php if ( $case_has_thumb ) : ?>
			<div class="ws-page-header__bg" style="background-image: url('<?php echo esc_url( $case_thumb_url ); ?>');" aria-hidden="true"></div>
			<div class="ws-page-header__overlay" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<p class="eyebrow ws-page-header__crumb">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>">Clinic cases</a>
			</p>
			<h1 class="ws-page-header__title"><?php the_title(); ?></h1>
			<?php if ( $initial || $context ) : ?>
				<p class="ws-page-header__lede">
					<?php echo esc_html( $initial ?: 'Patient' ); ?>
					<?php if ( $context ) : ?>&middot; <?php echo esc_html( $context ); ?><?php endif; ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<section class="ws-page-body">
		<div class="ws-container ws-case-layout">
			<article class="ws-case-detail">

				<?php if ( ( ! empty( $focus_areas ) && ! is_wp_error( $focus_areas ) ) || ( ! empty( $symptoms ) && ! is_wp_error( $symptoms ) ) ) : ?>
					<div class="ws-case-detail__tags">
						<?php if ( ! empty( $focus_areas ) && ! is_wp_error( $focus_areas ) ) : ?>
							<?php foreach ( $focus_areas as $term ) : ?>
								<a class="ws-case-detail__tag" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php if ( ! empty( $symptoms ) && ! is_wp_error( $symptoms ) ) : ?>
							<?php foreach ( $symptoms as $term ) : ?>
								<span class="ws-case-detail__tag ws-case-detail__tag--static"><?php echo esc_html( $term->name ); ?></span>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $summary ) : ?>
					<div class="ws-case-glance">
						<p class="ws-case-glance__lbl">At a glance</p>
						<div class="ws-case-glance__text"><?php echo wp_kses_post( $summary ); ?></div>
					</div>
				<?php endif; ?>

				<?php if ( $presentation ) : ?>
					<section class="ws-case-detail__section">
						<h2 class="ws-case-detail__label">Presentation</h2>
						<div class="ws-case-detail__body"><?php echo wp_kses_post( $presentation ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $diagnosis ) : ?>
					<section class="ws-case-detail__section">
						<h2 class="ws-case-detail__label">Diagnosis</h2>
						<div class="ws-case-detail__body"><?php echo wp_kses_post( $diagnosis ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $treatment ) : ?>
					<section class="ws-case-detail__section">
						<h2 class="ws-case-detail__label">Treatment</h2>
						<div class="ws-case-detail__body"><?php echo wp_kses_post( $treatment ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $result ) : ?>
					<section class="ws-case-detail__section ws-case-detail__section--result">
						<h2 class="ws-case-detail__label">Result</h2>
						<div class="ws-case-detail__body"><?php echo wp_kses_post( $result ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $case_notes ) : ?>
					<section class="ws-case-detail__section">
						<h2 class="ws-case-detail__label">Additional notes</h2>
						<div class="entry-content ws-case-detail__body"><?php echo wp_kses_post( $case_notes ); ?></div>
					</section>
				<?php
				else :
					$content = trim( wp_strip_all_tags( get_the_content() ) );
					if ( ! empty( $content ) ) :
						?>
						<section class="ws-case-detail__section">
							<h2 class="ws-case-detail__label">Additional notes</h2>
							<div class="entry-content ws-case-detail__body"><?php the_content(); ?></div>
						</section>
						<?php
					endif;
				endif;
				?>

				<p class="ws-case-detail__back">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'clinic_case' ) ); ?>" class="ws-link-arrow ws-link-arrow--back">Back to all cases</a>
				</p>

			</article>

			<?php get_template_part( 'template-parts/case-sidebar' ); ?>

		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<?php endwhile; ?>

<?php
get_footer();
