<?php get_header(); ?>
<main id="main-content" class="site-main container" tabindex="-1">
	<?php if ( is_home() && ! is_front_page() ) : ?>
		<h1><?php single_post_title(); ?></h1>
	<?php elseif ( is_archive() ) : ?>
		<header class="archive-header"><?php the_archive_title( '<h1>', '</h1>' ); the_archive_description(); ?></header>
	<?php elseif ( is_search() ) : ?>
		<h1><?php printf( esc_html__( 'Search results for: %s', 'wp-main-theme' ), esc_html( get_search_query() ) ); ?></h1>
	<?php endif; ?>
	<?php if ( have_posts() ) : ?>
		<div class="entry-list">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card' ); ?>>
				<a class="entry-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } else { echo '<span class="entry-image-placeholder" aria-hidden="true"></span>'; } ?></a>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry-summary"><?php the_excerpt(); ?></div>
				<a class="entry-read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read article', 'wp-main-theme' ); ?><span class="screen-reader-text">: <?php the_title(); ?></span></a>
			</article>
		<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No results found. Try another search.', 'wp-main-theme' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
