<?php
/** One specification table, with optional public catalog comparisons. */
defined( 'ABSPATH' ) || exit;
$current = $args['product'];
$data = $args['guidance'];
$choices = $args['choices'] ?? wpmain_product_comparison_candidates( $current, $data );
$raw = isset( $_GET['compare_product'] ) ? wp_unslash( $_GET['compare_product'] ) : array();
$choices = wpmain_product_comparison_requested( $choices, $current, $raw );
$query = isset( $_GET['compare_q'] ) && is_string( $_GET['compare_q'] ) ? sanitize_text_field( wp_unslash( $_GET['compare_q'] ) ) : '';
if ( $query && function_exists( 'wpmain_catalog_comparison_matches' ) ) {
 foreach ( wpmain_catalog_comparison_matches( $query, $current->get_id() ) as $found ) { $choices[ $found->get_id() ] = $found; }
}
$selected = wpmain_product_comparison_selection( $raw, $choices );
$selected_ids = array_map( static function ( $item ) { return $item->get_id(); }, $selected );
$items = array_merge( array( $current ), $selected );
$rows = array(); $labels = array();
foreach ( $items as $item ) {
 $rows[ $item->get_id() ] = wpmain_product_specification_rows( $item );
 foreach ( $rows[ $item->get_id() ] as $key => $row ) { $labels[ $key ] = $row['label']; }
}
?>
<section class="product-specification-panel" data-comparison data-endpoint="<?php echo esc_url( rest_url( 'wpmain/v1/comparison-search' ) ); ?>" data-current="<?php echo esc_attr( $current->get_id() ); ?>" data-loading="<?php esc_attr_e( 'Searching…', 'wp-main-theme' ); ?>" data-empty="<?php esc_attr_e( 'No products found.', 'wp-main-theme' ); ?>" data-error="<?php esc_attr_e( 'Search unavailable. Press Enter to try again.', 'wp-main-theme' ); ?>" data-limit="<?php esc_attr_e( 'Choose up to three products', 'wp-main-theme' ); ?>">
<?php if ( ! empty( $rows[ $current->get_id() ] ) ) : ?>
<h2><?php esc_html_e( 'Specifications', 'wp-main-theme' ); ?></h2>
<dl class="product-specification-list">
 <?php foreach ( $rows[ $current->get_id() ] as $row ) : ?><div><dt><?php echo esc_html( $row['label'] ); ?></dt><dd><?php echo wp_kses_post( $row['value'] ); ?></dd></div><?php endforeach; ?>
</dl>
<?php endif; ?>
<h2 class="product-comparison-heading"><?php esc_html_e( 'Compare specifications', 'wp-main-theme' ); ?></h2>
<form class="product-comparison-form" method="get" action="<?php echo esc_url( $current->get_permalink() . '#tab-additional_information' ); ?>">
 <?php if ( ! get_option( 'permalink_structure' ) ) : ?><input type="hidden" name="p" value="<?php echo esc_attr( $current->get_id() ); ?>"><input type="hidden" name="post_type" value="product"><?php endif; ?>
 <input type="hidden" name="compare_product[]" value="">
 <div class="comparison-toolbar">
  <label class="comparison-search"><span><?php esc_html_e( 'Find a product to compare', 'wp-main-theme' ); ?></span><input type="search" name="compare_q" value="<?php echo esc_attr( $query ); ?>" placeholder="<?php esc_attr_e( 'Product name or model…', 'wp-main-theme' ); ?>" autocomplete="off"></label>
  <button class="button" type="submit"><?php esc_html_e( 'Compare', 'wp-main-theme' ); ?> <span data-comparison-count><?php echo esc_html( count( $selected ) ); ?> / 3</span></button>
 </div>
 <fieldset><legend><?php esc_html_e( 'Choose products', 'wp-main-theme' ); ?></legend>
 <div class="product-comparison-choices">
 <?php foreach ( $choices as $choice ) : ?>
  <label class="product-comparison-choice" data-name="<?php echo esc_attr( $choice->get_name() ); ?>">
   <input type="checkbox" name="compare_product[]" value="<?php echo esc_attr( $choice->get_id() ); ?>" <?php checked( in_array( $choice->get_id(), $selected_ids, true ) ); ?>>
   <?php echo wp_kses_post( $choice->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ) ); ?>
   <span><?php echo esc_html( $choice->get_name() ); ?></span>
   <?php if ( in_array( $choice->get_id(), $data['comparison'], true ) ) : ?><small><?php esc_html_e( 'Suggested', 'wp-main-theme' ); ?></small><?php endif; ?>
  </label>
 <?php endforeach; ?>
 </div></fieldset>
 <p class="comparison-status" role="status" aria-live="polite"></p>
</form>
<?php if ( $selected ) : ?>
<div class="product-comparison-scroll" role="region" aria-label="<?php esc_attr_e( 'Product specification comparison', 'wp-main-theme' ); ?>" tabindex="0">
<table class="product-comparison" style="--comparison-columns:<?php echo esc_attr( count( $items ) ); ?>">
 <caption class="screen-reader-text"><?php esc_html_e( 'Product specification comparison', 'wp-main-theme' ); ?></caption>
 <thead><tr><th scope="col"><?php esc_html_e( 'Specification', 'wp-main-theme' ); ?></th>
 <?php foreach ( $items as $index => $item ) : ?><th scope="col"<?php echo 0 === $index ? ' class="is-current"' : ''; ?>><a href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo wp_kses_post( $item->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ) ); ?><span><?php echo esc_html( $item->get_name() ); ?></span></a><?php if ( 0 === $index ) : ?><span class="comparison-current"><?php esc_html_e( 'Current product', 'wp-main-theme' ); ?></span><?php endif; ?></th><?php endforeach; ?>
 </tr></thead>
 <tbody>
 <tr><th scope="row"><?php esc_html_e( 'Price', 'wp-main-theme' ); ?></th><?php foreach ( $items as $item ) : ?><td><?php echo wp_kses_post( $item->get_price_html() ?: '&mdash;' ); ?></td><?php endforeach; ?></tr>
 <?php foreach ( $labels as $key => $label ) : ?><tr><th scope="row"><?php echo esc_html( $label ); ?></th><?php foreach ( $items as $item ) : ?><td><?php echo wp_kses_post( $rows[ $item->get_id() ][ $key ]['value'] ?? '&mdash;' ); ?></td><?php endforeach; ?></tr><?php endforeach; ?>
 </tbody>
</table>
</div>
<?php endif; ?>
</section>
