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
