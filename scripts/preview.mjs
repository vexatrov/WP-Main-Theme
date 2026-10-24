import { runCLI } from '@wp-playground/cli';
import { readFileSync, existsSync, mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

// Local disposable WordPress, isolated from production and from real customer data.
const seed = readFileSync(new URL('../dev/seed.php', import.meta.url), 'utf8');
const refresh = readFileSync(new URL('../dev/refresh.php', import.meta.url), 'utf8');
mkdirSync('.local/cache', { recursive: true });
async function archive(name, url) {
	const path = resolve('.local/cache', name);
	if (!existsSync(path)) {
		console.log(`Downloading ${name}…`);
		const response = await fetch(url, { signal: AbortSignal.timeout(180000) });
		if (!response.ok) throw new Error(`${name}: HTTP ${response.status}`);
		writeFileSync(path, new Uint8Array(await response.arrayBuffer()));
	}
	return { resource: 'literal', name, contents: new Uint8Array(readFileSync(path)) };
}
const coreLanguage = await archive('wordpress-7.1.3-fa_IR.zip', 'https://downloads.wordpress.org/translation/core/7.1.3/fa_IR.zip');
const commerceLanguage = await archive('woocommerce-11.2.1-fa_IR.zip', 'https://downloads.wordpress.org/translation/plugin/woocommerce/11.2.1/fa_IR.zip');
const commerce = await archive('woocommerce-11.2.1.zip', 'https://downloads.wordpress.org/plugin/woocommerce.11.2.1.zip');
const site = await runCLI({
	command: 'server',
	port: 9400,
	wp: '7.1.3',
	php: '8.3',
	workers: 6,
	login: true,
	'define-bool': { WP_DEBUG: true, WP_DEBUG_DISPLAY: false, WP_DEBUG_LOG: true },
	mount: [{ hostPath: resolve('themes/wp-main-theme'), vfsPath: '/wordpress/wp-content/themes/wp-main-theme' }],
	blueprint: {
		steps: [
			{ step: 'unzip', zipFile: coreLanguage, extractToPath: '/wordpress/wp-content/languages' },
			{ step: 'unzip', zipFile: commerceLanguage, extractToPath: '/wordpress/wp-content/languages/plugins' },
			{ step: 'setSiteOptions', options: { WPLANG: 'fa_IR' } },
			{ step: 'installPlugin', pluginData: commerce, options: { activate: true } },
			{ step: 'activateTheme', themeFolderName: 'wp-main-theme' },
			{ step: 'runPHP', code: seed },
			{ step: 'runPHP', code: refresh },
		],
	},
});
// Playground's server initially uses Node's default interface; keep the ready
// preview loopback-only so its local auto-login is not available on the LAN.
await new Promise((resolve, reject) => site.server.close((error) => error ? reject(error) : resolve()));
await new Promise((resolve, reject) => {
	site.server.once('error', reject);
	site.server.listen(9400, '127.0.0.1', resolve);
});
console.log(`\nTheme preview: ${site.serverUrl}\nAdmin: ${site.serverUrl}/wp-admin/\nDisposable sample store. Ctrl+C stops the preview. Theme files are live-mounted.\n`);
for (const signal of ['SIGINT', 'SIGTERM']) {
	process.on(signal, async () => {
		await site[Symbol.asyncDispose]();
		process.exit(0);
	});
}
