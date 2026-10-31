<?php
/** Disposable demo layout fixtures; never deploy this file. */
require_once '/wordpress/wp-load.php';
function wpmain_preview_layouts() {
	if ( 'ابزار تتو / پیش‌نمایش' !== get_option( 'blogname' ) || get_option( 'wpmain_layout_preview' ) ) { return; }
	$pages = array(
		'brands' => array( 'برندها', 'templates/page-brands.php', '<p>برندهای نمونه برای بررسی چیدمان فهرست برندها.</p>' ),
		'help' => array( 'راهنمای خرید', 'templates/page-help.php', '<details><summary>چطور محصول مناسب را انتخاب کنم؟</summary><p>مشخصات، راهنمای انتخاب و مقایسه محصولات را در صفحه هر محصول ببینید.</p></details><details><summary>چطور سفارش را پیگیری کنم؟</summary><p>از صفحه پیگیری سفارش یا حساب کاربری استفاده کنید.</p><a href="/order-tracking/">پیگیری سفارش</a></details>' ),
		'order-tracking' => array( 'پیگیری سفارش', 'templates/page-help.php', '[woocommerce_order_tracking]' ),
	);
	foreach ( $pages as $slug => $data ) {
		if ( get_page_by_path( $slug ) ) { continue; }
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $data[0], 'post_content' => $data[2] ) );
		if ( $id && ! is_wp_error( $id ) ) { update_post_meta( $id, '_wp_page_template', $data[1] ); }
	}
	if ( taxonomy_exists( 'product_brand' ) ) {
		foreach ( array( 'برند نمونه الف', 'برند نمونه ب' ) as $index => $name ) {
			$term = term_exists( $name, 'product_brand' );
			if ( ! $term ) { $term = wp_insert_term( $name, 'product_brand' ); }
			if ( is_wp_error( $term ) ) { continue; }
			foreach ( array( 1 + $index, 5 + $index ) as $sku ) {
				$id = wc_get_product_id_by_sku( 'PREVIEW-' . $sku );
				if ( $id ) { wp_set_object_terms( $id, (int) $term['term_id'], 'product_brand' ); }
			}
		}
	}
	update_option( 'widget_woocommerce_price_filter', array( 2 => array( 'title' => 'محدوده قیمت' ), '_multiwidget' => 1 ) );
	update_option( 'widget_woocommerce_layered_nav_filters', array( 2 => array( 'title' => 'فیلترهای فعال' ), '_multiwidget' => 1 ) );
	$sidebars = get_option( 'sidebars_widgets', array() );
	$sidebars['shop-filters'] = array( 'woocommerce_price_filter-2', 'woocommerce_layered_nav_filters-2' );
	update_option( 'sidebars_widgets', $sidebars );
	update_option( 'wpmain_layout_preview', '0.6.0' );
}
if ( did_action( 'init' ) ) { wpmain_preview_layouts(); } else { add_action( 'init', 'wpmain_preview_layouts', 40 ); }
