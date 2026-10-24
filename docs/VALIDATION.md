# Validation

## Automated

GitHub Actions runs PHP syntax checks on PHP 8.1 and 8.3, JavaScript syntax validation, and a parent-theme packaging check. Syntax checks do not verify WordPress or commerce behavior.

## Required staging checks before launch

- Activate parent then child with WordPress debugging enabled; inspect logs. Repeat with WooCommerce disabled and enabled.
- Verify static homepage, posts index, single/page, archive, search/no-results, 404, password-protected content, paginated content and comments.
- Set logo and menus. Check desktop/mobile navigation, nested links, keyboard access, Escape, focus indicators, skip link, 200% zoom and no-JavaScript navigation.
- Check each brand at mobile/tablet/desktop widths, long names, real images, contrast, form errors, and empty states. Verify approved RTL/languages if required.
- Check shop/category/search, pagination, simple and variable products, gallery, sale/out-of-stock products, quantity, add-to-cart, remove/update, coupons, shipping, taxes, and persistence after reload.
- Test both the selected checkout/cart implementation and account/login/password reset; exercise guest and returning-customer orders, gateway success/failure/cancel, order confirmation, email, and refunds in sandbox.
- Confirm cache bypass with different customer sessions; no cross-session cart/account leakage. Verify responsive layout with checkout validation errors.
- Measure representative pages with real products and hosting caching. Record asset sizes, LCP, CLS, INP, and requests before performance changes. Do not promise scores from source alone.

## Initial local environment

No local WordPress/database or PHP runtime was supplied with this workspace. Runtime storefront and payment checks require staging; repository syntax checks are configured in CI. Record actual local checks in CHANGELOG.md.
