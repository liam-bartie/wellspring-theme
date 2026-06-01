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

	<?php
	$ar_eyebrow    = get_theme_mod( 'clinic_cases_eyebrow', 'Real outcomes' );
	$ar_title      = get_theme_mod( 'clinic_cases_title', 'Clinic cases' );
	$ar_lede       = get_theme_mod( 'clinic_cases_lede', "A curated record of patients we've worked with. Names are shortened to initials for privacy. Use the filters below to browse by focus area or search by symptom." );
	$ar_image      = get_theme_mod( 'clinic_cases_hero_image', '' );
	$header_class  = $ar_image ? 'ws-page-header ws-page-header--imaged' : 'ws-page-header';
	?>
	<section class="<?php echo esc_attr( $header_class ); ?>">
		<?php if ( $ar_image ) : ?>
			<div class="ws-page-header__bg" style="background-image: url('<?php echo esc_url( $ar_image ); ?>');" aria-hidden="true"></div>
			<div class="ws-page-header__overlay" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<?php if ( $ar_eyebrow ) : ?>
				<p class="eyebrow"><?php echo esc_html( $ar_eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $ar_title ) : ?>
				<h1 class="ws-page-header__title"><?php echo esc_html( $ar_title ); ?></h1>
			<?php endif; ?>
			<?php if ( $ar_lede ) : ?>
				<p class="ws-page-header__lede"><?php echo esc_html( $ar_lede ); ?></p>
			<?php endif; ?>
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
					<?php foreach ( $all_cases as $case ) {
						get_template_part( 'template-parts/case-card', null, array( 'case' => $case ) );
					} ?>
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
