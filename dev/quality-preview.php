<?php
/** Fictional QA content only; safe to rerun, never deploy to a real shop. */
require_once '/wordpress/wp-load.php';
if ( 'ابزار تاتو / پیش‌نمایش' === get_option( 'blogname' ) ) {
	// Capture test messages inside the disposable database; never send them.
	add_filter( 'pre_wp_mail', function ( $sent, $atts ) { update_option( 'wpmain_qa_last_mail', $atts, false ); return true; }, 10, 2 );
	// Retain the guard for requests after the initial Blueprint finishes.
	if ( ! file_exists( WPMU_PLUGIN_DIR . '/wpmain-demo-mail.php' ) ) {
		wp_mkdir_p( WPMU_PLUGIN_DIR );
		file_put_contents( WPMU_PLUGIN_DIR . '/wpmain-demo-mail.php', '<?php if ( "ابزار تاتو / پیش‌نمایش" === get_option("blogname") ) { add_filter("pre_wp_mail", function($sent,$atts) { update_option("wpmain_qa_last_mail",$atts,false); return true; },10,2); }' );
	}
}
function wpmain_quality_preview() {
	if ( 'ابزار تاتو / پیش‌نمایش' !== get_option( 'blogname' ) || '2' === get_option( 'wpmain_quality_preview' ) || ! function_exists( 'wpmain_catalog_index_product' ) ) { return; }
	$pen = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-1' ) );
	$rotary = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-5' ) );
	if ( ! $pen || ! $rotary ) { return; }
	$machine = get_term_by( 'slug', 'machines', 'product_cat' );
	if ( ! $machine ) { return; }
	if ( ! wc_attribute_taxonomy_id_by_name( 'system' ) ) { wc_create_attribute( array( 'name' => 'سیستم نمونه', 'slug' => 'system', 'type' => 'select', 'order_by' => 'name', 'has_archives' => false ) ); }
	if ( ! taxonomy_exists( 'pa_system' ) ) { register_taxonomy( 'pa_system', 'product', array( 'hierarchical' => false, 'show_ui' => false ) ); }
	foreach ( array( array( $pen, 'wireless', 'پن بی‌سیم نمونه' ), array( $rotary, 'rotary', 'پن روتاری نمونه' ) ) as $data ) {
		list( $product, $slug, $label ) = $data;
		$term = term_exists( $label, 'pa_system' );
		if ( ! $term ) { $term = wp_insert_term( $label, 'pa_system', array( 'slug' => $slug ) ); }
		$child = term_exists( $slug, 'product_cat' );
		if ( ! $child ) { $child = wp_insert_term( $label, 'product_cat', array( 'slug' => $slug, 'parent' => $machine->term_id ) ); }
		if ( ! is_wp_error( $child ) ) { wp_set_object_terms( $product->get_id(), (int) $child['term_id'], 'product_cat' ); }
		if ( ! is_wp_error( $term ) ) {
			$attribute = new WC_Product_Attribute(); $attribute->set_id( wc_attribute_taxonomy_id_by_name( 'system' ) ); $attribute->set_name( 'pa_system' ); $attribute->set_options( array( (int) $term['term_id'] ) ); $attribute->set_visible( true );
			$attributes = $product->get_attributes(); $attributes['pa_system'] = $attribute; $product->set_attributes( $attributes ); $product->save();
		}
	}
	$guide = get_page_by_path( 'preview-guide-1', OBJECT, 'post' );
	update_term_meta( $machine->term_id, '_wpmain_filter_attributes', array( 'system' ) );
	update_term_meta( $machine->term_id, '_wpmain_buying_guide', 'راهنمای آزمایشی انتخاب: سیستم، وزن و کاربرد را مقایسه کنید. مشخصات این محصولات صرفاً نمونه طراحی هستند.' );
	if ( $guide ) {
		update_term_meta( $machine->term_id, '_wpmain_category_guides', array( $guide->ID ) );
		wp_update_post( array( 'ID' => $guide->ID, 'post_content' => '<p>مقاله نمونه برای بررسی فهرست مطالب و پیوند محصولات.</p><h2 id="guide-start">انتخاب ابزار</h2><p>برای انتخاب، مشخصات و نیاز خود را بررسی کنید.</p><h2></h2><h3>سیستم و اندازه</h3><p>مشخصات واقعی باید از تأمین‌کننده تأیید شوند.</p><h2>مقایسه گزینه‌ها</h2><p>این داده‌ها فقط برای بررسی طراحی هستند.</p><h2>مقایسه گزینه‌ها</h2><p>نمونه عنوان تکراری برای بررسی پیوندهای یکتا.</p>[editorial_products ids="' . $pen->get_id() . ',' . $rotary->get_id() . '" heading="محصولات مرتبط با راهنما"]' ) );
	}
	$sale = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-4' ) );
	if ( $sale ) { $sale->set_sale_price( (string) round( (float) $sale->get_regular_price() * .8 ) ); $sale->save(); }
	foreach ( array( 'ENQUIRY' => '', 'OUT' => '120000' ) as $kind => $price ) {
		$product_id = wc_get_product_id_by_sku( 'QA-' . $kind );
		$product = $product_id ? wc_get_product( $product_id ) : new WC_Product_Simple();
		$product->set_name( 'ENQUIRY' === $kind ? 'محصول استعلام قیمت — نمونه' : 'محصول ناموجود — نمونه' );
		$product->set_sku( 'QA-' . $kind ); $product->set_status( 'publish' ); $product->set_regular_price( $price ); $product->set_stock_status( 'OUT' === $kind ? 'outofstock' : 'instock' );
		$product->set_description( 'فقط نمونه بررسی حالت‌های محصول.' ); $product->save();
	}
	$contact = get_page_by_path( 'contact' );
	if ( $contact ) { set_theme_mod( 'wpmain_contact_page', $contact->ID ); }
	$zone = new WC_Shipping_Zone( 0 );
	$free = false;
	foreach ( $zone->get_shipping_methods() as $method ) { if ( 'free_shipping' === $method->id ) { $free = true; } }
	if ( ! $free ) { $instance = $zone->add_shipping_method( 'free_shipping' ); update_option( 'woocommerce_free_shipping_' . $instance . '_settings', array( 'enabled' => 'yes', 'title' => 'ارسال آزمایشی', 'requires' => '' ) ); }
	if ( ! username_exists( 'qa-shopper' ) ) { wp_insert_user( array( 'user_login' => 'qa-shopper', 'user_email' => 'qa-shopper@example.invalid', 'user_pass' => wp_generate_password( 32 ), 'role' => 'customer' ) ); }
	foreach ( array( 'shipping' => 'ارسال سفارش', 'returns' => 'بازگشت کالا', 'warranty' => 'ضمانت و خدمات' ) as $slug => $title ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) { $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => '<p>این صفحه نمونه چیدمان است. شرایط واقعی هر فروشگاه باید پیش از راه‌اندازی توسط صاحب فروشگاه تکمیل و تأیید شود.</p><h2>راهنمای مشتریان</h2><p><a href="/contact/">ارتباط با فروشگاه</a></p>' ) ); update_post_meta( $id, '_wp_page_template', 'templates/page-help.php' ); }
	}
	// Rebuild indexes synchronously for this tiny fictional catalog only.
	foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) { wpmain_catalog_index_product( $id ); }
	update_option( 'wpmain_search_index_version', '1', false );
	update_option( 'wpmain_quality_preview', '2' );
}
if ( did_action( 'init' ) ) { wpmain_quality_preview(); } else { add_action( 'init', 'wpmain_quality_preview', 60 ); }
