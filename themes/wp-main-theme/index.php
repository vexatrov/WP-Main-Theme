<?php get_header(); ?>
<main id="main-content" class="site-main container" tabindex="-1">
	<?php if ( is_home() ) : ?>
		<h1><?php if ( is_front_page() ) { echo esc_html( get_bloginfo( 'name' ) ); } else { single_post_title(); } ?></h1>
	<?php elseif ( is_archive() ) : ?>
		<header class="archive-header"><?php the_archive_title( '<h1>', '</h1>' ); the_archive_description(); ?></header>
	<?php elseif ( is_search() ) : ?>
		<h1><?php printf( esc_html__( 'Search results for: %s', 'wp-main-theme' ), esc_html( get_search_query() ) ); ?></h1>
	<?php endif; ?>
	<?php if ( have_posts() ) : ?>
		<div class="entry-list">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/article-card' ); ?>
		<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No results found. Try another search.', 'wp-main-theme' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
