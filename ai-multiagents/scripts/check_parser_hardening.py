#!/usr/bin/env python3
"""Ad-hoc verification of extract_json_block hardening (2026-08-15).

Run:  python scripts/check_parser_hardening.py
"""
from __future__ import annotations

import sys
from pathlib import Path

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src.agent_adapter import extract_json_block

# 1. Real finding-5 shape: content contains PHP braces "%{$search}%" — the
#    naive brace-counting scanner used to break on these; must still find
#    the docs contract.
t1 = ('Done.\n'
      '------------------\n'
      '{"docs": [{"path": "docs/x.md", "title": "X", "summary": "s", '
      '"content": "the pattern \\"%{$search}%\\" with {braces} inside"}], '
      '"questions": []}\n'
      '------------------\n'
      'All verified.')
r1 = extract_json_block(t1)
assert r1 and r1['docs'][0]['content'] == 'the pattern \"%{$search}%\" with {braces} inside', r1
print('OK  braces-in-strings content extracted')

# 2. Truncated tail: missing closing ] and } (M7 case) — repaired in place
t2 = '{"docs": [{"path": "docs/x.md", "title": "X", "summary": "s", "content": "c"}], "questions": []'
r2 = extract_json_block(t2)
assert r2 and r2['docs'][0]['path'] == 'docs/x.md', r2
print('OK  truncated tail repaired')

# 3. Trailing comma — repaired
t3 = '{"docs": [{"path": "docs/x.md", "title": "X", "summary": "s", "content": "c"}], "questions": [],}'
r3 = extract_json_block(t3)
assert r3 and r3['questions'] == [], r3
print('OK  trailing comma repaired')

# 4. Fenced JSON still works
t4 = '```json\n{"docs": [{"path": "docs/x.md", "title": "X", "summary": "s", "content": "c"}], "questions": []}\n```'
r4 = extract_json_block(t4)
assert r4 and len(r4['docs']) == 1, r4
print('OK  fenced JSON still works')

# 5. Inline status object must not shadow the real contract
t5 = ('Running... {"status": "ok"} here is the real thing: '
      '{"docs": [{"path": "docs/x.md", "title": "X", "summary": "s", '
      '"content": "c"}], "questions": []}')
r5 = extract_json_block(t5)
assert r5 and 'docs' in r5, r5
print('OK  contract preferred over inline status')

# 6. Prose-only (brain wrote files itself) -> None, no false positive
t6 = 'I documented everything and wrote docs/foo.md myself. No JSON needed.'
assert extract_json_block(t6) is None
print('OK  prose-only returns None')

print('ALL PARSER CHECKS PASS')
