<?php
defined( 'ABSPATH' ) || exit;
function wpmain_support_navigation( $current = 0 ) {
	$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => 12, 'orderby' => 'menu_order title', 'order' => 'ASC', 'meta_query' => array( array( 'key' => '_wp_page_template', 'value' => array( 'templates/page-help.php', 'templates/page-contact.php', 'templates/page-about.php' ), 'compare' => 'IN' ) ) ) );
	if ( ! $pages ) { return; }
	echo '<nav class="support-navigation" aria-label="' . esc_attr__( 'Store help and information', 'wp-main-theme' ) . '"><h2>' . esc_html__( 'How can we help?', 'wp-main-theme' ) . '</h2><ul>';
	foreach ( $pages as $page ) { echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '"' . ( (int) $current === (int) $page->ID ? ' aria-current="page"' : '' ) . '>' . esc_html( get_the_title( $page ) ) . '</a></li>'; }
	echo '</ul></nav>';
}
