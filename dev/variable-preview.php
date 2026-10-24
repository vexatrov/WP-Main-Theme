<?php
/** Disposable sample variations to review range prices and option selection. */
require_once '/wordpress/wp-load.php';
function wpmain_preview_variations() {
	if ( 'ابزار تاتو / پیش‌نمایش' !== get_option( 'blogname' ) || get_option( 'wpmain_variable_preview' ) ) { return; }
	$id = wc_get_product_id_by_sku( 'PREVIEW-3' );
	if ( ! $id ) { return; }
	wp_set_object_terms( $id, 'variable', 'product_type' );
	$product = new WC_Product_Variable( $id );
	$attribute = new WC_Product_Attribute();
	$attribute->set_name( 'حجم' );
	$attribute->set_options( array( '۳۰ میلی‌لیتر', '۶۰ میلی‌لیتر' ) );
	$attribute->set_visible( true );
	$attribute->set_variation( true );
	$product->set_attributes( array( $attribute ) );
	$product->save();
	foreach ( array( '۳۰ میلی‌لیتر' => '300000', '۶۰ میلی‌لیتر' => '600000' ) as $size => $price ) {
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $id );
		$variation->set_attributes( array( sanitize_title( 'حجم' ) => $size ) );
		$variation->set_regular_price( $price );
		$variation->set_status( 'publish' );
		$variation->set_stock_status( 'instock' );
		$variation->save();
	}
	WC_Product_Variable::sync( $id );
	wc_delete_product_transients( $id );
	update_option( 'wpmain_variable_preview', '0.7.0' );
}
if ( did_action( 'init' ) ) { wpmain_preview_variations(); } else { add_action( 'init', 'wpmain_preview_variations', 45 ); }
