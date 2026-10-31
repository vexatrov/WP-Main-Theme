// Enforce the owner's preferred spelling in shared UI, translations and documentation.
import { readdirSync, readFileSync } from 'node:fs';
const incorrect = '\u062a\u0627\u062a\u0648';
const failures = [];
function scan(folder) {
	for (const entry of readdirSync(folder, { withFileTypes: true })) {
		const path = folder + '/' + entry.name;
		if (entry.isDirectory()) scan(path);
		else if (/\.(php|json|md|js|css)$/.test(path) && path !== 'dev/spelling-preview.php') {
			const text = readFileSync(path, 'utf8');
			if (text.includes(incorrect)) failures.push(path);
		}
	}
}
['themes', 'plugins', 'dev', 'docs'].forEach(scan);
if (failures.length) throw new Error('Use تتو in authored copy: ' + failures.join(', '));
console.log('PASS: mandatory Persian terminology in UI, fixtures, translations and docs (legacy migration identifiers excepted).');
