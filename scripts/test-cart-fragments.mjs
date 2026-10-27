// Native Woo AJAX integration against the disposable localhost preview only.
// Creates one isolated anonymous cart; never creates orders/accounts or contacts a gateway.
import assert from 'node:assert/strict';
const origin = new URL('http://127.0.0.1:9400');
assert.equal(origin.hostname, '127.0.0.1');
assert.equal(origin.port, '9400');
const cookies = new Map([['playground_auto_login_already_happened', '1']]);
async function request(path, fields) {
	const url = new URL(path, origin);
	assert.equal(url.origin, origin.origin, 'Requests stay on the guarded localhost preview');
	console.log(`Checking ${fields ? 'POST' : 'GET'} ${url.pathname}${url.search}`);
	const headers = { Cookie: [...cookies].map(([name, value]) => `${name}=${value}`).join('; '), Accept: 'application/json' };
	if (fields) {
		headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
		headers['X-Requested-With'] = 'XMLHttpRequest';
		headers.Origin = origin.origin;
		headers.Referer = new URL('/shop/', origin).href;
	}
	const response = await fetch(url, { method: fields ? 'POST' : 'GET', headers, body: fields ? new URLSearchParams(fields) : undefined, redirect: 'error', signal: AbortSignal.timeout(90000) });
	assert.equal(response.status, 200, `${url.pathname}${url.search}`);
	assert.match(response.headers.get('content-type') || '', /application\/json/i, 'Native endpoint returns JSON');
	for (const header of response.headers.getSetCookie()) {
		const pair = header.split(';', 1)[0];
		const separator = pair.indexOf('=');
		if (separator < 1) continue;
		const name = pair.slice(0, separator);
		assert.doesNotMatch(name, /^wordpress_logged_in_|^wordpress_sec_/, 'Cart test stays anonymous');
		if (/;\s*max-age=0(?:;|$)/i.test(header)) cookies.delete(name);
		else cookies.set(name, pair.slice(separator + 1));
	}
	const body = await response.text();
	assert.doesNotMatch(body, /Fatal error|Uncaught Error|<b>Warning<\/b>/, 'No PHP failures');
	return JSON.parse(body);
}
function checkFragments(result, productId) {
	assert.notEqual(result.error, true, 'Native add/refresh succeeds');
	assert.ok(result.fragments && typeof result.fragments === 'object', 'WooCommerce fragments are returned');
	const html = result.fragments['span[data-cart-count]'];
	assert.equal(typeof html, 'string', 'Authoritative cart count fragment exists');
	const opening = html.match(/^\s*<span\b[^>]*>/i)?.[0];
	assert.ok(opening, 'Count fragment remains a span');
	const attributes = Object.fromEntries([...opening.matchAll(/([\w:-]+)\s*=\s*(["'])(.*?)\2/g)].map(match => [match[1], match[3]]));
	assert.equal(attributes['data-count'], '1', 'Fresh anonymous cart quantity is one');
	assert.match(opening, /\bdata-cart-count(?:\s|=|>)/, 'Native selector marker is retained');
	assert.doesNotMatch(opening, /\shidden(?:\s|=|>)/i, 'Authoritative response count is visible');
	assert.match(html, />\s*1\s*<\/span>\s*$/, 'Quantity is visible text');
	assert.ok(attributes['aria-label']?.trim(), 'Count has a localized accessible label');
	assert.match(attributes['aria-label'], /1|۱|&#(?:x31|0*49);/i, 'Accessible label includes the current quantity');
	const widget = result.fragments['div.widget_shopping_cart_content'];
	assert.equal(typeof widget, 'string', 'Native WooCommerce widget fragment is preserved');
	assert.match(widget, /woocommerce-mini-cart/, 'Native widget still contains mini-cart markup');
	assert.ok(widget.includes(`data-product_id="${productId}"`), 'Native widget retains the added fixture');
	assert.ok(result.cart_hash, 'Native cart hash is retained');
	return widget;
}

const products = await request('/?rest_route=/wc/store/v1/products&sku=PREVIEW-1');
const product = products.find(item => item.sku === 'PREVIEW-1');
assert.ok(product?.id && product.type === 'simple' && product.is_purchasable, 'Known purchasable simple preview fixture exists');
assert.equal(new URL(product.permalink).origin, origin.origin, 'Fixture belongs to this local store');
const added = await request('/?wc-ajax=add_to_cart', { product_id: String(product.id), quantity: '1' });
const widget = checkFragments(added, product.id);
assert.ok([...cookies.keys()].some(name => name.startsWith('wp_woocommerce_session_')), 'WooCommerce established the isolated guest session');
const refreshed = await request('/?wc-ajax=get_refreshed_fragments', {});
const refreshedWidget = checkFragments(refreshed, product.id);
assert.equal(refreshed.cart_hash, added.cart_hash, 'Cookie-backed refresh preserves the same native cart');
assert.equal(refreshedWidget, widget, 'Theme count fragment does not alter the native widget fragment');
console.log('PASS: native add-to-cart and refreshed fragments expose count one with accessible visible feedback, preserve native widget/cart hash, and retain an isolated anonymous session. No order/account/gateway action performed.');
