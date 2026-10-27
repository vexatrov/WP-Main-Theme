(() => {
	const config = window.wpmainCartFeedback;
	const status = document.querySelector('[data-cart-feedback]');
	if (!config || !document.querySelector('[data-cart-count]') || !status) return;
	let request = 0;
	let active;
	let queued = false;
	let pendingMessage = '';
	let pendingCount = null;
	const say = (message) => { status.textContent = message; };
	const show = (value, message) => {
		// WooCommerce replaces fragment elements; always resolve the current node.
		const count = document.querySelector('[data-cart-count]');
		if (!count) return;
		count.textContent = String(value);
		count.setAttribute('data-count', String(value));
		count.setAttribute('aria-label', config.count.replace('%s', String(value)));
		count.hidden = false;
		if (message) say(`${message} ${config.count.replace('%s', String(value))}`);
	};
	const readCount = (node) => {
		const value = node?.getAttribute('data-count');
		if (typeof value !== 'string' || !/^(0|[1-9]\d*)$/.test(value)) return null;
		const number = Number(value);
		return Number.isSafeInteger(number) ? number : null;
	};
	const fragmentCount = (fragments) => {
		const html = fragments?.['span[data-cart-count]'];
		if (typeof html !== 'string') return null;
		const template = document.createElement('template');
		template.innerHTML = html;
		return readCount(template.content.querySelector('span[data-cart-count]'));
	};
	const refresh = async (message = '') => {
		const current = ++request;
		active?.abort();
		const controller = new AbortController();
		active = controller;
		const timeout = setTimeout(() => controller.abort(), 8000);
		try {
			const response = await fetch(config.url, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal, headers: { Accept: 'application/json' } });
			if (!response.ok) throw new Error('cart');
			const cart = await response.json();
			if (current !== request || !Number.isInteger(cart.items_count) || cart.items_count < 0) return;
			show(cart.items_count, message);
		} catch {
			if (current === request) { const count = document.querySelector('[data-cart-count]'); if (count) count.hidden = true; if (message) say(message); }
		} finally {
			clearTimeout(timeout);
		}
	};
	const queueRefresh = (message, value = null) => {
		pendingMessage = message;
		if (value !== null) {
			pendingCount = value;
			++request;
			active?.abort();
		}
		if (queued) return;
		queued = true;
		queueMicrotask(() => {
			queued = false;
			const value = pendingCount;
			pendingCount = null;
			if (value !== null) show(value, pendingMessage);
			else refresh(pendingMessage);
		});
	};
	const busy = (button, value) => button?.setAttribute('aria-busy', String(value));
	if (window.jQuery) {
		const body = window.jQuery(document.body);
		body.on('adding_to_cart', (_event, button) => { busy(button?.[0], true); say(config.loading); });
		body.on('added_to_cart', (_event, fragments, _hash, button) => { busy(button?.[0], false); queueRefresh(config.added, fragmentCount(fragments)); });
		body.on('removed_from_cart', (_event, fragments) => queueRefresh(config.updated, fragmentCount(fragments)));
		body.on('updated_wc_div', () => queueRefresh(config.updated));
		body.on('wc_fragments_refreshed', () => queueRefresh(config.updated, readCount(document.querySelector('[data-cart-count]'))));
		window.jQuery(document).ajaxError((_event, _xhr, settings) => {
			if (!/[?&]wc-ajax=add_to_cart(?:&|$)/.test(settings?.url || '')) return;
			document.querySelectorAll('.add_to_cart_button[aria-busy="true"]').forEach((button) => { busy(button, false); button.classList.remove('loading'); });
			say(config.error);
		});
	}
	document.body.addEventListener('wc-blocks_added_to_cart', () => queueRefresh(config.added));
	document.body.addEventListener('wc-blocks_removed_from_cart', () => queueRefresh(config.updated));
	window.addEventListener('pageshow', (event) => { if (event.persisted) refresh(); });
	document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') refresh(); });
	refresh();
})();
