<?php
/** Shared editorial helpers for public blog articles. */
defined( 'ABSPATH' ) || exit;

/**
 * Add collision-safe IDs to unanchored article headings and prepend a table of contents.
 * Existing heading IDs are retained exactly as authored.
 *
 * @param string $content Post content after normal WordPress content filters.
 * @return string
 */
function wpmain_editorial_table_of_contents( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}

	if ( ! preg_match_all( '~<h([23])\b[^>]*>(.*?)</h\1\s*>~is', $content, $matches, PREG_SET_ORDER ) || count( $matches ) < 3 ) {
		return $content;
	}

	$processor = new WP_HTML_Tag_Processor( $content );
	$used_ids  = array();
	while ( $processor->next_tag() ) {
		$id = $processor->get_attribute( 'id' );
		if ( is_string( $id ) && '' !== $id ) {
			$used_ids[ $id ] = true;
		}
	}

	$headings   = array();
	$heading_id = 0;
	foreach ( $matches as $match ) {
		++$heading_id;
		$level = (int) $match[1];
		$title = trim( html_entity_decode( wp_strip_all_tags( $match[2] ), ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' ) );

		$tag_processor = new WP_HTML_Tag_Processor( $match[0] );
		$tag_processor->next_tag( array( 'tag_name' => 'H' . $level ) );
		$id = $tag_processor->get_attribute( 'id' );
		if ( '' !== $title && ( ! is_string( $id ) || '' === $id ) ) {
			$base = rawurldecode( sanitize_title( $title ) );
			if ( '' === $base ) {
				$base = 'section-' . $heading_id;
			}
			$id         = $base;
			$suffix     = 2;
			while ( isset( $used_ids[ $id ] ) ) {
				$id = $base . '-' . $suffix;
				++$suffix;
			}
			$used_ids[ $id ] = true;
		}

		$headings[] = array(
			'id'    => $id,
			'level' => $level,
			'title' => $title,
		);
	}

	$items = array_values( array_filter( $headings, static function ( $heading ) {
		return '' !== $heading['title'];
	} ) );
	if ( count( $items ) < 3 ) {
		return $content;
	}

	$processor = new WP_HTML_Tag_Processor( $content );
	foreach ( $headings as $heading ) {
		if ( ! $processor->next_tag( array( 'tag_name' => 'H' . $heading['level'] ) ) ) {
			continue;
		}
		if ( '' !== $heading['title'] && ( null === $processor->get_attribute( 'id' ) || '' === $processor->get_attribute( 'id' ) ) ) {
			$processor->set_attribute( 'id', $heading['id'] );
		}
	}
	$content = $processor->get_updated_html();

	$links = '';
	foreach ( $items as $item ) {
		$links .= sprintf(
			'<li class="editorial-toc__item editorial-toc__item--level-%1$d"><a href="#%2$s">%3$s</a></li>',
			(int) $item['level'],
			esc_attr( $item['id'] ),
			esc_html( $item['title'] )
		);
	}

	$toc_id_base = 'editorial-toc-title';
	$toc_id      = $toc_id_base;
	$toc_suffix  = 2;
	while ( isset( $used_ids[ $toc_id ] ) ) {
		$toc_id = $toc_id_base . '-' . $toc_suffix;
		++$toc_suffix;
	}
	$toc = '<nav class="editorial-toc" aria-labelledby="' . esc_attr( $toc_id ) . '"><h2 id="' . esc_attr( $toc_id ) . '">' . esc_html__( 'On this page', 'wp-main-theme' ) . '</h2><ol>' . $links . '</ol></nav>';
	return $toc . $content;
}
add_filter( 'the_content', 'wpmain_editorial_table_of_contents', 20 );

/**
 * Render editor-curated, published WooCommerce products inside an article.
 * Example: [editorial_products ids="123,456" heading="Useful tools"]
 *
 * @param array $attributes Shortcode attributes.
 * @return string
 */
function wpmain_editorial_products_shortcode( $attributes ) {
	if ( ! is_singular( 'post' ) || ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$attributes = shortcode_atts(
		array(
			'ids'     => '',
			'heading' => '',
		),
		$attributes,
		'editorial_products'
	);

	$ids = array_slice( array_values( array_unique( array_filter( array_map( 'absint', explode( ',', (string) $attributes['ids'] ) ) ) ) ), 0, 4 );
	if ( empty( $ids ) ) {
		return '';
	}

	$cards = '';
	foreach ( $ids as $product_id ) {
		$product_post = get_post( $product_id );
		$product      = wc_get_product( $product_id );
		if ( ! $product_post || 'product' !== $product_post->post_type || 'publish' !== $product_post->post_status || '' !== $product_post->post_password || ! $product || ! $product->is_visible() ) {
			continue;
		}

		$cards .= '<li class="editorial-product">';
		$cards .= '<a class="editorial-product__link" href="' . esc_url( $product->get_permalink() ) . '">';
		$cards .= '<span class="editorial-product__image">' . wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ) . '</span>';
		$cards .= '<span class="editorial-product__title">' . esc_html( $product->get_name() ) . '</span>';
		$cards .= '</a>';
		$cards .= '<span class="editorial-product__price">' . wp_kses_post( $product->get_price_html() ) . '</span>';
		$cards .= '</li>';
	}

	if ( '' === $cards ) {
		return '';
	}

	$heading = trim( (string) $attributes['heading'] );
	$output  = '<section class="editorial-products" aria-label="' . esc_attr( $heading ?: __( 'Recommended products', 'wp-main-theme' ) ) . '">';
	if ( '' !== $heading ) {
		$output .= '<h2>' . esc_html( $heading ) . '</h2>';
	}
	$output .= '<ul class="editorial-products__grid">' . $cards . '</ul></section>';
	return $output;
}
add_shortcode( 'editorial_products', 'wpmain_editorial_products_shortcode' );
