<?php
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_normalize_search( $value ) {
	if ( ! is_scalar( $value ) ) { return ''; }
	$value = html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' );
	$value = strtr( $value, array( 'ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ۀ' => 'ه', 'ة' => 'ه', '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9' ) );
	return strtolower( preg_replace( '/[^\p{L}\p{N}]+/u', '', $value ) ?? '' );
}

function wpmain_catalog_search_terms( $query ) {
	if ( ! is_string( $query ) ) { return array(); }
	$parts = preg_split( '/\s+/u', trim( $query ) ) ?: array();
	return array_slice( array_values( array_unique( array_filter( array_map( 'wpmain_catalog_normalize_search', $parts ), 'strlen' ) ) ), 0, 8 );
}

/** Normalize once on update, rather than nested REPLACE calls on every search. */
function wpmain_catalog_index_product( $id ) {
	if ( ! function_exists( 'wc_get_product' ) || 'product' !== get_post_type( $id ) ) { return; }
	$post = get_post( $id );
	if ( ! $post ) { return; }
	$brands = wp_get_post_terms( $id, 'product_brand' );
	$brand_text = '';
	if ( ! is_wp_error( $brands ) ) {
		foreach ( $brands as $brand ) { $brand_text .= ' ' . $brand->name . ' ' . get_term_meta( $brand->term_id, '_wpmain_search_aliases', true ); }
	}
	$title = wpmain_catalog_normalize_search( $post->post_title );
	$sku = wpmain_catalog_normalize_search( get_post_meta( $id, '_sku', true ) );
	$variation_skus = array();
	$product = wc_get_product( $id );
	if ( $product && $product->is_type( 'variable' ) ) {
		foreach ( $product->get_visible_children() as $variation_id ) { $variation_skus[] = wpmain_catalog_normalize_search( get_post_meta( $variation_id, '_sku', true ) ); }
	}
	$aliases = get_post_meta( $id, '_wpmain_search_aliases', true );
	$aliases = is_string( $aliases ) ? $aliases : '';
	// Newlines between fields prevent accidental phrase matches spanning fields.
	$document = implode( "\n", array_map( 'wpmain_catalog_normalize_search', array( $post->post_title, $post->post_excerpt, $post->post_content, $sku, $aliases, $brand_text ) ) ) . "\n" . implode( "\n", $variation_skus );
	update_post_meta( $id, '_wpmain_search_document', $document );
	update_post_meta( $id, '_wpmain_search_title', $title );
	update_post_meta( $id, '_wpmain_search_sku', $sku );
}
add_action( 'woocommerce_new_product', 'wpmain_catalog_index_product', 30 );
add_action( 'woocommerce_update_product', 'wpmain_catalog_index_product', 30 );
function wpmain_catalog_index_variation_parent( $id ) { $parent = wp_get_post_parent_id( $id ); if ( $parent ) { wpmain_catalog_index_product( $parent ); } }
add_action( 'woocommerce_new_product_variation', 'wpmain_catalog_index_variation_parent', 30 );
add_action( 'woocommerce_update_product_variation', 'wpmain_catalog_index_variation_parent', 30 );
add_action( 'set_object_terms', function ( $id, $terms, $tt_ids, $taxonomy ) {
	if ( in_array( $taxonomy, array( 'product_brand', 'product_cat' ), true ) ) { wpmain_catalog_index_product( $id ); }
}, 30, 4 );

/** Bounded background rebuild, also useful after imports or brand edits. */
function wpmain_catalog_reindex_batch() {
	$cursor = absint( get_option( 'wpmain_search_cursor', 0 ) );
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND ID > %d ORDER BY ID ASC LIMIT 50", $cursor ) );
	foreach ( $ids as $id ) { wpmain_catalog_index_product( (int) $id ); }
	if ( count( $ids ) === 50 ) {
		update_option( 'wpmain_search_cursor', (int) end( $ids ), false );
		if ( ! wp_next_scheduled( 'wpmain_search_reindex' ) ) { wp_schedule_single_event( time() + 5, 'wpmain_search_reindex' ); }
	} else {
		delete_option( 'wpmain_search_cursor' );
		update_option( 'wpmain_search_index_version', '1', false );
	}
}
add_action( 'wpmain_search_reindex', 'wpmain_catalog_reindex_batch' );
add_action( 'init', function () {
	if ( '1' !== get_option( 'wpmain_search_index_version' ) && ! wp_next_scheduled( 'wpmain_search_reindex' ) ) { wp_schedule_single_event( time(), 'wpmain_search_reindex' ); }
} );
function wpmain_catalog_queue_reindex() {
	delete_option( 'wpmain_search_index_version' );
	delete_option( 'wpmain_search_cursor' );
	if ( ! wp_next_scheduled( 'wpmain_search_reindex' ) ) { wp_schedule_single_event( time(), 'wpmain_search_reindex' ); }
}
add_action( 'edited_product_brand', 'wpmain_catalog_queue_reindex', 30 );
add_action( 'admin_init', function () {
	if ( current_user_can( 'manage_woocommerce' ) && '1' !== get_option( 'wpmain_search_index_version' ) ) { wpmain_catalog_reindex_batch(); }
} );
add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', __( 'Search index', 'wp-store-catalog' ), __( 'Search index', 'wp-store-catalog' ), 'manage_woocommerce', 'wpmain-search-index', function () {
		echo '<div class="wrap"><h1>' . esc_html__( 'Product search index', 'wp-store-catalog' ) . '</h1><p>' . esc_html( '1' === get_option( 'wpmain_search_index_version' ) ? __( 'Search index is ready. Products update automatically when saved through WooCommerce.', 'wp-store-catalog' ) : __( 'Search indexing is in progress. Reload this admin page to process another batch, or let WordPress cron finish it.', 'wp-store-catalog' ) ) . '</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="wpmain_reindex">'; wp_nonce_field( 'wpmain_reindex' ); submit_button( __( 'Rebuild search index', 'wp-store-catalog' ) ); echo '</form></div>';
	} );
} );
add_action( 'admin_post_wpmain_reindex', function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( esc_html__( 'Permission denied.', 'wp-store-catalog' ), '', array( 'response' => 403 ) ); }
	check_admin_referer( 'wpmain_reindex' );
	wpmain_catalog_queue_reindex(); wpmain_catalog_reindex_batch();
	wp_safe_redirect( admin_url( 'admin.php?page=wpmain-search-index' ) ); exit;
} );

function wpmain_catalog_is_search_query( $query ) {
	return 'product' === $query->get( 'post_type' ) && ( $query->get( 'wpmain_suggestions' ) || ( ! is_admin() && $query->is_main_query() && $query->is_search() ) );
}
function wpmain_catalog_product_search( $search, $query ) {
	if ( ! wpmain_catalog_is_search_query( $query ) ) { return $search; }
	$terms = wpmain_catalog_search_terms( $query->get( 's' ) );
	if ( ! $terms ) { return ' AND 1=0 '; }
	global $wpdb;
	$matches = array();
	foreach ( $terms as $term ) { $matches[] = $wpdb->prepare( 'wpmain_doc.meta_value LIKE %s', '%' . $wpdb->esc_like( $term ) . '%' ); }
	return " AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} wpmain_doc WHERE wpmain_doc.post_id = {$wpdb->posts}.ID AND wpmain_doc.meta_key = '_wpmain_search_document' AND " . implode( ' AND ', $matches ) . ") AND {$wpdb->posts}.post_password = '' ";
}
add_filter( 'posts_search', 'wpmain_catalog_product_search', 20, 2 );
add_filter( 'posts_orderby', function ( $orderby, $query ) {
	if ( ! wpmain_catalog_is_search_query( $query ) || ! in_array( $query->get( 'orderby' ), array( '', 'relevance' ), true ) ) { return $orderby; }
	global $wpdb;
	$value = wpmain_catalog_normalize_search( $query->get( 's' ) );
	if ( '' === $value ) { return $orderby; }
	$exact_sku = $wpdb->prepare( "EXISTS (SELECT 1 FROM {$wpdb->postmeta} rank_sku WHERE rank_sku.post_id = {$wpdb->posts}.ID AND rank_sku.meta_key = '_wpmain_search_sku' AND rank_sku.meta_value = %s)", $value );
	$title = $wpdb->prepare( "EXISTS (SELECT 1 FROM {$wpdb->postmeta} rank_title WHERE rank_title.post_id = {$wpdb->posts}.ID AND rank_title.meta_key = '_wpmain_search_title' AND rank_title.meta_value LIKE %s)", '%' . $wpdb->esc_like( $value ) . '%' );
	return "CASE WHEN {$exact_sku} THEN 0 WHEN {$title} THEN 1 ELSE 2 END ASC, {$wpdb->posts}.post_date DESC";
}, 30, 2 );

/** Native term aliases are durable brand data, shared by every assigned product. */
add_action( 'product_brand_edit_form_fields', function ( $term ) {
	echo '<tr class="form-field"><th><label for="wpmain-brand-aliases">' . esc_html__( 'Search aliases', 'wp-store-catalog' ) . '</label></th><td><textarea id="wpmain-brand-aliases" name="wpmain_brand_aliases">' . esc_textarea( get_term_meta( $term->term_id, '_wpmain_search_aliases', true ) ) . '</textarea>';
	wp_nonce_field( 'wpmain_brand_aliases', 'wpmain_brand_nonce' );
	echo '<p>' . esc_html__( 'Confirmed Persian/English brand names, one per line.', 'wp-store-catalog' ) . '</p></td></tr>';
} );
add_action( 'edited_product_brand', function ( $id ) {
	$nonce = isset( $_POST['wpmain_brand_nonce'] ) && is_string( $_POST['wpmain_brand_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpmain_brand_nonce'] ) ) : '';
	$taxonomy = get_taxonomy( 'product_brand' );
	if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->manage_terms ) || ! wp_verify_nonce( $nonce, 'wpmain_brand_aliases' ) ) { return; }
	$value = isset( $_POST['wpmain_brand_aliases'] ) && is_string( $_POST['wpmain_brand_aliases'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpmain_brand_aliases'] ) ) : '';
	update_term_meta( $id, '_wpmain_search_aliases', $value );
}, 20 );

add_action( 'rest_api_init', function () {
	register_rest_route( 'wpmain/v1', '/suggestions', array( 'methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => 'wpmain_catalog_suggestions', 'args' => array( 'q' => array( 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field', 'validate_callback' => function ( $value ) { return is_string( $value ) && strlen( $value ) <= 240; } ) ) ) );
} );
function wpmain_catalog_suggestions( $request ) {
	if ( ! function_exists( 'wc_get_product' ) ) { return new WP_Error( 'catalog_unavailable', __( 'Catalog unavailable.', 'wp-store-catalog' ), array( 'status' => 503 ) ); }
	$text = $request->get_param( 'q' );
	$normalized = wpmain_catalog_normalize_search( $text );
	if ( strlen( $normalized ) < 2 ) { return rest_ensure_response( array( 'products' => array(), 'categories' => array(), 'brands' => array() ) ); }
	$query = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 'has_password' => false, 's' => $text, 'wpmain_suggestions' => true, 'orderby' => 'relevance', 'posts_per_page' => 6, 'no_found_rows' => true, 'tax_query' => array( array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => array( 'exclude-from-search', 'exclude-from-catalog' ), 'operator' => 'NOT IN' ) ) ) );
	$result = array( 'products' => array(), 'categories' => array(), 'brands' => array() );
	foreach ( $query->posts as $post ) {
		$product = wc_get_product( $post->ID );
		if ( ! $product || ! $product->is_visible() ) { continue; }
		$result['products'][] = array( 'label' => get_the_title( $post ), 'url' => get_permalink( $post ) );
	}
	foreach ( array( 'product_cat' => 'categories', 'product_brand' => 'brands' ) as $taxonomy => $key ) {
		if ( ! taxonomy_exists( $taxonomy ) ) { continue; }
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true, 'number' => 100 ) );
		if ( is_wp_error( $terms ) ) { continue; }
		foreach ( $terms as $term ) {
			$aliases = 'brands' === $key ? get_term_meta( $term->term_id, '_wpmain_search_aliases', true ) : '';
			if ( false === strpos( wpmain_catalog_normalize_search( $term->name . ' ' . $aliases ), $normalized ) ) { continue; }
			$url = get_term_link( $term );
			if ( ! is_wp_error( $url ) ) { $result[ $key ][] = array( 'label' => $term->name, 'url' => $url ); }
			if ( count( $result[ $key ] ) >= 3 ) { break; }
		}
	}
	return rest_ensure_response( $result );
}
