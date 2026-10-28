// Read-only completeness checks for the guarded fictional catalog. Never creates content.
import assert from 'node:assert/strict';
const origin = new URL(process.env.PREVIEW_URL || 'http://127.0.0.1:9400');
assert.ok(['localhost', '127.0.0.1', '[::1]'].includes(origin.hostname), 'Refuse a non-local preview');
assert.ok(['http:', 'https:'].includes(origin.protocol) && !origin.username && !origin.password, 'Refuse credentials or unsupported transport');
const headers = {Cookie: 'playground_auto_login_already_happened=1'};
const watchdog = setTimeout(() => { console.error('Demo-content validation exceeded 15 minutes.'); process.exit(1); }, 900000);
watchdog.unref();
async function get(path) {
	const url = new URL(path, origin);
	assert.equal(url.origin, origin.origin, 'Refuse product URLs outside the local demo origin');
	const response = await fetch(url, {headers, signal: AbortSignal.timeout(90000)});
	assert.equal(response.status, 200, url.pathname);
	const text = await response.text();
	assert.doesNotMatch(text, /Fatal error|Uncaught Error|Uncaught Exception|Warning:|Notice:/, url.pathname);
	return {response, text};
}
const skus = [...Array.from({length: 8}, (_, index) => `PREVIEW-${index + 1}`), 'QA-ENQUIRY', 'QA-OUT'];
try {
	const {text: home} = await get('/');
	assert.ok(home.includes('ابزار تاتو / پیش‌نمایش'), 'Refuse a store without the exact fictional demo identity');
	const {response, text} = await get('/?rest_route=/wc/store/v1/products&per_page=100');
	const products = JSON.parse(text);
	assert.equal(Number(response.headers.get('x-wp-total')), 10, 'Disposable demo has exactly ten public parent products');
	assert.equal(products.length, 10);
	assert.deepEqual(products.map(item => item.sku).sort(), [...skus].sort(), 'All ten expected SKUs are public parents; no test lifecycle fixtures remain');
	for (const sku of skus) {
		const item = products.find(product => product.sku === sku);
		assert.ok(item.id && item.permalink, sku);
		assert.match(item.description, /demo-product-description/, `${sku}: rich category-specific description`);
		assert.match(item.short_description, /demo-product-introduction/, `${sku}: short introduction`);
		assert.ok(item.images.length >= 3, `${sku}: primary image and two gallery images`);
		assert.ok(item.attributes.length >= 3, `${sku}: visible native specifications`);
		assert.ok(Number(item.review_count) >= 1, `${sku}: native review count`);
		const {text: html} = await get(item.permalink);
		for (const marker of ['demo-product-description', 'demo-product-introduction', 'woocommerce-product-attributes', 'wpmain_usage', 'product-guide-links', 'product-comparison-scroll']) {
			assert.ok(html.includes(marker), `${sku}: ${marker}`);
		}
		assert.ok((html.match(/class="product-faq"/g) || []).length >= 2, `${sku}: at least two product questions and answers`);
		assert.match(html, /بازبین نمایشی/, `${sku}: explicitly fictional reviewer`);
		assert.match(html, /این دیدگاه ساختگی است/, `${sku}: explicitly fictional review body`);
		assert.ok((html.match(/woocommerce-product-gallery__image/g) || []).length >= 3, `${sku}: native gallery markup`);
		if (sku === 'PREVIEW-3') {
			assert.equal(item.type, 'variable');
			assert.match(html, /variations_form/);
			assert.match(html, /۳۰ میلی‌لیتر/);
			assert.match(html, /۶۰ میلی‌لیتر/);
			assert.equal(item.prices.price_range.min_amount, '300000');
			assert.equal(item.prices.price_range.max_amount, '600000');
			const encoded=html.match(/data-product_variations="([^"]+)"/)?.[1];
			assert.ok(encoded, 'Native two-option form exposes its matching data');
			const variations=JSON.parse(encoded.replace(/&quot;/g,'"').replace(/&#039;|&#0*39;/g,"'").replace(/&amp;/g,'&'));
			assert.deepEqual(variations.map(v=>v.sku).sort(),['PREVIEW-3-30ML','PREVIEW-3-60ML']);
			assert.equal(new Set(variations.map(v=>v.image_id)).size,2,'Volume options have distinct sample images');
		} else {
			assert.equal(item.type, 'simple', `${sku}: original product type retained`);
		}
		if (sku === 'PREVIEW-4') assert.equal(item.on_sale, true, 'Sale fixture is retained');
		if (sku === 'QA-ENQUIRY') assert.equal(item.is_purchasable, false, 'Unpriced enquiry fixture is retained');
		if (sku === 'QA-OUT') assert.equal(item.is_in_stock, false, 'Unavailable fixture is retained');
		console.log(`PASS: ${sku} description, specifications, FAQs, fictional review, guide, comparison and gallery.`);
	}
	console.log('PASS: all ten fictional parent fixtures have complete reviewable content; native variation, sale, enquiry and stock states remain.');
} finally { clearTimeout(watchdog); }
