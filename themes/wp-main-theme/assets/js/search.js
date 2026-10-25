(() => {
	'use strict';
	document.querySelectorAll('[data-product-search]').forEach(form => {
		const input = form.querySelector('input[type="search"]');
		const panel = form.querySelector('.search-suggestions');
		const groups = panel.querySelector('.suggestion-groups');
		const status = panel.querySelector('[role="status"]');
		let timer, controller, generation = 0;
		input.setAttribute('aria-controls', panel.id);
		input.setAttribute('role', 'combobox');
		input.setAttribute('aria-haspopup', 'dialog');
		input.setAttribute('aria-autocomplete', 'list');
		input.setAttribute('aria-expanded', 'false');
		input.setAttribute('autocomplete', 'off');
		const close = () => { panel.hidden = true; input.setAttribute('aria-expanded', 'false'); };
		const open = () => { panel.hidden = false; input.setAttribute('aria-expanded', 'true'); };
		form.addEventListener('input', () => {
			clearTimeout(timer);
			controller?.abort();
			const request = ++generation;
			const query = input.value.trim();
			groups.replaceChildren();
			if (Array.from(query).length < 2) { close(); return; }
			timer = setTimeout(async () => {
				controller = new AbortController();
				open(); status.textContent = form.dataset.loading;
				try {
					const url = new URL(form.dataset.endpoint);
					url.searchParams.set('q', query);
					const response = await fetch(url, {signal: controller.signal, credentials: 'same-origin'});
					if (!response.ok) throw new Error('suggestions');
					const data = await response.json();
					if (request !== generation || input.value.trim() !== query) return;
					let count = 0;
					['products', 'categories', 'brands'].forEach(key => {
						if (!Array.isArray(data[key]) || !data[key].length) return;
						const section = document.createElement('section');
						const heading = document.createElement('h3'); heading.textContent = form.dataset[key];
						const list = document.createElement('ul');
						data[key].slice(0, 6).forEach(item => {
							const target = new URL(item.url, location.href);
							if (!['http:', 'https:'].includes(target.protocol) || target.origin !== location.origin) return;
							const row = document.createElement('li'); const link = document.createElement('a');
							link.href = target.href; link.textContent = item.label; row.append(link); list.append(row); count++;
						});
						section.append(heading, list); groups.append(section);
					});
					status.textContent = count ? form.dataset.results : form.dataset.empty;
				} catch (error) {
					if (error.name !== 'AbortError' && request === generation) status.textContent = form.dataset.error;
				}
			}, 250);
		});
		form.addEventListener('keydown', event => {
			if (event.key === 'Escape') { generation++; controller?.abort(); clearTimeout(timer); close(); input.focus(); return; }
			if (panel.hidden || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
			const links = [...panel.querySelectorAll('a')];
			if (!links.length) return;
			event.preventDefault();
			const index = links.indexOf(document.activeElement);
			const next = event.key === 'ArrowDown' ? index + 1 : index < 0 ? links.length - 1 : index - 1;
			if (next < 0 || next >= links.length) input.focus(); else links[next].focus();
		});
		form.addEventListener('focusout', event => { if (!form.contains(event.relatedTarget)) { generation++; controller?.abort(); clearTimeout(timer); close(); } });
		form.addEventListener('submit', () => { generation++; controller?.abort(); clearTimeout(timer); close(); });
	});
})();
