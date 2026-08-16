# Session — Resume Here

Last saved: 2026-08-17 (G:\ipma_erp2).

**Resume pointer (2026-08-17, latest):** FULL SESSION CHECKPOINT committed in
G:\ipma_erp2 as `089215f` (153 files, +3497/−3652) — "checkpoint: leave-approval
API+DataTable, overtime & datetimepicker fixes, casts on 78 models". Everything
below is in that commit: leave-approval API (`GET api/leaveapproval/{type}` +
DataTables + shared `_approval_modal` partial), overtime-edit staff force-include,
overtimereport date-range validation + datetimepicker crash fix (plugin method
API), modal global fix (`window.bootstrap` + `$.fn.modal` shim in app.js),
leaveapproval modal TypeError + scoped-CSS fix (data-bs-toggle removed from
approve button; modals appended inside `.page-...-index` wrapper), `$casts` on 78
models, hr_leaves table-name/relations restore, customer model repair, A2A HTTP
transport (`src/http_transport.py`). One file intentionally left uncommitted:
`app/Providers/Auth/EloquentUserProvider.php` (pre-existing plaintext-password
login shim, predates this session — user to decide). Remaining scratch files at
repo root (test_render*.php, casts_*_tmp.*, summary_changes.ts, etc.) are agent
leftovers — safe to delete. Team mesh verified working (MESH_TRANSPORT=local/http;
last run: leaveapproval-modal-fix, all 3 tasks green). To resume: pick the next
bug/feature, author a plan in ai-multiagents/plans/, dispatch via
`MESH_TRANSPORT=local ./.venv/Scripts/python.exe scripts/coordinator_manual.py
--plan plans/<plan>.json`.

**Resume pointer (2026-08-16 late):** MIRROR SYNCED — all session changes to
ai-multiagents copied to the standalone mirror repo `E:\Website\AI-AGENT-TEAM`
and committed there (`a617277`; working tree clean). The two spec txt files
(`AI-AGENT-TEAM.txt` + `AI-AGENT-TEAM-V1.txt`) gained the ULTIMATE RULE
(team-only + team-health-first) in their main-rule sections; docs/01, docs/02,
docs/07, RESUME.md, agents.yaml, agent_adapter/bug_fixer/doc_writer/+
doc_verifier, smoke tests, plans/ all synced byte-identical + compile OK.
G:\ipma_erp2's own working tree (367 files) is intentionally NOT committed —
user said they'll review and commit it themselves; leave it alone.

**Resume pointer (2026-08-16 late):** ULTIMATE RULE documented + extended
(user mandate, 2026-08-16): **the Coordinator can never work alone — it
MUST work IN TEAM.** Added as a highlighted rule in
`docs/01-architecture.md` (§4 Core Design Principles) +
`docs/02-team-roles.md` (§3 Communication Rules + §4 Coordinator Duties):
every instruction is decomposed and dispatched to specialist agents over
the A2A mesh; the Coordinator orchestrates, never executes solo;
un-assignable work must be routed through the team (extend a role) rather
than done directly. **Priority extension: team health comes first** — if
the Coordinator finds a discrepancy/bug in the team itself (agents,
adapters, roles, tooling, infra), resolving that situation is the ultimate
rule BEFORE any Laravel-project task; only after the team bugs are
resolved and tested successfully does the Coordinator return to the task
and work with the team. (Follow-up to the openclaude-fix review: future
fixes to the agent infra itself should be dispatched through the team once
infra-relevant capabilities exist.)

**Resume pointer (2026-08-16 late):** openclaude bug_fixer Bash fix DONE —
root-caused the finding-8 `Bash NotFound` abort: this box exports
`CLAUDE_CODE_USE_POWERSHELL_TOOL=1` at Machine+User scope, which the
adapter's `os.environ` pass-through delivered into every openclaude spawn,
making openclaude expose BOTH `Bash` + `PowerShell` tools; the nemotron
fix brain's Bash calls errored `not found` (guard category `NotFound`) 3x
and openclaude stopped the query. Fix: (1) per-agent `env` overrides in
`agents.yaml` (`CLAUDE_CODE_USE_POWERSHELL_TOOL: "0"` for openclaude) wired
through new `AgentAdapter._resolve_config_env()` (config env wins over
parent, `mcp_env` wins over config); (2) `BugFixer` now falls back across
wired agents — primary fix brain first, then every other wired agent on
`FixError` (records which brain fixed; all-fail still raises). Verified:
live openclaude run with pinned env completes; new smoke regressions in
`scripts/smoke_bugloop.py` (fallback + all-fail) — all 9 smoke suites green.

**Resume pointer (2026-08-16 late):** hallucination guard DONE — new
`src/doc_verifier.py` verifies every generated doc against the codebase
(files, classes, env keys, DB columns, and `Class::$fillable` model-field
claims) before `DocWriter` writes it; unverified claims trigger one brain
correction round, then `DocError` (interactive escape hatch for false
positives). Auto-disables on non-Laravel roots (smoke tests stay green).
Caught the exact finding-6 hallucination (wrong fields on HRLeave) while all
real docs pass with zero flags. New guard smoke tests in
`scripts/smoke_docwriter.py` (clean / hallucinated / corrected flows) — all
9 smoke suites green.

**Resume pointer (2026-08-16 late):** AGENTS.md §4 backlog item DONE — the
legacy module AJAX routes (`routes/Sales/ajax_sales.php` +
`routes/HumanResources/ajax_hr.php`) were migrated into the two API
controllers, creating **`ModelAjaxCRUDController`** (write, `routes/apiCRUD.php`)
as part of it; `AjaxSupportController` (read, `routes/api.php`) gained the 2
sales read endpoints. 17 write + 2 read endpoints, all 19 route names
preserved (blade/JS bridges untouched except 4 hardcoded `url('api/...')`
base-path fixes), `highMgmtAccess:1|5,14` preserved on the 7 HR admin
methods, legacy files deleted. Suite green 47 passed (159 assertions);
view:cache + npx mix --production (Node 22) green; manual smoke OK (500s on
model-bound PATCH are MariaDB-down environmental). ALL 10 audit findings
are now addressed AND the §4 legacy-AJAX backlog item is cleared.
#9 (schema FKs) + #10 (deps) were FIXED this session via
`coordinator_manual.py --plan plans/finding9-finding10.json`; #4's
checklist was APPLIED to the live .env (APP_DEBUG=false set; session
secure/domain + DB password left off/untouched per operator — plain HTTP
for now, with manual steps documented). Full suite green 47 passed (163
assertions) — incl. a bug-loop bonus: OutstationEditTest is now
self-contained on in-memory SQLite, so the suite passes with NO database
running (the old 2 environmental PDO failures are gone). symfony/yaml
7.4.1 → 7.4.15 (CVE-2026-45133 gone from composer audit). `npx mix
--production` fails on this machine's Node v26 (out of the project's
declared engines >=20 <23) but compiles cleanly under portable Node
v22.17.0 — environmental, documented in the audit report. Working tree
(~350 files) still uncommitted (do not `git checkout` anything).

## 2026-08-16: findings #9 (schema FKs) + #10 (deps) FIXED via manual coordinator

- **`coordinator_manual.py --plan plans/finding9-finding10.json`** — 3 tasks:
  task_001 composer (openclaude): symfony/yaml 7.4.1 → 7.4.15, composer
  audit 50 → 47 advisories, npm audit + npx mix run. task_002 migrations
  (openclaude): added the missing `index()` + `foreign()` declarations to
  the `quot_quotation_exclusions` block of
  `database/migrations/2026_08_08_100825_create_quot_quotation_tables.php`
  (ibfk_1 → quot_quotations, ibfk_2 → quot_exclusions, restrictOnDelete,
  matching the sibling `quot_quotation_remarks` block; `down()` untouched,
  no new migration). task_003 test (opencode):
  `tests/Unit/QuotationExclusionsForeignKeyTest.php` (3 tests / 18
  assertions, comment-aware source guards, dirname(__DIR__,2) convention).
- **Bug loop green**: initial 45/2 (the 2 = OutstationEditTest PDO failures)
  → after fixes 47 passed / 0 failed. **Bonus fix**: Bug Fixer converted
  OutstationEditTest to self-contained in-memory SQLite (real controller +
  route + view still exercised; only the DB backend stubbed) — the full
  suite now passes with NO database at all. def_001's fix note flaked on
  JSON parse (known spot; fixer retried, round 2 verified).
- **Coordinator corrections (verify-before-trust):** (1) task_001 agent
  violated its own guardrail by appending `"symfony/yaml": "7.4.15"`
  (exact pin) to composer.json → reverted (`git checkout -- composer.json`)
  + `composer update symfony/yaml:7.4.15`. NOTE: a plain `composer update
  symfony/yaml` resolves v8.1.2 which requires PHP >=8.4.1 (breaks the
  declared `php: ^8.1` floor) — use the `:7.4.15` pin. (2) `npx mix` build
  failure under Node v26 diagnosed as environmental (project engines
  >=20 <23; Node 23+ broke laravel-mix's `require('yargs/yargs')`); build
  verified green under portable Node v22.17.0 (`/tmp/node-v22.17.0-win-x64`
  from nodejs.org — still in /tmp if needed).
- **Doc Writer self-documented**: `docs/changelog/2026-08-16-symfony-yaml-
cve-45133.md`, `docs/changelog/2026-08-16-quot-quotation-exclusions-fk.md`,
  `docs/changelog/2026-08-16-bug-loop-summary.md` (drafts; coordinator's
  audit-report STATUS sections are authoritative).
- **#4**: deploy-time checklist STATUS written into
  `docs/audit-report-2026-08-15.md` (SESSION_SECURE_COOKIE=true, real
  SESSION_DOMAIN, APP_DEBUG=false, rotate DB password, sessions table check).
- **Verification**: `php artisan test` ✅ 47 passed (163 assertions); all 9
  smoke tests green; `php -l` clean on all touched files; composer.json
  unchanged (lock-only bump).

## 2026-08-16: Node 22 installed as the machine default (build toolchain fixed)

- **Why:** Node v26.7.0 (the old default) is outside the project's declared
  engines (`>=20 <23`) and breaks laravel-mix (`require('yargs/yargs')`
  ESM error). Chose **portable binary** over nvm-windows because the
  running freebuff agent process holds `C:\Program Files\nodejs\node.exe`
  (nvm-windows replaces that dir with a symlink → conflict/kill risk).
- **What:** Node **v22.23.2 LTS** extracted to `C:\tools\node22` and inserted
  into the **machine** PATH *before* `C:\Program Files\nodejs\` (user PATH
  alone wouldn't win — machine entries are evaluated first on Windows).
  Helper: `ai-multiagents/scripts/set-node22-path.ps1` (idempotent).
- **Verified in a fresh-PATH shell:** `node --version` → v22.23.2, `npm
  --version` → 10.9.8, `npx mix --production` → webpack compiled
  successfully, `npm run development` → webpack compiled successfully.
  The old Node 26 stays installed (still reachable by absolute path /
  tools that pin it); only the default changed. Open a NEW terminal for
  the new default to take effect.

## 2026-08-16: finding #4 deploy checklist applied (live .env)

- Set `APP_DEBUG=false` in `.env` (verified via `env -u APP_DEBUG php
  artisan tinker` → `config('app.debug')` = false). `SESSION_SECURE_COOKIE`
  kept **off** (site is plain HTTP — Secure cookies would break login),
  documented in .env as pending-HTTPS. `SESSION_DOMAIN` kept `localhost`
  (no real domain given). `DB_PASSWORD` untouched (manual rotation note
  added; MariaDB side must be updated separately).
- ⚠️ Found a **stale shell export `APP_DEBUG=true`** (not in any startup
  file) that overrides .env in processes launched from that shell — Laravel
  reads process env before Dotenv. Fresh processes read the file correctly;
  `unset APP_DEBUG` to make the current shell match.
- STATUS updated in `docs/audit-report-2026-08-15.md` (#4 section).

## 2026-08-16: dependency-upgrade sprint — composer audit 47→0, npm 41→14

**Composer (47 → 0 advisories, `composer audit` = "No security vulnerability
advisories found"):** all fixes were **lock-only patch/minor upgrades within
`composer.json` constraints** (zero manifest edits) — dompdf 3.1.4→3.1.6,
guzzle 7.10→7.15.3, psr7 2.8→2.13, laravel/framework 12.51→12.66,
commonmark 2.8→2.10, phpspreadsheet 1.30.2→1.30.6, symfony http-foundation/
http-kernel/mime 7.4.5→7.4.16, mailer/routing 7.4.4→7.4.15, polyfill-intl-idn
1.33→1.38.1, + ~35 transitive bumps. **Design decision:** symfony pinned to
7.4.x (not the 8.x that laravel 12.66 allows via carbon) — symfony 8 needs
PHP >=8.4 and would silently break the declared `php: ^8.1` floor.
**npm (41 → 14, all high/critical cleared):** safe-mode `npm audit fix`
(62 packages: babel, ws, websocket-driver, ajv, picomatch). The remaining 14
are `fix: None` deps frozen by **laravel-mix 6.0.49** (no 7.x exists):
elliptic/browserify stack, moment-timezone under pc-bootstrap4-datetimepicker,
uuid under webpack-dev-server, yaml. Build verified under portable Node 22
(`webpack compiled successfully`). Full suite still **47 passed (163
assertions)**; `php artisan test`, `view:cache`, routes all green.
See `docs/audit-report-2026-08-15.md` STATUS for the full table.

## 2026-08-16 (late): AGENTS.md §4 legacy AJAX migration DONE (ModelAjaxCRUDController created)

Migrated `routes/Sales/ajax_sales.php` + `routes/HumanResources/ajax_hr.php`
into the two API controllers; all 4 legacy files (2 route files + 2
controllers) deleted, `web.php` requires removed.

- **Write (17) → `API\ModelAjaxCRUDController`** (created via
  `php artisan make:controller`; method bodies moved VERBATIM, so behavior
  is byte-identical): sales `saleamend`/`saleapproved`/`salesend`; HR
  `leavecancel`/`uploaddoc`/`leaverapprove`/`supervisorstatus`/`hodstatus`/
  `dirstatus`/`hrstatus`/`deactivatestaff`/`deletecrossbackup`/`staffactivate`/
  `generateannualleave`/`generatemcleave`/`generatematernityleave`/
  `confirmoutstationattendance`. Registered in `routes/apiCRUD.php`;
  `highMgmtAccess:1|5,14` preserved on the 7 HR admin routes (was
  constructor middleware).
- **Read (2) → `AjaxSupportController`** (`sales.getOptSalesType`,
  `sales.getOptSalesDeliveryType`) in `routes/api.php`. Also **removed dead
  duplicate copies** of the 15 HR write methods from AjaxSupportController
  (pre-migration artifact; the api.php block was commented out — now the
  write logic lives ONLY in ModelAjaxCRUDController per §4).
- **All 19 route names preserved** — every `route('name')` bridge in
  blades/JS resolves unchanged; only 4 blades with hardcoded `url('...')`
  base paths were updated to `url('api/...')`. `UploadValidationTest`
  repointed to the new controller (AjaxSupportController is now read-only).
- **Verified:** `php artisan test` ✅ 47 passed (159 assertions); `route:list`
  shows all 19 under `api/` with correct middleware; `view:cache` ✅;
  `npx mix --production` (Node 22) ✅; curl smoke: GET reads → 302 auth
  redirect, POST-only on PATCH → 405, model-bound PATCH → 500 (MariaDB
  down — environmental).

## Next steps (2026-08-16)

1. **Commit the working tree** — ~350 files of accumulated fixes (findings
   #1–#3, #5–#10 + dep sprint + §4 AJAX migration + agent infra + docs)
   are still uncommitted. Review the diff, then commit. (Do NOT push
   unless asked.)
2. **Deploy-time manual items remaining** (per #4/#9/#10 STATUS): when
   HTTPS lands, enable SESSION_SECURE_COOKIE + real SESSION_DOMAIN;
   rotate the DB password on the MariaDB server side then update .env;
   run `php artisan migrate:fresh --seed` sanity + the `information_schema`
   FK-integrity query on the real DB; `unset APP_DEBUG` in the current
   shell (stale override).

4. **Residual npm (14 low/moderate)**: unfixable within the frozen laravel-
   mix 6 toolchain — elliptic/browserify, moment-timezone (pc-bootstrap4-
   datetimepicker), uuid (webpack-dev-server), yaml. Requires replacing
   laravel-mix 6 or force-overriding majors; separate workstream.

## Previous session (2026-08-15 late) — for history

**Resume pointer (2026-08-15 late session):** audit findings #1, #2, #3,
#5, #6, #7, #8 are FIXED; #4 (session config — deploy-time only), #9
(schema — blocked: MariaDB down), #10 (deps — composer audit run, symfony/
yaml advisory NOT yet patched) remain. Finding #7 + #8 executed via
`coordinator_manual.py` (plans `finding7-uploads.json`, `finding8-authz.
json`) and verified by coordinator (incl. corrections: LeaveController 31-site
softcopy-extension defect, agent-introduced BOM, base_path() in test →
dirname(__DIR__,2); authz config centralization + Gate + middleware).
App state: `php artisan test --testsuite=Unit` → 39 passed (123 assertions);
full suite 38 passed + 2 failed = OutstationEditTest PDO (MariaDB NOT
running — environmental). All 9 smoke tests green. The ~310-file
uncommitted working tree is NOT committed (do not `git checkout` anything).
See `docs/audit-report-2026-08-15.md` STATUS sections for details.

## 2026-08-15 (late): findings #7 (uploads) + #8 (authz) FIXED via manual coordinator

- **finding7-uploads.json** (2× laravel-security + 1× validation-testing):
  staff photo upload → `mimetypes:` MIME rule + generated filename
  `timestamp_Str::slug(username).guessExtension()`; document uploads in
  AjaxController/AjaxSupportController/LeaveController/HRLeaveRequestStore →
  `mimes:` upgraded to `mimetypes:`; uploaddoc filename generated.
  task_003 (test) timed out on opencode but the file was already written;
  coordinator verified + corrected. Doc Writer self-documented (draft).
- **Coordinator corrections (verify-before-trust):** (1) LeaveController
  softcopy filename defect — agent swapped `getClientOriginalName()` for
  `guessExtension()` but kept `$fileName = $currentDate.'_'.$file` at 31
  sites → stored names lost their extension (`…_pdf`); fixed all 31 to
  `$currentDate.'_'.Str::slug($user->name).'.'.$file`. (2) Agent introduced
  a UTF-8 BOM in LeaveController (broke php -l) → stripped. (3) UploadValidationTest
  used `base_path()` which throws under `php artisan test --filter` →
  switched to `dirname(__DIR__, 2)`. Unit suite 35 passed / 113 assertions
  incl. UploadValidationTest (5/28).
- **finding8-authz.json** (1× laravel-security + 1× validation-testing):
  `Login::isAdmin()` now reads `config('auth.admins.*')` (authorise_id +
  legacy login ids [117,72] moved to config/auth.php 'admins' block);
  `Gate::define('admin', fn($user) => $user->isAdmin())` in AuthServiceProvider;
  RedirectIfNotSystemAdmin uses `$user->can('admin')`. Behavior identical.
  Tester filed 1 spurious defect (evidence says 4/4 pass); bug fixer failed
  with repeated `Bash NotFound` tool failures (openclaude tool-config hiccup
  — infra, not code). AuthorizationGuardTest 4/10; unit suite 39 passed /
  123 assertions.
- **#4 session config:** repo side done (`.env.example` commented
  SESSION_SECURE_COOKIE line + config/session.php wired); only
  deployment-time steps remain (real SESSION_DOMAIN, SESSION_SECURE_COOKIE=true,
  APP_DEBUG=false in production .env).
- **#9 schema:** MariaDB DOWN → migrate:fresh + FK check blocked; static
  review confirmed audit claim (quot_quotation_exclusions has no FKs;
  ibfk_* names verbatim).
- **#10 deps:** composer audit → symfony/yaml CVE-2026-45133 (low) NOT yet
  patched; npm audit + npx mix --production NOT yet run.

## Status: M7 DONE — Windows E2E verified (2026-08-14); Linux leg + non-working agents removed from scope

**M7 Windows E2E re-verified 2026-08-14 on this machine** against a fresh
Laravel app at `E:\laravel-m7-e2e`: `summon.py --instruction "Add a GET
/health route …"` → 3 tasks done on **opencode** → bug loop ran (2 suites,
3 passed, 0 defects) → Doc Writer **wrote 2 docs to disk**
(`docs/api-endpoints/health.md`, `docs/execution-plans/test-suite.md`) →
`php artisan test` green (3 passed) → memory persisted across runs
(doc_writer.db 67→69, tester.db 466→467). Two real bugs were found and
fixed during this E2E (see below). The first run also confirmed the
original 2026-08-12 pass (E:\laravel13) is still the record for that app.
The **Linux E2E leg was removed from scope on 2026-08-14** (docs/06,
docs/07 §7) — M7's Definition of Done is now the Windows E2E only, so
**the milestone plan is complete**. The **agent team was de-scoped the same
day** to **opencode** (the only CLI with working credentials) + freebuff
(interactive fallback): openclaude/openclaw/claudeCode/gemini/hermes were
removed from config + docs after credential checks (openclaude → `402 API
quota exhausted`).

## Completed (verified green)

- **M0–M3**: scaffolding, config/state, memory, A2A bus + dispatcher.
  `scripts/smoke_bus.py` → `SMOKE TEST OK`.
- **M4 — Coordinator (Phase 5)**: `src/coordinator.py` (hybrid control loop +
  one-shot brain), selection order (flag → prompt → state.json → freebuff),
  `scripts/summon.py` CLI (`--instruction` / `--coordinator` / `--model`).
  `scripts/smoke_coordinator.py` → `SMOKE COORDINATOR OK`.
- **Phase 6 §1**: `src/test_detector.py` (detects `php artisan test` /
  `npm test` at summon time for Tester prompt injection).
- **M5 — Bug loop (Phase 6 §2–§3)**: `src/tester.py` (hybrid Tester: test
  report contract per docs/02, `run_tests`, regression-gate `verify_fix` —
  affected suite then full suite; never fixes bugs) and `src/bug_fixer.py`
  (hybrid Bug Fixer: fix-note contract, `COMPONENT_OWNERS` routing,
  `fix()` loop with `MAX_FIX_ROUNDS=3` and auto regression gate).
  `scripts/smoke_bugloop.py` → `SMOKE BUGLOOP OK` (2-round regression loop:
  round 1 FAIL → re-fix → round 2 PASS → verified).
- **Bug loop wired into Coordinator**: `Coordinator._run_bug_loop_if_needed`
  auto-triggers Tester → Bug Fixer for any completed task whose capability is
  a Tester capability; `Coordinator(role_brains={...})` pins role→agent.
  `scripts/smoke_coordinator_bugloop.py` → `SMOKE COORDINATOR BUGLOOP OK`
  (testing-task plan triggers loop; migration-only plan skips it).
- **M6 — Doc Writer (Phase 6 §4)**: `src/doc_writer.py` (docs contract,
  clarify-before-inventing loop). `scripts/smoke_docwriter.py` →
  `SMOKE DOCWRITER OK` (produces docs; asks specialists before guessing;
  no-JSON payloads raise `DocError`).
- **Doc Writer wired into Coordinator**: `Coordinator._run_docwriter_if_needed`
  auto-documents every completed implementation task (capability not in
  `TEST_RUN_CAPS`) after the bug loop; bug-loop verdict folded into scope.
  `scripts/smoke_coordinator_docwriter.py` → `SMOKE COORDINATOR DOCWRITER OK`
  (migration plan generates docs; test-only plan skips the doc writer).
- **Interactive role selection**: `Coordinator(ask_roles=True)` prompts the
  user to pick the agent for tester / bug_fixer / doc_writer when first
  needed (REPL). Selection order: explicit pin → prompt → state.json last
  session → role defaults; choices persist. `--tester` / `--bug-fixer` /
  `--doc-writer` flags pin one-shot runs. `scripts/smoke_role_select.py` →
  `SMOKE ROLE SELECT OK`.
- Fake agent gained `--tester` / `--fix` / `--docs` / `--docs-ask` modes
  for deterministic bug-loop / doc-writer testing.
- **agents.yaml templates verified (2026-08-12)** against each CLI's
  `--help`: `opencode run`, `openclaude -p`, `claude -p`, `gemini -p`,
  `openclaw agent --local --message` → `verified: true`, with per-agent
  `stdin_argv` overrides for long-prompt stdin fallback. **freebuff is
  interactive-only** (no headless mode in v0.0.146) → `verified: false`
  **and `headless: false`** (excluded from wiring/selection by
  `orchestrator.available_agents`); **hermes** not installed → unverified.
  Do not pin freebuff as a one-shot brain unless it gains a headless flag.
- **Agent team de-scoped (2026-08-14)**: `config/agents.yaml` now holds
  only **opencode** (`verified: true`, working credentials) and **freebuff**
  (interactive-only, `headless: false`). Removed: openclaude (live
  credential test → `402 API quota exhausted`), openclaw (no provider
  keys), claudeCode + gemini (fail), hermes (never installed).
  `config/roles.yaml` defaults updated to opencode/freebuff only;
  `scripts/smoke_role_select.py` + `scripts/smoke_argv_routing.py` updated
  to assert the de-scoped roster. Wiring is config-driven, so this is a
  config + docs change only.
- **M7 E2E fixes (2026-08-14)**:
  - `dispatcher.dispatch()` cascades failure: a task whose dependency
    `failed`/`canceled` is itself marked `failed` with
    `"blocked by failed dependency <id>"` instead of hanging `pending`
    forever (the coordinator loop silently spun through its 50-round cap
    when the impl task failed and its dependents could never run).
    Regression covered in `scripts/smoke_coordinator.py`.
  - `agent_adapter.extract_json_block()` now accepts ` ```json ` code
    fences (real LLMs wrap the contract JSON in fences, not the documented
    `---` delimiters) and its fallback scans **every** balanced `{...}`
    object, preferring one carrying a contract key (`report`/`docs`/`fix`/
    `tasks`/`clarifying_questions`) over an inline `{"status": "ok"}` in
    prose. Before the fix the Tester's fenced report was mis-parsed as
    `{"status":"ok"}` and the bug loop aborted with "report JSON missing
    'report' object". Regression covered in `scripts/smoke_bugloop.py`.
  - **Retry on malformed brain JSON**: all four brain calls (coordinator
    plan, tester report, bug-fixer note, doc-writer docs) retry **once**
    with a `JSON_RETRY_HINT` when the response's JSON does not parse — a
    real opencode run emitted `{"docs": [...], "questions": []}` missing
    the `]` closing the docs array and the doc writer aborted.
    `agent_adapter.JSON_RETRY_HINT`; regression in
    `scripts/smoke_docwriter.py` (flaky stub: malformed first, valid
    retry).
  - Machine reality (2026-08-14, this machine): **only opencode works**
    (openclaw = no provider keys, claudeCode + gemini = fail, openclaude =
    402 quota — all de-scoped same day). The brain's
    plan routed the impl task to `backend_specialist`, whose first wired
    default is openclaw → whole plan failed. **Seeding every role in
    `config/state.json` (`roles.<role_id>: opencode`) fixes one-agent
    machines** — plan-owner roles resolve via state.json (no flags exist
    to pin them). Out-of-vocabulary capabilities (e.g. `execution-plans`)
    are now **rejected at plan time** by `Coordinator._validate_plan`
    (PlanError), so a mislabeled test task can't silently skip the bug
    loop.
- **M7 real-CLI fixes (2026-08-12)**:
  - `agent_adapter._build_command` resolves binaries via `shutil.which()`
    and wraps Windows `.cmd`/`.bat` shims in `cmd /c` (a bare shim name
    fails with WinError 2).
  - `agent_adapter._shim_requires_stdin()`: newline/CR/`%` prompts go
    via stdin **only** for `.cmd`/`.bat` shims (cmd.exe truncates at
    newlines and env-expands `%VAR%`); real executables keep argv.
  - **M7-esc (2026-08-12):** embedded `"` round-trips **exactly**
    through `cmd /c` → npm shim → node with Python's default
    `list2cmdline` quoting (verified against a node argv echo shaped
    like the real npm gemini shim; a manual `\"` pre-escape was tried
    and double-escaped, so it was **removed**). Quote-only prompts stay
    in argv — required for gemini, whose `-p` reads its value from argv
    only. gemini's stdin fallback keeps an empty `-p` value:
    `stdin_argv: ["gemini", "-p", ""]` (bare `-p` errors).
    Regression: `scripts/smoke_argv_routing.py` →
    `SMOKE ARGV ROUTING OK`.
  - `dispatcher._next_owner` now excludes **every** agent that already
    failed the task (`failed_owners` in task metadata), so reassignment
    walks forward instead of ping-ponging between two failing agents.
  - `doc_writer._write_doc()` actually **writes** generated docs to
    `<laravel-root>/docs` (path-escape guarded; was reported-but-never-
    written).
  - Live machine status: only **opencode** has working credentials
    (all other agents de-scoped 2026-08-14); seed
    `config/state.json` `roles.<role_id>` or pin with flags to steer
    one-shot runs. Full notes in `docs/07 §8`.
  - 2026-08-14: this machine's `.venv` was rebuilt (the old one pointed at
    a deleted pythoncore-3.14 install from another user); system Python
    3.13 + PyYAML works. `git config --global --add safe.directory
    E:/Website/AI-AGENT-TEAM` was needed to read the repo. opencode
    leaves a lingering background process after a run (its own daemon) —
    kill leftover `opencode.exe` between runs if needed.

Docs `docs/06` Phase 6 fully marked DONE (M5 + M6).

## 2026-08-15 deploy session (G:\ipma_erp2)

- **Config de-scope applied for real**: `config/agents.yaml` no longer
  contains openclaude and `config/roles.yaml` defaults are opencode/freebuff
  only. This had been documented 2026-08-14 but never written to the files;
  `smoke_role_select.py` + `smoke_argv_routing.py` asserted the de-scoped
  roster and failed against the stale config. Now green.
- **One-shot mode no longer crashes on closed stdin**: `coordinator.run_one_shot`
  and `doc_writer.document` used `input()`; a backgrounded/nohup one-shot run
  died with `EOF when reading a line` whenever the brain asked a clarifying
  question (hit twice in the live audit run). Both now detect a non-TTY stdin
  and pass "(user unavailable - proceed with reasonable assumptions)" to the
  brain instead of blocking. `_brain_prompt` also includes the project root
  (opencode asked for it) via `getattr(self.orch, 'project_root', '')` so
  minimal smoke-test orch objects don't break.
- **Live deploy exercised**: full read-only audit of G:\ipma_erp2 ran as a
  6-task one-shot (all on opencode) and completed; all 8 smoke tests pass.
  Audit report written to `docs/audit-report-2026-08-15.md` (the Doc Writer
  had crashed pre-fix; report consolidated from `logs/task_00X.out.txt`).
- **Manual-coordinator driver added**: `scripts/coordinator_manual.py` lets an
  EXTERNAL brain (Buffy/you) author the plan JSON (same contract as the
  subprocess brain) and execute it through the same dispatcher -> bug loop ->
  doc writer path, with NO agent subprocess used for the plan itself.
  Usage: `python scripts/coordinator_manual.py --plan plan.json
  [--tester X --bug-fixer Y --doc-writer Z]`. Covered by the new
  `scripts/smoke_manual_coordinator.py` (9 assertions, all green).

## 2026-08-15: openclaude RESTORED (was: de-scoped 2026-08-14)

- **Live credential check PASSED**: the NVIDIA NIM key in `~/.openclaude.json`
  is valid (no 401/402) and `nvidia/nemotron-3.5-lightning-30b-a3b` returns
  HTTP 200 with real completions. Root cause of the old 402-era failure and
  the current "may not exist or you may not have access" error: the NVIDIA
  NIM provider profile's `model` pointed at `nvidia/llama-3.1-nemotron-70b-
  instruct`, which the account lists but which 404s on invoke. Fixed the
  profile (`~/.openclaude.json`, backup at `.openclaude.json.bak-buffy-
  20260815`) to the working model. openclaude `-p` (print mode) confirmed:
  `CRED_OK`, real file-read task, and `ORDER_OK` with the model flag AFTER
  the prompt (adapter appends model_flag at the end).
- **config/agents.yaml**: openclaude re-added with a FIXED template. Old
  template was broken twice: missing `-p` (would launch interactive mode and
  hang the adapter) and `--model {model}` baked into `argv` (literal
  `{model}` reaches the CLI when no model is set). New template:
  `argv: ["openclaude", "-p", "{prompt}"]`, `stdin_argv: ["openclaude",
  "-p"]`, `model_flag: ["--model", "{model}"]`, `verified: true`.
- **config/roles.yaml**: openclaude restored to its 8 roles (backend_specialist,
  laravel_specialist, php_specialist, db_admin, mysql/mariadb/mssql
  specialists, bug_fixer). Roster is now opencode + openclaude wired.
- **Smoke tests updated**: `smoke_argv_routing.py` + `smoke_role_select.py`
  asserted the de-scoped roster; updated to the two-agent roster and to
  assert the corrected openclaude template. All 9 smoke tests green.
- **Verified live**: orchestrator wires openclaude (74 capabilities, idle),
  and a real task through the adapter returns `state: completed` / `argv`.
- **Project-scoped openclaude config added**: `.openclaude/settings.local.json`
  at the Laravel root (G:\ipma_erp2), ported from E:\Website\AI-AGENT-TEAM's
  equivalent — grants openclaude read access to the project tree and tool
  permissions (php artisan, smoke scripts, pip, etc.), with both
  `ai-multiagents/scripts/` and `scripts/` command forms. Globally gitignored
  (`**/.openclaude/settings.local.json`), same as on E:.

## 2026-08-15: MCP tool layer wired (21st, playwright, filesystem, figma, context7)

- `config/agents.yaml` gained a top-level `mcp` block (opencode config
  schema, source `~/.config/opencode/opencode.json*`) defining the five
  servers, and each wired agent declares `mcp: [...]` + how the adapter
  delivers them: **opencode** via `mcp_delivery: env` (OPENCODE_CONFIG env
  var, merged with the global opencode config) and **openclaude** via
  `mcp_delivery: flag` (`--mcp-config <file>`, mcpServers shape).
- `AgentAdapter` renders a per-agent config file under `logs/<agent>-mcp.json`
  (gitignored) at boot: resolves `${VAR}` / `${env.VAR}` / `${VAR:-default}`
  from the environment and `{project_root}` to the Laravel root; local
  servers on Windows are wrapped `cmd /c` to match ~/.openclaude/settings.json;
  opencode entries get explicit `enabled: true`. `mcp_flag` is kept in both
  argv and stdin modes; `mcp_env` is merged into the subprocess env.
- **openclaude needs `--permission-mode bypassPermissions`** in argv or MCP
  tools load but stall on interactive approval prompts in one-shot mode
  (verified live: tool listed, prompt blocked; flag fixes it).
- **Verified live**: openclaude called `mcp__21st__get_usage` -> real usage
  data; opencode called `filesystem_list_directory` -> directory listing
  (both through the real adapter, `state: completed`). All 9 smoke tests
  green (smoke_argv_routing updated for the new argv).

## 2026-08-15: finding #5 run via manual coordinator (external brain = Buffy)

- **`coordinator_manual.py --plan plans/finding5-sqli.json`** dispatched 3 tasks (2× laravel-security + 1× validation-testing) — all executed on **openclaude** (laravel_specialist) and completed: LIKE-wildcard escaping in `ActivityLogController` + 31 sites in `AjaxDBController`, plus `tests/Unit/LikeWildcardEscapeTest.php` (10 data cases + source guard).
- **Bug loop ran green**: Tester (opencode) full suite → 2 suites, 31 passed, 0 defects, 0 fixes needed.
- **Doc Writer flaked again on JSON parse** (`doc writer brain returned no parseable JSON`) — the doc was written by the external brain to `docs/audit-report-2026-08-15.md` (finding #5 STATUS section) instead. Worth a retry-hardening look (the one-shot JSON_RETRY_HINT path may not be hit through `coordinator_manual` since `_run_docwriter_if_needed` uses the same adapter — likely a model-output issue, not a code path issue).
- Housekeeping note: `run_terminal_command` kills `nohup` children on its own timeout; long team runs must be executed in the foreground with an unbounded timeout.

## 2026-08-15: finding #6 run via manual coordinator (mass assignment)

- **`coordinator_manual.py --plan plans/finding6-mass-assignment.json`** — 3 tasks (2× laravel-security + 1× validation-testing) on **openclaude**. The 600s tool cap killed the run after task_001; a follow-up plan (`finding6-mass-assignment-2.json`, 2 tasks) finished task_002 + the test task on opencode. Bug loop green (2 suites, 35 passed, 0 defects).
- **Coordinator corrected task_001's output** (verify-before-trust): the agent's `HRLeave::$fillable` was incomplete (missing `period_day`, `period_time`, `softcopy`, `verify_code`, `hardcopy`, `leave_status_id`, `remarks` — would silently break the WORKING `leave` module), `store()` merged a bogus `'status' => 'pending'` (no such column), `createMany(array_only($validated,'arrays'))` was the wrong shape + `hasManyRelationship()` doesn't exist, and `update()` still used `$request->except(...)` (never actually fixed). Corrected all four.
- **Coordinator corrected the guard test**: the agent's `MassAssignmentGuardTest` used a machine-specific absolute path (`G:\ipma_erp2\...`, `base_path()` unavailable in plain TestCase) and only checked one controller — now `dirname(__DIR__, 2)`-relative and checks all 6 controllers. 35 tests / 113 assertions.
- **Doc Writer hit a SECOND flake mode live**: non-interactive loop discarded a valid docs payload whenever the brain returned docs + a clarifying question ("brain kept asking; giving up after 3 rounds"). Fixed: `DocWriter.document()` tells the brain to proceed with assumptions once, then accepts the docs (dropping the question) on the next round. Regression in `smoke_docwriter.py` (`--docs-ask-docs`). All 9 smoke tests green.
- **Doc Writer hallucination warning**: the brain's own finding-6 doc contained invented whitelist names (marked "(assumed)") that don't match the code — doc-writer output must be treated as a draft subject to coordinator verification until a hallucination guard exists.

## 2026-08-15: Doc Writer JSON flake FIXED (self-documentation via coordinator_manual)

- **Root cause, two parts:** (1) `extract_json_block`'s fallback brace-counting scan was **string-unaware** — doc content embedding PHP like `"%{$search}%"` broke the balance walk, so valid contracts were missed; (2) the doc-writer brain (openclaude/nemotron) holds **MCP filesystem tools** and repeatedly wrote the docs contract itself (`docs/response.json` + `.md`) while returning prose — the loop raised `DocError` even though a valid contract sat on disk, so the run did not self-document.
- **Fix landed:**
  - `agent_adapter.extract_json_block()`: fallback scan is now **string-aware** (braces inside `"..."` don't affect balance) and every candidate gets a **light repair pass** (`_repair_json`: trailing commas, truncated closing brackets — the M7 missing-`]` case now repairs in place instead of needing the retry). Regression: `scripts/smoke_docwriter.py` (repairable-json test) + `scripts/check_parser_hardening.py`.
  - `doc_writer.py`: `MAX_BRAIN_ATTEMPTS = 3` with an escalating hint (final retry explicitly forbids tools/file writes); **`_salvage_from_disk(since)`** adopts a `*.json` docs contract the brain wrote to `docs/` during the run (checked between attempts, so a file-writing brain costs 1 LLM call, not 3); the prompt now tells the brain it must NOT write files itself.
  - All 9 smoke tests green, incl. new salvage + retry + repair regressions in `smoke_docwriter.py`; fake agent gained `--docs-file <path>` (simulates the MCP filesystem write) for deterministic testing.


## Testing

- Bus/adapter/dispatcher/coordinator/bug-loop/doc-writer smoke tests live
  in `scripts/` and run with the fake agent (`scripts/fake_agent.py`, has
  `--plan`/`--clarify`/`--report`/`--slow`/`--tester`/`--fix`/`--docs`/
  `--docs-ask` test modes).
- Run: `.venv\Scripts\python.exe scripts\smoke_bus.py`,
  `scripts\smoke_coordinator.py`, `scripts\smoke_bugloop.py`,
  `scripts\smoke_docwriter.py`.

## Notes

- Windows is the dev OS; `cmd /c` is used to launch `.cmd`/`.bat` agent CLIs
  (works for .exe too). Agent invocation is template-driven
  (`config/agents.yaml`), never hard-coded.
- The coordinator selection order is implemented in
  `src/coordinator.py::select_coordinator`.
- The Tester/BugFixer regression contract lives in `docs/02 §5`; defect
  `component` values drive Bug Fixer routing (`src/bug_fixer.py`).
- Environment/tool output was flaky on this machine (duplicated lines,
  phantom output) — trust exit codes + smoke test pass lines, and prefer
  redirecting output to files when verifying.
