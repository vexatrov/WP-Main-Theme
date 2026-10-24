<?php defined( 'ABSPATH' ) || exit; ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card' ); ?>>
	<a class="entry-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } else { echo '<span class="entry-image-placeholder" aria-hidden="true"></span>'; } ?></a>
	<p class="article-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
	<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<div class="entry-summary"><?php the_excerpt(); ?></div>
	<a class="entry-read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read article', 'wp-main-theme' ); ?><span class="screen-reader-text">: <?php the_title(); ?></span></a>
</article>
