<?php
/** Offline contracts for public comparison choices and product owner content. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_filter( $hook, $callback, ...$args ) { $GLOBALS['filters'][ $hook ] = $callback; }
function __( $value, $domain ) { return $value; }
function esc_html__( $value, $domain ) { return esc_html( $value ); }
function esc_attr__( $value, $domain ) { return esc_attr( $value ); }
function esc_html_e( $value, $domain ) { echo esc_html( $value ); }
function esc_attr_e( $value, $domain ) { echo esc_attr( $value ); }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url( $value ) { return esc_attr( $value ); }
function wp_kses_post( $value ) { return $value; }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function get_post_field( $field, $id ) { return 6 === $id ? 'secret' : ''; }
function post_password_required() { return false; }
function is_wp_error( $value ) { return false; }
function get_option( $name ) { return 'woocommerce_hide_out_of_stock_items' === $name ? 'yes' : '/%postname%/'; }
function get_theme_mod( $name, $default ) { return $GLOBALS['reassurance'] ?? $default; }
function get_the_terms( $id, $taxonomy ) { return 'product_cat' === $taxonomy ? array( (object) array( 'term_id' => 10 ), (object) array( 'term_id' => 20 ), (object) array( 'term_id' => 15 ) ) : array(); }
function get_ancestors( $id, ...$args ) { return 10 === $id ? array() : array( 10 ); }
function wc_get_product_visibility_term_ids() { return array( 'exclude-from-catalog' => 90, 'outofstock' => 91 ); }
function wc_get_product( $id ) { return $GLOBALS['products'][ $id ] ?? false; }
function wc_attribute_label( $name, $product ) { return $name; }
function checked( $value ) { if ( $value ) { echo 'checked="checked"'; } }
function wpmain_catalog_guidance( $id ) { return array( 'comparison' => array( 2 ), 'alternatives' => array( 3 ), 'use_cases' => '', 'audience' => '', 'faq' => array() ); }
class WP_Query {
	public $posts;
	public function __construct( $args ) { $GLOBALS['query_args'] = $args; $this->posts = $GLOBALS['query_ids'] ?? array(); }
}
class DetailProduct {
	public function __construct( public $id, public $status = 'publish', public $visibility = 'visible', public $visible = true ) {}
	public function get_id() { return $this->id; }
	public function get_status() { return $this->status; }
	public function get_catalog_visibility() { return $this->visibility; }
	public function is_visible() { return $this->visible; }
	public function get_name() { return 'Model ' . $this->id; }
	public function get_permalink() { return 'https://example.test/product/' . $this->id; }
	public function get_attributes() { return array(); }
	public function get_image( ...$args ) { return '<img src="model-' . $this->id . '.jpg" alt="">'; }
	public function get_price_html() { return '100 تومان'; }
	public function get_availability() { return array( 'availability' => 'In stock' ); }
	public function is_in_stock() { return true; }
}
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../themes/wp-main-theme/inc/product-details.php';
for ( $id = 1; $id <= 20; $id++ ) { $GLOBALS['products'][ $id ] = new DetailProduct( $id ); }
$GLOBALS['products'][4]->status = 'private';
$GLOBALS['products'][5]->visibility = 'search';
$GLOBALS['products'][7]->visible = false;
check( array( 2, 3 ) === array_map( fn( $item ) => $item->get_id(), wpmain_visible_recommendations( array( 2, 4, 5, 6, 7, 3 ) ) ), 'Private, catalog-hidden, password-protected and Woo-hidden records cannot render, even when is_visible alone would permit admin access.' );
$product = $GLOBALS['products'][1];
check( 15 === wpmain_product_deepest_category( 1 )->term_id, 'Deepest category wins, with stable ID tie break.' );
$GLOBALS['query_ids'] = range( 2, 20 );
$data = wpmain_catalog_guidance( 1 );
$choices = wpmain_product_comparison_candidates( $product, $data );
check( 12 === count( $choices ) && array( 2, 3 ) === array_slice( array_keys( $choices ), 0, 2 ), 'Candidate rail is bounded to twelve and curated products come first.' );
check( 12 === $GLOBALS['query_args']['posts_per_page'] && false === $GLOBALS['query_args']['has_password'] && array( 90, 91 ) === $GLOBALS['query_args']['tax_query'][1]['terms'], 'Native query bounds and excludes password/catalog-hidden/out-of-stock products before output validation.' );
check( array( 3, 2, 8 ) === array_map( fn( $item ) => $item->get_id(), wpmain_product_comparison_selection( array( '3', '4', '2', '2', '-8', array(), '8', '9' ), $choices ) ), 'GET choices must belong to the public candidate set, remain unique and cap at three.' );
check( array() === wpmain_product_comparison_selection( '2', $choices ), 'Malformed GET input is not coerced into a selection.' );
$tabs = $GLOBALS['filters']['woocommerce_product_tabs']( array( 'additional_information' => array( 'title' => 'Additional information' ) ) );
check( 'Specifications' === $tabs['additional_information']['title'] && isset( $tabs['wpmain_comparison'] ), 'Native specifications tab is renamed and comparison can be available without curated defaults.' );
$args = array( 'product' => $product, 'guidance' => $data ); $_GET = array( 'compare_product' => array( '4', '6', '3', '2' ) );
ob_start(); require __DIR__ . '/../themes/wp-main-theme/template-parts/product/comparison.php'; $html = ob_get_clean();
check( str_contains( $html, 'type="checkbox"' ) && str_contains( $html, 'model-1.jpg' ) && str_contains( $html, 'model-2.jpg' ) && str_contains( $html, 'model-3.jpg' ), 'Selectable thumbnails and selected/current product images render together.' );
check( ! str_contains( $html, 'Model 4<' ) && ! str_contains( $html, 'Model 6<' ), 'Forged private/password selections never expose names, images or table data.' );
check( '' === wpmain_product_reassurance_messages( array() ), 'Malformed owner copy is ignored.' );
check( "One\nTwo\nThree\nFour" === wpmain_product_reassurance_messages( "<b>One</b>\n\nTwo\nThree\nFour\nFive" ), 'Owner copy strips markup, blank lines and caps four messages.' );
$GLOBALS['reassurance'] = ''; ob_start(); wpmain_product_reassurance(); check( '' === ob_get_clean(), 'Blank shared defaults render no assurance claims.' );
$GLOBALS['reassurance'] = '<script>Current & checked</script>'; ob_start(); wpmain_product_reassurance(); $html = ob_get_clean();
check( ! str_contains( $html, '<script>' ) && str_contains( $html, 'Current &amp; checked' ), 'Configured owner statements are escaped on output.' );
echo "PASS: bounded public thumbnail comparisons, native tab title, deepest category, visibility constraints and empty/editable store-information boxes.\n";
