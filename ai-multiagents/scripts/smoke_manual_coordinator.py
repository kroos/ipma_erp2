"""Smoke test: manual-coordinator driver path (external brain authors the plan).

Verifies the coordinator_manual.py flow: a plan authored outside the team
(loaded from a JSON file — no agent subprocess for the plan itself) executes
through the dispatcher, and the Tester -> Bug Fixer loop + Doc Writer run
exactly as in a brain-driven run.

Run from anywhere:  python scripts/smoke_manual_coordinator.py
"""

from __future__ import annotations

import json
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


roles = config_loader.load_roles()


def make_adapter(agent_id: str, extra: list[str], capabilities: list[str], bus):
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


with tempfile.TemporaryDirectory() as tmp:
    tmp = Path(tmp)
    # the external coordinator (Buffy/you) authors the plan to a JSON file
    plan_path = tmp / "plan.json"
    plan_path.write_text(json.dumps({
        "clarifying_questions": [],
        "tasks": [
            {"title": "audit login security",
             "capability": "laravel-security",
             "owner": "laravel_specialist", "dependsOn": []},
            {"title": "run the backend test suite",
             "capability": "backend-testing",
             "owner": "tester", "dependsOn": ["audit login security"]},
        ],
    }), encoding="utf-8")

    print("== external plan executes through the team (no brain subprocess) ==")
    bus = LocalBusTransport()
    executor = make_adapter("executor", [], ["laravel-security"], bus)
    tester_brain = make_adapter(
        "tester_brain", ["--tester"], roles.get("tester", {}).get("capabilities", []), bus
    )
    fixer_brain = make_adapter(
        "fixer_brain", ["--fix"], roles.get("bug_fixer", {}).get("capabilities", []), bus
    )
    docs_brain = make_adapter(
        "docs_brain", ["--docs"], roles.get("doc_writer", {}).get("capabilities", []), bus
    )
    adapters = {
        "executor": executor,
        "tester_brain": tester_brain,
        "fixer_brain": fixer_brain,
        "docs_brain": docs_brain,
    }
    dispatcher = Dispatcher(bus, adapters)
    orch = SimpleNamespace(
        adapters=adapters,
        bus=bus,
        dispatcher=dispatcher,
        roles=roles,
        project_root=tmp,  # docs land under tmp/docs, not the real repo
        state=StateStore(tmp / "state.json"),
    )
    coord = Coordinator(
        orch, brain=None,  # no agent brain: the plan was authored externally
        role_brains={
            "tester": "tester_brain",
            "bug_fixer": "fixer_brain",
            "doc_writer": "docs_brain",
        },
    )
    # -- the exact code path the driver (coordinator_manual.py) uses --
    raw = json.loads(plan_path.read_text(encoding="utf-8"))
    plan = coord._validate_plan(raw)
    coord._execute_plan(plan)

    tasks = dispatcher.tasks
    check("2 tasks created from external plan", len(tasks) == 2)
    check("impl task done", tasks["task_001"].state == "done")
    check("test task done", tasks["task_002"].state == "done")
    check("dependency wired",
          tasks["task_002"].depends_on == (tasks["task_001"].task_id,))
    check("owner resolved for impl task", tasks["task_001"].owner == "executor")

    check("bug loop ran", coord._bug_loop_results is not None)
    fixes = (coord._bug_loop_results or {}).get("fixes", [])
    check("defect verified", bool(fixes) and fixes[0]["status"] == "verified")

    check("doc writer ran", coord._docwriter_results is not None)
    docs = (coord._docwriter_results or {}).get("docs", [])
    written = bool(docs) and (tmp / docs[0]["path"]).is_file()
    check("doc written to disk", written, f"path={docs[0]['path'] if docs else '?'}")

    print("\n== finding-5 shape: doc brain WRITES the contract itself (no JSON) ==")
    # the doc-writer brain holds MCP filesystem tools: it writes the docs
    # contract to disk and replies with prose only. document() must salvage
    # the contract from disk so the run still self-documents.
    bus2 = LocalBusTransport()
    executor2 = make_adapter("executor2", [], ["laravel-security"], bus2)
    docs2 = make_adapter(
        "docs2", ["--docs-file", str(tmp / "docs" / "brain-contract.json")],
        roles.get("doc_writer", {}).get("capabilities", []), bus2,
    )
    adapters2 = {"executor2": executor2, "docs2": docs2}
    dispatcher2 = Dispatcher(bus2, adapters2)
    orch2 = SimpleNamespace(
        adapters=adapters2,
        bus=bus2,
        dispatcher=dispatcher2,
        roles=roles,
        project_root=tmp,
        state=StateStore(tmp / "state2.json"),
    )
    coord2 = Coordinator(
        orch2, brain=None,
        role_brains={"doc_writer": "docs2"},
    )
    plan2 = coord2._validate_plan({"clarifying_questions": [], "tasks": [
        {"title": "audit login security",
         "capability": "laravel-security",
         "owner": "laravel_specialist", "dependsOn": []},
    ]})
    coord2._execute_plan(plan2)
    check("doc writer ran (salvage scenario)",
          coord2._docwriter_results is not None)
    docs2_out = (coord2._docwriter_results or {}).get("docs", [])
    written2 = bool(docs2_out) and (tmp / docs2_out[0]["path"]).is_file()
    check("salvaged doc written to disk", written2,
          f"path={docs2_out[0]['path'] if docs2_out else '?'}")

    print("== out-of-vocabulary capability rejected (as in brain runs) ==")
    try:
        coord._validate_plan({"clarifying_questions": [], "tasks": [
            {"title": "x", "capability": "not-a-capability",
             "owner": "laravel_specialist", "dependsOn": []}]})
        check("bad capability rejected", False)
    except Exception:
        check("bad capability rejected", True)

print()
if failures:
    print(f"SMOKE MANUAL COORDINATOR FAILED: {failures}")
    sys.exit(1)
print("SMOKE MANUAL COORDINATOR OK")
