<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }
$category_ids = wpmain_sanitize_home_ids( get_theme_mod( 'wpmain_home_categories', '' ) );
$categories = get_terms( $category_ids ? array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'include' => array_slice( explode( ',', $category_ids ), 0, 6 ), 'orderby' => 'include' ) : array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'number' => 6, 'orderby' => 'name' ) );
$parts = get_theme_mod( 'wpmain_home_products_first', false ) ? array( 'products', 'categories' ) : array( 'categories', 'products' );
foreach ( $parts as $part ) :
?>
<?php if ( 'categories' === $part ) : ?>
<?php if ( ! is_wp_error( $categories ) && $categories ) : ?>
<section class="container storefront-section" aria-labelledby="category-heading">
	<div class="section-heading"><h2 id="category-heading"><?php esc_html_e( 'Shop by category', 'wp-main-theme' ); ?></h2></div>
	<div class="category-grid">
		<?php foreach ( $categories as $category ) :
			$link = get_term_link( $category );
			if ( is_wp_error( $link ) ) { continue; }
			$image_id = absint( get_term_meta( $category->term_id, 'thumbnail_id', true ) );
		?>
		<a class="category-card" href="<?php echo esc_url( $link ); ?>">
			<?php if ( $image_id ) { echo wp_get_attachment_image( $image_id, 'medium', false, array( 'alt' => '', 'loading' => 'lazy' ) ); } ?>
			<span><?php echo esc_html( $category->name ); ?></span><span aria-hidden="true">&nearr;</span>
		</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>
<?php else : ?>
<section class="container storefront-section" aria-labelledby="latest-heading">
	<div class="section-heading"><h2 id="latest-heading"><?php echo esc_html( 'latest' === wpmain_sanitize_home_product_source( get_theme_mod( 'wpmain_home_product_source', 'latest' ) ) && ! get_theme_mod( 'wpmain_home_products', '' ) ? __( 'Latest arrivals', 'wp-main-theme' ) : __( 'Selected products', 'wp-main-theme' ) ); ?></h2><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'View all products', 'wp-main-theme' ); ?> <span aria-hidden="true">&rarr;</span></a></div>
	<?php
	// WooCommerce owns visibility, stock rules, product rendering and loop state.
	$ids = array_filter( explode( ',', wpmain_sanitize_home_ids( get_theme_mod( 'wpmain_home_products', '' ) ) ), function ( $id ) {
		$product = wc_get_product( $id );
		return $product && 'publish' === $product->get_status() && '' === $product->get_post_password() && in_array( $product->get_catalog_visibility(), array( 'visible', 'catalog' ), true ) && $product->is_visible();
	} );
	$selection = $ids ? ' ids="' . implode( ',', $ids ) . '" orderby="post__in"' : ( 'featured' === wpmain_sanitize_home_product_source( get_theme_mod( 'wpmain_home_product_source', 'latest' ) ) ? ' visibility="featured" orderby="date" order="DESC"' : ' visibility="visible" orderby="date" order="DESC"' );
	// Only this merchandising loop excludes protected products, including unlocked ones.
	$public_products = static function ( $query ) { $query['has_password'] = false; return $query; };
	add_filter( 'woocommerce_shortcode_products_query', $public_products );
	try {
		echo do_shortcode( '[products limit="8" columns="4"' . $selection . ']' );
	} finally {
		remove_filter( 'woocommerce_shortcode_products_query', $public_products );
	}
	?>
</section>

<?php endif; endforeach; ?>
