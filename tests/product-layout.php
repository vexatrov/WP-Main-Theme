<?php
/** Behavioral hook-order check for the native product layout and supporting content. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['hooks'] = array();
function add_action( $hook, $callback, $priority = 10, $args = 1 ) { $GLOBALS['hooks'][ $hook ][] = array( $priority, $callback ); }
function add_filter( ...$args ) {}
function __( $message, $domain ) { return $message; }
function post_password_required() { return false; }
function wpmain_catalog_guidance( $id ) { return array( 'compatible' => array( 2 ), 'alternatives' => array(), 'guides' => array( 3 ) ); }
function get_the_terms( $id, $taxonomy ) { return false; }
function taxonomy_exists( $taxonomy ) { return false; }
function is_wp_error( $value ) { return false; }
function get_post_field( $field, $id ) { return ''; }
function wc_get_product( $id ) { return $GLOBALS['product']; }
function get_template_part( $path, $name, $args ) { echo str_ends_with( $path, '/guides' ) ? '<guides>' : '<recommendations>'; }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
function run_hook( $name ) {
	$callbacks = $GLOBALS['hooks'][ $name ] ?? array();
	usort( $callbacks, function ( $a, $b ) { return $a[0] <=> $b[0]; } );
	foreach ( $callbacks as $item ) { call_user_func( $item[1] ); }
}
require __DIR__ . '/../themes/wp-main-theme/inc/product-details.php';
$product = new class {
	public function get_id() { return 1; }
	public function get_status() { return 'publish'; }
	public function get_catalog_visibility() { return 'visible'; }
	public function is_visible() { return true; }
};
// Native Woo registers gallery at 20 and tabs at 10; the summary sits between hooks.
add_action( 'woocommerce_before_single_product_summary', function () { echo '<gallery>'; }, 20 );
add_action( 'woocommerce_after_single_product_summary', function () { echo '<tabs>'; }, 10 );
ob_start();
run_hook( 'woocommerce_before_single_product_summary' ); echo '<summary>';
run_hook( 'woocommerce_after_single_product_summary' );
$html = ob_get_clean();
check( str_starts_with( $html, '<div class="product-purchase-layout"><gallery><summary></div><tabs>' ), 'Native gallery and summary share a wrapper that closes before tabs.' );
check( strpos( $html, '<tabs>' ) < strpos( $html, '<guides>' ) && strpos( $html, '<guides>' ) < strpos( $html, '<recommendations>' ), 'Buying guides follow native tabs and precede recommendation rails.' );
check( 1 === substr_count( $html, '<guides>' ) && 1 === substr_count( $html, '<recommendations>' ), 'Supporting sections render once in the intended order.' );
echo "PASS: native gallery/summary wrapper closes before tabs; guides follow tabs before recommendation rails.\n";
