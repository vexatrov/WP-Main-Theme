<?php
defined( 'ABSPATH' ) || exit;

function wpmain_asset_version( $relative_path ) {
	$path = get_template_directory() . $relative_path;
	return file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme( get_template() )->get( 'Version' );
}

function wpmain_assets() {
	wp_enqueue_style( 'wpmain', get_template_directory_uri() . '/assets/css/main.css', array(), wpmain_asset_version( '/assets/css/main.css' ) );
	if ( is_rtl() ) {
		wp_enqueue_style( 'wpmain-rtl', get_template_directory_uri() . '/rtl.css', array( 'wpmain' ), wpmain_asset_version( '/rtl.css' ) );
	}
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'wpmain-store', get_template_directory_uri() . '/assets/css/store.css', array( 'wpmain' ), wpmain_asset_version( '/assets/css/store.css' ) );
		if ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) {
			wp_enqueue_style( 'wpmain-catalog', get_template_directory_uri() . '/assets/css/catalog-enhancements.css', array( 'wpmain-store' ), wpmain_asset_version( '/assets/css/catalog-enhancements.css' ) );
		}
		wp_enqueue_script( 'wpmain-cart-feedback', get_template_directory_uri() . '/assets/js/cart-feedback.js', array( 'jquery' ), wpmain_asset_version( '/assets/js/cart-feedback.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script( 'wpmain-cart-feedback', 'wpmainCartFeedback', wpmain_cart_feedback_settings() );
		if ( is_product() ) { wp_enqueue_style( 'wpmain-purchase', get_template_directory_uri() . '/assets/css/product-purchase.css', array( 'wpmain-store' ), wpmain_asset_version( '/assets/css/product-purchase.css' ) ); }
		if ( function_exists( 'wpmain_catalog_suggestions' ) ) { wp_enqueue_script( 'wpmain-search', get_template_directory_uri() . '/assets/js/search.js', array(), wpmain_asset_version( '/assets/js/search.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) ); }
	}
	if ( is_singular( 'post' ) ) {
		wp_enqueue_style( 'wpmain-editorial', get_template_directory_uri() . '/assets/css/editorial.css', array( 'wpmain' ), wpmain_asset_version( '/assets/css/editorial.css' ) );
	}
	// Also supplies submenu controls when WooCommerce is inactive.
	wp_enqueue_style( 'wpmain-feedback', get_template_directory_uri() . '/assets/css/purchase-feedback.css', class_exists( 'WooCommerce' ) ? array( 'wpmain-store' ) : array( 'wpmain' ), wpmain_asset_version( '/assets/css/purchase-feedback.css' ) );
	if ( is_child_theme() ) {
		wp_enqueue_style( 'wpmain-brand', get_stylesheet_uri(), array( 'wpmain' ), wp_get_theme()->get( 'Version' ) );
	}
	wp_enqueue_script( 'wpmain-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), wpmain_asset_version( '/assets/js/navigation.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'wpmain_assets' );
