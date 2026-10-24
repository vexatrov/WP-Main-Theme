# Shared storefront plan

Status: proposed implementation plan for layout discussion, 2026-10-24. Research is in RESEARCH-2026-10-24.md. Page inventory and discussion order are in PAGE-LAYOUTS.md. This document does not mean the planned features have already been implemented.

## Direction and boundaries

Build three distinct, professional B2C stores using one lean WordPress parent theme, three brand child themes and WooCommerce. Both tattoo stores primarily sell body tattoo supplies; the beauty store sells lash/makeup tools. Persian/RTL and tomans are the current defaults. Do not force PMU, wholesale messaging, a color-mode toggle, glass styling or a carousel onto every store.

The shared layer defines behavior, accessibility and component contracts. The brand layer controls typography, colors, header treatment, shapes, illustration/photo direction and page composition. A small functionality plugin should eventually own durable specifications, search aliases, compatibility, FAQs and comparison data. WooCommerce owns product types, variants, stock, orders, customer accounts and commerce calculations. Reuse its native brand support and attributes before adding custom equivalents.

Nothing should precede the header: the development notice has been removed. Keep preview/sample disclosures in development documentation and sample product content, not an extra storefront bar.

## Four shopping tasks to design around

1. Known purchase: search a brand/model/SKU → choose a valid variant → buy.
2. Browse: select a category → narrow by relevant attributes → compare a small shortlist → buy.
3. Need guidance: read a category guide or article → follow relevant products → understand compatibility/specifications → buy.
4. Return visit: find an existing order, track delivery, get support or reorder without navigating the whole catalog.

These tasks are hypotheses to validate with representative customers of each brand. Test both experienced artists and newer buyers; B2C includes practitioners purchasing for themselves.

## Launch essentials and later options

| Priority | Include | Completion evidence |
| --- | --- | --- |
| P0: dependable buying | Navigation/search; category hierarchy; useful filters; accurate product/variant information; toman pricing; gallery; cart/checkout; payment states; guest purchase; contact/policies; account/order routes. | A shopper finds an item, selects the right available variant, sees the full total, completes a sandbox order, returns from success/failure/cancel and can find support. |
| P1: specialist help | Brand directory/detail; Persian normalization/aliases; structured specifications; product FAQs; editorial/product links; explicit compatibility where verified; useful related items. | Catalog data is complete and maintainable, search matches agreed queries and compatibility is supported by real evidence. |
| P2: selective enhancements | Comparison, wishlist, recently viewed, saved carts, back-in-stock alerts, curated kits and quick reorder. Brand hero slider if it improves a specific campaign. | Confirm actual demand, ownership of notifications/data and performance cost before implementation. |

No account wall before buying, fake reviews, fabricated stock pressure, unsupported agency badges, perpetual countdowns or default autoplay video. Do not hide essential product facts inside a WhatsApp conversation. Enquiry pricing is for deliberately unpriced items, not the default B2C flow.

## Catalog structure and filters

Suggested tattoo starting groups: machines; cartridges/needles; inks; power/batteries/cables; stencil/printers; practice; hygiene/aftercare; accessories. Validate against actual stock; combine sparse groups instead of filling empty menus. Separate product brands from product categories. Keep cartridge configuration, needle diameter, pack quantity and compatible system as attributes rather than dozens of duplicate category/brand intersections.

For the beauty shop, confirm actual inventory first: likely lash tools/tweezers, application/cleanup tools, makeup brushes/applicators and consumables, with lashes/adhesives included only if sold. Relevant attributes can include purpose, tip shape, curl/length/thickness or adhesive working conditions where those categories exist. Never show tattoo-machine filters in the beauty shop.

Filter behavior: OR within one facet, AND across different facets; show applied chips, clear-all and result counts; retain state in URLs and browser history; back navigation restores position. Count descendants without double-counting the same product. A variable product is one catalog card, not a count for each variation. Use defined numeric units consistently. Explain unfamiliar abbreviations. Decide stock visibility deliberately and offer recovery for zero results.

## Search

Normalize Persian/Arabic ی/ي and ک/ك, Persian/Arabic/Latin digits and common whitespace variants. Preserve the original displayed title/SKU. Maintain explicit brand/model aliases such as مست/Mast and ایزد/EZ only after confirming catalog mappings; do not infer compatibility from a name match. Weight exact SKU/model and relevant title matches above generic description matches.

Suggestions may group products, categories and brands with keyboard navigation and a full results link. Debounce/cancel obsolete requests. Search must still work as a submitted form when enhanced suggestions fail. Treat no-result, slow/loading, network-error and partial matches as designed states. Avoid requiring an external search service until catalog size or relevance measurements justify one.

## Product page: proposed foundation

Desktop: breadcrumbs → gallery and purchase information in two columns → key specifications → full details/compatibility → FAQ/reviews → related or complementary products. The media/product information order is mirrored thoughtfully for RTL; English model names and SKU strings use directional isolation.

The purchase area should show title, brand, concise purpose, actual stock, selected variant, toman amount, quantity and one dominant add-to-cart action. Put delivery/returns/warranty summaries close to the decision, linked to their actual policies. Show a real review summary only when data exists. Distinguish price-per-piece and box price/quantity when applicable.

Mobile: gallery, title/brand, price/stock, variant selection, purchase action and essential specifications in a readable sequence. Optional sticky buy controls appear after the original purchase action leaves view, and must not cover variant errors, the keyboard, cookie controls or the footer. Opening a required variant choice takes priority over prematurely adding an item.

Data variants, not an identical specification table for every product: machines need stroke/power/weight/system; cartridges need grouping/diameter/taper/pack size; inks need shade/volume/set contents; beauty tools need material/shape/purpose or relevant sizes. Use only verified supplier facts. Product-specific FAQs must answer real purchase questions. Related products and compatible products are distinct relationships.

Required states: simple/variable; selection incomplete; unavailable combination; out of stock; sale/price range; enquiry price; missing image; long title; insufficient stock; add failure; add success. Update selected price, SKU, image and availability through WooCommerce's variation system.

## Listing and homepage layouts

A category hub helps shoppers choose a child category; a leaf category focuses on filtering and products. The shop is the full catalog, not a second marketing homepage. Brand pages reuse listing behavior with brand context and scoped categories. Campaign/new-arrival/sale lists should reuse the listing system unless a campaign genuinely needs a distinct layout.

Cards: controlled image ratio/contain surface, consistent scale, two-line visible title, useful brand/variant cues, stock/price and a consistent action area. Keep complete text available to assistive technology. Use a product link or variant choice when quick add would be ambiguous. Preserve image dimensions before loading. Align within each row; do not enlarge the final lonely card to fill the entire row.

Homepage framework: brand-specific hero → clear category routes → one or two purposeful product collections → useful brand discovery → a relevant guide/article → brief brand story/footer. Sequence can change per brand. Avoid a long stack of near-identical carousels and promotions. A product-led hero, an editorial image hero and an accessible campaign slider are separate child-theme compositions, not three global scripts loaded everywhere.

## Header, footer and navigation

Shared header: recognizable identity, product search, catalog access, cart and reachable account/support paths. Compact mode stays useful; essentials remain keyboard/touch accessible. Mobile drawers need a clear close/back action and nested labels; desktop navigation cannot rely on hover alone. Do not turn a full taxonomy dump into the visible top-level menu.

Glass treatment is a brand option with an opaque fallback and readable text over both bright and dark imagery. Test the actual blur cost on phones. Shape tokens cover buttons and inputs independently; each brand may also override the markup. The shared parent remains visually neutral.

Footer: short genuine introduction with About link, customer-service links, confirmed contact/social information and policy routes. Add trust/payment logos only with valid permission and supported integrations. Keep the footer navigable on phones. It supplies useful context, not a guaranteed SEO gain.

## Editorial and trust

Blog hub and category archives should expose useful topics. Single articles need a readable column, author identity, accurate dates, a table of contents when useful, clear headings, captioned images, relevant product cards and related reading. Do not force a sidebar or large product promotion into every article. Product and blog categories are separate systems. Article layout is coded; article content remains editable in WordPress.

About explains the actual brand and service. Contact presents confirmed phone/WhatsApp/email/hours and location only where applicable; include a real form only after choosing delivery/spam handling. Shipping/returns/warranty/FAQ content must match the business's actual operations. Policy drafting needs owner review; competitor wording is not a substitute.

## Checkout, post-purchase and operations

Use the native cart/checkout as the reliable base, with a clearly visible guest path, editable quantities/variants, early shipping estimates, explicit toman totals and helpful inline errors. Select the real gateway/shipping methods before finalizing payment UI. Verify whether the gateway accepts IRT or expects IRR, and perform any required unit mapping exactly once in the integration.

Design success, pending, failed and cancelled payment states. Prevent duplicate paid orders on callbacks/retries; stock and confirmation must follow the gateway result. Order tracking must verify the shopper's entitlement instead of exposing arbitrary order details. Include order history/detail, account/address edits and password recovery. Transactional emails are a separate layout touchpoint, not a storefront page.

Launch setup: HTTPS, staging, restore-tested backups, working transactional email, cache bypass for carts/accounts/checkout, and no caching of another shopper's session. Confirm catalog import/data ownership and who updates stock/specifications. Keep this local preview disposable; use persistent staging for real content. Analytics should capture product discovery, add-to-cart and checkout outcomes without placing contact details or payment information into analytics payloads.

## Performance and accessibility acceptance

- Test 320/390/768/1024/1366/1440px plus intermediate resizing and 200% zoom; no page-level horizontal overflow. A comparison table may scroll inside a labeled region.
- Proposed custom-theme budget: CSS ≤30KB gzip and custom JavaScript ≤8KB gzip on a basic page, excluding WordPress/WooCommerce/vendor assets. Measure total page cost separately; third-party scripts need a stated purpose.
- Responsive WebP/AVIF where supported, sized images, reserved geometry; do not lazy-load the main hero/product LCP image. Load lower-priority media later. Self-host a licensed, carefully subset Persian font only when approved; default system fonts remain a valid fallback.
- Target real-user LCP ≤2.5s, INP ≤200ms and CLS ≤0.1 at the 75th percentile on mobile and desktop. Laboratory tests diagnose issues; the local PHP-WASM preview does not prove those targets.
- Keyboard/focus testing for navigation, dialogs, filters, variant selection and sliders; proper labels/errors, sufficient contrast, reduced-motion behavior and 44px primary touch controls as a design target. Sticky elements must not hide focus or content.

## Implementation sequence

1. Discuss the page families in PAGE-LAYOUTS.md and agree product/category data structure. Start with listing and single product; use realistic long titles, variable products and stock/price states.
2. Build shared listing/search and product presentation with theme hooks/template parts. Move durable specialist data into a small plugin; do not build a second catalog backend.
3. Finish cart/checkout/account and real payment/shipping integration in persistent staging; test errors as thoroughly as success.
4. Finalize editorial, About/Contact/help/policies and footer, then implement each brand's homepage/header/art direction.
5. Validate real catalog/media, analytics, accessibility and production performance. Launch one store first; roll the validated shared parent version to the others with separate brand review.

Open decisions for later discussion: actual product tree and stock, supported variants, useful filters, true brand promises/contact details, gateway/shipping, guest/account expectations, wishlist/comparison demand, and each brand's hero/header direction. These are not blockers to saving and iterating this plan.
