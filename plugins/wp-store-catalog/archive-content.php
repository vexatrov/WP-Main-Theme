<?php
/** Durable editorial data shared by native category and brand archives. */
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_archive_plain( $value, $limit ) {
	return is_string( $value ) ? trim( wp_html_excerpt( sanitize_text_field( $value ), $limit, '' ) ) : '';
}

function wpmain_catalog_archive_highlights( $input ) {
	$lines = is_array( $input ) ? $input : ( is_string( $input ) ? preg_split( '/\r\n|\r|\n/', $input ) : array() );
	$items = array();
	foreach ( $lines as $line ) {
		$line = wpmain_catalog_archive_plain( $line, 160 );
		if ( '' !== $line && ! in_array( $line, $items, true ) ) { $items[] = $line; }
		if ( count( $items ) >= 3 ) { break; }
	}
	return $items;
}

function wpmain_catalog_archive_fields( $term = null ) {
	$id = is_object( $term ) ? $term->term_id : 0;
	$editing = (bool) $id;
	$fields = array(
		'wpmain_archive_intro' => array( __( 'Short archive introduction', 'wp-store-catalog' ), __( 'Up to 320 characters of factual introductory copy. Leave blank to use a short version of the native description.', 'wp-store-catalog' ), get_term_meta( $id, '_wpmain_archive_intro', true ), 3 ),
		'wpmain_archive_highlights' => array( __( 'Archive facts', 'wp-store-catalog' ), __( 'Up to three short factual lines, 160 characters each. Use only verified product or brand information.', 'wp-store-catalog' ), implode( "\n", wpmain_catalog_archive_highlights( get_term_meta( $id, '_wpmain_archive_highlights', true ) ) ), 4 ),
		'wpmain_archive_content' => array( __( 'Detailed archive buying guide', 'wp-store-catalog' ), __( 'Shown below the products. Safe HTML supports headings, paragraphs, lists, tables and details/summary for FAQs. Do not repeat keywords or invent compatibility claims. Blank categories retain existing buying guidance.', 'wp-store-catalog' ), get_term_meta( $id, '_wpmain_archive_content', true ), 12 ),
	);
	foreach ( $fields as $name => $field ) {
		echo $editing ? '<tr class="form-field"><th>' : '<div class="form-field">';
		echo '<label for="' . esc_attr( $name ) . '">' . esc_html( $field[0] ) . '</label>';
		if ( $editing ) { echo '</th><td>'; }
		echo '<textarea class="large-text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" rows="' . esc_attr( $field[3] ) . '">' . esc_textarea( is_string( $field[2] ) ? $field[2] : '' ) . '</textarea><p class="description">' . esc_html( $field[1] ) . '</p>';
		if ( 'wpmain_archive_content' === $name ) { wp_nonce_field( 'wpmain_archive_content', 'wpmain_archive_nonce' ); }
		echo $editing ? '</td></tr>' : '</div>';
	}
}

function wpmain_catalog_archive_save( $id, $taxonomy ) {
	if ( ! in_array( $taxonomy, array( 'product_cat', 'product_brand' ), true ) ) { return; }
	$object = get_taxonomy( $taxonomy );
	$nonce = isset( $_POST['wpmain_archive_nonce'] ) && is_string( $_POST['wpmain_archive_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpmain_archive_nonce'] ) ) : '';
	if ( ! $object || ! current_user_can( $object->cap->edit_terms ) || ! wp_verify_nonce( $nonce, 'wpmain_archive_content' ) ) { return; }
	foreach ( array( 'intro', 'highlights', 'content' ) as $key ) {
		$field = 'wpmain_archive_' . $key;
		if ( ! isset( $_POST[ $field ] ) || ! is_string( $_POST[ $field ] ) ) { continue; }
		$value = wp_unslash( $_POST[ $field ] );
		$value = 'highlights' === $key ? wpmain_catalog_archive_highlights( $value ) : ( 'intro' === $key ? wpmain_catalog_archive_plain( $value, 320 ) : wp_kses_post( $value ) );
		update_term_meta( $id, '_' . $field, $value );
	}
}

foreach ( array( 'product_cat', 'product_brand' ) as $wpmain_archive_taxonomy ) {
	add_action( $wpmain_archive_taxonomy . '_add_form_fields', 'wpmain_catalog_archive_fields' );
	add_action( $wpmain_archive_taxonomy . '_edit_form_fields', 'wpmain_catalog_archive_fields' );
	add_action( 'created_' . $wpmain_archive_taxonomy, static function ( $id ) use ( $wpmain_archive_taxonomy ) { wpmain_catalog_archive_save( $id, $wpmain_archive_taxonomy ); } );
	add_action( 'edited_' . $wpmain_archive_taxonomy, static function ( $id ) use ( $wpmain_archive_taxonomy ) { wpmain_catalog_archive_save( $id, $wpmain_archive_taxonomy ); } );
}
unset( $wpmain_archive_taxonomy );
