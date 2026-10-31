/* Admin-only controls. Durable values remain native post/term/theme metadata. */
(() => {
	'use strict';
	const config = window.wpmainEditor;
	if (!config) return;
	const button = (text, label, click) => {
		const node = document.createElement('button');
		node.type = 'button'; node.className = 'button'; node.textContent = text;
		if (label) node.setAttribute('aria-label', label);
		node.addEventListener('click', click); return node;
	};
	const format = (text, value) => text.replace('%s', String(value));
	const initialize = () => {
	document.querySelectorAll('.wpmain-editor-media').forEach(root => {
		if (!window.wp?.media || root.dataset.wpmainReady) return;
		root.dataset.wpmainReady = '1';
		const value = root.querySelector('.wpmain-media-value');
		const preview = root.querySelector('.wpmain-media-preview');
		const name = root.querySelector('.wpmain-media-name');
		const remove = root.querySelector('.wpmain-media-remove');
		value.type = 'hidden'; root.querySelector('.wpmain-media-ui').hidden = false;
		let frame;
		root.querySelector('.wpmain-media-choose').addEventListener('click', () => {
			if (!frame) {
				frame = window.wp.media({ title: config.choose, button: { text: config.use }, library: { type: 'image' }, multiple: false });
				frame.on('select', () => {
					const image = frame.state().get('selection').first().toJSON();
					value.value = String(image.id); value.dispatchEvent(new Event('change', { bubbles: true }));
					preview.src = image.sizes?.thumbnail?.url || image.url; preview.hidden = false;
					name.textContent = image.title || image.filename || ''; remove.hidden = false;
				});
				frame.on('open', () => { const selection = frame.state().get('selection'); selection.reset(); if (value.value) selection.add(window.wp.media.attachment(Number(value.value))); });
			}
			frame.open();
		});
		remove.addEventListener('click', () => {
			value.value = ''; value.dispatchEvent(new Event('change', { bubbles: true }));
			preview.removeAttribute('src'); preview.hidden = true; name.textContent = ''; remove.hidden = true;
			root.querySelector('.wpmain-media-choose').focus();
		});
	});
	document.querySelectorAll('.wpmain-editor-picker').forEach(root => {
		if (root.dataset.wpmainReady) return;
		const value = root.querySelector('.wpmain-picker-value');
		const search = root.querySelector('.wpmain-picker-search');
		const selected = root.querySelector('.wpmain-picker-selected');
		const results = root.querySelector('.wpmain-picker-results');
		const status = root.querySelector('.wpmain-picker-status');
		const limit = Number(root.dataset.limit);
		let items;
		try { items = JSON.parse(root.dataset.items); } catch { return; }
		if (!Array.isArray(items) || !Number.isInteger(limit) || limit < 1) return;
		root.dataset.wpmainReady = '1';
		value.type = 'hidden'; root.querySelector('.wpmain-picker-ui').hidden = false;
		let timer, active, generation = 0;
		const hint = () => format(config.hint, limit);
		const render = () => {
			selected.replaceChildren();
			items.forEach((item, index) => {
				const li = document.createElement('li');
				const title = document.createElement('span'); title.textContent = item.title; li.append(title);
				const actions = document.createElement('span'); actions.className = 'wpmain-picker-actions';
				const move = (offset) => { const next = index + offset; [items[index], items[next]] = [items[next], items[index]]; commit(); selected.children[next]?.querySelector('button:not([disabled])')?.focus(); };
				const earlier = button('↑', format(config.earlier, item.title), () => move(-1)); earlier.disabled = index === 0;
				const later = button('↓', format(config.later, item.title), () => move(1)); later.disabled = index === items.length - 1;
				actions.append(earlier, later, button('×', format(config.remove, item.title), () => { items.splice(index, 1); commit(); search.focus(); }));
				li.append(actions); selected.append(li);
			});
			status.textContent = hint();
		};
		const commit = () => {
			value.value = items.map(item => item.id).join(',');
			value.dispatchEvent(new Event('input', { bubbles: true }));
			value.dispatchEvent(new Event('change', { bubbles: true }));
			results.hidden = true; render();
		};
		render();
		search.addEventListener('input', () => {
			clearTimeout(timer); active?.abort(); const current = ++generation;
			results.hidden = true; results.replaceChildren();
			const term = search.value.trim();
			if ([...term].length < 2) { status.textContent = hint(); return; }
			timer = setTimeout(async () => {
				const controller = new AbortController(); active = controller; status.textContent = config.loading;
				const timeout = setTimeout(() => controller.abort(), 30000);
				try {
					const url = new URL(config.url, document.baseURI);
					url.search = new URLSearchParams({ action: 'wpmain_editor_search', security: config.nonce, kind: root.dataset.kind, term }).toString();
					const response = await fetch(url, { credentials: 'same-origin', signal: controller.signal });
					if (!response.ok) throw new Error('search');
					const data = await response.json();
					if (current !== generation) return;
					if (!data.success || !Array.isArray(data.data)) throw new Error('search');
					results.replaceChildren();
					data.data.filter(item => !items.some(chosen => chosen.id === item.id)).forEach(item => {
						const li = document.createElement('li');
						li.append(button(item.title, '', () => {
							if (items.length >= limit) { status.textContent = config.limit; return; }
							items.push(item); search.value = ''; commit(); search.focus();
						})); results.append(li);
					});
					results.hidden = !results.children.length;
					status.textContent = results.hidden ? config.empty : hint();
				} catch { if (current === generation) status.textContent = config.error; }
				finally { clearTimeout(timeout); }
			}, 300);
		});
		search.addEventListener('keydown', event => {
			if (event.key === 'ArrowDown' && !results.hidden) { event.preventDefault(); results.querySelector('button')?.focus(); }
			if (event.key === 'Escape') { ++generation; clearTimeout(timer); active?.abort(); results.hidden = true; status.textContent = hint(); }
		});
		results.addEventListener('keydown', event => {
			const buttons = [...results.querySelectorAll('button')]; const index = buttons.indexOf(document.activeElement);
			if (event.key === 'Escape') { results.hidden = true; search.focus(); }
			if (event.key === 'ArrowDown') { event.preventDefault(); buttons[Math.min(index + 1, buttons.length - 1)]?.focus(); }
			if (event.key === 'ArrowUp') { event.preventDefault(); if (index <= 0) search.focus(); else buttons[index - 1]?.focus(); }
		});
	});
	};
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
	else initialize();
	window.wp?.customize?.bind('ready', initialize);
})();
