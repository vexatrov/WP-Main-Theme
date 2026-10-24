<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }
$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'number' => 6, 'orderby' => 'name' ) );
?>
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
<section class="container storefront-section" aria-labelledby="latest-heading">
	<div class="section-heading"><h2 id="latest-heading"><?php esc_html_e( 'Latest arrivals', 'wp-main-theme' ); ?></h2><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'View all products', 'wp-main-theme' ); ?> <span aria-hidden="true">&rarr;</span></a></div>
	<?php
	// WooCommerce owns visibility, stock rules, product rendering and loop state.
	echo do_shortcode( '[products limit="8" columns="4" orderby="date" order="DESC" visibility="visible"]' );
	?>
</section>
