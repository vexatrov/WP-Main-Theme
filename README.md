# WP Main Theme

Shared, hand-coded WordPress + WooCommerce foundation for three B2C stores: two tattoo supply brands and one lash/makeup tools brand. One parent theme, independent brand child themes, separate WordPress installations and domains.

## Status

Version 0.5.0 adds square full-width product images and editable product guidance: use cases, audience, comparison, FAQs, guide links, compatible products and alternatives. WP Store Catalog stores this data separately from the theme. The Persian RTL preview uses tomans (IRT), with clearly labeled sample inventory. Brand identities, advanced filtering and production verification remain pending. The three supplied Instagram references are saved in [brand references](docs/BRANDS.md).

## See the theme locally

Run `pnpm install --frozen-lockfile`, then `pnpm dev` (Node.js 22+). Once boot completes, open http://127.0.0.1:9400/ . PHP/Docker/MySQL installations are not needed. See [local preview](docs/LOCAL-PREVIEW.md) for demo-data behavior and restart instructions. Development dependencies are not shipped in the theme ZIP.

The earlier storefront feature list and implementation boundaries are saved in [roadmap](docs/ROADMAP.md).

New design planning: [reference research](docs/RESEARCH-2026-10-24.md), [storefront plan](docs/STOREFRONT-PLAN.md), and [page/layout discussion inventory](docs/PAGE-LAYOUTS.md). These describe proposed work, not completed features. The development notice above the header has been removed at the owner's request.

See [theming](docs/THEMING.md) for child-theme design hooks, header behavior, grid rules, required pages and toman configuration. Each brand can change its header effects, button/search shapes and hero layout without adding those effects to every store.

No page builder, frontend framework, build step, external font, or JavaScript dependency. WordPress remains the CMS; WooCommerce is the required commerce plugin. Core editor blocks can manage page content inside coded templates.

## Install

1. Use a maintained WordPress release (theme minimum 6.6), PHP 8.1+; select a currently supported PHP version compatible with your host and WooCommerce.
2. Copy `themes/wp-main-theme` to `wp-content/themes/wp-main-theme`.
3. Copy `themes/brand-starter` to `wp-content/themes/<brand-slug>`. Change its theme name and text domain; retain `Template: wp-main-theme`.
4. Install WooCommerce from its official distribution and run store setup. Activate the brand child theme under Appearance > Themes.
   Install/activate `plugins/wp-store-catalog` for the approved product guidance and relationships. See [product editing and layout](docs/PRODUCT-PAGES.md).
5. Set the site title/logo and assign primary/footer menus. Create Home, set it as the static front page under Settings > Reading. Under Appearance > Customize > Storefront homepage, set the intro label, heading, description, button label and image. Additional Home editor content appears below the hero. With no static front page WordPress shows the post index.
6. Configure WooCommerce shop/cart/checkout/account pages, currency, tax, shipping, payment provider, and transactional email.
7. Create About and Contact pages and assign their supplied templates. Create a Blog/Magazine page and select it as the posts page under Settings > Reading. Assign footer navigation and configure the introduction/About link under Customize > Footer information.

Parent theme also activates without WooCommerce for content work. Never edit WordPress or WooCommerce core. See [architecture](docs/ARCHITECTURE.md), [deployment](docs/DEPLOYMENT.md), and [validation](docs/VALIDATION.md).

Homepage category cards use up to six populated top-level WooCommerce categories, ordered alphabetically, with their configured thumbnails. Latest arrivals use WooCommerce's product shortcode (eight products), respecting catalog visibility and stock settings. No demo inventory or fabricated store promises are inserted. Configure products and category images in WooCommerce; sections naturally reflect that data. Hero image alternative text comes from its Media Library record.

## Repository workflow

All theme source, implementation notes, and setup scripts belong here. Completed changes are committed and pushed to `main`, per project instructions. A GitHub push does not deploy to hosting. Deploy from a known commit after staging checks. Hosting credentials and customer data stay outside Git.

## Package

Run `powershell -File scripts/package.ps1`. Upload `dist/wp-main-theme.zip` via Appearance > Themes > Add New. Package the renamed brand child folder separately when ready. Generated ZIPs are reproducible and ignored by Git; source stays committed.

## License

Theme code is GPL-2.0-or-later. See [license](LICENSE).
