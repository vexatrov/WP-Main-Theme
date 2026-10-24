<?php get_header(); ?>
<main id="main-content" class="site-main container" tabindex="-1">
 <?php while ( have_posts() ) : the_post(); ?>
 <article id="post-<?php the_ID(); ?>" <?php post_class( 'article-layout' ); ?>>
  <header class="article-header reading-width">
   <div class="article-topics"><?php the_category( ' / ' ); ?></div>
   <h1><?php the_title(); ?></h1>
   <p class="article-meta"><?php echo esc_html( get_the_author() ); ?> · <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
  </header>
  <?php if ( has_post_thumbnail() ) : ?><div class="article-cover"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?></div><?php endif; ?>
  <div class="entry-content reading-width article-body"><?php the_content(); wp_link_pages(); ?></div>
 </article>
 <div class="reading-width article-body"><?php the_post_navigation(); ?>
 <?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?></div>
 <?php
 $related = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'category__in' => wp_get_post_categories( get_the_ID() ), 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'has_password' => false ) );
 if ( $related->have_posts() ) : ?>
 <section class="storefront-section" aria-labelledby="related-reading"><h2 id="related-reading"><?php esc_html_e( 'Continue reading', 'wp-main-theme' ); ?></h2><div class="entry-list">
 <?php while ( $related->have_posts() ) : $related->the_post(); get_template_part( 'template-parts/article-card' ); endwhile; ?>
 </div></section>
 <?php endif; wp_reset_postdata(); endwhile; ?>
</main>
<?php get_footer(); ?>
