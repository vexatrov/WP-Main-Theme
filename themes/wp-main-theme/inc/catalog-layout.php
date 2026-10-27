<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/catalog-price-widget.php';

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
			$brand = is_tax( 'product_brand' ) ? get_queried_object_id() : wpmain_catalog_query_value( 'filter_product_brand' );
			if ( $brand ) { $url = add_query_arg( 'filter_product_brand', $brand, $url ); }
			echo '<a href="' . esc_url( $url ) . '"' . ( $parent === $category->term_id ? ' aria-current="page"' : '' ) . '>';
			$image = absint( get_term_meta( $category->term_id, 'thumbnail_id', true ) );
			if ( $image ) { echo wp_get_attachment_image( $image, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); }
			echo '<span>' . esc_html( $category->name ) . '</span></a>';
		}
		echo '</nav>';
	}
	$filters = wpmain_catalog_filter_keys();
	$brands = wpmain_catalog_available_brands();
	if ( is_wp_error( $brands ) ) { $brands = array(); }
	$attributes = is_product_category() && function_exists( 'wpmain_catalog_category_attributes' ) ? wpmain_catalog_category_attributes( get_queried_object_id() ) : array();
	if ( $filters ) {
		wpmain_catalog_filter_chips();
	}
	if ( is_active_sidebar( 'shop-filters' ) || $brands || $attributes ) {
		echo '<details class="catalog-filters"' . ( $filters ? ' open' : '' ) . '><summary>' . esc_html__( 'Filter products', 'wp-main-theme' ) . '</summary><div class="catalog-filter-grid">';
		if ( $brands ) { wpmain_catalog_brand_filter( $brands ); }
		foreach ( $attributes as $attribute ) {
			if ( class_exists( 'WC_Widget_Layered_Nav' ) ) { the_widget( 'WC_Widget_Layered_Nav', array( 'title' => wc_attribute_label( 'pa_' . $attribute ), 'attribute' => $attribute, 'display_type' => 'list', 'query_type' => 'or' ), array( 'before_widget' => '<section class="catalog-widget widget_layered_nav">', 'after_widget' => '</section>', 'before_title' => '<h3>', 'after_title' => '</h3>' ) ); }
		}
		dynamic_sidebar( 'shop-filters' );
		echo '</div></details>';
	}
}
add_action( 'woocommerce_before_shop_loop', 'wpmain_catalog_navigation', 5 );
add_action( 'woocommerce_before_shop_loop', function () { echo '<div class="catalog-toolbar">'; }, 15 );
add_action( 'woocommerce_before_shop_loop', function () { echo '</div>'; }, 35 );
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

/** Scalar query values only; WooCommerce uses comma-separated facet values. */
function wpmain_catalog_query_value( $key ) {
	return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) ) : '';
}

/** Use WP's descendant-aware taxonomy query, never a separate SQL catalog scan. */
function wpmain_catalog_available_brands() {
	if ( ! taxonomy_exists( 'product_brand' ) || is_tax( 'product_brand' ) ) { return array(); }
	$args = array( 'taxonomy' => 'product_brand', 'hide_empty' => true );
	if ( is_product_category() ) {
		$tax_query = array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => get_queried_object_id(), 'include_children' => true ) );
		if ( function_exists( 'wc_get_product_visibility_term_ids' ) ) {
			$visibility = wc_get_product_visibility_term_ids();
			$tax_query[] = array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => array( $visibility['exclude-from-catalog'] ), 'operator' => 'NOT IN' );
		}
		// Bound memory; large categories retain catalog-wide choices and recovery guidance.
		$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'has_password' => false, 'fields' => 'ids', 'posts_per_page' => 251, 'no_found_rows' => true, 'tax_query' => $tax_query ) );
		if ( ! $ids ) { return array(); }
		if ( count( $ids ) <= 250 ) { $args['object_ids'] = $ids; }
	}
	return get_terms( $args );
}

/** Remove one selection, retaining the route, search, ordering and other facets. */
function wpmain_catalog_remove_filter_url( $key, $value = null ) {
	$url = remove_query_arg( array( 'paged', 'product-page' ), get_pagenum_link( 1, false ) );
	$remaining = null === $value ? array() : array_values( array_diff( explode( ',', wpmain_catalog_query_value( $key ) ), array( (string) $value ) ) );
	if ( $remaining ) { return add_query_arg( $key, implode( ',', $remaining ), $url ); }
	$keys = array( $key );
	if ( 0 === strpos( $key, 'filter_' ) ) { $keys[] = 'query_type_' . substr( $key, 7 ); }
	return remove_query_arg( $keys, $url );
}

function wpmain_catalog_filter_chips() {
	$chips = array();
	foreach ( wpmain_catalog_filter_keys() as $key ) {
		$value = wpmain_catalog_query_value( $key );
		if ( '' === $value || 0 === strpos( $key, 'query_type_' ) ) { continue; }
		if ( in_array( $key, array( 'min_price', 'max_price' ), true ) ) {
			if ( ! is_numeric( $value ) ) { continue; }
			$label = sprintf( 'min_price' === $key ? __( 'Minimum price: %s', 'wp-main-theme' ) : __( 'Maximum price: %s', 'wp-main-theme' ), wp_strip_all_tags( wc_price( (float) $value ) ) );
			$chips[] = array( $label, wpmain_catalog_remove_filter_url( $key ) );
			continue;
		}
		foreach ( array_filter( explode( ',', $value ) ) as $selection ) {
			if ( 'rating_filter' === $key ) {
				if ( ! in_array( $selection, array( '1', '2', '3', '4', '5' ), true ) ) { continue; }
				$label = sprintf( __( 'Rating: %s stars', 'wp-main-theme' ), $selection );
			} else {
				$is_brand = 'filter_product_brand' === $key;
				$taxonomy = $is_brand ? 'product_brand' : 'pa_' . substr( $key, 7 );
				if ( ! taxonomy_exists( $taxonomy ) ) { continue; }
				$term = get_term_by( $is_brand ? 'id' : 'slug', $is_brand ? absint( $selection ) : $selection, $taxonomy );
				if ( ! $term || is_wp_error( $term ) ) { continue; }
				$label = ( $is_brand ? __( 'Brand', 'wp-main-theme' ) : wc_attribute_label( $taxonomy ) ) . ': ' . $term->name;
			}
			$chips[] = array( $label, wpmain_catalog_remove_filter_url( $key, $selection ) );
		}
	}
	if ( ! $chips ) { return; }
	echo '<nav class="catalog-filter-status" aria-label="' . esc_attr__( 'Applied filters', 'wp-main-theme' ) . '"><ul class="catalog-filter-chips">';
	foreach ( $chips as $chip ) {
		echo '<li><a href="' . esc_url( $chip[1] ) . '" aria-label="' . esc_attr( sprintf( __( 'Remove %s', 'wp-main-theme' ), $chip[0] ) ) . '">' . esc_html( $chip[0] ) . ' <span aria-hidden="true">×</span></a></li>';
	}
	echo '</ul><a href="' . esc_url( wpmain_catalog_reset_url() ) . '">' . esc_html__( 'Clear filters', 'wp-main-theme' ) . '</a></nav>';
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
	echo '<button type="submit">' . esc_html__( 'Apply brand', 'wp-main-theme' ) . '</button>';
	if ( ! wc_get_loop_prop( 'total' ) ) {
		echo '<p class="catalog-brand-note">' . esc_html__( 'Some brand and filter combinations may have no matching products. Remove a selection to broaden results.', 'wp-main-theme' ) . '</p>';
	}
	echo '</form>';
}

function wpmain_catalog_empty_recovery() {
	echo '<section class="catalog-empty"><h2>' . esc_html__( 'Let’s find another option', 'wp-main-theme' ) . '</h2><p>' . esc_html__( 'Try a broader search, remove a filter, or explore the full catalog.', 'wp-main-theme' ) . '</p>';
	get_template_part( 'template-parts/header/product-search' );
	echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'View all products', 'wp-main-theme' ) . '</a></section>';
}
add_action( 'woocommerce_no_products_found', 'wpmain_catalog_empty_recovery', 20 );

function wpmain_catalog_buying_guidance() {
	if ( ! is_product_category() ) { return; }
	$id = get_queried_object_id();
	$copy = get_term_meta( $id, '_wpmain_buying_guide', true );
	$guides = get_term_meta( $id, '_wpmain_category_guides', true );
	if ( ! is_string( $copy ) ) { $copy = ''; }
	$articles = is_array( $guides ) && $guides ? get_posts( array( 'post_type' => 'post', 'post__in' => array_map( 'absint', $guides ), 'orderby' => 'post__in', 'posts_per_page' => 4, 'has_password' => false ) ) : array();
	if ( ! $copy && ! $articles ) { return; }
	echo '<section class="category-buying-guide"><h2>' . esc_html__( 'Choosing the right product', 'wp-main-theme' ) . '</h2>';
	if ( $copy ) { echo '<div class="entry-content">' . wp_kses_post( wpautop( esc_html( $copy ) ) ) . '</div>'; }
	if ( $articles ) {
		echo '<ul class="product-guide-links">';
		foreach ( $articles as $article ) { echo '<li><a href="' . esc_url( get_permalink( $article ) ) . '">' . esc_html( get_the_title( $article ) ) . '</a></li>'; }
		echo '</ul>';
	}
	echo '</section>';
}
add_action( 'woocommerce_after_shop_loop', 'wpmain_catalog_buying_guidance', 30 );
add_action( 'woocommerce_no_products_found', 'wpmain_catalog_buying_guidance', 30 );
