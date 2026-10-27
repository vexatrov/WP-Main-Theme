# Shared-theme completion and release gates

Release: **1.0.0-rc.2**, catalog plugin **0.4.0**, a shared-theme release candidate. This is not a claim that three branded shops are launched. Brand child themes remain deliberately deferred by the owner. The scope below is reusable behavior and layout, with real WordPress/WooCommerce as the backend.

The [2026-10-27 audit](RELEASE-AUDIT-2026-10-27.md) is a historical findings report. Rc.2 implements the shared backlog: labeled price inputs, named removable selections, scoped category brand routes, compact stock/sale/option states, live cart feedback, submenu controls, editable homepage/key specs, Persian plugin tools and an SEO/schema integration. Search lifecycle fixes and isolated integration CI are included. This is not a full accessibility or production sign-off.

## Rc.2 verification and remaining gates

- Offline PHP syntax and seven contract suites pass, as do navigation/submenu, cart request race/error and search suggestion tests. Both Persian bundles pass source coverage, placeholders and committed-MO freshness checks.
- Custom CSS is approximately **9.49 KB gzip** and JS **3.78 KB gzip**, excluding core/WooCommerce/media. These are asset budgets, not Core Web Vitals results.
- A read-only runtime release script verifies head/assets, numeric price controls, cache-neutral cart placeholders and IRT-to-IRR schema amounts against the fictional Store API. The fresh integration runner boots an isolated database, captures test mail and runs lifecycle/shopping checks sequentially. See VALIDATION.md for actual run outcomes; the presence of a script is not a passing test claim.
- Current local Windows/WASM requests have intermittent long startup/WordPress initialization delays. Hosted MySQL/load tests, real Safari/iOS/Android, screen readers, full zoom review, gateway callbacks, actual delivery/reset email and restore remain release gates.
- Configure any external SEO owner deliberately: the fallback yields to recognized plugins. The schema adapter affects native Woo Product JSON-LD only; feeds and gateway conversion need their own verified integration. See SEO.md.
- Fresh lifecycle fixtures and the complete Linux integration/commerce CI suite passed for commit `3e783f5`; local release/schema and catalog-polish HTTP checks also passed. The updated shop has been measured across six screen widths and its native add-to-cart status/alignment checked in the browser. See VALIDATION.md for exact evidence and scope.

The table below retains the earlier rc.1 functional evidence; new rc.2 checks are recorded above and in VALIDATION.md.

| Requested area | Implemented | Validation / remaining gate |
| --- | --- | --- |
| Persian search | Stored normalized product documents, Persian/Arabic letters/digits/spacing, product and shared brand aliases, visible variation SKUs, exact-SKU/title preference, grouped suggestions, cancelled stale requests, keyboard/fallback behavior, protected index rebuild admin action. | PHP query contracts, HTTP Persian/SKU/alias results and suggestions pass. Suggestion keyboard/race/error behavior has a focused JS unit test. Real query relevance and a browser/screen-reader pass remain store QA. |
| Catalog | Child/sibling category routes, category artwork, inherited category-specific native attribute filters, native brand/price facets, buying text and guide links, active/reset and empty states. | Actual filtered membership, empty/reset context, attribute scope and guidance checked over HTTP with fictional data. Real attributes and guidance need the inventory owner. |
| Product pages | Compact aligned square cards, common-unit stacked variation range, gallery/zoom/native selection, visible key specs and brand, use/audience, comparison, FAQ, guide links, curated compatible/alternative groups, distinct unpriced/out-of-stock paths and sale display. | Product states, variation data and purchase rules checked against WooCommerce fixtures. Compatibility remains an explicit editorial relationship; no automatic inference. |
| Shopping | Native cart/checkout/account/order forms retained and styled. | Local add/update/remove, coupon/stock/variation/checkout errors, guest isolation, recovery request, tracking denial and fictional COD checkout/receipt protection tested. No real charge or delivery. Zarinpal callbacks, actual email transport, account sign-in and reset-link completion need staging. |
| Articles/support | Readable articles, collision-safe TOC with preserved anchors, curated product shortcode, related reading, About/Contact/Help/policy templates, actual-page support navigation and footer intro. | PHP anchor contracts and rendered article/product/support links checked. Real copy, contact details and policies must be provided/approved by the owner. No pretend contact form. |
| Final checks | PHP/JS contracts and syntax, package generation with SHA-256 manifests, custom asset budgets, staging read-only preflight, release/rollback instructions. | Custom CSS approximately 8 KB and JS 2 KB gzip, excluding core/Woo/media. Prior shared layouts were viewed at 320/390/1024/1366/1440px. Fresh browser automation was unavailable in this completion run; the latest visual/200%-zoom/assistive-technology sweep and real hosted performance remain gates. No staging host was supplied. |

## What is deliberately not guessed

No real domain/host, gateway merchant key, shipping prices, tax decisions, return/warranty/privacy/terms text, contact information, production catalog or brand identity is invented. Zarinpal is only the likely gateway, per the owner. Currency remains tomans; integration-specific IRT/IRR conversion stays outside the theme. There is no deployed staging site without hosting access.

Optional wishlist, mini-cart, campaign carousel, automatic dark mode, analytics and notification systems are not needed for this shared release; no such feature is claimed. A model-level curated comparison is included, not a separate shopper-managed shortlist. Each brand can override heroes, header treatment, button shapes and templates in a child theme.

## Reproduce checks

1. `pnpm install --frozen-lockfile` and `pnpm dev`; wait for the ready URL. Preview files now persist in ignored `.local/site`.
2. `pnpm test:php`, `pnpm test:navigation`, `pnpm test:cart-feedback`, `pnpm test:search-ui`, `pnpm test:seo`, `pnpm check:translations`, `pnpm check:assets`.
3. Against the localhost demo: `pnpm test:preview`, `pnpm test:search`, `pnpm test:catalog`, `pnpm test:products`, `pnpm test:cards`, `pnpm test:layouts`, `pnpm test:features`, `pnpm test:shopping`, `pnpm test:recovery`.
4. `pnpm test:order` creates a fictional COD order **only** in the guarded localhost sample store. Never point tests at production. Its mail is captured locally by the dev fixture.
5. `powershell -File scripts/package.ps1`. Install the parent/plugin ZIPs on the host's staging site, configure a child theme, then follow DEPLOYMENT.md and VALIDATION.md. The package hashes and commit are in ignored `dist/release-manifest.json`.
6. `pnpm test:catalog-polish` and `pnpm test:release` exercise the new read-only local checks. `pnpm test:integration` explicitly starts a fresh isolated fixture database; stop the normal port-9400 preview first. Never use that runner against production.

No WordPress core, installed third-party plugins, generated orders, database, uploads, runtime test credentials or local mail capture are part of Git or the deployable ZIPs.
