# SEO ownership and toman schema

The [2026-10-31 SEO/performance audit](SEO-PERFORMANCE-AUDIT-2026-10-31.md) is a historical pre-fix report. Rc.5/plugin 0.6.0 implements its shared metadata, archive-content, utility-indexing, heading and keyboard-focus corrections. This is a reusable foundation; each shop's content/domain strategy is separate.

WP Store Catalog supplies a small fallback SEO owner when Yoast, All in One SEO, Rank Math, SEOPress and The SEO Framework are absent. `wpmain_catalog_seo_enabled` lets the deployment's selected integration disable this fallback. Detection happens when hooks run, after plugins load.

## Metadata and discovery policy

The fallback emits descriptions and Open Graph/Twitter metadata from saved overrides, public excerpts/content, short archive introductions/native descriptions, visible homepage hero copy and the actual site tagline. Descriptions are plain escaped text limited to 160 characters; shortcodes/content filters are never executed. Sharing images use the explicit library attachment, post thumbnail, term thumbnail, homepage hero or installed logo. Protected/private/draft/missing singular posts receive no fallback metadata. Search, error and commerce utility pages do not receive sharing metadata.

### Editing metadata and archive copy

Edit a page, product or article: **Search and sharing** contains SEO title, description and a native sharing-image library picker with preview/removal. Blank fields restore automatic values. Edit the assigned Home/Shop/Journal page for those routes. Titles retain native page numbers and site suffixes; visible headings remain independent. Native category, product-category, brand and article-tag editors provide the same controls. Invalid images, malformed submissions, unauthorized writes and autosaves cannot overwrite manual values. The durable fields remain in the companion plugin when child themes change. With an external SEO plugin active, use that plugin's controls; bundled fields remain saved but are not automatically imported. See EDITOR-CONTROLS.md.

Product categories/brands also have separate short introductions, three factual highlights and rich lower buying guides. Full native descriptions remain the lower fallback when custom rich/legacy guidance is empty. These fields support useful factual copy, not keyword stuffing.

### Schema ownership

WooCommerce owns Product, price, availability, genuine review data and BreadcrumbList. The fallback complements these with Organization from the site's actual name/URL/logo, WebSite except where WooCommerce already outputs it for a shop-as-homepage, and BlogPosting from published headlines, dates, public authors and media. It invents no address, rating, warranty, shipping or return claim. Visible FAQs remain accessible content; indiscriminate FAQ rich-result markup is not injected. `wpmain_catalog_seo_graph` supports explicit integrations. All bundled fallback graphs yield to a detected SEO owner; the currency adapter remains separately controllable.

WordPress retains singular canonical ownership. For home/archive routes, `get_pagenum_link()` supplies the canonical URL, preserving the page number and plain-permalink route/taxonomy variables while removing filters, sorting and tracking parameters. Archive page 2 stays canonical to page 2 and indexable. Existing robots restrictions are never removed.

The `wp_robots` filter adds `noindex` for search, 404, Cart, Checkout, My Account and native order-tracking shortcode pages, plus URLs containing `filter_*`, `query_type_*`, `min_price`, `max_price`, `rating_filter`, `stock_status`, `on_sale`, `orderby`, `compare_product` or `products_per_page`. Normal taxonomy/brand routes remain indexable. Core sitemap queries exclude assigned commerce pages and indexed tracking pages while retaining other exclusions/meta constraints. Tracking flags update on page save; existing sites backfill 50 published pages per administrator dashboard request until complete. Resaving a tracking page updates it immediately. Custom utility implementations can extend native sitemap/robots filters. Do not block utility URLs in robots.txt before crawlers can see noindex.

When another SEO owner is detected/selected, it owns metadata, canonicals, robots and sitemap policy. Confirm equivalent settings there; this plugin does not assume another plugin's sitemap API or schema graph matches WordPress/WooCommerce.

## Isolated IRT-to-IRR schema adapter

WooCommerce remains the only Product schema generator. On `woocommerce_structured_data_product` at priority 999, the adapter operates only when the actual WooCommerce store currency is `IRT` and `wpmain_catalog_toman_schema_enabled` is true. A separate currency/schema integration must disable this filter to take ownership.

For schema money nodes declared as IRT or inheriting IRT from a containing `priceCurrency`/`currency`, it multiplies supported decimal amounts by ten using string arithmetic and changes the matching currency to IRR. Supported fields are `price`, `lowPrice`, `highPrice`, `minPrice`, `maxPrice`, and `value`/`minValue`/`maxValue` on typed `MonetaryAmount` nodes. This covers native simple/list/sale/aggregate offers, nested price specifications and typed shipping charges. Converted descendants receive an explicit currency when needed. Explicit USD/IRR/other currency branches stay untouched. No currency is guessed when schema has no declared context.

Counts, measurements, reference quantities, shipping times, percentages and SKUs are unchanged. Malformed, conflicting-currency and deeply nested/cyclic data are left unchanged rather than guessed. Monetary values must be non-negative plain decimal strings or ordinary finite numeric values; scientific notation and formatted currency text are unsupported. Conversion is idempotent: a second pass sees IRR and does not multiply again. A partly malformed money node keeps its original amounts/currency together. The adapter does not affect display/database prices, orders, feeds, gateway payloads or other plugins' independently generated graphs.

## Validation and deployment gates

Run `node scripts/test-seo.mjs` for offline PHP behavioral tests covering all five SEO-owner detections, public/protected metadata, escaping, query/canonical/pagination policy, sitemap exclusions, exact large-decimal conversion, nested sale/aggregate/shipping money, mixed currencies, quantities, malformed values and idempotence. Controlled WordPress API doubles exercise behavior; they do not certify live output or search appearance.

Before launch, inspect deployed head metadata, the core/selected-owner sitemap, robots settings and Product JSON-LD for real simple, variable, sale, unpriced and unavailable products. Validate with Google's Rich Results Test and Search Console. Confirm every monetary amount/currency pair and extension-added node. Test the gateway's toman/rial contract independently and configure feed currency in its own owner. Check descriptions, imagery and archive/search/filter indexing with the owner. No rich-result eligibility or ranking outcome is guaranteed.

References: [WordPress robots API](https://developer.wordpress.org/reference/functions/wp_robots/), [pagination URL API](https://developer.wordpress.org/reference/functions/get_pagenum_link/), [native sitemap query filter](https://developer.wordpress.org/reference/hooks/wp_sitemaps_posts_query_args/), and [Google product merchant listing requirements](https://developers.google.com/search/docs/appearance/structured-data/merchant-listing).
