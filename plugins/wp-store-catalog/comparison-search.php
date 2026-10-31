<?php
/** Public read-only comparison discovery shares the stored Persian search index. */
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_comparison_matches( $text, $exclude = 0 ) {
	if ( ! is_string( $text ) || strlen( $text ) > 240 || mb_strlen( trim( $text ) ) < 2 || ! function_exists( 'wc_get_product' ) ) { return array(); }
	$query = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'has_password' => false, 's' => $text, 'wpmain_suggestions' => true, 'orderby' => 'relevance', 'posts_per_page' => 6, 'fields' => 'ids', 'no_found_rows' => true, 'post__not_in' => array( absint( $exclude ) ), 'tax_query' => array( array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => array( 'exclude-from-search', 'exclude-from-catalog' ), 'operator' => 'NOT IN' ) ) ) );
	$items = array();
	foreach ( $query->posts as $id ) {
		$product = wc_get_product( $id );
		if ( $product && $product->get_id() !== absint( $exclude ) && 'publish' === $product->get_status() && in_array( $product->get_catalog_visibility(), array( 'visible', 'catalog' ), true ) && $product->is_visible() && ! get_post_field( 'post_password', $id ) ) { $items[] = $product; }
	}
	return array_slice( $items, 0, 6 );
}

add_action( 'rest_api_init', static function () {
	register_rest_route( 'wpmain/v1', '/comparison-search', array( 'methods' => 'GET', 'permission_callback' => '__return_true', 'args' => array(
		'q' => array( 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field', 'validate_callback' => static function ( $value ) { return is_string( $value ) && strlen( $value ) <= 240; } ),
		'exclude' => array( 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ),
	), 'callback' => static function ( $request ) {
		$items = array();
		foreach ( wpmain_catalog_comparison_matches( $request->get_param( 'q' ), $request->get_param( 'exclude' ) ) as $product ) {
			$image = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
			$items[] = array( 'id' => $product->get_id(), 'label' => wp_strip_all_tags( $product->get_name() ), 'image' => $image ?: wc_placeholder_img_src( 'woocommerce_thumbnail' ) );
		}
		return rest_ensure_response( array( 'products' => $items ) );
	} ) );
} );
