<?php
// Preserve the posts homepage until a static front page is selected.
if ( 'posts' === get_option( 'show_on_front' ) ) {
	get_template_part( 'index' );
	return;
}
get_header();
$hero_image = absint( get_theme_mod( 'wpmain_hero_image', 0 ) );
$hero_title = get_theme_mod( 'wpmain_hero_title', '' );
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
?>
<main id="main-content" class="site-main storefront-main" tabindex="-1">
	<section class="container storefront-hero <?php echo $hero_image ? 'has-hero-image' : ''; ?>" aria-labelledby="storefront-title">
		<div class="hero-copy">
			<?php if ( get_theme_mod( 'wpmain_hero_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( get_theme_mod( 'wpmain_hero_eyebrow' ) ); ?></p><?php endif; ?>
			<h1 id="storefront-title"><?php echo esc_html( $hero_title ? $hero_title : get_bloginfo( 'name' ) ); ?></h1>
			<?php if ( get_theme_mod( 'wpmain_hero_description' ) ) : ?><p class="hero-description"><?php echo esc_html( get_theme_mod( 'wpmain_hero_description' ) ); ?></p><?php endif; ?>
			<?php if ( $shop_url ) : ?><a class="button hero-button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( get_theme_mod( 'wpmain_hero_button', __( 'Explore the shop', 'wp-main-theme' ) ) ); ?> <span aria-hidden="true">&rarr;</span></a><?php endif; ?>
		</div>
		<?php if ( $hero_image ) : ?><div class="hero-media"><?php echo wp_get_attachment_image( $hero_image, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 768px) 100vw, 50vw' ) ); ?></div><?php endif; ?>
	</section>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php if ( post_password_required() ) : ?>
			<div class="container home-content"><?php the_content(); ?></div>
		<?php else : ?>
			<?php if ( trim( get_the_content() ) ) : ?><div class="container entry-content home-content"><?php the_content(); wp_link_pages(); ?></div><?php endif; ?>
			<?php get_template_part( 'template-parts/storefront/catalog' ); ?>
		<?php endif; ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
