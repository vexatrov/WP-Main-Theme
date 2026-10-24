<?php
defined( 'ABSPATH' ) || exit;
// Delegate to WooCommerce's shortcode to preserve loop/global state and hooks.
$ids = implode( ',', array_map( function ( $item ) { return $item->get_id(); }, $args['products'] ) );
?>
<section class="product-resources product-<?php echo esc_attr( $args['kind'] ); ?>" aria-labelledby="product-<?php echo esc_attr( $args['kind'] ); ?>-title">
	<h2 id="product-<?php echo esc_attr( $args['kind'] ); ?>-title"><?php echo esc_html( $args['heading'] ); ?></h2>
	<?php echo do_shortcode( '[products ids="' . $ids . '" limit="4" columns="4" orderby="post__in"]' ); ?>
</section>
