"""Smoke test: Doc Writer (M6/M7) — produces docs, clarifies before inventing.

Run from anywhere:  python scripts/smoke_docwriter.py
"""

from __future__ import annotations

import json
import sys
import tempfile
from pathlib import Path
from types import SimpleNamespace
from unittest import mock

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src.agent_adapter import AgentAdapter
from src.doc_writer import DocError, DocWriter
from src.message_bus import LocalBusTransport

FAKE = SYSTEM_DIR / "scripts" / "fake_agent.py"

failures = []


def check(name: str, cond: bool, detail: str = "") -> None:
    status = "OK " if cond else "FAIL"
    print(f"  [{status}] {name}{'  ' + detail if detail else ''}")
    if not cond:
        failures.append(name)


bus = LocalBusTransport()


def make_adapter(agent_id: str, extra: list[str]):
    adapter = AgentAdapter(
        agent_id,
        {
            "binary": "python",
            "argv": ["python", str(FAKE), "-p", "{prompt}", *extra],
            "model_flag": [],
            "timeout_sec": 60,
            "verified": True,
        },
        bus,
        project_root=SYSTEM_DIR,
    )
    adapter.register(f"Fake {agent_id}", [])
    return adapter


docs_brain = make_adapter("docs_brain", ["--docs"])
ask_brain = make_adapter("ask_brain", ["--docs-ask"])

with tempfile.TemporaryDirectory() as tmp:
    tmp_root = Path(tmp)
    orch = SimpleNamespace(
        adapters={"docs_brain": docs_brain, "ask_brain": ask_brain},
        roles={},
        project_root=tmp_root,
    )

    print("== Doc Writer: produces docs ==")
    writer = DocWriter(orch, brain="docs_brain")
    payload = writer.document("login flow")
    check("1 doc produced", len(payload["docs"]) == 1)
    check("no open questions", payload["questions"] == [])
    check("doc path set", payload["docs"][0]["path"] == "docs/feature-login.md")
    check("doc content set", "Login" in payload["docs"][0]["content"])
    check("doc written to disk under project root",
          (tmp_root / "docs" / "feature-login.md").is_file())

    print("\n== Doc Writer: clarifies instead of inventing ==")
    writer2 = DocWriter(orch, brain="ask_brain")
    with mock.patch("builtins.input", side_effect=["LoginController"]):
        payload2 = writer2.document("login flow")
    check("questions surfaced first", payload2["questions"] == [])
    check("docs produced after answer", len(payload2["docs"]) == 1)
    check("never invents: answer used",
          "LoginController" in payload2["docs"][0]["content"])

    print("\n== Doc Writer: unsafe paths refused ==")
    bad_writer = DocWriter(orch, brain="docs_brain")
    for bad in ["../evil.md", "/etc/passwd", "docs/../../evil.md", "C:evil.md"]:
        try:
            bad_writer._write_doc({"path": bad, "content": "x"})
            check(f"refuses {bad!r}", False)
        except DocError:
            check(f"refuses {bad!r}", True)

    print("\n== Doc Writer: hard failures ==")
    plain_brain = make_adapter("plain_brain", [])
    orch.adapters["plain_brain"] = plain_brain
    bad_writer = DocWriter(orch, brain="plain_brain")  # ECHO mode: no JSON
    with mock.patch("builtins.input", side_effect=[""]):
        try:
            bad_writer.document("no json")
            check("no-JSON docs raises DocError", False)
        except DocError:
            check("no-JSON docs raises DocError", True)

    print("\n== Doc Writer: repairable JSON fixed in place (no retry) ==")
    class RepairableBrain:
        def __init__(self):
            self.calls = 0

        def run_task(self, message):
            self.calls += 1
            # malformed: trailing comma + missing closing `}` of the payload
            text = ('------------------\n'
                    '{"docs": [{"path": "docs/x.md", "title": "X", '
                    '"summary": "s", "content": "c"}], "questions": [],}\n'
                    '------------------')
            return {"parts": [{"type": "text", "text": text}],
                    "metadata": {"state": "completed"}}

    repairable = RepairableBrain()
    orch.adapters["repairable"] = repairable
    repair_writer = DocWriter(orch, brain="repairable")
    payload3 = repair_writer._ask_brain("scope", [])
    check("trailing comma repaired in place (no retry)", repairable.calls == 1)
    check("repaired payload produced the docs", len(payload3["docs"]) == 1)

    print("\n== Doc Writer: malformed JSON retried once (M7 E2E) ==")
    class FlakyBrain:
        def __init__(self):
            self.calls = 0

        def run_task(self, message):
            self.calls += 1
            if self.calls == 1:
                # malformed: docs array missing its closing `]` (not
                # repairable by the balance pass)
                text = ('{"docs": [{"path": "docs/x.md", "title": "X", '
                        '"summary": "s", "content": "c"}, "questions": []}')
            else:
                text = ('------------------\n'
                        '{"docs": [{"path": "docs/x.md", "title": "X", '
                        '"summary": "s", "content": "c"}], "questions": []}\n'
                        '------------------')
            return {"parts": [{"type": "text", "text": text}],
                    "metadata": {"state": "completed"}}

    flaky = FlakyBrain()
    orch.adapters["flaky"] = flaky
    retry_writer = DocWriter(orch, brain="flaky")
    payload3 = retry_writer._ask_brain("scope", [])
    check("malformed first response retried once", flaky.calls == 2)
    check("retry produced the docs payload", len(payload3["docs"]) == 1)

    print("\n== Doc Writer: persistent no-JSON raises after retries ==")
    from src.doc_writer import MAX_BRAIN_ATTEMPTS

    class DumbBrain:
        def __init__(self):
            self.calls = 0

        def run_task(self, message):
            self.calls += 1
            # prose only, never any JSON — e.g. the brain writing files
            return {"parts": [{"type": "text",
                               "text": "I documented everything myself."}],
                    "metadata": {"state": "completed"}}

    dumb = DumbBrain()
    orch.adapters["dumb"] = dumb
    dumb_writer = DocWriter(orch, brain="dumb")
    try:
        dumb_writer.document("no json")
        check("persistent no-JSON raises DocError", False)
    except DocError:
        check("persistent no-JSON raises DocError", True)
    check("no-JSON brain retried MAX_BRAIN_ATTEMPTS times",
          dumb.calls == MAX_BRAIN_ATTEMPTS)

    print("\n== Doc Writer: salvages brain-written contract from disk ==")
    # the brain (with filesystem MCP) writes the docs contract itself and
    # replies with prose only — document() must recover the payload from
    # disk instead of aborting (finding-5 flake)
    salvaged_json = tmp_root / "docs" / "brain-contract.json"
    salvage_brain = make_adapter(
        "salvage_brain", ["--docs-file", str(salvaged_json)])
    orch.adapters["salvage_brain"] = salvage_brain
    salvage_writer = DocWriter(orch, brain="salvage_brain")
    payload4 = salvage_writer.document("login flow")
    check("salvaged 1 doc from the brain-written contract",
          len(payload4["docs"]) == 1)
    check("salvaged doc written to disk under project root",
          (tmp_root / "docs" / "salvaged.md").is_file())

    print("\n== Doc Writer: non-interactive keeps docs despite question ==")
    askdocs_brain = make_adapter("askdocs_brain", ["--docs-ask-docs"])
    orch.adapters["askdocs_brain"] = askdocs_brain
    askdocs_writer = DocWriter(orch, brain="askdocs_brain", interactive=False)
    payload5 = askdocs_writer.document("login flow")
    check("docs kept despite open question", len(payload5["docs"]) == 1)
    check("question dropped after non-interactive accept",
          payload5["questions"] == [])
    check("kept doc written to disk under project root",
          (tmp_root / "docs" / "feature-login.md").is_file())

# ---------------------------------------------------------------------------
# Hallucination guard: verify docs against code before writing
# ---------------------------------------------------------------------------
print("\n== Doc Writer: hallucination guard against a fake Laravel root ==")

with tempfile.TemporaryDirectory() as tmp2:
    laravel = Path(tmp2)  # this temp dir WILL look like a Laravel root
    (laravel / "artisan").write_text("#!/usr/bin/env php\n", encoding="utf-8")
    (laravel / "composer.json").write_text("{}\n", encoding="utf-8")
    (laravel / ".env.example").write_text(
        "APP_DEBUG=true\nDB_PASSWORD=secret\n", encoding="utf-8")
    (laravel / "app" / "Models").mkdir(parents=True)
    (laravel / "app" / "Models" / "HRLeave.php").write_text(
        "<?php\nnamespace App\\Models\\HumanResources;\n"
        "class HRLeave { protected $fillable = "
        "['period_day', 'remarks', 'softcopy']; }\n", encoding="utf-8")
    (laravel / "routes").mkdir()
    (laravel / "routes" / "web.php").write_text(
        "<?php\nRoute::patch('/leavecancel/{hrleave}', ...)"
        "->name('leavecancel.leavecancel');\n", encoding="utf-8")
    (laravel / "config").mkdir()
    (laravel / "config" / "auth.php").write_text(
        "<?php\nreturn ['admins' => [117]];\n", encoding="utf-8")
    (laravel / "resources" / "views" / "login").mkdir(parents=True)
    (laravel / "resources" / "views" / "login" / "index.blade.php").write_text(
        "x", encoding="utf-8")
    (laravel / "database" / "migrations").mkdir(parents=True)
    (laravel / "database" / "migrations" / "x.php").write_text(
        "<?php\nSchema::create('hr_leaves', function ($t) {"
        "$t->id(); $t->string('period_day'); $t->text('remarks'); });\n",
        encoding="utf-8")

    # brain that emits a CLEAN doc (all claims exist in the fake root)
    class CleanBrain:
        def run_task(self, message):
            content = (
                "# Leave Cancel\n\n"
                "Uses `route('leavecancel.leavecancel')`, "
                "config `config('auth.admins')`, env `env('APP_DEBUG')`, "
                "model `HRLeave::$fillable = ['period_day', 'remarks']`, "
                "view `login.index`, file `app/Models/HRLeave.php`.\n"
            )
            text = ('------------------\n'
                    '{"docs": [{"path": "docs/leave-cancel.md", '
                    '"title": "Leave Cancel", "summary": "s", '
                    '"content": ' + json.dumps(content) + '}], '
                    '"questions": []}\n------------------')
            return {"parts": [{"type": "text", "text": text}],
                    "metadata": {"state": "completed"}}

    clean_brain = CleanBrain()
    clean_orch = SimpleNamespace(
        adapters={"clean": clean_brain},
        roles={},
        project_root=laravel,
    )
    clean_writer = DocWriter(clean_orch, brain="clean", interactive=False)
    payload6 = clean_writer.document("leave cancel")
    check("clean doc passes verification", len(payload6["docs"]) == 1)
    check("clean doc written to disk",
          (laravel / "docs" / "leave-cancel.md").is_file())

    # brain that hallucinates a field name that does NOT exist
    class BadBrain:
        def __init__(self):
            self.calls = 0

        def run_task(self, message):
            self.calls += 1
            content = (
                "# Leave Cancel\n\n"
                "Updates `HRLeave::$fillable = "
                "['start_date', 'end_date', 'leave_type']` "
                "via `route('leavecancel.leavecancel')`.\n"
            )
            text = ('------------------\n'
                    '{"docs": [{"path": "docs/bad-leave-cancel.md", '
                    '"title": "Leave Cancel", "summary": "s", '
                    '"content": ' + json.dumps(content) + '}], '
                    '"questions": []}\n------------------')
            return {"parts": [{"type": "text", "text": text}],
                    "metadata": {"state": "completed"}}

    bad_brain = BadBrain()
    bad_orch = SimpleNamespace(
        adapters={"bad": bad_brain},
        roles={},
        project_root=laravel,
    )
    bad_writer = DocWriter(bad_orch, brain="bad", interactive=False)
    try:
        bad_writer.document("leave cancel")
        check("hallucinated doc raises DocError", False)
    except DocError:
        check("hallucinated doc raises DocError", True)
    check("hallucinated doc NOT written",
          not (laravel / "docs" / "bad-leave-cancel.md").exists())

    # brain that hallucinates on call 1 but CORRECTS on the verify round
    class FixingBrain:
        def __init__(self):
            self.calls = 0

        def run_task(self, message):
            self.calls += 1
            if self.calls == 1:
                content = (
                    "# Leave Cancel\n\n"
                    "Updates `HRLeave::$fillable = "
                    "['start_date', 'end_date']` via "
                    "`route('leavecancel.leavecancel')`.\n"
                )
            else:
                content = (
                    "# Leave Cancel\n\n"
                    "Updates `HRLeave::$fillable = ['period_day', 'remarks']` "
                    "via `route('leavecancel.leavecancel')`.\n"
                )
            text = ('------------------\n'
                    '{"docs": [{"path": "docs/fixed-leave-cancel.md", '
                    '"title": "Leave Cancel", "summary": "s", '
                    '"content": ' + json.dumps(content) + '}], '
                    '"questions": []}\n------------------')
            return {"parts": [{"type": "text", "text": text}],
                    "metadata": {"state": "completed"}}

    fixing_brain = FixingBrain()
    fixing_orch = SimpleNamespace(
        adapters={"fixing": fixing_brain},
        roles={},
        project_root=laravel,
    )
    fixing_writer = DocWriter(fixing_orch, brain="fixing", interactive=False)
    payload7 = fixing_writer.document("leave cancel")
    check("brain correction round used", fixing_brain.calls == 2)
    check("corrected doc passes verification", len(payload7["docs"]) == 1)
    check("corrected doc written to disk",
          (laravel / "docs" / "fixed-leave-cancel.md").is_file())
    check("corrected content used",
          "start_date" not in (laravel / "docs" / "fixed-leave-cancel.md")
          .read_text(encoding="utf-8"))

print()
if failures:
    print(f"SMOKE DOCWRITER FAILED: {failures}")
    sys.exit(1)
print("SMOKE DOCWRITER OK")
