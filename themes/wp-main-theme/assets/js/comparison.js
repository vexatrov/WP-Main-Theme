/* Progressive comparison search; the native GET form works without JavaScript. */
(() => {
	'use strict';
	const init = () => {
		if (location.hash === '#tab-wpmain_comparison') {
			history.replaceState(null, '', location.pathname + location.search + '#tab-additional_information');
			document.querySelector('a[href="#tab-additional_information"]')?.click();
			document.getElementById('tab-additional_information')?.scrollIntoView({block:'start'});
		}
		document.querySelectorAll('[data-comparison]').forEach(root => {
			const input = root.querySelector('input[type="search"]');
			const rail = root.querySelector('.product-comparison-choices');
			const status = root.querySelector('.comparison-status');
			const counter = root.querySelector('[data-comparison-count]');
			const selected = new Map();
			const initial = [...rail.querySelectorAll('label')].map(label => {
				const box = label.querySelector('input');
				const item = {id:Number(box.value),label:label.dataset.name,image:label.querySelector('img')?.getAttribute('src') || ''};
				if (box.checked) selected.set(item.id,item); return item;
			});
			let generation = 0, timer, controller;
			const update = () => {
				counter.textContent = `${selected.size} / 3`;
				rail.querySelectorAll('input[type="checkbox"]').forEach(box => { box.disabled = !box.checked && selected.size >= 3; });
			};
			const render = products => {
				const items = new Map([...selected, ...products.map(item=>[item.id,item])]);
				rail.replaceChildren();
				items.forEach(item => {
					if (!Number.isSafeInteger(item.id) || item.id <= 0 || typeof item.label !== 'string') return;
					const label = document.createElement('label'); label.className = 'product-comparison-choice'; label.dataset.name = item.label;
					const box = document.createElement('input'); box.type='checkbox'; box.name='compare_product[]'; box.value=String(item.id); box.checked=selected.has(item.id);
					const title = document.createElement('span'); title.textContent=item.label;
					label.append(box);
					if (item.image) { const url=new URL(item.image,document.baseURI); if (['https:','http:'].includes(url.protocol)) { const image=document.createElement('img'); image.src=url.href; image.alt=''; image.loading='lazy'; label.append(image); } }
					label.append(title); rail.append(label);
				});
				update();
			};
			rail.addEventListener('change', event => {
				const box = event.target;
				if (!box.matches('input[type="checkbox"]')) return;
				const id = Number(box.value);
				if (box.checked && selected.size < 3) { const label=box.closest('label'); selected.set(id,{id,label:label.dataset.name,image:label.querySelector('img')?.getAttribute('src')||''}); }
				else { selected.delete(id); box.checked=false; }
				update();
			});
			input.addEventListener('input', () => {
				clearTimeout(timer); controller?.abort(); const current=++generation;
				const query=input.value.trim();
				if ([...query].length < 2) { status.textContent=''; render(initial); return; }
				timer=setTimeout(async()=>{
					const active=new AbortController(); controller=active;
					const timeout=setTimeout(()=>active.abort(),30000); status.textContent=root.dataset.loading;
					try {
						const url=new URL(root.dataset.endpoint,document.baseURI); url.searchParams.set('q',query); url.searchParams.set('exclude',root.dataset.current);
						const response=await fetch(url,{credentials:'same-origin',signal:active.signal}); if(!response.ok)throw new Error('search');
						const data=await response.json(); if(current!==generation)return;
						if(!Array.isArray(data.products))throw new Error('search');
						render(data.products); status.textContent=data.products.length?'':root.dataset.empty;
					} catch {if(current===generation)status.textContent=root.dataset.error;}
					finally {clearTimeout(timeout);}
				},300);
			});
			input.addEventListener('keydown',event=>{if(event.key==='Escape'){++generation;clearTimeout(timer);controller?.abort();input.value='';status.textContent='';render(initial);}if(event.key==='ArrowDown'){event.preventDefault();rail.querySelector('input:not(:disabled)')?.focus();}});
			update();
		});
	};
	if(window.jQuery)window.jQuery(init);else if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
