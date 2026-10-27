<?php
defined( 'ABSPATH' ) || exit;

/** Render a short set of editor-marked specifications beside the purchase form. */
function wpmain_product_key_specs() {
	global $product;
	if ( ! $product ) {
		return;
	}

	$items = array();
	$attributes = $product->get_attributes();
	$selection = $product->get_meta( '_wpmain_key_specs', true );
	if ( is_array( $selection ) ) {
		$ordered = array();
		foreach ( array_slice( array_unique( array_filter( $selection, 'is_string' ) ), 0, 4 ) as $name ) {
			foreach ( $attributes as $attribute ) {
				if ( $name === $attribute->get_name() ) { $ordered[] = $attribute; break; }
			}
		}
		$attributes = $ordered;
	}
	foreach ( $attributes as $attribute ) {
		if ( ! $attribute->get_visible() ) {
			continue;
		}

		$values = $attribute->is_taxonomy()
			? wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) )
			: $attribute->get_options();
		if ( is_wp_error( $values ) || ! $values ) {
			continue;
		}

		$items[] = array(
			'label' => wc_attribute_label( $attribute->get_name(), $product ),
			'value' => implode( ', ', array_map( 'wp_strip_all_tags', $values ) ),
		);
		if ( 4 <= count( $items ) ) {
			break;
		}
	}

	if ( $items ) {
		echo '<section class="wpmain-product-key-specs"><h2 class="wpmain-product-key-specs__heading">' . esc_html__( 'Key specifications', 'wp-main-theme' ) . '</h2><dl>';
		foreach ( $items as $item ) {
			echo '<div class="wpmain-product-key-specs__item"><dt>' . esc_html( $item['label'] ) . '</dt><dd>' . esc_html( $item['value'] ) . '</dd></div>';
		}
		echo '</dl></section>';
	}

	wpmain_product_brand_link( $product );
}
add_action( 'woocommerce_single_product_summary', 'wpmain_product_key_specs', 25 );

/** Show a native assigned brand term when WooCommerce Brands is available. */
function wpmain_product_brand_link( $product ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}
	$terms = get_the_terms( $product->get_id(), 'product_brand' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	$term = reset( $terms );
	$link = get_term_link( $term );
	if ( is_wp_error( $link ) ) {
		return;
	}
	echo '<p class="wpmain-product-brand"><span>' . esc_html__( 'Brand', 'wp-main-theme' ) . ':</span> <a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></p>';
}

/** Find a contact page selected in the Customizer or using the theme contact template. */
function wpmain_contact_page_url() {
	$page_id = absint( get_theme_mod( 'wpmain_contact_page', 0 ) );
	if ( $page_id && 'publish' === get_post_status( $page_id ) && 'page' === get_post_type( $page_id ) ) {
		return get_permalink( $page_id );
	}

	$pages = get_pages( array(
		'post_status' => 'publish',
		'meta_key'    => '_wp_page_template',
		'meta_value'  => 'templates/page-contact.php',
		'number'      => 1,
	) );
	return $pages ? get_permalink( $pages[0]->ID ) : '';
}

/** Give unpriced and unavailable products distinct, useful next steps. */
function wpmain_product_purchase_guidance() {
	global $product;
	if ( ! $product ) {
		return;
	}

	if ( ! $product->is_in_stock() ) {
		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		$url   = '';
		if ( $terms && ! is_wp_error( $terms ) ) {
			$url = get_term_link( reset( $terms ) );
		}
		if ( ! $url || is_wp_error( $url ) ) {
			$url = wc_get_page_permalink( 'shop' );
		}

		echo '<div class="wpmain-product-state wpmain-product-state--unavailable"><p>' . esc_html__( 'Looking for an available option?', 'wp-main-theme' ) . '</p>';
		if ( $url && ! is_wp_error( $url ) ) {
			echo '<a class="button wpmain-product-state__link" href="' . esc_url( $url ) . '">' . esc_html__( 'Browse available products', 'wp-main-theme' ) . '</a>';
		}
		echo '</div>';
		return;
	}

	if ( '' !== $product->get_price() ) {
		return;
	}
	$contact_url = wpmain_contact_page_url();
	if ( ! $contact_url ) {
		return;
	}
	echo '<div class="wpmain-product-state wpmain-product-state--enquiry"><p>' . esc_html__( 'Contact us for current availability and pricing.', 'wp-main-theme' ) . '</p><a class="button wpmain-product-state__link" href="' . esc_url( $contact_url ) . '">' . esc_html__( 'Ask about this product', 'wp-main-theme' ) . '</a></div>';
}
add_action( 'woocommerce_single_product_summary', 'wpmain_product_purchase_guidance', 29 );
