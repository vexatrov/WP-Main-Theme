# Product detail controls

The parent renders native WooCommerce product information and cards. Child overrides of `template-parts/product/*` continue to resolve normally; comparison overrides still receive the established `products` argument with the current product first. No WooCommerce templates are copied.

## Specifications and comparisons

The native Additional information tab is labeled **Specifications** (**مشخصات** in Persian). Visible attributes, weight/dimensions and WooCommerce's native rendering remain the source of truth.

The comparison tab offers a horizontal rail of selectable product thumbnails. Editor-selected comparison recommendations appear first, followed by curated alternatives and newest products from the current product's most-specific assigned category. The category is chosen by ancestry depth, then the lowest term ID for a stable tie. Category membership supplies comparison candidates; it does not create a compatibility relationship. The rail has at most twelve public catalog products. Current product is never a checkbox choice.

Up to three choices plus the current product appear as columns with images, price, availability and visible attributes. Curated comparison products are selected by default. Submit **Update comparison** to replace them using the read-only `compare_product[]` GET parameter. An empty submission compares only the current model. Selection does not change product metadata or create a cart/order. If more than three boxes are checked, the first three valid choices in submitted order are retained. The form works without JavaScript, including plain permalinks.

Every submitted ID must belong to the bounded candidate set. Draft/private, catalog-hidden, password-protected and WooCommerce-hidden records cannot expose names, images, prices or availability through a forged request, including when an administrator views the page. Native catalog out-of-stock visibility rules apply. Changing to a different category/model may therefore change the available candidate set. Unknown/malformed IDs are ignored.

## Compact recommendation groups

Three independent groups sit immediately after the main purchase area, before the detailed tabs:

- **Works with this product:** editor-verified compatible products only.
- **Other options for the same use:** editor-selected alternatives. Its heading links to the product's most-specific category archive.
- **More from [brand]:** up to four other public products from the first assigned native brand; its heading links to that brand archive. This group does not claim compatibility or equivalent use.

Empty groups stay hidden. Each uses native WooCommerce cards in a horizontal scrolling rail, preserving images, prices and purchase actions. Rails are keyboard focusable and support touch/trackpad scrolling; they have no autoplay or additional JavaScript. Groups sit alongside each other on desktop and stack below 64rem. Existing curated-alternative suppression of generic related products remains; native related products still provide the fallback when no visible alternatives exist. Buying-guide links follow the groups.

## Shared store information boxes

Open **Appearance → Customize → Product store information → Product information boxes**. Enter up to four plain-text messages, one per line. They appear on every public product under the purchase controls. Blank default settings display no boxes. The active child theme stores its own setting, so each brand can use its own delivery/support wording.

The help text includes the owner's supplied examples: قیمت‌ها به‌روز هستند / ارسال یک‌روزه تهران، دو‌روزه سایر شهرها / ضمانت اصالت کالا — ۱۰۰٪ اورجینال / گارانتی و پشتیبانی. The field stores the owner's actual copy; there are no default guarantees, inferred delivery dates or icons that invent policy. Markup is stripped, empty lines removed and output escaped. The fictional local fixture may configure sample messages separately; those are not production settings.

## Validation

`tests/product-details.php` checks bounded candidates and selections, visibility/password protection, native query exclusions, category choice, selected/default images and checkbox markup, Specifications title, and blank/sanitized/escaped store information. Run through `node scripts/test-php.mjs`; CI also invokes the contract directly on PHP 8.1 and 8.3.

These isolated contracts do not certify browser layout or a booted shopping session. On the store preview, inspect the three groups at desktop/mobile widths, long Persian names and prices, keyboard access to scrolling/checkboxes, a no-JavaScript comparison submission, private/hidden records and the product's active native tabs. No product data or preview settings need to be mutated to run the offline contract.
