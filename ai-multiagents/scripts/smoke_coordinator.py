"""Smoke test: hybrid Coordinator (M4) with a fake brain + fake executor.

Run from anywhere:  python scripts/smoke_coordinator.py
"""

from __future__ import annotations

import sys
import tempfile
from pathlib import Path
from types import SimpleNamespace

from unittest import mock

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src.agent_adapter import AgentAdapter
from src.coordinator import Coordinator, PlanError, select_coordinator
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


bus = LocalBusTransport()

brain = AgentAdapter(
    "brain", {
        "binary": "python",
        "argv": ["python", str(FAKE), "-p", "{prompt}", "--clarify"],
        "model_flag": [],
        "timeout_sec": 60,
        "verified": True,
    },
    bus,
    project_root=SYSTEM_DIR,
)
brain.register("Brain", [])

executor = AgentAdapter(
    "executor", {
        "binary": "python",
        "argv": ["python", str(FAKE), "-p", "{prompt}"],
        "model_flag": [],
        "timeout_sec": 60,
        "verified": True,
    },
    bus,
    project_root=SYSTEM_DIR,
)
executor.register("Executor", ["migrations"])

dispatcher = Dispatcher(bus, {"brain": brain, "executor": executor})

with tempfile.TemporaryDirectory() as tmp:
    state = StateStore(Path(tmp) / "state.json")
    orch = SimpleNamespace(
        adapters={"brain": brain, "executor": executor},
        bus=bus,
        dispatcher=dispatcher,
        roles={},   # no role->agent mapping; capability routing resolves owner
        state=state,
    )

    print("== select_coordinator ==")
    # explicit
    check("explicit choice", select_coordinator(orch, explicit="brain") == "brain")
    check("persisted choice", state.get_last_coordinator() == "brain")
    # fallback freebuff (not present) -> first available
    check("fallback first available", select_coordinator(orch) == "brain")

    print("== one-shot with clarifying questions ==")
    coord = Coordinator(orch, brain="brain")
    with mock.patch("builtins.input", side_effect=["mysql", "app_"]):
        coord.run_one_shot("set up the database schema")

    tasks = dispatcher.tasks
    check("plan created 2 tasks", len(tasks) == 2)
    t1, t2 = tasks["task_001"], tasks["task_002"]
    check("task 1 done", t1.state == "done")
    check("task 2 done (dep satisfied)", t2.state == "done")
    check("dependency wired", t2.depends_on == (t1.task_id,))
    check("owner resolved via capability", t1.owner == "executor")

print("== plan capability validation (M7 E2E) ==")
# The brain must only use capabilities from the vocabulary; an
# out-of-vocabulary one (e.g. "execution-plans") is rejected at plan time
# instead of silently skipping the bug loop.
orch_v = SimpleNamespace(
    adapters={"brain": brain, "executor": executor},
    bus=bus,
    dispatcher=dispatcher,
    roles={"tester": {"capabilities": ["backend-testing", "api-testing"]}},
    state=state,
)
coord_v = Coordinator(orch_v, brain="brain")
try:
    coord_v._validate_plan({"clarifying_questions": [], "tasks": [
        {"title": "run suite", "capability": "execution-plans",
         "owner": "tester", "dependsOn": []}]})
    check("out-of-vocabulary capability rejected", False)
except PlanError:
    check("out-of-vocabulary capability rejected", True)
valid = coord_v._validate_plan({"clarifying_questions": [], "tasks": [
    {"title": "run suite", "capability": "backend-testing",
     "owner": "tester", "dependsOn": []}]})
check("valid capability accepted",
      valid["tasks"][0]["capability"] == "backend-testing")

print("== reassignment walks forward past failing agents (M7) ==")
# Two agents that always fail + one that succeeds. The dispatcher must
# walk fail_a -> fail_b -> ok (never ping-pong back to a failed agent).
# Failing adapters exit non-zero immediately via python -c.
bus2 = LocalBusTransport(
    capability_map={"migrations": ["fail_a", "fail_b", "ok"]}
)

def make_fail(agent_id: str):
    a = AgentAdapter(
        agent_id, {
            "binary": "python",
            "argv": ["python", "-c", "import sys; sys.exit(1)"],
            "model_flag": [],
            "timeout_sec": 30,
            "verified": True,
        },
        bus2,
        project_root=SYSTEM_DIR,
    )
    a.register(f"Failing {agent_id}", ["migrations"])
    return a

fail_a, fail_b = make_fail("fail_a"), make_fail("fail_b")
ok_agent = AgentAdapter(
    "ok", {
        "binary": "python",
        "argv": ["python", str(FAKE), "-p", "{prompt}"],
        "model_flag": [],
        "timeout_sec": 30,
        "verified": True,
    },
    bus2,
    project_root=SYSTEM_DIR,
)
ok_agent.register("OK agent", ["migrations"])

dispatcher2 = Dispatcher(bus2, {"fail_a": fail_a, "fail_b": fail_b, "ok": ok_agent})
# auto owner: resolved by capability map order (fail_a first)
dispatcher2.create_task("retry me", "migrations", owner=None)
for _ in range(20):
    if dispatcher2.tick() == 0:
        break
task = dispatcher2.tasks["task_001"]
check("task ends done after walking failures", task.state == "done")
check("owner ended on the working agent", task.owner == "ok")
check("failures tracked dispatcher-private",
      dispatcher2._task_failures[task.task_id] == {"fail_a", "fail_b"})
check("no failed_owners leaked into task metadata",
      "failed_owners" not in task.metadata)

print("== failed dependency cascades to dependents (M7 E2E) ==")
# One permanently-failing agent + a dependent task. When task A fails,
# task B (dependsOn A) must fail explicitly instead of hanging `pending`.
bus3 = LocalBusTransport(
    capability_map={"migrations": ["boom"]}
)
boom = AgentAdapter(
    "boom", {
        "binary": "python",
        "argv": ["python", "-c", "import sys; sys.exit(1)"],
        "model_flag": [],
        "timeout_sec": 30,
        "verified": True,
    },
    bus3,
    project_root=SYSTEM_DIR,
)
boom.register("Boom", ["migrations"])
dispatcher3 = Dispatcher(bus3, {"boom": boom})
dispatcher3.create_task("migrate users", "migrations", owner=None)
dispatcher3.create_task("migrate posts", "migrations", owner=None,
                        depends_on=("task_001",))
for _ in range(20):
    if dispatcher3.tick() == 0:
        break
t_a, t_b = dispatcher3.tasks["task_001"], dispatcher3.tasks["task_002"]
check("dependency fails", t_a.state == "failed")
check("dependent fails, not pending", t_b.state == "failed")
check("dependent has clear reason",
      t_b.error == "blocked by failed dependency task_001")

print()
if failures:
    print(f"SMOKE COORDINATOR FAILED: {failures}")
    sys.exit(1)
print("SMOKE COORDINATOR OK")
