<?php get_header(); ?>
<main id="main-content" class="site-main container reading-width" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1><?php the_title(); ?></h1>
			<p><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
			<?php the_post_thumbnail( 'large' ); ?>
			<div class="entry-content"><?php the_content(); wp_link_pages(); ?></div>
		</article>
		<?php the_post_navigation(); ?>
		<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
