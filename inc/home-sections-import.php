<?php
/**
 * Tools -> Wellspring Home Sections.
 *
 * Migrates the home page's top-level content fields into "Page sections" rows,
 * so the blocks become reorderable in the editor.
 *
 * Design constraints this screen exists to satisfy:
 *
 *   - Nothing is destructive. Rows are written; the original top-level fields
 *     are left exactly as they are. Rolling back is restoring front-page.php.
 *   - Values come from wellspring_home_block_values(), the same function
 *     front-page.php renders from, so a field that has never been saved
 *     migrates as the default the page is actually displaying rather than as
 *     an empty row.
 *   - It previews before it writes, and it refuses to overwrite existing rows
 *     unless that is explicitly confirmed.
 *
 * @package Wellspring
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WELLSPRING_HOME_SECTIONS_SLUG = 'wellspring-home-sections';

/**
 * The blocks to migrate, in the order front-page.php renders them.
 *
 * Hero and the closing call-to-action are deliberately absent: they stay
 * pinned in the template so the page cannot lose its opening or its CTA.
 *
 * @return array layout name => array( label, block key in wellspring_home_block_values() )
 */
function wellspring_home_section_plan() {
	return array(
		'home_intro'        => array( 'Home: intro text', 'intro' ),
		'home_wwt'          => array( 'Home: what we treat', 'wwt' ),
		'home_practitioner' => array( 'Home: practitioner', 'practitioner' ),
		'home_modalities'   => array( 'Home: modalities', 'modalities' ),
		'home_cases'        => array( 'Home: featured clinic cases', 'cases' ),
		'home_reviews'      => array( 'Home: reviews', null ),
	);
}

/**
 * Reduce an ACF image value to the attachment ID that ACF stores.
 *
 * get_field() returns an array for these fields (return_format => array), but
 * update_field() expects the ID. Writing the array back would save a serialised
 * blob that ACF cannot read, and the image would silently vanish.
 *
 * @param mixed $value ACF image value.
 * @return int|string Attachment ID, or '' when unset.
 */
function wellspring_image_to_id( $value ) {
	if ( is_array( $value ) && isset( $value['ID'] ) ) {
		return (int) $value['ID'];
	}
	if ( is_object( $value ) && isset( $value->ID ) ) {
		return (int) $value->ID;
	}
	if ( is_numeric( $value ) ) {
		return (int) $value;
	}
	return '';
}

/**
 * Build the rows that would be written, from the live values.
 *
 * @return array List of ACF flexible-content rows.
 */
function wellspring_build_home_section_rows() {
	$values = function_exists( 'wellspring_home_block_values' ) ? wellspring_home_block_values() : array();
	$rows   = array();

	foreach ( wellspring_home_section_plan() as $layout => $meta ) {
		list( , $block ) = $meta;

		$row = array( 'acf_fc_layout' => $layout );

		if ( null !== $block && isset( $values[ $block ] ) ) {
			foreach ( $values[ $block ] as $key => $value ) {
				// Image sub-fields must be stored as IDs.
				if ( in_array( $key, array( 'portrait', 'tcm_image', 'acu_image' ), true ) ) {
					$row[ $key ] = wellspring_image_to_id( $value );
					continue;
				}
				$row[ $key ] = $value;
			}
		}

		if ( 'home_cases' === $layout ) {
			$row['cases'] = function_exists( 'wellspring_home_featured_case_ids' )
				? wellspring_home_featured_case_ids()
				: array();
		}

		$rows[] = $row;
	}

	return $rows;
}

/**
 * Register the Tools screen.
 */
add_action(
	'admin_menu',
	function () {
		add_management_page(
			'Wellspring Home Sections',
			'Wellspring Home Sections',
			'edit_pages',
			WELLSPRING_HOME_SECTIONS_SLUG,
			'wellspring_render_home_sections_screen'
		);
	}
);

/**
 * Render (and, on POST, run) the migration screen.
 */
function wellspring_render_home_sections_screen() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( 'You do not have permission to run this migration.' );
	}

	$home_id = (int) get_option( 'page_on_front' );

	echo '<div class="wrap"><h1>Wellspring Home Sections</h1>';

	if ( ! $home_id ) {
		echo '<div class="notice notice-error"><p>No static front page is set, so there is nothing to migrate. Set one under Settings &rsaquo; Reading.</p></div></div>';
		return;
	}

	if ( ! function_exists( 'update_field' ) || ! function_exists( 'get_field' ) ) {
		echo '<div class="notice notice-error"><p>ACF is not active, so this migration cannot run.</p></div></div>';
		return;
	}

	// Existing rows are the thing most worth knowing before writing.
	$existing = get_field( 'page_sections', $home_id );
	$existing = is_array( $existing ) ? $existing : array();

	$rows = wellspring_build_home_section_rows();

	// ------------------------------------------------------------------ write
	if ( isset( $_POST['wellspring_home_sections_go'] ) ) {
		check_admin_referer( 'wellspring_home_sections' );

		$confirmed = ! empty( $_POST['wellspring_replace_existing'] );

		if ( $existing && ! $confirmed ) {
			echo '<div class="notice notice-error"><p><strong>Refused.</strong> This page already has '
				. count( $existing )
				. ' section row(s). Tick the confirmation box to replace them.</p></div>';
		} else {
			$ok = update_field( 'page_sections', $rows, $home_id );

			if ( $ok ) {
				echo '<div class="notice notice-success"><p><strong>Wrote ' . count( $rows )
					. ' section rows to the home page.</strong> The original fields were not changed. '
					. 'The page still renders from those fields until the template cutover, so it should look identical right now.</p></div>';
				$existing = get_field( 'page_sections', $home_id );
				$existing = is_array( $existing ) ? $existing : array();
			} else {
				echo '<div class="notice notice-error"><p>update_field() reported failure. Nothing was changed.</p></div>';
			}
		}
	}

	// ------------------------------------------------------------------ state
	echo '<h2>Current state</h2><table class="widefat striped" style="max-width:52rem"><tbody>';
	printf(
		'<tr><td style="width:16rem"><strong>Home page</strong></td><td>%s <span style="color:#666">(ID %d)</span></td></tr>',
		esc_html( get_the_title( $home_id ) ),
		$home_id
	);
	printf(
		'<tr><td><strong>Existing section rows</strong></td><td>%s</td></tr>',
		$existing
			? esc_html( count( $existing ) ) . ' &mdash; listed below'
			: 'none'
	);
	echo '</tbody></table>';

	if ( $existing ) {
		echo '<p><strong>Rows already on the page:</strong></p><ol>';
		foreach ( $existing as $row ) {
			printf( '<li><code>%s</code></li>', esc_html( $row['acf_fc_layout'] ?? '(unknown)' ) );
		}
		echo '</ol>';
	}

	// ---------------------------------------------------------------- preview
	echo '<h2>What would be written</h2>';
	echo '<p>Values are read through the same function the front page renders from, so a field that has never been saved migrates as the default the page is currently showing &mdash; not as an empty row.</p>';

	$plan = wellspring_home_section_plan();

	foreach ( $rows as $i => $row ) {
		$layout = $row['acf_fc_layout'];
		$label  = $plan[ $layout ][0] ?? $layout;

		printf(
			'<h3 style="margin-bottom:.3em">%d. %s <code style="font-weight:normal">%s</code></h3>',
			$i + 1,
			esc_html( $label ),
			esc_html( $layout )
		);

		$fields = array_diff_key( $row, array( 'acf_fc_layout' => 1 ) );

		if ( ! $fields ) {
			echo '<p style="color:#666;margin-top:0">No editable fields &mdash; this section only controls placement.</p>';
			continue;
		}

		echo '<table class="widefat striped" style="max-width:60rem;margin-bottom:1.2em"><thead><tr>'
			. '<th style="width:11rem">Field</th><th style="width:5rem">Chars</th><th>Value</th>'
			. '</tr></thead><tbody>';

		foreach ( $fields as $key => $value ) {
			if ( is_array( $value ) ) {
				$shown = empty( $value ) ? '<em style="color:#a00">empty</em>' : esc_html( implode( ', ', array_map( 'strval', $value ) ) );
				$len   = count( $value ) . ' item(s)';
			} elseif ( '' === $value || null === $value ) {
				$shown = '<em style="color:#a00">empty</em>';
				$len   = '0';
			} else {
				$text  = wp_strip_all_tags( (string) $value );
				$len   = (string) mb_strlen( $text );
				$shown = esc_html( mb_substr( $text, 0, 220 ) ) . ( mb_strlen( $text ) > 220 ? '&hellip;' : '' );
			}

			printf(
				'<tr><td><code>%s</code></td><td>%s</td><td>%s</td></tr>',
				esc_html( $key ),
				esc_html( $len ),
				$shown // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- escaped above.
			);
		}

		echo '</tbody></table>';
	}

	// ------------------------------------------------------------------- form
	echo '<form method="post" style="margin-top:2em;padding:1.2em;background:#fff;border:1px solid #c3c4c7;max-width:52rem">';
	wp_nonce_field( 'wellspring_home_sections' );

	if ( $existing ) {
		echo '<p><label><input type="checkbox" name="wellspring_replace_existing" value="1"> '
			. '<strong>Replace the ' . count( $existing ) . ' existing row(s).</strong> '
			. 'Anything added by hand on this page will be discarded.</label></p>';
	}

	echo '<p style="margin-bottom:1em;color:#555">Writing these rows does not change the live page: the template still renders from the original fields until the cutover. Safe to run, and safe to re-run.</p>';

	submit_button( 'Write ' . count( $rows ) . ' section rows', 'primary', 'wellspring_home_sections_go', false );
	echo '</form></div>';
}
