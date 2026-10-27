<?php
/**
 * Plugin Name: WP Store Catalog
 * Description: Product guidance, relationships, Persian search and lightweight storefront SEO for the shared storefront.
 * Version: 0.4.0
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * Text Domain: wp-store-catalog
 * License: GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	load_plugin_textdomain( 'wp-store-catalog', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

/** Names are stable WooCommerce attribute keys, never copied specification values. */
function wpmain_catalog_spec_names( $input, $product ) {
	$input = is_array( $input ) ? $input : explode( ',', is_string( $input ) ? $input : '' );
	$available = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( $attribute->get_visible() ) { $available[] = $attribute->get_name(); }
	}
	$names = array();
	foreach ( $input as $name ) {
		$name = is_string( $name ) ? trim( $name ) : '';
		if ( in_array( $name, $available, true ) && ! in_array( $name, $names, true ) ) { $names[] = $name; }
		if ( count( $names ) >= 4 ) { break; }
	}
	return $names;
}

function wpmain_catalog_ids( $input, $self = 0, $type = 'product', $limit = 4 ) {
	$values = is_array( $input ) ? $input : explode( ',', (string) $input );
	$ids = array();
	foreach ( $values as $value ) {
		$id = absint( is_scalar( $value ) ? $value : 0 );
		if ( ! $id || $id === $self || in_array( $id, $ids, true ) || $type !== get_post_type( $id ) ) { continue; }
		$ids[] = $id;
		if ( count( $ids ) >= $limit ) { break; }
	}
	return $ids;
}

require_once __DIR__ . '/search.php';
require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/seo.php';

function wpmain_catalog_guidance( $id ) {
	$raw_faq = get_post_meta( $id, '_wpmain_faq', true );
	$faq = is_array( $raw_faq ) ? array_slice( array_values( array_filter( $raw_faq, function ( $row ) { return is_array( $row ) && isset( $row['question'], $row['answer'] ) && is_string( $row['question'] ) && is_string( $row['answer'] ) && trim( $row['question'] ) && trim( $row['answer'] ); } ) ), 0, 6 ) : array();
	$use_cases = get_post_meta( $id, '_wpmain_use_cases', true );
	$audience = get_post_meta( $id, '_wpmain_audience', true );
	return array(
		'use_cases' => is_string( $use_cases ) ? $use_cases : '',
		'audience' => is_string( $audience ) ? $audience : '',
		'compatible' => wpmain_catalog_ids( get_post_meta( $id, '_wpmain_compatible', true ), $id ),
		'alternatives' => wpmain_catalog_ids( get_post_meta( $id, '_wpmain_alternatives', true ), $id ),
		'comparison' => wpmain_catalog_ids( get_post_meta( $id, '_wpmain_comparison', true ), $id, 'product', 3 ),
		'guides' => wpmain_catalog_ids( get_post_meta( $id, '_wpmain_guides', true ), 0, 'post' ),
		'faq' => $faq,
	);
}

add_filter( 'woocommerce_product_data_tabs', function ( $tabs ) {
	$tabs['wpmain_guidance'] = array( 'label' => __( 'Product guidance', 'wp-store-catalog' ), 'target' => 'wpmain-guidance-data', 'priority' => 65 );
	return $tabs;
} );

function wpmain_catalog_editor() {
	global $product_object;
	if ( ! $product_object ) { return; }
	$id = $product_object->get_id();
	$data = wpmain_catalog_guidance( $id );
	echo '<div id="wpmain-guidance-data" class="panel woocommerce_options_panel hidden">';
	wp_nonce_field( 'wpmain_save_guidance', 'wpmain_guidance_nonce' );
	$specs = $product_object->get_meta( '_wpmain_key_specs', true );
	$spec_options = array( '' => __( 'Automatic / unused', 'wp-store-catalog' ) );
	foreach ( $product_object->get_attributes() as $attribute ) {
		if ( $attribute->get_visible() ) { $spec_options[ $attribute->get_name() ] = wc_attribute_label( $attribute->get_name(), $product_object ); }
	}
	for ( $slot = 0; $slot < 4; $slot++ ) {
		woocommerce_wp_select( array( 'id' => '_wpmain_key_spec_' . $slot, 'name' => '_wpmain_key_specs[]', 'value' => is_array( $specs ) ? ( $specs[ $slot ] ?? '' ) : '', 'label' => sprintf( __( 'Key specification %d', 'wp-store-catalog' ), $slot + 1 ), 'options' => $spec_options ) );
	}
	echo '<p class="form-field description">' . esc_html__( 'Choose up to four visible attributes in display order. Leave all slots empty for the first four visible attributes. Save attribute changes before selecting specifications.', 'wp-store-catalog' ) . '</p>';
	woocommerce_wp_textarea_input( array( 'id' => '_wpmain_use_cases', 'label' => __( 'Use cases', 'wp-store-catalog' ), 'description' => __( 'Explain the applications. One paragraph per line.', 'wp-store-catalog' ) ) );
	woocommerce_wp_textarea_input( array( 'id' => '_wpmain_audience', 'label' => __( 'Who is it for?', 'wp-store-catalog' ) ) );
	woocommerce_wp_textarea_input( array( 'id' => '_wpmain_search_aliases', 'label' => __( 'Search aliases', 'wp-store-catalog' ), 'description' => __( 'Brand, model or common spelling aliases. Separate each alias with a new line.', 'wp-store-catalog' ) ) );
	$fields = array(
		'compatible' => __( 'Verified compatible products', 'wp-store-catalog' ),
		'alternatives' => __( 'Alternatives for the same use', 'wp-store-catalog' ),
		'comparison' => __( 'Compare specifications with', 'wp-store-catalog' ),
		'guides' => __( 'Related buying guides', 'wp-store-catalog' ),
	);
	foreach ( $fields as $key => $label ) {
		$action = 'guides' === $key ? 'wpmain_search_guides' : 'woocommerce_json_search_products';
		echo '<p class="form-field"><label for="_wpmain_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		echo '<select multiple="multiple" class="wc-product-search" style="width:50%" id="_wpmain_' . esc_attr( $key ) . '" name="_wpmain_' . esc_attr( $key ) . '[]" data-action="' . esc_attr( $action ) . '" data-placeholder="' . esc_attr__( 'Search by name…', 'wp-store-catalog' ) . '">';
		foreach ( $data[ $key ] as $selected ) {
			echo '<option selected="selected" value="' . esc_attr( $selected ) . '">' . esc_html( get_the_title( $selected ) . ' (#' . $selected . ')' ) . '</option>';
		}
		echo '</select></p>';
	}
	echo '<p class="form-field description">' . esc_html__( 'Choose up to 4 compatible products, 4 alternatives, 3 comparisons and 4 guides. Compatibility must be verified; comparison attributes should use matching names and units.', 'wp-store-catalog' ) . '</p>';
	for ( $i = 0; $i < 6; $i++ ) {
		$faq = $data['faq'][ $i ] ?? array( 'question' => '', 'answer' => '' );
		echo '<p class="form-field"><label for="wpmain-faq-q-' . esc_attr( $i ) . '">' . esc_html( sprintf( __( 'FAQ %d', 'wp-store-catalog' ), $i + 1 ) ) . '</label>';
		echo '<input id="wpmain-faq-q-' . esc_attr( $i ) . '" name="wpmain_faq_question[]" value="' . esc_attr( $faq['question'] ?? '' ) . '" aria-label="' . esc_attr__( 'Question', 'wp-store-catalog' ) . '"></p>';
		echo '<p class="form-field"><label for="wpmain-faq-a-' . esc_attr( $i ) . '">' . esc_html__( 'Answer', 'wp-store-catalog' ) . '</label><textarea id="wpmain-faq-a-' . esc_attr( $i ) . '" name="wpmain_faq_answer[]">' . esc_textarea( $faq['answer'] ?? '' ) . '</textarea></p>';
	}
	echo '</div>';
}
add_action( 'woocommerce_product_data_panels', 'wpmain_catalog_editor' );

function wpmain_catalog_save( $product ) {
	$nonce = isset( $_POST['wpmain_guidance_nonce'] ) && is_string( $_POST['wpmain_guidance_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpmain_guidance_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'wpmain_save_guidance' ) || ! current_user_can( 'edit_post', $product->get_id() ) ) { return; }
	// Only this panel's field can change the selection; preserve it on partial saves.
	if ( isset( $_POST['_wpmain_key_specs'] ) && is_array( $_POST['_wpmain_key_specs'] ) ) {
		$raw_specs = wp_unslash( $_POST['_wpmain_key_specs'] );
		if ( ! array_filter( $raw_specs ) ) { $product->delete_meta_data( '_wpmain_key_specs' ); }
		else { $product->update_meta_data( '_wpmain_key_specs', wpmain_catalog_spec_names( $raw_specs, $product ) ); }
	}
	foreach ( array( 'use_cases', 'audience', 'search_aliases' ) as $key ) {
		$raw = $_POST[ '_wpmain_' . $key ] ?? '';
		$product->update_meta_data( '_wpmain_' . $key, is_string( $raw ) ? sanitize_textarea_field( wp_unslash( $raw ) ) : '' );
	}
	foreach ( array( 'compatible', 'alternatives', 'comparison', 'guides' ) as $key ) {
		$product->update_meta_data( '_wpmain_' . $key, wpmain_catalog_ids( wp_unslash( $_POST[ '_wpmain_' . $key ] ?? array() ), $product->get_id(), 'guides' === $key ? 'post' : 'product', 'comparison' === $key ? 3 : 4 ) );
	}
	$questions = isset( $_POST['wpmain_faq_question'] ) && is_array( $_POST['wpmain_faq_question'] ) ? wp_unslash( $_POST['wpmain_faq_question'] ) : array();
	$answers = isset( $_POST['wpmain_faq_answer'] ) && is_array( $_POST['wpmain_faq_answer'] ) ? wp_unslash( $_POST['wpmain_faq_answer'] ) : array();
	$faq = array();
	foreach ( array_slice( $questions, 0, 6 ) as $index => $question ) {
		$answer = $answers[ $index ] ?? '';
		$question = is_string( $question ) ? sanitize_text_field( $question ) : '';
		$answer = is_string( $answer ) ? sanitize_textarea_field( $answer ) : '';
		if ( $question && $answer ) { $faq[] = array( 'question' => $question, 'answer' => $answer ); }
	}
	$product->update_meta_data( '_wpmain_faq', $faq );
}
add_action( 'woocommerce_admin_process_product_object', 'wpmain_catalog_save' );

add_action( 'wp_ajax_wpmain_search_guides', function () {
	check_ajax_referer( 'search-products', 'security' );
	if ( ! current_user_can( 'edit_products' ) ) { wp_send_json_error( null, 403 ); }
	$term = isset( $_GET['term'] ) && is_string( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 's' => $term, 'posts_per_page' => 20 ) );
	$result = array();
	foreach ( $posts as $post ) { $result[ $post->ID ] = $post->post_title . ' (#' . $post->ID . ')'; }
	wp_send_json( $result );
} );
