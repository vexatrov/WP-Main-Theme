<?php
/** Correct fictional preview copy without changing existing URLs or technical identifiers. */
defined( 'ABSPATH' ) || exit;

function wpmain_preview_correct_spelling() {
	// The legacy name is intentionally retained only as a migration identity guard.
	if ( ! in_array( get_option( 'blogname' ), array( 'ابزار تاتو / پیش‌نمایش', 'ابزار تتو / پیش‌نمایش' ), true ) || get_option( 'wpmain_preview_spelling_v2' ) ) { return; }
	$correct = static function ( $value ) use ( &$correct ) {
		if ( is_array( $value ) ) { return array_map( $correct, $value ); }
		if ( ! is_string( $value ) ) { return $value; }
		// Preserve HTML attributes and every absolute/relative URL, including old slugs.
		$parts = preg_split( '~(<[^>]*>|https?://[^\s<>"\x27]+|/[^\s<>"\x27]+)~u', $value, -1, PREG_SPLIT_DELIM_CAPTURE );
		foreach ( $parts as $i => $part ) { if ( 0 === $i % 2 ) { $parts[ $i ] = str_replace( 'تاتو', 'تتو', $part ); } }
		return implode( '', $parts );
	};
	foreach ( array( 'blogname', 'blogdescription' ) as $key ) { update_option( $key, $correct( get_option( $key ) ) ); }
	$posts = get_posts( array( 'post_type' => array( 'post', 'page', 'product', 'product_variation', 'attachment', 'nav_menu_item' ), 'post_status' => 'any', 'posts_per_page' => -1 ) );
	foreach ( $posts as $post ) {
		$change = array( 'ID' => $post->ID );
		foreach ( array( 'post_title', 'post_content', 'post_excerpt' ) as $key ) { $change[ $key ] = $correct( $post->$key ); }
		wp_update_post( wp_slash( $change ) );
		foreach ( get_post_meta( $post->ID ) as $key => $values ) {
			if ( str_starts_with( $key, '_wpmain_' ) || '_wp_attachment_image_alt' === $key ) { update_post_meta( $post->ID, $key, wp_slash( $correct( get_post_meta( $post->ID, $key, true ) ) ) ); }
		}
	}
	foreach ( array( 'product_cat', 'product_brand', 'category', 'post_tag' ) as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) { continue; }
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		if ( is_wp_error( $terms ) ) { continue; }
		foreach ( $terms as $term ) {
			wp_update_term( $term->term_id, $taxonomy, array( 'name' => $correct( $term->name ), 'description' => $correct( $term->description ) ) );
			foreach ( get_term_meta( $term->term_id ) as $key => $values ) {
				if ( str_starts_with( $key, '_wpmain_' ) ) { update_term_meta( $term->term_id, $key, wp_slash( $correct( get_term_meta( $term->term_id, $key, true ) ) ) ); }
			}
		}
	}
	foreach ( get_theme_mods() as $key => $value ) { set_theme_mod( $key, $correct( $value ) ); }
	foreach ( get_comments( array( 'status' => 'all', 'number' => 0 ) ) as $comment ) { wp_update_comment( wp_slash( array( 'comment_ID' => $comment->comment_ID, 'comment_content' => $correct( $comment->comment_content ) ) ) ); }
	if ( function_exists( 'wpmain_catalog_index_product' ) ) {
		foreach ( $posts as $post ) { if ( 'product' === $post->post_type ) { clean_post_cache( $post->ID ); wpmain_catalog_index_product( $post->ID ); } }
	}
	update_option( 'wpmain_preview_spelling_v2', 1, false );
}
if ( did_action( 'init' ) ) { wpmain_preview_correct_spelling(); }
else { add_action( 'init', 'wpmain_preview_correct_spelling', 90 ); }
