// Offline PHP contract checks using the official runtime already installed for preview.
import {createRequire} from 'node:module';
import {dirname, resolve} from 'node:path';
import {pathToFileURL} from 'node:url';
import {readdirSync} from 'node:fs';
const require = createRequire(import.meta.url);
const base = dirname(require.resolve('@wp-playground/cli'));
const {loadNodeRuntime, createNodeFsMountHandler} = await import(pathToFileURL(require.resolve('@php-wasm/node', {paths: [base]})).href);
const {PHP} = await import(pathToFileURL(require.resolve('@php-wasm/universal', {paths: [base]})).href);
const php = new PHP(await loadNodeRuntime('8.3', {emscriptenOptions: {processId: 1}}));
php.mkdir('/workspace');
await php.mount('/workspace', createNodeFsMountHandler(resolve('.')));
try {
	const files = directory => readdirSync(directory, {withFileTypes:true}).flatMap(entry => entry.isDirectory() ? files(directory+'/'+entry.name) : entry.name.endsWith('.php') ? [directory+'/'+entry.name] : []);
	for (const file of ['themes','plugins','dev','tests'].flatMap(files)) {
		const response = await php.run({code: '<?php token_get_all(file_get_contents('+JSON.stringify('/workspace/'+file)+'), TOKEN_PARSE);'});
		if (response.exitCode || response.errors) throw new Error(`PHP syntax ${file}: ${response.errors}`);
	}
	console.log('PASS: PHP syntax for all repository themes, plugins, fixtures and contracts.');
	for (const file of ['tests/catalog.php', 'tests/search.php', 'tests/editorial.php', 'tests/catalog-filters.php', 'tests/merchandising.php', 'tests/seo.php']) {
		const response = await php.run({scriptPath: '/workspace/' + file});
		console.log(response.text);
		if (response.exitCode || response.errors || /Fatal error|Warning:|Notice:/.test(response.text)) throw new Error(`${file}: ${response.errors}`);
	}
} finally { php.exit(); }
