// Read-only HTTP checks against the fictional local fixtures; never creates orders.
import assert from 'node:assert/strict';
const origin = process.env.PREVIEW_URL || 'http://127.0.0.1:9400';
assert.ok(['localhost', '127.0.0.1', '[::1]'].includes(new URL(origin).hostname), 'Local preview only');
const decode = value => value.replace(/&amp;|&#0*38;/g, '&');
async function page(path) {
	const response = await fetch(new URL(path, origin), {headers: {Cookie: 'playground_auto_login_already_happened=1'}, signal: AbortSignal.timeout(30000)});
	assert.equal(response.status, 200);
	const html = await response.text();
	assert.doesNotMatch(html, /Fatal error|Uncaught Error/);
	return html;
}
const shop = await page('/shop/');
const brand = shop.match(/<select id="catalog-brand"[\s\S]+?<option value="(\d+)"/)?.[1];
assert.ok(brand, 'Fictional brand fixture exists');
const html = await page(`/shop/?filter_product_brand=${brand}&min_price=100&max_price=900000&rating_filter=4,5&orderby=price&s=pen&post_type=product`);
assert.match(html, /catalog-filter-chips/);
assert.doesNotMatch(html, /class="price_slider(?:\s|")/);
for (const key of ['min_price', 'max_price']) {
	const input = html.match(new RegExp(`<input[^>]+id="([^"]+)"[^>]+name="${key}"[^>]*>`));
	assert.ok(input, `${key} numeric input exists`);
	assert.match(input[0], /type="number"/);
	assert.ok(html.includes(`for="${input[1]}"`), `${key} explicit label`);
}
const chips = html.match(/<ul class="catalog-filter-chips">([\s\S]+?)<\/ul>/)?.[1];
assert.ok(chips);
for (const [, href] of chips.matchAll(/href="([^"]+)"/g)) {
	const url = new URL(decode(href));
	assert.equal(url.searchParams.get('s'), 'pen');
	assert.equal(url.searchParams.get('orderby'), 'price');
	assert.ok(!url.searchParams.has('paged') && !url.searchParams.has('product-page'));
}
const brandTerm = JSON.parse(await page(`/?rest_route=/wp/v2/product_brand/${brand}`));
assert.ok(brandTerm.slug, 'Native brand REST fixture is exposed');
const brandArchive = await page(`/?taxonomy=product_brand&term=${encodeURIComponent(brandTerm.slug)}`);
const category = brandArchive.match(/class="catalog-categories"[\s\S]+?<a href="([^"]+)"/)?.[1];
assert.ok(category, 'Brand archive category drilldown exists');
assert.equal(new URL(decode(category)).searchParams.get('filter_product_brand'), brand);
const scoped = await page(decode(category));
for (const [, href] of (scoped.match(/class="catalog-categories"[\s\S]+?<\/nav>/)?.[0] || '').matchAll(/href="([^"]+)"/g)) {
	assert.equal(new URL(decode(href)).searchParams.get('filter_product_brand'), brand);
}
console.log('PASS: labeled native price GET fields, named chips preserving context, no slider, brand category drilldown.');
