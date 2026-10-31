# Validation

## Rc.5 — 2026-10-31

All repository PHP parses and twelve offline behavioral contracts pass. Added coverage verifies SEO override nonce/capability/autosave rules, partial/malformed update preservation, media validation, title pagination/suffix retention, assigned shop/home/journal metadata, archive introductions, full native archive guidance, safe BlogPosting encoding/public author data, native schema ownership and tracking-shortcode policy. Five external SEO-owner detections pass. Existing cart race/error/event tests remain passing. Both translation bundles pass coverage/freshness checks (133 theme, 42 plugin messages). The mandatory Persian terminology check passes.

Own-theme assets measure **11.85 KB gzip CSS** and **4.98 KB gzip JS**, within the existing 30/8 KB budgets. No frontend dependencies, remote fonts or global WooCommerce asset removal were added. Hero image selection now caps at the shared desktop layout width. These figures exclude WordPress/WooCommerce/media and do not establish production Core Web Vitals.

Read-only localhost HTTP checks pass one title/canonical/nonempty description, parseable expected schema and corrected visible spelling on Home, Shop, parent category, child category, brand, Journal and a native product. Store API confirms all ten parent product names use the preferred spelling and the existing encoded product permalink is retained. Browser AX and a saved phone screenshot confirm corrected header, hero, category and product copy. Captures remain ignored under `.local/seo-foundation/`. This targeted foundation review does not repeat rc.4's complete responsive/checkout matrix; those historical results remain below.

The live article check also passes one title/canonical/description and BlogPosting JSON-LD. The order-tracking route renders without fallback sharing metadata. The HTTP foundation script's article selector was corrected to use actual dated article links; the final two routes were then checked with its `--finish` option against the already captured homepage. Core sitemap/indexing behavior remains covered by offline policy contracts because the fictional preview deliberately keeps WordPress public indexing disabled.

## Rc.4 — 2026-10-28

Repository PHP syntax and all eleven offline contracts pass, including native layout hook order and category/brand editorial sanitation, nonce/capability checks, legacy fallback and empty states. Navigation, search, cart feedback, card options and SEO ownership tests pass. Translation checks cover 133 theme and 37 plugin messages. Custom CSS is 11.83 KB gzip and JS 4.98 KB gzip, excluding core/WooCommerce/media; both remain within their budgets.

The guarded complete-catalog fixture ran successfully on the persistent local preview. Read-only checks passed for every one of the ten fictional parent products: descriptions, native specifications, usage/audience, FAQs, sample reviews, galleries, comparison/guide links and preserved variable/sale/unpriced/unavailable states. Product-detail HTTP checks verify actual unique IDs and the order tabs → guides → recommendations. Archive checks passed native parent inclusion of child-only products, child links, brand/category scope, top intro/facts and lower comparison/FAQ content, including an empty filtered result. No fresh PHP errors appeared in the preview debug log.

Browser measurements of the populated product at 320/390/768/1024/1366/1440px showed no horizontal document overflow. Gallery/summary columns share their top/bottom bounds at desktop widths; smaller screens stack them. Product imagery remains square and native gallery controls remain present. All recommendation groups have matching card widths/heights within each viewport and 44px purchase buttons. Reviews/FAQs were checked at phone widths; the mobile comparison scrolls within its own container. Native tabs precede guides and consistent recommendation rows. Measurements and screenshots are local/ignored under `.local/review/rc4-*`.

Category and brand pages were also checked at 320/768/1440px without horizontal overflow. Real term artwork, category routes and both editorial regions were present; brand pages omit a second brand selector. Native category FAQs opened correctly at 320px. A focused native variation-payload check confirmed distinct image IDs/SKUs for 30/60ml and retained prices. The fixture initially read WooCommerce's inherited SKU getter; using the stored `edit` context fixed the sample SKU assignment. The full-demo regression now checks both variation SKUs and image IDs.

This is a responsive review in the in-app browser, not a certification of every physical device, third-party extension, screen reader or production host. All review text and generated images are explicitly fictional preview data. No test content, uploads, database or captured mail is included in the deployable ZIPs.

## Rc.3 — 2026-10-27

All nine offline PHP contracts and repository syntax pass, including bounded public comparison choices, escaped message boxes, native variation template/global restoration, private/hidden/malformed picker rejection and unique contextual-help IDs when a model appears in multiple sliders. Navigation, cart feedback, search UI, SEO ownership and the new picker interaction tests pass. Both Persian bundles pass coverage/placeholder/MO checks. Custom asset budgets remain below 30 KB CSS / 8 KB JS gzip.

Local HTTP checks passed for compact range/card markup, lazy native variation forms with AJAX matching, malformed picker 404s, three recommendation groups, four configured sample boxes, Specifications title, illustrated comparison selection and comparison URL noindex. Brand/category navigation checks passed, including actual sample archive images and retained brand scope. A test initially treated repeated `data-product_id` attributes as duplicate HTML IDs; it now checks actual whitespace-prefixed `id` attributes. Real repeated native help-span IDs were separately fixed through WooCommerce args/link filters and covered by contracts.

Browser review at 428px confirmed no horizontal overflow, aligned first-row cards at approximately 290.7px, no available-stock captions, and a single unavailable badge with `brightness(.78)` imagery. The grid picker opened, loaded native size choices, selected 30ml at 300,000 tomans, and completed a fictional native add-to-cart while remaining on `/shop/` with success feedback. Product review confirmed four message boxes, three native scrolling recommendation groups and an illustrated selectable comparison with two model images. Local screenshots remain ignored under `.local/review/rc3-*.png`. This does not certify real-device, third-party add-on or production gateway behavior.

Message defaults are empty in shipped code. `dev/storefront-preview.php` configures the owner's example wording and representative brand artwork only inside the guarded fictional store; neither its database nor fixtures ship in either ZIP.

## Rc.2 — 2026-10-27

Offline PHP syntax and all seven contract suites pass: catalog sanitation/save permissions, search guards, article anchors, filter selection/removal/price fields, homepage public-product selection/key-spec ordering, SEO/currency behavior and authoritative cart fragments. Navigation/submenu, cart feedback race/abort/error, search UI, translation coverage/MO freshness and asset budgets pass. Five external SEO-owner detection cases pass in the focused SEO runner. Custom assets: CSS 9.49 KB gzip, JS 3.78 KB gzip, excluding WordPress/WooCommerce/media.

Runtime checks passed for head/assets/price fields/cache-neutral cart placeholders and exact native JSON-LD IRR amounts for simple, sale and variable products against Store API toman prices. The enquiry check initially assumed Store API serialized an unpriced product as an empty string; this WooCommerce version serializes it as `0`. The corrected check passed for the actual enquiry UI and absence of offers/purchase controls. `test-catalog-polish` also passed against the persistent preview, including brand category drilldown and named filter removal.

Browser review confirmed explicit Persian labels on native min/max steppers, the compact category rail and product-state cues. Shop measurements at 320/390/768/1024/1366/1440px showed no horizontal document overflow, aligned cards within rows and 44×44px purchase targets. A native fictional add-to-cart succeeded, announced its status and reused the stock row for View cart without changing that row's card heights. This is a partial shop review, not a full screen-reader/real-device/accessibility certification. Local Windows/WASM PHP initialization intermittently exceeded test/browser navigation deadlines; these local times are not production performance measurements.

The fresh isolated lifecycle fixture passed locally, with mail capture, variation edits/private/trash/restore/delete, native imports, matching beyond 100 categories, rename, rebuild and bundled Persian loading. It took approximately 13.5 minutes on this Windows/WASM setup and disposed its separate port-9401 server. Only an early WooCommerce translation notice appeared during installation.

Final cart review removed a redundant Store API GET after classic add/remove: the native uncached WooCommerce response now supplies an escaped authoritative count fragment. Tests cover replaced DOM elements, zero counts, stale reads, duplicate bridge events and fallback failure. Mobile badge CSS explicitly overrides the older visually-hidden header-label rule. Cached page HTML still contains an empty hidden placeholder and never a session-specific number.

[Linux CI run 40477208401](https://github.com/vexatrov/WP-Main-Theme/actions/runs/40477208401) for commit `c6d862b` also passed all four jobs after the final cart fragment change. The additional `test:cart-fragments` script passed against the restarted persistent localhost preview: native anonymous AJAX add/refresh, visible accessible quantity one, cookie-backed persistence and preservation of WooCommerce's mini-cart fragment/cart hash. It creates no order/account and contacts no gateway. This regression is now included in isolated CI.

[Linux CI run 40475407306](https://github.com/vexatrov/WP-Main-Theme/actions/runs/40475407306) for commit `3e783f5` passed all four jobs: PHP 8.1/8.3, assets/package, and fresh WordPress integration. The integration job verified its guarded lifecycle success marker and sequentially passed smoke/product/layout/card/search/catalog/catalog-polish/features/release/shopping/recovery/order/cart-feedback suites. Fictional COD checkout, receipt-key protection, tracking denial, guest isolation and mail capture were included; no Zarinpal charge, real email delivery or hosted MySQL test occurred. The integration database is separate from `.local/site`.

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
