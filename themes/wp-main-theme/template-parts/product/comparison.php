<?php
defined( 'ABSPATH' ) || exit;
$current = $args['product'];
$data = $args['guidance'];
$choices = $args['choices'] ?? wpmain_product_comparison_candidates( $current, $data );
$raw = isset( $_GET['compare_product'] ) ? wp_unslash( $_GET['compare_product'] ) : $data['comparison'];
$selected = wpmain_product_comparison_selection( $raw, $choices );
$selected_ids = array_map( function ( $item ) { return $item->get_id(); }, $selected );
$items = array_merge( array( $current ), $selected );
$attributes = array();
foreach ( $items as $item ) {
	foreach ( $item->get_attributes() as $name => $attribute ) {
		if ( $attribute->get_visible() ) { $attributes[ $name ] = wc_attribute_label( $name, $item ); }
	}
}
?>
<h2><?php esc_html_e( 'Compare specifications', 'wp-main-theme' ); ?></h2>
<p><?php esc_html_e( 'Compare products for the same task. Missing information is shown as a dash.', 'wp-main-theme' ); ?></p>
<form class="product-comparison-form" method="get" action="<?php echo esc_url( $current->get_permalink() . '#tab-wpmain_comparison' ); ?>">
	<?php if ( ! get_option( 'permalink_structure' ) ) : ?><input type="hidden" name="p" value="<?php echo esc_attr( $current->get_id() ); ?>"><input type="hidden" name="post_type" value="product"><?php endif; ?>
	<input type="hidden" name="compare_product[]" value="">
	<fieldset><legend><?php esc_html_e( 'Choose up to three products to compare', 'wp-main-theme' ); ?></legend>
	<p><?php esc_html_e( 'Recommended comparisons appear first, followed by other products in this category.', 'wp-main-theme' ); ?></p>
	<div class="product-comparison-choices">
	<?php foreach ( $choices as $choice ) : ?>
		<label class="product-comparison-choice">
			<input type="checkbox" name="compare_product[]" value="<?php echo esc_attr( $choice->get_id() ); ?>" <?php checked( in_array( $choice->get_id(), $selected_ids, true ) ); ?>>
			<?php echo wp_kses_post( $choice->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ) ); ?>
			<span><?php echo esc_html( $choice->get_name() ); ?></span>
			<?php if ( in_array( $choice->get_id(), $data['comparison'], true ) ) : ?><small><?php esc_html_e( 'Recommended comparison', 'wp-main-theme' ); ?></small><?php endif; ?>
		</label>
	<?php endforeach; ?>
	</div></fieldset>
	<button class="button" type="submit"><?php esc_html_e( 'Update comparison', 'wp-main-theme' ); ?></button>
</form>
<div class="product-comparison-scroll" role="region" aria-label="<?php esc_attr_e( 'Product specification comparison', 'wp-main-theme' ); ?>" tabindex="0">
<table class="product-comparison">
	<caption class="screen-reader-text"><?php esc_html_e( 'Product specification comparison', 'wp-main-theme' ); ?></caption>
	<thead><tr><th scope="col"><?php esc_html_e( 'Specification', 'wp-main-theme' ); ?></th>
	<?php foreach ( $items as $index => $item ) : ?><th scope="col"><a href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo wp_kses_post( $item->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ) ); ?><?php echo esc_html( $item->get_name() ); ?></a><?php if ( 0 === $index ) : ?><span class="comparison-current"><?php esc_html_e( 'Current product', 'wp-main-theme' ); ?></span><?php endif; ?></th><?php endforeach; ?>
	</tr></thead>
	<tbody>
	<tr><th scope="row"><?php esc_html_e( 'Price', 'wp-main-theme' ); ?></th><?php foreach ( $items as $item ) : ?><td><?php echo wp_kses_post( $item->get_price_html() ? $item->get_price_html() : '&mdash;' ); ?></td><?php endforeach; ?></tr>
	<tr><th scope="row"><?php esc_html_e( 'Availability', 'wp-main-theme' ); ?></th><?php foreach ( $items as $item ) : ?><td><?php $stock = $item->get_availability(); echo esc_html( $stock['availability'] ? $stock['availability'] : ( $item->is_in_stock() ? __( 'In stock', 'wp-main-theme' ) : __( 'Out of stock', 'wp-main-theme' ) ) ); ?></td><?php endforeach; ?></tr>
	<?php foreach ( $attributes as $name => $label ) : ?><tr><th scope="row"><?php echo esc_html( $label ); ?></th><?php foreach ( $items as $item ) : ?><td><?php $attribute = $item->get_attributes()[ $name ] ?? null; echo esc_html( $attribute && $attribute->get_visible() && $item->get_attribute( $name ) ? $item->get_attribute( $name ) : '—' ); ?></td><?php endforeach; ?></tr><?php endforeach; ?>
	</tbody>
</table>
</div>
