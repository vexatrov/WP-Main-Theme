import {readFileSync,readdirSync} from 'node:fs';
import {gzipSync} from 'node:zlib';
const root = 'themes/wp-main-theme';
for (const [folder,extension,budget] of [['assets/css','.css',30*1024],['assets/js','.js',8*1024]]) {
	const files = readdirSync(`${root}/${folder}`).filter(file => file.endsWith(extension));
	const bytes = files.reduce((sum,file) => sum + gzipSync(readFileSync(`${root}/${folder}/${file}`)).length,0);
	console.log(`${folder}: ${(bytes/1024).toFixed(2)} KB gzip across ${files.length} files; budget ${budget/1024} KB`);
	if (bytes > budget) throw new Error(`${folder} exceeds the custom-theme budget`);
}
console.log('Excludes WordPress, WooCommerce, brand assets, fonts, media and other plugins; this is not a Core Web Vitals measurement.');
