// Read-only sample audit of the fictional local storefront. No settings/content writes.
import assert from 'node:assert/strict';
import {mkdirSync,writeFileSync,readFileSync} from 'node:fs';
import {gzipSync} from 'node:zlib';
import {performance} from 'node:perf_hooks';
const origin='http://127.0.0.1:9400';
const directory='.local/seo-audit-2026-10-31';
mkdirSync(directory,{recursive:true});
const decode=s=>s.replace(/&amp;/g,'&').replace(/&quot;/g,'"').replace(/&#0*39;|&apos;/g,"'");
const attrs=tag=>Object.fromEntries([...tag.matchAll(/([\w:-]+)\s*=\s*(["'])(.*?)\2/gs)].map(m=>[m[1].toLowerCase(),decode(m[3])]));
const tags=(html,name)=>[...html.matchAll(new RegExp(`<${name}\\b[^>]*>`,'gi'))].map(m=>attrs(m[0]));
const text=html=>decode(html.replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim());
async function request(path){
	let url=new URL(path,origin);const started=performance.now(),redirects=[];
	for(let i=0;i<8;i++){
		assert.equal(url.origin,origin,'Keep the audit on the local preview');
		const response=await fetch(url,{headers:{Cookie:'playground_auto_login_already_happened=1'},redirect:'manual',signal:AbortSignal.timeout(90000)});
		const headersMs=performance.now()-started;
		if([301,302,303,307,308].includes(response.status)&&response.headers.get('location')){redirects.push({url:url.href,status:response.status});await response.body?.cancel();url=new URL(response.headers.get('location'),url);continue;}
		const body=await response.text();return {response,url,body,redirects,headersMs:Math.round(headersMs),totalMs:Math.round(performance.now()-started)};
	}
	throw new Error('Redirect limit');
}
const rows=[];
async function audit(label,path){
	console.log(`Checking ${label}`);const r=await request(path);const {body}=r;
	writeFileSync(`${directory}/${label}.html`,body);
	// Native Woo scripts contain HTML templates; those are not rendered image/heading nodes.
	const markup=body.replace(/(<script\b[^>]*>)[\s\S]*?(<\/script>)/gi,'$1$2').replace(/(<style\b[^>]*>)[\s\S]*?(<\/style>)/gi,'$1$2');
	const links=tags(markup,'link'),meta=tags(markup,'meta'),images=tags(markup,'img'),scripts=tags(markup,'script');
	const heading=[...markup.matchAll(/<h([1-6])\b[^>]*>([\s\S]*?)<\/h\1>/gi)].map(m=>({level:Number(m[1]),text:text(m[2])}));
	const ids=[...markup.matchAll(/\sid\s*=\s*(["'])(.*?)\1/gi)].map(m=>m[2]);
	const jsonLd=[];for(const match of body.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)){if(attrs(match[1]).type==='application/ld+json'){try{jsonLd.push(JSON.parse(match[2]));}catch{jsonLd.push({parseError:true});}}}
	const row={label,url:r.url.href,status:r.response.status,redirects:r.redirects,headersMs:r.headersMs,totalMs:r.totalMs,htmlBytes:Buffer.byteLength(body),htmlGzipBytes:gzipSync(body).length,headers:Object.fromEntries(['content-type','content-encoding','cache-control','x-robots-tag','vary'].map(k=>[k,r.response.headers.get(k)])),title:text(body.match(/<title\b[^>]*>([\s\S]*?)<\/title>/i)?.[1]||''),h1:heading.filter(h=>h.level===1),headings:heading,canonical:links.filter(l=>l.rel==='canonical').map(l=>l.href),descriptions:meta.filter(m=>m.name==='description').map(m=>m.content),robots:meta.filter(m=>/^(robots|googlebot)$/i.test(m.name||'')).map(m=>m.content),social:meta.filter(m=>/^(og:|twitter:)/.test(m.property||m.name||'')),images:images.map(i=>({src:i.src,alt:i.alt??null,width:i.width,height:i.height,loading:i.loading,fetchpriority:i.fetchpriority,sizes:i.sizes,srcset:i.srcset})),styles:links.filter(l=>l.rel==='stylesheet').map(l=>l.href),scripts:scripts.filter(s=>s.src).map(s=>s.src),internalLinks:tags(markup,'a').filter(a=>a.href&&!a.href.startsWith('#')).map(a=>a.href).filter(h=>{try{return new URL(h,r.url).origin===origin;}catch{return false;}}),duplicateIds:[...new Set(ids.filter((id,index)=>ids.indexOf(id)!==index))],jsonLd,phpFailure:/Fatal error|Uncaught (?:Error|Exception)|<b>Warning<\/b>/.test(body)};
	rows.push(row);writeFileSync(`${directory}/pages.json`,JSON.stringify(rows,null,2));
	console.log(JSON.stringify({page:label,status:row.status,headersMs:row.headersMs,bytes:row.htmlBytes,h1:row.h1.length,description:row.descriptions.length,canonical:row.canonical.length,robots:row.robots}));return row;
}
const api=await request('/?rest_route=/wc/store/v1/products&per_page=100');assert.equal(api.response.status,200);const products=JSON.parse(api.body);
await audit('home','/');await audit('shop','/shop/');await audit('category','/product-category/machines/');await audit('child-category','/product-category/machines/wireless/');
const brands=await audit('brands','/brands/');
const brandLink=readBrandLink();if(brandLink)await audit('brand',brandLink);
function readBrandLink(){return decode(readFileSync(`${directory}/brands.html`,'utf8').match(/class="brand-card" href="([^"]+)"/)?.[1]||'')||null;}
for(const [label,sku] of [['product','PREVIEW-1'],['variable','PREVIEW-3'],['sale','PREVIEW-4'],['enquiry','QA-ENQUIRY'],['unavailable','QA-OUT']]){const p=products.find(p=>p.sku===sku);if(p)await audit(label,p.permalink);}
await audit('journal','/journal/');const posts=await request('/?rest_route=/wp/v2/posts&slug=preview-guide-1');const article=JSON.parse(posts.body)[0];if(article?.link)await audit('article',article.link);
for(const [label,path] of [['about','/about/'],['contact','/contact/'],['search','/?s=PREVIEW&post_type=product'],['filtered','/shop/?min_price=100&orderby=price'],['cart','/cart/'],['account','/my-account/'],['tracking','/order-tracking/'],['missing','/wpmain-audit-missing-20261031/'],['robots','/robots.txt'],['sitemap','/wp-sitemap.xml']])await audit(label,path);
console.log(`Saved ${rows.length} read-only page samples to ${directory}. Local timings are not hosted Core Web Vitals.`);
