(() => {
	const config = window.wpmainCardVariations;
	const dialog = document.querySelector('[data-card-options]');
	const $ = window.jQuery;
	if (!config || !dialog || typeof dialog.showModal !== 'function' || !$?.fn?.wc_variation_form) return;
	const content = dialog.querySelector('[data-card-options-content]');
	const status = dialog.querySelector('[data-card-options-status]');
	const title = dialog.querySelector('#card-options-title');
	const details = dialog.querySelector('[data-card-options-link]');
	const close = dialog.querySelector('[data-card-options-close]');
	let opener;
	let controller;
	let request = 0;
	const clear = () => {
		++request;
		controller?.abort();
		$(content).find('.variations_form').trigger('reset_data').off();
		content.replaceChildren();
		content.removeAttribute('aria-busy');
		if (opener?.isConnected) opener.focus();
	};
	close.addEventListener('click', () => dialog.close());
	dialog.addEventListener('close', clear);
	dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
	document.addEventListener('click', async event => {
		const link = event.target.closest('a[data-card-variations]');
		if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		event.preventDefault();
		opener = link;
		details.href = link.href;
		title.textContent = config.loading;
		status.textContent = config.loading;
		content.replaceChildren();
		content.setAttribute('aria-busy', 'true');
		if (!dialog.open) dialog.showModal();
		close.focus();
		controller?.abort();
		const currentController = new AbortController();
		controller = currentController;
		const current = ++request;
		const timeout = setTimeout(() => currentController.abort(), 30000);
		try {
			const url = new URL(config.url, window.location.href);
			url.searchParams.set('action', 'wpmain_card_variations');
			url.searchParams.set('product_id', link.dataset.cardVariations);
			const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', signal: currentController.signal });
			if (!response.ok) throw new Error('options');
			const result = await response.json();
			if (current !== request || !dialog.open) return;
			if (!result.success || typeof result.data?.html !== 'string') throw new Error('form');
			content.innerHTML = result.data.html;
			const form = content.querySelector('form.variations_form');
			if (!form) throw new Error('form');
			const returnUrl = new URL(window.location.href);
			returnUrl.hash = '';
			form.action = returnUrl.href;
			// Avoid duplicate native attribute/quantity IDs on product pages and rails.
			form.querySelectorAll('[id]').forEach(element => {
				const old = element.id;
				element.id = `card-options-${old}`;
				form.querySelectorAll('label[for]').forEach(label => { if (label.htmlFor === old) label.htmlFor = element.id; });
			});
			title.textContent = result.data.name;
			$(form).wc_variation_form();
			status.textContent = '';
			(form.querySelector('select') || close).focus();
		} catch {
			if (current === request && dialog.open) { content.replaceChildren(); status.textContent = config.error; }
		} finally {
			clearTimeout(timeout);
			if (current === request) content.removeAttribute('aria-busy');
		}
	});
})();
