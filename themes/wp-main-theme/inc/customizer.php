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
}
add_action( 'customize_register', 'wpmain_customize_register' );
