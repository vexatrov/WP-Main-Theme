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
}
add_action( 'after_setup_theme', 'wpmain_store_hooks', 20 );
