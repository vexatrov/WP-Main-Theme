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
		<div class="header-main-row">
		<div class="site-brand">
			<a class="brand-identity" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php if ( has_custom_logo() ) { echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand-logo', 'alt' => '', 'loading' => 'eager' ) ); } else { ?>
				<svg class="brand-mark" aria-hidden="true" width="36" height="36" viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="30" height="30" rx="8"/><path d="M11 11h14M18 11v15M12 18h12"/></svg>
				<?php } ?><span class="site-title"><?php bloginfo( 'name' ); ?></span>
			</a>
		</div>
		<div class="header-actions">
		<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
			<?php if ( get_option( 'woocommerce_myaccount_page_id' ) ) : ?><a class="account-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg><span><?php esc_html_e( 'Account', 'wp-main-theme' ); ?></span></a><?php endif; ?>
			<a class="cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg><span><?php esc_html_e( 'Cart', 'wp-main-theme' ); ?></span><span class="cart-count" data-cart-count hidden></span></a>
			<span class="screen-reader-text" data-cart-feedback role="status" aria-live="polite" aria-atomic="true"></span>
		<?php endif; ?>
		<button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false" hidden><?php esc_html_e( 'Menu', 'wp-main-theme' ); ?></button>
		</div></div>
		<div class="header-tools">
			<?php wpmain_header_socials(); ?>
			<?php if ( function_exists( 'wc_get_cart_url' ) ) { get_template_part( 'template-parts/header/product-search' ); } else { get_search_form(); } ?>
		</div>
		<nav class="primary-navigation" data-submenu-label="<?php esc_attr_e( 'Toggle submenu for %s', 'wp-main-theme' ); ?>" aria-label="<?php esc_attr_e( 'Primary navigation', 'wp-main-theme' ); ?>">
			<div class="header-nav-content">
			<?php if ( has_nav_menu( 'primary' ) ) { wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_id' => 'primary-menu', 'fallback_cb' => false, 'depth' => 3 ) ); } else { echo '<ul id="primary-menu"></ul>'; } ?>
			<?php wpmain_header_pages(); ?>
			</div>
		</nav>
	</div>
</header>
