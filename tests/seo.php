<?php
/** Offline behavioral tests; WordPress/WooCommerce APIs are controlled doubles. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['context'] = array();
$GLOBALS['options'] = array( 'page_for_posts' => 9 );
$GLOBALS['posts'] = array(
	1 => (object) array( 'ID' => 1, 'post_status' => 'publish', 'post_type' => 'product', 'post_password' => '', 'post_excerpt' => 'A "pen" & <b>ink</b>', 'post_content' => 'Public text.' ),
	2 => (object) array( 'ID' => 2, 'post_status' => 'private', 'post_type' => 'product', 'post_password' => '', 'post_excerpt' => 'PRIVATE SECRET', 'post_content' => '' ),
	3 => (object) array( 'ID' => 3, 'post_status' => 'draft', 'post_type' => 'post', 'post_password' => '', 'post_excerpt' => 'DRAFT SECRET', 'post_content' => '' ),
	4 => (object) array( 'ID' => 4, 'post_status' => 'publish', 'post_type' => 'post', 'post_password' => 'password', 'post_excerpt' => 'PROTECTED SECRET', 'post_content' => '' ),
	9 => (object) array( 'ID' => 9, 'post_status' => 'publish', 'post_type' => 'page', 'post_password' => '', 'post_excerpt' => '', 'post_content' => 'Our real published journal.' ),
);
function add_filter( ...$args ) { $GLOBALS['hooks'][] = $args; }
function add_action( ...$args ) { $GLOBALS['hooks'][] = $args; }
function apply_filters( $name, $value ) { return $GLOBALS['overrides'][ $name ] ?? $value; }
function flag( $key ) { return ! empty( $GLOBALS['context'][ $key ] ); }
function is_search() { return flag( 'search' ); }
function is_404() { return flag( '404' ); }
function is_cart() { return flag( 'cart' ); }
function is_checkout() { return flag( 'checkout' ); }
function is_account_page() { return flag( 'account' ); }
function is_singular() { return flag( 'singular' ); }
function is_home() { return flag( 'home' ); }
function is_front_page() { return flag( 'front' ); }
function is_archive() { return flag( 'archive' ); }
function is_tax() { return flag( 'tax' ); }
function is_category() { return false; }
function is_tag() { return false; }
function is_admin() { return flag( 'admin' ); }
function is_feed() { return flag( 'feed' ); }
function is_embed() { return flag( 'embed' ); }
function is_post_type_viewable( $type ) { return 'secret' !== $type; }
function get_queried_object_id() { return $GLOBALS['context']['id'] ?? 1; }
function get_queried_object() { return $GLOBALS['object'] ?? null; }
function get_query_var( $key ) { return $GLOBALS['context'][ $key ] ?? ''; }
function get_pagenum_link( $page, $escape = true ) { return $GLOBALS['page_url'] ?? 'https://shop.invalid/shop/page/' . $page . '/'; }
function wp_get_canonical_url( $id ) { return 'https://shop.invalid/product/' . $id . '/'; }
function wp_parse_url( ...$args ) { return parse_url( ...$args ); }
function remove_query_arg( $remove, $url ) {
	$parts = parse_url( $url ); parse_str( $parts['query'] ?? '', $query );
	foreach ( $remove as $key ) { unset( $query[ $key ] ); }
	return $parts['scheme'] . '://' . $parts['host'] . ( $parts['path'] ?? '/' ) . ( $query ? '?' . http_build_query( $query ) : '' );
}
class WP_Term { public $taxonomy = 'product_cat'; public $description = 'Real category <b>description</b>.'; }
function get_taxonomy( $name ) { return (object) array( 'query_var' => 'product_cat' ); }
function get_post( $id ) { return $GLOBALS['posts'][ $id ] ?? null; }
function get_option( $key ) { return $GLOBALS['options'][ $key ] ?? ''; }
function get_bloginfo( $key ) { return array( 'charset' => 'UTF-8', 'description' => 'Real store tagline.', 'name' => 'Store & Supplies' )[ $key ] ?? ''; }
function wp_strip_all_tags( $text, $remove = false ) { return strip_tags( $text ); }
function strip_shortcodes( $text ) { return preg_replace( '/\[private\].*?\[\/private\]/s', '', $text ); }
function wp_html_excerpt( $text, $limit, $suffix ) { return mb_strlen( $text ) > $limit ? mb_substr( $text, 0, $limit ) . $suffix : $text; }
function wp_get_document_title() { return 'Store "Title"'; }
function get_post_thumbnail_id( $id ) { return 1 === $id ? 10 : 0; }
function wp_get_attachment_image_url( $id, $size ) { return 10 === $id ? 'https://shop.invalid/real.jpg' : false; }
function get_theme_mod( $key ) { return 0; }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return esc_attr( $value ); }
function wc_get_page_id( $key ) { return array( 'cart' => 10, 'checkout' => 11, 'myaccount' => 12 )[ $key ]; }
function get_woocommerce_currency() { return $GLOBALS['currency'] ?? 'IRT'; }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
function head() { ob_start(); wpmain_catalog_seo_head(); return ob_get_clean(); }
require __DIR__ . '/../plugins/wp-store-catalog/seo.php';

foreach ( array( 'search', '404', 'cart', 'checkout', 'account' ) as $scenario ) {
	$GLOBALS['context'] = array( $scenario => true );
	$robots = wpmain_catalog_seo_robots( array( 'index' => true, 'nofollow' => true ) );
	check( true === $robots['noindex'] && ! isset( $robots['index'] ) && true === $robots['nofollow'], $scenario . ' keeps existing restrictions and adds noindex.' );
	check( '' === head(), 'No sharing metadata on ' . $scenario );
}
$GLOBALS['context'] = array( 'archive' => true, 'paged' => 3 );
$_GET = array( 'filter_pa_size' => array( 'unexpected' ) );
check( true === wpmain_catalog_seo_robots( array() )['noindex'], 'Filter keys need not have scalar values to be excluded.' );
foreach ( array( 'query_type_pa_size', 'min_price', 'max_price', 'rating_filter', 'stock_status', 'on_sale', 'orderby', 'compare_product' ) as $key ) {
	$_GET = array( $key => 'x' ); check( wpmain_catalog_seo_noindex(), $key . ' excludes the parameterized page.' );
}
$_GET = array( 'paged' => 3, 'utm_source' => 'newsletter' );
check( ! wpmain_catalog_seo_noindex(), 'Archive pagination and campaign parameters stay indexable.' );
check( array( 'noindex' => true ) === wpmain_catalog_seo_robots( array( 'noindex' => true ) ), 'Existing noindex is never removed.' );
$GLOBALS['page_url'] = 'https://shop.invalid/shop/page/3/?filter_pa_size=large&orderby=price&utm_source=mail';
check( 'https://shop.invalid/shop/page/3/' === wpmain_catalog_seo_canonical(), 'Canonical removes filters/tracking but keeps pagination path.' );
$GLOBALS['page_url'] = 'https://shop.invalid/?post_type=product&paged=3&min_price=0';
check( 'https://shop.invalid/?post_type=product&paged=3' === wpmain_catalog_seo_canonical(), 'Plain permalink routes and pagination survive cleanup.' );
$GLOBALS['context'] = array( 'home' => true, 'paged' => 2 );
$GLOBALS['page_url'] = 'https://shop.invalid/?page_id=9&paged=2&utm_source=mail';
check( 'https://shop.invalid/?page_id=9&paged=2' === wpmain_catalog_seo_canonical(), 'Assigned posts page retains its plain-permalink ID and pagination.' );
$GLOBALS['context'] = array( 'archive' => true, 'paged' => 2 );
$GLOBALS['page_url'] = 'https://shop.invalid/?page_id=15&paged=2&orderby=price&min_price=100';
check( 'https://shop.invalid/?page_id=15&paged=2' === wpmain_catalog_seo_canonical(), 'Assigned shop page retains its plain-permalink ID and pagination while filters are removed.' );
$GLOBALS['page_url'] = 'https://shop.invalid/?pagename=store%2Fshop&paged=2&filter_pa_size=large';
check( 'https://shop.invalid/?pagename=store%2Fshop&paged=2' === wpmain_catalog_seo_canonical(), 'Hierarchical page-slug routes retain their identity and archive pagination.' );
$GLOBALS['object'] = new WP_Term();
$GLOBALS['page_url'] = 'https://shop.invalid/?product_cat=needles&paged=2&filter_brand=x';
check( 'https://shop.invalid/?product_cat=needles&paged=2' === wpmain_catalog_seo_canonical(), 'Taxonomy route query variable is retained.' );
$GLOBALS['context'] = array( 'archive' => true, 'tax' => true );
$_GET = array();
$html = head();
check( 1 === substr_count( $html, 'rel="canonical"' ), 'Archive receives one fallback canonical.' );
check( str_contains( $html, 'Real category description.' ), 'Term description uses real term copy.' );
check( ! str_contains( $html, 'og:image' ) && str_contains( $html, 'content="summary"' ), 'No image is fabricated for a category.' );
$GLOBALS['context'] = array( 'singular' => true, 'id' => 1 );
$html = head();
check( ! str_contains( $html, 'rel="canonical"' ), 'Core singular canonical is not duplicated.' );
check( str_contains( $html, 'A &quot;pen&quot; &amp; ink' ), 'Description is HTML escaped.' );
check( str_contains( $html, 'og:image' ) && str_contains( $html, 'real.jpg' ), 'A real product thumbnail is used.' );
foreach ( array( 2, 3, 4, 100 ) as $id ) {
	$GLOBALS['context']['id'] = $id; check( '' === head(), 'Private, draft, protected and missing posts never leak metadata.' );
}
check( null === wpmain_catalog_seo_public_post( 0 ), 'Zero ID never falls back to the loop post.' );
$GLOBALS['context'] = array( 'home' => true );
check( str_contains( head(), 'Our real published journal.' ), 'Posts-page description comes from published content.' );
$args = wpmain_catalog_seo_sitemap( array( 'post__not_in' => array( 99, 10 ), 'orderby' => 'ID' ), 'page' );
check( array( 99, 10, 11, 12 ) === $args['post__not_in'] && 'ID' === $args['orderby'], 'Sitemap removes commerce pages and preserves existing exclusions.' );
check( array() === wpmain_catalog_seo_sitemap( array(), 'product' ), 'Product sitemap is retained.' );
$GLOBALS['overrides']['wpmain_catalog_seo_enabled'] = false;
check( '' === head() && array() === wpmain_catalog_seo_sitemap( array(), 'page' ), 'External owner disables fallback metadata/sitemap rules.' );
unset( $GLOBALS['overrides']['wpmain_catalog_seo_enabled'] );

$schema = array(
	'@type' => 'Product', 'sku' => '123',
	'offers' => array(
		array( '@type' => 'AggregateOffer', 'priceCurrency' => 'IRT', 'lowPrice' => '100.25', 'highPrice' => '999999999999999999.99', 'offerCount' => 3,
			'priceSpecification' => array(
				array( '@type' => 'UnitPriceSpecification', 'priceType' => 'https://schema.org/ListPrice', 'price' => '125.50' ),
				array( '@type' => 'UnitPriceSpecification', 'priceType' => 'https://schema.org/SalePrice', 'price' => '100.25', 'priceCurrency' => 'IRT', 'referenceQuantity' => array( '@type' => 'QuantitativeValue', 'value' => 2, 'unitCode' => 'C62' ) ),
				array( '@type' => 'UnitPriceSpecification', 'price' => '2.50', 'priceCurrency' => 'USD' ),
			),
			'shippingDetails' => array( 'shippingRate' => array( '@type' => 'MonetaryAmount', 'value' => '12.05', 'currency' => 'IRT' ), 'deliveryTime' => array( 'handlingTime' => array( '@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 3 ) ) ),
		),
		array( '@type' => 'Offer', 'price' => '3.20', 'priceCurrency' => 'USD', 'shippingDetails' => array( 'shippingRate' => array( '@type' => 'MonetaryAmount', 'currency' => 'IRT', 'value' => '0.01' ) ) ),
	),
);
$converted = wpmain_catalog_toman_schema( $schema );
$offer = $converted['offers'][0];
check( 'IRR' === $offer['priceCurrency'] && '1002.5' === $offer['lowPrice'] && '9999999999999999999.9' === $offer['highPrice'], 'Aggregate currency and amounts convert exactly beyond float precision.' );
check( 3 === $offer['offerCount'], 'Offer count is unchanged.' );
check( '1255' === $offer['priceSpecification'][0]['price'] && 'IRR' === $offer['priceSpecification'][0]['priceCurrency'], 'Inherited list-price context is converted and made explicit.' );
check( '1002.5' === $offer['priceSpecification'][1]['price'], 'Sale price is converted.' );
check( 2 === $offer['priceSpecification'][1]['referenceQuantity']['value'], 'Unit quantity remains unchanged.' );
check( '2.50' === $offer['priceSpecification'][2]['price'] && 'USD' === $offer['priceSpecification'][2]['priceCurrency'], 'Explicit mixed currency is untouched.' );
check( '120.5' === $offer['shippingDetails']['shippingRate']['value'] && 'IRR' === $offer['shippingDetails']['shippingRate']['currency'], 'Shipping MonetaryAmount converts.' );
check( 3 === $offer['shippingDetails']['deliveryTime']['handlingTime']['maxValue'], 'Shipping time is not money.' );
check( '0.1' === $converted['offers'][1]['shippingDetails']['shippingRate']['value'] && '3.20' === $converted['offers'][1]['price'], 'Explicit IRT shipping converts inside a USD offer without touching USD.' );
check( $converted === wpmain_catalog_toman_schema( $converted ), 'A second filter pass is idempotent.' );
check( '100.25' === $schema['offers'][0]['lowPrice'], 'Input data is never mutated by reference.' );
foreach ( array( '', '-1', '1,000', '1e3', 'NaN', '1.2.3', array(), true, null ) as $invalid ) {
	$bad = array( '@type' => 'Offer', 'priceCurrency' => 'IRT', 'price' => $invalid );
	check( $bad === wpmain_catalog_toman_schema( $bad ), 'Malformed money is neither converted nor relabeled.' );
}
$bad = array( 'priceCurrency' => 'IRT', 'price' => '1', 'highPrice' => 'bad' );
check( $bad === wpmain_catalog_toman_schema( $bad ), 'Partly malformed node stays coherent.' );
$conflict = array( 'priceCurrency' => 'IRT', 'currency' => 'USD', 'price' => '2' );
check( $conflict === wpmain_catalog_toman_schema( $conflict ), 'Conflicting currency declarations are not guessed.' );
$malformed = array( '@type' => array( array( 'bad' ) ), 'priceCurrency' => array( 'IRT' ), 'price' => '5' );
check( $malformed === wpmain_catalog_toman_schema( $malformed ), 'Malformed type/currency data is safely ignored.' );
$money_range = array( 'priceCurrency' => 'IRT', 'shippingRate' => array( '@type' => 'https://schema.org/MonetaryAmount', 'minValue' => '0.12', 'maxValue' => '2.00' ) );
$money_range = wpmain_catalog_toman_schema( $money_range );
check( '1.2' === $money_range['shippingRate']['minValue'] && '20' === $money_range['shippingRate']['maxValue'] && 'IRR' === $money_range['shippingRate']['currency'], 'Typed inherited monetary ranges convert with explicit output currency.' );
check( $money_range === wpmain_catalog_toman_schema( $money_range ), 'An unchanged context-only parent cannot cause double conversion.' );
$deep = array( 'priceCurrency' => 'IRT', 'price' => '1' );
for ( $depth = 0; $depth < 35; $depth++ ) { $deep = array( 'priceCurrency' => 'IRT', 'price' => '2', 'child' => $deep ); }
check( $deep === wpmain_catalog_toman_schema( $deep ), 'Unexpected depth is rejected atomically before currency changes.' );
$no_context = array( 'price' => '12', 'quantity' => 4 );
check( $no_context === wpmain_catalog_toman_schema( $no_context ), 'Store currency alone never causes a guessed conversion.' );
check( '0' === wpmain_catalog_toman_decimal( '0.00' ) && '0.005' === wpmain_catalog_toman_decimal( '0.0005' ), 'Zero and sub-unit decimals convert exactly.' );
check( null === wpmain_catalog_toman_decimal( INF ), 'Infinite numbers are rejected.' );
$GLOBALS['currency'] = 'IRR'; check( $schema === wpmain_catalog_toman_schema( $schema ), 'Only actual IRT stores activate conversion.' );
$GLOBALS['currency'] = 'IRT'; $GLOBALS['overrides']['wpmain_catalog_toman_schema_enabled'] = false;
check( $schema === wpmain_catalog_toman_schema( $schema ), 'A separate monetary integration can take ownership.' );
unset( $GLOBALS['overrides']['wpmain_catalog_toman_schema_enabled'] );
define( 'WPSEO_VERSION', 'test' );
check( ! wpmain_catalog_seo_enabled(), 'Detected SEO plugin disables fallback ownership.' );
echo "PASS: public metadata, escaped descriptions, robots/query/canonical/sitemap policy, SEO ownership, exact nested/mixed IRT conversion and idempotence.\n";

