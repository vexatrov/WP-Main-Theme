<?php
/** Admin search/save contracts: capability boundaries, bounded public queries and safe partial saves. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['hooks'] = array(); $GLOBALS['allowed'] = array(); $GLOBALS['saved'] = array();
function add_action( $name, $callback, ...$args ) { $GLOBALS['hooks'][ $name ] = $callback; }
function current_user_can( $cap, ...$args ) { return in_array( $cap, $GLOBALS['allowed'], true ); }
function check_ajax_referer( ...$args ) { if ( empty( $GLOBALS['nonce'] ) ) { throw new RuntimeException( 'nonce' ); } }
function sanitize_key( $value ) { return preg_replace( '/[^a-z_]/', '', $value ); }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_unslash( $value ) { return $value; }
class Editor_Response extends RuntimeException { public $data; public $status; public function __construct( $data, $status = 200 ) { $this->data = $data; $this->status = $status; } }
function wp_send_json_success( $data ) { throw new Editor_Response( $data ); }
function wp_send_json_error( $data, $status ) { throw new Editor_Response( $data, $status ); }
function get_posts( $args ) { $GLOBALS['query'] = $args; return array( (object) array( 'ID' => 1 ) ); }
function get_post( $id ) { return 1 === $id ? (object) array( 'ID' => 1, 'post_type' => 'product' ) : null; }
function get_the_title( $id ) { return 'Public &amp; correct title'; }
function wc_get_product( $id ) { return new class { public function is_visible() { return $GLOBALS['visible'] ?? true; } }; }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function wp_is_post_revision( $id ) { return false; }
function wp_is_post_autosave( $id ) { return $GLOBALS['autosave'] ?? false; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['saved'][ $id ][ $key ] = $value; }
function wpmain_catalog_ids( $raw, $self, $type, $limit ) { $GLOBALS['id_args'] = array( $type, $limit ); return array_slice( array_values( array_unique( array_filter( array_map( 'intval', explode( ',', $raw ) ) ) ) ), 0, $limit ); }
function check( $value, $message ) { if ( ! $value ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../plugins/wp-store-catalog/editor-controls.php';
$search = $GLOBALS['hooks']['wp_ajax_wpmain_editor_search'];
$_GET = array( 'kind' => 'product', 'term' => 'pen' );
try { $search(); throw new RuntimeException( 'missing rejection' ); } catch ( RuntimeException $error ) { check( 'nonce' === $error->getMessage(), 'Invalid nonce is rejected' ); }
$GLOBALS['nonce'] = true;
try { $search(); } catch ( Editor_Response $response ) { check( 403 === $response->status, 'Visitors cannot search admin catalog' ); }
$GLOBALS['allowed'] = array( 'edit_products', 'read_post' );
try { $search(); } catch ( Editor_Response $response ) { check( 'Public & correct title' === $response->data[0]['title'], 'Results are plain text, not encoded HTML' ); }
check( 20 === $GLOBALS['query']['posts_per_page'] && 'publish' === $GLOBALS['query']['post_status'] && false === $GLOBALS['query']['has_password'], 'Search is bounded to public unprotected content' );
$GLOBALS['visible'] = false;
try { $search(); } catch ( Editor_Response $response ) { check( array() === $response->data, 'Hidden products are excluded' ); }
$_GET['kind'] = 'user';
try { $search(); } catch ( Editor_Response $response ) { check( 403 === $response->status, 'Unknown object kinds cannot expand permissions' ); }
$_GET = array( 'kind' => 'product', 'term' => 'p' );
try { $search(); } catch ( Editor_Response $response ) { check( array() === $response->data, 'One-character searches do not scan catalog' ); }
$save = $GLOBALS['hooks']['save_post_post'];
$_POST = array( 'wpmain_article_products_nonce' => 'valid', 'wpmain_article_products' => '1,2,3,4,5' );
$save( 9 ); check( array() === $GLOBALS['saved'], 'Editing products does not grant editing an article' );
$GLOBALS['allowed'][] = 'edit_post'; $save( 9 );
check( array( 1, 2, 3, 4 ) === $GLOBALS['saved'][9]['_wpmain_article_products'] && array( 'product', 4 ) === $GLOBALS['id_args'], 'Ordered article selections use bounded native product validation' );
unset( $_POST['wpmain_article_products'] ); $save( 9 );
check( 4 === count( $GLOBALS['saved'][9]['_wpmain_article_products'] ), 'Partial saves preserve relationships' );
$_POST['wpmain_article_products'] = array( 'bad' ); $save( 9 );
check( 4 === count( $GLOBALS['saved'][9]['_wpmain_article_products'] ), 'Malformed selections preserve relationships' );
$_POST['wpmain_article_products'] = ''; $GLOBALS['autosave'] = true; $save( 9 );
check( 4 === count( $GLOBALS['saved'][9]['_wpmain_article_products'] ), 'Autosaves cannot erase manual recommendations' );
$GLOBALS['autosave'] = false; $save( 9 );
check( array() === $GLOBALS['saved'][9]['_wpmain_article_products'], 'Explicit empty selection removes recommendations' );
echo "PASS: admin search nonce/capability boundaries, bounded published queries, hidden products, article selection limits and partial/autosave preservation.\n";
