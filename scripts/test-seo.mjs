// Focused offline SEO behavior checks using the existing Playground PHP runtime.
import { createRequire } from 'node:module';
import { dirname, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
const require = createRequire(import.meta.url);
const base = dirname(require.resolve('@wp-playground/cli'));
const { loadNodeRuntime, createNodeFsMountHandler } = await import(pathToFileURL(require.resolve('@php-wasm/node', { paths: [base] })).href);
const { PHP } = await import(pathToFileURL(require.resolve('@php-wasm/universal', { paths: [base] })).href);
const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
php.mkdir('/workspace');
await php.mount('/workspace', createNodeFsMountHandler(resolve('.')));
try {
	const result = await php.run({ scriptPath: '/workspace/tests/seo.php' });
	console.log(result.text.trim());
	if (result.exitCode || result.errors || /Fatal error|Warning:|Notice:/.test(result.text)) throw new Error(result.errors || result.text);
	for (const owner of ['WPSEO_VERSION', 'AIOSEO_VERSION', 'RANK_MATH_VERSION', 'SEOPRESS_VERSION', 'THE_SEO_FRAMEWORK_VERSION']) {
		const result = await php.run({ code: `<?php define('ABSPATH', '/workspace/'); define('${owner}', 'test'); function add_action(...$args) {} function add_filter(...$args) {} function apply_filters($name, $value) { return $value; } require '/workspace/plugins/wp-store-catalog/seo.php'; if (wpmain_catalog_seo_enabled()) throw new RuntimeException('SEO owner not detected');` });
		if (result.exitCode || result.errors || /Fatal error|Warning:|Notice:/.test(result.text)) throw new Error(`${owner}: ${result.errors || result.text}`);
	}
	console.log('PASS: Yoast, AIOSEO, Rank Math, SEOPress and The SEO Framework detection.');
} finally { php.exit(); }
