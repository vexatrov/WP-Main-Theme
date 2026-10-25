// Behavioral unit checks for the progressive enhancement; no real browser is claimed.
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';
let active, scheduled, requests=[];
class Element {
	constructor(tag='div') {this.tag=tag;this.children=[];this.attrs={};this.handlers={};this.hidden=false;this.value='';}
	setAttribute(key,value){this.attrs[key]=value;}
	append(...items){this.children.push(...items);}
	replaceChildren(...items){this.children=items;}
	addEventListener(name,handler){this.handlers[name]=handler;}
	focus(){active=this;}
	contains(target){return target===this||this.children.some(child=>child.contains(target));}
	querySelectorAll(tag){return this.children.flatMap(child=>[...(child.tag===tag?[child]:[]),...child.querySelectorAll(tag)]);}
}
const input=new Element('input'),panel=new Element(),status=new Element(),groups=new Element(),form=new Element('form');
form.append(input,panel);panel.append(status,groups);panel.id='suggestions';panel.hidden=true;
form.dataset={endpoint:'http://127.0.0.1:9400/wp-json/wpmain/v1/suggestions',loading:'Loading',empty:'Empty',error:'Submit search instead',products:'Products',categories:'Categories',brands:'Brands',results:'Ready'};
form.querySelector=selector=>selector.startsWith('input')?input:panel;
panel.querySelector=selector=>selector.startsWith('[role')?status:groups;
const document={querySelectorAll:()=>[form],createElement:tag=>new Element(tag),get activeElement(){return active;}};
const context={document,location:{href:'http://127.0.0.1:9400/',origin:'http://127.0.0.1:9400'},URL,AbortController,setTimeout:fn=>(scheduled=fn,1),clearTimeout:()=>{scheduled=null;},fetch:(url,options)=>new Promise(resolve=>requests.push({url,options,resolve}))};
vm.runInNewContext(readFileSync('themes/wp-main-theme/assets/js/search.js','utf8'),context);
input.value='Mast';form.handlers.input();const old=scheduled();
input.value='EZ';form.handlers.input();const current=scheduled();
const response=label=>({ok:true,json:async()=>({products:[{label,url:'http://127.0.0.1:9400/product/sample/'}],categories:[],brands:[]})});
requests[1].resolve(response('Current'));await current;
requests[0].resolve(response('Obsolete'));await old;
assert.equal(groups.querySelectorAll('a')[0].textContent,'Current','Late response cannot replace current suggestions');
assert.equal(input.attrs['aria-expanded'],'true');input.focus();
form.handlers.keydown({key:'ArrowDown',preventDefault(){}});
assert.equal(active,groups.querySelectorAll('a')[0],'Down arrow moves to suggestion');
form.handlers.keydown({key:'Escape'});assert.equal(panel.hidden,true);assert.equal(active,input);
input.value='Bad network';form.handlers.input();const error=scheduled();requests[2].resolve({ok:false});await error;
assert.equal(status.textContent,'Submit search instead','Failed suggestions preserve submitted search fallback');
form.handlers.submit();assert.equal(panel.hidden,true);
input.value='x';form.handlers.input();assert.equal(panel.hidden,true,'Short input closes suggestions');
console.log('PASS: obsolete responses ignored, keyboard browsing/Escape, accessible expanded state, failed-network fallback and submit cleanup.');
