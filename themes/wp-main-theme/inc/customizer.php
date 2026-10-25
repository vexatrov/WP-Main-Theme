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
