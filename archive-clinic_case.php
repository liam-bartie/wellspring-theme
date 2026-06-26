<?php
/**
 * Archive template for Clinic Cases — the faceted library.
 *
 * A search box plus a left filter rail (focus area, symptom, treatment used)
 * sit above a card grid. All filtering is client-side over the rendered cards.
 * Lives at /clinic-cases/.
 *
 * @package Wellspring
 */

get_header();

$focus_terms    = get_terms( array( 'taxonomy' => 'case_focus', 'hide_empty' => true ) );
$symptom_terms  = get_terms( array( 'taxonomy' => 'case_symptom', 'hide_empty' => true ) );
$modality_terms = get_terms( array( 'taxonomy' => 'case_modality', 'hide_empty' => true ) );

$all_cases = get_posts(
	array(
		'post_type'      => 'clinic_case',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$facet_groups = array(
	'focus'    => array( 'label' => 'Focus area', 'attr' => 'tags', 'terms' => $focus_terms ),
	'symptom'  => array( 'label' => 'Symptom', 'attr' => 'symptom', 'terms' => $symptom_terms ),
	'modality' => array( 'label' => 'Treatment used', 'attr' => 'modality', 'terms' => $modality_terms ),
);
?>

<main id="primary" class="site-main">

	<?php
	$ar_eyebrow   = get_theme_mod( 'clinic_cases_eyebrow', 'Real outcomes' );
	$ar_title     = get_theme_mod( 'clinic_cases_title', 'Clinic cases' );
	$ar_lede      = get_theme_mod( 'clinic_cases_lede', "A curated record of patients we've worked with. Names are shortened to initials for privacy. Search by symptom, or filter by focus area and treatment." );
	$ar_image     = get_theme_mod( 'clinic_cases_hero_image', '' );
	$header_class = $ar_image ? 'ws-page-header ws-page-header--imaged' : 'ws-page-header';
	?>
	<section class="<?php echo esc_attr( $header_class ); ?>">
		<?php if ( $ar_image ) : ?>
			<div class="ws-page-header__bg" style="background-image: url('<?php echo esc_url( $ar_image ); ?>');" aria-hidden="true"></div>
			<div class="ws-page-header__overlay" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="ws-container ws-container--narrow ws-page-header__content">
			<?php if ( $ar_eyebrow ) : ?><p class="eyebrow"><?php echo esc_html( $ar_eyebrow ); ?></p><?php endif; ?>
			<?php if ( $ar_title ) : ?><h1 class="ws-page-header__title"><?php echo esc_html( $ar_title ); ?></h1><?php endif; ?>
			<?php if ( $ar_lede ) : ?><p class="ws-page-header__lede"><?php echo esc_html( $ar_lede ); ?></p><?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/reviewed-by' ); ?>

	<section class="ws-section ws-cases-archive">
		<div class="ws-container">

			<label class="ws-cases-search">
				<span class="screen-reader-text">Search cases</span>
				<input type="search" id="ws-cases-search" placeholder="Search by symptom or keyword — “jaw”, “insomnia”, “heavy periods”…" autocomplete="off" />
			</label>

			<?php if ( ! empty( $all_cases ) ) : ?>
				<div class="ws-cases-faceted">

					<aside class="ws-cases-rail" aria-label="Filter cases">
						<div class="ws-cases-rail__head">
							<h2 class="ws-cases-rail__title">Filters</h2>
							<button type="button" id="ws-cases-reset" class="ws-cases-rail__clear" hidden>Clear all</button>
						</div>
						<?php foreach ( $facet_groups as $key => $group ) : ?>
							<?php if ( ! empty( $group['terms'] ) && ! is_wp_error( $group['terms'] ) ) : ?>
								<div class="ws-facet">
									<p class="ws-facet__label"><?php echo esc_html( $group['label'] ); ?></p>
									<div class="ws-facet__chips" data-facet-group="<?php echo esc_attr( $group['attr'] ); ?>">
										<?php foreach ( $group['terms'] as $term ) : ?>
											<button type="button" class="ws-facet__chip" data-facet-value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</aside>

					<div class="ws-cases-results">
						<div class="ws-cases-results__top">
							<p class="ws-cases-results__count"><span id="ws-cases-count"><?php echo count( $all_cases ); ?></span> <?php echo esc_html( _n( 'case', 'cases', count( $all_cases ), 'wellspring' ) ); ?></p>
						</div>

						<div class="ws-cases-grid ws-cases-grid--rows">
							<?php foreach ( $all_cases as $case ) {
								get_template_part( 'template-parts/case-card', null, array( 'case' => $case ) );
							} ?>
						</div>

						<p class="ws-cases-empty" hidden>No cases match your filters. <button type="button" id="ws-cases-reset-2">Clear filters</button></p>
					</div>

				</div>
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
	const cards = Array.from(document.querySelectorAll('.ws-case-card'));
	const chips = Array.from(document.querySelectorAll('.ws-facet__chip'));
	const empty = document.querySelector('.ws-cases-empty');
	const countEl = document.getElementById('ws-cases-count');
	const resetBtns = [document.getElementById('ws-cases-reset'), document.getElementById('ws-cases-reset-2')];
	let activeSearch = '';

	function activeByGroup() {
		const groups = {};
		document.querySelectorAll('.ws-facet__chips').forEach(box => {
			const g = box.dataset.facetGroup;
			groups[g] = Array.from(box.querySelectorAll('.ws-facet__chip.is-active')).map(c => c.dataset.facetValue);
		});
		return groups;
	}

	function apply() {
		const groups = activeByGroup();
		let visible = 0, anyFilter = !!activeSearch;
		Object.values(groups).forEach(v => { if (v.length) anyFilter = true; });

		cards.forEach(card => {
			const matchesSearch = !activeSearch || (card.dataset.search || '').includes(activeSearch);
			let matchesFacets = true;
			for (const g in groups) {
				const active = groups[g];
				if (!active.length) continue;
				const cardVals = (card.dataset[g === 'tags' ? 'tags' : g] || '').split(',');
				if (!active.some(v => cardVals.includes(v))) { matchesFacets = false; break; }
			}
			const show = matchesSearch && matchesFacets;
			card.style.display = show ? '' : 'none';
			if (show) visible++;
		});

		if (countEl) countEl.textContent = visible;
		if (empty) empty.hidden = visible > 0;
		resetBtns.forEach(b => { if (b) b.hidden = !anyFilter; });
	}

	chips.forEach(chip => {
		chip.addEventListener('click', () => { chip.classList.toggle('is-active'); apply(); });
	});

	if (search) {
		search.addEventListener('input', () => { activeSearch = search.value.toLowerCase().trim(); apply(); });
	}

	function reset() {
		chips.forEach(c => c.classList.remove('is-active'));
		activeSearch = '';
		if (search) search.value = '';
		apply();
	}
	resetBtns.forEach(b => { if (b) b.addEventListener('click', reset); });
})();
</script>

<?php
get_footer();
