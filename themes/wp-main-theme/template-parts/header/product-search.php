<?php defined( 'ABSPATH' ) || exit; ?>
<form role="search" method="get" class="search-form product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label><span class="screen-reader-text"><?php esc_html_e( 'Search products', 'wp-main-theme' ); ?></span><input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products…', 'wp-main-theme' ); ?>" required></label>
	<input type="hidden" name="post_type" value="product">
	<button type="submit"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4.5 4.5"/></svg><span class="screen-reader-text"><?php esc_html_e( 'Search', 'wp-main-theme' ); ?></span></button>
</form>
