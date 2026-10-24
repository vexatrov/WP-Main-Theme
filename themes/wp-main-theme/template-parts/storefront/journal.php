<?php
defined( 'ABSPATH' ) || exit;
$articles = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'has_password' => false ) );
if ( ! $articles->have_posts() ) { return; }
?>
<section class="container storefront-section" aria-labelledby="journal-heading">
	<div class="section-heading"><h2 id="journal-heading"><?php esc_html_e( 'Guides and inspiration', 'wp-main-theme' ); ?></h2>
	<?php $journal = absint( get_option( 'page_for_posts' ) ); if ( $journal && 'publish' === get_post_status( $journal ) ) : ?><a href="<?php echo esc_url( get_permalink( $journal ) ); ?>"><?php esc_html_e( 'All articles', 'wp-main-theme' ); ?></a><?php endif; ?></div>
	<div class="entry-list"><?php while ( $articles->have_posts() ) : $articles->the_post(); get_template_part( 'template-parts/article-card' ); endwhile; ?></div>
</section>
<?php wp_reset_postdata(); ?>
