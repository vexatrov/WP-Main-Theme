// Creates a fictional COD order only on the guarded localhost sample store. No gateway or email delivery.
import assert from 'node:assert/strict';
const base='http://127.0.0.1:9400';
const guest={Cookie:'playground_auto_login_already_happened=1'};
async function api(route,{token,method='GET',data}={}) {
	const response=await fetch(`${base}/?rest_route=/wc/store/v1/${route}`,{method,headers:{...guest,'Content-Type':'application/json',...(token?{'Cart-Token':token}:{})},body:data?JSON.stringify(data):undefined,signal:AbortSignal.timeout(60000)});
	const value=await response.json();
	assert.ok(response.ok,`${route}: ${response.status} ${JSON.stringify(value).slice(0,500)}`);
	return {value,token:response.headers.get('cart-token')||token};
}
const home=await fetch(base,{headers:guest}); assert.ok((await home.text()).includes('ابزار تاتو / پیش‌نمایش'),'Refuse non-demo store');
const product=(await api('products&sku=PREVIEW-1')).value[0];
const {token}=await api('cart');
await api('cart/add-item',{token,method:'POST',data:{id:product.id,quantity:1}});
const address={first_name:'QA',last_name:'Shopper',address_1:'Fictional test address',address_2:'',city:'Tehran',state:'THR',postcode:'1111111111',country:'IR'};
const cart=(await api('cart/update-customer',{token,method:'POST',data:{billing_address:{...address,email:'qa-order@example.invalid',phone:'09000000000'},shipping_address:address}})).value;
const rate=cart.shipping_rates?.[0]?.shipping_rates?.[0]; assert.ok(rate,'Test shipping rate is available');
await api('cart/select-shipping-rate',{token,method:'POST',data:{package_id:0,rate_id:rate.rate_id}});
const result=(await api('checkout',{token,method:'POST',data:{billing_address:{...address,email:'qa-order@example.invalid',phone:'09000000000'},shipping_address:address,payment_method:'cod',payment_data:[],customer_note:'Fictional theme QA order; no real goods, payment or delivery.'}})).value;
assert.ok(result.order_id);
assert.equal(result.payment_result?.payment_status,'success');
const received=new URL(result.payment_result.redirect_url);
assert.equal(received.origin,base);
const page=await fetch(received,{headers:guest,signal:AbortSignal.timeout(60000)});
assert.equal(page.status,200); assert.match(await page.text(),/woocommerce-order/);
const incorrect=new URL(received); incorrect.searchParams.set('key','incorrect-qa-key');
const denied=await fetch(incorrect,{headers:guest,signal:AbortSignal.timeout(60000)});
assert.doesNotMatch(await denied.text(),/woocommerce-order-overview/,'Receipt cannot be read with an incorrect key');
const trackingPage=await fetch(base+'/order-tracking/',{headers:guest,signal:AbortSignal.timeout(60000)});
const trackingNonce=(await trackingPage.text()).match(/name="woocommerce-order-tracking-nonce"[^>]*value="([^"]+)"/)?.[1];
assert.ok(trackingNonce);
async function track(email) {
	const response=await fetch(base+'/order-tracking/',{method:'POST',headers:{...guest,'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({orderid:String(result.order_id),order_email:email,track:'track','woocommerce-order-tracking-nonce':trackingNonce}),signal:AbortSignal.timeout(60000)});
	return response.text();
}
assert.match(await track('qa-order@example.invalid'),/class="order-info"/,'Correct order/email opens tracking status');
assert.doesNotMatch(await track('wrong@example.invalid'),/class="order-info"/,'Incorrect email cannot read that order');
assert.equal((await api('cart',{token})).value.items_count,0,'Completed test order clears the cart');
console.log('PASS: fictional COD checkout, shipping selection, receipt/key protection, correct/incorrect tracking email and cart clearing. No Zarinpal/payment callback was tested.');
