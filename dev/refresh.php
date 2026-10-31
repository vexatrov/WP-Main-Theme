<?php
/** Local demo fixtures only. Also safe to re-run against the existing demo. */
require_once '/wordpress/wp-load.php';

function wpmain_refresh_demo() {
	if ( '0.4.0' === get_option( 'wpmain_preview_version' ) ) { return; }
	if ( 'ابزار تتو / پیش‌نمایش' !== get_option( 'blogname' ) ) { return; }
	if ( 'IRR' === get_option( 'woocommerce_currency' ) ) {
		for ( $i = 1; $i <= 8; $i++ ) {
			$product = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-' . $i ) );
			if ( $product ) { $product->set_regular_price( (float) $product->get_regular_price() / 10 ); $product->save(); }
		}
	}
	update_option( 'woocommerce_currency', 'IRT' );
	update_option( 'woocommerce_currency_pos', 'right_space' );
	update_option( 'woocommerce_price_num_decimals', 0 );
	update_option( 'woocommerce_coming_soon', 'no' );
	$pages = array(
		'about' => array( 'درباره ما', 'templates/page-about.php', '<p>این نسخه آزمایشی، پایه مشترک فروشگاه‌های تجهیزات تتو بدن و ابزار زیبایی است. داستان و اطلاعات واقعی هر برند در طراحی اختصاصی همان فروشگاه قرار می‌گیرد.</p>' ),
		'contact' => array( 'تماس با ما', 'templates/page-contact.php', '<p>راه‌های ارتباطی تأییدشده هر فروشگاه، شامل تلفن و واتساپ، در این بخش نمایش داده می‌شوند. این نسخه آزمایشی اطلاعات تماس واقعی ندارد.</p><p><a href="/shop/">بازگشت به فروشگاه</a></p>' ),
		'journal' => array( 'مجله', '', '' ),
	);
	$ids = array();
	foreach ( $pages as $slug => $data ) {
		$existing = get_page_by_path( $slug );
		$id = $existing ? $existing->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $data[0], 'post_content' => $data[2] ) );
		$ids[ $slug ] = $id;
		if ( $data[1] ) { update_post_meta( $id, '_wp_page_template', $data[1] ); }
	}
	update_option( 'page_for_posts', $ids['journal'] );
	set_theme_mod( 'wpmain_about_page', $ids['about'] );
	set_theme_mod( 'wpmain_footer_about', 'یک پیش‌نمایش از فروشگاه تخصصی ابزار تتو بدن؛ معرفی کوتاه و واقعی هر برند در این قسمت قرار می‌گیرد.' );
	$footer = wp_create_nav_menu( 'پیوندهای پایین صفحه' );
	if ( ! is_wp_error( $footer ) ) {
		foreach ( $ids as $id ) { wp_update_nav_menu_item( $footer, 0, array( 'menu-item-title' => get_the_title( $id ), 'menu-item-object' => 'page', 'menu-item-object-id' => $id, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) ); }
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		$locations['footer'] = $footer;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
	$titles = array( 'راهنمای انتخاب تجهیزات تتو بدن', 'آشنایی با کارتریج‌ها و کاربرد آن‌ها در طراحی‌های مختلف تتو بدن', 'نگهداری ابزار تتو' );
	$media = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	foreach ( $titles as $index => $title ) {
		$slug = 'preview-guide-' . $index;
		if ( get_page_by_path( $slug, OBJECT, 'post' ) ) { continue; }
		$id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => '<p>این متن صرفاً نمونه‌ای برای بررسی چیدمان مجله فروشگاه است. محتوای آموزشی واقعی پس از تأیید هر برند اضافه می‌شود.</p>', 'post_excerpt' => $index ? 'نمونه متن با طول متفاوت برای بررسی هم‌ترازی کارت‌های مقاله در صفحه مجله و اندازه‌های مختلف نمایشگر.' : 'نمونه کوتاه برای بررسی چیدمان کارت مقاله.' ) );
		if ( $media && 1 !== $index ) { set_post_thumbnail( $id, $media[0] ); }
	}
	// Long titles stress the shared product layout without altering real catalog data.
	$product = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-8' ) );
	if ( $product ) { $product->set_name( 'لوازم محافظتی و جانبی برای استفاده روزانه در استودیو تتو — نمونه آزمایشی' ); $product->save(); }
	update_option( 'wpmain_preview_version', '0.4.0' );
}
if ( did_action( 'init' ) ) { wpmain_refresh_demo(); } else { add_action( 'init', 'wpmain_refresh_demo', 30 ); }
