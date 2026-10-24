<footer class="site-footer">
	<?php if ( get_theme_mod( 'wpmain_footer_about' ) ) : ?>
	<section class="container footer-about" aria-labelledby="footer-brand-heading">
		<h2 id="footer-brand-heading"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
		<p><?php echo esc_html( get_theme_mod( 'wpmain_footer_about' ) ); ?></p>
		<?php $about_id = absint( get_theme_mod( 'wpmain_about_page', 0 ) ); ?>
		<?php if ( $about_id && 'publish' === get_post_status( $about_id ) && 'page' === get_post_type( $about_id ) ) : ?><a href="<?php echo esc_url( get_permalink( $about_id ) ); ?>"><?php esc_html_e( 'More about us', 'wp-main-theme' ); ?></a><?php endif; ?>
	</section>
	<?php endif; ?>
	<div class="container footer-inner">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => 'nav', 'container_aria_label' => __( 'Footer navigation', 'wp-main-theme' ), 'fallback_cb' => false, 'depth' => 1 ) ); ?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
