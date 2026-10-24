<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label><span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'wp-main-theme' ); ?></span><input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'wp-main-theme' ); ?>" required></label>
	<button type="submit"><?php esc_html_e( 'Search', 'wp-main-theme' ); ?></button>
</form>
