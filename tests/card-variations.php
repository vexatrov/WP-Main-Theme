<?php
/** Native variation picker boundaries, template contract and global restoration. */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function __( $message, $domain ) { return $message; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function esc_html__( $value, $domain ) { return esc_html( $value ); }
function absint( $value ) { return abs( (int) $value ); }
function wc_get_product( $id ) { return 1 === $id ? $GLOBALS['selected'] : false; }
function get_post( $id ) { return (object) array( 'ID' => $id ); }
function nocache_headers() { $GLOBALS['no_cache'] = true; }
function wp_send_json_error( $value, $status ) { throw new RuntimeException( 'json:' . $status ); }
function wp_send_json_success( $value ) { $GLOBALS['response'] = $value; throw new RuntimeException( 'json:200' ); }
function wc_get_template( $path, $args ) {
	$GLOBALS['template'] = array( $path, $args, $GLOBALS['product'], $GLOBALS['post'] );
	if ( ! empty( $GLOBALS['template_error'] ) ) { throw new RuntimeException( 'template-error' ); }
	echo '<form class="variations_form cart"></form>';
}
function wp_enqueue_script( ...$args ) { $GLOBALS['scripts'][] = $args; }
function wp_enqueue_style( ...$args ) { $GLOBALS['styles'][] = $args; }
function wp_localize_script( ...$args ) { $GLOBALS['config'] = $args; }
function get_template_directory_uri() { return 'https://shop.invalid/theme'; }
function wpmain_asset_version( $file ) { return 'test'; }
function admin_url( $file ) { return 'https://shop.invalid/wp-admin/' . $file; }
function wp_unique_id( $prefix ) { static $id = 0; return $prefix . ++$id; }
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require __DIR__ . '/../themes/wp-main-theme/inc/woocommerce.php';
$selected = new class {
	public $status = 'publish'; public $password = ''; public $visible = true; public $visibility = 'visible'; public $type = 'variable'; public $stock = true; public $price = '100';
	public function is_type( $type ) { return $this->type === $type; }
	public function get_status() { return $this->status; }
	public function get_post_password() { return $this->password; }
	public function get_catalog_visibility() { return $this->visibility; }
	public function is_visible() { return $this->visible; }
	public function get_id() { return 1; }
	public function get_name() { return 'Real variable product'; }
	public function get_variation_attributes() { return array( 'size' => array( 'small', 'large' ) ); }
	public function get_default_attributes() { return array( 'size' => 'small' ); }
	public function is_in_stock() { return $this->stock; }
	public function is_on_backorder( $qty ) { return false; }
	public function get_price() { return $this->price; }
};
check( $selected === wpmain_card_variation_product( 1 ), 'Public visible variable product can be read.' );
check( false === wpmain_card_variation_product( 2 ), 'Missing product is rejected.' );
foreach ( array( 'status' => 'draft', 'password' => 'protected', 'visibility' => 'hidden', 'visible' => false, 'type' => 'simple' ) as $key => $value ) {
	$previous = $selected->$key; $selected->$key = $value;
	check( false === wpmain_card_variation_product( 1 ), $key . ' boundary rejects private/inapplicable forms.' );
	$selected->$key = $previous;
}
$product = (object) array( 'before' => 'product' ); $post = (object) array( 'before' => 'post' );
$old_product = $product; $old_post = $post;
$html = wpmain_card_variation_form( $selected );
check( str_contains( $html, 'variations_form' ), 'Native form markup is returned.' );
check( false === $GLOBALS['template'][1]['available_variations'], 'Variations are lazily matched by native Woo AJAX, never embedded in catalog HTML.' );
check( $selected === $GLOBALS['template'][2] && 1 === $GLOBALS['template'][3]->ID, 'Native template receives the selected product/post context.' );
check( $product === $old_product && $post === $old_post, 'Loop globals are restored.' );
$GLOBALS['template_error'] = true;
try { wpmain_card_variation_form( $selected ); } catch ( RuntimeException $error ) { check( 'template-error' === $error->getMessage(), 'Template exception propagates.' ); }
check( $product === $old_product && $post === $old_post, 'Globals are restored after template failure too.' );
$GLOBALS['template_error'] = false;
$_GET = array( 'product_id' => array( '1' ) );
try { wpmain_card_variation_request(); } catch ( RuntimeException $error ) { check( 'json:404' === $error->getMessage(), 'Malformed product ID returns not found.' ); }
$_GET = array( 'product_id' => '1' );
try { wpmain_card_variation_request(); } catch ( RuntimeException $error ) { check( 'json:200' === $error->getMessage(), 'Valid public read succeeds.' ); }
check( ! empty( $GLOBALS['no_cache'] ) && 'Real variable product' === $GLOBALS['response']['name'], 'Read response is uncached and uses real product data.' );
$link = '<a href="https://shop.invalid/product/real/" class="button product_type_variable">Choose</a>';
$enhanced = wpmain_card_variation_link( $link, $selected );
check( str_contains( $enhanced, 'data-card-variations="1"' ) && str_contains( $enhanced, 'aria-haspopup="dialog"' ) && str_contains( $enhanced, 'href="https://shop.invalid/product/real/"' ), 'Dialog enhancement keeps a real no-JS product route.' );
check( in_array( 'wc-add-to-cart-variation', $GLOBALS['scripts'][0][2], true ), 'Native variation dependency is enqueued only for an eligible rendered card.' );
$selected->type = 'simple'; check( $link === wpmain_card_variation_link( $link, $selected ), 'Simple card stays native.' );
$product = $selected; $product->stock = false;
ob_start(); wpmain_card_image_start(); wpmain_card_image_end(); $badge = ob_get_clean();
check( str_contains( $badge, 'card-image is-unavailable' ) && str_contains( $badge, 'Out of stock' ), 'Unavailable badge belongs to image wrapper.' );
$ids = array();
for ( $rail = 0; $rail < 3; $rail++ ) {
	$args = wpmain_card_action_args( array( 'aria-describedby_text' => 'Options for <ink>', 'attributes' => array( 'aria-describedby' => 'shared-context' ) ) );
	check( ! isset( $args['aria-describedby_text'] ), 'Native product-ID help span is suppressed for each repeated card.' );
	$id = $args['_wpmain_help_id']; $ids[] = $id;
	check( 'shared-context ' . $id === $args['attributes']['aria-describedby'], 'Existing contextual help is preserved alongside the unique description.' );
	$help = wpmain_card_action_help( '<a>Choose</a>', $selected, $args );
	check( str_contains( $help, 'id="' . $id . '"' ) && str_contains( $help, 'Options for &lt;ink&gt;' ), 'Each link receives its own escaped screen-reader help.' );
}
check( 3 === count( array_unique( $ids ) ), 'Same product repeated across three rails has three distinct help IDs.' );
$empty = wpmain_card_action_args( array( 'aria-describedby_text' => '', 'attributes' => array() ) );
check( ! isset( $empty['aria-describedby_text'] ) && ! isset( $empty['_wpmain_help_id'] ), 'Empty native help emits neither a product-ID duplicate nor an unnecessary span.' );
$product->stock = true;
ob_start(); wpmain_card_image_start(); wpmain_card_image_end(); $badge = ob_get_clean();
check( ! str_contains( $badge, 'card-stock' ), 'Available products do not display a stock badge.' );
echo "PASS: public-product boundaries, native lazy variation template, restored globals, uncached endpoint, fallback link/dependencies, image stock badge and unique repeated-card contextual help.\n";
