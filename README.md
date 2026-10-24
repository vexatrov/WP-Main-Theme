# WP Main Theme

Shared, hand-coded WordPress + WooCommerce foundation for three B2C stores: two tattoo supply brands and one lash/makeup tools brand. One parent theme, independent brand child themes, separate WordPress installations and domains.

## Status

Version 0.2.0 adds a coded storefront homepage with CMS-editable hero content, real category cards and latest product arrivals, product search, and account/cart links. Includes responsive navigation, accessible skip link, content/search/404/comment templates, and hook-based WooCommerce integration. This is a shared base, not a launch-ready branded shop. Brand identities, advanced filtering, and live-store validation remain pending. The three supplied Instagram references are saved in [brand references](docs/BRANDS.md).

No page builder, frontend framework, build step, external font, or JavaScript dependency. WordPress remains the CMS; WooCommerce is the required commerce plugin. Core editor blocks can manage page content inside coded templates.

## Install

1. Use a maintained WordPress release (theme minimum 6.6), PHP 8.1+; select a currently supported PHP version compatible with your host and WooCommerce.
2. Copy `themes/wp-main-theme` to `wp-content/themes/wp-main-theme`.
3. Copy `themes/brand-starter` to `wp-content/themes/<brand-slug>`. Change its theme name and text domain; retain `Template: wp-main-theme`.
4. Install WooCommerce from its official distribution and run store setup. Activate the brand child theme under Appearance > Themes.
5. Set the site title/logo and assign primary/footer menus. Create Home, set it as the static front page under Settings > Reading. Under Appearance > Customize > Storefront homepage, set the intro label, heading, description, button label and image. Additional Home editor content appears below the hero. With no static front page WordPress shows the post index.
6. Configure WooCommerce shop/cart/checkout/account pages, currency, tax, shipping, payment provider, and transactional email.

Parent theme also activates without WooCommerce for content work. Never edit WordPress or WooCommerce core. See [architecture](docs/ARCHITECTURE.md), [deployment](docs/DEPLOYMENT.md), and [validation](docs/VALIDATION.md).

Homepage category cards use up to six populated top-level WooCommerce categories, ordered alphabetically, with their configured thumbnails. Latest arrivals use WooCommerce's product shortcode (eight products), respecting catalog visibility and stock settings. No demo inventory or fabricated store promises are inserted. Configure products and category images in WooCommerce; sections naturally reflect that data. Hero image alternative text comes from its Media Library record.

## Repository workflow

All theme source, implementation notes, and setup scripts belong here. Completed changes are committed and pushed to `main`, per project instructions. A GitHub push does not deploy to hosting. Deploy from a known commit after staging checks. Hosting credentials and customer data stay outside Git.

## Package

Run `powershell -File scripts/package.ps1`. Upload `dist/wp-main-theme.zip` via Appearance > Themes > Add New. Package the renamed brand child folder separately when ready. Generated ZIPs are reproducible and ignored by Git; source stays committed.

## License

Theme code is GPL-2.0-or-later. See [license](LICENSE).
