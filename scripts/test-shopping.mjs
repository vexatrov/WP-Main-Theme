import assert from 'node:assert/strict';
const base = 'http://127.0.0.1:9400';
const guest = {Cookie: 'playground_auto_login_already_happened=1'};
async function call(route, {method = 'GET', data, token, status = 200} = {}) {
	const response = await fetch(`${base}/?rest_route=/wc/store/v1/${route}`, {method, headers: {...guest, 'Content-Type':'application/json', ...(token ? {'Cart-Token':token} : {})}, body:data ? JSON.stringify(data) : undefined, signal:AbortSignal.timeout(60000)});
	const value = await response.json();
	assert.equal(response.status, status, `${route}: ${JSON.stringify(value).slice(0,200)}`);
	return {value, token:response.headers.get('cart-token') || token};
}
const pen = (await call('products&sku=PREVIEW-1')).value[0];
let {token} = await call('cart');
assert.ok(token);
let cart = (await call('cart/add-item',{method:'POST', token, data:{id:pen.id,quantity:1},status:201})).value;
assert.equal(cart.items_count,1);
const key = cart.items[0].key;
cart = (await call('cart/update-item',{method:'POST',token,data:{key,quantity:3}})).value;
assert.equal(cart.items_count,3);
assert.equal(cart.totals.currency_code,'IRT');
console.log('Checking cart errors and checkout validation');
await call('cart/apply-coupon',{method:'POST',token,data:{code:'invalid-qa-coupon'},status:400});
await call('checkout',{method:'POST',token,data:{billing_address:{},shipping_address:{},payment_method:'cod'},status:400});
const variable = (await call('products&sku=PREVIEW-3')).value[0];
await call('cart/add-item',{method:'POST',token,data:{id:variable.id,quantity:1},status:400});
assert.ok(variable.variations[0]?.id, 'Fixture exposes valid variation IDs');
cart = (await call('cart/add-item',{method:'POST',token,data:{id:variable.variations[0].id,quantity:1},status:201})).value;
assert.equal(cart.items_count,4,'A selected variation can be added');
const variationKey = cart.items.find(item => item.id !== pen.id).key;
await call('cart/remove-item',{method:'POST',token,data:{key:variationKey}});
const out = (await call('products&sku=QA-OUT')).value[0];
await call('cart/add-item',{method:'POST',token,data:{id:out.id,quantity:1},status:400});
cart = (await call('cart/remove-item',{method:'POST',token,data:{key}})).value;
assert.equal(cart.items_count,0);
assert.equal((await call('cart')).value.items_count,0,'Fresh visitor remains isolated');
for (const path of ['/my-account/','/my-account/lost-password/','/my-account/orders/','/my-account/edit-address/','/order-tracking/']) {
	const response = await fetch(base+path,{headers:guest,signal:AbortSignal.timeout(60000)});
	assert.equal(response.status,200,path); assert.doesNotMatch(await response.text(),/Fatal error|Uncaught Error/);
}
console.log('PASS: add/update/remove, invalid coupon, missing variation, out-of-stock rejection, empty checkout validation, guest isolation and account/recovery/tracking pages.');
