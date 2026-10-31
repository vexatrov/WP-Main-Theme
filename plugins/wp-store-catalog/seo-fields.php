<?php
/** Portable, optional SEO overrides. Empty values retain automatic metadata. */
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_seo_fields( $id, $term = false ) {
	$labels = array( 'title' => __( 'SEO title', 'wp-store-catalog' ), 'description' => __( 'Meta description', 'wp-store-catalog' ), 'image' => __( 'Social image attachment ID', 'wp-store-catalog' ) );
	foreach ( $labels as $key => $label ) {
		$value = $term ? get_term_meta( $id, '_wpmain_seo_' . $key, true ) : get_post_meta( $id, '_wpmain_seo_' . $key, true );
		echo '<p><label for="wpmain_seo_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label><br>';
		if ( 'description' === $key ) {
			echo '<textarea class="widefat" rows="3" id="wpmain_seo_description" name="wpmain_seo_description">' . esc_textarea( is_string( $value ) ? $value : '' ) . '</textarea>';
		} else {
			echo '<input class="widefat" type="' . ( 'image' === $key ? 'number' : 'text' ) . '" id="wpmain_seo_' . esc_attr( $key ) . '" name="wpmain_seo_' . esc_attr( $key ) . '" value="' . esc_attr( is_scalar( $value ) ? $value : '' ) . '"' . ( 'image' === $key ? ' min="0" step="1"' : '' ) . '>';
		}
		echo '</p>';
	}
	echo '<p class="description">' . esc_html__( 'Optional overrides. Leave blank for automatic values. An active SEO plugin owns frontend metadata instead. Image IDs must refer to images in the media library.', 'wp-store-catalog' ) . '</p>';
	wp_nonce_field( 'wpmain_seo', 'wpmain_seo_nonce' );
}

function wpmain_catalog_seo_save_fields( $id, $term = false ) {
	$nonce = $_POST['wpmain_seo_nonce'] ?? '';
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'wpmain_seo' ) ) { return; }
	foreach ( array( 'title', 'description', 'image' ) as $key ) {
		$field = 'wpmain_seo_' . $key;
		// Quick edit, imports and malformed input must not erase saved overrides.
		if ( ! isset( $_POST[ $field ] ) || ! is_string( $_POST[ $field ] ) ) { continue; }
		$value = wp_unslash( $_POST[ $field ] );
		if ( 'image' === $key ) {
			if ( '' !== $value && ! ctype_digit( $value ) ) { continue; }
			$value = absint( $value );
			if ( $value && ! wp_attachment_is_image( $value ) ) { continue; }
		} else {
			$value = wp_html_excerpt( sanitize_text_field( $value ), 'title' === $key ? 200 : 500, '' );
		}
		if ( $term ) { update_term_meta( $id, '_' . $field, $value ); }
		else { update_post_meta( $id, '_' . $field, $value ); }
	}
}

add_action( 'add_meta_boxes', static function ( $type ) {
	if ( ! in_array( $type, array( 'post', 'page', 'product' ), true ) || ! is_post_type_viewable( $type ) ) { return; }
	add_meta_box( 'wpmain-seo', __( 'Search and sharing', 'wp-store-catalog' ), static function ( $post ) { wpmain_catalog_seo_fields( $post->ID ); }, $type, 'normal' );
} );
add_action( 'save_post', static function ( $id, $post ) {
	if ( ! in_array( $post->post_type, array( 'post', 'page', 'product' ), true ) || wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $id ) ) { return; }
	wpmain_catalog_seo_save_fields( $id );
}, 10, 2 );
foreach ( array( 'product_cat', 'product_brand', 'category', 'post_tag' ) as $wpmain_seo_taxonomy ) {
	add_action( $wpmain_seo_taxonomy . '_add_form_fields', static function () { echo '<div class="form-field">'; wpmain_catalog_seo_fields( 0, true ); echo '</div>'; } );
	add_action( $wpmain_seo_taxonomy . '_edit_form_fields', static function ( $term ) { echo '<tr class="form-field"><th>' . esc_html__( 'Search and sharing', 'wp-store-catalog' ) . '</th><td>'; wpmain_catalog_seo_fields( $term->term_id, true ); echo '</td></tr>'; } );
	foreach ( array( 'created_', 'edited_' ) as $wpmain_seo_prefix ) {
		add_action( $wpmain_seo_prefix . $wpmain_seo_taxonomy, static function ( $id ) use ( $wpmain_seo_taxonomy ) {
			$taxonomy = get_taxonomy( $wpmain_seo_taxonomy );
			if ( $taxonomy && current_user_can( 'edit_term', $id ) ) { wpmain_catalog_seo_save_fields( $id, true ); }
		} );
	}
}
unset( $wpmain_seo_taxonomy, $wpmain_seo_prefix );
