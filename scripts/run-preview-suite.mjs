// Fresh isolated WordPress/WooCommerce integration suite. Never reuses .local/site.
import {runCLI} from '@wp-playground/cli';
import {readFileSync, existsSync, mkdirSync, mkdtempSync, writeFileSync, copyFileSync} from 'node:fs';
import {resolve} from 'node:path';
import {spawn} from 'node:child_process';
import assert from 'node:assert/strict';

assert.ok(process.env.CI || process.argv.includes('--isolated'), 'Use CI=true or --isolated to explicitly opt into a fresh disposable fixture database.');
const fixturesOnly = process.argv.includes('--fixtures-only');
const port = fixturesOnly ? 9401 : 9400;
const origin = `http://127.0.0.1:${port}`;
let occupied = false;
try { await fetch(origin, {signal: AbortSignal.timeout(1500)}); occupied = true; } catch (error) { if (error.name === 'TimeoutError') occupied = true; }
assert.ok(!occupied, `Port ${port} is occupied. Stop that server before starting the isolated suite.`);
const watchdog = setTimeout(() => { console.error('Isolated integration suite exceeded 15 minutes.'); process.exit(1); }, 900000);
watchdog.unref();
mkdirSync('.local', {recursive: true});
const sitePath = mkdtempSync(resolve('.local/ci-site-'));
const logs = resolve('.local/ci-logs');
mkdirSync(logs, {recursive: true}); mkdirSync('.local/cache', {recursive: true});
async function archive(name, url) {
	const path = resolve('.local/cache', name);
	if (!existsSync(path)) {
		const response = await fetch(url, {signal: AbortSignal.timeout(180000)});
		assert.ok(response.ok, `${name}: ${response.status}`);
		writeFileSync(path, new Uint8Array(await response.arrayBuffer()));
	}
	return {resource: 'literal', name, contents: new Uint8Array(readFileSync(path))};
}
const language = await archive('wordpress-7.1.3-fa_IR.zip', 'https://downloads.wordpress.org/translation/core/7.1.3/fa_IR.zip');
const commerceLanguage = await archive('woocommerce-11.2.1-fa_IR.zip', 'https://downloads.wordpress.org/translation/plugin/woocommerce/11.2.1/fa_IR.zip');
const commerce = await archive('woocommerce-11.2.1.zip', 'https://downloads.wordpress.org/plugin/woocommerce.11.2.1.zip');
const fixtures = ['seed', 'refresh', 'product-preview', 'layout-preview', 'variable-preview', 'quality-preview', 'storefront-preview', 'complete-catalog-preview', 'search-lifecycle'];
let site;
try {
	console.log(`Booting fresh WordPress/WooCommerce at ${sitePath} (port ${port})…`);
	site = await runCLI({
		command: 'start', path: sitePath, mode: 'create-new-site', port, wp: '7.1.3', php: '8.3', workers: 6, login: true, skipBrowser: true,
		'mount-before-install': [{hostPath: sitePath, vfsPath: '/wordpress'}],
		'define-bool': {WP_DEBUG: true, WP_DEBUG_DISPLAY: false, WP_DEBUG_LOG: true, DISABLE_WP_CRON: true, WPMAIN_ISOLATED_CI: true},
		mount: [
			{hostPath: resolve('themes/wp-main-theme'), vfsPath: '/wordpress/wp-content/themes/wp-main-theme'},
			{hostPath: resolve('plugins/wp-store-catalog'), vfsPath: '/wordpress/wp-content/plugins/wp-store-catalog'},
		],
		blueprint: {steps: [
			{step: 'unzip', zipFile: language, extractToPath: '/wordpress/wp-content/languages'},
			{step: 'unzip', zipFile: commerceLanguage, extractToPath: '/wordpress/wp-content/languages/plugins'},
			{step: 'setSiteOptions', options: {WPLANG: 'fa_IR'}},
			{step: 'mkdir', path: '/wordpress/wp-content/mu-plugins'},
			{step: 'writeFile', path: '/wordpress/wp-content/mu-plugins/wpmain-demo-mail.php', data: '<?php add_filter("pre_wp_mail", function($sent,$atts) { update_option("wpmain_qa_last_mail",$atts,false); return true; },10,2);'},
			{step: 'installPlugin', pluginData: commerce, options: {activate: true}},
			{step: 'activateTheme', themeFolderName: 'wp-main-theme'},
			...fixtures.map(name => ({step: 'runPHP', code: readFileSync(new URL(`../dev/${name}.php`, import.meta.url), 'utf8')})),
		]},
	});
	assert.equal(readFileSync(resolve(sitePath, 'wp-content/wpmain-lifecycle-passed.txt'), 'utf8'), 'PASS: guarded lifecycle assertions completed.', 'Booted lifecycle fixture must explicitly finish all assertions.');
	console.log('PASS: booted lifecycle fixture completed every guarded assertion.');
	if (!fixturesOnly) {
	await new Promise((done, reject) => site.server.close(error => error ? reject(error) : done()));
	await new Promise((done, reject) => { site.server.once('error', reject); site.server.listen(9400, '127.0.0.1', done); });
	const deadline = Date.now() + 120000;
	let ready = false;
	while (Date.now() < deadline) {
		try {
			const response = await fetch(origin, {headers: {Cookie: 'playground_auto_login_already_happened=1'}, signal: AbortSignal.timeout(15000)});
			if (response.ok && (await response.text()).includes('ابزار تتو / پیش‌نمایش')) { ready = true; break; }
		} catch {}
		await new Promise(done => setTimeout(done, 1000));
	}
	assert.ok(ready, 'Isolated demo did not become ready within 120 seconds.');
	// Each process completes before the next starts; six PHP workers are shared.
	for (const name of ['smoke-preview', 'test-product-details', 'test-layouts', 'test-cards', 'test-search', 'test-catalog', 'test-catalog-polish', 'test-archive-content', 'test-features', 'test-release', 'test-shopping', 'test-recovery', 'test-order', 'test-cart-feedback', 'test-cart-fragments', 'test-demo-content']) {
		console.log(`\nRunning ${name}…`);
		await new Promise((done, reject) => {
			const child = spawn(process.execPath, [`scripts/${name}.mjs`], {stdio: 'inherit', timeout: 240000});
			child.once('error', reject);
			child.once('exit', (code, signal) => code === 0 ? done() : reject(new Error(`${name} failed: ${code ?? signal}`)));
		});
	}
	console.log('PASS: isolated sequential WordPress/WooCommerce HTTP suite. Browser and real gateway release gates remain.');
	} else { console.log('PASS: fresh isolated booted WordPress/WooCommerce lifecycle fixtures.'); }
} finally {
	const debug = resolve(sitePath, 'wp-content/debug.log');
	if (existsSync(debug)) copyFileSync(debug, resolve(logs, 'wordpress-debug.log'));
	if (site) await site[Symbol.asyncDispose]();
	clearTimeout(watchdog);
}
