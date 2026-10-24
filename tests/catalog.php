<?php
/** Focused catalog data/save-contract checks; WordPress integration is tested in the demo. */
define( 'ABSPATH', __DIR__ );
function add_filter( ...$args ) {}
function add_action( ...$args ) {}
function absint( $value ) { return abs( (int) $value ); }
function get_post_type( $id ) { return array( 1 => 'product', 2 => 'product', 3 => 'product', 4 => 'product', 5 => 'post', 6 => 'page' )[ $id ] ?? false; }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['metadata'][ $key ] ?? ''; }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function current_user_can( ...$args ) { return $GLOBALS['allowed'] ?? false; }
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return strip_tags( $value ); }
function sanitize_textarea_field( $value ) { return strip_tags( $value ); }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../plugins/wp-store-catalog/wp-store-catalog.php';
check( array( 2, 3 ) === wpmain_catalog_ids( array( 1, 2, 2, 5, 3, 4 ), 1, 'product', 2 ), 'Relationships exclude self, duplicates and wrong types and enforce the limit.' );
check( array( 5 ) === wpmain_catalog_ids( array( 2, 5, 6 ), 0, 'post' ), 'Guides must reference posts.' );
$GLOBALS['metadata'] = array( '_wpmain_faq' => array( array( 'question' => 'Valid?', 'answer' => 'Yes' ), array( 'question' => array(), 'answer' => 'Malformed' ), array( 'question' => 'Incomplete', 'answer' => '' ) ), '_wpmain_use_cases' => array( 'malformed' ) );
check( 1 === count( wpmain_catalog_guidance( 1 )['faq'] ), 'Malformed/incomplete FAQs are omitted.' );
check( '' === wpmain_catalog_guidance( 1 )['use_cases'], 'Malformed copy is omitted.' );
$product = new class {
	public $values = array();
	public function get_id() { return 1; }
	public function update_meta_data( $key, $value ) { $this->values[ $key ] = $value; }
};
$_POST = array( 'wpmain_guidance_nonce' => 'invalid', '_wpmain_use_cases' => 'Do not save' );
$GLOBALS['allowed'] = true;
wpmain_catalog_save( $product ); check( array() === $product->values, 'Invalid nonce cannot change metadata.' );
$_POST['wpmain_guidance_nonce'] = 'valid'; $GLOBALS['allowed'] = false;
wpmain_catalog_save( $product ); check( array() === $product->values, 'User without edit permission cannot change metadata.' );
$GLOBALS['allowed'] = true;
$_POST['_wpmain_compatible'] = array( 1, 2, 2, 5 );
$_POST['wpmain_faq_question'] = array( 'Question', 'Incomplete' );
$_POST['wpmain_faq_answer'] = array( 'Answer', '' );
wpmain_catalog_save( $product );
check( array( 2 ) === $product->values['_wpmain_compatible'], 'Authorized save still validates relationship IDs.' );
check( 1 === count( $product->values['_wpmain_faq'] ), 'Authorized save keeps only complete FAQ pairs.' );
echo "PASS: relationship constraints, malformed data, nonce/capability checks and save contract.\n";
