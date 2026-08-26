<?php
/**
 * Stub-WordPress harness for inc/seo.php.
 *
 * Not a WordPress install — just enough of the API to drive
 * wellspring_seo_resolve() and the wp_head output through every context and
 * fallback branch, so the logic is tested rather than merely parsed.
 *
 * One scenario per process (resolve() memoises per request, as it should in
 * production), dispatched by argv[1]. Run via run_tests.sh.
 */

$SCENARIO = $argv[1] ?? '';
$STATE    = array();

function st( $k, $d = null ) {
	global $STATE;
	return array_key_exists( $k, $STATE ) ? $STATE[ $k ] : $d;
}

// ----------------------------------------------------------------- WP classes
class WP_Post {
	public $ID = 0, $post_type = 'page', $post_excerpt = '', $post_title = '';
	public function __construct( $a = array() ) { foreach ( $a as $k => $v ) { $this->$k = $v; } }
}
class WP_Term {
	public $term_id = 0, $taxonomy = '', $name = '', $description = '';
	public function __construct( $a = array() ) { foreach ( $a as $k => $v ) { $this->$k = $v; } }
}
class WP_Error {}

// ------------------------------------------------------------- hook recorders
$HOOKS = array( 'filters' => array(), 'actions' => array() );
function add_filter( $t, $cb, $p = 10, $a = 1 ) { global $HOOKS; $HOOKS['filters'][ $t ][] = $cb; }
function add_action( $t, $cb, $p = 10, $a = 1 ) { global $HOOKS; $HOOKS['actions'][ $t ][] = $cb; }
function apply_filters( $t, $value ) {
	global $HOOKS;
	$args = array_slice( func_get_args(), 2 );
	foreach ( $HOOKS['filters'][ $t ] ?? array() as $cb ) {
		$value = $cb( ...array_merge( array( $value ), $args ) );
	}
	return $value;
}
function render_hook( $t ) {
	global $HOOKS;
	ob_start();
	foreach ( $HOOKS['actions'][ $t ] ?? array() as $cb ) { $cb(); }
	return ob_get_clean();
}

// ------------------------------------------------------------ conditional tags
function is_front_page() { return (bool) st( 'is_front_page' ); }
function is_home()       { return (bool) st( 'is_home' ); }
function is_singular( $t = '' ) { return (bool) st( 'is_singular' ); }
function is_tax( $t = '' )      { return (bool) st( 'is_tax' ); }
function is_category()   { return false; }
function is_tag()        { return false; }
function is_post_type_archive( $t = '' ) {
	if ( ! st( 'is_post_type_archive' ) ) { return false; }
	return ( '' === $t ) ? true : ( $t === st( 'archive_post_type' ) );
}
function is_search()     { return (bool) st( 'is_search' ); }
function is_404()        { return (bool) st( 'is_404' ); }
function is_admin()      { return false; }
function is_feed()       { return false; }
function is_wp_error( $t ) { return $t instanceof WP_Error; }

// -------------------------------------------------------------- data accessors
function get_option( $k, $d = false )    { return st( "option:$k", $d ); }
function get_theme_mod( $k, $d = false ) { return st( "mod:$k", $d ); }
function get_post( $id = null )          { return st( 'post' ); }
function get_queried_object()            { return st( 'queried' ); }
function get_queried_object_id()         { $p = st( 'post' ); return $p ? $p->ID : 0; }
function get_query_var( $k, $d = '' )    { return st( "qv:$k", $d ); }
function get_field( $name, $target = null ) {
	$map = st( 'fields', array() );
	return $map[ ( null === $target ) ? $name : "$target|$name" ] ?? null;
}
function has_post_thumbnail( $p = null ) { return (bool) st( 'has_thumb' ); }
function get_the_post_thumbnail_url( $p = null, $s = '' ) { return st( 'thumb_url', '' ); }
function get_post_ancestors( $p )        { return st( 'ancestors', array() ); }
function get_the_title( $p = null )      { return st( 'the_title', 'Title' ); }
function get_the_date( $f = '', $p = null )          { return '2026-08-01T00:00:00+00:00'; }
function get_the_modified_date( $f = '', $p = null ) { return '2026-08-20T00:00:00+00:00'; }

// ------------------------------------------------------------------------ urls
function home_url( $p = '' )               { return 'https://example.test' . ( $p ?: '' ); }
function get_permalink( $p = null )         { return st( 'permalink', 'https://example.test/page' ); }
function get_term_link( $t )               { return st( 'term_link', 'https://example.test/focus/pain-relief' ); }
function get_post_type_archive_link( $pt ) { return 'https://example.test/clinic-cases'; }
function get_pagenum_link( $n )            { return 'https://example.test/clinic-cases/page/' . $n; }
function trailingslashit( $s )             { return rtrim( $s, '/' ) . '/'; }
function add_query_arg( $a )               { return '/'; }

// -------------------------------------------------------------------- escaping
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s )  { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function wp_strip_all_tags( $s, $breaks = false ) {
	$s = strip_tags( (string) $s );
	return $breaks ? trim( preg_replace( '/[\r\n\t ]+/', ' ', $s ) ) : $s;
}
function wp_json_encode( $d, $f = 0 ) { return json_encode( $d, $f ); }
function sanitize_text_field( $s )     { return trim( strip_tags( (string) $s ) ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function __( $s, $d = '' )         { return $s; }
function esc_html_e( $s, $d = '' ) { echo esc_html( $s ); }
function esc_attr_e( $s, $d = '' ) { echo esc_attr( $s ); }
function current_user_can( $c )    { return true; }
function get_bloginfo( $k = '' ) {
	return array( 'name' => 'Wellspring Health', 'description' => 'Acupuncture & TCM', 'language' => 'en-CA' )[ $k ] ?? '';
}
function wp_get_document_title() {
	$t = apply_filters( 'pre_get_document_title', '' );
	return ( '' !== $t ) ? $t : st( 'the_title', 'Title' ) . ' – ' . get_bloginfo( 'name' );
}

// seo.php refuses to load outside WordPress; satisfy that guard.
define( 'ABSPATH', __DIR__ . '/' );

// Simulate an active SEO plugin for the guard scenario.
if ( 'plugin_guard' === $SCENARIO ) {
	define( 'WPSEO_VERSION', '99.0' );
}

require __DIR__ . '/../inc/seo.php';

// ---------------------------------------------------------------------- runner
$pass = 0; $fail = 0;
function check( $label, $got, $want ) {
	global $pass, $fail;
	$ok = ( $got === $want );
	$ok ? $pass++ : $fail++;
	printf( "  %s  %-50s%s\n", $ok ? 'PASS' : 'FAIL', $label,
		$ok ? '' : "  got=" . var_export( $got, true ) . " want=" . var_export( $want, true ) );
}
function contains( $label, $haystack, $needle, $want = true ) {
	check( $label, str_contains( $haystack, $needle ), $want );
}

$page = function ( $extra = array() ) {
	return array_merge( array(
		'is_singular'  => true,
		'post'         => new WP_Post( array( 'ID' => 12, 'post_type' => 'page' ) ),
		'the_title'    => 'About',
		'permalink'    => 'https://example.test/about',
		'option:page_on_front' => 5,
	), $extra );
};

switch ( $SCENARIO ) {

	case 'explicit':
		echo "SCENARIO: ported explicit title+description (byte-exact, no suffix)\n";
		$STATE = $page( array( 'fields' => array(
			'12|seo_title'       => 'Acupuncture & TCM Clinic Calgary',
			'12|seo_description' => 'Calgary acupuncture and TCM for pain, stress and sleep.',
		) ) );
		$r = wellspring_seo_resolve();
		check( 'title_source is explicit',   $r['title_source'], 'explicit' );
		check( 'title is verbatim',          $r['title'], 'Acupuncture & TCM Clinic Calgary' );
		check( 'no site name appended',      str_contains( $r['title'], 'Wellspring Health' ), false );
		check( 'description explicit',       $r['description_source'], 'explicit' );
		check( 'document title uses ours',   wp_get_document_title(), 'Acupuncture & TCM Clinic Calgary' );
		$head = render_hook( 'wp_head' );
		contains( 'description tag emitted', $head, 'name="description" content="Calgary acupuncture' );
		contains( 'ampersand escaped in og', $head, 'og:title" content="Acupuncture &amp; TCM' );
		contains( 'no canonical on singular', $head, 'rel="canonical"', false );
		contains( 'og:url present',          $head, 'og:url" content="https://example.test/about' );
		contains( 'twitter card is summary', $head, 'twitter:card" content="summary"' );
		break;

	case 'fallback_title':
		echo "SCENARIO: no explicit title -> core builds Page – Site\n";
		$STATE = $page( array( 'fields' => array() ) );
		$r = wellspring_seo_resolve();
		check( 'title_source is core',  $r['title_source'], 'core' );
		check( 'title left empty',      $r['title'], '' );
		check( 'core title used',       wp_get_document_title(), 'About – Wellspring Health' );
		break;

	case 'fallback_subheading':
		echo "SCENARIO: description falls back to page_subheading\n";
		$STATE = $page( array( 'fields' => array(
			'12|page_subheading' => 'Meet Dr. Laura Cowburn, registered acupuncturist in Calgary.',
		) ) );
		$r = wellspring_seo_resolve();
		check( 'source is page_subheading', $r['description_source'], 'page_subheading' );
		check( 'value used',                $r['description'], 'Meet Dr. Laura Cowburn, registered acupuncturist in Calgary.' );
		check( 'not truncated',             $r['description_truncated'], false );
		break;

	case 'fallback_excerpt':
		echo "SCENARIO: no subheading -> excerpt\n";
		$STATE = $page( array(
			'post'   => new WP_Post( array( 'ID' => 12, 'post_type' => 'page', 'post_excerpt' => 'An excerpt sentence.' ) ),
			'fields' => array( '12|page_subheading' => '   ' ),
		) );
		$r = wellspring_seo_resolve();
		check( 'blank subheading skipped', $r['description_source'], 'excerpt' );
		check( 'excerpt used',             $r['description'], 'An excerpt sentence.' );
		break;

	case 'clinic_case':
		echo "SCENARIO: clinic case uses the At-a-glance summary, not page_subheading\n";
		$STATE = $page( array(
			'post'      => new WP_Post( array( 'ID' => 77, 'post_type' => 'clinic_case', 'post_excerpt' => 'ignored excerpt' ) ),
			'the_title' => 'Chronic low back pain — J.M.',
			'permalink' => 'https://example.test/clinic-case/back-pain-jm',
			'fields'    => array(
				'77|page_subheading' => 'should be ignored for a case',
				'77|summary'         => '<p>Six weeks of acupuncture resolved two years of low back pain.</p>',
			),
		) );
		$r = wellspring_seo_resolve();
		check( 'source is case_summary', $r['description_source'], 'case_summary' );
		check( 'HTML stripped',          $r['description'], 'Six weeks of acupuncture resolved two years of low back pain.' );
		$head = render_hook( 'wp_head' );
		contains( 'MedicalWebPage schema', $head, '"@type":"MedicalWebPage"' );
		contains( 'breadcrumbs emitted',   $head, '"@type":"BreadcrumbList"' );
		break;

	case 'truncation':
		echo "SCENARIO: an EXPLICIT over-long description is never shortened\n";
		$long = 'Acupuncture and Traditional Chinese Medicine in Calgary for chronic pain, insomnia, anxiety, digestive complaints, hormonal imbalance and a great many other conditions besides.';
		$STATE = $page( array( 'fields' => array( '12|seo_description' => $long ) ) );
		$r = wellspring_seo_resolve();
		check( 'emitted verbatim',      $r['description'], $long );
		check( 'not truncated',         $r['description_truncated'], false );
		check( 'flagged over limit',    $r['description_over_limit'], true );
		break;

	case 'truncation_fallback':
		echo "SCENARIO: a DERIVED over-long description truncates on a word boundary\n";
		$long = 'Acupuncture and Traditional Chinese Medicine in Calgary for chronic pain, insomnia, anxiety, digestive complaints, hormonal imbalance and a great many other conditions besides.';
		$STATE = $page( array( 'fields' => array( '12|page_subheading' => $long ) ) );
		$r = wellspring_seo_resolve();
		check( 'source is fallback',      $r['description_source'], 'page_subheading' );
		check( 'flagged truncated',       $r['description_truncated'], true );
		check( 'within limit + ellipsis', mb_strlen( $r['description'] ) <= 161, true );
		check( 'ends with ellipsis',      str_ends_with( $r['description'], '…' ), true );
		// The character before the ellipsis must end a whole word: the next
		// character in the source at that point must have been a space.
		$stem = mb_substr( $r['description'], 0, -1 );
		check( 'cut on a word boundary',  mb_substr( $long, mb_strlen( $stem ), 1 ), ' ' );
		break;

	case 'invisibles':
		echo "SCENARIO: entities and invisible characters survive into the tag\n";
		$STATE = $page( array( 'fields' => array(
			'12|seo_title'       => "Women\u{2019}s Health & Fertility",
			'12|seo_description' => "Pain relief\u{00A0}and sleep support.",
		) ) );
		$r = wellspring_seo_resolve();
		check( 'curly apostrophe kept', str_contains( $r['title'], "\u{2019}" ), true );
		check( 'NBSP kept in desc',     str_contains( $r['description'], "\u{00A0}" ), true );
		$head = render_hook( 'wp_head' );
		contains( 'raw ampersand escaped once', $head, 'Women&#039;s', false );
		contains( 'amp entity in og:title',     $head, '&amp; Fertility' );
		break;

	case 'noindex':
		echo "SCENARIO: noindex toggle and search/404\n";
		$STATE = $page( array( 'fields' => array( '12|seo_noindex' => true ) ) );
		$r = wellspring_seo_resolve();
		check( 'noindex set', $r['noindex'], true );
		$robots = apply_filters( 'wp_robots', array( 'index' => true, 'follow' => true ) );
		check( 'robots noindex true',   $robots['noindex'] ?? null, true );
		check( 'robots index removed',  array_key_exists( 'index', $robots ), false );
		break;

	case 'term_archive':
		echo "SCENARIO: term archive gets the canonical core omits\n";
		$STATE = array(
			'is_tax'    => true,
			'queried'   => new WP_Term( array( 'term_id' => 9, 'taxonomy' => 'case_focus', 'name' => 'Pain Relief', 'description' => 'Cases treating chronic and acute pain.' ) ),
			'term_link' => 'https://example.test/clinic-cases/focus/pain-relief',
			'the_title' => 'Pain Relief',
			'fields'    => array(),
		);
		$r = wellspring_seo_resolve();
		check( 'context is term',        $r['context'], 'term' );
		check( 'canonical populated',    $r['canonical'], 'https://example.test/clinic-cases/focus/pain-relief' );
		check( 'desc from term',         $r['description_source'], 'term_description' );
		$head = render_hook( 'wp_head' );
		contains( 'canonical emitted',   $head, 'rel="canonical" href="https://example.test/clinic-cases/focus/pain-relief"' );
		break;

	case 'term_acf':
		echo "SCENARIO: term SEO field is read with the {taxonomy}_{id} target\n";
		$STATE = array(
			'is_tax'  => true,
			'queried' => new WP_Term( array( 'term_id' => 9, 'taxonomy' => 'case_symptom', 'name' => 'Insomnia' ) ),
			'fields'  => array( 'case_symptom_9|seo_title' => 'Insomnia cases' ),
		);
		$r = wellspring_seo_resolve();
		check( 'explicit term title', $r['title_source'], 'explicit' );
		check( 'value',               $r['title'], 'Insomnia cases' );
		break;

	case 'cpt_archive':
		echo "SCENARIO: CPT archive reads the Customizer settings\n";
		$STATE = array(
			'is_post_type_archive' => true,
			'archive_post_type'    => 'clinic_case',
			'qv:post_type'         => 'clinic_case',
			'mod:clinic_cases_seo_title' => 'Clinic Cases — Real Patient Outcomes',
			'mod:clinic_cases_lede'      => 'A curated record of patients we have worked with.',
		);
		$r = wellspring_seo_resolve();
		check( 'title explicit',       $r['title_source'], 'explicit' );
		check( 'desc from hero lede',  $r['description_source'], 'archive_lede' );
		check( 'canonical set',        $r['canonical'], 'https://example.test/clinic-cases' );
		break;

	case 'paged':
		echo "SCENARIO: page 2 of an archive is self-referential via get_pagenum_link\n";
		$STATE = array(
			'is_post_type_archive' => true,
			'archive_post_type'    => 'clinic_case',
			'qv:post_type'         => 'clinic_case',
			'qv:paged'             => 3,
		);
		$r = wellspring_seo_resolve();
		check( 'canonical is page 3',  $r['canonical'], 'https://example.test/clinic-cases/page/3' );
		check( 'no hand-built slash',  str_contains( $r['canonical'], '//page' ), false );
		break;

	case 'front_static':
		echo "SCENARIO: static front page — core owns the canonical\n";
		$STATE = array(
			'is_front_page'        => true,
			'is_singular'          => true,
			'option:page_on_front' => 5,
			'post'                 => new WP_Post( array( 'ID' => 5 ) ),
			'the_title'            => 'Home',
			'fields'               => array( '5|seo_title' => 'Wellspring Health Acupuncture & TCM Clinic Calgary' ),
		);
		$r = wellspring_seo_resolve();
		check( 'context front',            $r['context'], 'front' );
		check( 'canonical left to core',   $r['canonical'], '' );
		check( 'og_url still populated',   $r['og_url'], 'https://example.test/' );
		$head = render_hook( 'wp_head' );
		contains( 'no duplicate canonical', $head, 'rel="canonical"', false );
		contains( 'WebSite schema',         $head, '"@type":"WebSite"' );
		contains( 'og:type website',        $head, 'og:type" content="website"' );
		break;

	case 'og_image':
		echo "SCENARIO: share image falls back to the featured image\n";
		$STATE = $page( array( 'has_thumb' => true, 'thumb_url' => 'https://example.test/img/hero.jpg', 'fields' => array() ) );
		$r = wellspring_seo_resolve();
		check( 'thumbnail used', $r['og_image'], 'https://example.test/img/hero.jpg' );
		$head = render_hook( 'wp_head' );
		contains( 'large image card', $head, 'twitter:card" content="summary_large_image"' );
		break;

	case 'plugin_guard':
		echo "SCENARIO: an SEO plugin is active — module must go silent\n";
		$STATE = $page( array( 'fields' => array( '12|seo_title' => 'Should not be used' ) ) );
		check( 'guard detects plugin',   wellspring_seo_plugin_active(), true );
		check( 'title filter passes through', apply_filters( 'pre_get_document_title', '' ), '' );
		$head = render_hook( 'wp_head' );
		check( 'no SEO block emitted',   str_contains( $head, 'Wellspring SEO' ), false );
		check( 'no description emitted', str_contains( $head, 'name="description"' ), false );
		contains( 'admin notice mentions dormant', render_hook( 'admin_notices' ), 'dormant' );
		break;

	default:
		fwrite( STDERR, "unknown scenario: $SCENARIO\n" );
		exit( 2 );
}

printf( "  -> %d passed, %d failed\n\n", $pass, $fail );
exit( $fail ? 1 : 0 );
