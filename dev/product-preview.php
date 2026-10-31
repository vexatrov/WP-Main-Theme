<?php
/** Fictional fixtures for reviewing product layouts, never production data. */
require_once '/wordpress/wp-load.php';

function wpmain_preview_product_details() {
	if ( 'ابزار تتو / پیش‌نمایش' !== get_option( 'blogname' ) || '0.6.0' === get_option( 'wpmain_product_preview_version' ) ) { return; }
	// Also supports updating a demo that was already running before the plugin existed.
	if ( ! function_exists( 'wpmain_catalog_guidance' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$result = activate_plugin( 'wp-store-catalog/wp-store-catalog.php' );
		if ( is_wp_error( $result ) ) { return; }
	}
	$pen = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-1' ) );
	$alternative = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-5' ) );
	$needle = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-2' ) );
	$magnum = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-6' ) );
	if ( ! $pen || ! $alternative || ! $needle || ! $magnum ) { return; }
	$frames = array( 'pen' => '150 80 500 500', 'ink' => '170 40 460 460', 'cartridge' => '150 50 500 500', 'accessory' => '205 130 390 390' );
	foreach ( $frames as $type => $frame ) {
		$media = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'name' => 'demo-' . $type, 'posts_per_page' => 1 ) );
		if ( ! $media ) { continue; }
		$path = get_attached_file( $media[0]->ID );
		if ( ! $path || 'svg' !== pathinfo( $path, PATHINFO_EXTENSION ) ) { continue; }
		$svg = file_get_contents( $path );
		$svg = preg_replace( '/<svg[^>]*>/', '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="' . $frame . '">', $svg, 1 );
		$svg = preg_replace( '/<ellipse[^>]*\/>/', '', $svg );
		file_put_contents( $path, $svg );
		$metadata = wp_get_attachment_metadata( $media[0]->ID );
		$metadata['width'] = 800; $metadata['height'] = 800;
		wp_update_attachment_metadata( $media[0]->ID, $metadata );
	}
	$guides = get_posts( array( 'name' => 'preview-guide-1', 'post_type' => 'post', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	foreach ( array( $pen, $alternative ) as $index => $item ) {
		$attrs = array();
		foreach ( array( 'نوع دستگاه (نمونه)' => array( 'پن بی‌سیم', 'پن روتاری' ), 'استروک (نمونه، میلی‌متر)' => array( '۳.۵', '۴.۰' ), 'وزن (نمونه، گرم)' => array( '۱۸۰', '۱۹۵' ) ) as $label => $values ) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_name( $label );
			$attribute->set_options( array( $values[ $index ] ) );
			$attribute->set_visible( true );
			$attrs[] = $attribute;
		}
		$item->set_attributes( $attrs );
		$item->set_description( '<h2>معرفی محصول</h2><p>این محصول، مشخصات و قیمت آن صرفاً برای بررسی طراحی صفحه آزمایشی هستند و به مدل واقعی اشاره ندارند.</p><p>در نسخه نهایی، توضیح کاربرد، ویژگی‌های واقعی، محتویات بسته و محدودیت‌های محصول در این بخش ثبت می‌شود.</p>' );
		$item->update_meta_data( '_wpmain_use_cases', 'نمونه چیدمان کاربردها: توضیح وظایف مناسب برای دستگاه و تفاوت آن با گزینه‌های دیگر. اطلاعات واقعی باید توسط فروشگاه تأیید شود.' );
		$item->update_meta_data( '_wpmain_audience', 'نمونه معرفی مخاطب: چه کسی با توجه به تجربه، نیاز و ابزار فعلی خود می‌تواند این محصول را انتخاب کند؟ این متن راهنمای استفاده واقعی نیست.' );
		$item->update_meta_data( '_wpmain_comparison', array( $index ? $pen->get_id() : $alternative->get_id() ) );
		$item->update_meta_data( '_wpmain_alternatives', array( $index ? $pen->get_id() : $alternative->get_id() ) );
		$item->update_meta_data( '_wpmain_compatible', array( $needle->get_id(), $magnum->get_id() ) );
		$item->update_meta_data( '_wpmain_guides', $guides );
		$item->update_meta_data( '_wpmain_faq', array(
			array( 'question' => 'این مشخصات واقعی هستند؟', 'answer' => 'خیر. این‌ها داده‌های فرضی برای بررسی صفحه هستند. در فروشگاه واقعی فقط مشخصات تأییدشده منتشر می‌شوند.' ),
			array( 'question' => 'چطور ابزار سازگار را انتخاب کنم؟', 'answer' => 'در این پیش‌نمایش، ارتباط محصولات فقط نمونه نمایشی است. در فروشگاه واقعی، سیستم اتصال و مشخصات دقیق محصول باید بررسی و تأیید شوند.' ),
		) );
		$item->update_meta_data( '_wpmain_search_aliases', $index ? "روتاری\nrotary pen" : "مست\nMast\nwireless pen\nپن بی سیم" );
		$item->save();
	}
	$needle->update_meta_data( '_wpmain_guides', $guides );
	$needle->update_meta_data( '_wpmain_use_cases', 'نمونه بخش کاربردهای کارتریج؛ توضیحات واقعی پس از بررسی مشخصات محصول اضافه می‌شوند.' );
	$needle->save();
	update_option( 'wpmain_product_preview_version', '0.6.0' );
}
if ( did_action( 'init' ) ) { wpmain_preview_product_details(); } else { add_action( 'init', 'wpmain_preview_product_details', 40 ); }
