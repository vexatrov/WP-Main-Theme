# Editorial articles

The shared theme adds a generated table of contents to public single-post articles. When the article content contains at least three non-empty `h2` or `h3` headings, a keyboard-accessible “On this page” navigation list appears before the article body. Headings without IDs receive readable IDs derived from their text; generated IDs avoid IDs already used anywhere in the article. IDs already present on headings are left as authored. The table of contents and IDs are produced from the filtered post content, so headings added by normal content filters can participate.

The automatic table of contents depends on WordPress's `WP_HTML_Tag_Processor` (WordPress 6.2 or later). On an older WordPress version the article remains readable and no automatic table of contents is generated. Empty headings are ignored; headings nested inside unusual markup may not be recognized by the lightweight heading scan.

Editors can add a curated product group in a post using the native Shortcode block or the classic editor's shortcode syntax:

```text
[editorial_products ids="123,456" heading="Useful tools"]
```

Replace the IDs with up to four WooCommerce product IDs. The `heading` attribute is optional; if omitted the group has an accessible default label without a visible heading. Only published, visible, non-password-protected products are shown. The links use WooCommerce's current product URL, thumbnail and formatted price. Invalid IDs and products that are no longer eligible are skipped. The shortcode renders only in single blog posts and requires WooCommerce.

The article layout rules live in `assets/css/editorial.css` and are loaded on single blog posts. They use the theme's existing brand tokens, and child themes can override the presentation. This work does not add automatic product recommendations, product relationship storage, article FAQ/schema output, author biographies, related-article changes or a dedicated article editor. Curated product choices remain editorial decisions made by post authors.

## Validation notes

PHP contract tests cover empty/duplicate headings, preserved authored anchors and generated IDs. HTTP checks in the real localhost WordPress preview verify the TOC, unique IDs, live anchor targets and product shortcode output. Fresh browser/assistive-technology and child-theme visual checks remain in the release gates in COMPLETION.md.
