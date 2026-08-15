# 06 - Implementation Plan

This is the build plan. It is phased so the system can be incrementally verified.

## Phase 0 — Scaffolding

1. Create the `ai-multiagents/` tree (folder layout per `docs/05`).
2. Create `requirements.txt` (PyYAML; stdlib for the rest — sqlite3, subprocess, json, pathlib).
3. Create `.editorconfig` and `.gitignore`.
4. Create `scripts/setup.py` + `setup.sh` + `setup.ps1`:
   - Detect OS (Windows/Linux) and Python 3 availability.
   - Create a virtualenv and install requirements.
   - Detect each AI agent binary (`opencode`, `freebuff` — team de-scoped
     2026-08-14) on `PATH` and report which are available.
   - **Exit cleanly if Python 3 is missing.**

## Phase 1 — Config & State

1. `config/agents.yaml` — one entry per AI agent (id + binary/CLI name).
2. `config/roles.yaml` — full role tree (Coordinator, 3 tier-2 leads, all specialists, App Designer, Tester, Bug Fixer, Doc Writer) with `default_agent` per the mapping in `docs/03`.
3. `src/config_loader.py`:
   - Loads YAML configs.
   - Resolves **Laravel root** = parent of `ai-multiagents/` (no hard-coded paths).
4. `src/state.py`:
   - Reads/writes `config/state.json` (last-session model per agent).
   - Implements the model resolution order from `docs/03 §4`.

## Phase 2 — Memory

1. `src/memory.py` — SQLite-backed per-agent persistent memory.
   - One DB per agent (`memory/<agent_id>.db`).
   - Tables: `entries` (id, timestamp, kind, content), `decisions` (id, date, decision, rationale), `context` (key, value).
   - API: `remember(agent, kind, content)`, `recall(agent, query)`, `save_decision(agent, decision, rationale)`.
   - Verifiable: two sessions, write in one, recall in the other.

## Phase 3 — A2A Message Bus (Omniroute)

1. `src/message_bus.py` — the Omniroute gateway:
   - Full A2A data model (per `docs/04`): Agent Card, Message (parts: text/file/data), Task, Artifact, lifecycle states `submitted → working → input-required → completed | failed | canceled`.
   - `OmnirouteTransport` interface + `LocalBusTransport` (default): in-process queues + per-agent persisted outbox in SQLite (at-least-once, dedupe by `messageId`). `HttpJsonRpcTransport` is a documented future swap-in, not built yet.
   - Smart routing: named delivery, capability addressing (`toCapability`), and broadcast; resolves against the Agent Card registry.
   - **Central no-double-dispatch enforcement**: reject `task` to an agent with status `working`; return a clear `routing` error when no idle capable agent exists.
2. `src/agent_adapter.py`:
   - Wraps a CLI binary as an A2A-speaking agent, driven entirely by `config/agents.yaml` templates (never hard-coded commands).
   - **One-shot process per task**: renders the template (`{prompt}`, optional `{model}`), spawns a fresh subprocess from the Laravel root, captures stdout/stderr to per-task output files, enforces `timeout_sec` (kills runaway processes on both OSes).
   - **Prompt delivery**: config-driven `prompt_mode` — argv injection first; on spawn failure, retry via stdin (fallback). Long prompts that exceed argv limits switch to stdin automatically.
   - Emits `status` messages on lifecycle changes; produces a `result` envelope with Artifacts.
   - Registers its Agent Card at summon; statuses reported to Omniroute.

## Phase 4 — Orchestrator & Dispatcher

1. `src/orchestrator.py`:
   - Boot sequence: load config/state → spawn all agents → wire mesh → register cards → hand control to Coordinator.
   - Shutdown sequence: persist state + memory → close queues → terminate subprocesses.
   - Signal handling (Ctrl+C) for clean shutdown on both OSes.
2. `src/dispatcher.py`:
   - Global task list; task lifecycle `pending → in_progress → done | failed`.
   - Subtask decomposition support.
   - **No-double-dispatch rule**: an agent whose status is `working` is never handed a new task.
   - Dependency tracking; parallel and sequential coordination.
   - Reassign on failure; every task has an owner.

## Phase 5 — Coordinator Role (hybrid)

> **DONE (M4).** `src/coordinator.py` (control loop + one-shot brain),
> `src/coordinator.py::select_coordinator` (selection order), `scripts/summon.py`
> (CLI), `scripts/fake_agent.py` (`--plan`/`--clarify` test modes),
> `scripts/smoke_coordinator.py` (green). Phase 6 §1 `src/test_detector.py` also done.

The Coordinator is a **hybrid**: a Python control loop + the user-chosen AI agent
as the "brain" for judgment calls. (Decision recorded in `docs/03`.)

1. **Coordinator loop** (`src/coordinator.py`, pure Python):
   - User gateway: interactive REPL, or one-shot `--instruction "..."`.
   - Owns the global task list, dispatch via Omniroute, status monitoring, result collection.
   - Calls the chosen agent **one-shot** only for judgment calls: instruction decomposition, conflict resolution, deciding clarifying questions.
   - Reports summarized results to the user (never raw internal chatter).
2. **Task plan schema** — the only contract between brain and loop. The agent returns structured JSON:
   ```json
   {
     "clarifying_questions": [],
     "tasks": [
       { "title": "...", "capability": "migrations",
         "dependsOn": ["task_..."], "owner": "laravel_specialist" }
     ]
   }
   ```
   - If `clarifying_questions` is non-empty, the loop asks the user first and re-invokes with answers.
   - The loop validates the plan, creates tasks, and dispatches; the agent never issues raw dispatch commands.
3. **Selection UX** (from `docs/03`):
   - `--coordinator <agent>` flag (one-shot) → prompt (interactive) → `state.json` last session → fallback **freebuff**.
4. **Tier-2 leads** (Backend/Frontend/DB) use the same one-shot pattern: their lead agent decomposes a scoped sub-instruction into a subtask plan for their specialists, reviews returned results, then reports up.
5. **Summon semantics (spec rule 5):** all agents are summoned at boot by the orchestrator and stay wired. "Auto-summon on instruction" = the Coordinator re-activates an agent only if one was unavailable (e.g. crashed); otherwise it dispatches to the already-wired mesh.

## Phase 6 — Bug Loop & Docs

> **DONE (M5 + M6).** `src/tester.py` (hybrid Tester: report contract,
> regression-gate `verify_fix`), `src/bug_fixer.py` (hybrid Bug Fixer:
> fix-note contract, `fix()` loop with regression gate), `src/doc_writer.py`
> (hybrid Doc Writer: docs contract, clarify-before-inventing),
> `scripts/smoke_bugloop.py` (green: finds → fixes → verifies, 2-round
> regression loop), `scripts/smoke_docwriter.py` (green: produces docs,
> clarifies first). Fake-agent modes `--tester`/`--fix`/`--docs`/`--docs-ask`
> added for deterministic testing.

1. **Framework detection** (`src/test_detector.py`): auto-detect from project files at summon time — `phpunit.xml`/Pest (`php artisan test`) and `package.json` scripts (`npm test` → vitest/jest/playwright). Detected commands are injected into the Tester prompt.
2. **Tester agent** (decision: execution delegated to the CLI agent):
   - Runs test cases itself inside its environment (test plans, unit/feature/API/DB/auth/validation/edge-case/regression, responsive/browser checks).
   - **Contract:** must still emit a **structured test report** (delimited JSON block in its output) that the adapter extracts and the loop parses — defect list per the schema in `docs/02`:
     ```json
     {"report": {"suitesRun": 2, "passed": 18, "failed": 1, "defects": [ { ... } ]}}
     ```
   - **Never fixes bugs** — it documents defects only.
   - Timeout enforced by the outer subprocess; free-text is acceptable in the report but the JSON block is required.
   - Implemented: `src/tester.py` (`run_tests`, `verify_fix`), memory-backed defect log.
3. **Bug Fixer agent:**
   - Reproduces, routes root cause to the right owner (Backend/Frontend/DB/Architecture), implements fix, requests Tester verification.
   - On fix submit, **regression gate auto-triggers**: Tester re-runs the affected suite, then the full suite, before marking the defect `verified`.
   - Regression loop until Tester passes.
   - Implemented: `src/bug_fixer.py` (`fix` loop, `COMPONENT_OWNERS` routing, `MAX_FIX_ROUNDS=3`).
   - **Wired into the Coordinator:** after a plan completes, any task whose
     `capability` is a Tester capability (config/roles.yaml) auto-triggers
     `Coordinator._run_bug_loop_if_needed` — Tester runs the full suite,
     each defect goes through `BugFixer.fix()`, and the summary is reported
     to the user (Coordinator is the only user gateway). Role→agent pinning
     via `Coordinator(role_brains={...})` or, in REPL mode, an interactive
     prompt (see below).
     Verified by `scripts/smoke_coordinator_bugloop.py` (Scenario A triggers
     the loop + 2-round fix; Scenario B with no testing tasks skips it).
   - **Interactive role selection (REPL):** when a pinned role (tester /
     bug_fixer / doc_writer) is first needed and not pinned by flag, the
     Coordinator asks the user which wired agent should play it
     (`Coordinator(ask_roles=True)`; `--tester` / `--bug-fixer` /
     `--doc-writer` flags pin one-shot runs). Selection order mirrors
     `select_coordinator`: explicit pin → interactive prompt → state.json
     last session → role defaults. Choices persist in state.json.
     Verified by `scripts/smoke_role_select.py`.
4. **Doc Writer agent:**
   - Synchronizes docs with implementation; asks specialists rather than inventing behavior.
   - Implemented: `src/doc_writer.py` (`document`, clarify loop before producing docs).
   - **Wired into the Coordinator:** after a plan completes (and the bug
     loop runs), every **done implementation task** (capability not in
     `TEST_RUN_CAPS`) is auto-documented via
     `Coordinator._run_docwriter_if_needed`; the bug-loop verdict is folded
     into the doc scope, and generated doc paths are reported to the user.
     The Doc Writer brain resolves like the other pinned roles (flag →
     prompt → state.json → defaults).
     Verified by `scripts/smoke_coordinator_docwriter.py` (Scenario A
     generates docs for migration tasks; Scenario B with only a test-run
     task skips the doc writer).

## Phase 7 — Portability & Packaging (see docs/07)

1. Path handling: all relative to Laravel root; `pathlib` everywhere.
2. OS abstraction: subprocess launch uses a cross-OS runner (`shell=True` avoided; command lists + `os.devnull` etc.); line endings handled.
3. E2E verification matrix (Windows only since 2026-08-14 — the Linux leg
   was removed from scope; portability design rules in docs/07 still apply):
   - Fresh Laravel app on **Windows** (`E:\laravel13` 2026-08-12,
     `E:\laravel-m7-e2e` 2026-08-14).
   - `summon.py --instruction "..."` produces a change; Tester verifies; Doc Writer documents; memory persists to a second run.

## Milestone Gate Criteria

| Milestone | Definition of Done |
|-----------|--------------------|
| M0 | Folder tree + setup scripts run on both OSes; agent binaries detected. |
| M1 | Config loads; state resolves last-session models; Laravel root resolved. |
| M2 | Memory write/recall works across two sessions. |
| M3 | A2A envelopes route between two agents; dedupe works. |
| M4 | Coordinator dispatches a real task; no-double-dispatch enforced. |
| M5 | Bug loop: Tester finds → Bug Fixer fixes → Tester verifies PASS. | **DONE** — smoke_bugloop + smoke_coordinator_bugloop green |
| M6 | Doc Writer documents; docs stay in sync. | **DONE** — smoke_docwriter + smoke_coordinator_docwriter green |
| M7 | Full E2E against a fresh Laravel app. | **DONE** (2026-08-12 `E:\laravel13`; re-verified 2026-08-14 `E:\laravel-m7-e2e`): summon → change → bug loop → docs written → memory persisted, all green. Real bugs fixed along the way (4 on 2026-08-12; dependency-cascade + ```json fence parsing on 2026-08-14 — docs/07 §8). Linux E2E leg removed from scope 2026-08-14. |

## Open Questions to Resolve During Build

1. **Coordinator default agent** — spec says "chosen by user". One-shot CLI mode needs a prompt or flag for this.
2. ~~How much "A2A" is real protocol vs. internal envelope?~~ **Resolved:** full A2A data model on a local bus; `HttpJsonRpcTransport` is the documented future swap-in (ADR-2).
3. ~~Agent CLI invocation shapes~~ **Resolved:** config-driven templates in
   `agents.yaml`. Historical (2026-08-12): templates were verified against
   each real CLI's `--help` (`opencode run`, `openclaude -p`, `claude -p`,
   `gemini -p`, `openclaw agent --local --message` → `verified: true`).
   **De-scoped 2026-08-14:** openclaude (credential test → `402 API quota
   exhausted`), openclaw (no provider keys), claudeCode + gemini (fail) and
   hermes (never installed) were removed from `agents.yaml`; the team is now
   **opencode** (verified + working credentials) and **freebuff**
   (interactive-only TUI, `headless: false`, excluded from wiring — never
   pin it as a one-shot brain).
4. **Tester tooling** — does the Tester shell out to `php artisan test` / `npm test`, or inspect via the CLI agent? Default: the CLI agent runs project test commands.
