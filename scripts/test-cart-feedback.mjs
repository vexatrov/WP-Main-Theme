import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import assert from 'node:assert/strict';
const source = readFileSync('themes/wp-main-theme/assets/js/cart-feedback.js', 'utf8');
const events = {}, jq = {};
const makeCount = () => ({ hidden: true, setAttribute(key, value) { this[key] = value; }, getAttribute(key) { return this[key] ?? null; } });
let count = makeCount();
const status = {};
const requests = [], replies = [];
const config = { url: '/wp-json/wc/store/v1/cart', count: 'Cart items: %s', added: 'Added.', updated: 'Updated.', loading: 'Adding.', error: 'Failed.' };
const document = {
	body: { addEventListener(name, fn) { events[name] = fn; } },
	querySelector(selector) { return selector === '[data-cart-count]' ? count : status; },
	querySelectorAll() { return []; },
	addEventListener(name, fn) { events[name] = fn; },
	createElement() { const template = { innerHTML: '' }; template.content = { querySelector() { const value = template.innerHTML.match(/data-count="([^"]*)"/)?.[1]; return value === undefined ? null : { getAttribute: () => value }; } }; return template; },
};
const window = { wpmainCartFeedback: config, addEventListener(name, fn) { events[name] = fn; }, jQuery() { return { on(names, fn) { names.split(' ').forEach(name => jq[name] = fn); }, ajaxError(fn) { jq.error = fn; } }; } };
const fetch = (url, options) => { requests.push({ url, options }); return new Promise(resolve => replies.push(resolve)); };
const tick = () => new Promise(resolve => setImmediate(resolve));
const reply = async (index, items_count, ok = true) => { replies[index]({ ok, json: async () => ({ items_count }) }); await tick(); };
const fragment = number => ({ 'span[data-cart-count]': `<span data-cart-count data-count="${number}">${number}</span>` });
runInNewContext(source, { window, document, fetch, AbortController, setTimeout, clearTimeout, queueMicrotask });
assert.equal(count.hidden, true, 'No cached HTML count is displayed');
assert.equal(requests[0].options.cache, 'no-store');
assert.equal(requests[0].options.credentials, 'same-origin');
events.pageshow({ persisted: false });
assert.equal(requests.length, 1, 'Initial pageshow does not duplicate startup fetch');
const button = { setAttribute(key, value) { this[key] = value; } };
jq.adding_to_cart({}, [button]); assert.equal(button['aria-busy'], 'true');
assert.equal(status.textContent, 'Adding.');
// Blocks' jQuery bridge can run before our classic handler in the same event stack.
events['wc-blocks_added_to_cart']();
jq.added_to_cart({}, fragment(4), '', [button]);
const detached = count; count = makeCount(); // Native fragments replace the element.
await tick();
assert.equal(button['aria-busy'], 'false');
assert.equal(requests.length, 1, 'Authoritative classic fragment needs no extra Store API GET');
assert.equal(requests[0].options.signal.aborted, true, 'Fragment aborts an older session read immediately');
assert.equal(count.textContent, '4'); assert.equal(count.hidden, false);
assert.equal(count['aria-label'], 'Cart items: 4'); assert.equal(detached.textContent, undefined, 'Replaced DOM node is not retained');
assert.equal(status.textContent, 'Added. Cart items: 4');
await reply(0, 99); assert.equal(count.textContent, '4', 'Late aborted read cannot overwrite an authoritative fragment');
jq.removed_from_cart({}, fragment(0)); events['wc-blocks_removed_from_cart'](); await tick();
assert.equal(requests.length, 1, 'Remove fragment and Blocks bridge coalesce without GET');
assert.equal(count.textContent, '0'); assert.equal(status.textContent, 'Updated. Cart items: 0');
events['wc-blocks_added_to_cart'](); await tick();
assert.equal(requests.length, 2, 'Blocks-only updates still fetch');
events['wc-blocks_removed_from_cart'](); await tick();
assert.equal(requests[1].options.signal.aborted, true, 'A newer read aborts the previous read');
await reply(2, 1); await reply(1, 5); assert.equal(count.textContent, '1', 'Older reads cannot overwrite newer reads');
events.pageshow({ persisted: true }); await reply(3, 0, false);
assert.equal(count.hidden, true, 'Failed refresh never leaves a stale count');
jq.added_to_cart({}, fragment('3.5'), '', [button]); await tick();
assert.equal(requests.length, 5, 'Malformed fragment count falls back to supported cart API');
await reply(4, 2); assert.equal(count.textContent, '2');
count['data-count'] = '7'; jq.wc_fragments_refreshed(); await tick();
assert.equal(requests.length, 5, 'Fresh native fragment refresh reuses its authoritative DOM count');
assert.equal(count.textContent, '7');
jq.error({}, {}, { url: '/?wc-ajax=other' }); assert.notEqual(status.textContent, 'Failed.');
jq.error({}, {}, { url: '/?wc-ajax=add_to_cart' }); assert.equal(status.textContent, 'Failed.');
console.log('PASS: cache-safe count, authoritative add/remove fragments, replaced nodes, zero quantities, event coalescing, no redundant GET, stale-race cancellation, fallback failures and AJAX feedback.');
