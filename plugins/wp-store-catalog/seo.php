<?php
/** Small fallback SEO owner and an isolated WooCommerce product-schema currency adapter. */
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_seo_enabled() {
	$owner = defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ) || function_exists( 'aioseo' ) || defined( 'RANK_MATH_VERSION' ) || function_exists( 'rank_math' ) || defined( 'SEOPRESS_VERSION' ) || function_exists( 'seopress_init' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) || function_exists( 'the_seo_framework' );
	return (bool) apply_filters( 'wpmain_catalog_seo_enabled', ! $owner );
}

function wpmain_catalog_seo_filter_key( $key ) {
	return is_string( $key ) && ( preg_match( '/^(filter_|query_type_)/', $key ) || in_array( $key, array( 'min_price', 'max_price', 'rating_filter', 'stock_status', 'on_sale', 'orderby', 'products_per_page', 'compare_product' ), true ) );
}

function wpmain_catalog_seo_noindex() {
	if ( is_search() || is_404() || ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'is_account_page' ) && is_account_page() ) ) { return true; }
	foreach ( array_keys( $_GET ) as $key ) {
		if ( wpmain_catalog_seo_filter_key( $key ) ) { return true; }
	}
	return false;
}

function wpmain_catalog_seo_robots( $robots ) {
	if ( wpmain_catalog_seo_enabled() && wpmain_catalog_seo_noindex() ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'wpmain_catalog_seo_robots', 99 );

/** Retain route and pagination query variables; drop filters, sorting and tracking. */
function wpmain_catalog_seo_canonical() {
	if ( is_singular() ) { return wp_get_canonical_url( get_queried_object_id() ) ?: ''; }
	if ( ! is_home() && ! is_archive() && ! is_front_page() ) { return ''; }
	$url = get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ), false );
	$allowed = array( 'page_id', 'pagename', 'post_type', 'taxonomy', 'term', 'cat', 'category_name', 'tag', 'tag_id', 'author', 'author_name', 'm', 'year', 'monthnum', 'day', 'paged' );
	$object = get_queried_object();
	if ( $object instanceof WP_Term ) {
		$taxonomy = get_taxonomy( $object->taxonomy );
		if ( $taxonomy && is_string( $taxonomy->query_var ) ) { $allowed[] = $taxonomy->query_var; }
	}
	$query = array();
	parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
	$remove = array_diff( array_keys( $query ), $allowed );
	return $remove ? remove_query_arg( $remove, $url ) : $url;
}

/** Read only public, unprotected post fields; never execute shortcodes or content filters. */
function wpmain_catalog_seo_public_post( $id ) {
	if ( ! $id ) { return null; }
	$post = get_post( $id );
	return $post && 'publish' === $post->post_status && '' === $post->post_password && is_post_type_viewable( $post->post_type ) ? $post : null;
}

function wpmain_catalog_seo_description( $post = null ) {
	$text = '';
	if ( $post ) {
		$text = '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		$text = $term instanceof WP_Term ? $term->description : '';
	} elseif ( is_front_page() || is_home() ) {
		$page = wpmain_catalog_seo_public_post( (int) get_option( is_home() ? 'page_for_posts' : 'page_on_front' ) );
		$text = $page ? ( $page->post_excerpt ?: $page->post_content ) : get_bloginfo( 'description' );
	}
	$text = html_entity_decode( wp_strip_all_tags( strip_shortcodes( $text ), true ), ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) ?? '' );
	return wp_html_excerpt( $text, 160, '…' );
}

function wpmain_catalog_seo_head() {
	if ( ! wpmain_catalog_seo_enabled() || is_admin() || is_feed() || is_embed() || wpmain_catalog_seo_noindex() ) { return; }
	$post = is_singular() ? wpmain_catalog_seo_public_post( get_queried_object_id() ) : null;
	if ( is_singular() && ! $post ) { return; }
	$url = wpmain_catalog_seo_canonical();
	if ( ! $url ) { return; }
	$description = wpmain_catalog_seo_description( $post );
	$title = wp_get_document_title();
	$image_id = $post ? get_post_thumbnail_id( $post->ID ) : 0;
	$image = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : false;
	if ( ! $image ) { $image = wp_get_attachment_image_url( (int) get_theme_mod( 'custom_logo' ), 'full' ); }
	// Core owns singular canonicals, including multi-page posts.
	if ( ! is_singular() ) { echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n"; }
	if ( $description ) { echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n"; }
	$properties = array( 'og:type' => $post && 'post' === $post->post_type ? 'article' : 'website', 'og:title' => $title, 'og:url' => $url, 'og:site_name' => get_bloginfo( 'name' ) );
	if ( $description ) { $properties['og:description'] = $description; }
	if ( $image ) { $properties['og:image'] = $image; }
	foreach ( $properties as $property => $value ) { echo '<meta property="' . esc_attr( $property ) . '" content="' . ( in_array( $property, array( 'og:url', 'og:image' ), true ) ? esc_url( $value ) : esc_attr( $value ) ) . '" />' . "\n"; }
	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
	if ( $description ) { echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n"; }
	if ( $image ) { echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n"; }
}
add_action( 'wp_head', 'wpmain_catalog_seo_head', 5 );

function wpmain_catalog_seo_sitemap( $args, $post_type ) {
	if ( 'page' !== $post_type || ! wpmain_catalog_seo_enabled() || ! function_exists( 'wc_get_page_id' ) ) { return $args; }
	$exclude = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
	foreach ( array( 'cart', 'checkout', 'myaccount' ) as $page ) {
		$id = (int) wc_get_page_id( $page );
		if ( $id > 0 ) { $exclude[] = $id; }
	}
	$args['post__not_in'] = array_values( array_unique( $exclude ) );
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'wpmain_catalog_seo_sitemap', 10, 2 );

/** Multiply a non-negative decimal by ten using strings, never binary arithmetic. */
function wpmain_catalog_toman_decimal( $value ) {
	if ( ! is_string( $value ) && ! is_int( $value ) && ! is_float( $value ) ) { return null; }
	$value = (string) $value;
	if ( ! preg_match( '/^([0-9]+)(?:\.([0-9]+))?$/D', $value, $parts ) ) { return null; }
	$fraction = $parts[2] ?? '';
	$integer = ltrim( $parts[1] . ( $fraction ? $fraction[0] : '0' ), '0' );
	$integer = '' === $integer ? '0' : $integer;
	$fraction = rtrim( substr( $fraction, 1 ), '0' );
	return $integer . ( '' !== $fraction ? '.' . $fraction : '' );
}

/** Currency context comes from markup, never from a guessed store-wide default. */
function wpmain_catalog_toman_node( $node, $inherited = '', $depth = 0 ) {
	if ( ! is_array( $node ) || $depth > 32 ) { return $node; }
	$currency = $inherited;
	$keys = array();
	foreach ( array( 'priceCurrency', 'currency' ) as $key ) {
		if ( array_key_exists( $key, $node ) ) {
			$keys[] = $key;
			$value = is_string( $node[ $key ] ) ? $node[ $key ] : '';
			if ( count( $keys ) > 1 && $currency !== $value ) { $currency = ''; break; }
			$currency = $value;
		}
	}
	$types = isset( $node['@type'] ) ? array_filter( (array) $node['@type'], 'is_string' ) : array();
	$money = (bool) array_intersect( $types, array( 'MonetaryAmount', 'https://schema.org/MonetaryAmount', 'http://schema.org/MonetaryAmount' ) );
	$fields = array( 'price', 'lowPrice', 'highPrice', 'minPrice', 'maxPrice' );
	if ( $money ) { $fields = array_merge( $fields, array( 'value', 'minValue', 'maxValue' ) ); }
	$converted = array();
	if ( 'IRT' === $currency ) {
		foreach ( $fields as $key ) {
			if ( ! array_key_exists( $key, $node ) ) { continue; }
			$value = wpmain_catalog_toman_decimal( $node[ $key ] );
			// Do not relabel a malformed or partly converted monetary node.
			if ( null === $value ) { $converted = array(); break; }
			$converted[ $key ] = $value;
		}
		if ( $converted ) {
			$node = array_replace( $node, $converted );
			if ( ! $keys ) { $keys[] = $money ? 'currency' : 'priceCurrency'; }
			foreach ( $keys as $key ) { $node[ $key ] = 'IRR'; }
		}
	}
	// Pass ORIGINAL context: descendants still contain original monetary amounts.
	foreach ( $node as $key => $value ) {
		if ( is_array( $value ) ) { $node[ $key ] = wpmain_catalog_toman_node( $value, $currency, $depth + 1 ); }
	}
	return $node;
}

function wpmain_catalog_toman_schema( $data ) {
	if ( ! function_exists( 'get_woocommerce_currency' ) || 'IRT' !== get_woocommerce_currency() || ! apply_filters( 'wpmain_catalog_toman_schema_enabled', true ) ) { return $data; }
	// Reject unexpectedly deep/cyclic data before changing any inherited currency.
	$bounded = function ( $value, $depth = 0 ) use ( &$bounded ) {
		if ( ! is_array( $value ) ) { return true; }
		if ( $depth > 32 ) { return false; }
		foreach ( $value as $child ) { if ( is_array( $child ) && ! $bounded( $child, $depth + 1 ) ) { return false; } }
		return true;
	};
	if ( ! $bounded( $data ) ) { return $data; }
	return wpmain_catalog_toman_node( $data );
}
add_filter( 'woocommerce_structured_data_product', 'wpmain_catalog_toman_schema', 999 );
