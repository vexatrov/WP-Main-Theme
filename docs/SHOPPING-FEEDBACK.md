# Shopping feedback

The shared theme adds compact stock/option labels to product cards using WooCommerce product data. It retains native purchase URLs, AJAX handling, prices, full-width square imagery and two-line titles. The purchase grid reuses the stock-label line for WooCommerce’s native “View cart” link after addition, stacks sale prices, and keeps the action at 44px without an empty row. No fabricated units or stock quantities are shown.

The header renders an empty hidden count so full-page caches cannot leak another guest’s cart quantity. The browser reads `wc/store/v1/cart` with same-origin credentials and `cache: no-store` at startup, page restore and return to the tab. Classic add/remove responses carry an authoritative count in an uncached native WooCommerce fragment; the script reuses it without an extra cart GET, coalesces duplicate Blocks bridge events and cancels stale reads. Blocks-only changes and events without a valid fragment use the API fallback. Failed reads hide the count. Successful actions announce polite feedback without moving focus. The count stays visible as a small badge in the compact mobile header; only the Cart text is visually hidden. WooCommerce retains native validation/redirects and the full cart route.

The Store API cart GET is WooCommerce’s supported session-aware read API; no customer data is embedded in the theme configuration. Hosting/CDN rules must exclude Store API cart responses from caching and forward guest session cookies. Theme JavaScript cannot override a CDN that ignores these rules. The count is item quantity, not distinct product lines. Existing counts refresh on browser tab return, not through background polling.

Navigation inserts separate disclosure buttons alongside parent links, retaining ordinary navigation. Enter/Space uses native button activation; Escape closes the branch and returns focus to its button before closing the containing navigation. Changing compact/mobile navigation resets branches. Without JavaScript, submenu links remain visible.

## Registration

Require `inc/cart-feedback.php`; enqueue `assets/css/purchase-feedback.css` after store CSS (also on non-commerce pages for submenu styles). With WooCommerce active, enqueue `assets/js/cart-feedback.js` with the `jquery` dependency and localize `wpmain_cart_feedback_settings()` as `wpmainCartFeedback`. Keep native WooCommerce scripts enabled. Navigation requires the translated `data-submenu-label` supplied by the header.

## Verification and remaining gates

`node scripts/test-cart-feedback.mjs` checks anonymous count initialization, cookie-aware/no-store fetch, native events, busy state, stale-response rejection, request failures and scoped AJAX errors. `node scripts/test-navigation.mjs` checks desktop/mobile/sticky behavior, focus preservation, branch disclosures and Escape. These tests use DOM doubles; they do not establish browser layout or a live WooCommerce session.

Before release, verify real simple/variable/sale/unpriced/unavailable cards together, long Persian titles and prices, zoom/RTL, two independent cached guest sessions, classic and Blocks add/remove, native failure/redirect paths, browser Back, and real assistive technology. Confirm WooCommerce’s Store API is available through the selected security/cache stack. No preview accounts, orders or stock were changed for these unit tests.
