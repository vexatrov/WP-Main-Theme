import { runCLI } from '@wp-playground/cli';
import { readFileSync, existsSync, mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
const persistentPath = resolve('.local/site');
const resume = existsSync(resolve(persistentPath, 'wp-config.php'));
mkdirSync(persistentPath, { recursive: true });

// Local disposable WordPress, isolated from production and from real customer data.
const seed = readFileSync(new URL('../dev/seed.php', import.meta.url), 'utf8');
const refresh = readFileSync(new URL('../dev/refresh.php', import.meta.url), 'utf8');
const productPreview = readFileSync(new URL('../dev/product-preview.php', import.meta.url), 'utf8');
const layoutPreview = readFileSync(new URL('../dev/layout-preview.php', import.meta.url), 'utf8');
const variablePreview = readFileSync(new URL('../dev/variable-preview.php', import.meta.url), 'utf8');
const qualityPreview = readFileSync(new URL('../dev/quality-preview.php', import.meta.url), 'utf8');
const storefrontPreview = readFileSync(new URL('../dev/storefront-preview.php', import.meta.url), 'utf8');
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
	command: 'start',
	path: persistentPath,
	// Empty directories are not recognized as WordPress by CLI auto-detection.
	...(resume ? {} : { 'mount-before-install': [{ hostPath: persistentPath, vfsPath: '/wordpress' }] }),
	mode: resume ? 'apply-to-existing-site' : 'create-new-site',
	port: 9400,
	wp: '7.1.3',
	php: '8.3',
	workers: 6,
	login: true,
	skipBrowser: true,
	'define-bool': { WP_DEBUG: true, WP_DEBUG_DISPLAY: false, WP_DEBUG_LOG: true, DISABLE_WP_CRON: true },
	mount: [
		{ hostPath: resolve('themes/wp-main-theme'), vfsPath: '/wordpress/wp-content/themes/wp-main-theme' },
		{ hostPath: resolve('plugins/wp-store-catalog'), vfsPath: '/wordpress/wp-content/plugins/wp-store-catalog' },
	],
	blueprint: {
		steps: resume ? [] : [
			{ step: 'unzip', zipFile: coreLanguage, extractToPath: '/wordpress/wp-content/languages' },
			{ step: 'unzip', zipFile: commerceLanguage, extractToPath: '/wordpress/wp-content/languages/plugins' },
			{ step: 'setSiteOptions', options: { WPLANG: 'fa_IR' } },
			{ step: 'installPlugin', pluginData: commerce, options: { activate: true } },
			{ step: 'activateTheme', themeFolderName: 'wp-main-theme' },
			{ step: 'runPHP', code: seed },
			{ step: 'runPHP', code: refresh },
			{ step: 'runPHP', code: productPreview },
			{ step: 'runPHP', code: layoutPreview },
			{ step: 'runPHP', code: variablePreview },
			{ step: 'runPHP', code: qualityPreview },
			{ step: 'runPHP', code: storefrontPreview },
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
