# Storefront roadmap

The previous non-WordPress PMU site is a functionality reference. It does not define these stores' market: both tattoo brands are primarily body tattoo B2C; the third sells lash/makeup tools. All three share an engine while brand presentation remains independent. This list records the reference features, not a claim that they have all been built or must ship together.

See RESEARCH-2026-10-24.md, STOREFRONT-PLAN.md and PAGE-LAYOUTS.md for the researched proposal, launch priorities, and complete page-family discussion list. Local references are inputs to an original design, not a full design specification.

## Current review milestone

- Shared classic theme and WooCommerce hook integration.
- Static homepage with editable hero, live category cards and latest products.
- Responsive header/menu, product search, account and cart links.
- Persian theme translations and locale-dependent RTL.
- Real local WordPress/WooCommerce preview with clearly labeled sample products/artwork/prices.
- Native WooCommerce product, gallery, account, cart and checkout behavior. Actual gateway/shipping configuration and production verification remain required.

## Navigation and merchandising reference

- [ ] Separate catalog entry points where each store's real category tree calls for them (no mandatory PMU-first navigation).
- [ ] Brand-based hero carousel with grouped products and artwork; prefer a static hero until multiple campaigns justify a carousel.
- [ ] Dedicated category illustrations.
- [ ] Category sliders with faded next-item preview and keyboard support.
- [ ] Expandable category menus with child panels.
- [ ] Category sections following parent/child hierarchy.
- [ ] Brand directory with verified agency labels where applicable.
- [ ] Dedicated brand pages with category filtering.
- [ ] Combined category, brand and attribute filters.
- [ ] Category counts including products in child categories.
- [ ] Persian buying guidance and relevant category links on the shop page.
- [ ] Mobile shortcuts to category sections, brands and product listing.
- [ ] Brand-specific decorative artwork.

## Search and product information reference

- [ ] Persian character normalization and brand aliases.
- [ ] Brand-aware searches (earlier examples: «پن مست» and «دستگاه ایزد»); confirm real brands/catalog aliases before implementation.
- [ ] Structured specifications appropriate to each store's inventory.
- [ ] Product specification comparison.
- [ ] Compatible-product recommendations on supported products.
- [ ] Related products (native WooCommerce available; refine relevance for real inventory).
- [ ] Product recommendations inside blog articles.
- [ ] Product-specific FAQs.
- [ ] Validate variable product price, SKU and image selection with actual variants (native WooCommerce available).
- [ ] Validate galleries and zoom with real photography (native WooCommerce support declared).
- [ ] Enquiry pricing for intentionally unpriced products.

## Contact, accessibility and operations reference

- [ ] Phone/WhatsApp paths with confirmed contact information.
- [ ] Optional studio/wholesale enquiry messaging, secondary to B2C checkout.
- [ ] Relevant Instagram links per store; references saved in BRANDS.md, no assumed shared social footer.
- [ ] Full keyboard verification for menus, sliders and filters as they are introduced.
- [ ] Catalog administration for products, specifications, compatibility, FAQs, redirects, media and blog content. Reuse WordPress/WooCommerce admin and REST APIs; add a custom plugin only for missing functionality.
- [ ] Analytics selected and configured for the actual hosting/measurement requirements; none installed by default.

## Implementation boundaries

Theme: presentation, navigation and responsive components. Child themes: identity, artwork and layout variation. A separately versioned functionality plugin: durable product metadata, advanced search, compatibility, comparison and FAQs. WordPress/WooCommerce: content, catalog, variants, inventory, customer accounts, orders and commerce APIs. Test changes in the local preview, then in persistent hosting staging with real integrations before public launch.
