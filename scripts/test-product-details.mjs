import assert from 'node:assert/strict';

const headers = { Cookie: 'playground_auto_login_already_happened=1' };
const base = 'http://127.0.0.1:9400';
const lookup = await fetch(`${base}/?rest_route=/wc/store/v1/products&sku=PREVIEW-1`, { headers, signal: AbortSignal.timeout(60000) });
assert.equal(lookup.status, 200);
const items = await lookup.json();
const pen = items.find(item => item.sku === 'PREVIEW-1');
assert.ok(pen, 'Sample pen is available');
const response = await fetch(pen.permalink, { headers, signal: AbortSignal.timeout(60000) });
assert.equal(response.status, 200);
const html = await response.text();
for (const marker of ['wpmain_usage', 'wpmain_comparison', 'wpmain_faq', 'product-comparison-scroll', 'product-guide-links', 'product-compatible-title', 'product-alternatives-title', 'single_add_to_cart_button']) {
	assert.ok(html.includes(marker), `${marker} is rendered`);
}
assert.match(html, /preview-guide-1/);
assert.match(html, /پرسش‌های متداول محصول/);
assert.ok(!/Fatal error|Uncaught Error/.test(html));
const ids = [...html.matchAll(/id="([^"]+)"/g)].map(match => match[1]);
assert.equal(new Set(ids).size, ids.length, 'Page IDs are unique');
console.log(`PASS: product guidance, comparison, FAQs, guide links, separate recommendations and buying controls.\nReview: ${pen.permalink}`);
