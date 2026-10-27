<?php
defined( 'ABSPATH' ) || exit;

/** Replace only the native classic widget output, preserving its saved settings. */
function wpmain_catalog_price_widget( $instance, $widget, $args ) {
	if ( ! is_a( $widget, 'WC_Widget_Price_Filter' ) || ! ( is_shop() || is_product_taxonomy() || is_search() ) ) { return $instance; }
	echo $args['before_widget']; // Trusted registered sidebar markup.
	$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : __( 'Filter by price', 'wp-main-theme' ), $instance, $widget->id_base );
	if ( $title ) { echo $args['before_title'] . esc_html( $title ) . $args['after_title']; }
	$id = wp_unique_id( 'catalog-price-' );
	echo '<form method="get" class="catalog-price-filter" action="' . esc_url( remove_query_arg( array( 'paged', 'product-page' ), get_pagenum_link( 1, false ) ) ) . '">';
	foreach ( array( 'min_price' => __( 'Minimum price', 'wp-main-theme' ), 'max_price' => __( 'Maximum price', 'wp-main-theme' ) ) as $key => $label ) {
		$value = wpmain_catalog_query_value( $key );
		$value = is_numeric( $value ) && (float) $value >= 0 ? $value : '';
		echo '<label for="' . esc_attr( $id . $key ) . '">' . esc_html( $label ) . ' (' . esc_html( get_woocommerce_currency_symbol() ) . ')</label><input id="' . esc_attr( $id . $key ) . '" name="' . esc_attr( $key ) . '" type="number" min="0" step="any" inputmode="decimal" value="' . esc_attr( $value ) . '">';
	}
	wc_query_string_form_fields( null, array( 'min_price', 'max_price', 'paged', 'product-page', 'submit' ) );
	echo '<button type="submit">' . esc_html__( 'Apply price', 'wp-main-theme' ) . '</button></form>' . $args['after_widget'];
	return false;
}
add_filter( 'widget_display_callback', 'wpmain_catalog_price_widget', 10, 3 );
