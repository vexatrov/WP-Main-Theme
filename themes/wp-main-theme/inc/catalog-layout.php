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
	if ( ! is_wp_error( $categories ) && $categories ) {
		echo '<nav class="catalog-categories" aria-label="' . esc_attr__( 'Product categories', 'wp-main-theme' ) . '">';
		foreach ( $categories as $category ) {
			$url = get_term_link( $category );
			if ( ! is_wp_error( $url ) ) { echo '<a href="' . esc_url( $url ) . '">' . esc_html( $category->name ) . '</a>'; }
		}
		echo '</nav>';
	}
	if ( is_active_sidebar( 'shop-filters' ) ) {
		echo '<details class="catalog-filters"><summary>' . esc_html__( 'Filter products', 'wp-main-theme' ) . '</summary><div class="catalog-filter-grid">';
		dynamic_sidebar( 'shop-filters' );
		echo '</div></details>';
	}
}
add_action( 'woocommerce_before_shop_loop', 'wpmain_catalog_navigation', 5 );
add_action( 'woocommerce_no_products_found', 'wpmain_catalog_navigation', 5 );
