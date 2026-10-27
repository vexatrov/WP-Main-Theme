<?php
/** Isolated URL contracts: no database or preview mutation. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function sanitize_text_field( $value ) { return strip_tags( $value ); }
function wp_unslash( $value ) { return stripslashes( $value ); }
function is_shop() { return true; }
function is_product_taxonomy() { return false; }
function is_search() { return false; }
function is_product_category() { return ! empty( $GLOBALS['category_archive'] ); }
function is_tax( $taxonomy ) { return 'product_brand' === $taxonomy && ! empty( $GLOBALS['brand_archive'] ); }
function get_queried_object_id() { return $GLOBALS['term_id'] ?? 12; }
function absint( $value ) { return abs( (int) $value ); }
function is_wp_error( $value ) { return false; }
function get_term_link( $term ) { return 'https://example.test/product-category/' . $term->slug . '/'; }
function get_term_meta( $id, $key, $single ) { return $GLOBALS['term_thumbnail'] ?? 0; }
function wp_get_attachment_image( $id, $size, $icon, $attributes ) { $GLOBALS['image_attributes'] = $attributes; return 99 === $id ? '<img src="real-thumbnail.jpg" alt="">' : ''; }
function taxonomy_exists( $taxonomy ) { return true; }
function __( $text, $domain = '' ) { return $text; }
function esc_html__( $text, $domain = '' ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_html( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_attr( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_url( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function apply_filters( $hook, $value, ...$args ) { return $value; }
function wp_unique_id( $prefix ) { static $id = 0; return $prefix . ++$id; }
function get_woocommerce_currency_symbol() { return 'تومان'; }
function wc_query_string_form_fields( $values, $exclude ) { $GLOBALS['excluded_fields'] = $exclude; }
class WC_Widget_Price_Filter { public $id_base = 'woocommerce_price_filter'; }
function get_pagenum_link( ...$args ) { return 'https://example.test/product-category/needles/?' . http_build_query( $_GET ); }
function remove_query_arg( $keys, $url ) {
	$parts = parse_url( $url ); parse_str( $parts['query'] ?? '', $query );
	foreach ( (array) $keys as $key ) { unset( $query[ $key ] ); }
	return 'https://example.test' . $parts['path'] . ( $query ? '?' . http_build_query( $query ) : '' );
}
function add_query_arg( $key, $value, $url ) {
	$parts = parse_url( $url ); parse_str( $parts['query'] ?? '', $query ); $query[ $key ] = $value;
	return 'https://example.test' . $parts['path'] . '?' . http_build_query( $query );
}
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../themes/wp-main-theme/inc/catalog-layout.php';
$_GET = array( 's' => 'needle', 'post_type' => 'product', 'orderby' => 'price', 'filter_product_brand' => '12', 'filter_system' => 'cartridge,classic', 'query_type_system' => 'or', 'rating_filter' => '4,5', 'min_price' => '100', 'max_price' => '500', 'paged' => '3', 'product-page' => '3' );
parse_str( parse_url( wpmain_catalog_remove_filter_url( 'filter_system', 'cartridge' ), PHP_URL_QUERY ), $query );
check( 'classic' === $query['filter_system'] && 'or' === $query['query_type_system'], 'Retain remaining facet terms and OR semantics' );
check( '12' === $query['filter_product_brand'] && 'needle' === $query['s'] && 'price' === $query['orderby'], 'Retain brand/search/sort' );
check( ! isset( $query['paged'], $query['product-page'] ), 'Reset pagination' );
$_GET['filter_system'] = 'classic';
parse_str( parse_url( wpmain_catalog_remove_filter_url( 'filter_system', 'classic' ), PHP_URL_QUERY ), $query );
check( ! isset( $query['filter_system'] ) && ! isset( $query['query_type_system'] ), 'Remove orphan query_type when final term removed' );
parse_str( parse_url( wpmain_catalog_remove_filter_url( 'rating_filter', '4' ), PHP_URL_QUERY ), $query );
check( '5' === $query['rating_filter'] && '100' === $query['min_price'], 'Remove only selected rating' );
parse_str( parse_url( wpmain_catalog_reset_url(), PHP_URL_QUERY ), $query );
check( array( 's' => 'needle', 'post_type' => 'product', 'orderby' => 'price' ) === $query, 'Clear only filters and pagination' );
$_GET['min_price'] = array( '100' );
check( '' === wpmain_catalog_query_value( 'min_price' ), 'Reject array parameters' );
$_GET['max_price'] = '<script>oops</script>';
ob_start();
$result = wpmain_catalog_price_widget( array(), new WC_Widget_Price_Filter(), array( 'before_widget' => '<section>', 'after_widget' => '</section>', 'before_title' => '<h3>', 'after_title' => '</h3>' ) );
$html = ob_get_clean();
check( false === $result, 'Stop native slider output after accessible replacement' );
check( 2 === substr_count( $html, 'type="number"' ) && 2 === substr_count( $html, 'value=""' ), 'Reject malformed and array bounds in rendered controls' );
preg_match_all( '/<input id="([^"]+)"/', $html, $inputs );
foreach ( $inputs[1] as $id ) { check( false !== strpos( $html, 'for="' . $id . '"' ), 'Explicit price input labels' ); }
check( in_array( 'min_price', $GLOBALS['excluded_fields'], true ) && in_array( 'paged', $GLOBALS['excluded_fields'], true ), 'Native form preservation excludes duplicated prices and pagination' );
check( array( 'title' => 'Other' ) === wpmain_catalog_price_widget( array( 'title' => 'Other' ), new stdClass(), array() ), 'Other widgets remain untouched' );
$category = (object) array( 'slug' => 'needles' );
$_GET = array(); $GLOBALS['brand_archive'] = true;
check( false !== strpos( wpmain_catalog_category_url( $category ), 'filter_product_brand=12' ), 'Category drilldown from brand archive keeps current brand' );
check( array() === wpmain_catalog_available_brands(), 'Brand archive never renders an unrelated brand selector' );
$GLOBALS['brand_archive'] = false; $GLOBALS['category_archive'] = true;
$_GET = array( 'filter_product_brand' => '34' );
check( false !== strpos( wpmain_catalog_category_url( $category ), 'filter_product_brand=34' ), 'Category drilldown keeps selected brand' );
$_GET['filter_product_brand'] = array( '34' );
check( false === strpos( wpmain_catalog_category_url( $category ), 'filter_product_brand' ), 'Malformed brand input cannot become a navigation scope' );
$GLOBALS['term_thumbnail'] = 99;
ob_start(); wpmain_catalog_archive_thumbnail(); $image = ob_get_clean();
check( false !== strpos( $image, 'catalog-archive-thumbnail' ) && '' === $GLOBALS['image_attributes']['alt'], 'Current category artwork is real and decorative beside the native heading' );
$GLOBALS['category_archive'] = false; $GLOBALS['brand_archive'] = true;
ob_start(); wpmain_catalog_archive_thumbnail(); $image = ob_get_clean();
check( false !== strpos( $image, 'real-thumbnail.jpg' ), 'Current brand uses its native taxonomy thumbnail' );
foreach ( array( 0, 100 ) as $id ) {
	$GLOBALS['term_thumbnail'] = $id;
	ob_start(); wpmain_catalog_archive_thumbnail(); $image = ob_get_clean();
	check( '' === $image, 'Absent or missing artwork never fabricates a logo or placeholder box' );
}
echo "PASS: catalog single selection removal, query_type cleanup, pagination and context preservation.\n";
