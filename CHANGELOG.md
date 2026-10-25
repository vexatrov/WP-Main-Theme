# Changelog

## 0.8.2

- Centered product-card plus/option icons independently of hidden button text. Removed the inherited 1.5rem flex gap that shifted the icons sideways.
- Verified simple and variable buttons in the running browser: 44px targets, zero gap, centered icon layer.


## 0.8.1

- Fixed article excerpt clipping leaking into WooCommerce product summaries and hiding purchase controls.
- Reduced mobile header/category spacing and grouped catalog sorting/result count.
- Reserved full two-line title height to align product cards; kept product purchase controls at least 44px tall.
- Visually reviewed real preview layouts on phone, tablet/laptop and desktop.


## 0.8.0

- Category routes now include configured thumbnails, sibling navigation on leaf categories and a current-category marker.
- Added a native WooCommerce brand selector alongside configurable price/attribute widgets.
- Keep active filters open and offer a scoped clear action that preserves search/sort.
- Added empty-result recovery with product search and a full-catalog link.
- No additional scripts, dependency or WooCommerce template copies.


## 0.7.0

- Compact product purchase row: small accessible action beside price; two stacked variation prices with one currency unit.
- Two-line titles and reduced card spacing.
- Refined header search, account/cart icons and menu control with no new frontend JavaScript.
- Added fictional variable-product fixture and price-range checks.


## 0.6.0

- Added native WooCommerce catalog navigation/filter area, brand-directory and Help/policies page templates.
- Expanded article layouts, related reading and homepage guide cards; fixed desktop article grids so sparse rows do not stretch cards.
- Styled native account/order/cart surfaces without replacing WooCommerce forms.
- Added disposable brand/help/tracking fixtures and route/filter checks.
- Removed duplicate Playground plugin activation from preview startup.
- Brand work and Zarinpal integration remain deferred by owner direction.


## 0.5.0 — 2026-10-24

- Product card images now fill the card width in a square canvas with no default inner padding/background/radius; child variables permit other designs. Tightened original demo vector framing.
- Added small WP Store Catalog plugin with classic product-editor fields for use cases/audience, FAQs, guide links, compatible items, alternatives and comparison choices; permission/nonce/type/limit checks preserve valid metadata.
- Added theme sections for product guidance, comparison from visible WooCommerce attributes, FAQs, public article links and two separate curated recommendation groups. Empty sections hide; generic related items remain a fallback.
- Kept native WooCommerce descriptions, attributes, galleries, variant selection and purchase behavior. Comparison is model-level; no external recommendation service or extra frontend library added.
- Added plugin packaging, demo fixtures, HTTP feature checks and PHP save/data-contract checks in CI. Product/admin and responsive verification recorded in PRODUCT-PAGES.md.
- Live preview passed catalog/cart and product-detail HTTP checks. Browser verified comparison controls and full-width square images at desktop/phone sizes; the WordPress product editor saved guidance and kept curated product/article selections.

## 2026-10-24 — reference research and layout planning

- Removed the development notice above the header from the active demo and from future seed setup; removed its unused CSS and updated the smoke assertion.
- Researched supplied MRT/Artoo/Boom references plus Killer Ink, Barber DTS and LashBase, with primary UX/accessibility/platform references.
- Added research findings, proposed storefront architecture/priorities, and a page/layout discussion inventory. No unapproved brand redesign or advanced planned functionality is represented as implemented.

## 0.4.0 — 2026-10-24

- Added compact sticky header with responsive menu access, focus protection, scroll hysteresis and anchor clearance; no dependencies added.
- Aligned product cards across homepage/shop/search/related loops and blog cards with mixed-length titles or missing images.
- Added About/Contact templates, demo Magazine page, footer introduction and About link.
- Extracted hero template part and exposed independent button/input/header tokens for child themes; glass and slider effects stay brand-specific.
- Corrected currency to tomans (IRT), including demo amounts. Theme never forces currency or converts production prices.
- Passed local navigation behavior tests and HTTP checks for standard pages, IRT currency, catalog/search, add-to-cart and guest cart isolation. Browser tests at 1440/1366/390/320px found no homepage overflow; product rows had equal image/card dimensions and button alignment. Blog rows aligned, including a missing thumbnail; compact header stayed at top and menus supported Escape. Production performance, gateways and complete accessibility audits remain pending.

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
