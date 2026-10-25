# Product purchase information and availability

The shared theme adds purchase guidance through WooCommerce summary hooks in `inc/product-purchase.php`. The normal WooCommerce gallery, price, stock handling, simple/variable purchase forms, and variation scripts remain responsible for product purchasing. No checkout or payment behavior is added.

The summary can show up to four product attributes marked **Visible on the product page** in Product data > Attributes. Empty values are skipped. It also links the first assigned term from WooCommerce's `product_brand` taxonomy when that taxonomy and an actual term are available.

An in-stock product with no price receives an enquiry prompt only when the site has a confirmed contact page: either the `wpmain_contact_page` theme setting points to a published page, or a published page uses the theme's `templates/page-contact.php` template. The theme does not guess contact details. If neither source finds a page, the prompt is omitted.

An out-of-stock product gets a separate recovery prompt and a link to its first assigned product category. If no category is assigned, the link falls back to the WooCommerce shop page when configured. WooCommerce continues to render its native stock status. This state takes precedence over the unpriced prompt.

The purchase summary styles live in `assets/css/product-purchase.css`; the theme's asset loader should enqueue this file on single-product pages. Styles use existing brand tokens, preserve WooCommerce's native forms, and give variation selectors, quantity input, purchase actions, and recovery links a 44px minimum target height.

## Storefront strings

- Key specifications
- Brand
- Looking for an available option?
- Browse available products
- Contact us for current availability and pricing.
- Ask about this product

Strings use the `wp-main-theme` text domain and ship with Persian translations. Real localhost WordPress HTTP checks cover unpriced, unavailable, sale and variable fixtures, and native cart tests exercise stock/variation rejection and selected variation purchase. Current browser visual and real gateway checks remain release gates in COMPLETION.md.
