// Read-only verification of reusable SEO output in the local fictional store.
import assert from 'node:assert/strict';
import { writeFileSync, readFileSync, mkdirSync } from 'node:fs';
const origin = 'http://127.0.0.1:9400';
mkdirSync('.local/seo-foundation', { recursive: true });
async function get(path) {
	const url = new URL(path, origin); assert.equal(url.origin, origin);
	const response = await fetch(url, { headers: { Cookie: 'playground_auto_login_already_happened=1' }, signal: AbortSignal.timeout(240000) });
	assert.equal(response.status, 200, url.pathname);
	const text = await response.text();
	assert.doesNotMatch(text, /Fatal error|Uncaught Error|<b>Warning<\/b>/);
	return text;
}
function inspect(html, label, type = 'WebSite') {
	writeFileSync('.local/seo-foundation/' + label + '.html', html);
	const head = html.split('</head>')[0];
	assert.equal([...head.matchAll(/<meta name="description" content="[^"]+"/g)].length, 1, label + ': one nonempty description');
	assert.equal([...head.matchAll(/<title>/g)].length, 1, label + ': one document title');
	assert.equal([...head.matchAll(/rel="canonical"/g)].length, 1, label + ': one canonical');
	const markup = html.replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '').replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi, '');
	assert.equal(markup.replace(/<[^>]+>/g, '').match(/.{0,35}تاتو.{0,35}/g)?.join('; ') || '', '', label + ': corrected visible spelling');
	const schemas = [...html.matchAll(/<script\b[^>]*type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g)].map(m => JSON.parse(m[1]));
	assert.ok(JSON.stringify(schemas).includes('"' + type + '"'), label + ': ' + type + ' schema');
	writeFileSync('.local/seo-foundation/' + label + '.html', html);
	console.log('PASS:', label, 'metadata, canonical, schema and spelling');
}
const finish = process.argv.includes('--finish');
const home = finish ? readFileSync('.local/seo-foundation/home.html', 'utf8') : await get('/');
if (!finish) {
inspect(home, 'home');
const products = JSON.parse(await get('/?rest_route=/wc/store/v1/products&per_page=100'));
assert.ok(products.length >= 10);
for (const product of products) assert.doesNotMatch(product.name, /تاتو/);
for (const [path, label] of [['/shop/', 'shop'], ['/product-category/machines/', 'category'], ['/product-category/machines/wireless/', 'child'], ['/journal/', 'journal']]) inspect(await get(path), label);
const brands = await get('/brands/');
const brand = brands.match(/class="brand-card" href="([^"]+)"/)?.[1]; assert.ok(brand);
inspect(await get(brand.replace(/&amp;/g, '&')), 'brand');
const product = products.find(p => p.sku === 'PREVIEW-1'); assert.ok(product);
assert.match(decodeURIComponent(product.permalink), /تاتو/, 'Existing product slug is preserved');
inspect(await get(product.permalink), 'product', 'Product');
}
const article = home.match(/href="([^"]*\/\d{4}\/\d{2}\/\d{2}\/[^"\s]+)"/)?.[1]; assert.ok(article);
inspect(await get(article.replace(/&amp;/g, '&')), 'article', 'BlogPosting');
const tracking = await get('/order-tracking/');
assert.doesNotMatch(tracking.split('</head>')[0], /property="og:title"/, 'Tracking receives no fallback sharing metadata');
console.log('PASS: utility tracking route and unchanged legacy product permalink.');
