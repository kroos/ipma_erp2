"""Smoke test: bug loop wired into the Coordinator (docs/06 Phase 6 §3).

A plan containing a testing task (capability in the tester role) must
auto-trigger Tester -> Bug Fixer, ending with a bug-loop summary.

Run from anywhere:  python scripts/smoke_coordinator_bugloop.py
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


def run_one_shot(plan_mode: str, caps: dict[str, list[str]]) -> Coordinator:
    """Fresh bus/adapters/coordinator for one scenario."""
    local_bus = LocalBusTransport()
    global bus, dispatcher
    bus = local_bus
    brain = make_adapter("brain", [plan_mode], [])
    tester_brain = make_adapter("tester_brain", ["--tester"], caps.get("tester", []))
    fixer_brain = make_adapter("fixer_brain", ["--fix"], caps.get("bug_fixer", []))
    executor = make_adapter("executor", [], ["migrations"])  # Scenario B
    adapters = {
        "brain": brain,
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
        project_root=SYSTEM_DIR,
        state=StateStore(temp_state),
    )
    coord = Coordinator(
        orch, brain="brain",
        role_brains={"tester": "tester_brain", "bug_fixer": "fixer_brain"},
    )
    # NOTE: the plan task owner "tester" routes to tester_brain, so the fake
    # runs once as the dispatched task and again inside the bug loop's
    # full-suite run. That double execution is intentional here.
    coord.run_one_shot("run the backend test suite")
    return coord


roles = config_loader.load_roles()

with tempfile.TemporaryDirectory() as tmp:
    temp_state = Path(tmp) / "state.json"

    print("== Scenario A: testing task in plan triggers the bug loop ==")
    coord = run_one_shot("--plan-test", roles)
    tasks = dispatcher.tasks
    check("plan created 1 task", len(tasks) == 1)
    check("testing task done", tasks["task_001"].state == "done")
    check("bug loop results recorded", coord._bug_loop_results is not None)
    data = coord._bug_loop_results or {}
    report = data.get("report", {})
    fixes = data.get("fixes", [])
    check("tester found 1 defect", len(report.get("defects", [])) == 1)
    check("1 fix outcome", len(fixes) == 1)
    check("defect verified", fixes and fixes[0]["status"] == "verified")
    check("verified in 2 rounds (regression loop)", fixes and fixes[0]["rounds"] == 2)

    print("\n== Scenario B: no testing tasks -> bug loop skipped ==")
    coord2 = run_one_shot("--plan", roles)
    tasks2 = dispatcher.tasks
    check("2 migration tasks done", len(tasks2) == 2
          and all(t.state == "done" for t in tasks2.values()))
    check("bug loop skipped", coord2._bug_loop_results is None)

print()
if failures:
    print(f"SMOKE COORDINATOR BUGLOOP FAILED: {failures}")
    sys.exit(1)
print("SMOKE COORDINATOR BUGLOOP OK")
