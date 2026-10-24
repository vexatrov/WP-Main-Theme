# Changelog

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
