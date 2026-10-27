// Read-only checks against the disposable localhost WordPress/WooCommerce release preview.
// Long request deadlines accommodate Windows/WASM startup; these are not performance measurements.
import assert from 'node:assert/strict';
const origin = 'http://127.0.0.1:9400';
const headers = { Cookie: 'playground_auto_login_already_happened=1' };
async function get(path) {
	const url = new URL(path, origin);
	assert.equal(url.origin, origin, 'Release checks stay on the local preview');
	const response = await fetch(url, { headers, signal: AbortSignal.timeout(90000) });
	assert.equal(response.status, 200, `${url.pathname}${url.search}`);
	assert.equal(new URL(response.url).origin, origin, 'Responses remain local');
	const body = await response.text();
	assert.doesNotMatch(body, /Fatal error|Uncaught Error|<b>Warning<\/b>/, 'No PHP errors');
	return body;
}
const decode = value => value.replace(/&amp;|&#0*38;/g, '&').replace(/&quot;/g, '"').replace(/&#0*39;|&apos;/g, "'");
function attrs(tag) {
	return Object.fromEntries([...tag.matchAll(/([\w:-]+)\s*=\s*(["'])(.*?)\2/gs)].map(match => [match[1], decode(match[3])]));
}
function tags(html, name) { return [...html.matchAll(new RegExp(`<${name}\\b[^>]*>`, 'gi'))].map(match => ({ tag: match[0], ...attrs(match[0]) })); }
function canonical(html) {
	const links = tags(html, 'link').filter(link => link.rel === 'canonical');
	assert.equal(links.length, 1, 'Exactly one canonical');
	return new URL(links[0].href);
}
function checkHeader(html) {
	const match = html.match(/<span\b(?=[^>]*\bdata-cart-count\b)[^>]*>([\s\S]*?)<\/span>/i);
	assert.ok(match, 'Header includes cart count');
	assert.match(match[0].split('>')[0], /\shidden(?:\s|=|$)/, 'Server count is hidden');
	assert.equal(match[1].trim(), '', 'Cached HTML never embeds a guest cart quantity');
	assert.ok(!('aria-label' in attrs(match[0].split('>')[0])), 'Cached count has no session-specific accessible label');
	assert.ok(tags(html, 'span').some(tag => /\bdata-cart-feedback\b/.test(tag.tag) && tag.role === 'status' && tag['aria-live'] === 'polite'), 'Polite cart status is present');
}
function graphs(html) {
	return [...html.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)].filter(match => attrs(match[1]).type === 'application/ld+json').map(match => JSON.parse(match[2]));
}
function walk(value, visit) {
	if (!value || typeof value !== 'object') return;
	if (!Array.isArray(value)) visit(value);
	Object.values(value).forEach(child => walk(child, visit));
}
const hasType = (node, name) => [node['@type']].flat().some(type => typeof type === 'string' && type.replace(/^https?:\/\/schema\.org\//, '') === name);
function productGraphs(html) {
	const products = [];
	graphs(html).forEach(graph => walk(graph, node => { if (hasType(node, 'Product')) products.push(node); }));
	return products;
}
function decimalKey(value) {
	const match = String(value).match(/^(\d+)(?:\.(\d+))?$/);
	assert.ok(match, `Plain non-negative decimal: ${value}`);
	const integer = match[1].replace(/^0+(?=\d)/, '');
	const fraction = (match[2] || '').replace(/0+$/, '');
	return integer + (fraction ? `.${fraction}` : '');
}
function irrFromMinor(value, minor) {
	assert.match(String(value), /^\d+$/);
	const amount = (BigInt(value) * 10n).toString().padStart(minor + 1, '0');
	return decimalKey(minor ? `${amount.slice(0, -minor)}.${amount.slice(-minor)}` : amount);
}
function checkMoney(product, schema) {
	assert.equal(product.prices.currency_code, 'IRT', `${product.sku}: Store API still uses tomans`);
	const minor = product.prices.currency_minor_unit;
	assert.ok(Number.isInteger(minor) && minor >= 0 && minor <= 6, 'Supported Store API decimal precision');
	const source = [product.prices.price, product.prices.regular_price, product.prices.sale_price, product.prices.price_range?.min_amount, product.prices.price_range?.max_amount].filter(value => value !== undefined && value !== null && value !== '');
	const expected = new Set(source.map(value => irrFromMinor(value, minor)));
	const actual = new Set();
	let monetaryFields = 0;
	const visit = (node, inherited = '') => {
		if (!node || typeof node !== 'object') return;
		if (Array.isArray(node)) { node.forEach(child => visit(child, inherited)); return; }
		for (const key of ['priceCurrency', 'currency']) {
			if (key in node) assert.notEqual(node[key], 'IRT', `${product.sku}: no IRT declaration remains`);
		}
		const currency = node.priceCurrency ?? node.currency ?? inherited;
		const money = hasType(node, 'MonetaryAmount');
		for (const key of ['price', 'lowPrice', 'highPrice', 'minPrice', 'maxPrice', ...(money ? ['value', 'minValue', 'maxValue'] : [])]) {
			if (!(key in node)) continue;
			assert.equal(currency, 'IRR', `${product.sku}: ${key} has matching IRR context`);
			const amount = decimalKey(node[key]);
			monetaryFields++;
			if (!money) {
				assert.ok(expected.has(amount), `${product.sku}: nested ${key}=${amount} equals a Store API toman amount ×10`);
				actual.add(amount);
			}
		}
		Object.values(node).forEach(child => visit(child, currency));
	};
	visit(schema);
	assert.ok(monetaryFields > 0, `${product.sku}: native product money exists`);
	assert.ok(actual.has(irrFromMinor(product.prices.price, minor)), `${product.sku}: current price is converted`);
	if (product.on_sale) {
		assert.ok(actual.has(irrFromMinor(product.prices.regular_price, minor)), 'Sale graph retains the converted regular/list price');
		assert.ok(actual.has(irrFromMinor(product.prices.sale_price, minor)), 'Sale graph retains the converted sale price');
	}
	if (product.prices.price_range) {
		for (const key of ['min_amount', 'max_amount']) assert.ok(actual.has(irrFromMinor(product.prices.price_range[key], minor)), `Variable ${key} is converted`);
	}
}

console.log('Checking cache-neutral header, catalog controls, metadata and asset registration');
const shop = await get('/shop/');
checkHeader(shop);
const meta = tags(shop, 'meta');
assert.ok(meta.some(tag => tag.property === 'og:title'), 'Open Graph fallback is emitted');
assert.ok(meta.some(tag => tag.name === 'twitter:card'), 'Twitter fallback is emitted');
const shopCanonical = canonical(shop);
assert.equal(shopCanonical.origin, origin);
assert.equal(shopCanonical.pathname.replace(/\/$/, ''), '/shop');
assert.equal(shopCanonical.search, '');
const form = shop.match(/<form\b(?=[^>]*\bclass=["'][^"']*catalog-price-filter)[\s\S]*?<\/form>/i)?.[0];
assert.ok(form, 'Catalog uses native-semantic numeric price form');
for (const name of ['min_price', 'max_price']) {
	const input = tags(form, 'input').find(tag => tag.name === name);
	assert.ok(input && input.type === 'number' && input.id, `${name}: numeric input has an ID`);
	const label = [...form.matchAll(/<label\b([^>]*)>([\s\S]*?)<\/label>/gi)].find(match => attrs(match[1]).for === input.id);
	assert.ok(label && label[2].replace(/<[^>]+>/g, '').trim(), `${name}: an associated visible label exists`);
}
assert.doesNotMatch(form, /price_slider|role=["']slider["']/i, 'No unlabeled slider replacement');
assert.doesNotMatch(shop, /class=["'][^"']*price_slider(?:\s|["'])/i, 'Legacy slider is not rendered');
assert.match(shop, /card-stock/, 'Product cards display purchase state cues');
assert.match(shop, /card-purchase/, 'Shared compact purchase rows remain');
const script = tags(shop, 'script').find(tag => tag.src?.includes('/assets/js/cart-feedback.js'));
const style = tags(shop, 'link').find(tag => tag.href?.includes('/assets/css/purchase-feedback.css'));
assert.ok(script && style, 'Feedback script and stylesheet are enqueued');
assert.match(await get(script.src), /wc-blocks_added_to_cart/);
assert.match(await get(style.href), /card-purchase/);
const filtered = await get('/shop/?min_price=100&orderby=price');
assert.ok(tags(filtered, 'meta').some(tag => tag.name === 'robots' && /(?:^|[,\s])noindex(?:$|[,\s])/.test(tag.content)), 'Filtered URL has noindex');
checkHeader(filtered);

console.log('Checking native simple, sale, variable and unpriced product schema against Store API prices');
for (const sku of ['PREVIEW-1', 'PREVIEW-4', 'PREVIEW-3', 'QA-ENQUIRY']) {
	const products = JSON.parse(await get(`/?rest_route=/wc/store/v1/products&sku=${encodeURIComponent(sku)}`));
	const product = products.find(item => item.sku === sku);
	assert.ok(product, `${sku}: expected disposable fixture exists`);
	if (sku === 'PREVIEW-4') assert.equal(product.on_sale, true, 'Sale fixture is still on sale');
	if (sku === 'PREVIEW-3') assert.equal(product.type, 'variable', 'Variable fixture retains its native type');
	// Store API may serialize WooCommerce's empty native price as zero.
	// The rendered enquiry state and absent purchase form establish this fixture's state.
	if (sku === 'QA-ENQUIRY') assert.ok(['', '0'].includes(product.prices.price), 'Enquiry fixture has the expected empty/zero Store API serialization');
	const html = await get(product.permalink);
	checkHeader(html);
	assert.equal(canonical(html).pathname, new URL(product.permalink).pathname, `${sku}: singular canonical remains native`);
	assert.ok(tags(html, 'meta').some(tag => tag.name === 'description' && tag.content), `${sku}: real product description`);
	const schema = productGraphs(html);
	if (sku === 'QA-ENQUIRY') {
		assert.match(html, /wpmain-product-state--enquiry/, 'Native empty price renders the enquiry state');
		assert.ok(!tags(html, 'form').some(form => (form.class || '').split(/\s+/).includes('cart')), 'Unpriced product has no purchase form');
		assert.doesNotMatch(html, /class=["'][^"']*\bsingle_add_to_cart_button\b/, 'Unpriced product has no purchase button');
		assert.ok(schema.every(node => !node.offers || (Array.isArray(node.offers) && node.offers.length === 0)), 'Unpriced product has no fabricated offer');
	} else {
		assert.equal(schema.length, 1, `${sku}: one native Product graph`);
		assert.equal(schema[0].sku, sku);
		checkMoney(product, schema[0]);
	}
	console.log(`PASS: ${sku}`);
}
console.log('PASS: read-only release head/assets/controls/cart placeholder and native monetary schema checks. Browser interaction, hosted caching, performance and gateway tests remain separate.');
