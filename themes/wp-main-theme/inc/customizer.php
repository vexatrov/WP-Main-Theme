<?php
defined( 'ABSPATH' ) || exit;

function wpmain_customize_register( $customizer ) {
	$customizer->add_section( 'wpmain_storefront', array( 'title' => __( 'Storefront homepage', 'wp-main-theme' ), 'priority' => 35 ) );
	$fields = array(
		'hero_eyebrow' => array( __( 'Intro label', 'wp-main-theme' ), '', 'text' ),
		'hero_title' => array( __( 'Heading', 'wp-main-theme' ), '', 'text' ),
		'hero_description' => array( __( 'Description', 'wp-main-theme' ), '', 'textarea' ),
		'hero_button' => array( __( 'Shop button label', 'wp-main-theme' ), __( 'Explore the shop', 'wp-main-theme' ), 'text' ),
	);
	foreach ( $fields as $key => $field ) {
		$customizer->add_setting( 'wpmain_' . $key, array( 'default' => $field[1], 'sanitize_callback' => 'textarea' === $field[2] ? 'sanitize_textarea_field' : 'sanitize_text_field' ) );
		$customizer->add_control( 'wpmain_' . $key, array( 'label' => $field[0], 'section' => 'wpmain_storefront', 'type' => $field[2] ) );
	}
	$merchandising = array(
		'home_categories' => array( __( 'Category IDs in display order', 'wp-main-theme' ), __( 'Comma-separated IDs; leave blank for six populated top-level categories.', 'wp-main-theme' ), 'wpmain_sanitize_home_ids' ),
		'home_products' => array( __( 'Product IDs in display order', 'wp-main-theme' ), __( 'Up to eight published catalog products. Leave blank to use the product source below.', 'wp-main-theme' ), 'wpmain_sanitize_home_ids' ),
		'home_sections' => array( __( 'Homepage section order', 'wp-main-theme' ), __( 'Comma-separated: content, catalog, journal. Omitted sections are appended. The hero remains first.', 'wp-main-theme' ), 'wpmain_sanitize_home_sections' ),
	);
	foreach ( $merchandising as $key => $field ) {
		$customizer->add_setting( 'wpmain_' . $key, array( 'default' => '', 'sanitize_callback' => $field[2] ) );
		$customizer->add_control( 'wpmain_' . $key, array( 'label' => $field[0], 'description' => $field[1], 'section' => 'wpmain_storefront', 'type' => 'text' ) );
	}
	$customizer->add_setting( 'wpmain_home_product_source', array( 'default' => 'latest', 'sanitize_callback' => 'wpmain_sanitize_home_product_source' ) );
	$customizer->add_control( 'wpmain_home_product_source', array( 'label' => __( 'Homepage product source', 'wp-main-theme' ), 'section' => 'wpmain_storefront', 'type' => 'select', 'choices' => array( 'latest' => __( 'Latest arrivals', 'wp-main-theme' ), 'featured' => __( 'WooCommerce featured products', 'wp-main-theme' ) ) ) );
	$customizer->add_setting( 'wpmain_home_products_first', array( 'default' => false, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$customizer->add_control( 'wpmain_home_products_first', array( 'label' => __( 'Show products before categories', 'wp-main-theme' ), 'section' => 'wpmain_storefront', 'type' => 'checkbox' ) );
	$customizer->add_setting( 'wpmain_hero_image', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$customizer->add_control( new WP_Customize_Media_Control( $customizer, 'wpmain_hero_image', array( 'label' => __( 'Hero image', 'wp-main-theme' ), 'section' => 'wpmain_storefront', 'mime_type' => 'image' ) ) );
	$customizer->add_section( 'wpmain_footer', array( 'title' => __( 'Footer information', 'wp-main-theme' ), 'priority' => 36 ) );
	$customizer->add_setting( 'wpmain_footer_about', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$customizer->add_control( 'wpmain_footer_about', array( 'label' => __( 'Short brand introduction', 'wp-main-theme' ), 'description' => __( 'A short, useful introduction for visitors. Avoid repeating keywords.', 'wp-main-theme' ), 'section' => 'wpmain_footer', 'type' => 'textarea' ) );
	$customizer->add_setting( 'wpmain_about_page', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$customizer->add_control( 'wpmain_about_page', array( 'label' => __( 'About page', 'wp-main-theme' ), 'section' => 'wpmain_footer', 'type' => 'dropdown-pages' ) );
	$customizer->add_setting( 'wpmain_contact_page', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$customizer->add_control( 'wpmain_contact_page', array( 'label' => __( 'Contact and product enquiry page', 'wp-main-theme' ), 'section' => 'wpmain_footer', 'type' => 'dropdown-pages' ) );
}
add_action( 'customize_register', 'wpmain_customize_register' );

/** Retain only positive, unique decimal IDs in the editor's order. */
function wpmain_sanitize_home_ids( $value ) {
	$ids = array();
	foreach ( explode( ',', is_string( $value ) ? $value : '' ) as $item ) {
		$item = trim( $item );
		if ( ctype_digit( $item ) && (int) $item > 0 ) { $ids[] = (int) $item; }
	}
	return implode( ',', array_slice( array_unique( $ids ), 0, 8 ) );
}

function wpmain_sanitize_home_sections( $value ) {
	$order = is_string( $value ) ? array_map( 'trim', explode( ',', $value ) ) : array();
	return implode( ',', array_unique( array_merge( array_intersect( $order, array( 'content', 'catalog', 'journal' ) ), array( 'content', 'catalog', 'journal' ) ) ) );
}

function wpmain_sanitize_home_product_source( $value ) {
	return 'featured' === $value ? 'featured' : 'latest';
}
