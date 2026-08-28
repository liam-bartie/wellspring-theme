<?php
/**
 * Case-study disclosure blocks.
 *
 * Long-form authorship, consent and medical-disclaimer copy for the Clinic
 * Cases listing and individual case pages. Separate from the reviewed-by badge:
 * the badge is a one-line credibility pill with deliberately narrow allowed
 * HTML, and this content needs headings, several paragraphs and links.
 *
 * Structure, and why:
 *
 *   disclosure_cases_top     Clinic Cases listing, above the grid.
 *   disclosure_cases_bottom  Clinic Cases listing, below the grid.
 *   disclosure_case_bottom   Individual case page, below the case.
 *   medical_disclaimer       ONE field, appended to BOTH bottoms.
 *
 * The disclaimer is shared on purpose. It appears in two places, and two
 * hand-edited copies of a medical disclaimer will eventually differ — which for
 * a registered practitioner is a real problem rather than an untidy one. Only
 * the opening sentence differs between the contexts ("These are individual
 * patient experiences" on a listing of many, "This reflects an individual
 * patient experience" on one case), so that sentence lives in each context's own
 * field and everything legally load-bearing lives here once.
 *
 * @package Wellspring
 */

define( 'WELLSPRING_DISCLOSURE_SEED_VERSION', '1' );

/**
 * The slots this feature renders into.
 *
 * @return array slot => array( option field, label )
 */
function wellspring_disclosure_slots() {
	return array(
		'cases_top'    => array( 'disclosure_cases_top', 'Clinic Cases listing — above the cases' ),
		'cases_bottom' => array( 'disclosure_cases_bottom', 'Clinic Cases listing — below the cases' ),
		'case_bottom'  => array( 'disclosure_case_bottom', 'Individual case page — below the case' ),
	);
}

/**
 * Resolved HTML for one slot, disclaimer included where it applies.
 *
 * @param string $slot One of wellspring_disclosure_slots().
 * @return string HTML, already safe for output, or '' to render nothing.
 */
function wellspring_disclosure_html( $slot ) {
	$slots = wellspring_disclosure_slots();

	if ( ! isset( $slots[ $slot ] ) || ! function_exists( 'get_field' ) ) {
		return '';
	}

	$body = trim( (string) get_field( $slots[ $slot ][0], 'option' ) );

	// The disclaimer is appended to the two "bottom" slots only.
	$disclaimer = '';
	if ( 'cases_top' !== $slot ) {
		$disclaimer = trim( (string) get_field( 'medical_disclaimer', 'option' ) );
	}

	if ( '' === $body && '' === $disclaimer ) {
		return '';
	}

	$html = '';

	if ( '' !== $body ) {
		$html .= '<div class="ws-disclosure__body">' . wp_kses_post( $body ) . '</div>';
	}

	if ( '' !== $disclaimer ) {
		$html .= '<div class="ws-disclosure__legal">' . wp_kses_post( $disclaimer ) . '</div>';
	}

	return $html;
}

/**
 * Settings, on the same Settings > Wellspring page as the badge and reviews.
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
	add_action(
		'acf/init',
		function () {
			$fields = array(
				array(
					'key'     => 'field_ws_disc_note',
					'label'   => '',
					'type'    => 'message',
					'message' => 'Authorship, consent and medical-disclaimer copy for the case studies. Full formatting is available &mdash; headings, paragraphs and links &mdash; so this is where longer explanatory content belongs. The short green badge is separate, above.',
				),
			);

			foreach ( wellspring_disclosure_slots() as $slot => $meta ) {
				list( $name, $label ) = $meta;
				$fields[] = array(
					'key'          => 'field_ws_' . $name,
					'name'         => $name,
					'label'        => $label,
					'instructions' => 'cases_top' === $slot
						? 'Leave empty to show nothing above the cases.'
						: 'The medical disclaimer below is added automatically underneath this. Leave this empty and only the disclaimer shows.',
					'type'         => 'wysiwyg',
					'tabs'         => 'all',
					'toolbar'      => 'full',
					'media_upload' => 0,
					'delay'        => 1,
				);
			}

			$fields[] = array(
				'key'          => 'field_ws_medical_disclaimer',
				'name'         => 'medical_disclaimer',
				'label'        => 'Medical disclaimer (shared)',
				'instructions' => 'Appears below the cases on the listing <em>and</em> below every individual case. Kept as one field so the two can never drift apart. Leave empty to show no disclaimer anywhere.',
				'type'         => 'wysiwyg',
				'tabs'         => 'all',
				'toolbar'      => 'basic',
				'media_upload' => 0,
				'delay'        => 1,
			);

			acf_add_local_field_group(
				array(
					'key'        => 'group_wellspring_disclosure',
					'title'      => 'Case study disclosures',
					'fields'     => $fields,
					'location'   => array(
						array(
							array(
								'param'    => 'options_page',
								'operator' => '==',
								'value'    => 'wellspring-settings',
							),
						),
					),
					'menu_order' => 5,
					'active'     => true,
				)
			);
		}
	);
}

/**
 * Seed the fields once with the copy Amber wrote, so nobody has to paste it.
 *
 * Writes only where a field is empty, so it can never overwrite an edit, and
 * the version flag means a deliberately cleared field stays cleared.
 */
add_action(
	'admin_init',
	function () {
		if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
			return;
		}
		if ( get_option( 'wellspring_disclosure_seeded' ) === WELLSPRING_DISCLOSURE_SEED_VERSION ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$about = function_exists( 'wellspring_page_url' ) ? wellspring_page_url( 'about' ) : '/about';

		$seed = array(
			'disclosure_cases_top' => '<h2>About These Case Studies</h2>' . "\n"
				. '<p>The cases on this page are real patient encounters from '
				. '<a href="' . esc_url( $about ) . '">Dr. Laura Cowburn</a>&rsquo;s private practice in Calgary, AB. '
				. 'Dr. Cowburn is a Doctor of Traditional Chinese Medicine and Acupuncture, trained at the '
				. 'Alberta College of Acupuncture &amp; Traditional Chinese Medicine (ACATCM), and registered with '
				. 'the College of Acupuncturists of Alberta (CAA).</p>',

			'disclosure_cases_bottom' => '<p>Each case is personally written by '
				. '<a href="' . esc_url( $about ) . '">Dr. Laura Cowburn</a>, Doctor of TCM and Registered '
				. 'Acupuncturist in Alberta, from her own diagnostic notes and treatment records and reflects her '
				. 'direct clinical reasoning for that patient. Before we publish a case, we ask the patient&rsquo;s '
				. 'permission to share their story; names are shortened to initials and identifying details are '
				. 'removed to protect their privacy.</p>' . "\n"
				. '<p>These are individual patient experiences, not clinical trial data.</p>',

			'disclosure_case_bottom' => '<p>This reflects an individual patient experience, not clinical trial data.</p>',

			'medical_disclaimer' => '<p>Results vary from person to person depending on the condition, its severity, '
				. 'and how an individual responds to treatment. TCM and acupuncture are not a substitute for care '
				. 'from your physician or another regulated health professional. If you&rsquo;re dealing with a '
				. 'serious or urgent symptom, please see a doctor first.</p>',
		);

		$written = 0;
		foreach ( $seed as $name => $value ) {
			$existing = get_field( $name, 'option' );
			if ( '' === trim( (string) $existing ) ) {
				update_field( $name, $value, 'option' );
				$written++;
			}
		}

		update_option( 'wellspring_disclosure_seeded', WELLSPRING_DISCLOSURE_SEED_VERSION );

		if ( $written ) {
			set_transient( 'wellspring_disclosure_seeded_notice', $written, 60 );
		}
	}
);

add_action(
	'admin_notices',
	function () {
		$n = get_transient( 'wellspring_disclosure_seeded_notice' );
		if ( false === $n ) {
			return;
		}
		delete_transient( 'wellspring_disclosure_seeded_notice' );
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sprintf( '%d case-study disclosure field(s) filled in. Review them under Settings > Wellspring.', $n ) )
		);
	}
);
