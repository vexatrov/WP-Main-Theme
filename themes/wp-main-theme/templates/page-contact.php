<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 */
get_header();
?>
<main id="main-content" class="site-main container reading-width" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'contact-page' ); ?>>
		<h1><?php the_title(); ?></h1>
		<div class="entry-content"><?php the_content(); wp_link_pages(); ?></div>
	</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
