(() => {
	const button = document.querySelector('.menu-toggle');
	const menu = document.getElementById('primary-menu');
	if (!button || !menu) return;
	const mobile = window.matchMedia('(max-width: 48rem)');
	const setOpen = (open) => {
		button.setAttribute('aria-expanded', String(open));
		menu.hidden = mobile.matches && !open;
	};
	const sync = () => {
		button.hidden = !mobile.matches;
		setOpen(false);
	};
	button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && mobile.matches && button.getAttribute('aria-expanded') === 'true') {
			setOpen(false);
			button.focus();
		}
	});
	mobile.addEventListener('change', sync);
	sync();
})();
