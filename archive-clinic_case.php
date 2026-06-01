<?php
/**
 * Archive template for Clinic Cases.
 *
 * Lists all clinic_case posts as filterable cards with a JS-powered
 * search + chip filter. Lives at /clinic-cases/.
 *
 * @package Wellspring
 */

get_header();

$focus_terms = get_terms(
	array(
		'taxonomy'   => 'case_focus',
		'hide_empty' => true,
	)
);

$all_cases = get_posts(
	array(
		'post_type'      => 'clinic_case',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
?>

<main id="primary" class="site-main">

	<section class="ws-page-header">
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<p class="eyebrow">Real outcomes</p>
			<h1 class="ws-page-header__title">Clinic cases</h1>
			<p class="ws-page-header__lede">A curated record of patients we've worked with. Names are shortened to initials for privacy. Use the filters below to browse by focus area or search by symptom.</p>
		</div>
	</section>

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<section class="ws-section ws-cases-archive">
		<div class="ws-container">

			<?php if ( ! empty( $focus_terms ) && ! is_wp_error( $focus_terms ) ) : ?>
				<div class="ws-cases-toolbar">
					<label class="ws-cases-search">
						<span class="screen-reader-text">Search cases</span>
						<input type="search" id="ws-cases-search" placeholder="Search by symptom or keyword…" autocomplete="off" />
					</label>

					<div class="ws-cases-filter" role="group" aria-label="Filter by focus area">
						<button type="button" class="ws-cases-filter__btn is-active" data-filter="all">All</button>
						<?php foreach ( $focus_terms as $term ) : ?>
							<button type="button" class="ws-cases-filter__btn" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $all_cases ) ) : ?>
				<div class="ws-cases-grid">
					<?php
					foreach ( $all_cases as $case ) :
						$focus_areas    = wp_get_post_terms( $case->ID, 'case_focus' );
						$focus_slugs    = wp_list_pluck( $focus_areas, 'slug' );
						$focus_names    = wp_list_pluck( $focus_areas, 'name' );
						$primary_focus  = $focus_areas[0]->name ?? '';
						$initial        = function_exists( 'get_field' ) ? get_field( 'patient_initial', $case->ID ) : '';
						$context        = function_exists( 'get_field' ) ? get_field( 'patient_context', $case->ID ) : '';
						$result_excerpt = function_exists( 'get_field' ) ? wp_strip_all_tags( get_field( 'result', $case->ID ) ) : '';
						if ( ! $result_excerpt ) {
							$result_excerpt = wp_strip_all_tags( $case->post_content );
						}
						$result_excerpt = wp_trim_words( $result_excerpt, 30, '…' );
						$monogram       = '';
						if ( $initial ) {
							$parts = explode( ' ', trim( $initial ) );
							foreach ( $parts as $part ) {
								$monogram .= strtoupper( substr( $part, 0, 1 ) );
							}
						}
						?>
						<article
							class="ws-case-card"
							data-search="<?php echo esc_attr( strtolower( $case->post_title . ' ' . wp_strip_all_tags( $case->post_content ) . ' ' . $context . ' ' . implode( ' ', $focus_names ) ) ); ?>"
							data-tags="<?php echo esc_attr( implode( ',', $focus_slugs ) ); ?>"
						>
							<?php if ( $primary_focus ) : ?>
								<p class="ws-case-card__focus"><?php echo esc_html( $primary_focus ); ?></p>
							<?php endif; ?>

							<div class="ws-case-card__patient">
								<?php if ( $monogram ) : ?>
									<span class="ws-case-card__monogram" aria-hidden="true"><?php echo esc_html( substr( $monogram, 0, 2 ) ); ?></span>
								<?php endif; ?>
								<span><?php echo esc_html( $initial ?: 'Patient' ); ?><?php if ( $context ) : ?> &middot; <?php echo esc_html( $context ); ?><?php endif; ?></span>
							</div>

							<h3 class="ws-case-card__title">
								<a href="<?php echo esc_url( get_permalink( $case->ID ) ); ?>"><?php echo esc_html( $case->post_title ); ?></a>
							</h3>

							<?php if ( $result_excerpt ) : ?>
								<p class="ws-case-card__excerpt"><?php echo esc_html( $result_excerpt ); ?></p>
							<?php endif; ?>

							<div class="ws-case-card__divider" aria-hidden="true"></div>

							<a href="<?php echo esc_url( get_permalink( $case->ID ) ); ?>" class="ws-case-card__cta">Read the full case <span aria-hidden="true">→</span></a>
						</article>
						<?php
					endforeach;
					?>
				</div>

				<p class="ws-cases-empty" hidden>No cases match your filters. <button type="button" id="ws-cases-reset">Clear filters</button></p>
			<?php else : ?>
				<p class="ws-cases-empty">No clinic cases published yet. Add one in <strong>WP admin → Clinic Cases → Add new case</strong>.</p>
			<?php endif; ?>

		</div>
	</section>

	<?php get_template_part( 'template-parts/cta-banner' ); ?>

</main>

<script>
(function() {
	const search = document.getElementById('ws-cases-search');
	const filterBtns = document.querySelectorAll('.ws-cases-filter__btn');
	const cards = document.querySelectorAll('.ws-case-card');
	const empty = document.querySelector('.ws-cases-empty');
	const reset = document.getElementById('ws-cases-reset');
	let activeFilter = 'all';
	let activeSearch = '';

	function apply() {
		let visible = 0;
		cards.forEach(card => {
			const tags = (card.dataset.tags || '').split(',');
			const searchable = card.dataset.search || '';
			const matchesFilter = activeFilter === 'all' || tags.includes(activeFilter);
			const matchesSearch = !activeSearch || searchable.includes(activeSearch);
			const show = matchesFilter && matchesSearch;
			card.style.display = show ? '' : 'none';
			if (show) visible++;
		});
		if (empty) empty.hidden = visible > 0;
	}

	filterBtns.forEach(btn => {
		btn.addEventListener('click', () => {
			filterBtns.forEach(b => b.classList.remove('is-active'));
			btn.classList.add('is-active');
			activeFilter = btn.dataset.filter;
			apply();
		});
	});

	if (search) {
		search.addEventListener('input', () => {
			activeSearch = search.value.toLowerCase().trim();
			apply();
		});
	}

	if (reset) {
		reset.addEventListener('click', () => {
			activeFilter = 'all';
			activeSearch = '';
			if (search) search.value = '';
			filterBtns.forEach(b => b.classList.toggle('is-active', b.dataset.filter === 'all'));
			apply();
		});
	}
})();
</script>

<?php
get_footer();
