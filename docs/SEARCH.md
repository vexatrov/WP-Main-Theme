# Product search

The shared catalog plugin extends front-end product searches only. It normalizes common Persian/Arabic spelling and digit variations before matching product title, description, SKU and editable aliases.

- Arabic `ي`, `ى`, `ك`, `ة` and their common Persian equivalents.
- Persian, Arabic and Latin digits.
- Spaces, zero-width joiners and common separators such as hyphens.

This supports inputs such as `پن بيسيم` for a product named `پن بی‌سیم`, as well as an exact SKU. Product editors can enter one search alias per line in **Product data → Product guidance → Search aliases**. Use aliases only for confirmed brand names, model numbers and common customer spellings. Do not add broad terms that would produce irrelevant matches, and do not use aliases to imply compatibility.

Search continues to be an ordinary submitted WooCommerce form; there is no external search provider or frontend search dependency. Results preserve native product visibility and the standard WooCommerce listing controls. Live type-ahead suggestions, typo correction and weighted relevance are deferred until the real catalog and search queries are available for review.

`pnpm test:search` exercises Persian/Arabic letter variants, SKU, fixture aliases and a no-result query in the local preview.
