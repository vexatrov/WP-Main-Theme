(() => {
	const header = document.querySelector('[data-sticky-header]');
	const button = document.querySelector('.menu-toggle');
	const menu = document.getElementById('primary-menu');
	const nav = header?.querySelector('.primary-navigation');
	const mobile = window.matchMedia('(max-width: 48rem)');
	let compact = false;
	let scheduled = false;
	const branches = [];
	nav?.querySelectorAll('.menu-item-has-children').forEach((item, index) => {
		const submenu = item.querySelector(':scope > .sub-menu');
		const link = item.querySelector(':scope > a');
		if (!submenu || !link) return;
		const toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'submenu-toggle';
		toggle.textContent = '⌄';
		submenu.id ||= `primary-submenu-${index}`;
		toggle.setAttribute('aria-controls', submenu.id);
		toggle.setAttribute('aria-label', nav.dataset.submenuLabel.replace('%s', link.textContent.trim()));
		const close = () => { toggle.setAttribute('aria-expanded', 'false'); submenu.hidden = true; };
		toggle.addEventListener('click', () => {
			const open = toggle.getAttribute('aria-expanded') !== 'true';
			toggle.setAttribute('aria-expanded', String(open));
			submenu.hidden = !open;
		});
		item.addEventListener('keydown', (event) => {
			if (event.key !== 'Escape' || submenu.hidden) return;
			event.stopPropagation();
			close();
			toggle.focus();
		});
		link.after(toggle);
		branches.push(close);
		close();
	});
	const collapsible = () => mobile.matches || compact;
	const setOpen = (open) => {
		if (!button || !menu || !nav) return;
		button.setAttribute('aria-expanded', String(open));
		nav.hidden = collapsible() && !open;
		menu.hidden = nav.hidden;
		if (!open) branches.forEach((close) => close());
	};
	const sync = () => {
		if (button) button.hidden = !collapsible();
		setOpen(false);
	};
	button?.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && collapsible() && button?.getAttribute('aria-expanded') === 'true') {
			setOpen(false);
			button.focus();
		}
	});
	const update = () => {
		scheduled = false;
		if (!header || header.contains(document.activeElement)) return;
		// Separate enter/exit thresholds avoid flicker when the header shrinks.
		const next = compact ? window.scrollY > 32 : window.scrollY > 256;
		if (next === compact) return;
		compact = next;
		header.classList.toggle('is-compact', compact);
		sync();
	};
	const schedule = () => {
		if (scheduled) return;
		scheduled = true;
		window.requestAnimationFrame(update);
	};
	window.addEventListener('scroll', schedule, { passive: true });
	header?.addEventListener('focusout', schedule);
	if (header && 'ResizeObserver' in window) {
		new ResizeObserver(([entry]) => {
			document.documentElement.style.setProperty('--sticky-header-height', `${entry.target.getBoundingClientRect().height}px`);
		}).observe(header);
	}
	mobile.addEventListener('change', sync);
	sync();
	update();
})();
