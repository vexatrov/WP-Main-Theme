# Native editor controls

WP Store Catalog 0.7.0 replaces routine ID entry with native image selection and searchable ordered picks. Saved keys and existing values are retained. These scripts/styles load only in supported WordPress admin/Customizer screens; they add no storefront dependencies.

## Sharing images

Open **Search and sharing** in a product, article, page or taxonomy editor. **Choose image** opens WordPress's own image library. The selected image shows a thumbnail and title. **Remove image** restores the automatic sharing-image fallback after saving. Existing attachment IDs remain compatible; no image is deleted when removing a selection.

## Product and guide relationships

- Product editor → **Product guidance**: existing WooCommerce searches select compatible products, same-use alternatives, comparison products and buying guides. Their established limits remain unchanged.
- Product category editor → **Related buying guides**: search published, unprotected articles by name, choose up to four, reorder with the earlier/later buttons, then save the category.
- Article editor → **Article product recommendations**: select up to four visible published products. They appear after the article using the existing public-product renderer. Leave this selection empty when you already placed product shortcodes in the article. Existing shortcodes retain their custom placement and behavior.
- In the block editor, open the native **Metadata / متاباکس‌ها** drawer below the canvas to access the panels. Its native separator supports keyboard resizing; it does not need a page builder.

Enter at least two characters to search. Up/down arrows navigate result buttons; Enter selects; Escape closes/cancels results. Selection order is explicit, with named remove/reorder controls and announced status/error messages. Empty selections retain each feature's existing automatic behavior or remove optional article recommendations. Save/publish through WordPress normally.

## Homepage

Appearance → Customize → **Storefront homepage** now has named **Homepage categories** and **Homepage products** selectors. Select/reorder up to six categories or eight catalog products. Blank lists retain the existing automatic category/product source. Section order uses a named dropdown instead of comma-separated internal keys. Hero media continues to use WordPress's native media control.

## Data and safety

Article choices use `_wpmain_article_products`; category guides retain `_wpmain_category_guides`; homepage controls retain their existing theme modifications; sharing images retain `_wpmain_seo_image`. Admin search requires an authenticated, authorized role and a nonce; object kinds are allowlisted, queries capped at 20, and public results exclude passwords and hidden products. Saves retain existing nonce/capability/sanitization protections. Partial or malformed article/guide submissions preserve selections; article autosaves/revisions cannot erase them. Sharing images must be real library images. Frontend rendering independently checks product visibility.

Without admin JavaScript, ID inputs remain available as a fallback. Do not remove WordPress/WooCommerce admin assets globally: existing native product search, media and block-editor save flows depend on them. New controls initialize on DOM readiness and the Customizer's ready event without rebuilding already initialized controls.
