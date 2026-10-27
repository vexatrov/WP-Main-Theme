<?php
defined( 'ABSPATH' ) || exit;

function wpmain_visible_recommendations( $ids ) {
	$products = array();
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( $product && 'publish' === $product->get_status() && in_array( $product->get_catalog_visibility(), array( 'visible', 'catalog' ), true ) && $product->is_visible() && ! get_post_field( 'post_password', $id ) ) { $products[] = $product; }
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
	if ( isset( $tabs['additional_information'] ) ) { $tabs['additional_information']['title'] = __( 'Specifications', 'wp-main-theme' ); }
	if ( ! $product || ! function_exists( 'wpmain_catalog_guidance' ) || post_password_required() ) { return $tabs; }
	$data = wpmain_catalog_guidance( $product->get_id() );
	$custom = array();
	if ( $data['use_cases'] || $data['audience'] ) { $custom['wpmain_usage'] = array( 'title' => __( 'Use cases & suitability', 'wp-main-theme' ), 'priority' => 15 ); }
	if ( wpmain_product_comparison_candidates( $product, $data ) ) { $custom['wpmain_comparison'] = array( 'title' => __( 'Compare specifications', 'wp-main-theme' ), 'priority' => 25 ); }
	if ( $data['faq'] ) { $custom['wpmain_faq'] = array( 'title' => __( 'Product FAQs', 'wp-main-theme' ), 'priority' => 35 ); }
	foreach ( $custom as &$tab ) { $tab['callback'] = 'wpmain_product_detail_tab'; }
	unset( $tab );
	return array_merge( $tabs, $custom );
} );

function wpmain_product_resources() {
	global $product;
	if ( ! $product || ! function_exists( 'wpmain_catalog_guidance' ) || post_password_required() ) { return; }
	$data = wpmain_catalog_guidance( $product->get_id() );
	$category = wpmain_product_deepest_category( $product->get_id() );
	$category_url = $category ? get_term_link( $category ) : '';
	$groups = array();
	foreach ( array( 'compatible' => __( 'Works with this product', 'wp-main-theme' ), 'alternatives' => __( 'Other options for the same use', 'wp-main-theme' ) ) as $key => $heading ) {
		$products = wpmain_visible_recommendations( $data[ $key ] );
		if ( $products ) { $groups[] = array( 'kind' => $key, 'heading' => $heading, 'products' => $products, 'url' => 'alternatives' === $key && ! is_wp_error( $category_url ) ? $category_url : '' ); }
	}
	$brands = taxonomy_exists( 'product_brand' ) ? get_the_terms( $product->get_id(), 'product_brand' ) : array();
	if ( $brands && ! is_wp_error( $brands ) ) {
		$brand = reset( $brands );
		$brand_url = get_term_link( $brand );
		$products = wpmain_product_term_choices( $product, 'product_brand', $brand->term_id, 4 );
		if ( $products ) { $groups[] = array( 'kind' => 'same-brand', 'heading' => sprintf( __( 'More from %s', 'wp-main-theme' ), $brand->name ), 'products' => $products, 'url' => is_wp_error( $brand_url ) ? '' : $brand_url ); }
	}
	if ( $groups ) {
		echo '<div class="product-recommendation-groups">';
		foreach ( $groups as $group ) { get_template_part( 'template-parts/product/recommendations', null, $group ); }
		echo '</div>';
	}
	get_template_part( 'template-parts/product/guides', null, array( 'guides' => $data['guides'] ) );
}
add_action( 'woocommerce_after_single_product_summary', 'wpmain_product_resources', 8 );

// Curated alternatives replace generic related items only when visible choices exist.
add_filter( 'woocommerce_related_products', function ( $ids, $id ) {
	if ( ! function_exists( 'wpmain_catalog_guidance' ) ) { return $ids; }
	$data = wpmain_catalog_guidance( $id );
	return wpmain_visible_recommendations( $data['alternatives'] ) ? array() : $ids;
}, 10, 2 );

/** Most-specific assigned category, stable when multiple branches have equal depth. */
function wpmain_product_deepest_category( $id ) {
	$terms = get_the_terms( $id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) { return null; }
	usort( $terms, function ( $a, $b ) {
		$depth = count( get_ancestors( $b->term_id, 'product_cat', 'taxonomy' ) ) - count( get_ancestors( $a->term_id, 'product_cat', 'taxonomy' ) );
		return $depth ?: $a->term_id <=> $b->term_id;
	} );
	return reset( $terms );
}

/** Bounded native taxonomy lookup; visibility is checked again before rendering. */
function wpmain_product_term_choices( $product, $taxonomy, $term_id, $limit = 12 ) {
	$visibility = wc_get_product_visibility_term_ids();
	$excluded = array( $visibility['exclude-from-catalog'] ?? 0 );
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) { $excluded[] = $visibility['outofstock'] ?? 0; }
	$tax_query = array( array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => array( $term_id ) ) );
	if ( array_filter( $excluded ) ) { $tax_query[] = array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => array_filter( $excluded ), 'operator' => 'NOT IN' ); }
	$query = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => 12, 'fields' => 'ids', 'no_found_rows' => true, 'post__not_in' => array( $product->get_id() ), 'orderby' => 'date', 'order' => 'DESC', 'tax_query' => $tax_query ) );
	return array_slice( wpmain_visible_recommendations( $query->posts ), 0, $limit );
}

function wpmain_product_comparison_candidates( $product, $data ) {
	$items = wpmain_visible_recommendations( array_merge( $data['comparison'], $data['alternatives'] ) );
	$category = wpmain_product_deepest_category( $product->get_id() );
	if ( $category ) { $items = array_merge( $items, wpmain_product_term_choices( $product, 'product_cat', $category->term_id ) ); }
	$choices = array();
	foreach ( $items as $item ) {
		if ( $item->get_id() !== $product->get_id() ) { $choices[ $item->get_id() ] = $item; }
		if ( count( $choices ) >= 12 ) { break; }
	}
	return $choices;
}

/** Only public bounded choices are accepted, including for forged GET requests. */
function wpmain_product_comparison_selection( $raw, $choices ) {
	$selected = array();
	foreach ( is_array( $raw ) ? array_slice( $raw, 0, 30 ) : array() as $value ) {
		if ( ! is_scalar( $value ) || ! ctype_digit( (string) $value ) ) { continue; }
		$id = (int) $value;
		if ( isset( $choices[ $id ] ) ) { $selected[ $id ] = $choices[ $id ]; }
		if ( count( $selected ) >= 3 ) { break; }
	}
	return array_values( $selected );
}

function wpmain_product_reassurance_messages( $raw ) {
	if ( ! is_string( $raw ) ) { return ''; }
	$lines = array_filter( array_map( 'sanitize_text_field', preg_split( '/\R/u', $raw ) ?: array() ), 'strlen' );
	return implode( "\n", array_slice( $lines, 0, 4 ) );
}

function wpmain_product_reassurance() {
	if ( post_password_required() ) { return; }
	$copy = wpmain_product_reassurance_messages( get_theme_mod( 'wpmain_product_reassurance', '' ) );
	if ( ! $copy ) { return; }
	echo '<ul class="product-reassurance" aria-label="' . esc_attr__( 'Store information', 'wp-main-theme' ) . '">';
	foreach ( explode( "\n", $copy ) as $line ) { echo '<li>' . esc_html( $line ) . '</li>'; }
	echo '</ul>';
}
add_action( 'woocommerce_single_product_summary', 'wpmain_product_reassurance', 35 );
add_action( 'wp_enqueue_scripts', function () {
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'wpmain-product-details', get_template_directory_uri() . '/assets/css/product-details.css', array( 'wpmain-purchase' ), wpmain_asset_version( '/assets/css/product-details.css' ) );
	}
} );
