<?php
/** Isolated editor contracts; no WordPress database or preview writes. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_filter( $hook, $callback, ...$args ) { $GLOBALS['test_filters'][ $hook ][] = $callback; }
function remove_filter( $hook, $callback, ...$args ) { $GLOBALS['test_filters'][ $hook ] = array_filter( $GLOBALS['test_filters'][ $hook ] ?? array(), fn( $item ) => $item !== $callback ); }
function absint( $value ) { return abs( (int) $value ); }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function current_user_can( ...$args ) { return $GLOBALS['allowed'] ?? false; }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return strip_tags( $value ); }
function sanitize_textarea_field( $value ) { return strip_tags( $value ); }
function is_wp_error( $value ) { return false; }
function taxonomy_exists( $value ) { return false; }
function wc_attribute_label( $name, $product ) { return $name; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $value, $domain ) { return esc_html( $value ); }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../plugins/wp-store-catalog/wp-store-catalog.php';
require __DIR__ . '/../themes/wp-main-theme/inc/customizer.php';
require __DIR__ . '/../themes/wp-main-theme/inc/product-purchase.php';

check( '3,2,4' === wpmain_sanitize_home_ids( '3,2,3,-1,0,4,2foo,<b>5</b>' ), 'IDs must be positive decimal IDs and preserve unique order.' );
check( '' === wpmain_sanitize_home_ids( array( 1 ) ), 'Malformed Customizer input cannot enter a shortcode.' );
check( 8 === count( explode( ',', wpmain_sanitize_home_ids( '1,2,3,4,5,6,7,8,9' ) ) ), 'Homepage product limit is eight.' );
check( 'journal,catalog,content' === wpmain_sanitize_home_sections( 'journal,invalid,catalog,journal' ), 'Only allowed sections render; omissions append once.' );
check( 'content,catalog,journal' === wpmain_sanitize_home_sections( array() ), 'Malformed section settings restore automatic order.' );
check( 'latest' === wpmain_sanitize_home_product_source( 'unsafe' ), 'Unknown product sources restore latest arrivals.' );

class SpecAttribute {
	public function __construct( public $name, public $visible = true ) {}
	public function get_name() { return $this->name; }
	public function get_visible() { return $this->visible; }
	public function is_taxonomy() { return false; }
	public function get_options() { return array( 'اندازه بلند مدل X12 <script>bad</script>' ); }
}
$product = new class {
	public $values = array();
	public function get_id() { return 1; }
	public function get_attributes() { return array_map( fn( $name ) => new SpecAttribute( $name, 'hidden' !== $name ), array( 'a', 'b', 'hidden', 'c', 'd', 'e' ) ); }
	public function get_meta( $key, $single ) { return $this->values[ $key ] ?? ''; }
	public function update_meta_data( $key, $value ) { $this->values[ $key ] = $value; }
	public function delete_meta_data( $key ) { unset( $this->values[ $key ] ); }
};
check( array( 'e', 'b', 'a', 'c' ) === wpmain_catalog_spec_names( array( 'e', 'hidden', 'b', 'b', array(), 'a', 'missing', 'c', 'd' ), $product ), 'Curated specs keep editor order, deduplicate and reject invisible/missing keys, capped at four.' );
$GLOBALS['allowed'] = true;
$_POST = array( 'wpmain_guidance_nonce' => 'invalid', '_wpmain_key_specs' => array( 'b' ) );
wpmain_catalog_save( $product );
check( array() === $product->values, 'Invalid nonce cannot update specifications.' );
$_POST['wpmain_guidance_nonce'] = 'valid'; $GLOBALS['allowed'] = false;
wpmain_catalog_save( $product );
check( array() === $product->values, 'Missing edit permission cannot update specifications.' );
$GLOBALS['allowed'] = true;
$_POST['_wpmain_key_specs'] = array( 'e', 'hidden', 'b' );
wpmain_catalog_save( $product );
check( array( 'e', 'b' ) === $product->values['_wpmain_key_specs'], 'Authorized save persists only visible attribute references.' );
ob_start(); wpmain_product_key_specs(); $html = ob_get_clean();
check( strpos( $html, '<dt>e</dt>' ) < strpos( $html, '<dt>b</dt>' ) && false === strpos( $html, '<dt>a</dt>' ), 'Purchase summary follows selected order only.' );
check( false === strpos( $html, '<script>' ) && false !== strpos( $html, 'X12' ), 'Mixed Persian and Latin values render with markup stripped.' );
unset( $_POST['_wpmain_key_specs'] ); wpmain_catalog_save( $product );
check( array( 'e', 'b' ) === $product->values['_wpmain_key_specs'], 'Partial saves preserve curated specifications.' );
$_POST['_wpmain_key_specs'] = array( '', '', '', '' ); wpmain_catalog_save( $product );
check( ! isset( $product->values['_wpmain_key_specs'] ), 'Clearing all slots restores the automatic default.' );
ob_start(); wpmain_product_key_specs(); $html = ob_get_clean();
check( 4 === substr_count( $html, '<dt>' ) && false !== strpos( $html, '<dt>a</dt>' ) && false === strpos( $html, '<dt>hidden</dt>' ), 'Default remains first four visible attributes.' );
echo "PASS: homepage ordering/ID sanitization and curated key-spec editor/save/render contracts.\n";

class WooCommerce {}
function get_theme_mod( $key, $default = '' ) { return $GLOBALS['theme_mods'][ $key ] ?? $default; }
function get_terms( $args ) { return array(); }
function __( $value, $domain = '' ) { return $value; }
function esc_html_e( $value, $domain = '' ) { echo esc_html( $value ); }
function esc_url( $value ) { return esc_html( $value ); }
function wc_get_page_permalink( $page ) { return '/shop/'; }
function wc_get_product( $id ) {
	return new class( (int) $id ) {
		public function __construct( private $id ) {}
		public function get_status() { return 3 === $this->id ? 'draft' : 'publish'; }
		public function get_post_password() { return 2 === $this->id ? 'secret' : ''; }
		public function get_catalog_visibility() { return 4 === $this->id ? 'hidden' : 'visible'; }
		public function is_visible() { return 5 !== $this->id; }
	};
}
function do_shortcode( $shortcode ) {
	$query = array( 'post_status' => 'publish' );
	foreach ( $GLOBALS['test_filters']['woocommerce_shortcode_products_query'] ?? array() as $callback ) { $query = $callback( $query ); }
	$GLOBALS['shortcode_query'] = $query;
	$GLOBALS['shortcode'] = $shortcode;
	if ( ! empty( $GLOBALS['throw_shortcode'] ) ) { throw new RuntimeException( 'simulated shortcode error' ); }
	return '';
}
foreach ( array( 'latest', 'featured' ) as $source ) {
	$GLOBALS['theme_mods'] = array( 'wpmain_home_product_source' => $source );
	ob_start(); include __DIR__ . '/../themes/wp-main-theme/template-parts/storefront/catalog.php'; ob_end_clean();
	check( false === $GLOBALS['shortcode_query']['has_password'], 'Automatic and featured queries exclude protected products.' );
	check( empty( $GLOBALS['test_filters']['woocommerce_shortcode_products_query'] ), 'Homepage query filter does not affect later shortcodes.' );
}
$GLOBALS['theme_mods']['wpmain_home_products'] = '2,3,4,5,1';
ob_start(); include __DIR__ . '/../themes/wp-main-theme/template-parts/storefront/catalog.php'; ob_end_clean();
check( false !== strpos( $GLOBALS['shortcode'], ' ids="1"' ), 'Curated products exclude protected, draft, hidden and otherwise invisible products.' );
$GLOBALS['throw_shortcode'] = true;
ob_start();
try { include __DIR__ . '/../themes/wp-main-theme/template-parts/storefront/catalog.php'; } catch ( RuntimeException $error ) {}
ob_end_clean();
check( empty( $GLOBALS['test_filters']['woocommerce_shortcode_products_query'] ), 'Exception also removes scoped query filter.' );
echo "PASS: public-only automatic/featured/curated merchandising and scoped filter cleanup.\n";
