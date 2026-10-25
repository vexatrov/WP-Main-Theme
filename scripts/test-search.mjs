import assert from 'node:assert/strict';

const origin = 'http://127.0.0.1:9400';
const guest = { Cookie: 'playground_auto_login_already_happened=1' };

async function search(query) {
	const response = await fetch(new URL(`/?s=${encodeURIComponent(query)}&post_type=product`, origin), { headers: guest, signal: AbortSignal.timeout(60000) });
	assert.equal(response.status, 200, query);
	const html = await response.text();
	assert.doesNotMatch(html, /Fatal error|Uncaught Error/, query);
	return html;
}

for (const query of [ 'پن بی‌سیم', 'پن بيسيم', 'PREVIEW-1' ]) {
	const html = await search(query);
	assert.match(html, /پن تاتو بی‌سیم — نمونه/, query);
}
for (const query of [ 'Mast', 'مست' ]) {
	const html = await search(query);
	assert.match(html, /پن تاتو بی‌سیم — نمونه/, query);
}
assert.doesNotMatch(await search('ناموجود-نمونه'), /woocommerce-loop-product__title/);
console.log('PASS: Persian/Arabic letter variants, SKU and editable aliases return the intended product without false results.');
