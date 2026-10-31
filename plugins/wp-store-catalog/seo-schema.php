<?php
/** Complement native WooCommerce Product/BreadcrumbList markup without inventing claims. */
defined( 'ABSPATH' ) || exit;

function wpmain_catalog_seo_schema() {
	if ( ! wpmain_catalog_seo_enabled() || is_admin() || is_feed() || is_embed() || wpmain_catalog_seo_noindex() ) { return; }
	$post = is_singular() ? wpmain_catalog_seo_public_post( get_queried_object_id() ) : null;
	if ( is_singular() && ! $post ) { return; }
	$url = wpmain_catalog_seo_canonical();
	if ( ! $url ) { return; }
	$home = home_url( '/' );
	$organization = array( '@type' => 'Organization', '@id' => $home . '#organization', 'name' => get_bloginfo( 'name' ), 'url' => $home );
	$logo = wp_get_attachment_image_url( (int) get_theme_mod( 'custom_logo' ), 'full' );
	if ( $logo ) { $organization['logo'] = $logo; }
	$graph = array( $organization );
	// WooCommerce outputs its WebSite only when the shop itself is the front page.
	$native_website = function_exists( 'is_shop' ) && is_shop() && is_front_page();
	if ( ! $native_website ) {
		$graph[] = array( '@type' => 'WebSite', '@id' => $home . '#website', 'url' => $home, 'name' => get_bloginfo( 'name' ), 'publisher' => array( '@id' => $home . '#organization' ) );
	}
	if ( $post && 'post' === $post->post_type ) {
		$article = array( '@type' => 'BlogPosting', '@id' => $url . '#article', 'mainEntityOfPage' => $url, 'headline' => get_the_title( $post ), 'datePublished' => get_post_time( DATE_W3C, true, $post ), 'dateModified' => get_post_modified_time( DATE_W3C, true, $post ), 'publisher' => array( '@id' => $home . '#organization' ) );
		$image = wpmain_catalog_seo_image( $post );
		if ( $image ) { $article['image'] = $image; }
		$author = get_the_author_meta( 'display_name', $post->post_author );
		if ( $author ) { $article['author'] = array( '@type' => 'Person', 'name' => $author, 'url' => get_author_posts_url( $post->post_author ) ); }
		$graph[] = $article;
	}
	$data = apply_filters( 'wpmain_catalog_seo_graph', array( '@context' => 'https://schema.org', '@graph' => $graph ) );
	$json = wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( $json ) { echo '<script type="application/ld+json">' . $json . '</script>' . "\n"; }
}
add_action( 'wp_head', 'wpmain_catalog_seo_schema', 6 );
