<?php
/** Override writes must preserve partial edits, reject malformed media and require authorization. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['hooks'] = array();
function add_action( ...$args ) { $GLOBALS['hooks'][ $args[0] ][] = $args[1]; }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce; }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function wp_unslash( $value ) { return stripslashes( $value ); }
function wp_html_excerpt( $value, $limit, $suffix ) { return mb_substr( $value, 0, $limit ); }
function absint( $value ) { return abs( (int) $value ); }
function wp_attachment_is_image( $id ) { return 10 === $id; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['saved'][ $id ][ $key ] = $value; }
function update_term_meta( $id, $key, $value ) { update_post_meta( $id, $key, $value ); }
function wp_is_post_revision( $id ) { return false; }
function wp_is_post_autosave( $id ) { return $GLOBALS['autosave'] ?? false; }
function current_user_can( ...$args ) { return $GLOBALS['allowed'] ?? false; }
function get_taxonomy( $name ) { return (object) array(); }
function check( $value, $message ) { if ( ! $value ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../plugins/wp-store-catalog/seo-fields.php';
$save = $GLOBALS['hooks']['save_post'][0];
$post = (object) array( 'post_type' => 'product' );
$_POST = array( 'wpmain_seo_nonce' => 'invalid', 'wpmain_seo_title' => '<b>Title</b>' );
$GLOBALS['allowed'] = true; $save( 1, $post );
check( empty( $GLOBALS['saved'] ), 'Invalid nonce blocks writes' );
$_POST['wpmain_seo_nonce'] = 'valid'; $GLOBALS['allowed'] = false; $save( 1, $post );
check( empty( $GLOBALS['saved'] ), 'Missing capability blocks writes' );
$GLOBALS['allowed'] = true; $GLOBALS['autosave'] = true; $save( 1, $post );
check( empty( $GLOBALS['saved'] ), 'Autosave cannot erase manual overrides' );
$GLOBALS['autosave'] = false; $save( 1, $post );
check( 'Title' === $GLOBALS['saved'][1]['_wpmain_seo_title'], 'Title is sanitized' );
$_POST = array( 'wpmain_seo_nonce' => 'valid', 'wpmain_seo_title' => array( 'bad' ), 'wpmain_seo_image' => '999', 'wpmain_seo_description' => '<b>Description</b>' );
$save( 1, $post );
check( 'Title' === $GLOBALS['saved'][1]['_wpmain_seo_title'] && ! isset( $GLOBALS['saved'][1]['_wpmain_seo_image'] ), 'Malformed title and non-image attachment preserve saved values' );
check( 'Description' === $GLOBALS['saved'][1]['_wpmain_seo_description'], 'Description is sanitized' );
$_POST = array( 'wpmain_seo_nonce' => 'valid', 'wpmain_seo_image' => '10' );
$save( 1, $post ); check( 10 === $GLOBALS['saved'][1]['_wpmain_seo_image'], 'Real library image is accepted' );
$_POST['wpmain_seo_image'] = '';
$save( 1, $post ); check( 0 === $GLOBALS['saved'][1]['_wpmain_seo_image'], 'Explicit blank restores automatic image' );
$_POST['wpmain_seo_title'] = 'Term title';
$GLOBALS['hooks']['edited_product_cat'][0]( 7 );
check( 'Term title' === $GLOBALS['saved'][7]['_wpmain_seo_title'], 'Taxonomy edits use the same durable fields' );
echo "PASS: native SEO editor save authorization, autosave, sanitization, partial preservation and image validation.\n";
