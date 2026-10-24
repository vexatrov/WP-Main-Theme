<?php
/** Disposable local sample catalog. Never deploy this file to a real shop. */
require '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

update_option( 'blogname', 'ابزار تاتو / پیش‌نمایش' );
update_option( 'blogdescription', 'فروشگاه آزمایشی تجهیزات تاتو بدن' );
update_option( 'blog_public', 0 );
update_option( 'woocommerce_currency', 'IRR' );
update_option( 'woocommerce_price_num_decimals', 0 );
update_option( 'woocommerce_default_country', 'IR' );
update_option( 'woocommerce_store_address', '' );
update_option( 'woocommerce_allow_tracking', 'no' );
update_option( 'woocommerce_onboarding_profile', array( 'completed' => true ) );
update_option( 'woocommerce_enable_guest_checkout', 'yes' );
update_option( 'woocommerce_enable_reviews', 'no' );
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'پرداخت آزمایشی هنگام تحویل', 'description' => 'فقط برای بررسی مسیر خرید؛ سفارش واقعی ثبت نکنید.' ) );
WC_Install::create_pages();

$home = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'خانه', 'post_content' => '' ) );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
set_theme_mod( 'wpmain_hero_eyebrow', 'برای هنر روی پوست' );
set_theme_mod( 'wpmain_hero_title', 'ابزار دقیق. آزادی در خلق.' );
set_theme_mod( 'wpmain_hero_description', 'از دستگاه و کارتریج تا رنگ و لوازم جانبی؛ همه‌چیز برای تاتو بدن، در یک فروشگاه تخصصی.' );
set_theme_mod( 'wpmain_hero_button', 'دیدن همه محصولات' );

// Simple original vector diagrams: sample artwork, not photographs of real products.
function wpmain_demo_image( $name, $type, $background = '#efeee9' ) {
	$shapes = array(
		'pen' => '<g transform="translate(310 80) rotate(24 90 240)"><rect x="44" y="20" width="92" height="310" rx="38" fill="#252a27"/><rect x="50" y="45" width="80" height="130" rx="30" fill="#424a44"/><path d="M52 210h76M52 230h76M52 250h76M52 270h76" stroke="#737d75" stroke-width="5"/><path d="M57 330h66l-18 82H75z" fill="#979f97"/><path d="M88 412h4v58h-4z" fill="#252a27"/><circle cx="90" cy="94" r="19" fill="#d36b36"/></g>',
		'ink' => '<path d="M335 210h130l15 230q0 30-30 30h-100q-30 0-30-30z" fill="#242b28"/><rect x="342" y="150" width="116" height="70" rx="8" fill="#363f38"/><path d="M366 150l15-70h38l15 70" fill="#252b27"/><rect x="325" y="278" width="150" height="122" rx="4" fill="#e2cfaa"/><circle cx="400" cy="339" r="30" fill="#d36b36"/>',
		'cartridge' => '<g transform="translate(230 100) rotate(-20 160 200)"><rect x="95" y="80" width="130" height="220" rx="25" fill="#d3d8ce" stroke="#788678" stroke-width="4"/><rect x="108" y="95" width="104" height="70" rx="14" fill="#d36b36"/><path d="M118 300h84l-30 95h-24z" fill="#a6b3a2"/><path d="M160 395v65" stroke="#363e36" stroke-width="5"/><path d="M138 190h44M138 215h44M138 240h44" stroke="#788678" stroke-width="5"/></g>',
		'accessory' => '<rect x="235" y="210" width="330" height="230" rx="35" fill="#3a443c"/><rect x="265" y="240" width="270" height="170" rx="20" fill="#aebbaa"/><circle cx="400" cy="325" r="58" fill="#e9e9de"/><path d="M380 325h40M400 305v40" stroke="#d36b36" stroke-width="10"/>',
	);
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600"><rect width="800" height="600" fill="' . $background . '"/><ellipse cx="400" cy="500" rx="180" ry="18" fill="#000" opacity=".06"/>' . $shapes[ $type ] . '</svg>';
	$upload = wp_upload_bits( $name . '.svg', null, $svg );
	if ( $upload['error'] ) { return 0; }
	$id = wp_insert_attachment( array( 'post_title' => $name, 'post_mime_type' => 'image/svg+xml', 'post_status' => 'inherit', 'guid' => $upload['url'] ), $upload['file'] );
	wp_update_attachment_metadata( $id, array( 'width' => 800, 'height' => 600, 'file' => _wp_relative_upload_path( $upload['file'] ) ) );
	update_post_meta( $id, '_wp_attachment_image_alt', 'تصویر شماتیک آزمایشی محصول' );
	return $id;
}
// These are generated, trusted SVGs inserted internally, not user SVG uploads.
add_filter( 'upload_mimes', function ( $mimes ) { $mimes['svg'] = 'image/svg+xml'; return $mimes; } );
$images = array();
foreach ( array( 'pen', 'ink', 'cartridge', 'accessory' ) as $type ) {
	$images[ $type ] = wpmain_demo_image( 'demo-' . $type, $type );
}
set_theme_mod( 'wpmain_hero_image', $images['pen'] );
$categories = array();
foreach ( array( 'machines' => array( 'دستگاه تاتو', 'pen' ), 'cartridges' => array( 'کارتریج و سوزن', 'cartridge' ), 'inks' => array( 'رنگ تاتو', 'ink' ), 'accessories' => array( 'لوازم جانبی', 'accessory' ) ) as $slug => $data ) {
	$term = wp_insert_term( $data[0], 'product_cat', array( 'slug' => $slug ) );
	if ( ! is_wp_error( $term ) ) {
		$categories[ $slug ] = $term['term_id'];
		update_term_meta( $term['term_id'], 'thumbnail_id', $images[ $data[1] ] );
	}
}
$samples = array(
	array( 'پن تاتو بی‌سیم — نمونه', 'machines', 'pen', 12500000 ),
	array( 'کارتریج راند لاینر — نمونه', 'cartridges', 'cartridge', 850000 ),
	array( 'رنگ مشکی تاتو — نمونه', 'inks', 'ink', 2400000 ),
	array( 'پد تمرین تاتو — نمونه', 'accessories', 'accessory', 650000 ),
	array( 'پن تاتو روتاری — نمونه', 'machines', 'pen', 9600000 ),
	array( 'کارتریج مگنوم — نمونه', 'cartridges', 'cartridge', 950000 ),
	array( 'رنگ میکس تاتو — نمونه', 'inks', 'ink', 2100000 ),
	array( 'لوازم محافظتی — نمونه', 'accessories', 'accessory', 450000 ),
);
foreach ( $samples as $index => $sample ) {
	$product = new WC_Product_Simple();
	$product->set_name( $sample[0] );
	$product->set_status( 'publish' );
	$product->set_regular_price( $sample[3] );
	$product->set_sku( 'PREVIEW-' . ( $index + 1 ) );
	$product->set_short_description( 'محصول و قیمت صرفاً آزمایشی هستند. این پیش‌نمایش برای بررسی ظاهر و مسیر خرید ساخته شده است.' );
	$product->set_description( '<h2>درباره این نمونه</h2><p>اطلاعات واقعی محصول، مشخصات فنی و تصاویر برند در مرحله بعد جایگزین می‌شوند.</p>' );
	$product->set_category_ids( array( $categories[ $sample[1] ] ) );
	$product->set_image_id( $images[ $sample[2] ] );
	$product->set_stock_status( 'instock' );
	$product->save();
}
$menu = wp_create_nav_menu( 'ناوبری اصلی' );
foreach ( array( 'فروشگاه' => wc_get_page_permalink( 'shop' ), 'دستگاه تاتو' => get_term_link( $categories['machines'] ), 'کارتریج و سوزن' => get_term_link( $categories['cartridges'] ), 'رنگ تاتو' => get_term_link( $categories['inks'] ) ) as $label => $url ) {
	wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $label, 'menu-item-url' => $url, 'menu-item-status' => 'publish' ) );
}
set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu ) );
// Local preview marker only; no production banner or demo behavior in the theme.
wp_mkdir_p( WPMU_PLUGIN_DIR );
file_put_contents( WPMU_PLUGIN_DIR . '/preview-notice.php', '<?php add_action("wp_body_open", function(){ echo \'<div class="preview-notice" role="note">پیش‌نمایش توسعه · محصولات، تصاویر و قیمت‌ها آزمایشی هستند · فروشگاه واقعی نیست</div>\'; });' );
echo 'Sample body-tattoo storefront created.';
