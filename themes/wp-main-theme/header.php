<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'wp-main-theme' ); ?></a>
<header class="site-header" data-sticky-header>
	<div class="container header-inner">
		<div class="site-brand">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>
		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false" hidden><?php esc_html_e( 'Menu', 'wp-main-theme' ); ?></button>
			<nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'wp-main-theme' ); ?>">
				<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_id' => 'primary-menu', 'fallback_cb' => false, 'depth' => 2 ) ); ?>
			</nav>
		<?php endif; ?>
		<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
			<?php get_template_part( 'template-parts/header/product-search' ); ?>
			<?php if ( get_option( 'woocommerce_myaccount_page_id' ) ) : ?><a class="account-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg><span><?php esc_html_e( 'Account', 'wp-main-theme' ); ?></span></a><?php endif; ?>
			<a class="cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg><span><?php esc_html_e( 'Cart', 'wp-main-theme' ); ?></span></a>
		<?php endif; ?>
	</div>
</header>
