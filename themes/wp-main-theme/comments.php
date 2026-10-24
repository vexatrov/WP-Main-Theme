<?php
if ( post_password_required() ) { return; }
?>
<section class="comments-area" aria-label="<?php esc_attr_e( 'Comments', 'wp-main-theme' ); ?>">
	<?php if ( have_comments() ) : ?>
		<h2><?php esc_html_e( 'Comments', 'wp-main-theme' ); ?></h2>
		<ol class="comment-list"><?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true ) ); ?></ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php comment_form(); ?>
</section>
