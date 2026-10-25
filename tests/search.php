<?php
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function is_admin() { return false; }
function check( $value, $message ) { if ( ! $value ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../plugins/wp-store-catalog/search.php';
check( wpmain_catalog_normalize_search( 'PREVIEW-۱۲٣' ) === 'preview123', 'SKU letters, separators and mixed digits normalize.' );
check( wpmain_catalog_normalize_search( 'پِن‌ بيسيم' ) === 'پنبیسیم', 'Marks, zero-width joiners, spaces and Arabic yeh normalize.' );
check( array( '0' ) === wpmain_catalog_search_terms( '۰' ), 'The numeric zero is a valid search term.' );
check( count( wpmain_catalog_search_terms( '1 2 3 4 5 6 7 8 9 10' ) ) === 8, 'Search terms are bounded.' );
check( array() === wpmain_catalog_search_terms( array() ), 'Malformed query is not coerced.' );
$wpdb = new class {
	public $posts = 'wp_posts'; public $postmeta = 'wp_postmeta';
	public function esc_like( $value ) { return addcslashes( $value, '_%\\' ); }
	public function prepare( $sql, ...$args ) { foreach ( $args as $value ) { $sql = preg_replace( '/%s/', "'" . str_replace( "'", "''", $value ) . "'", $sql, 1 ); } return $sql; }
};
$query = new class {
	public $type = 'product'; public $text = 'پن مست';
	public function get( $key ) { return array( 'post_type' => $this->type, 's' => $this->text )[ $key ] ?? ''; }
	public function is_main_query() { return true; } public function is_search() { return true; }
};
$sql = wpmain_catalog_product_search( 'original', $query );
check( str_contains( $sql, "meta_key = '_wpmain_search_document'" ) && str_contains( $sql, ' AND wpmain_doc.meta_value LIKE ' ), 'Terms use AND within a single normalized product document.' );
check( ! str_contains( $sql, 'REPLACE(' ) && str_contains( $sql, "post_password = ''" ), 'No nested SQL normalization or protected products.' );
$query->type = 'post'; check( 'original' === wpmain_catalog_product_search( 'original', $query ), 'Blog searches retain WordPress behavior.' );
$query->type = 'product'; $query->text = '!'; check( ' AND 1=0 ' === wpmain_catalog_product_search( 'original', $query ), 'Empty normalized search does not expose the catalog.' );
echo "PASS: normalization, zero, query bounds, guarded product searches and safe document matching.\n";
