<?php
/** Native admin-only media and ordered relationship controls; no frontend dependency. */
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_editor_item( $id, $kind ) {
	if ( 'product_cat' === $kind ) {
		$term = get_term( $id, $kind );
		if ( ! $term || is_wp_error( $term ) ) { return null; }
		return array( 'id' => (int) $id, 'title' => $term->name );
	}
	$post = get_post( $id );
	if ( ! $post || $kind !== $post->post_type || ! current_user_can( 'read_post', $id ) ) { return null; }
	return array( 'id' => (int) $id, 'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ) );
}

function wpmain_catalog_editor_picker( $name, $value, $kind = 'product', $limit = 4, $attributes = '' ) {
	$ids = is_array( $value ) ? $value : ( is_scalar( $value ) ? explode( ',', (string) $value ) : array() );
	$ids = array_slice( array_values( array_unique( array_filter( array_map( static function ( $id ) { return is_scalar( $id ) ? absint( $id ) : 0; }, $ids ) ) ) ), 0, $limit );
	$items = array();
	foreach ( $ids as $id ) {
		$item = wpmain_catalog_editor_item( $id, $kind );
		if ( $item ) { $items[] = $item; }
	}
	echo '<div class="wpmain-editor-picker" data-kind="' . esc_attr( $kind ) . '" data-limit="' . esc_attr( $limit ) . '" data-items="' . esc_attr( wp_json_encode( $items ) ) . '">';
	// A plain ID field remains usable if admin JavaScript fails or is disabled.
	echo '<input class="widefat wpmain-picker-value" type="text" id="' . esc_attr( str_replace( '_', '-', $name ) ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( implode( ',', array_map( 'absint', $ids ) ) ) . '" ' . $attributes . '>';
	echo '<div class="wpmain-picker-ui" hidden><ul class="wpmain-picker-selected"></ul><label><span class="screen-reader-text">' . esc_html__( 'Search by name', 'wp-store-catalog' ) . '</span><input type="search" class="widefat wpmain-picker-search" autocomplete="off" placeholder="' . esc_attr__( 'Search by name…', 'wp-store-catalog' ) . '"></label><ul class="wpmain-picker-results" hidden></ul><p class="description wpmain-picker-status" role="status" aria-live="polite"></p></div></div>';
}

function wpmain_catalog_editor_media( $name, $value ) {
	$id = absint( $value );
	$image = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : false;
	echo '<div class="wpmain-editor-media"><input class="wpmain-media-value" type="number" min="0" step="1" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" value="' . esc_attr( $id ?: '' ) . '"><div class="wpmain-media-ui" hidden>';
	echo '<img class="wpmain-media-preview" alt=""' . ( $image ? ' src="' . esc_url( $image ) . '"' : ' hidden' ) . '><span class="wpmain-media-name">' . esc_html( $id ? get_the_title( $id ) : '' ) . '</span><div><button type="button" class="button wpmain-media-choose">' . esc_html__( 'Choose image', 'wp-store-catalog' ) . '</button> <button type="button" class="button wpmain-media-remove"' . ( $id ? '' : ' hidden' ) . '>' . esc_html__( 'Remove image', 'wp-store-catalog' ) . '</button></div></div></div>';
}

function wpmain_catalog_editor_assets() {
	$screen = get_current_screen();
	if ( ! $screen || ( ! in_array( $screen->base, array( 'post', 'term', 'edit-tags', 'customize' ), true ) ) ) { return; }
	wp_enqueue_media();
	$base = plugin_dir_url( __FILE__ ) . 'assets/';
	wp_enqueue_script( 'wpmain-editor-controls', $base . 'editor-controls.js', array( 'media-views' ), (string) filemtime( __DIR__ . '/assets/editor-controls.js' ), true );
	wp_enqueue_style( 'wpmain-editor-controls', $base . 'editor-controls.css', array(), (string) filemtime( __DIR__ . '/assets/editor-controls.css' ) );
	wp_localize_script( 'wpmain-editor-controls', 'wpmainEditor', array(
		'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'wpmain_editor_search' ),
		'choose' => __( 'Choose image', 'wp-store-catalog' ), 'use' => __( 'Use this image', 'wp-store-catalog' ),
		'remove' => __( 'Remove %s', 'wp-store-catalog' ), 'earlier' => __( 'Move %s earlier', 'wp-store-catalog' ), 'later' => __( 'Move %s later', 'wp-store-catalog' ),
		'hint' => __( 'Search by name. Selected items appear in this order. Limit: %s.', 'wp-store-catalog' ),
		'loading' => __( 'Searching…', 'wp-store-catalog' ), 'empty' => __( 'No matching items.', 'wp-store-catalog' ),
		'error' => __( 'Search failed. Try again.', 'wp-store-catalog' ), 'limit' => __( 'Selection limit reached.', 'wp-store-catalog' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'wpmain_catalog_editor_assets' );
add_action( 'customize_controls_enqueue_scripts', 'wpmain_catalog_editor_assets' );

function wpmain_catalog_editor_search_allowed( $kind ) {
	if ( 'product_cat' === $kind ) { return current_user_can( 'manage_product_terms' ) || current_user_can( 'edit_theme_options' ); }
	if ( 'product' === $kind ) { return current_user_can( 'edit_products' ) || current_user_can( 'edit_theme_options' ); }
	return 'post' === $kind && ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_products' ) );
}

add_action( 'wp_ajax_wpmain_editor_search', static function () {
	check_ajax_referer( 'wpmain_editor_search', 'security' );
	$kind = isset( $_GET['kind'] ) && is_string( $_GET['kind'] ) ? sanitize_key( $_GET['kind'] ) : '';
	if ( ! wpmain_catalog_editor_search_allowed( $kind ) ) { wp_send_json_error( null, 403 ); }
	$term = isset( $_GET['term'] ) && is_string( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	if ( mb_strlen( $term ) < 2 || mb_strlen( $term ) > 100 ) { wp_send_json_success( array() ); }
	$items = array();
	if ( 'product_cat' === $kind ) {
		$terms = get_terms( array( 'taxonomy' => $kind, 'hide_empty' => false, 'search' => $term, 'number' => 20 ) );
		if ( ! is_wp_error( $terms ) ) { foreach ( $terms as $item ) { $items[] = array( 'id' => $item->term_id, 'title' => $item->name ); } }
	} else {
		$posts = get_posts( array( 'post_type' => $kind, 'post_status' => 'publish', 'has_password' => false, 's' => $term, 'search_columns' => array( 'post_title' ), 'posts_per_page' => 20, 'no_found_rows' => true ) );
		foreach ( $posts as $post ) {
			if ( 'product' === $kind ) { $product = function_exists( 'wc_get_product' ) ? wc_get_product( $post->ID ) : null; if ( ! $product || ! $product->is_visible() ) { continue; } }
			$item = wpmain_catalog_editor_item( $post->ID, $kind );
			if ( $item ) { $items[] = $item; }
		}
	}
	wp_send_json_success( $items );
} );

add_action( 'add_meta_boxes_post', static function () {
	add_meta_box( 'wpmain-article-products', __( 'Article product recommendations', 'wp-store-catalog' ), static function ( $post ) {
		wpmain_catalog_editor_picker( 'wpmain_article_products', get_post_meta( $post->ID, '_wpmain_article_products', true ), 'product', 4 );
		echo '<p class="description">' . esc_html__( 'Up to four products shown after the article. Leave blank if you already placed product shortcodes in the article.', 'wp-store-catalog' ) . '</p>';
		wp_nonce_field( 'wpmain_article_products', 'wpmain_article_products_nonce' );
	}, 'post', 'normal' );
} );
add_action( 'save_post_post', static function ( $id ) {
	$nonce = $_POST['wpmain_article_products_nonce'] ?? '';
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'wpmain_article_products' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) { return; }
	if ( isset( $_POST['wpmain_article_products'] ) && is_string( $_POST['wpmain_article_products'] ) ) {
		update_post_meta( $id, '_wpmain_article_products', wpmain_catalog_ids( wp_unslash( $_POST['wpmain_article_products'] ), 0, 'product', 4 ) );
	}
} );
