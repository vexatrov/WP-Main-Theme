# Validation

## Rc.2 — 2026-10-27

Offline PHP syntax and all six contract suites pass: catalog sanitation/save permissions, search guards, article anchors, filter selection/removal/price fields, homepage public-product selection/key-spec ordering, and SEO/currency behavior. Navigation/submenu, cart feedback race/abort/error, search UI, translation coverage/MO freshness and asset budgets pass. Five external SEO-owner detection cases pass in the focused SEO runner. Custom assets: CSS 9.34 KB gzip, JS 3.44 KB gzip, excluding WordPress/WooCommerce/media.

Runtime checks have verified the new head/assets/price fields/cache-neutral cart placeholders and exact native JSON-LD IRR amounts for simple, sale and variable products against Store API toman prices. The enquiry check initially assumed Store API serialized an unpriced product as an empty string; this WooCommerce version serializes it as `0`. The test now checks the actual enquiry UI and absence of offers/purchase controls instead of confusing serialization with a free price. Final enquiry and full isolated suite outcomes are recorded below when complete.

Browser review has loaded the updated mobile shop and confirmed explicit Persian labels on native min/max steppers, the compact category rail and product-state cues. This is a partial review, not a full screen-reader/real-device/accessibility certification. Local Windows/WASM PHP initialization has intermittently exceeded test/browser navigation deadlines; static CSS is fast but WordPress initialization itself was slow in diagnostic traces. These local times are not production performance measurements. The fresh integration runner captures test mail, uses a distinct fixture database and never reuses `.local/site`; its CI job must finish successfully before being counted as integration evidence.

## Current shared release candidate

See COMPLETION.md for the current implemented scope, command list and remaining release gates. The completion run passed offline PHP syntax/contracts, normalized-search HTTP checks, suggestions/category facets/guidance/product-state/article/support HTTP checks, native recovery/unknown-tracking checks, fictional COD checkout/receipt-key checks, navigation/search interaction units and custom asset budgets. No external payment, email delivery or staging deployment is claimed. Detailed historical viewport results below apply to earlier shared layouts; the new completion-run browser automation could not attach/respond, so a fresh visual/zoom/assistive-technology sweep remains required.

## Automated

GitHub Actions runs PHP syntax checks on PHP 8.1 and 8.3, JavaScript syntax validation, and a parent-theme packaging check. Syntax checks do not verify WordPress or commerce behavior.

## Required staging checks before launch

- Activate parent then child with WordPress debugging enabled; inspect logs. Repeat with WooCommerce disabled and enabled.
- Set a static homepage and test each Customizer field, hero image/alt text, empty hero settings, additional editor content, and password-protected Home content. Confirm the posts homepage still renders posts.
- Test homepage with no products, populated/empty categories, missing category images, hidden/out-of-stock products, and plugin disabled. Product search must use WooCommerce results and support pagination.
- Verify static homepage, posts index, single/page, archive, search/no-results, 404, password-protected content, paginated content and comments.
- Set logo and menus. Check desktop/mobile navigation, nested links, keyboard access, Escape, focus indicators, skip link, 200% zoom and no-JavaScript navigation.
- Check each brand at mobile/tablet/desktop widths, long names, real images, contrast, form errors, and empty states. Verify approved RTL/languages if required.
- Check shop/category/search, pagination, simple and variable products, gallery, sale/out-of-stock products, quantity, add-to-cart, remove/update, coupons, shipping, taxes, and persistence after reload.
- Test both the selected checkout/cart implementation and account/login/password reset; exercise guest and returning-customer orders, gateway success/failure/cancel, order confirmation, email, and refunds in sandbox.
- Confirm cache bypass with different customer sessions; no cross-session cart/account leakage. Verify responsive layout with checkout validation errors.
- Measure representative pages with real products and hosting caching. Record asset sizes, LCP, CLS, INP, and requests before performance changes. Do not promise scores from source alone.

## Initial local environment

v0.4.0: local navigation and HTTP preview checks passed, including About/Contact/Magazine routes and IRT currency. Browser checks covered 1440/1366/390/320px homepage overflow and product-row dimensions/button alignment, desktop and 390px blog layout, and compact sticky-header position/menu/Escape behavior. Child-brand effects require separate testing; local runtime speed does not establish production Core Web Vitals.

A disposable WordPress/PHP/SQLite environment is now supplied via `pnpm dev`; see LOCAL-PREVIEW.md. Production payment integrations and persistent MySQL hosting still require staging validation. Record actual preview checks in CHANGELOG.md; do not equate local sandbox checks with live gateway validation.

The v0.3.0 demo passed HTTP checks for RTL/Persian homepage, product detail/search, cart page, Store API add-to-cart and guest cart isolation. Browser checks covered homepage at desktop and 390px mobile viewport, mobile menu toggle and Escape focus, shop archive, product add-to-cart, block cart rendering and checkout form entry. No order was submitted; payment, shipping, variable products, refunds, and complete accessibility audits remain unverified. WooCommerce activation logged an early-translation notice in this environment.

## 0.6.0–0.7.0 local checks

Passed against the restarted WordPress preview: layout routes (shop, brand directory/archive, help, order tracking, magazine/article and homepage guides), price-filter empty result, compact card markup and single-unit 300,000/600,000 toman variation range, native variation form/data, product guidance and the existing RTL/search/cart/session-isolation smoke tests. Navigation unit checks passed. 0.6.0 GitHub Actions passed PHP 8.1/8.3 and asset/package jobs. Browser visual inspection for this iteration was unavailable because browser access to the previous failed-preview tab was rejected; no fresh viewport/visual results are claimed. Native variable checkout and Zarinpal remain untested.

## 0.8.0 catalog checks

Passed pnpm test:catalog against the running WordPress preview: selected-brand cards verified against Store API brand membership; leaf current-category marker; active-filter disclosure; reset preserves search/sort and removes price/brand filters; form preserves existing values; no-results recovery. No new PHP errors appeared in the preview log. Fresh visual/viewport checks were not performed in this iteration. PHP syntax and package validation run in CI.


## 0.8.1 browser review — 2026-10-25

Reviewed the running preview through the in-app browser. Shop at 390px: header reduced from about 183px to 130px; all eight cards measured 282px high after fixing title line allocation. Shop at 320px had no horizontal overflow; the mobile menu opened and Escape closed it. At 1024px the shop used three equal 307px columns with equal 430px cards and no overflow. Desktop shop was also viewed at 1440px.

Found and fixed generic `.entry-summary` excerpt clamping applied to WooCommerce's `.summary.entry-summary`, which clipped the quantity/purchase controls despite their presence in HTTP tests. Scoped the rules to `.entry-card .entry-summary`. At 1440px and 390px the product summary now has visible overflow and the purchase form measures about 52px high; the main button is 44px high. Product page at 320px had no page overflow. Homepage reviewed at 390/1366px; article reviewed at 390/1366px, with no measured overflow and a 768px desktop reading column. These are representative layout checks, not a full accessibility or checkout audit.

Used one lower-cost agent for a narrow read-only CSS review. Its proposed category aria-current issue was rejected after checking the actual term logic and passing existing integration test. Screenshot evidence is local/ignored at `.local/review/shop-mobile-2026-10-25.jpg`; production source and this report are tracked in Git.
