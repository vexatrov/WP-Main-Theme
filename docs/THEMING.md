# Shared behavior and brand-specific design

The parent theme owns responsive foundations, accessible menu behavior, compact sticky headers, consistent product/blog card geometry and neutral page templates. It does not force a liquid-glass identity, carousel or a particular brand's artwork.

## Child theme extension points

- Copy `themes/brand-starter` for a brand. Keep `Template: wp-main-theme`.
- Override `--button-radius` and `--input-radius` independently from `--brand-radius`. Header surface/shadow use `--header-surface` and `--header-shadow`. Change brand colors/fonts/container width through existing tokens.
- Add `template-parts/storefront/hero.php` in the child for a custom hero: static editorial, curated product arrangement or a progressively enhanced slider. WordPress selects the child's file automatically. The default hero is a template part, not tied to the page content loop.
- Override `template-parts/storefront/catalog.php` or `front-page.php` for a different home layout; override `header.php`/`footer.php` for brand structure changes. Preserve `wp_head`, `wp_footer`, `wp_body_open`, the skip-link target, and standard menu semantics.
- The optional sticky behavior targets `[data-sticky-header]`. Its compact state is `.is-compact`. A child header that reuses this behavior should preserve `.menu-toggle`, `.primary-navigation` and `#primary-menu`; supply matching `aria-controls`. A fully custom header may supply its own script and omit the data attribute.
- Liquid glass belongs to a child stylesheet with an opaque fallback, readable contrast and browser-support checks. No blur effects or animation libraries are shipped globally.
- Sliders, comparisons and additional assets should load only on pages that use them; respect reduced motion and keyboard navigation. No autoplay carousel is imposed on every shop.

## Responsive behavior

Product grids use four columns on wide screens, three on smaller laptops, two on phones/tablets and one on very narrow screens. Shared grid rules cover the homepage, shop/category/search and related-product loops. Images use a square `contain` surface; product titles display up to two lines without removing the full accessible text. Flex layout aligns prices/actions within a row. Blog cards reserve an image area even when a thumbnail is missing; headings/excerpts are visually limited and links align at the bottom of their row. Child themes can override geometry where deliberately required.

Sticky headers keep product search and cart available. Scrolling beyond 256px enters compact mode; returning near the top exits it. Separate thresholds prevent shrink/expand jitter. The script does not hide focused controls; menus remain available through the toggle in compact mode. Scroll work is passive and batched by requestAnimationFrame; a ResizeObserver updates anchor clearance. With JavaScript unavailable, the header stays sticky and menus remain visible.

## Required pages and footer

Create Home, Shop (WooCommerce), About, Contact and a posts page; assign menus explicitly. `templates/page-about.php` and `templates/page-contact.php` are selectable editor templates, with page content managed in WordPress. They do not create fake contact details or a nonfunctional form. Configure a real mail/contact integration separately if a form is required.

Under Appearance > Customize > Footer information, enter a brief factual brand introduction and select the About page. This is visitor context with a crawlable About link, not a promised ranking improvement. Keep the paragraph useful and avoid keyword stuffing, in line with [Google's people-first content guidance](https://developers.google.com/search/docs/fundamentals/creating-helpful-content).

## Currency

The requested currency is **tomans (تومان)**. Set WooCommerce's store currency to IRT, zero decimal places, and display the symbol after the amount. The theme never forces a site's currency or applies an exchange rate. All stored product prices must be entered in tomans. The demo's old sample rial amounts are divided by ten once by the local fixture refresh; no production price conversion is performed. Verify any real payment gateway's expected rial/toman units and supported currency before connecting it. A label change alone is not a unit conversion.

## Performance limits

Shared type/spacing tokens and alignment choices are documented in VISUAL-BASELINE.md. Rc.6 supplies `--heading-hero`, `--heading-page`, `--heading-section`, `--heading-subsection` and `--section-spacing`. Parent defaults use reading-start alignment (right for Persian); explicit content alignment and child-theme overrides remain available. Homepage product/category choices are now named, reorderable Customizer controls when the companion plugin is active; source IDs remain a no-JavaScript/plugin-absent fallback.

The new header behavior adds no dependency. Images use WordPress attachment markup/responsive sizes; product images below the fold retain native lazy-loading behavior. WooCommerce checkout scripts remain intact. Source/asset checks and local layout tests are useful, but do not establish production Core Web Vitals. Measure with real images, catalog and hosting before making performance claims.
