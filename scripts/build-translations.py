"""Build/check both Persian gettext bundles with Python's standard library.

JSON files are the editable source. --check validates gettext source coverage,
format placeholders and committed MO bytes without modifying either bundle.
"""
import argparse
import json
import re
import struct
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
BUNDLES = (
    (ROOT / 'themes/wp-main-theme', 'wp-main-theme', 'fa_IR'),
    (ROOT / 'plugins/wp-store-catalog', 'wp-store-catalog', 'wp-store-catalog-fa_IR'),
)
HEADER = 'Content-Type: text/plain; charset=UTF-8\nLanguage: fa_IR\nPlural-Forms: nplurals=2; plural=(n > 1);\n'


def compile_mo(messages):
    messages = dict(messages, **{'': HEADER})
    keys = sorted(messages)
    original = [key.encode() for key in keys]
    translated = [messages[key].encode() for key in keys]
    count = len(keys)
    offset = 28 + 16 * count
    tables = []
    for values in (original, translated):
        for value in values:
            tables.append((len(value), offset))
            offset += len(value) + 1
    data = struct.pack('<7I', 0x950412DE, 0, count, 28, 28 + 8 * count, 0, 0)
    data += b''.join(struct.pack('<2I', *row) for row in tables)
    return data + b''.join(value + b'\0' for value in original + translated)


def source_messages(folder, domain):
    # Project gettext calls use literal singular strings; plural calls must be
    # handled explicitly rather than silently dropping untranslated messages.
    pattern = re.compile(r"\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*'((?:\\.|[^'\\])*)'\s*,\s*'" + re.escape(domain) + r"'", re.S)
    found = set()
    for path in folder.rglob('*.php'):
        source = path.read_text(encoding='utf-8')
        for match in pattern.finditer(source):
            found.add(match[1].replace("\\'", "'").replace('\\\\', '\\'))
    return found


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--check', action='store_true')
    args = parser.parse_args()
    errors = []
    for folder, domain, basename in BUNDLES:
        target = folder / 'languages' / basename
        messages = json.loads(target.with_suffix('.json').read_text(encoding='utf-8'))
        missing = sorted(source_messages(folder, domain) - messages.keys())
        errors.extend(f'{domain}: missing translation: {key}' for key in missing)
        for key, value in messages.items():
            if not isinstance(value, str) or not value.strip():
                errors.append(f'{domain}: empty/invalid translation: {key}')
                continue
            # Match PHP printf placeholders including positional forms.
            tokens = lambda text: sorted(re.findall(r'%(?:\d+\$)?[-+0-9.]*[sdfoxXucbGeEFg]', text))
            if tokens(key) != tokens(value):
                errors.append(f'{domain}: mismatched placeholders: {key}')
        data = compile_mo(messages)
        if args.check:
            if not target.with_suffix('.mo').exists() or target.with_suffix('.mo').read_bytes() != data:
                errors.append(f'{domain}: MO bundle is stale; run python scripts/build-translations.py')
        elif not missing:
            target.with_suffix('.mo').write_bytes(data)
        print(f'{domain}: {len(messages)} translations {"checked" if args.check else "built"}.')
    if errors:
        raise SystemExit('\n'.join(errors))


if __name__ == '__main__':
    main()
