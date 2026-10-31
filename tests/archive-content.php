<?php
/** Offline archive editorial save/render contracts; no catalog queries or writes. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['meta'] = array(); $GLOBALS['hooks'] = array();
function add_action( ...$args ) { $GLOBALS['hooks'][] = $args; }
function add_filter( ...$args ) {}
function remove_action( ...$args ) { $GLOBALS['removed'][] = $args; }
function sanitize_text_field( $value ) { return trim( preg_replace( '/\s+/u', ' ', strip_tags( $value ) ) ); }
function wp_html_excerpt( $value, $limit, $suffix ) { return mb_strlen( $value ) > $limit ? mb_substr( $value, 0, $limit ) . $suffix : $value; }
function wp_unslash( $value ) { return $value; }
function wp_verify_nonce( $value, $action ) { return 'valid' === $value; }
function current_user_can( $cap ) { return $GLOBALS['allowed'] ?? false; }
function get_taxonomy( $name ) { return in_array( $name, array( 'product_cat', 'product_brand' ), true ) ? (object) array( 'cap' => (object) array( 'edit_terms' => 'manage_product_terms' ) ) : null; }
function update_term_meta( $id, $key, $value ) { $GLOBALS['meta'][ $id ][ $key ] = $value; }
function get_term_meta( $id, $key, $single ) { return $GLOBALS['meta'][ $id ][ $key ] ?? ''; }
function wp_kses_post( $value ) { $GLOBALS['kses_called'] = true; return strip_tags( $value, '<a><h2><h3><p><ul><li><details><summary><table><tr><th><td>' ); }
function is_product_category() { return 'product_cat' === ( $GLOBALS['taxonomy'] ?? '' ); }
function is_tax( $taxonomy ) { return $taxonomy === ( $GLOBALS['taxonomy'] ?? '' ); }
function get_queried_object_id() { return 7; }
class WP_Term { public $description = ''; }
function get_queried_object() { $term = new WP_Term(); $term->description = $GLOBALS['native_description'] ?? ''; return $term; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function strip_shortcodes( $value ) { return preg_replace( '/\[[^]]*\]/', '', $value ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_html__( $value, $domain ) { return esc_html( $value ); }
function wpautop( $value ) { return '<p>' . $value . '</p>'; }
function get_posts( $args ) { return array(); }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../plugins/wp-store-catalog/archive-content.php';
require __DIR__ . '/../themes/wp-main-theme/inc/catalog-layout.php';
check( 320 === mb_strlen( wpmain_catalog_archive_plain( str_repeat( 'الف', 200 ), 320 ) ), 'Persian intro is limited by characters, not bytes' );
check( array( 'One', 'Two', 'Three' ) === wpmain_catalog_archive_highlights( array( 'One', '', array(), 'One', '<b>Two</b>', 'Three', 'Four' ) ), 'Facts sanitize/deduplicate, reject arrays and cap at three' );
check( array( 'One', 'Two' ) === wpmain_catalog_archive_highlights( "One\nTwo" ), 'Legacy/newline admin input is supported' );
$_POST = array( 'wpmain_archive_nonce' => 'invalid', 'wpmain_archive_intro' => 'New copy' );
$GLOBALS['allowed'] = true; wpmain_catalog_archive_save( 7, 'product_cat' );
check( array() === $GLOBALS['meta'], 'Invalid nonce cannot write archive copy' );
$_POST['wpmain_archive_nonce'] = 'valid'; $GLOBALS['allowed'] = false; wpmain_catalog_archive_save( 7, 'product_cat' );
check( array() === $GLOBALS['meta'], 'Missing term capability cannot write' );
$GLOBALS['allowed'] = true;
$_POST += array( 'wpmain_archive_highlights' => "Fact one\nFact two\nFact three\nFact four", 'wpmain_archive_content' => '<h2>Choosing</h2><details><summary>Question</summary><p>Answer</p></details><script>bad</script>' );
foreach ( array( 'product_cat', 'product_brand' ) as $taxonomy ) {
	wpmain_catalog_archive_save( 7, $taxonomy );
	check( array( 'Fact one', 'Fact two', 'Fact three' ) === get_term_meta( 7, '_wpmain_archive_highlights', true ), 'Both taxonomies store facts as a capped string array' );
	check( ! str_contains( get_term_meta( 7, '_wpmain_archive_content', true ), '<script>' ) && $GLOBALS['kses_called'], 'Detailed copy uses WordPress safe HTML sanitizer' );
	$GLOBALS['taxonomy'] = $taxonomy;
	ob_start(); wpmain_catalog_archive_intro(); $html = ob_get_clean();
	check( str_contains( $html, 'New copy' ) && 3 === substr_count( $html, '<li>' ), 'Both archives render a short intro and three facts' );
	ob_start(); wpmain_catalog_buying_guidance(); $html = ob_get_clean();
	check( str_contains( $html, '<details>' ) && str_contains( $html, 'catalog-archive-content' ), 'Both archives render safe detailed content below products' );
}
$_POST = array( 'wpmain_archive_nonce' => 'valid', 'wpmain_archive_intro' => array( 'bad' ) );
wpmain_catalog_archive_save( 7, 'product_brand' );
check( 'New copy' === get_term_meta( 7, '_wpmain_archive_intro', true ), 'Partial and malformed field submissions preserve existing copy' );
$GLOBALS['meta'][7] = array( '_wpmain_buying_guide' => 'Legacy category advice' );
$GLOBALS['taxonomy'] = 'product_cat'; $GLOBALS['native_description'] = '<b>Native category overview</b>';
ob_start(); wpmain_catalog_archive_intro(); $html = ob_get_clean();
check( str_contains( $html, 'Native category overview' ) && ! str_contains( $html, '<b>' ), 'Blank intro uses a plain native-description excerpt' );
ob_start(); wpmain_catalog_buying_guidance(); $html = ob_get_clean();
check( str_contains( $html, 'Legacy category advice' ), 'Blank rich content retains old category guidance' );
$GLOBALS['meta'][7] = array();
$GLOBALS['native_description'] = '<p>Full native guidance <a href="/guide/">with a useful link</a>.</p>';
ob_start(); wpmain_catalog_buying_guidance(); $html = ob_get_clean();
check( str_contains( $html, 'Full native guidance' ) && str_contains( $html, 'href="/guide/"' ), 'Native long description and safe links survive when custom guidance is blank' );
$GLOBALS['meta'][7] = array(); $GLOBALS['native_description'] = '';
ob_start(); wpmain_catalog_archive_intro(); wpmain_catalog_buying_guidance(); $html = ob_get_clean();
check( '' === $html, 'Empty editorial fields render no empty UI' );
check( in_array( array( 'woocommerce_after_shop_loop', 'wpmain_catalog_buying_guidance', 30 ), $GLOBALS['hooks'], true ) && in_array( array( 'woocommerce_no_products_found', 'wpmain_catalog_buying_guidance', 30 ), $GLOBALS['hooks'], true ), 'Detailed guide runs after pagination and on empty results' );
echo "PASS: category/brand archive editorial nonce/capabilities, bounded factual copy, safe rich HTML, legacy fallback and no-empty rendering.\n";
