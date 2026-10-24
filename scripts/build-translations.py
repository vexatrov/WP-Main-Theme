"""Build the small theme-only Persian gettext catalog. Python standard library only."""
import json
import struct
from pathlib import Path

root = Path(__file__).resolve().parent.parent
folder = root / 'themes' / 'wp-main-theme' / 'languages'
messages = json.loads((folder / 'fa_IR.json').read_text(encoding='utf-8'))
messages[''] = 'Content-Type: text/plain; charset=UTF-8\nLanguage: fa_IR\nPlural-Forms: nplurals=2; plural=(n > 1);\n'
keys = sorted(messages)
original = [key.encode() for key in keys]
translated = [messages[key].encode() for key in keys]
count = len(keys)
offset = 28 + 16 * count
original_table, translated_table = [], []
for value in original:
    original_table.append((len(value), offset))
    offset += len(value) + 1
for value in translated:
    translated_table.append((len(value), offset))
    offset += len(value) + 1
data = struct.pack('<7I', 0x950412DE, 0, count, 28, 28 + 8 * count, 0, 0)
data += b''.join(struct.pack('<2I', *row) for row in original_table + translated_table)
data += b''.join(value + b'\0' for value in original + translated)
(folder / 'fa_IR.mo').write_bytes(data)
print(f'Built {count - 1} Persian theme translations.')
