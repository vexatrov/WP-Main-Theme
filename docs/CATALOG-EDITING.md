# Category filters and buying guidance

Use WooCommerce's native categories, product brands and global attributes. Category artwork comes from the category thumbnail; parent categories show child routes and leaf categories offer sibling routes. WooCommerce owns descendant-aware catalog queries, sorting and pagination.

The shop shows a labeled category rail and brand filter. Brand archives keep the current brand and offer category routes, without a second brand selector; category archives offer the brand filter and an All categories route. These category routes preserve the selected brand. The category rail uses ordinary keyboard-accessible links and a visible horizontal scrollbar when needed.

Category and brand archive headings show their own native `thumbnail_id` image beside the WooCommerce title. Set category artwork in Products > Categories and brand artwork in Products > Brands. A missing attachment or unset thumbnail leaves the title alone; the theme never invents a logo. Heading artwork is decorative because the adjacent heading names the term.

Both category and brand add/edit forms also provide **Short archive introduction** (320 plain-text characters), **Archive facts** (up to three verified lines, 160 characters each), and **Detailed archive buying guide** (safe HTML). The durable plugin stores `_wpmain_archive_intro` as a string, `_wpmain_archive_highlights` as up to three strings in an array, and `_wpmain_archive_content` as sanitized HTML. Textarea facts are entered one per line. These fields stay with taxonomy data when the theme changes; partial/non-editor saves preserve them.

The short introduction and facts appear beneath the archive heading. A blank introduction uses a shortened plain-text version of the native description; its full text is not duplicated above the grid. Detailed content appears after products and pagination, including no-result pages. Use factual choosing advice, evidence-backed comparisons and `<details><summary>Question</summary><p>Answer</p></details>` for optional FAQs; headings, lists and tables are supported. Do not add a second page-title heading, fabricated supplier claims, shortcode-driven private content or repetitive keyword paragraphs. Blank detailed category content retains the legacy Buying guidance field and article links. Brands do not borrow category guidance.

Parent category pages explicitly show child-category routes and keep WooCommerce's native descendant-aware product query: assigned child products remain in the parent's listing. Leaf pages show siblings instead. Brand scope survives those routes. The theme never replaces this behavior with direct-parent-only custom product queries.

Edit Products > Categories > Edit to configure:

- **Filter attribute slugs**: up to four registered global attribute slugs, e.g. `system, diameter, pack-size`, without the `pa_` prefix. Empty settings inherit the nearest configured parent. A machine category and a beauty-tool category can therefore use different facets. Saving discards unregistered attributes.
- **Buying guidance**: plain text paragraphs below the product listing.
- **Buying guide article IDs**: up to four published, unprotected articles, rendered as useful links.

Assign attribute terms to the actual products. Local/custom product attributes support specifications but cannot be layered-navigation facets; WooCommerce requires global attributes for filters. Native layered navigation handles counts and links, OR within a facet and AND across facets. It also respects the current category. Price/active-filter widgets remain configurable in Appearance > Widgets > Shop filters. Avoid adding the same attribute twice through both category settings and widgets.

The brand select uses WooCommerce's native `filter_product_brand` query parameter. For categories with up to 250 published, unprotected, catalog-visible products, options are scoped to the category and descendants through WordPress taxonomy APIs. A capped 251-ID lookup bounds memory; larger categories keep catalog-wide choices. The form explains that combinations can have no matching products. This is category membership, not a promise of in-stock inventory or matching price/attribute combinations. Category drilldown preserves the brand on brand archives and on categories with a brand filter.

Named selections above results remove one brand, price bound, attribute term or rating at a time. Removing the last attribute term also removes its query type. Clear filters preserves category/search/sorting context and resets pagination. Do not claim zero-result combinations are unavailable stock without checking actual catalog data. All forms work with custom JavaScript disabled.

Add the classic WooCommerce **Filter Products by Price** widget to Shop filters to show the shared price form. The theme replaces its output through WordPress's widget callback, retaining saved title/settings. Minimum and maximum fields have explicit labels and an Apply price action, use the configured store currency and native WooCommerce URL parameters, and retain other filter/search/sort values. The inaccessible range handles are not rendered; WooCommerce assets remain enabled. This replacement covers the classic widget, not third-party or block price-filter implementations. Theme compact category links scroll horizontally when needed and keep their text labels.

Validation commands: `node scripts/test-php.mjs` (include `tests/catalog-filters.php` in its contracts) and, with the fictional preview already running, `node scripts/test-catalog-polish.mjs`. The latter only reads localhost pages. Keyboard, screen-reader, RTL, zoom, browser Back, real mobile browsers and large real catalog checks remain release review requirements.

Do not add arbitrary attribute slugs or invented supplier claims to production. Category guidance and compatibility facts require the store's real inventory and editorial review. The preview's system attribute, prices and child categories are fictional test fixtures.
