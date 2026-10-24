<?php
defined( 'ABSPATH' ) || exit;

function wpmain_setup() {
	load_theme_textdomain( 'wp-main-theme', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array( 'height' => 100, 'width' => 300, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	register_nav_menus( array( 'primary' => __( 'Primary navigation', 'wp-main-theme' ), 'footer' => __( 'Footer navigation', 'wp-main-theme' ) ) );
}
add_action( 'after_setup_theme', 'wpmain_setup' );
