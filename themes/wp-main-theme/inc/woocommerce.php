<?php
defined( 'ABSPATH' ) || exit;

function wpmain_store_support() {
	add_theme_support( 'woocommerce', array( 'product_grid' => array( 'default_columns' => 4, 'min_columns' => 2, 'max_columns' => 4 ) ) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'wpmain_store_support' );

function wpmain_store_wrapper_start() {
	echo '<main id="main-content" class="site-main container store-main" tabindex="-1">';
}

function wpmain_store_wrapper_end() {
	echo '</main>';
}

function wpmain_store_hooks() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	add_action( 'woocommerce_before_main_content', 'wpmain_store_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'wpmain_store_wrapper_end', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	add_action( 'woocommerce_after_shop_loop_item', 'wpmain_card_purchase_start', 7 );
	add_action( 'woocommerce_after_shop_loop_item', 'wpmain_card_purchase_end', 15 );
}
add_action( 'after_setup_theme', 'wpmain_store_hooks', 20 );

function wpmain_card_purchase_start() {
	global $product;
	echo '<div class="card-purchase">';
	if ( ! $product ) { return; }
	if ( $product->is_type( 'variable' ) && '' !== $product->get_price() ) {
		$min = $product->get_variation_price( 'min', true );
		$max = $product->get_variation_price( 'max', true );
		if ( $min !== $max ) {
			$symbol = get_woocommerce_currency_symbol();
			// Keep native currency formatting while printing the common unit once.
			$amount = function ( $value ) use ( $symbol ) { return trim( str_replace( $symbol, '', wp_strip_all_tags( wc_price( $value ) ) ) ); };
			echo '<span class="price card-price-range"><span class="card-price-amounts"><span><span class="screen-reader-text">' . esc_html__( 'From', 'wp-main-theme' ) . ' </span><bdi>' . esc_html( $amount( $min ) ) . '</bdi></span><span><span class="screen-reader-text">' . esc_html__( 'To', 'wp-main-theme' ) . ' </span><bdi>' . esc_html( $amount( $max ) ) . '</bdi></span></span><span class="card-price-unit">' . esc_html( $symbol ) . '</span>' . wp_kses_post( $product->get_price_suffix() ) . '</span>';
			return;
		}
	}
	woocommerce_template_loop_price();
}

function wpmain_card_purchase_end() {
	echo '</div>';
}
