<?php
/**
 * Template Name: Help and policies
 * Template Post Type: page
 */
get_header();
?>
<main id="main-content" class="site-main container reading-width" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'help-page' ); ?>>
		<header class="archive-header"><p class="eyebrow"><?php esc_html_e( 'Customer care', 'wp-main-theme' ); ?></p><h1><?php the_title(); ?></h1></header>
		<div class="entry-content"><?php the_content(); wp_link_pages(); ?></div>
	</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
