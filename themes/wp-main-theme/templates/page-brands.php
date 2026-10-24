<?php
/**
 * Template Name: Brand directory
 * Template Post Type: page
 */
get_header();
?>
<main id="main-content" class="site-main container" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
	<header class="archive-header"><p class="eyebrow"><?php esc_html_e( 'Explore the catalog', 'wp-main-theme' ); ?></p><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div></header>
	<?php endwhile; ?>
	<?php $brands = taxonomy_exists( 'product_brand' ) ? get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => true, 'orderby' => 'name' ) ) : array(); ?>
	<?php if ( ! is_wp_error( $brands ) && $brands ) : ?>
	<div class="brand-directory">
	<?php foreach ( $brands as $brand ) : $url = get_term_link( $brand ); if ( is_wp_error( $url ) ) { continue; } ?>
		<a class="brand-card" href="<?php echo esc_url( $url ); ?>">
			<?php $image = absint( get_term_meta( $brand->term_id, 'thumbnail_id', true ) ); if ( $image ) { echo wp_get_attachment_image( $image, 'medium', false, array( 'alt' => '', 'loading' => 'lazy' ) ); } ?>
			<h2><?php echo esc_html( $brand->name ); ?></h2><span><?php esc_html_e( 'Explore products', 'wp-main-theme' ); ?> &larr;</span>
		</a>
	<?php endforeach; ?>
	</div>
	<?php else : ?><p><?php esc_html_e( 'Brands will appear here when products are available.', 'wp-main-theme' ); ?></p><?php endif; ?>
</main>
<?php get_footer(); ?>
