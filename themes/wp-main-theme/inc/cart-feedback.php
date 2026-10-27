<?php
/** Cache-safe cart feedback configuration; the browser reads the current guest session. */
defined( 'ABSPATH' ) || exit;

function wpmain_cart_feedback_settings() {
	return array(
		'url' => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
		'count' => __( 'Cart items: %s', 'wp-main-theme' ),
		'added' => __( 'Added to cart.', 'wp-main-theme' ),
		'updated' => __( 'Cart updated.', 'wp-main-theme' ),
		'loading' => __( 'Adding to cart…', 'wp-main-theme' ),
		'error' => __( 'Could not add this item. Please try again or open the product.', 'wp-main-theme' ),
	);
}

/** Only uncached WooCommerce AJAX responses contain a session-specific count. */
function wpmain_cart_count_fragment( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) { return $fragments; }
	$count = max( 0, (int) WC()->cart->get_cart_contents_count() );
	$label = sprintf( __( 'Cart items: %s', 'wp-main-theme' ), $count );
	$fragments['span[data-cart-count]'] = '<span class="cart-count" data-cart-count data-count="' . esc_attr( $count ) . '" aria-label="' . esc_attr( $label ) . '">' . esc_html( $count ) . '</span>';
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'wpmain_cart_count_fragment' );
