<?php defined( 'ABSPATH' ) || exit; ?>
<form role="search" method="get" class="search-form product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label><span class="screen-reader-text"><?php esc_html_e( 'Search products', 'wp-main-theme' ); ?></span><input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products…', 'wp-main-theme' ); ?>" required></label>
	<input type="hidden" name="post_type" value="product">
	<button type="submit"><?php esc_html_e( 'Search', 'wp-main-theme' ); ?></button>
</form>
