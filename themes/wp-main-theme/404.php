<?php get_header(); ?>
<main id="main-content" class="site-main container" tabindex="-1">
	<h1><?php esc_html_e( 'Page not found', 'wp-main-theme' ); ?></h1>
	<p><?php esc_html_e( 'Try searching for what you need.', 'wp-main-theme' ); ?></p>
	<?php get_search_form(); ?>
</main>
<?php get_footer(); ?>
