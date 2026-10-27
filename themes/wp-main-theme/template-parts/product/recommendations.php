<?php
defined( 'ABSPATH' ) || exit;
// Delegate to WooCommerce's shortcode to preserve loop/global state and hooks.
$ids = implode( ',', array_map( function ( $item ) { return $item->get_id(); }, $args['products'] ) );
?>
<section class="product-resources product-<?php echo esc_attr( $args['kind'] ); ?>" aria-labelledby="product-<?php echo esc_attr( $args['kind'] ); ?>-title">
	<h2 id="product-<?php echo esc_attr( $args['kind'] ); ?>-title"><?php if ( ! empty( $args['url'] ) ) : ?><a href="<?php echo esc_url( $args['url'] ); ?>"><?php echo esc_html( $args['heading'] ); ?> <span aria-hidden="true">&nearr;</span></a><?php else : ?><?php echo esc_html( $args['heading'] ); ?><?php endif; ?></h2>
	<div class="product-recommendation-slider" role="region" aria-labelledby="product-<?php echo esc_attr( $args['kind'] ); ?>-title" tabindex="0">
	<?php echo do_shortcode( '[products ids="' . $ids . '" limit="4" columns="4" orderby="post__in"]' ); ?>
	</div>
</section>
