import assert from 'node:assert/strict';
const origin = 'http://127.0.0.1:9400';
async function get(path) {
	const response = await fetch(new URL(path, origin), {headers: {Cookie: 'playground_auto_login_already_happened=1'}, signal: AbortSignal.timeout(60000)});
	assert.equal(response.status, 200, path);
	const html = await response.text(); assert.doesNotMatch(html, /Fatal error|Uncaught Error/); return html;
}
console.log('Checking suggestions and catalog-specific facets');
const suggestions = JSON.parse(await get('/?rest_route=/wpmain/v1/suggestions&q=Mast'));
assert.ok(suggestions.products.some(item => item.label.includes('بی‌سیم')));
const machines = await get('/product-category/machines/');
assert.match(machines, /filter_system=/);
assert.match(machines, /category-buying-guide/);
const narrowed = await get('/product-category/machines/?filter_system=wireless&query_type_system=or');
assert.match(narrowed, /پن تاتو بی‌سیم/);
assert.doesNotMatch(narrowed, /<h2 class="woocommerce-loop-product__title">پن تاتو روتاری/);
console.log('Checking unpriced, unavailable and sale product states');
for (const [sku, marker] of [['QA-ENQUIRY','wpmain-product-state--enquiry'],['QA-OUT','wpmain-product-state--unavailable'],['PREVIEW-4','<del']]) {
	const products = JSON.parse(await get('/?rest_route=/wc/store/v1/products&sku=' + sku));
	assert.ok(products[0], sku);
	const page = await get(products[0].permalink); assert.ok(page.includes(marker), sku);
	if (sku.startsWith('QA-')) assert.doesNotMatch(page, /class="single_add_to_cart_button/);
}
console.log('Checking article anchors, products and support navigation');
const articlePost = JSON.parse(await get('/?rest_route=/wp/v2/posts&slug=preview-guide-1'))[0];
const article = await get(articlePost.link);
assert.match(article, /editorial-toc/); assert.match(article, /editorial-products/);
const ids = [...article.matchAll(/\sid="([^"]+)"/g)].map(match => match[1]);
assert.equal(new Set(ids).size, ids.length, 'No duplicate HTML IDs');
for (const anchor of article.matchAll(/href="#([^"]+)"/g)) assert.ok(ids.includes(anchor[1]), `Anchor ${anchor[1]} has a target`);
const support = await get('/shipping/'); assert.match(support, /support-navigation/); assert.match(support, /aria-current="page"/);
console.log('PASS: suggestions, scoped attribute filters, buying guidance, product states, article anchors/products and support navigation.');
