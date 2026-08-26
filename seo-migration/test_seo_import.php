<?php
/**
 * Stub-WordPress harness for inc/seo-import.php.
 *
 * Exercises the classification logic — the part that decides whether a value
 * gets written, left alone, or blocked as a conflict. That decision is the only
 * thing standing between a re-run and a clobbered hand edit, so it is tested
 * rather than eyeballed.
 *
 * One scenario per process. Run via the loop in the accompanying command.
 */

$SCENARIO = $argv[1] ?? '';
$STATE    = array();

function st( $k, $d = null ) { global $STATE; return $STATE[ $k ] ?? $d; }

class WP_Post {
	public $ID = 0, $post_status = 'publish', $post_type = 'page';
	public function __construct( $a = array() ) { foreach ( $a as $k => $v ) { $this->$k = $v; } }
}

// ---------------------------------------------------------------- WP stubs
function add_action( $t, $cb, $p = 10, $a = 1 ) {}
function add_filter( $t, $cb, $p = 10, $a = 1 ) {}
function apply_filters( $t, $v ) { return $v; }
function __( $s, $d = '' ) { return $s; }
function esc_html__( $s, $d = '' ) { return $s; }
function esc_html( $s ) { return $s; }
function esc_attr( $s ) { return $s; }
function get_template_directory() { return __DIR__; }
function get_option( $k, $d = false ) { return st( "option:$k", $d ); }
function get_page_by_path( $p ) { $m = st( 'pages', array() ); return $m[ $p ] ?? null; }
function get_theme_mod( $k, $d = false ) { return st( "mod:$k", $d ); }
function set_theme_mod( $k, $v ) { global $STATE; $STATE[ "mod:$k" ] = $v; }
function get_field( $n, $id = null ) { return st( "field:$id:$n", '' ); }
function update_field( $n, $v, $id ) { global $STATE; $STATE[ "field:$id:$n" ] = $v; return true; }
function get_post_meta( $id, $k, $single = false ) { return st( "meta:$id:$k", '' ); }
function update_post_meta( $id, $k, $v ) { global $STATE; $STATE[ "meta:$id:$k" ] = $v; return true; }
function add_management_page( ...$a ) {}
function current_user_can( $c ) { return true; }
function wp_die( $m ) { throw new Exception( $m ); }
function wellspring_seo_plugin_active() { return false; }
function sanitize_text_field( $s ) { return $s; }
function wp_unslash( $s ) { return $s; }
function check_admin_referer( $a ) { return true; }
function wp_nonce_field( $a ) {}

define( 'ABSPATH', __DIR__ . '/' );
require __DIR__ . '/../inc/seo-import.php';

// ---------------------------------------------------------------- runner
$pass = 0; $fail = 0;
function check( $label, $got, $want ) {
	global $pass, $fail;
	$ok = ( $got === $want );
	$ok ? $pass++ : $fail++;
	printf( "  %s  %-46s%s\n", $ok ? 'PASS' : 'FAIL', $label,
		$ok ? '' : '  got=' . var_export( $got, true ) . ' want=' . var_export( $want, true ) );
}

$PAGE = array( 'about' => new WP_Post( array( 'ID' => 12 ) ) );
$ENTRY = array(
	'source_path' => '/about',
	'target_type' => 'page',
	'target_key'  => 'about',
	'title'       => 'About',
	'description' => 'Meet Dr. Laura Cowburn.',
);

switch ( $SCENARIO ) {

	case 'fresh':
		echo "SCENARIO: empty target -> will write\n";
		$STATE = array( 'pages' => $PAGE );
		$p = wellspring_seo_import_plan( array( $ENTRY ) )[0];
		check( 'target resolved', $p['target']['kind'], 'post' );
		check( 'title status',    $p['fields']['title']['status'], 'write' );
		check( 'desc status',     $p['fields']['description']['status'], 'write' );
		break;

	case 'idempotent':
		echo "SCENARIO: values already correct -> unchanged, nothing rewritten\n";
		$STATE = array(
			'pages' => $PAGE,
			'field:12:seo_title'       => 'About',
			'field:12:seo_description' => 'Meet Dr. Laura Cowburn.',
		);
		$p = wellspring_seo_import_plan( array( $ENTRY ) )[0];
		check( 'title unchanged', $p['fields']['title']['status'], 'unchanged' );
		check( 'desc unchanged',  $p['fields']['description']['status'], 'unchanged' );
		$log = wellspring_seo_import_run( array( $ENTRY ), array() );
		check( 'run reports 0 written', str_contains( $log[0], '0 written' ), true );
		break;

	case 'reimport':
		echo "SCENARIO: our own earlier value differs -> safe update\n";
		$STATE = array(
			'pages' => $PAGE,
			'field:12:seo_title' => 'Old imported title',
			'meta:12:_ws_seo_import_sha_title' => hash( 'sha256', 'Old imported title' ),
		);
		$p = wellspring_seo_import_plan( array( $ENTRY ) )[0];
		check( 'classified update', $p['fields']['title']['status'], 'update' );
		break;

	case 'conflict':
		echo "SCENARIO: hand-edited value -> conflict, NOT overwritten by default\n";
		$STATE = array(
			'pages' => $PAGE,
			'field:12:seo_title' => 'Amber wrote this by hand',
			// no stamp, or a stamp that no longer matches
			'meta:12:_ws_seo_import_sha_title' => hash( 'sha256', 'something else entirely' ),
		);
		$p = wellspring_seo_import_plan( array( $ENTRY ) )[0];
		check( 'classified conflict', $p['fields']['title']['status'], 'conflict' );

		$log = wellspring_seo_import_run( array( $ENTRY ), array() );
		check( 'left alone without tick', st( 'field:12:seo_title' ), 'Amber wrote this by hand' );
		check( 'logged as conflict', (bool) preg_grep( '/CONFLICT/', $log ), true );

		$log2 = wellspring_seo_import_run( array( $ENTRY ), array( '0:title' ) );
		check( 'overwritten when ticked', st( 'field:12:seo_title' ), 'About' );
		check( 'stamp recorded', st( 'meta:12:_ws_seo_import_sha_title' ), hash( 'sha256', 'About' ) );
		break;

	case 'missing_page':
		echo "SCENARIO: target page does not exist -> reported, never skipped\n";
		$STATE = array( 'pages' => array() );
		$e = $ENTRY; $e['target_key'] = 'events';
		$p = wellspring_seo_import_plan( array( $e ) )[0];
		check( 'target missing',  $p['target']['kind'], 'missing' );
		check( 'title status',    $p['fields']['title']['status'], 'missing' );
		check( 'note names slug', str_contains( $p['fields']['title']['note'], 'events' ), true );
		$log = wellspring_seo_import_run( array( $e ), array() );
		check( 'log names NO TARGET', (bool) preg_grep( '/NO TARGET/', $log ), true );
		check( 'counted as needing attention', str_contains( $log[0], '1 needing attention' ), true );
		break;

	case 'draft_page':
		echo "SCENARIO: page exists but is a draft -> treated as missing\n";
		$STATE = array( 'pages' => array( 'about' => new WP_Post( array( 'ID' => 12, 'post_status' => 'draft' ) ) ) );
		$p = wellspring_seo_import_plan( array( $ENTRY ) )[0];
		check( 'missing kind', $p['target']['kind'], 'missing' );
		check( 'note says draft', str_contains( $p['fields']['title']['note'], 'draft' ), true );
		break;

	case 'nothing_to_port':
		echo "SCENARIO: old site had no description -> nothing to port, not a write\n";
		$STATE = array( 'pages' => $PAGE );
		$e = $ENTRY; $e['description'] = '';
		$p = wellspring_seo_import_plan( array( $e ) )[0];
		check( 'desc nothing',  $p['fields']['description']['status'], 'nothing' );
		check( 'title still write', $p['fields']['title']['status'], 'write' );
		$log = wellspring_seo_import_run( array( $e ), array() );
		// Never set at all, rather than set to empty: the write did not happen.
		check( 'description never touched', st( 'field:12:seo_description' ), null );
		check( 'title was written',         st( 'field:12:seo_title' ), 'About' );
		break;

	case 'front_page':
		echo "SCENARIO: front_page target resolves via page_on_front\n";
		$STATE = array( 'option:page_on_front' => 5 );
		$e = array( 'source_path' => '/', 'target_type' => 'front_page', 'target_key' => '',
			'title' => 'Wellspring Health', 'description' => 'Calgary acupuncture.' );
		$p = wellspring_seo_import_plan( array( $e ) )[0];
		check( 'resolves to post', $p['target']['kind'], 'post' );
		check( 'correct id',       $p['target']['post_id'], 5 );
		check( 'will write',       $p['fields']['title']['status'], 'write' );
		break;

	case 'front_page_unset':
		echo "SCENARIO: no static front page set -> reported, not a silent skip\n";
		$STATE = array();
		$e = array( 'source_path' => '/', 'target_type' => 'front_page', 'target_key' => '', 'title' => 'X', 'description' => 'Y' );
		$p = wellspring_seo_import_plan( array( $e ) )[0];
		check( 'missing kind', $p['target']['kind'], 'missing' );
		check( 'note mentions Reading', str_contains( $p['fields']['title']['note'], 'Reading' ), true );
		break;

	case 'cpt_archive':
		echo "SCENARIO: clinic_case archive writes theme mods, not post meta\n";
		$STATE = array();
		$e = array( 'source_path' => '/clinic-cases', 'target_type' => 'cpt_archive', 'target_key' => 'clinic_case',
			'title' => 'TCM & Acupuncture: Calgary Results', 'description' => 'Read case studies.' );
		$p = wellspring_seo_import_plan( array( $e ) )[0];
		check( 'theme_mod target', $p['target']['kind'], 'theme_mod' );
		check( 'will write',       $p['fields']['title']['status'], 'write' );
		wellspring_seo_import_run( array( $e ), array() );
		check( 'theme mod set',    st( 'mod:clinic_cases_seo_title' ), 'TCM & Acupuncture: Calgary Results' );
		check( 'stamp set',        st( 'mod:clinic_cases_seo_title_import_sha' ), hash( 'sha256', 'TCM & Acupuncture: Calgary Results' ) );
		// second run must be a no-op
		$p2 = wellspring_seo_import_plan( array( $e ) )[0];
		check( 'idempotent on rerun', $p2['fields']['title']['status'], 'unchanged' );
		break;

	case 'byte_exact':
		echo "SCENARIO: awkward characters survive the write unchanged\n";
		$STATE = array( 'pages' => $PAGE );
		$tricky = "Women\u{2019}s Health\u{00A0}& Fertility — “quoted”";
		$e = $ENTRY; $e['title'] = $tricky;
		wellspring_seo_import_run( array( $e ), array() );
		check( 'written verbatim', st( 'field:12:seo_title' ), $tricky );
		check( 'NBSP survived',    str_contains( st( 'field:12:seo_title' ), "\u{00A0}" ), true );
		break;

	default:
		fwrite( STDERR, "unknown scenario: $SCENARIO\n" );
		exit( 2 );
}

printf( "  -> %d passed, %d failed\n\n", $pass, $fail );
exit( $fail ? 1 : 0 );
