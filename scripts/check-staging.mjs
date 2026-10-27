// Read-only staging checks: pages/assets/public schema only; no orders or settings.
import assert from 'node:assert/strict';
const supplied = process.argv[2];
if (!supplied) throw new Error('Usage: node scripts/check-staging.mjs https://your-staging-host.example');
const base = new URL(supplied);
if (base.username || base.password || !['http:', 'https:'].includes(base.protocol)) throw new Error('Use a plain HTTP(S) origin without credentials.');
const local = ['localhost', '127.0.0.1', '[::1]'].includes(base.hostname);
if (!local && base.protocol !== 'https:') throw new Error('Hosted staging must use HTTPS.');
const decode = value => value.replace(/&amp;|&#0*38;/g, '&');
const attrs = tag => Object.fromEntries([...tag.matchAll(/([\w:-]+)\s*=\s*["']([^"']*)["']/g)].map(([, key, value]) => [key.toLowerCase(), decode(value)]));
async function get(input, {allowMissing = false} = {}) {
	let url = new URL(input, base);
	for (let redirects = 0; redirects < 8; redirects++) {
		assert.ok(['http:', 'https:'].includes(url.protocol), 'HTTP(S) requests only');
		if (!local) assert.equal(url.protocol, 'https:', `Insecure redirect or asset: ${url}`);
		const headers = local && url.origin === base.origin ? {Cookie: 'playground_auto_login_already_happened=1'} : {};
		const response = await fetch(url, {headers, redirect: 'manual', signal: AbortSignal.timeout(30000)});
		if ([301, 302, 303, 307, 308].includes(response.status)) {
			assert.ok(response.headers.get('location'), 'Redirect has Location');
			url = new URL(response.headers.get('location'), url);
			continue;
		}
		if (!(allowMissing && response.status === 404)) assert.ok(response.ok, `${url.pathname}: HTTP ${response.status}`);
		return {response, url, text: await response.text()};
	}
	throw new Error('Too many redirects');
}
const home = await get(base);
assert.equal(home.url.origin, base.origin, 'Use the final canonical staging origin as the argument');
assert.doesNotMatch(home.text, /Fatal error|Uncaught Error|WordPress is not ready yet/);
assert.match(home.text, /wpmain-css/, 'Shared theme stylesheet');
if (!local) {
	const http = new URL(base); http.protocol = 'http:';
	const response = await fetch(http, {redirect: 'manual', signal: AbortSignal.timeout(30000)});
	assert.ok([301, 302, 307, 308].includes(response.status), 'HTTP must redirect permanently or temporarily to HTTPS');
	const destination = new URL(response.headers.get('location'), http);
	assert.equal(destination.protocol, 'https:', 'First HTTP redirect upgrades to HTTPS');
	assert.equal(destination.hostname, base.hostname, 'HTTP redirect remains on the staging host');
}
const robotTags = [...home.text.matchAll(/<meta\b[^>]*>/gi)].map(match => attrs(match[0])).filter(tag => /^(robots|googlebot)$/i.test(tag.name || '')).map(tag => tag.content || '').join(' ');
if (!local) assert.match(`${home.response.headers.get('x-robots-tag') || ''} ${robotTags}`, /\bnoindex\b/i, 'Hosted staging must be excluded from indexing');
const cssUrls = [...new Set([...home.text.matchAll(/<link\b[^>]*>/gi)].map(match => attrs(match[0])).filter(tag => /(?:^|\s)stylesheet(?:\s|$)/i.test(tag.rel || '') && tag.href).map(tag => new URL(tag.href, home.url).href))];
assert.ok(cssUrls.length > 0, 'Stylesheets are linked');
for (const url of cssUrls) {
	const css = await get(url);
	assert.match(css.response.headers.get('content-type') || '', /text\/css/i, 'Stylesheet returns CSS rather than a login/error page');
	assert.ok(css.text.trim(), 'Stylesheet is not empty');
}
const cart = JSON.parse((await get('/?rest_route=/wc/store/v1/cart')).text);
assert.equal(cart.items_count, 0, 'Fresh guest cart must be empty; inspect cache/session isolation');
assert.equal(cart.totals?.currency_code, 'IRT', 'Commerce display currency must be tomans');
const shop = await get('/shop/');
const firstProduct = shop.text.match(/<li\b[^>]*class=["'][^"']*\bproduct\b[^"']*["'][^>]*>[\s\S]*?<a\b[^>]*href=["']([^"']+)["'][^>]*class=["'][^"']*woocommerce-LoopProduct-link/i)?.[1];
if (firstProduct) {
	const productUrl = new URL(decode(firstProduct), base);
	assert.equal(productUrl.origin, base.origin, 'Product stays on staging');
	const product = await get(productUrl);
	const scripts = [...product.text.matchAll(/<script\b[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi)];
	const nodes = [];
	const visit = value => { if (Array.isArray(value)) value.forEach(visit); else if (value && typeof value === 'object') { nodes.push(value); Object.values(value).forEach(visit); } };
	for (const [, json] of scripts) visit(JSON.parse(json));
	const offers = nodes.filter(node => [node['@type']].flat().some(type => ['Offer', 'AggregateOffer'].includes(type)));
	const priced = offers.filter(offer => offer.price !== undefined || offer.lowPrice !== undefined || offer.highPrice !== undefined);
	const summaryPrice = product.text.match(/<p\b[^>]*class=["'][^"']*\bprice\b[^"']*["'][^>]*>([\s\S]*?)<\/p>/i)?.[1] || '';
	if (/woocommerce-Price-amount/.test(summaryPrice)) assert.ok(priced.length > 0, 'Priced product must emit a priced Offer or AggregateOffer');
	for (const offer of priced) {
		assert.equal(offer.priceCurrency, 'IRR', 'IRT is not ISO 4217: external integration must emit IRR with correctly converted amounts');
		for (const field of ['price', 'lowPrice', 'highPrice']) if (offer[field] !== undefined) assert.ok(Number.isFinite(Number(offer[field])) && Number(offer[field]) >= 0, 'Offer amounts must be nonnegative numbers');
	}
	console.log(priced.length ? 'PASS: first public product offer uses IRR with numeric amounts.' : 'SKIP: first product has no priced offers; unpriced products require manual schema review.');
} else console.log('SKIP: no public shop product detected; product schema remains unchecked.');
const robots = await get('/robots.txt', {allowMissing: true});
let sitemapUrl = robots.text.match(/^Sitemap:\s*(\S+)/im)?.[1] || '/wp-sitemap.xml';
const queue = [new URL(sitemapUrl, base)];
const seen = new Set();
let sitemapFiles = 0;
while (queue.length && sitemapFiles < 12) {
	const url = queue.shift();
	if (seen.has(url.href)) continue;
	seen.add(url.href);
	assert.equal(url.origin, base.origin, 'Sitemap must remain on staging');
	const sitemap = await get(url, {allowMissing: true});
	if (sitemap.response.status === 404 && sitemapFiles === 0) { console.log('SKIP: no discovered sitemap; staging noindex can disable WordPress sitemaps.'); break; }
	assert.match(sitemap.text, /<(?:[\w-]+:)?(?:sitemapindex|urlset)\b/, 'Sitemap returns XML');
	sitemapFiles++;
	const links = [...sitemap.text.matchAll(/<loc>\s*([^<]+)\s*<\/loc>/gi)].map(match => new URL(decode(match[1].trim()), base));
	if (/<(?:[\w-]+:)?sitemapindex\b/.test(sitemap.text)) queue.push(...links);
	else for (const link of links) assert.ok(!/(?:^|\/)(?:cart|checkout|my-account|order-tracking|order-received|order-pay)(?:\/|$)/i.test(link.pathname), `Commerce utility URL must not be indexed: ${link.pathname}`);
}
if (queue.length) console.log('LIMIT: checked 12 sitemap files; remaining files need separate review.');
console.log(`PASS: page/HTTPS redirects, ${cssUrls.length} stylesheet responses, staging noindex, fresh IRT cart; inspected ${sitemapFiles} sitemap files.`);
console.log('No Core Web Vitals score measured. Still requires amount-conversion/variation schema checks, filtered URL policy, gateway callbacks, shipping/tax, mail, backups, screen readers and real-device review.');
