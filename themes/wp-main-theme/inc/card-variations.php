<?php
/** Lazy native WooCommerce variation forms for product-card option actions. */
defined( 'ABSPATH' ) || exit;

function wpmain_card_variation_product( $id ) {
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : false;
	return $product && $product->is_type( 'variable' ) && 'publish' === $product->get_status() && '' === $product->get_post_password() && 'hidden' !== $product->get_catalog_visibility() && $product->is_visible() ? $product : false;
}

function wpmain_card_variation_form( $selected ) {
	global $product, $post;
	$previous_product = $product;
	$previous_post = $post;
	$product = $selected;
	$post = get_post( $selected->get_id() );
	ob_start();
	try {
		// False uses native WooCommerce AJAX matching, without embedding variation lists.
		wc_get_template( 'single-product/add-to-cart/variable.php', array( 'available_variations' => false, 'attributes' => $selected->get_variation_attributes(), 'selected_attributes' => $selected->get_default_attributes() ) );
		return ob_get_clean();
	} catch ( Throwable $error ) {
		ob_end_clean();
		throw $error;
	} finally {
		$product = $previous_product;
		$post = $previous_post;
	}
}

function wpmain_card_variation_request() {
	$id = isset( $_GET['product_id'] ) && is_string( $_GET['product_id'] ) && ctype_digit( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
	$product = $id ? wpmain_card_variation_product( $id ) : false;
	nocache_headers();
	if ( ! $product ) { wp_send_json_error( array( 'message' => __( 'These options are unavailable. Please open the product.', 'wp-main-theme' ) ), 404 ); }
	// Public read-only data: no cart mutation or customer information in this endpoint.
	wp_send_json_success( array( 'html' => wpmain_card_variation_form( $product ), 'name' => $product->get_name() ) );
}
add_action( 'wp_ajax_wpmain_card_variations', 'wpmain_card_variation_request' );
add_action( 'wp_ajax_nopriv_wpmain_card_variations', 'wpmain_card_variation_request' );

function wpmain_card_variation_link( $html, $product ) {
	if ( ! $product->is_type( 'variable' ) || ! $product->is_in_stock() || '' === $product->get_price() ) { return $html; }
	static $loaded = false;
	if ( ! $loaded ) {
		$loaded = true;
		wp_enqueue_script( 'wpmain-card-variations', get_template_directory_uri() . '/assets/js/card-variations.js', array( 'jquery', 'wc-add-to-cart-variation' ), wpmain_asset_version( '/assets/js/card-variations.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_enqueue_style( 'wpmain-card-variations', get_template_directory_uri() . '/assets/css/card-variations.css', array( 'wpmain-feedback' ), wpmain_asset_version( '/assets/css/card-variations.css' ) );
		wp_localize_script( 'wpmain-card-variations', 'wpmainCardVariations', array( 'url' => admin_url( 'admin-ajax.php' ), 'loading' => __( 'Loading product options…', 'wp-main-theme' ), 'error' => __( 'Options could not be loaded. Please open the product.', 'wp-main-theme' ) ) );
	}
	return preg_replace( '/<a\b/', '<a data-card-variations="' . esc_attr( $product->get_id() ) . '" aria-haspopup="dialog"', $html, 1 );
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'wpmain_card_variation_link', 20, 2 );

function wpmain_card_variation_dialog() {
	if ( ! wp_script_is( 'wpmain-card-variations', 'enqueued' ) ) { return; }
	// Card loops can enqueue after wp_head, including related rails and shortcodes.
	wp_print_styles( 'wpmain-card-variations' );
	echo '<dialog class="card-options woocommerce" data-card-options aria-labelledby="card-options-title"><div class="card-options-header"><h2 id="card-options-title">' . esc_html__( 'Choose product options', 'wp-main-theme' ) . '</h2><button type="button" data-card-options-close>' . esc_html__( 'Close', 'wp-main-theme' ) . '</button></div><p data-card-options-status role="status" aria-live="polite"></p><div class="product" data-card-options-content></div><a data-card-options-link>' . esc_html__( 'Open full product details', 'wp-main-theme' ) . '</a></dialog>';
}
add_action( 'wp_footer', 'wpmain_card_variation_dialog', 15 );

