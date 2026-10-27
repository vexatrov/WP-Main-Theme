# Product search

The shared catalog plugin extends front-end product searches only. It normalizes common Persian/Arabic spelling and digit variations before matching product title, description, SKU and editable aliases.

- Arabic `ي`, `ى`, `ك`, `ة` and their common Persian equivalents.
- Persian, Arabic and Latin digits.
- Spaces, zero-width joiners and common separators such as hyphens.

This supports inputs such as `پن بيسيم` for a product named `پن بی‌سیم`, as well as an exact SKU. Product editors can enter one search alias per line in **Product data → Product guidance → Search aliases**. Use aliases only for confirmed brand names, model numbers and common customer spellings. Do not add broad terms that would produce irrelevant matches, and do not use aliases to imply compatibility.

Search retains a normal submitted WooCommerce form, with an optional native-JavaScript suggestions panel. Requests are debounced and obsolete requests cancelled. Suggestions group up to six products, three categories and three brands, use text-only labels and same-origin links, and support Arrow Up/Down, Enter, Escape and ordinary Tab navigation. Errors retain the submitted search fallback. Terms are capped at eight; the public suggestions endpoint limits input to 240 bytes. Category/brand suggestions now query stored normalized term documents, rather than scanning only the first 100 populated terms. Result counts are bounded; substring query cost still needs measurement on the intended MySQL catalog. See SEARCH-VALIDATION.md.

The plugin stores normalized documents in private product metadata on WooCommerce saves. Brand names/aliases are indexed for every assigned product, and visible variation SKUs are included. Matching uses a prepared EXISTS query with AND between words; exact parent SKU and title matches are ranked before other matches when the shopper has not selected another sorting method. Hidden/protected products are excluded from suggestions. WordPress still owns product visibility in the main result query. There are no per-request nested SQL normalization expressions.

Edit shared brand aliases under Products > Brands > Edit. Index version 2 refreshes product and term documents in batches of 50 after activation/upgrades and brand edits. WooCommerce > Search index displays status and provides a nonce/capability-protected rebuild button. WP cron or visiting that admin page advances the batches. Native post/meta updates and variation changes/deletions also refresh the relevant product documents. Rebuild after an importer that writes SQL directly. Until the initial rebuild finishes, existing products or terms may have incomplete search results. Typo correction and production catalog relevance tuning remain store-specific work.

`pnpm test:search` exercises Persian/Arabic letter variants, SKU, fixture aliases and a no-result query in the local preview.
