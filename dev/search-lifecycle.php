<?php
/** Booted integration checks, only inside the fresh CI demo database. */
require_once '/wordpress/wp-load.php';
if ( ! defined( 'WPMAIN_ISOLATED_CI' ) || ! WPMAIN_ISOLATED_CI || 'ابزار تاتو / پیش‌نمایش' !== get_option( 'blogname' ) ) {
	throw new RuntimeException( 'Refusing search lifecycle fixtures outside the isolated CI demo.' );
}
function wpmain_lifecycle_check( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
wpmain_lifecycle_check( wp_mail( 'ci-mail@example.invalid', 'CI capture guard', 'Fictional integration mail only.' ), 'CI mail capture must accept test mail.' );
$captured = get_option( 'wpmain_qa_last_mail' );
wpmain_lifecycle_check( is_array( $captured ) && 'CI capture guard' === $captured['subject'], 'CI mail must be captured before checkout/recovery suites run.' );
$products = array(); $terms = array();
try {
	$parent = new WC_Product_Variable();
	$parent->set_name( 'QA isolated lifecycle parent' ); $parent->set_status( 'publish' ); $parent->save(); $products[] = $parent->get_id();
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $parent->get_id() ); $variation->set_status( 'publish' ); $variation->set_regular_price( '100' ); $variation->set_sku( 'CI-LIFECYCLE-123' ); $variation->save(); $products[] = $variation->get_id();
	$document = fn() => get_post_meta( $parent->get_id(), '_wpmain_search_document', true );
	wpmain_lifecycle_check( str_contains( $document(), 'cilifecycle123' ), 'New variation SKU must reach the parent document.' );
	$variation->set_sku( 'CI-LIFECYCLE-456' ); $variation->save();
	wpmain_lifecycle_check( str_contains( $document(), 'cilifecycle456' ) && ! str_contains( $document(), 'cilifecycle123' ), 'Updated variation SKU must replace its old match.' );
	wp_update_post( array( 'ID' => $variation->get_id(), 'post_status' => 'private' ) );
	wpmain_lifecycle_check( ! str_contains( $document(), 'cilifecycle456' ), 'Nonpublic variation must leave parent search document.' );
	wp_update_post( array( 'ID' => $variation->get_id(), 'post_status' => 'publish' ) );
	wpmain_lifecycle_check( str_contains( $document(), 'cilifecycle456' ), 'Published variation must return to parent search document.' );
	wp_trash_post( $variation->get_id() );
	wpmain_lifecycle_check( ! str_contains( $document(), 'cilifecycle456' ), 'Trashed variation must leave parent search document.' );
	wp_untrash_post( $variation->get_id() ); wp_update_post( array( 'ID' => $variation->get_id(), 'post_status' => 'publish' ) );
	wpmain_lifecycle_check( str_contains( $document(), 'cilifecycle456' ), 'Restored published variation must return.' );
	wp_delete_post( $variation->get_id(), true );
	wpmain_lifecycle_check( ! str_contains( $document(), 'cilifecycle456' ), 'Permanently deleted variation must leave parent search document.' );
	wp_update_post( array( 'ID' => $parent->get_id(), 'post_title' => 'WP import updated title' ) );
	wpmain_lifecycle_check( str_contains( $document(), 'wpimportupdatedtitle' ), 'Native WordPress/import title updates must refresh document.' );
	update_post_meta( $parent->get_id(), '_wpmain_search_aliases', 'واردات CI-ALIAS-987' );
	wpmain_lifecycle_check( str_contains( $document(), 'cialias987' ), 'Metadata imports must refresh aliases after post insertion.' );
	// More than 100 populated categories prove matching is not a first-page scan.
	for ( $i = 0; $i < 105; $i++ ) {
		$term = wp_insert_term( sprintf( 'A CI padding %03d', $i ), 'product_cat' );
		wpmain_lifecycle_check( ! is_wp_error( $term ), 'Category fixture insertion failed.' ); $terms[] = (int) $term['term_id'];
	}
	$last = wp_insert_term( 'ZZ CI beyond first hundred', 'product_cat' ); $terms[] = (int) $last['term_id'];
	wp_set_object_terms( $parent->get_id(), $terms, 'product_cat' );
	$matches = wpmain_catalog_matching_terms( 'product_cat', 'zzcibeyondfirsthundred' );
	wpmain_lifecycle_check( 1 === count( $matches ) && (int) $last['term_id'] === $matches[0]->term_id, 'Term beyond first 100 must be suggested.' );
	wp_update_term( $last['term_id'], 'product_cat', array( 'name' => 'ZZ renamed CI term' ) );
	wpmain_lifecycle_check( ! wpmain_catalog_matching_terms( 'product_cat', 'zzcibeyondfirsthundred' ) && 1 === count( wpmain_catalog_matching_terms( 'product_cat', 'zzrenamedciterm' ) ), 'Renamed terms must replace stale suggestion matches.' );
	wpmain_lifecycle_check( 'راهنمای محصول' === __( 'Product guidance', 'wp-store-catalog' ), 'Bundled Persian plugin translation must load in WordPress.' );
	$indexed = get_post_meta( $parent->get_id(), '_wpmain_search_document', true );
	delete_post_meta( $parent->get_id(), '_wpmain_search_document' );
	wpmain_catalog_queue_reindex();
	for ( $batch = 0; $batch < 20 && '2' !== get_option( 'wpmain_search_index_version' ); $batch++ ) { wpmain_catalog_reindex_batch(); }
	wpmain_lifecycle_check( '2' === get_option( 'wpmain_search_index_version' ) && $indexed === get_post_meta( $parent->get_id(), '_wpmain_search_document', true ), 'Bounded rebuild must recover missing product and term documents.' );
	file_put_contents( WP_CONTENT_DIR . '/wpmain-lifecycle-passed.txt', 'PASS: guarded lifecycle assertions completed.' );
	echo "PASS: booted WooCommerce variation save/private/trash/restore/delete, native import/meta updates, >100-term matching/rename, rebuild recovery and Persian plugin loading.\n";
} finally {
	foreach ( array_reverse( $products ) as $id ) { if ( get_post( $id ) ) { wp_delete_post( $id, true ); } }
	foreach ( $terms as $id ) { wp_delete_term( $id, 'product_cat' ); }
}
