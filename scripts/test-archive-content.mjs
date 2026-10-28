// Read-only checks of fictional taxonomy copy, hierarchy and native listings.
import assert from 'node:assert/strict';
const origin='http://127.0.0.1:9400';
async function get(path){
	const url=new URL(path,origin);assert.equal(url.origin,origin);
	const response=await fetch(url,{headers:{Cookie:'playground_auto_login_already_happened=1'},signal:AbortSignal.timeout(90000)});
	assert.equal(response.status,200,url.pathname);
	const html=await response.text();assert.doesNotMatch(html,/Fatal error|Uncaught Error|<b>Warning<\/b>/);return html;
}
function editorial(html){
	const intro=html.indexOf('class="catalog-archive-intro"');
	const grid=html.indexOf('<ul class="products');
	const bottom=html.indexOf('catalog-archive-content');
	// The class name also occurs in styles only as an external URL, never inline CSS.
	assert.ok(intro>0&&grid>intro&&bottom>grid,'Short intro precedes products, detailed guidance follows products');
	assert.match(html,/class="catalog-archive-facts"/);
	assert.match(html.slice(bottom),/<table>/,'Bottom guidance contains an illustrative comparison');
	assert.match(html.slice(bottom),/<details>/,'Bottom guidance has accessible native FAQs');
}
const products=JSON.parse(await get('/?rest_route=/wc/store/v1/products&per_page=100'));
const pens=['PREVIEW-1','PREVIEW-5'].map(sku=>products.find(p=>p.sku===sku));
assert.ok(pens.every(Boolean));
const machines=await get('/product-category/machines/');editorial(machines);
assert.match(machines,/\/machines\/wireless\//,'Parent offers child-category navigation');
assert.match(machines,/\/machines\/rotary\//,'Both populated child categories are offered');
for(const pen of pens){
	assert.ok(!pen.categories.some(c=>c.slug==='machines'),'Fixture remains assigned only to its child branch');
	assert.ok(machines.includes(`post-${pen.id}`),'Native parent grid includes child-only product');
}
const child=await get('/product-category/machines/wireless/');editorial(child);
assert.match(child,/id="catalog-brand"/,'Child category retains brand filtering');
const brands=await get('/brands/');
const brandUrl=brands.match(/class="brand-card" href="([^"]+)"/)?.[1];assert.ok(brandUrl);
const brand=await get(brandUrl.replace(/&amp;/g,'&'));editorial(brand);
assert.match(brand,/catalog-categories/);assert.doesNotMatch(brand,/id="catalog-brand"/);
const empty=await get('/product-category/machines/?min_price=999999999');
assert.match(empty,/catalog-archive-content/,'Detailed guidance remains available with no matching products');
assert.doesNotMatch(empty,/<ul class="products/,'Empty results do not pretend to contain products');
console.log('PASS: native parent/child product membership, child routes, brand/category scope, short top copy and rich bottom comparison/FAQ content, including empty results.');
