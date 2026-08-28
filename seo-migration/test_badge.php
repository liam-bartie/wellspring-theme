<?php
/**
 * test_badge.php — exercises wellspring_badge_text() resolution.
 *
 * Standalone: stubs the handful of WordPress/ACF functions the resolver
 * touches, includes inc/acf-fields.php for the real implementation, then
 * drives it through every branch. No WordPress required.
 *
 * Usage: php test_badge.php /path/to/theme
 * Exit:  0 all passed, 1 any failure
 */

$theme = $argv[1] ?? dirname( __DIR__ );

// ---------------------------------------------------------------- test state
$GLOBALS['ws_t'] = array();
function st( $k, $d = null ) {
	return array_key_exists( $k, $GLOBALS['ws_t'] ) ? $GLOBALS['ws_t'][ $k ] : $d;
}

// ------------------------------------------------------------------- stubs
// No-ops so including the field file defines the functions without running
// any registration.
function acf_add_local_field_group( $a ) {}
function add_action( $h, $c, $p = 10, $n = 1 ) {}
function add_filter( $h, $c, $p = 10, $n = 1 ) {}
function get_option( $k, $d = false ) { return $d; }

function is_singular( $t = '' ) {
	if ( ! st( 'singular', false ) ) {
		return false;
	}
	return '' === $t ? true : ( st( 'post_type' ) === $t );
}
function is_post_type_archive( $t = '' ) {
	return st( 'pt_archive' ) === $t;
}
function is_tax( $t = '' ) {
	$cur = st( 'tax' );
	if ( ! $cur ) {
		return false;
	}
	return is_array( $t ) ? in_array( $cur, $t, true ) : ( $cur === $t || '' === $t );
}
function get_queried_object_id() { return (int) st( 'id', 0 ); }

function get_field( $name, $post_id = false ) {
	$store = 'option' === $post_id ? st( 'options', array() ) : st( 'meta', array() );
	return array_key_exists( $name, $store ) ? $store[ $name ] : null;
}

// ------------------------------------------------------------------- harness
require_once $theme . '/inc/acf-fields.php';

$pass = 0;
$fail = 0;

function scenario( $label, array $state, $expected ) {
	global $pass, $fail;
	$GLOBALS['ws_t'] = $state;
	$got = wellspring_badge_text();
	if ( $got === $expected ) {
		$pass++;
		printf( "  PASS  %s\n", $label );
	} else {
		$fail++;
		printf( "  FAIL  %s\n        expected: %s\n        got:      %s\n",
			$label, var_export( $expected, true ), var_export( $got, true ) );
	}
}

$PAGE = WELLSPRING_BADGE_DEFAULT_PAGE;
$CASE = WELLSPRING_BADGE_DEFAULT_CASE;

echo "wellspring_badge_text() — resolution order\n\n";

// ---- 5: theme constants, nothing configured -------------------------------
scenario( 'plain page, nothing configured -> page constant',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10 ), $PAGE );

scenario( 'single clinic case, nothing configured -> CASE constant (not page)',
	array( 'singular' => true, 'post_type' => 'clinic_case', 'id' => 20 ), $CASE );

scenario( 'clinic-cases archive (no editor screen) -> CASE constant',
	array( 'pt_archive' => 'clinic_case' ), $CASE );

scenario( 'case_focus taxonomy archive -> CASE constant',
	array( 'tax' => 'case_focus' ), $CASE );

scenario( 'case_symptom taxonomy archive -> CASE constant',
	array( 'tax' => 'case_symptom' ), $CASE );

scenario( 'case_modality taxonomy archive -> CASE constant',
	array( 'tax' => 'case_modality' ), $CASE );

scenario( 'unrelated taxonomy archive -> PAGE constant',
	array( 'tax' => 'category' ), $PAGE );

// ---- 3 + 4: global defaults ----------------------------------------------
scenario( 'global page default wins over constant, on a page',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'options' => array( 'badge_default' => 'Global page copy.' ) ),
	'Global page copy.' );

scenario( 'global page default does NOT leak onto a clinic case',
	array( 'singular' => true, 'post_type' => 'clinic_case', 'id' => 20,
	       'options' => array( 'badge_default' => 'Global page copy.' ) ),
	$CASE );

scenario( 'global case default applies to the case archive',
	array( 'pt_archive' => 'clinic_case',
	       'options' => array( 'badge_clinic_case' => 'Written by Dr C.' ) ),
	'Written by Dr C.' );

scenario( 'empty global default falls through to the constant',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'options' => array( 'badge_default' => '' ) ), $PAGE );

scenario( 'whitespace-only global default falls through to the constant',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'options' => array( 'badge_default' => "  \n\t " ) ), $PAGE );

// ---- 2: per-post override ------------------------------------------------
scenario( 'per-post override beats the global default',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'meta' => array( 'badge_override' => 'Just this page.' ),
	       'options' => array( 'badge_default' => 'Global page copy.' ) ),
	'Just this page.' );

scenario( 'per-case override beats the global case default',
	array( 'singular' => true, 'post_type' => 'clinic_case', 'id' => 20,
	       'meta' => array( 'badge_override' => 'This case only.' ),
	       'options' => array( 'badge_clinic_case' => 'Written by Dr C.' ) ),
	'This case only.' );

scenario( 'whitespace-only override falls through to the global default',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'meta' => array( 'badge_override' => '   ' ),
	       'options' => array( 'badge_default' => 'Global page copy.' ) ),
	'Global page copy.' );

scenario( 'override on a post is ignored on an archive (no post context)',
	array( 'pt_archive' => 'clinic_case',
	       'meta' => array( 'badge_override' => 'Should not appear.' ) ),
	$CASE );

// ---- 1: hide toggle ------------------------------------------------------
scenario( 'hide toggle returns empty string',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'meta' => array( 'badge_hide' => true ) ), '' );

scenario( 'hide beats an override on the same post',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'meta' => array( 'badge_hide' => true, 'badge_override' => 'Ignored.' ) ), '' );

scenario( 'hide beats the global default too',
	array( 'singular' => true, 'post_type' => 'clinic_case', 'id' => 20,
	       'meta' => array( 'badge_hide' => 1 ),
	       'options' => array( 'badge_clinic_case' => 'Written by Dr C.' ) ), '' );

scenario( 'hide unticked (0) does not hide',
	array( 'singular' => true, 'post_type' => 'page', 'id' => 10,
	       'meta' => array( 'badge_hide' => 0 ) ), $PAGE );

// ---- allowed-HTML shape --------------------------------------------------
echo "\nwellspring_badge_allowed_html()\n\n";
$allowed = wellspring_badge_allowed_html();
foreach ( array( 'strong', 'em', 'a', 'br' ) as $tag ) {
	if ( isset( $allowed[ $tag ] ) ) { $pass++; echo "  PASS  <$tag> permitted\n"; }
	else { $fail++; echo "  FAIL  <$tag> should be permitted\n"; }
}
foreach ( array( 'script', 'p', 'div', 'h2', 'iframe', 'style' ) as $tag ) {
	if ( ! isset( $allowed[ $tag ] ) ) { $pass++; echo "  PASS  <$tag> blocked\n"; }
	else { $fail++; echo "  FAIL  <$tag> must NOT be permitted (badge is a single <p>)\n"; }
}

printf( "\n%d passed, %d failed\n", $pass, $fail );
exit( $fail > 0 ? 1 : 0 );
