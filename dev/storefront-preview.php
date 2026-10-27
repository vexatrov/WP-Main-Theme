<?php
/** Fictional artwork and owner-requested sample messages. Never deploy fixtures. */
require_once '/wordpress/wp-load.php';
function wpmain_storefront_preview() {
	if ( 'ابزار تاتو / پیش‌نمایش' !== get_option( 'blogname' ) || get_option( 'wpmain_storefront_preview' ) || ! function_exists( 'wc_get_product' ) ) { return; }
	set_theme_mod( 'wpmain_product_reassurance', "قیمت‌ها به‌روز هستند\nارسال یک‌روزه تهران، دو‌روزه سایر شهرها\nضمانت اصالت کالا — ۱۰۰٪ اورجینال\nگارانتی و پشتیبانی" );
	// Representative sample artwork, not an actual brand's logo.
	foreach ( array( 1, 2 ) as $sku ) {
		$product = wc_get_product( wc_get_product_id_by_sku( 'PREVIEW-' . $sku ) );
		if ( ! $product || ! $product->get_image_id() ) { continue; }
		$terms = get_the_terms( $product->get_id(), 'product_brand' );
		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			if ( ! get_term_meta( $term->term_id, 'thumbnail_id', true ) ) { update_term_meta( $term->term_id, 'thumbnail_id', $product->get_image_id() ); }
		}
	}
	update_option( 'wpmain_storefront_preview', '1', false );
}
if ( did_action( 'init' ) ) { wpmain_storefront_preview(); } else { add_action( 'init', 'wpmain_storefront_preview', 70 ); }
