# Homepage merchandising and key specifications

The parent stays lightweight and uses native Customizer settings and WooCommerce product data. Child hero/catalog/journal template overrides still resolve through `get_template_part()`; an override that owns its own query or composition must explicitly adopt these settings.

## Homepage

Select a static front page under Settings → Reading. Open Appearance → Customize → Storefront homepage:

- **Category IDs in display order:** comma-separated positive term IDs, up to six rendered populated product categories. Blank uses the six alphabetical populated top-level categories. Selected categories may be descendants; empty/deleted categories are omitted. An entirely unavailable curated category list stays empty.
- **Product IDs in display order:** up to eight positive product IDs. Published catalog-visible products are validated again at render time; password-protected, hidden and unavailable records are omitted. WooCommerce also applies its native visibility/stock rules. If none remain, the selected automatic source applies.
- **Homepage product source:** newest products (default), or WooCommerce's native featured products. Explicit valid product IDs take precedence; featured mode may legitimately have no products until editors mark products featured.
- **Show products before categories:** changes the order inside the catalog group.
- **Homepage section order:** enter `content,catalog,journal` in the desired order. Unknown/duplicate tokens are dropped; omitted sections append in default order. The hero stays first. Empty page content remains omitted; password-protected pages retain their content gate.

Find category IDs in Products → Categories (the term edit URL includes `tag_ID`); WooCommerce shows product IDs in Products. Settings belong to the active child theme's theme mods, so brands can curate independently. Theme code supplies no stock or identity fixtures.

## Product purchase summary

In the classic WooCommerce product editor, **Product data → Product guidance** offers four ordered **Key specification** selectors. Only saved visible attributes are available. Save attribute edits first, then choose and save specifications. Duplicate selections are removed; hidden/deleted keys never render. Leave all four slots empty to restore the first four visible attributes with nonempty values. Other product specifications remain unchanged.

Selections are references in plugin-owned `_wpmain_key_specs` product metadata, not copied values. Thus current attribute values/labels and taxonomy terms remain the source of truth. Saves require the existing guidance nonce and product edit permission. A partial request lacking this field preserves existing selection; invalid saved references disappear from output instead of being replaced with unrelated attributes.

## Persian bundles

Editable sources: theme `languages/fa_IR.json` and plugin `languages/wp-store-catalog-fa_IR.json`. The plugin registers its text domain on `init`; WordPress resolves the bundled domain/locale MO file and may prefer a site language pack. Native WooCommerce/WordPress translations remain their own responsibility.

Run `python scripts/build-translations.py` to rebuild both MO files, and `python scripts/build-translations.py --check` to check literal gettext coverage, printf placeholders and exact MO freshness without writes. Existing theme translations are retained. No translation dependency is added. The current source convention is single-quoted literal singular gettext calls; extend the extractor/compiler if plural/context messages are introduced.

## Validation limits

`tests/merchandising.php` covers input normalization, ordering, permission/nonce protection, partial saves, resetting defaults, hidden/missing attribute references and escaped mixed Persian/Latin output using isolated stubs. This is not a booted WordPress/browser certification. Check Customizer save/preview, native product editor selectors, featured stock visibility, child overrides and long Persian attribute layouts on the release store before deployment. No local fixture data or preview changes are required to run these contracts.
