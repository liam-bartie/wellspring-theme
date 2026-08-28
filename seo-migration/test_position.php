<?php
/**
 * test_position.php — exercises the "hide the Position select off the home
 * page" filter.
 *
 * Standalone: stubs the WordPress functions the resolver touches, includes
 * inc/acf-fields.php for the real implementation, then drives every branch.
 *
 * Usage: php test_position.php /path/to/theme
 * Exit:  0 all passed, 1 any failure
 */

$theme = $argv[1] ?? dirname( __DIR__ );

$GLOBALS['ws_t'] = array();
function st( $k, $d = null ) {
	return array_key_exists( $k, $GLOBALS['ws_t'] ) ? $GLOBALS['ws_t'][ $k ] : $d;
}

// ------------------------------------------------------------------- stubs
function acf_add_local_field_group( $a ) {}
function add_action( $h, $c, $p = 10, $n = 1 ) {}
function get_option( $k, $d = false ) {
	return 'page_on_front' === $k ? st( 'front_page', 0 ) : $d;
}
function is_admin() { return (bool) st( 'admin', true ); }
function sanitize_text_field( $v ) { return is_string( $v ) ? trim( $v ) : $v; }
function wp_unslash( $v ) { return $v; }

// Record which field keys got a filter attached, so we can prove all seven
// layouts are covered rather than trusting a hand-written list.
$GLOBALS['ws_filters'] = array();
function add_filter( $hook, $cb, $p = 10, $n = 1 ) {
	$GLOBALS['ws_filters'][] = $hook;
}

// Deliberately NOT defining acf_get_form_data(), so the request-superglobal
// fallback path is the one under test. ACF Pro provides it in production; the
// helper is guarded with function_exists for exactly this reason.

require_once $theme . '/inc/acf-fields.php';

$pass = 0;
$fail = 0;
$FIELD = array( 'key' => 'field_sec_text_pos', 'name' => 'position', 'type' => 'select' );

function scenario( $label, array $state, array $get, array $post, $expect_kept ) {
	global $pass, $fail, $FIELD;
	$GLOBALS['ws_t'] = $state;
	$_GET  = $get;
	$_POST = $post;

	$out  = wellspring_hide_section_position( $FIELD );
	$kept = ( false !== $out );

	if ( $kept === $expect_kept ) {
		$pass++;
		printf( "  PASS  %s\n", $label );
	} else {
		$fail++;
		printf( "  FAIL  %s\n        expected field %s, got %s\n",
			$label,
			$expect_kept ? 'KEPT' : 'REMOVED',
			$kept ? 'KEPT' : 'REMOVED' );
	}
}

echo "wellspring_hide_section_position()\n\n";

$HOME  = 12;
$INNER = 340;

// ---- the two cases that matter ------------------------------------------
scenario( 'editing the home page -> select is KEPT',
	array( 'front_page' => $HOME ), array( 'post' => (string) $HOME ), array(), true );

scenario( 'editing an inner page (Pain Relief) -> select is REMOVED',
	array( 'front_page' => $HOME ), array( 'post' => (string) $INNER ), array(), false );

// ---- every request shape ACF uses ---------------------------------------
scenario( 'classic save of an inner page ($_POST[post_ID]) -> REMOVED',
	array( 'front_page' => $HOME ), array(), array( 'post_ID' => (string) $INNER ), false );

scenario( 'ACF ajax add-row on an inner page ($_POST[post_id]) -> REMOVED',
	array( 'front_page' => $HOME ), array(), array( 'post_id' => (string) $INNER ), false );

scenario( 'ACF ajax add-row on the home page -> KEPT',
	array( 'front_page' => $HOME ), array(), array( 'post_id' => (string) $HOME ), true );

scenario( 'integer (not string) post id still matches home -> KEPT',
	array( 'front_page' => $HOME ), array( 'post' => $HOME ), array(), true );

// ---- safe-failure behaviour ---------------------------------------------
scenario( 'no post context at all -> KEPT (never strip it from home by guessing)',
	array( 'front_page' => $HOME ), array(), array(), true );

scenario( 'front page not configured -> KEPT (cannot tell what home is)',
	array( 'front_page' => 0 ), array( 'post' => (string) $INNER ), array(), true );

scenario( 'front-end request -> KEPT untouched (filter must not alter output)',
	array( 'front_page' => $HOME, 'admin' => false ), array( 'post' => (string) $INNER ), array(), true );

scenario( 'empty string post id is treated as no context -> KEPT',
	array( 'front_page' => $HOME ), array( 'post' => '' ), array(), true );

// ---- the field must come back unmodified when kept ----------------------
$GLOBALS['ws_t'] = array( 'front_page' => $HOME );
$_GET  = array( 'post' => (string) $HOME );
$_POST = array();
$out   = wellspring_hide_section_position( $FIELD );
if ( $out === $FIELD ) {
	$pass++; echo "  PASS  kept field is returned byte-identical (no silent mutation)\n";
} else {
	$fail++; echo "  FAIL  kept field was modified\n";
}

// ---- all seven layouts wired up -----------------------------------------
echo "\nfilter coverage — every layout that has a Position select\n\n";
$expected = array( 'text', 'head', 'it', 'map', 'tm', 'faq', 'cases' );
foreach ( $expected as $slug ) {
	$hook = 'acf/prepare_field/key=field_sec_' . $slug . '_pos';
	if ( in_array( $hook, $GLOBALS['ws_filters'], true ) ) {
		$pass++; printf( "  PASS  %-6s layout filtered\n", $slug );
	} else {
		$fail++; printf( "  FAIL  %-6s layout NOT filtered — its dropdown would still show\n", $slug );
	}
}

printf( "\n%d passed, %d failed\n", $pass, $fail );
exit( $fail > 0 ? 1 : 0 );
