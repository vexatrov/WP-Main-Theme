# Read-only staging checks

Run `node scripts/check-staging.mjs https://your-staging-host.example` against the final canonical staging origin. Run checks sequentially with other preview/commerce scripts. Local fictional preview is supported with `http://127.0.0.1:9400`; hosted-only HTTPS and noindex requirements do not apply to localhost.

The command reads the homepage, its linked CSS files, a fresh guest cart, the shop and its first public product, robots.txt and up to twelve discovered sitemap files. It checks response/redirect failures, HTTPS upgrades and downgrade prevention, staging noindex, CSS content types, an empty guest cart with IRT display currency, and commerce utility URL exclusion from discovered sitemaps. It neither creates orders/accounts nor changes settings. Each request has a thirty-second timeout; no parallel requests are issued.

The first product's priced JSON-LD offers must use IRR and numeric amounts. This checks the external currency integration's emitted code, **not** whether every toman amount was multiplied correctly. Review actual price-to-schema conversions, sales, variable products, nested shipping amounts, feeds and gateway units separately. An unpriced product or empty shop is explicitly reported as skipped; it does not certify schema.

WordPress can disable sitemaps on a noindex staging site, so a missing sitemap is reported as skipped. More than twelve sitemap files are reported as incomplete. Utility URL checks recognize standard English WooCommerce paths; inspect custom/localized routes and production sitemap/indexing policy separately. The script does not crawl every product, inspect CSS imports or assets loaded later by JavaScript, or use browser rendering. Its guest check uses one fresh request context, not a two-shopper cache-isolation test. Authentication-protected staging may need a separate authorized runner; do not put credentials in the command URL.

No Core Web Vitals score, accessibility certification, gateway result, email delivery result or backup-restore result is produced. Complete browser, screen-reader, real-device, production currency/SEO and hosting release gates before launch.
