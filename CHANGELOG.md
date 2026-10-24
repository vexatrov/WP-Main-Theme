# Changelog

## 0.3.0 — 2026-10-24

- Corrected brand scope: both tattoo shops are B2C and primarily sell body tattoo supplies, not PMU-focused.
- Added runnable WordPress Playground development server with live-mounted theme, WooCommerce, Persian locale and disposable sample catalog.
- Added Persian theme translation source/compiled catalog and locale-dependent RTL stylesheet.
- Refined shared header, mobile search/menu layout, homepage product grid and category cards.
- Added local preview setup and persistence documentation. Demo products, schematic artwork and prices are explicitly marked as samples.
- Validated the live local demo: Persian/RTL homepage, product details, product search, cart page, Store API add-to-cart and independent guest carts passed the HTTP smoke check. Browser checks covered desktop/mobile layout, menu toggle/Escape, shop archive, product add-to-cart, rendered block cart and checkout form entry. No order/payment was submitted.
- Disabled WooCommerce's initial coming-soon mode in demo setup. Cached official language archives directly after Playground's language download step failed. WooCommerce emitted an early-translation notice during activation; no storefront fatal errors were observed.

## 0.2.0 — 2026-10-24

- Saved all three supplied Instagram references in docs/BRANDS.md; visual review blocked by Instagram fetch restrictions.
- Added static storefront homepage, sanitized Customizer hero controls, category cards, and native WooCommerce latest arrivals.
- Added product search/account navigation, responsive homepage layouts, and additional product/account styles; retained native cart/checkout behavior.
- Updated setup and staging validation instructions. Live WordPress/checkout checks remain pending.

## 0.1.0 — 2026-10-24

- Created shared custom classic theme with WooCommerce hooks and no page builder.
- Added brand child-theme starter with one fixed palette and system fonts.
- Added content templates, responsive navigation, and base commerce styling.
- Added architecture, deployment, validation, repository instructions, and package script.
- Added CI for PHP/JavaScript syntax and packaging. Runtime WordPress and checkout validation pending staging.
- Local validation passed: JavaScript syntax; navigation behavior (mobile collapse/toggle, Escape focus, desktop resize); ZIP creation and required-file structure. Parent theme source is approximately 15 KB before WordPress/WooCommerce assets. PHP lint runs in CI because PHP is unavailable locally.
