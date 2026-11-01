# Header and unified product specifications — rc.7

## Header

The shared header shows a logo/neutral fallback mark together with the site name, account/cart actions, left-hand search, optional social icons and visible desktop destinations. Compact desktop scrolling retains the page/category navigation. Mobile uses a clearly labeled menu and separate 44px child-category disclosure buttons; ordinary parent links still open their category.

Use Appearance → Menus → **Primary navigation** for products/categories, with child/grandchild items nested explicitly in the menu. Desktop pointer hover and keyboard focus expose submenus; click/touch toggles are available separately. Escape closes a branch without reopening it when focus returns to its button. Desktop dropdowns shift back within viewport edges. Child links are not invented from every taxonomy descendant: they must be assigned in the native menu.

The optional **Header pages** menu location controls informational destinations. Without it, the header links actual published unprotected Shop, Brands, Journal, About and Contact pages plus Home; URLs already in the primary menu are omitted. Missing pages are not linked. Appearance → Customize → **Header and social links** supplies Instagram, Telegram and WhatsApp URLs. Blank fields hide their icons. WordPress Site Identity supplies the logo; the name remains visible alongside it.

The fictional preview's guarded `dev/header-preview.php` adds two real child-category menu items and generic Instagram/Telegram platform URLs to demonstrate the controls. These are not claimed brand profiles, and the fixture never ships in production packages. New/resumed preview startup includes it; existing choices are not overwritten.

## Specifications and comparison

There is one **Specifications & comparison** tab, retaining WooCommerce's `additional_information` key. The separate comparison tab and instructional paragraphs are removed. A concise search label, illustrated choices, selection counter and Compare button provide the controls. Current/suggested labels identify products without long explanatory copy.

Search uses the companion plugin's public `/wpmain/v1/comparison-search` route and existing stored Persian normalization/aliases. Results are capped at six; published status, passwords, catalog/search visibility and the current-product exclusion are enforced server-side, including privileged requests. Users can select up to three public products outside the initial suggestions. Submitted IDs are revalidated independently, so forged private/hidden IDs cannot expose names, images or values.

The GET form remains usable without JavaScript: submit `compare_q` to find products, select their checkboxes and submit again. JavaScript adds debounced/abortable search, stale-response guards, selected-item retention, a three-item cap and keyboard access. Comparison URLs remain noindex. The old `#tab-wpmain_comparison` fragment opens the unified tab through a small compatibility handler; new forms target `#tab-additional_information`.

The current product's visible attributes and native weight/dimensions appear first, with `woocommerce_display_product_attributes` filtering retained for extensions. Suggested comparison products begin unchecked. Only after a shopper selects and submits a product does the comparison table appear below the search and choices, showing price and specifications for the current and selected products. Attribute names/units are not inferred or silently reconciled across unrelated products. Missing entries use a dash. The comparison table uses start-aligned cells and keeps row labels sticky during mobile horizontal scrolling.

Shared header and product-only comparison assets use existing brand tokens; no framework, page builder, remote font or global WooCommerce asset removal is introduced.
