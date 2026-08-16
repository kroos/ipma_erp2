"""Smoke test: the bug loop (M5) — Tester finds -> Bug Fixer fixes ->
Tester verifies PASS, with the regression gate (affected suite, then full).

Run from anywhere:  python scripts/smoke_bugloop.py
"""

from __future__ import annotations

import sys
from pathlib import Path

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from types import SimpleNamespace

from src.agent_adapter import AgentAdapter, extract_json_block
from src.bug_fixer import BugFixer, FixError
from src.message_bus import LocalBusTransport
from src.tester import ReportError, Tester

FAKE = SYSTEM_DIR / "scripts" / "fake_agent.py"

failures = []


def check(name: str, cond: bool, detail: str = "") -> None:
    status = "OK " if cond else "FAIL"
    print(f"  [{status}] {name}{'  ' + detail if detail else ''}")
    if not cond:
        failures.append(name)


def make_adapter(agent_id: str, extra: list[str], capabilities: list[str]):
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
    adapter.register(f"Fake {agent_id}", capabilities)
    return adapter


bus = LocalBusTransport()
tester_brain = make_adapter("tester_brain", ["--tester"], ["backend-testing"])
fixer_brain = make_adapter("fixer_brain", ["--fix"], ["fix-implementation"])
plain_brain = make_adapter("plain_brain", [], [])  # returns ECHO: ... (no JSON)

orch = SimpleNamespace(
    adapters={
        "tester_brain": tester_brain,
        "fixer_brain": fixer_brain,
        "plain_brain": plain_brain,
    },
    roles={},  # explicit brains; roles unused in smoke
    project_root=SYSTEM_DIR,
)

print("== Tester: run_tests finds a defect ==")
tester = Tester(orch, brain="tester_brain")
report = tester.run_tests(scope="full suite", target="AuthTest")
check("report has failed count", report["failed"] == 1)
check("defect documented", len(report["defects"]) == 1)
defect = report["defects"][0]
check("defect component backend", defect["component"] == "backend")
check("defect normalized status", defect["status"] == "open")
check("defect normalized evidence", isinstance(defect["evidence"], dict))

print("\n== Bug loop: fix -> regression gate (round 1 FAIL, round 2 PASS) ==")
fixer = BugFixer(orch, brain="fixer_brain")
outcome = fixer.fix(defect, tester)
check("defect verified", outcome["status"] == "verified")
check("took 2 rounds (regression loop)", outcome["rounds"] == 2)
check("root cause captured", "redirect" in outcome["fix"]["rootCause"])
check("routed to backend specialist", outcome["fix"]["routedTo"] == "backend_specialist")
check("defect status updated", defect["status"] == "verified")

print("\n== Bug loop: fix brain FAILS -> falls back to next wired agent ==")
# finding-8 regression: openclaude's Bash tool failed with `NotFound` and
# the defect was lost. The BugFixer must try the next wired agent (opencode)
# instead of giving up. broken_brain returns ECHO (no JSON -> FixError);
# good_brain (--fix) is the fallback and must produce the fix note.
broken_brain = make_adapter("broken_brain", [], [])          # ECHO, no JSON
orch.adapters["broken_brain"] = broken_brain
fallback_fixer = BugFixer(orch, brain="broken_brain")
fallback_outcome = fallback_fixer.fix(dict(defect), tester)
check("defect verified via fallback brain",
      fallback_outcome["status"] == "verified")
check("note records the fallback brain",
      fallback_outcome["fix"].get("brain") == "fixer_brain")
check("primary brain tried before fallback",
      fallback_outcome["fix"].get("brain") != "broken_brain")

print("\n== Bug loop: ALL fix brains fail -> defect NOT lost silently ==")
all_broken = BugFixer(orch, brain="plain_brain")  # plain_brain also ECHO
# plain_brain is the primary and every other adapter returns a fix note, so
# force a total-failure scenario with a dedicated orchard (only bad brains).
bad_orch = SimpleNamespace(
    adapters={"bad1": broken_brain, "bad2": plain_brain},
    roles={},
    project_root=SYSTEM_DIR,
)
all_broken = BugFixer(bad_orch, brain="bad1")
try:
    all_broken.fix(dict(defect), tester)
    check("all-failed fix brains raise FixError", False)
except FixError:
    check("all-failed fix brains raise FixError", True)

print("\n== extract_json_block: fences + inline prose (M7 E2E) ==")
# Real-LLM output wraps the report in a ```json fence while prose inline
# objects ({"status": "ok"}) appear earlier — the parser must pick the
# contract object, not the first `{...}` in the text.
real_world = (
    "Full suite verified.\n"
    "- HealthTest passes: returns 200 + {\"status\":\"ok\"}\n"
    "```json\n"
    '{"report": {"suitesRun": 2, "passed": 3, "failed": 0, "defects": []}}\n'
    "```\n"
)
parsed = extract_json_block(real_world)
check("fenced report extracted (not the inline object)",
      parsed is not None and "report" in parsed
      and parsed["report"]["passed"] == 3)
check("dashes-delimited still works",
      extract_json_block("----\n{\"docs\": []}\n----") == {"docs": []})
check("bare object at end still works",
      extract_json_block("done: {\"fix\": {\"status\": \"submitted\"}}")
      == {"fix": {"status": "submitted"}})
check("no JSON returns None", extract_json_block("no json here") is None)

print("\n== Tester never fixes / hard failures ==")
bad_tester = Tester(orch, brain="plain_brain")
try:
    bad_tester.run_tests(scope="full suite")
    check("no-JSON report raises ReportError", False)
except ReportError:
    check("no-JSON report raises ReportError", True)

print()
if failures:
    print(f"SMOKE BUGLOOP FAILED: {failures}")
    sys.exit(1)
print("SMOKE BUGLOOP OK")
