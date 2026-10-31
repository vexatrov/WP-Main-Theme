<?php
/** Demonstrate generic social buttons and native nested menu items in the fictional preview only. */
require_once '/wordpress/wp-load.php';
function wpmain_header_preview() {
	if ( 'ابزار تتو / پیش‌نمایش' !== get_option( 'blogname' ) || get_option( 'wpmain_header_preview_v1' ) ) { return; }
	foreach ( array( 'instagram' => 'https://www.instagram.com/', 'telegram' => 'https://t.me/' ) as $name => $url ) {
		if ( ! get_theme_mod( 'wpmain_social_' . $name ) ) { set_theme_mod( 'wpmain_social_' . $name, $url ); }
	}
	$locations = get_nav_menu_locations();
	$menu = $locations['primary'] ?? 0;
	$parent = get_term_by( 'slug', 'machines', 'product_cat' );
	if ( ! $menu || ! $parent ) { return; }
	$items = wp_get_nav_menu_items( $menu ) ?: array();
	$parent_item = 0; $existing = array();
	foreach ( $items as $item ) {
		if ( 'product_cat' === $item->object ) { $existing[] = (int) $item->object_id; if ( (int) $item->object_id === $parent->term_id ) { $parent_item = $item->ID; } }
		if ( untrailingslashit( $item->url ) === untrailingslashit( get_term_link( $parent ) ) ) { $parent_item = $item->ID; }
	}
	if ( ! $parent_item ) { return; }
	if ( ! add_option( 'wpmain_header_preview_v1', 'working', '', false ) ) { return; }
	$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent->term_id, 'hide_empty' => false, 'number' => 8 ) );
	if ( ! is_wp_error( $children ) ) {
		foreach ( $children as $child ) {
			if ( in_array( $child->term_id, $existing, true ) ) { continue; }
			wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $child->name, 'menu-item-type' => 'taxonomy', 'menu-item-object' => 'product_cat', 'menu-item-object-id' => $child->term_id, 'menu-item-parent-id' => $parent_item, 'menu-item-status' => 'publish' ) );
		}
	}
	update_option( 'wpmain_header_preview_v1', 1, false );
}
if ( did_action( 'init' ) ) { wpmain_header_preview(); } else { add_action( 'init', 'wpmain_header_preview', 95 ); }
