# Approved product card and detail foundation

User requirements recorded 2026-10-24. Two body tattoo B2C stores and the lash/makeup tools store share these behaviors; child themes can style or rearrange them independently.

## Product cards

- Square, full-width image with no default inner padding, frame, background surface or rounded image corners. The complete image is retained with `object-fit: contain`; actual product photography should be composed to fill its own square canvas. CSS cannot remove whitespace already inside a photo without cropping it.
- Product title, current price in tomans and the appropriate WooCommerce purchase action. Simple purchasable products can add to cart; products requiring a variant keep WooCommerce's choose-options behavior.
- Aligned card/image geometry, title area, price and action across a row. Full title remains in the accessible link text when its visual display is limited to two lines.
- Child variables `--product-image-padding`, `--product-image-surface` and `--product-image-radius` permit a deliberate boxed/image treatment for another design without changing the default shared behavior.

## Single product content

1. Gallery, title, short introduction, current price, stock, variants and purchase controls remain native WooCommerce functions.
2. Full description: native WordPress/WooCommerce rich content editor; can include images, lists and links.
3. Use cases and audience: editable paragraphs describing what the product is for and who it suits.
4. Specifications: native WooCommerce visible attributes, with consistent names and units across comparable products.
5. Specification comparison: the current model alongside up to three curated products. Shows product-level price/range, availability and a union of visible attributes. Missing values are a dash, not an invented match. The table scrolls in its own keyboard-focusable region on phones.
6. Product FAQs: up to six complete question/answer pairs. Native `details` controls; no FAQ rich-result claims or automatic schema injection.
7. Buying-guide links: up to four published, public blog articles selected through admin search. A needle can link to a needle guide; content remains in the original blog, not duplicated on the product page.
8. **Works with this product:** up to four independently selected compatible products; e.g. a pen's supported cartridges or a printer's supported papers.
9. **Other options for the same use:** up to four independently selected substitutes, including different brands, prices or specifications. This is a separate relationship from compatibility or comparison selection.

Empty sections are hidden. Unpublished, hidden or password-protected recommendations and private/password-protected guides are not rendered. Out-of-stock visibility follows WooCommerce's catalog rules. Curated alternatives replace generic related products when visible choices exist, avoiding a second competing generic list. Native related products remain the fallback if there are no visible curated alternatives.

Comparison is currently model-level, not a live selector that synchronizes several products' variations. Native purchase-panel variant updates continue to work. Weight/dimensions can still appear in native Additional Information; use normalized visible attributes for comparison rows. Dynamic multi-product variation comparison is a later enhancement if needed.

## Editing and installation

Install/activate WooCommerce, then install `plugins/wp-store-catalog` as the **WP Store Catalog** plugin. The package script produces `dist/wp-store-catalog.zip` alongside the parent theme ZIP. This small plugin owns the durable data, so changing themes does not lose the product relationships or guidance.

In the classic WooCommerce product editor, open Product data > Product guidance. Enter use cases/audience, search/select compatible items, alternatives and comparison products, select published guides and fill FAQ pairs. Use the normal Description, Short description and Attributes controls for their native content. Update the product normally. The custom admin panel and storefront sections have bundled Persian translations. Key-specification dropdowns select up to four existing visible attributes in display order. The new block-based product editor is not integrated in this iteration.

The theme renders through `inc/product-details.php` and child-overridable `template-parts/product/*`. No WooCommerce plugin template files are copied or modified. There is no new frontend framework, external recommendation service or per-product third-party request.

## Data quality

Compatibility is a verified editorial choice, not a claim inferred from shared keywords/categories. Alternatives should perform the same job; comparison choices should have useful overlapping specifications. Do not populate technical specifications from guesswork. The local pen/cartridge relationships and specifications are explicitly fictional review fixtures; they are not compatibility advice for actual equipment.

The plugin protects saves with edit permission and a nonce, caps selections, removes self/duplicate/wrong-type links and skips incomplete FAQs. Bulk/programmatic product updates without this editor's nonce do not erase existing guidance. Product metadata import/export mapping and REST editing are not implemented yet.

## Validation

- `pnpm test:products`: public demo page contains guidance, comparison, FAQ, published guide links and both recommendation groups; buying controls remain and IDs are unique.
- `php tests/catalog.php` (CI): ID constraints, malformed data, nonce/capability rejection and authorized save contract.
- `pnpm test:preview`: catalog/search, standard pages, tomans, add-to-cart and independent guest carts.
- Browser checks passed at 1440/390/320px: contained comparison scrolling, square edge-to-edge recommendation images equal to card width with zero padding, and no document overflow. The classic product editor displayed all selected relationships; guide search returned the matching published article. A normal Update returned the product-updated message and preserved relationship/guide selections.

Real variants, actual compatibility records, production content and payment gateways still require staging validation.

## Compact cards (0.7.0)

The shared catalog card now reserves two title lines and puts price beside a small 44px purchase control. Simple products retain native AJAX add-to-cart; variable products use the native product/option link, never an arbitrary variant. The icon retains WooCommerce's accessible action label. A differing variation price range uses two stacked amounts with one currency symbol; prices are calculated using WooCommerce tax-display rules. Single product purchase controls remain full-size. The demo ink product has 300,000/600,000 toman variations for inspection. `pnpm test:cards` checks the range and native variation data; it does not complete a variable-product checkout.
