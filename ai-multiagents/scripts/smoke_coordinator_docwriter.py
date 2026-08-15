"""Smoke test: Doc Writer wired into the Coordinator (docs/06 Phase 6 §4).

Completed implementation tasks auto-generate docs; plans with only
test-run tasks skip the doc writer.

Run from anywhere:  python scripts/smoke_coordinator_docwriter.py
"""

from __future__ import annotations

import sys
import tempfile
from pathlib import Path
from types import SimpleNamespace

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src import config_loader
from src.agent_adapter import AgentAdapter
from src.coordinator import Coordinator
from src.dispatcher import Dispatcher
from src.message_bus import LocalBusTransport
from src.state import StateStore

FAKE = SYSTEM_DIR / "scripts" / "fake_agent.py"

failures = []


def check(name: str, cond: bool, detail: str = "") -> None:
    status = "OK " if cond else "FAIL"
    print(f"  [{status}] {name}{'  ' + detail if detail else ''}")
    if not cond:
        failures.append(name)


bus: LocalBusTransport
dispatcher: Dispatcher


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


def run_one_shot(plan_mode: str, roles: dict, temp_state: Path, tmp_root: Path,
                 role_brains: dict[str, str]) -> Coordinator:
    """Fresh bus/adapters/coordinator for one scenario."""
    local_bus = LocalBusTransport()
    global bus, dispatcher
    bus = local_bus
    brain = make_adapter("brain", [plan_mode], [])
    docs_brain = make_adapter("docs_brain", ["--docs"],
                              roles.get("doc_writer", {}).get("capabilities", []))
    tester_brain = make_adapter("tester_brain", ["--tester"],
                                roles.get("tester", {}).get("capabilities", []))
    fixer_brain = make_adapter("fixer_brain", ["--fix"],
                               roles.get("bug_fixer", {}).get("capabilities", []))
    executor = make_adapter("executor", [], ["migrations"])
    adapters = {
        "brain": brain,
        "docs_brain": docs_brain,
        "tester_brain": tester_brain,
        "fixer_brain": fixer_brain,
        "executor": executor,
    }
    dispatcher = Dispatcher(bus, adapters)
    orch = SimpleNamespace(
        adapters=adapters,
        bus=bus,
        dispatcher=dispatcher,
        roles=roles,
        project_root=tmp_root,
        state=StateStore(temp_state),
    )
    coord = Coordinator(
        orch, brain="brain", role_brains=role_brains,
    )
    coord.run_one_shot("add users migration")
    return coord


roles = config_loader.load_roles()

with tempfile.TemporaryDirectory() as tmp:
    temp_state = Path(tmp) / "state.json"
    tmp_root = Path(tmp)

    print("== Scenario A: implementation tasks done -> docs generated ==")
    coord = run_one_shot("--plan", roles, temp_state, tmp_root,
                         {"doc_writer": "docs_brain"})
    tasks = dispatcher.tasks
    check("2 migration tasks done", len(tasks) == 2
          and all(t.state == "done" for t in tasks.values()))
    check("doc writer results recorded", coord._docwriter_results is not None)
    payload = coord._docwriter_results or {}
    docs = payload.get("docs", [])
    check("1 doc produced", len(docs) == 1)
    check("doc path set", docs and docs[0]["path"] == "docs/feature-login.md")
    check("bug loop untouched", coord._bug_loop_results is None)

    print("\n== Scenario B: test-only plan -> doc writer skipped ==")
    coord2 = run_one_shot("--plan-test", roles, temp_state, tmp_root,
                          {"doc_writer": "docs_brain",
                           "tester": "tester_brain",
                           "bug_fixer": "fixer_brain"})
    check("testing task done", len(dispatcher.tasks) == 1
          and all(t.state == "done" for t in dispatcher.tasks.values()))
    check("bug loop ran", coord2._bug_loop_results is not None)
    check("doc writer skipped (no impl work)", coord2._docwriter_results is None)

print()
if failures:
    print(f"SMOKE COORDINATOR DOCWRITER FAILED: {failures}")
    sys.exit(1)
print("SMOKE COORDINATOR DOCWRITER OK")
