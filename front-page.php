<?php
/**
 * The home page template.
 *
 * Used automatically by WordPress as the front page when set in
 * Settings → Reading → "Your homepage displays" → A static page.
 *
 * @package Wellspring
 */

get_header();
?>

<main id="primary" class="site-main">

	<section class="ws-hero">
		<div class="ws-container ws-container--narrow">
			<p class="eyebrow">Calgary &middot; Inglewood</p>
			<h1 class="ws-hero__title">Calm, considered care for body and mind.</h1>
			<p class="ws-hero__lede">Acupuncture and Traditional Chinese Medicine for pain relief, women&rsquo;s health, sleep, digestion, and beyond &mdash; practised by Dr.&nbsp;Laura Cowburn for over a decade.</p>
			<div class="ws-hero__actions">
				<a href="/book-appointments/" class="ws-btn">Book an appointment</a>
				<a href="/what-we-treat/" class="ws-btn ws-btn--ghost">See what we treat</a>
			</div>
		</div>
	</section>

	<section class="ws-section ws-section--mist">
		<div class="ws-container">
			<header class="ws-section-header">
				<p class="eyebrow">What we treat</p>
				<h2>Six areas of focus, drawn from thousands of years of practice.</h2>
				<p class="ws-section-header__lede">From acute pain to chronic patterns, hormonal cycles to mental clarity &mdash; acupuncture and herbal medicine address the body as a whole, not in parts.</p>
			</header>

			<div class="ws-cards">
				<a class="ws-card" href="/what-we-treat/pain-relief/">
					<h3 class="ws-card__title">Pain Relief &amp; Injury Recovery</h3>
					<p class="ws-card__body">Back pain, headaches and migraines, neck and shoulder, sciatica, arthritis, hand and foot pain.</p>
					<span class="ws-card__cta">Learn more</span>
				</a>
				<a class="ws-card" href="/what-we-treat/womens-health/">
					<h3 class="ws-card__title">Women&rsquo;s Health</h3>
					<p class="ws-card__body">Fertility and IVF support, menstrual pain and PMS, perimenopause and menopause, pregnancy support.</p>
					<span class="ws-card__cta">Learn more</span>
				</a>
				<a class="ws-card" href="/what-we-treat/mental-health-sleep/">
					<h3 class="ws-card__title">Mental Health &amp; Sleep</h3>
					<p class="ws-card__body">Anxiety, depression, chronic fatigue, insomnia, stress &mdash; restoring nervous system balance and rest.</p>
					<span class="ws-card__cta">Learn more</span>
				</a>
				<a class="ws-card" href="/what-we-treat/digestive-health/">
					<h3 class="ws-card__title">Digestive Health</h3>
					<p class="ws-card__body">IBS, GERD and heartburn, bloating, irregular digestion &mdash; addressing the gut as a foundation for wellbeing.</p>
					<span class="ws-card__cta">Learn more</span>
				</a>
				<a class="ws-card" href="/what-we-treat/respiratory/">
					<h3 class="ws-card__title">Respiratory Disorders</h3>
					<p class="ws-card__body">Allergies, recurring colds and sinus issues, asthma support, post-viral recovery.</p>
					<span class="ws-card__cta">Learn more</span>
				</a>
				<a class="ws-card" href="/what-we-treat/other-conditions/">
					<h3 class="ws-card__title">Other Conditions</h3>
					<p class="ws-card__body">Skin issues, neuropathy, post-surgical recovery, immune support, and conditions beyond these six categories.</p>
					<span class="ws-card__cta">Learn more</span>
				</a>
			</div>
		</div>
	</section>

	<section class="ws-section ws-practitioner">
		<div class="ws-container">
			<div class="ws-practitioner__inner">
				<div class="ws-practitioner__portrait" aria-hidden="true">
					<span class="ws-practitioner__monogram">LC</span>
				</div>
				<div class="ws-practitioner__body">
					<p class="eyebrow">Meet your practitioner</p>
					<h2>Dr. Laura Cowburn</h2>
					<p class="ws-practitioner__credential">Doctor of Traditional Chinese Medicine &middot; Registered Acupuncturist (Alberta)</p>
					<p>For more than a decade, Dr. Cowburn has practised in Calgary &mdash; drawing on acupuncture, herbal medicine, cupping, and patient counsel to help her clients feel themselves again. Her approach combines classical TCM diagnosis with a modern, evidence-aware lens, and a genuine commitment to time spent listening.</p>
					<p><a href="/about/" class="ws-link-arrow">Read her full story</a></p>
				</div>
			</div>
		</div>
	</section>

	<section class="ws-section ws-section--mist">
		<div class="ws-container ws-container--narrow">
			<header class="ws-section-header ws-section-header--center">
				<p class="eyebrow">Patient stories</p>
				<h2>The work, in their words.</h2>
			</header>

			<div class="ws-quotes">
				<figure class="ws-quote">
					<blockquote class="ws-quote__body">After years of failed migraine treatments, six weeks with Dr. Cowburn cut my frequency in half. I left every appointment feeling listened to in a way I hadn&rsquo;t in any clinic before.</blockquote>
					<figcaption class="ws-quote__attr">Wayne X. &middot; <span>Migraine relief</span></figcaption>
				</figure>
				<figure class="ws-quote">
					<blockquote class="ws-quote__body">Pregnancy was tough on my body and Wellspring helped me through it. Calm space, real expertise, and treatment that actually worked.</blockquote>
					<figcaption class="ws-quote__attr">Paiva K. &middot; <span>Pregnancy support</span></figcaption>
				</figure>
				<figure class="ws-quote">
					<blockquote class="ws-quote__body">I came in skeptical and left a regular. The combination of acupuncture and herbal medicine has been genuinely life-changing for my digestion and sleep.</blockquote>
					<figcaption class="ws-quote__attr">Sherry Y. &middot; <span>Holistic care</span></figcaption>
				</figure>
			</div>
		</div>
	</section>

	<section class="ws-section ws-cta">
		<div class="ws-container ws-container--narrow ws-cta__inner">
			<h2 class="ws-cta__title">Ready when you are.</h2>
			<p class="ws-cta__lede">New patients welcome. Appointments typically available within the week. Direct billing to most major insurers.</p>
			<div class="ws-cta__actions">
				<a href="/book-appointments/" class="ws-btn">Book an appointment</a>
				<a href="tel:+15876004945" class="ws-btn ws-btn--ghost">Call (587)&nbsp;600-4945</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
