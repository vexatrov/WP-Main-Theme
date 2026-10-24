<?php
defined( 'ABSPATH' ) || exit;

function wpmain_visible_recommendations( $ids ) {
	$products = array();
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( $product && $product->is_visible() && ! get_post_field( 'post_password', $id ) ) { $products[] = $product; }
	}
	return $products;
}

function wpmain_product_detail_tab( $key, $tab ) {
	global $product;
	if ( ! $product || ! function_exists( 'wpmain_catalog_guidance' ) ) { return; }
	get_template_part( 'template-parts/product/details', null, array( 'kind' => $key, 'product' => $product, 'guidance' => wpmain_catalog_guidance( $product->get_id() ) ) );
}

add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	global $product;
	if ( ! $product || ! function_exists( 'wpmain_catalog_guidance' ) || post_password_required() ) { return $tabs; }
	$data = wpmain_catalog_guidance( $product->get_id() );
	$custom = array();
	if ( $data['use_cases'] || $data['audience'] ) { $custom['wpmain_usage'] = array( 'title' => __( 'Use cases & suitability', 'wp-main-theme' ), 'priority' => 15 ); }
	if ( wpmain_visible_recommendations( $data['comparison'] ) ) { $custom['wpmain_comparison'] = array( 'title' => __( 'Compare specifications', 'wp-main-theme' ), 'priority' => 25 ); }
	if ( $data['faq'] ) { $custom['wpmain_faq'] = array( 'title' => __( 'Product FAQs', 'wp-main-theme' ), 'priority' => 35 ); }
	foreach ( $custom as &$tab ) { $tab['callback'] = 'wpmain_product_detail_tab'; }
	unset( $tab );
	return array_merge( $tabs, $custom );
} );

function wpmain_product_resources() {
	global $product;
	if ( ! $product || ! function_exists( 'wpmain_catalog_guidance' ) || post_password_required() ) { return; }
	$data = wpmain_catalog_guidance( $product->get_id() );
	get_template_part( 'template-parts/product/guides', null, array( 'guides' => $data['guides'] ) );
	foreach ( array( 'compatible' => __( 'Works with this product', 'wp-main-theme' ), 'alternatives' => __( 'Other options for the same use', 'wp-main-theme' ) ) as $key => $heading ) {
		$products = wpmain_visible_recommendations( $data[ $key ] );
		if ( ! $products ) { continue; }
		get_template_part( 'template-parts/product/recommendations', null, array( 'kind' => $key, 'heading' => $heading, 'products' => $products ) );
	}
}
add_action( 'woocommerce_after_single_product_summary', 'wpmain_product_resources', 12 );

// Curated alternatives replace generic related items only when visible choices exist.
add_filter( 'woocommerce_related_products', function ( $ids, $id ) {
	if ( ! function_exists( 'wpmain_catalog_guidance' ) ) { return $ids; }
	$data = wpmain_catalog_guidance( $id );
	return wpmain_visible_recommendations( $data['alternatives'] ) ? array() : $ids;
}, 10, 2 );
