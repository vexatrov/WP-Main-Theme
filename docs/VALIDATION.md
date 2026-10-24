# Validation

## Automated

GitHub Actions runs PHP syntax checks on PHP 8.1 and 8.3, JavaScript syntax validation, and a parent-theme packaging check. Syntax checks do not verify WordPress or commerce behavior.

## Required staging checks before launch

- Activate parent then child with WordPress debugging enabled; inspect logs. Repeat with WooCommerce disabled and enabled.
- Set a static homepage and test each Customizer field, hero image/alt text, empty hero settings, additional editor content, and password-protected Home content. Confirm the posts homepage still renders posts.
- Test homepage with no products, populated/empty categories, missing category images, hidden/out-of-stock products, and plugin disabled. Product search must use WooCommerce results and support pagination.
- Verify static homepage, posts index, single/page, archive, search/no-results, 404, password-protected content, paginated content and comments.
- Set logo and menus. Check desktop/mobile navigation, nested links, keyboard access, Escape, focus indicators, skip link, 200% zoom and no-JavaScript navigation.
- Check each brand at mobile/tablet/desktop widths, long names, real images, contrast, form errors, and empty states. Verify approved RTL/languages if required.
- Check shop/category/search, pagination, simple and variable products, gallery, sale/out-of-stock products, quantity, add-to-cart, remove/update, coupons, shipping, taxes, and persistence after reload.
- Test both the selected checkout/cart implementation and account/login/password reset; exercise guest and returning-customer orders, gateway success/failure/cancel, order confirmation, email, and refunds in sandbox.
- Confirm cache bypass with different customer sessions; no cross-session cart/account leakage. Verify responsive layout with checkout validation errors.
- Measure representative pages with real products and hosting caching. Record asset sizes, LCP, CLS, INP, and requests before performance changes. Do not promise scores from source alone.

## Initial local environment

v0.4.0: local navigation and HTTP preview checks passed, including About/Contact/Magazine routes and IRT currency. Browser checks covered 1440/1366/390/320px homepage overflow and product-row dimensions/button alignment, desktop and 390px blog layout, and compact sticky-header position/menu/Escape behavior. Child-brand effects require separate testing; local runtime speed does not establish production Core Web Vitals.

A disposable WordPress/PHP/SQLite environment is now supplied via `pnpm dev`; see LOCAL-PREVIEW.md. Production payment integrations and persistent MySQL hosting still require staging validation. Record actual preview checks in CHANGELOG.md; do not equate local sandbox checks with live gateway validation.

The v0.3.0 demo passed HTTP checks for RTL/Persian homepage, product detail/search, cart page, Store API add-to-cart and guest cart isolation. Browser checks covered homepage at desktop and 390px mobile viewport, mobile menu toggle and Escape focus, shop archive, product add-to-cart, block cart rendering and checkout form entry. No order was submitted; payment, shipping, variable products, refunds, and complete accessibility audits remain unverified. WooCommerce activation logged an early-translation notice in this environment.
