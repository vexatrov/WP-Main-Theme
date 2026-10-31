# SEO ownership and toman schema

The [2026-10-31 SEO/performance audit](SEO-PERFORMANCE-AUDIT-2026-10-31.md) records current source/runtime evidence and known gaps, including incomplete description sources for visible homepage/shop/archive copy. The capabilities below do not imply complete production optimization.

WP Store Catalog supplies a small fallback SEO owner when Yoast, All in One SEO, Rank Math, SEOPress and The SEO Framework are absent. `wpmain_catalog_seo_enabled` lets the deployment's selected integration disable this fallback. Detection happens when hooks run, after plugins load.

## Metadata and discovery policy

The fallback emits descriptions and Open Graph/Twitter metadata from public, published, unprotected post excerpts/content, term descriptions or the real site tagline. It strips markup/shortcodes without executing content filters and escapes attributes. Product/post thumbnails and an installed custom logo are the only image sources. Password-protected, private, draft and missing posts receive no fallback metadata. Search, error and commerce utility pages do not receive sharing metadata.

WordPress retains singular canonical ownership. For home/archive routes, `get_pagenum_link()` supplies the canonical URL, preserving the page number and plain-permalink route/taxonomy variables while removing filters, sorting and tracking parameters. Archive page 2 stays canonical to page 2 and indexable. Existing robots restrictions are never removed.

The `wp_robots` filter adds `noindex` for search, 404, Cart, Checkout and My Account, plus URLs containing `filter_*`, `query_type_*`, `min_price`, `max_price`, `rating_filter`, `stock_status`, `on_sale`, `orderby` or `products_per_page`. Ordinary taxonomy/brand routes remain indexable. Native WordPress sitemap queries exclude assigned Cart, Checkout and My Account page IDs while preserving earlier exclusions and product/category discovery. Do not block these URLs in robots.txt before crawlers can see their noindex directives.

When another SEO owner is detected/selected, it owns metadata, canonicals, robots and sitemap policy. Confirm equivalent settings there; this plugin does not assume another plugin's sitemap API or schema graph matches WordPress/WooCommerce.

## Isolated IRT-to-IRR schema adapter

WooCommerce remains the only Product schema generator. On `woocommerce_structured_data_product` at priority 999, the adapter operates only when the actual WooCommerce store currency is `IRT` and `wpmain_catalog_toman_schema_enabled` is true. A separate currency/schema integration must disable this filter to take ownership.

For schema money nodes declared as IRT or inheriting IRT from a containing `priceCurrency`/`currency`, it multiplies supported decimal amounts by ten using string arithmetic and changes the matching currency to IRR. Supported fields are `price`, `lowPrice`, `highPrice`, `minPrice`, `maxPrice`, and `value`/`minValue`/`maxValue` on typed `MonetaryAmount` nodes. This covers native simple/list/sale/aggregate offers, nested price specifications and typed shipping charges. Converted descendants receive an explicit currency when needed. Explicit USD/IRR/other currency branches stay untouched. No currency is guessed when schema has no declared context.

Counts, measurements, reference quantities, shipping times, percentages and SKUs are unchanged. Malformed, conflicting-currency and deeply nested/cyclic data are left unchanged rather than guessed. Monetary values must be non-negative plain decimal strings or ordinary finite numeric values; scientific notation and formatted currency text are unsupported. Conversion is idempotent: a second pass sees IRR and does not multiply again. A partly malformed money node keeps its original amounts/currency together. The adapter does not affect display/database prices, orders, feeds, gateway payloads or other plugins' independently generated graphs.

## Validation and deployment gates

Run `node scripts/test-seo.mjs` for offline PHP behavioral tests covering all five SEO-owner detections, public/protected metadata, escaping, query/canonical/pagination policy, sitemap exclusions, exact large-decimal conversion, nested sale/aggregate/shipping money, mixed currencies, quantities, malformed values and idempotence. Controlled WordPress API doubles exercise behavior; they do not certify live output or search appearance.

Before launch, inspect deployed head metadata, the core/selected-owner sitemap, robots settings and Product JSON-LD for real simple, variable, sale, unpriced and unavailable products. Validate with Google's Rich Results Test and Search Console. Confirm every monetary amount/currency pair and extension-added node. Test the gateway's toman/rial contract independently and configure feed currency in its own owner. Check descriptions, imagery and archive/search/filter indexing with the owner. No rich-result eligibility or ranking outcome is guaranteed.

References: [WordPress robots API](https://developer.wordpress.org/reference/functions/wp_robots/), [pagination URL API](https://developer.wordpress.org/reference/functions/get_pagenum_link/), [native sitemap query filter](https://developer.wordpress.org/reference/hooks/wp_sitemaps_posts_query_args/), and [Google product merchant listing requirements](https://developers.google.com/search/docs/appearance/structured-data/merchant-listing).
