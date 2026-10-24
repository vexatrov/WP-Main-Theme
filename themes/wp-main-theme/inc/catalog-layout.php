<?php
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_widgets() {
	register_sidebar( array( 'name' => __( 'Shop filters', 'wp-main-theme' ), 'id' => 'shop-filters', 'description' => __( 'Add WooCommerce price, attribute and active-filter widgets here.', 'wp-main-theme' ), 'before_widget' => '<section id="%1$s" class="catalog-widget %2$s">', 'after_widget' => '</section>', 'before_title' => '<h3>', 'after_title' => '</h3>' ) );
}
add_action( 'widgets_init', 'wpmain_catalog_widgets' );

function wpmain_catalog_navigation() {
	if ( ! is_shop() && ! is_product_taxonomy() && ! is_search() ) { return; }
	$parent = is_product_category() ? get_queried_object_id() : 0;
	$categories = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent, 'hide_empty' => true ) );
	// Leaf categories offer sibling routes instead of a dead end.
	if ( $parent && ! is_wp_error( $categories ) && ! $categories ) {
		$current = get_queried_object();
		$categories = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => (int) $current->parent, 'hide_empty' => true ) );
	}
	if ( ! is_wp_error( $categories ) && $categories ) {
		echo '<nav class="catalog-categories" aria-label="' . esc_attr__( 'Product categories', 'wp-main-theme' ) . '">';
		foreach ( $categories as $category ) {
			$url = get_term_link( $category );
			if ( is_wp_error( $url ) ) { continue; }
			echo '<a href="' . esc_url( $url ) . '"' . ( $parent === $category->term_id ? ' aria-current="page"' : '' ) . '>';
			$image = absint( get_term_meta( $category->term_id, 'thumbnail_id', true ) );
			if ( $image ) { echo wp_get_attachment_image( $image, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); }
			echo '<span>' . esc_html( $category->name ) . '</span></a>';
		}
		echo '</nav>';
	}
	$filters = wpmain_catalog_filter_keys();
	$brands = taxonomy_exists( 'product_brand' ) && ! is_tax( 'product_brand' ) ? get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => true ) ) : array();
	if ( is_wp_error( $brands ) ) { $brands = array(); }
	if ( $filters ) {
		echo '<div class="catalog-filter-status"><span>' . esc_html__( 'Filters are applied', 'wp-main-theme' ) . '</span><a href="' . esc_url( wpmain_catalog_reset_url() ) . '">' . esc_html__( 'Clear filters', 'wp-main-theme' ) . '</a></div>';
	}
	if ( is_active_sidebar( 'shop-filters' ) || $brands ) {
		echo '<details class="catalog-filters"' . ( $filters ? ' open' : '' ) . '><summary>' . esc_html__( 'Filter products', 'wp-main-theme' ) . '</summary><div class="catalog-filter-grid">';
		if ( $brands ) { wpmain_catalog_brand_filter( $brands ); }
		dynamic_sidebar( 'shop-filters' );
		echo '</div></details>';
	}
}
add_action( 'woocommerce_before_shop_loop', 'wpmain_catalog_navigation', 5 );
add_action( 'woocommerce_no_products_found', 'wpmain_catalog_navigation', 5 );

/** Read-only query keys; values are never output without sanitation/escaping. */
function wpmain_catalog_filter_keys() {
	return array_values( array_filter( array_keys( $_GET ), function ( $key ) {
		if ( '' === $_GET[ $key ] ) { return false; }
		return in_array( $key, array( 'min_price', 'max_price', 'rating_filter' ), true ) || 0 === strpos( (string) $key, 'filter_' ) || 0 === strpos( (string) $key, 'query_type_' );
	} ) );
}

function wpmain_catalog_reset_url() {
	return remove_query_arg( array_merge( wpmain_catalog_filter_keys(), array( 'paged', 'product-page' ) ), get_pagenum_link( 1, false ) );
}

function wpmain_catalog_brand_filter( $brands ) {
	$selected = isset( $_GET['filter_product_brand'] ) && is_scalar( $_GET['filter_product_brand'] ) ? absint( $_GET['filter_product_brand'] ) : 0;
	echo '<form method="get" class="catalog-brand-filter" action="' . esc_url( get_pagenum_link( 1, false ) ) . '"><label for="catalog-brand">' . esc_html__( 'Brand', 'wp-main-theme' ) . '</label><select id="catalog-brand" name="filter_product_brand"><option value="">' . esc_html__( 'All brands', 'wp-main-theme' ) . '</option>';
	foreach ( $brands as $brand ) {
		echo '<option value="' . esc_attr( $brand->term_id ) . '"' . selected( $selected, $brand->term_id, false ) . '>' . esc_html( $brand->name ) . '</option>';
	}
	echo '</select>';
	// Keep the search, category context, price, attributes and sorting on submit.
	wc_query_string_form_fields( null, array( 'filter_product_brand', 'paged', 'product-page', 'submit' ) );
	echo '<button type="submit">' . esc_html__( 'Apply brand', 'wp-main-theme' ) . '</button></form>';
}

function wpmain_catalog_empty_recovery() {
	echo '<section class="catalog-empty"><h2>' . esc_html__( 'Let’s find another option', 'wp-main-theme' ) . '</h2><p>' . esc_html__( 'Try a broader search, remove a filter, or explore the full catalog.', 'wp-main-theme' ) . '</p>';
	get_template_part( 'template-parts/header/product-search' );
	echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'View all products', 'wp-main-theme' ) . '</a></section>';
}
add_action( 'woocommerce_no_products_found', 'wpmain_catalog_empty_recovery', 20 );
