import assert from 'node:assert/strict';
const base = 'http://127.0.0.1:9400';
async function get(path) {
	const response = await fetch(new URL(path, base), { headers: { Cookie: 'playground_auto_login_already_happened=1' }, signal: AbortSignal.timeout(60000) });
	assert.equal(response.status, 200, path);
	return response.text();
}
const home = (await get('/')).replace(/&#x([\da-f]+);/gi, (_, hex) => String.fromCodePoint(parseInt(hex, 16)));
assert.match(home, /card-purchase/);
assert.match(home, /card-price-range/);
const range = home.match(/<span class="price card-price-range">([\s\S]+?)<span class="card-price-unit">([^<]+)<\/span>/);
assert.ok(range, 'Range uses one common currency unit');
assert.match(range[1], /300,000/);
assert.match(range[1], /600,000/);
assert.match(range[2], /تومان/);
assert.doesNotMatch(range[1], /تومان/);
const products = JSON.parse(await get('/?rest_route=/wc/store/v1/products&sku=PREVIEW-3'));
assert.equal(products[0].type, 'variable');
const detail = await get(products[0].permalink);
assert.match(detail, /variations_form/);
assert.match(detail, /300000/);
assert.match(detail, /600000/);
assert.match(home, /class="product_type_variable|product_type_variable /);
console.log('PASS: compact purchase row, single-unit stacked range, native variable-product selection and price data.');
