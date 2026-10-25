# Category filters and buying guidance

Use WooCommerce's native categories, product brands and global attributes. Category artwork comes from the category thumbnail; parent categories show child routes and leaf categories offer sibling routes. WooCommerce owns descendant-aware catalog queries, sorting and pagination.

Edit Products > Categories > Edit to configure:

- **Filter attribute slugs**: up to four registered global attribute slugs, e.g. `system, diameter, pack-size`, without the `pa_` prefix. Empty settings inherit the nearest configured parent. A machine category and a beauty-tool category can therefore use different facets. Saving discards unregistered attributes.
- **Buying guidance**: plain text paragraphs below the product listing.
- **Buying guide article IDs**: up to four published, unprotected articles, rendered as useful links.

Assign attribute terms to the actual products. Local/custom product attributes support specifications but cannot be layered-navigation facets; WooCommerce requires global attributes for filters. Native layered navigation handles counts and links, OR within a facet and AND across facets. It also respects the current category. Price/active-filter widgets remain configurable in Appearance > Widgets > Shop filters. Avoid adding the same attribute twice through both category settings and widgets.

The brand select uses WooCommerce's native `filter_product_brand` query parameter. Its options are catalog-wide, so a category/brand combination can correctly return no matches. Clear filters preserves the category/search/sorting context and resets pagination. Do not claim zero-result combinations are unavailable stock without checking actual catalog data. All forms work with custom JavaScript disabled.

Do not add arbitrary attribute slugs or invented supplier claims to production. Category guidance and compatibility facts require the store's real inventory and editorial review. The preview's system attribute, prices and child categories are fictional test fixtures.
