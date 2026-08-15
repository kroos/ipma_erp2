# 07 - Cross-Platform Portability

## 1. Goal

`ai-multiagents` must work in **any Laravel project root** on **Windows and Linux**
with minimal setup.

## 2. Constraints

- **Single runtime dependency:** Python 3 (standard library + `pyyaml` only).
- No compiled native modules, no OS-specific daemons, no fixed absolute paths.
- The system locates the Laravel root as the **parent directory of
  `ai-multiagents/`** and never hard-codes a path.

## 3. Design Rules

| Area | Rule |
|------|------|
| Paths | Use `pathlib.Path` everywhere; no string-concatenated paths. |
| OS detection | `sys.platform` (`win32` vs `linux`) — configures subprocess + line endings only; core logic is identical. |
| Subprocess | Invoke agents as **command lists** (`[binary, arg, ...]`), never a hand-built shell string; avoid `shell=True`. |
| Agent binary names | Resolve via `shutil.which()`; `opencode.exe` on Windows vs `opencode` on Linux handled automatically. |
| Newlines | Use `os.linesep` / `Path.write_text(..., newline="")` consistently for generated files. |
| Virtualenv | `python -m venv` works identically on both OSes; venv is gitignored. |
| Bootstrap | `setup.ps1` (Windows, PowerShell) + `setup.sh` (Linux/macOS) are thin wrappers around `setup.py`. |
| Encoding | Force `utf-8` on all file I/O (Windows console default is not UTF-8). |
| Signals | KeyboardInterrupt handling for Ctrl+C works on both platforms; no `signal`-specific code paths except where Python requires. |

## 4. Laravel Root Resolution

```
laravel-root/
├── ai-multiagents/          # SYSTEM_DIR = this folder
├── app/                     # PROJECT_ROOT = SYSTEM_DIR.parent
├── artisan
└── ...
```

Python:

```python
SYSTEM_DIR = Path(__file__).resolve().parent.parent   # ai-multiagents/
PROJECT_ROOT = SYSTEM_DIR.parent                       # Laravel root
```

Guard: confirm `PROJECT_ROOT / "artisan"` exists (and `composer.json`) before
working; if missing, warn that this is not a Laravel root.

## 5. OS Abstraction in Code

- One module `src/platform.py` (folded into `config_loader`/`orchestrator` or kept
  separate) exposes:
  - `is_windows()`, `is_linux()`
  - `find_binary(name)` → first match of `name` / `name.exe` on `PATH`
  - `spawn(cmd_list, cwd, env, ...)` → cross-OS subprocess runner (captures stdout/stderr to per-task output files, enforces per-agent timeout, argv→stdin prompt fallback)
  - `safe_path(*parts)` → `Path` join with correct separators
  - `utf8_open(path, mode)` → forced UTF-8 file open

## 6. Bootstrap Scripts (behavior)

`scripts/setup.py` (shared logic):

1. Print OS + Python version.
2. Create venv, install `requirements.txt`.
3. Detect every agent binary from `config/agents.yaml` via `find_binary`; print an
   availability table.
4. Verify a Laravel root (artisan present); report the resolved root.
5. Exit non-zero with a clear message if Python is missing or the venv install fails.

`setup.ps1`:

```powershell
# thin wrapper
if (-not (Get-Command python -ErrorAction SilentlyContinue)) { throw "Python 3 required" }
python scripts/setup.py
```

`setup.sh`:

```bash
#!/usr/bin/env bash
command -v python3 >/dev/null || { echo "Python 3 required"; exit 1; }
python3 scripts/setup.py
```

## 7. Testing Matrix (E2E)

| OS | Python | Laravel | Expectation |
|----|--------|---------|-------------|
| Windows 10/11 | 3.10+ | 11/12/13 | setup, summon, task run, memory persist |

> **Scope (2026-08-14):** the Linux rows of this matrix were removed from
> the project's Definition of Done — M7 is verified on **Windows only**.
> The portability design rules in §3 (OS abstraction, pathlib, UTF-8,
> cross-OS subprocess) still apply to the code; Linux verification can be
> re-added via a CI runner if one becomes available.

One-shot mode (`summon.py --instruction "..."`) is essential for CI/headless
environments where no interactive terminal exists.

## 8. Windows E2E Findings (M7, 2026-08-12)

Verified end-to-end on Windows against a **fresh Laravel 13.8 app**
(`E:\laravel13`, SQLite) with real agent turns:

| Area | Finding | Fix landed |
|------|---------|-----------|
| Windows shims | `shutil.which()` resolves npm CLIs to `.CMD` shims; `subprocess` cannot start a bare `.cmd` name. | `_build_command` resolves the binary and wraps `.cmd`/`.bat` in `cmd /c` (`agent_adapter.py`). |
| cmd.exe quoting | Newlines/CR truncate an argv prompt at the first line break and `%VAR%` is env-expanded by `cmd /c`; embedded `"` **does** survive the shim re-parse (see below). | `_shim_requires_stdin()`: only `.cmd`/`.bat` shims + newline/CR/`%` prompts go via stdin; real executables keep argv (`agent_adapter.py`). |
| Quote round-trip | **M7-esc finding (2026-08-12):** Python's `list2cmdline` already escapes embedded `"` so it round-trips **exactly** through `cmd /c` → npm-style `.CMD` shim → node (verified with an argv echo shaped like the real npm gemini shim). Manual pre-escaping (`\"`) was tried first and double-escaped — **removed**. Quote-only prompts stay in argv, which is required for CLIs like gemini whose `-p` flag reads its value from argv only. | `_escape_cmd_arg`/`argv-escaped` path deleted; `"` no longer in the stdin-required char set; regression covered by `scripts/smoke_argv_routing.py` (part C: real `.CMD` shim round-trip). |
| gemini stdin | `gemini -p` requires a value in argv (bare `-p` → "Not enough arguments following: p"); stdin content is appended to the `-p` value. | `stdin_argv: ["gemini", "-p", ""]` — keep an **empty** value and pipe the real prompt via stdin (verified: full prompt echoed back, RC 0). Only newline/CR/`%`/long prompts take this path; quote-only prompts stay in argv. |
| Reassignment | A failing task ping-ponged between two failing agents forever — `_next_owner` excluded only the last owner. | `failed_owners` set in task metadata; reassignment walks forward over every previously-failed agent (`dispatcher.py`). |
| Doc Writer | Docs were reported (`docs/api/...`) but **never written to disk** — only stored in memory. | `_write_doc()` writes under `<laravel-root>/docs`, path-escape guarded, forward-slash paths (`doc_writer.py`). |
| Live memory | Memory persists across processes: `memory/doc_writer.db` accumulates entries from each run (both `docs/api-endpoints.md` and the regenerated `docs/api/health-endpoint.md` seen after run 2). | — (already correct) |

Real CLI status on this machine (2026-08-12): only **opencode** has working
credentials — claudeCode + openclaw are out of credits, gemini hit its daily
free-tier quota. The pipeline correctly failed, reassigned, and finally ran
with the seeded last-session role choices in `config/state.json` (the
persistence feature, `roles.<role_id>`). On a machine where only one agent
works, seed state.json or pin roles via `summon.py --tester/--bug-fixer/
--doc-writer`.

**Team de-scoped 2026-08-14:** a live credential check confirmed
**openclaude also fails** (`402 API quota exhausted`), so openclaude,
openclaw, claudeCode, gemini and hermes were **removed** from
`config/agents.yaml` and `config/roles.yaml`. The team is now **opencode**
(only wired headless agent) + **freebuff** (interactive-only fallback).
Wiring is config-driven, so this is a config change only — no code paths
were removed.

Full M7 Windows pass: `summon.py --coordinator opencode --tester opencode
--bug-fixer opencode --doc-writer opencode --instruction "Add a GET /health
route ... run the test suite"` → 3 tasks done → bug loop (2 suites, 3
passed, 0 defects) → 2 docs written → memory persisted.

### 8.1 Re-verification (2026-08-14, `E:\laravel-m7-e2e`)

M7 re-verified end to end on this machine (fresh Laravel 13 app, SQLite) —
full pipeline green: 3 tasks done on **opencode** → bug loop (2 suites, 3
passed, 0 defects) → 2 docs written → `php artisan test` green → memory
persisted across runs (doc_writer.db 67→69, tester.db 466→467). Two more
real bugs surfaced and fixed:

| Area | Finding | Fix landed |
|------|---------|-----------|
| Failed dependency | When the impl task failed permanently (all its capable agents broken), its dependents stayed `pending` forever with no error and the coordinator loop silently spun through its 50-round cap — the run ended with tasks that never ran and no explanation. | `dispatcher.dispatch()` cascades: a task whose dependency is `failed`/`canceled` is marked `failed` with `"blocked by failed dependency <id>"` (`dispatcher.py`); regression in `scripts/smoke_coordinator.py`. |
| JSON extraction | Real LLM output wrapped the Tester's contract report in a ` ```json ` code fence instead of the documented `---` delimiters; the fallback grabbed the **first** balanced `{...}` in prose (`{"status":"ok"}`) and the bug loop aborted with "report JSON missing 'report' object". | `extract_json_block()` accepts ` ```json ` fences **and** scans every balanced object, preferring one with a contract key (`report`/`docs`/`fix`/`tasks`/`clarifying_questions`), else the last (`agent_adapter.py`); regression in `scripts/smoke_bugloop.py`. |
| One-agent machines | The brain routed the impl task to `backend_specialist`, whose first wired default is openclaw (broken on this machine) → the whole plan failed before opencode (the one working agent) was ever used. | Seed **every** role in `config/state.json` (`roles.<role_id>: opencode`) — plan-owner roles resolve via state.json since no flags exist to pin them. Verified: re-run with all 20 roles seeded completed greenly. |
| Capability vocabulary | The brain emitted an out-of-vocabulary capability (`execution-plans`) — tolerated (task runs, doc writer documents it), but only `TEST_RUN_CAPS` trigger the bug loop, so a mislabeled test task can skip it. | `Coordinator._validate_plan` now **rejects** capabilities outside the vocabulary with `PlanError` (fails loud with the valid list) (`coordinator.py`); regression in `scripts/smoke_coordinator.py`. |
| Zombie process | After a run an `opencode.exe` process lingered post-shutdown (opencode's own daemon behavior; the adapter's `subprocess.run` only waits on the direct child). | Observation; kill leftover `opencode.exe` between runs. |
| Malformed brain JSON | A de-scoped-team run (2026-08-14, `E:\laravel-m7-e2e2`) failed at the Doc Writer: opencode emitted `{"docs": [...], "questions": []}` with the docs array's closing `]` missing — `extract_json_block` correctly returned nothing and the stage aborted. | All four brain calls (plan/report/fix-note/docs) now **retry once** with a `JSON_RETRY_HINT` on unparseable JSON (`agent_adapter.py`); regression in `scripts/smoke_docwriter.py`. Full de-scoped E2E re-run (`E:\laravel-m7-e2e3`) completed green: tasks → bug loop → docs written. |
