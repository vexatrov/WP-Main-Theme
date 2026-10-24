# Local WordPress preview

This is a real WordPress + WooCommerce development store using the official WordPress Playground CLI (PHP WebAssembly + SQLite). It is disposable, not production hosting. Both tattoo brands are primarily body tattoo B2C stores; the sample catalog reflects that correction. The preview pins WordPress 7.1.3, WooCommerce 11.2.1, PHP 8.3 and Playground CLI 3.1.40. Upgrade the preview versions together after compatibility checks.

## Start

1. Install Node.js 22+ and pnpm.
2. Run `pnpm install --frozen-lockfile` at the repository root.
3. Run `pnpm dev` and wait for the ready URL. First boot downloads WordPress, WooCommerce, and Persian translations and can take several minutes. WooCommerce/translation archives are cached in ignored `.local/cache`; WordPress also uses Playground's own cache. The preview uses six PHP workers to handle WordPress loopback requests.
4. Open http://127.0.0.1:9400/ . The dashboard is at http://127.0.0.1:9400/wp-admin/ . Local admin auto-login is enabled by Playground.
5. Stop with Ctrl+C. Restart with the same command.

Run `pnpm test:preview` against a ready demo to check the Persian/RTL homepage, product page/search, cart page, Store API add-to-cart, and fresh-session cart isolation. This changes only a disposable demo cart; it does not place an order.

The parent theme is mounted directly from the repository. Refresh after PHP/CSS/JS edits; no frontend compilation is needed. An occupied port fails startup rather than stopping another application. Keep this demo on the local machine; do not publish or tunnel its auto-login admin.

## What is sample data?

The `dev/seed.php` Blueprint step creates a Persian RTL homepage, body tattoo categories, eight simple products, original schematic SVG illustrations, sample rial prices, a menu, and native commerce pages. A visible notice marks the store as a preview. No real customer information, brand logos, or stock is included. The local demo enables cash-on-delivery solely to inspect checkout without a payment provider; it sends no payments.

The SVG upload exception exists only during the trusted seed step and does not enable SVG uploads on production. Do not copy `dev/seed.php` or the preview's generated MU-plugin to production. Only `themes/wp-main-theme` and the selected brand child theme are deployable storefront code.

## Persistence and rebuilding

Theme code is permanent in Git. This preview intentionally recreates its database and sample inventory on each start. Admin edits, orders, and uploads made within a preview are disposable. For lasting content work, use managed-host staging with a persistent database. Do not enter real orders, customer details, or production credentials here.

## Translations

The theme ships `languages/fa_IR.mo`, built from the editable `fa_IR.json`. After changing the translation source run `python scripts/build-translations.py` and commit both files. WordPress/WooCommerce translation packs are downloaded separately on preview boot. The theme supports RTL by locale; other locales retain LTR.

## Next milestones

1. Review shared desktop/mobile shopping layout in this preview.
2. Add native hierarchical navigation, attribute/brand filtering and Persian search enhancements.
3. Add specifications, comparison, compatibility and product FAQ functionality in a dedicated small plugin.
4. Apply actual branding in three child themes; verify real catalog and payment/shipping flows in persistent staging.

The previous PMU site's carousel, illustrations, and interactions are references for discussion, not features already implemented. No analytics or custom catalog API is included yet.
