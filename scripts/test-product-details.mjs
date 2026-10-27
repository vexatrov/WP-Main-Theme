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
const ids = [...html.matchAll(/\sid="([^"]+)"/g)].map(match => match[1]);
assert.equal(new Set(ids).size, ids.length, `Page IDs are unique: ${ids.filter((id,index)=>ids.indexOf(id)!==index).join(', ')}`);
assert.match(html, /product-comparison-choice/, 'Comparison offers selectable illustrated recommendations');
assert.match(html, /name="compare_product\[\]"/, 'Comparison uses native GET choices');
assert.match(html, /id="tab-title-additional_information"[\s\S]*?مشخصات/, 'Native additional-information title is Specifications');
assert.match(html, /product-same-brand-title/, 'Assigned brand has its own product rail');
assert.equal((html.match(/class="product-recommendation-slider"/g) || []).length, 3, 'Fixture shows three adjacent recommendation rails');
const reassurance = html.match(/<ul class="product-reassurance"[^>]*>([\s\S]*?)<\/ul>/)?.[1];
assert.equal((reassurance?.match(/<li>/g) || []).length, 4, 'Owner sample messages appear in four shared boxes');
const checkbox = html.match(/type="checkbox" name="compare_product\[\]" value="(\d+)"/)?.[1];
assert.ok(checkbox, 'A public comparison recommendation exists');
const selectionUrl = new URL(pen.permalink);
selectionUrl.searchParams.append('compare_product[]', checkbox);
const selectedResponse = await fetch(selectionUrl, {headers, signal: AbortSignal.timeout(90000)});
assert.equal(selectedResponse.status, 200);
const selected = await selectedResponse.text();
assert.match(selected, /name=['"]robots['"][^>]+noindex/, 'Fallback SEO noindexes shopper comparison URLs');
const table = selected.match(/<table class="product-comparison">([\s\S]*?)<\/table>/)?.[1];
assert.equal((table?.match(/<th scope="col">/g) || []).length, 3, 'Current product plus one selected model and specification heading');
assert.equal((table?.match(/<img\b/g) || []).length, 2, 'Both compared models include images');
console.log(`PASS: product guidance, comparison, FAQs, guide links, separate recommendations and buying controls.\nReview: ${pen.permalink}`);
