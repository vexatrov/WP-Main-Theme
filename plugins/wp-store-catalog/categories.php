<?php
defined( 'ABSPATH' ) || exit;

add_action( 'product_cat_edit_form_fields', function ( $term ) {
	$attributes = get_term_meta( $term->term_id, '_wpmain_filter_attributes', true );
	$guides = get_term_meta( $term->term_id, '_wpmain_category_guides', true );
	echo '<tr class="form-field"><th><label for="wpmain-category-facets">' . esc_html__( 'Filter attribute slugs', 'wp-store-catalog' ) . '</label></th><td><input id="wpmain-category-facets" name="wpmain_category_facets" value="' . esc_attr( is_array( $attributes ) ? implode( ', ', $attributes ) : '' ) . '"><p>' . esc_html__( 'Up to four global attribute slugs, e.g. stroke, diameter. Empty categories inherit their parent settings.', 'wp-store-catalog' ) . '</p></td></tr>';
	echo '<tr class="form-field"><th><label for="wpmain-category-guidance">' . esc_html__( 'Buying guidance', 'wp-store-catalog' ) . '</label></th><td><textarea rows="6" id="wpmain-category-guidance" name="wpmain_category_guidance">' . esc_textarea( get_term_meta( $term->term_id, '_wpmain_buying_guide', true ) ) . '</textarea></td></tr>';
	echo '<tr class="form-field"><th><label for="wpmain-category-guides">' . esc_html__( 'Buying guide article IDs', 'wp-store-catalog' ) . '</label></th><td><input id="wpmain-category-guides" name="wpmain_category_guides" value="' . esc_attr( is_array( $guides ) ? implode( ',', $guides ) : '' ) . '"><p>' . esc_html__( 'Up to four published article IDs, separated by commas.', 'wp-store-catalog' ) . '</p>';
	wp_nonce_field( 'wpmain_category_data', 'wpmain_category_nonce' );
	echo '</td></tr>';
} );
add_action( 'edited_product_cat', function ( $id ) {
	$taxonomy = get_taxonomy( 'product_cat' );
	$nonce = isset( $_POST['wpmain_category_nonce'] ) && is_string( $_POST['wpmain_category_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpmain_category_nonce'] ) ) : '';
	if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->manage_terms ) || ! wp_verify_nonce( $nonce, 'wpmain_category_data' ) ) { return; }
	$input = isset( $_POST['wpmain_category_facets'] ) && is_string( $_POST['wpmain_category_facets'] ) ? wp_unslash( $_POST['wpmain_category_facets'] ) : '';
	$attributes = array();
	foreach ( explode( ',', $input ) as $slug ) {
		$slug = sanitize_title( trim( $slug ) );
		if ( $slug && taxonomy_exists( 'pa_' . $slug ) ) { $attributes[] = $slug; }
	}
	update_term_meta( $id, '_wpmain_filter_attributes', array_slice( array_values( array_unique( $attributes ) ), 0, 4 ) );
	$guidance = isset( $_POST['wpmain_category_guidance'] ) && is_string( $_POST['wpmain_category_guidance'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpmain_category_guidance'] ) ) : '';
	update_term_meta( $id, '_wpmain_buying_guide', $guidance );
	$guides = isset( $_POST['wpmain_category_guides'] ) && is_string( $_POST['wpmain_category_guides'] ) ? wp_unslash( $_POST['wpmain_category_guides'] ) : '';
	update_term_meta( $id, '_wpmain_category_guides', wpmain_catalog_ids( $guides, 0, 'post', 4 ) );
} );

function wpmain_catalog_category_attributes( $term_id ) {
	$ids = array_merge( array( $term_id ), get_ancestors( $term_id, 'product_cat', 'taxonomy' ) );
	foreach ( $ids as $id ) {
		$attributes = get_term_meta( $id, '_wpmain_filter_attributes', true );
		if ( is_array( $attributes ) && $attributes ) { return array_slice( array_filter( $attributes, function ( $slug ) { return is_string( $slug ) && taxonomy_exists( 'pa_' . $slug ); } ), 0, 4 ); }
	}
	return array();
}
