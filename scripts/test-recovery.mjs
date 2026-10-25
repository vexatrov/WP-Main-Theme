import assert from 'node:assert/strict';
const base = 'http://127.0.0.1:9400';
const headers = {Cookie:'playground_auto_login_already_happened=1'};
async function form(path, fields, nonceName) {
	const page = await fetch(base+path,{headers,signal:AbortSignal.timeout(60000)});
	assert.equal(page.status,200);
	const html = await page.text();
	const nonce = html.match(new RegExp(`name="${nonceName}"[^>]*value="([^"]+)"`))?.[1];
	assert.ok(nonce,nonceName);
	const response = await fetch(base+path,{method:'POST',headers:{...headers,'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({...fields,[nonceName]:nonce}),signal:AbortSignal.timeout(60000)});
	assert.equal(response.status,200);
	return {html:await response.text(),url:response.url};
}
const recovery = await form('/my-account/lost-password/',{user_login:'qa-shopper',wc_reset_password:'true'},'woocommerce-lost-password-nonce');
assert.ok(recovery.url.includes('reset-link-sent=true'),'Successful native password recovery request');
const invalid = await form('/my-account/lost-password/',{user_login:'no-such-qa-account@example.invalid',wc_reset_password:'true'},'woocommerce-lost-password-nonce');
assert.match(invalid.html,/woocommerce-error/,'Invalid recovery shows native error');
const tracking = await form('/order-tracking/',{orderid:'999999999',order_email:'nobody@example.invalid',track:'پیگیری'},'woocommerce-order-tracking-nonce');
assert.match(tracking.html,/woocommerce-error|woocommerce-info/);
assert.doesNotMatch(tracking.html,/woocommerce-order-details/,'Unknown order is not disclosed');
console.log('PASS: native recovery request, invalid recovery, nonce forms and denied unknown order lookup. Emails are captured locally, not delivered.');
