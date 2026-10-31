# SEO and performance audit — 2026-10-31

Audited source: `237022b`, parent **1.0.0-rc.4**, catalog plugin **0.5.0**. Read-only review of shared theme/plugin behavior and the fictional localhost preview. This report does not implement the recommendations below.

## Verdict

**A lightweight, SEO-aware foundation; not a complete SEO sign-off, and not a verified fast production shop.** There are concrete metadata/content gaps to fix. Current local HTTP responses are slow. Production speed needs measurements on the intended hosting with real product imagery, inventory, caching and integrations.

The shared theme cannot supply each brand's keyword strategy, factual product information, genuine reviews, original photographs, delivery policies, search demand or domain authority. Those remain separate store work.

## Evidence and scope

- Three bounded source reviews covered technical SEO, frontend/server performance risks, and on-page structure/accessibility. No production products, settings, orders or content were changed.
- Captured **23 route samples**, plus a separate multi-result search check: Home, Shop, parent/child category, brand directory/archive, simple/variable/sale/unpriced/unavailable products, journal/article, About/Contact, search/filter, Cart/Account/tracking, a missing URL, robots.txt and the sitemap endpoint. This is representative coverage, not a crawl of every possible URL.
- Public requests skipped Playground's automatic admin login. Source documents, headers, timing and schema snapshots are ignored in `.local/seo-audit-2026-10-31/`. `node scripts/audit-seo.mjs` reproduces the bounded local route audit.
- Measured theme asset budgets and fetched HTML-referenced external CSS/JS for Home and a product page. All fetched files returned HTTP 200. These inventories exclude dynamically requested resources, media and inline code. A separate check fetched all 14 distinct local image URLs found in the sample documents; each returned HTTP 200 with an image content type. External avatar providers were not fetched by that check.
- Re-ran focused SEO/owner contract checks. Earlier rc.4 integration/responsive results remain historical evidence for unchanged functional code; this audit did not rerun an entire checkout or real-device matrix.
- The current browser tooling did not expose the requested LCP/layout-shift timeline instrumentation. No Lighthouse score, field INP, LCP or CLS result is claimed.

## Prioritized findings

| Priority | Finding | Evidence and recommended correction |
| --- | --- | --- |
| High | **Fallback metadata ignores visible editorial copy.** | Live Home, Shop, parent category, child category, brand and journal samples had no meta description. `plugins/wp-store-catalog/seo.php:55–65` omits Customizer hero copy, configured Shop/Posts-page copy and `_wpmain_archive_intro`; conditional sharing output also loses descriptions. Read the correct public source in each context, retain sensible native fallbacks, and add regression coverage. Meta descriptions can influence displayed snippets; their presence alone does not establish rankings. |
| High | **Long native category/brand descriptions can lose visible content and links.** | `themes/wp-main-theme/inc/catalog-layout.php:24–37` removes the native archive description and shows only 320 stripped characters. Its lower guide renderer at line 214 does not fall back to the remaining native description. A store entering a long guide in WooCommerce's normal Description field can unintentionally hide it. Preserve safe native long copy below products when no custom lower guide exists, or provide an explicit migration path. Current demo custom lower guides do render. |
| High for the preview; hosting diagnosis required | **Current server response time is slow.** | A single sequential localhost pass observed approximately 7.7–36.1 seconds to response headers on normal content pages. Home was 19.6s, Shop 27.5s and a simple product 15.4s. These are Windows/WASM preview observations, not hosted TTFB benchmarks or Core Web Vitals. Investigate the runtime/host with server profiling before attributing delay to a particular theme function. Re-test cold and warm anonymous requests on staging. |
| Medium | **Search/import/taxonomy scale has not been demonstrated.** | Stored normalization is correct, but `plugins/wp-store-catalog/search.php:160–171,194` uses leading-wildcard metadata matching/relevance work. Result limits do not bound database scanning. Variation-save indexing repeatedly visits parent children (`search.php:36,52–77`). Archive category/brand lists are not term-count bounded (`inc/catalog-layout.php:63,141`). Benchmark realistic inventory and variation imports on MySQL, then consider parent-update coalescing and bounded/searchable taxonomy controls. No catalog-size failure threshold was measured. |
| Medium | **Keyboard focus may be lost after AJAX addition.** | `assets/css/purchase-feedback.css:18` hides `.button.added` once its View cart replacement exists. The feedback script does not preserve or transfer focus. Source/native Woo review identifies a keyboard regression risk; this audit did not mutate the browser cart to reproduce it. Test Enter/Space purchase and the next Tab action, then preserve the focused action or intentionally focus its replacement when appropriate. |
| Lower / configuration-dependent | **Posts-as-homepage has no main H1.** | `front-page.php:3` delegates to `index.php`, whose heading conditions at line 3 omit `is_home() && is_front_page()`. The configured static demo homepage has one H1, as do the sampled normal HTML pages. Add an appropriate heading for the supported posts-front configuration. |
| Lower / production policy | **Tracking is outside the utility-page indexing policy.** | `seo.php:15,99` explicitly handles Cart, Checkout and Account, but not the order-tracking page. The preview's global noindex currently covers it; its production exclusion is not guaranteed by that code. Detect its configured page/actual tracking shortcode and decide its noindex/sitemap policy without excluding useful Help pages indiscriminately. This is not an order-data exposure finding. |
| Lower | **Responsive hero sizing can request an oversized image on wide screens.** | `template-parts/storefront/hero.php:14` declares desktop `50vw` although the container caps at 80rem. Match `sizes` to the real capped column. Eager loading/high priority/native dimensions are already present. SVG demo imagery cannot validate real raster candidate selection. |

Additional small opportunities: reuse the bounded comparison candidate set within a request; coalesce rapid cart foreground-refresh events while preserving authoritative mutation updates; measure whether late-loaded option-dialog CSS delays first opening. These are optimization candidates, not measured causes of the current long server response.

## Performance measurements

| Item | Home | Simple product |
| --- | ---: | ---: |
| HTML body, raw | 57,383 bytes | 75,904 bytes |
| HTML-referenced stylesheet files | 9 | 13 |
| HTML-referenced external script files | 15 | 17 |
| Those external CSS/JS files, raw total | 346,314 bytes | 408,438 bytes |
| Estimated gzip size of those files | 91,485 bytes | 111,319 bytes |
| One observed response-header time | 19.6s | 15.4s |

**The gzip rows are compression estimates, not actual transferred bytes.** The sampled local static responses had no Content-Encoding and `Cache-Control: public, max-age=0`. Long-lived versioned static caching, compression and HTTP/2/3 need verification on the real host; localhost configuration does not establish their production status.

All custom theme CSS totals **11.83 KiB gzip**, and all custom JS **4.98 KiB gzip**, within the existing 30/8 KiB budgets. These small theme totals exclude WordPress/WooCommerce dependencies, HTML, media and integrations. They are not the whole page weight. The largest sampled external files were native WooCommerce RTL styling and jQuery.

Positive source properties: no page builder/frontend framework, system fonts, no remote-font dependency, deferred custom scripts, eager/high-priority hero, WordPress image APIs, lazy offscreen imagery, bounded product recommendations and lazy variation forms. Do not strip native WooCommerce assets globally to obtain a better score without validating the affected commerce flows.

For a real launch, collect mobile/desktop lab measurements and eventually field data. Google's good thresholds are **LCP ≤2.5s, INP ≤200ms, CLS ≤0.1**, assessed at the 75th percentile. A tiny stylesheet or one local run cannot establish those results. [Google thresholds](https://support.google.com/webmasters/answer/9205520), [threshold methodology](https://web.dev/articles/defining-core-web-vitals-thresholds).

## On-page and technical coverage

| Area | Verified capability / limit |
| --- | --- |
| Titles and headings | Native document titles, one H1 on sampled normal HTML routes, server-rendered product/category/article information. Posts-front H1 fallback remains the source finding above. No bundled per-page SEO title/description/social-image override UI. |
| Snippets/social cards | Product/article/static-copy descriptions render; six sampled main content routes lacked descriptions. Sharing images use a post thumbnail or site logo, not the new archive thumbnail/hero as a fallback. A selected external SEO owner can replace this behavior. |
| Product data | Parsed native Product/BreadcrumbList data without JSON errors on all five sampled product states. Simple offer: 12,500,000 IRR for 1,250,000 tomans; variable range: 3,000,000–6,000,000 IRR; sale: 520,000 IRR; unavailable: 1,200,000 IRR with OutOfStock; enquiry: no fabricated priced offer. Separate feeds/gateway/third-party graphs need their own currency contract. |
| Reviews and content | Fixture Review/AggregateRating markup is present and explicitly fictional. Keep the sample database out of production; valid markup is not authentic customer evidence. Real descriptions, verified compatibility, reviews and brand/policy copy still need store owners. |
| Internal discovery | Ordinary links for product/category/brand routes, guides, comparisons and pagination; native GET search. Parent archive HTTP checks previously verified child-only products. Homepage categories are curated/bounded; larger archive taxonomy rendering needs the scale check above. |
| Canonicals | Sample normal content pages had one canonical. Core owns singular canonicals. Source/contracts preserve archive pagination and plain-permalink routes, and hand off to recognized SEO owners. A real second result page was not available in this small catalog, so runtime pagination remains a real-data check. |
| Search/facets | Filtered archives use noindex and omit fallback sharing/canonical output. A single-SKU search redirected to its product through native behavior; a separate multiple-result search was checked. Global preview noindex prevents proving positive production indexability from these responses alone. |
| Indexing/sitemap | Every sampled normal preview page had noindex/nofollow. robots.txt was reachable; it did not blanket-block public pages. `/wp-sitemap.xml` returned 404 while WordPress's public-indexing option is disabled; core conditions sitemap availability on that option. This is appropriate preview protection, not a production sitemap pass. Cart/Checkout/Account exclusions are covered by source/contracts; tracking needs the policy decision above. |
| Errors/redirects | The deliberately missing URL returned a real 404. Captured route redirects and final URLs are in the ignored samples. No PHP failure markers or duplicate actual HTML IDs were found on the sampled normal content pages. Full broken-link/media crawling and redirect migration testing remain real-content QA. |
| Images | Standard HTML parsing found ALT attributes and dimensions on the actual sampled image nodes. Decorative archive thumbnails intentionally use empty ALT beside names. Real photographs need appropriate descriptive ALT, compression and responsive derivatives; schematic SVG fixtures do not prove that pipeline. Native script templates were excluded from image diagnostics to avoid false positives. |
| Mobile/accessibility | rc.4 has recorded responsive checks at 320–1440px, mobile table containment and keyboard-accessible native controls. This audit identifies the added-button focus risk; it is not a full screen-reader, contrast or real-device certification. |
| Brand isolation/language | Shared parent behavior, separate child identities/installations, RTL support and system fonts are present. Existing preview display copy still contains legacy spelling; migrate human-facing names to the approved «تتو» terminology while preserving technical guards and old URLs or adding redirects. |
| HTTPS/operations/measurement | No public host/domain, HTTPS redirect/HSTS/CDN, production cache isolation/load test, Search Console crawl coverage, Merchant Center validation or field Core Web Vitals were audited. These depend on the actual deployed shop. |

## Release priorities

1. Fix metadata source coverage and preserve long native archive content. Add regression tests for the actual editor paths.
2. Reproduce/fix keyboard purchase focus and the posts-front heading; make tracking indexing explicit.
3. Benchmark production-like staging with real raster images and catalog size. Confirm compression/static caching, anonymous page caching and cart/account exclusions, server TTFB, MySQL search/import work, and CWV.
4. Populate each store's original factual content and approved terminology, then verify one SEO owner, real metadata, HTTPS, sitemap, robots and authentic Product data on its domain. Keep staging private/noindex; enable production indexing deliberately.

Google's guidance emphasizes [crawlable ecommerce navigation](https://developers.google.com/search/docs/specialty/ecommerce/help-google-understand-your-ecommerce-site-structure), [stable URLs/canonicals](https://developers.google.com/search/docs/specialty/ecommerce/designing-a-url-structure-for-ecommerce-sites), [product structured data](https://developers.google.com/search/docs/appearance/structured-data/product), and [useful, people-first content](https://developers.google.com/search/docs/fundamentals/creating-helpful-content). These are supported principles, not a promise of ranking, traffic or rich-result eligibility.
