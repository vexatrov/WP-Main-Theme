<?php
// Preserve the posts homepage until a static front page is selected.
if ( 'posts' === get_option( 'show_on_front' ) ) {
	get_template_part( 'index' );
	return;
}
get_header();
?>
<main id="main-content" class="site-main storefront-main" tabindex="-1">
	<?php get_template_part( 'template-parts/storefront/hero' ); ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php if ( post_password_required() ) : ?>
			<div class="container home-content"><?php the_content(); ?></div>
		<?php else : ?>
			<?php foreach ( explode( ',', wpmain_sanitize_home_sections( get_theme_mod( 'wpmain_home_sections', '' ) ) ) as $section ) : ?>
				<?php if ( 'content' === $section ) : ?>
					<?php if ( trim( get_the_content() ) ) : ?><div class="container entry-content home-content"><?php the_content(); wp_link_pages(); ?></div><?php endif; ?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/storefront/' . $section ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
