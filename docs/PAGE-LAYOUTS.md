# Page/layout discussion inventory

The current shared release candidate and verification status are recorded in COMPLETION.md. The planning inventory below remains useful for brand-specific review; older progress notes are historical.

Discuss families once, then approve each brand's visual variant. A shared listing template can serve many URLs; every row does not mean a new custom PHP file. This is a planning inventory, not a completed-feature checklist.

| Order | Page or component family | Layout decisions |
| --- | --- | --- |
| 1 | Global header/navigation/search overlay | Expanded/compact states, mobile nested menu, search suggestions, account/cart/support placement; glass only for the chosen child brand. |
| 2 | Global footer | Short About, service/policy links, actual contact/social routes, mobile organization. |
| 3 | Shop / all products | Catalog introduction, filter/sort toolbar, grid density, pagination or load-more, restoring position. |
| 4 | Parent category / category hub | Category artwork, child-category navigation, overview and optional product preview. |
| 5 | Leaf product category | Relevant facet filters, applied chips/counts, product listing and compact buying guidance. |
| 6 | Single product | Gallery, purchase panel, selected variation, specifications, FAQ, reviews and recommendations; different data sets for machines/cartridges/inks/beauty tools. |
| 7 | Search results | Query suggestions, mixed category/brand/product matches, filtering, correction and zero-result recovery. |
| 8 | Brand directory | A–Z or searchable brand list, logos, verified agency information where applicable. |
| 9 | Single brand | Brand introduction, relevant categories and scoped product listing; reuse filter system. |
| 10 | Homepage | One composition per brand: editorial/static, product-led or campaign slider; section order and primary routes. |
| 11 | Cart and mini-cart | Confirmation, variant/quantity editing, coupon, totals/shipping estimate, empty cart and checkout path. Mini-cart is optional; full cart is essential. |
| 12 | Checkout | Guest/account choice, contact/address/shipping/payment, order summary, inline errors and mobile keyboard behavior. |
| 13 | Order confirmation and payment-result states | Paid/pending/failed/cancelled, retry/resume, receipt and support; no duplicate order on retry. |
| 14 | Order tracking | Secure lookup, fulfilment status, carrier link and recovery for incorrect details. |
| 15 | Sign-in / registration / password reset | Authentication method, guest shopping, validation, confirmation and recovery states. OTP only if chosen and integrated. |
| 16 | My Account | Dashboard, order history/detail, addresses, profile/security; downloads only if the store sells downloadable items. |
| 17 | Blog / magazine index | Featured articles, topics, consistent cards and pagination. |
| 18 | Blog category archive | Topic introduction, article grid and category navigation; tags/author archives only if editorial needs justify them. |
| 19 | Single blog article / buying guide | Title/author/date, optional contents, reading width, media, related products, related articles and comments if enabled. |
| 20 | About | Real brand story, service, people/location where relevant, trust and useful next routes. |
| 21 | Contact | Confirmed phone/WhatsApp/email/hours, location where relevant and optional working contact form. |
| 22 | Help / FAQ / buying guide hub | Searchable or grouped support questions with links into policies/catalog. Product-specific FAQs remain on the product. |
| 23 | Shipping / returns / warranty | Clear, readable policy sections and useful contact paths; separate URLs or one hub as the business requires. |
| 24 | Privacy / terms / other required policies | Shared readable document layout, owner-reviewed content and accurate links from checkout/footer. |
| 25 | New arrivals / sale / collection / campaign | Reuse product listing for ordinary collections; a distinct campaign landing page only when useful. |
| 26 | Comparison (optional) | Same-family specifications, differences, mobile contained scrolling and replace/remove controls. |
| 27 | Wishlist (optional) | Save/remove, stock/price changes, guest persistence and account transfer only if justified. |
| 28 | System/recovery layouts | 404, empty category/search/cart, unavailable/discontinued product, form/network errors and maintenance/coming-soon where actually needed. |

## Required states are part of each layout

Design loading, empty, long-text, missing-image, error, success and keyboard/focus states. For products include simple/variable, sale, enquiry and out-of-stock. For checkout include payment return/retry; for account include unauthorized access. These are not extra marketing pages, but must not be left to accidental default styling.

## Recommended first discussion

Begin with the **single product and category/listing layouts**. They define the cards, information hierarchy, product attributes and buying decisions used across all three stores. Approve the global header/footer alongside them, then design each brand's homepage around those established components.

## Can all of this be redesigned?

Yes: `header.php`, `footer.php`, `single.php`, archive/page templates and template parts are ours to design in the parent/child theme. WooCommerce's classic product/archive layout can be reorganized through hooks and selective `woocommerce/` template overrides, while retaining its product objects, variation forms, stock rules, cart actions and extension hooks. A full visual redesign does not require Elementor or replacing WooCommerce's backend.

Block Cart/Checkout uses its own styling and extension APIs. Its redesign must respect supported extension points and the chosen gateway; a classic PHP override alone will not change a block checkout. Track any overridden WooCommerce template version and validate it after plugin updates. Transactional email layouts are another separate deliverable if we want them branded consistently.

## Shared implementation progress — 0.6.0

Owner direction: finish the main theme before working on any brand identity. Zarinpal is the likely gateway, but integration is deferred at the owner's request.

Implemented foundations: responsive sticky header/footer; square aligned product cards; native product gallery/purchase/variation behavior; curated product guidance/comparison/FAQ; category-navigation links; configurable native WooCommerce filter area; native brand archive with a new brand-directory page template; magazine grid; article header/byline/reading column/related articles; homepage guide cards; About/Contact and Help/policies templates; native account/order/cart styling. The demo includes help, brands and a native secure order-tracking form.

Still pending: Persian search normalization/alias relevance and suggestions; category-specific facet configuration against real inventory; richer category hubs; article contents navigation; optional wishlist and interactive comparison selection; mini-cart if selected; enquiry-price contact behavior; launch validation of real variable products, payment-result states and gateway integration. Existing native WooCommerce behavior is not a claim that these custom experiences or real payment flows are finished. Brand-specific heroes, glass effects and identity work remain deferred.

Setup: use native product brands and assign the Brand directory template to a page. In Appearance > Widgets, add WooCommerce price/attribute/active-filter widgets to Shop filters. The filter disclosure is keyboard accessible; WooCommerce owns filtering and query parameters. The theme does not invent product counts, filter values or verified agency badges. Add Details blocks to Help/policies pages for expandable answers; owners supply policies. Product recommendations in articles can use native WooCommerce product shortcodes in a Shortcode block.

## Catalog iteration — 0.8.0

The catalog renders category thumbnails where configured, child routes for parent categories and sibling routes on leaves. It adds a single-brand selector using WooCommerce's native `filter_product_brand` parameter; native price/attribute widgets stay configurable under Shop filters. Brand options are populated catalog-wide, so some category/brand combinations can legitimately have no matches. Active query filters keep the disclosure open; Clear filters removes native facet/price parameters and resets pagination while retaining search/sort and the current archive. Empty listings provide a product-search form and all-products route. No invented product counts or custom filtering SQL.

Remaining catalog work: category-specific attribute setup against real inventory, richer editable buying guidance, scoped facet availability and counts, and a fresh visual/accessibility review. No JavaScript-driven instant filtering has been added; forms and URLs work without custom JavaScript.
