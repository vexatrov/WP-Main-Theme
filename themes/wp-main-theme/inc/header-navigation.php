<?php
/** Header destinations use real published pages and native editable menus. */
defined( 'ABSPATH' ) || exit;

function wpmain_header_pages() {
	if ( has_nav_menu( 'header_pages' ) ) {
		wp_nav_menu( array( 'theme_location' => 'header_pages', 'container' => false, 'menu_class' => 'header-page-links', 'fallback_cb' => false, 'depth' => 3 ) );
		return;
	}
	$used = array();
	$locations = get_nav_menu_locations();
	foreach ( isset( $locations['primary'] ) ? ( wp_get_nav_menu_items( $locations['primary'] ) ?: array() ) : array() as $item ) { $used[] = untrailingslashit( $item->url ); }
	$links = array( array( home_url( '/' ), __( 'Home', 'wp-main-theme' ) ) );
	$ids = array( (int) get_option( 'page_for_posts' ) );
	if ( function_exists( 'wc_get_page_id' ) ) { $ids[] = wc_get_page_id( 'shop' ); }
	$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => 8, 'orderby' => 'menu_order title', 'order' => 'ASC', 'meta_query' => array( array( 'key' => '_wp_page_template', 'value' => array( 'templates/page-brands.php', 'templates/page-about.php', 'templates/page-contact.php' ), 'compare' => 'IN' ) ) ) );
	foreach ( $pages as $page ) { $ids[] = $page->ID; }
	foreach ( array_unique( array_filter( array_map( 'absint', $ids ) ) ) as $id ) {
		$page = get_post( $id );
		if ( $page && 'publish' === $page->post_status && '' === $page->post_password ) { $links[] = array( get_permalink( $id ), get_the_title( $id ) ); }
	}
	echo '<ul class="header-page-links">';
	foreach ( $links as $link ) {
		$url = untrailingslashit( $link[0] );
		if ( in_array( $url, $used, true ) ) { continue; }
		$used[] = $url;
		echo '<li><a href="' . esc_url( $link[0] ) . '"' . ( $url === untrailingslashit( get_permalink( get_queried_object_id() ) ?: '' ) && is_page() ? ' aria-current="page"' : '' ) . '>' . esc_html( $link[1] ) . '</a></li>';
	}
	echo '</ul>';
}

function wpmain_header_socials() {
	$links = array( 'instagram' => 'Instagram', 'telegram' => 'Telegram', 'whatsapp' => 'WhatsApp' );
	$html = '';
	foreach ( $links as $key => $label ) {
		$url = get_theme_mod( 'wpmain_social_' . $key, '' );
		if ( ! is_string( $url ) || ! esc_url( $url, array( 'http', 'https' ) ) ) { continue; }
		$icons = array(
			'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/>',
			'telegram' => '<path d="m3 11 18-7-4 16-6-5-4 3 1-6 10-6-7 9"/>',
			'whatsapp' => '<path d="M20 11.5a8 8 0 0 1-12 7L3 20l1.5-5A8 8 0 1 1 20 11.5Z"/><path d="M8 8c0 4 4 7 7 7l1-2-3-1-1 1-2-2 1-1-1-3Z"/>',
		);
		$html .= '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '" title="' . esc_attr( $label ) . '"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6">' . $icons[ $key ] . '</svg></a>';
	}
	if ( $html ) { echo '<nav class="header-social" aria-label="' . esc_attr__( 'Social media', 'wp-main-theme' ) . '">' . $html . '</nav>'; }
}
