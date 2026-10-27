<?php
/** Session-specific fragment contracts, without WordPress or a mutable live cart. */
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
function __( $text, $domain ) { return $text; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function WC() { return $GLOBALS['wc']; }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../themes/wp-main-theme/inc/cart-feedback.php';
$GLOBALS['wc'] = (object) array( 'cart' => null );
check( array( 'existing' => 'preserved' ) === wpmain_cart_count_fragment( array( 'existing' => 'preserved' ) ), 'No cart leaves fragments unchanged.' );
$GLOBALS['wc']->cart = new class {
	public $count = 4;
	public function get_cart_contents_count() { return $this->count; }
};
$fragments = wpmain_cart_count_fragment( array( 'existing' => 'preserved' ) );
$html = $fragments['span[data-cart-count]'];
check( 'preserved' === $fragments['existing'], 'Other native fragments are preserved.' );
check( str_contains( $html, 'data-count="4"' ) && str_contains( $html, '>4</span>' ) && str_contains( $html, 'aria-label="Cart items: 4"' ), 'Uncached fragment carries authoritative quantity and accessible label.' );
check( ! str_contains( $html, 'hidden' ), 'Fresh fragment can be displayed without waiting for another request.' );
$GLOBALS['wc']->cart->count = 0;
check( str_contains( wpmain_cart_count_fragment( array() )['span[data-cart-count]'], 'data-count="0"' ), 'Empty cart is an explicit zero rather than unknown.' );
$header = file_get_contents( __DIR__ . '/../themes/wp-main-theme/header.php' );
check( str_contains( $header, 'data-cart-count hidden></span>' ), 'Server-rendered header remains cache-neutral.' );
echo "PASS: uncached authoritative cart fragments, zero count, unrelated fragments and cache-neutral header.\n";
