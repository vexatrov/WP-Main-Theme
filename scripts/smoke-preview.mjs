import assert from 'node:assert/strict';

const base = 'http://127.0.0.1:9400';
// Bypass Playground's convenience auto-login to test real guest sessions.
const guestHeaders = { Cookie: 'playground_auto_login_already_happened=1' };
async function get(path, options = {}) {
	console.log(`Checking ${path}`);
	const response = await fetch(new URL(path, base), { ...options, headers: { ...guestHeaders, ...options.headers }, signal: AbortSignal.timeout(60000) });
	assert.equal(response.status, 200, `${path}: HTTP ${response.status}`);
	const html = await response.text();
	assert.ok(!/Fatal error|Uncaught Error/.test(html), `${path}: PHP failure`);
	return html;
}
const home = await get('/');
assert.match(home, /dir="rtl"/);
assert.match(home, /ابزار دقیق/);
assert.match(home, /بر اساس دسته‌بندی/);
assert.match(home, /پیش‌نمایش توسعه/);
assert.match(home, /wpmain-rtl-css/);
const productId = home.match(/data-product_id="(\d+)"/)?.[1];
assert.ok(productId, 'Homepage contains a purchasable sample product');
const productUrl = home.match(/href="([^"]+)"[^>]*class="woocommerce-LoopProduct-link/)?.[1];
assert.ok(productUrl, 'Homepage contains product links');
const product = await get(productUrl.replaceAll('&amp;', '&'));
assert.match(product, /single_add_to_cart_button/);
const search = await get('/?s=' + encodeURIComponent('کارتریج') + '&post_type=product');
assert.match(search, /woocommerce-loop-product__title/);
const cartUrl = home.match(/class="cart-link" href="([^"]+)"/)?.[1];
assert.ok(cartUrl, 'Header cart link exists');
await get(cartUrl.replaceAll('&amp;', '&'));
for (const page of ['/about/', '/contact/', '/journal/']) { await get(page); }
const cartApi = `${base}/?rest_route=/wc/store/v1/cart`;
console.log('Checking WooCommerce cart API and session isolation');
const session = await fetch(cartApi, { headers: guestHeaders, signal: AbortSignal.timeout(60000) });
assert.equal(session.status, 200);
const token = session.headers.get('cart-token');
assert.ok(token, 'WooCommerce Store API supplies an isolated cart token');
assert.equal((await session.json()).totals.currency_code, 'IRT', 'Store uses tomans');
const add = await fetch(`${base}/?rest_route=/wc/store/v1/cart/add-item`, {
	method: 'POST',
	headers: { ...guestHeaders, 'Content-Type': 'application/json', 'Cart-Token': token },
	body: JSON.stringify({ id: Number(productId), quantity: 1 }),
	signal: AbortSignal.timeout(60000),
});
assert.ok([200, 201].includes(add.status), `Sample product can be added to cart: HTTP ${add.status}`);
assert.equal((await add.json()).items_count, 1);
const freshSession = await fetch(cartApi, { headers: guestHeaders, signal: AbortSignal.timeout(60000) });
assert.equal((await freshSession.json()).items_count, 0, 'Fresh visitor cart is empty');
console.log('PASS: RTL/Persian homepage, preview marker, products, product page, search, cart page, add-to-cart and cart-session isolation.');
