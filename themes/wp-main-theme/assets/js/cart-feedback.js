(() => {
	const config = window.wpmainCartFeedback;
	const count = document.querySelector('[data-cart-count]');
	const status = document.querySelector('[data-cart-feedback]');
	if (!config || !count || !status) return;
	let request = 0;
	let active;
	let queued = false;
	let pendingMessage = '';
	const say = (message) => { status.textContent = message; };
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
			count.textContent = String(cart.items_count);
			count.setAttribute('aria-label', config.count.replace('%s', String(cart.items_count)));
			count.hidden = false;
			if (message) say(`${message} ${config.count.replace('%s', String(cart.items_count))}`);
		} catch {
			if (current === request) { count.hidden = true; if (message) say(message); }
		} finally {
			clearTimeout(timeout);
		}
	};
	const queueRefresh = (message) => {
		pendingMessage = message;
		if (queued) return;
		queued = true;
		queueMicrotask(() => { queued = false; refresh(pendingMessage); });
	};
	const busy = (button, value) => button?.setAttribute('aria-busy', String(value));
	if (window.jQuery) {
		const body = window.jQuery(document.body);
		body.on('adding_to_cart', (_event, button) => { busy(button?.[0], true); say(config.loading); });
		body.on('added_to_cart', (_event, _fragments, _hash, button) => { busy(button?.[0], false); queueRefresh(config.added); });
		body.on('removed_from_cart updated_wc_div wc_fragments_refreshed', () => queueRefresh(config.updated));
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
