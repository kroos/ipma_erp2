"""Smoke test: Omniroute bus + AgentAdapter + Dispatcher (M3/M4).

Run from anywhere:  python scripts/smoke_bus.py
"""

from __future__ import annotations

import sys
from pathlib import Path

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src.agent_adapter import AgentAdapter, extract_json_block
from src.dispatcher import Dispatcher
from src.message_bus import AgentCard, LocalBusTransport, RoutingError, make_message

FAKE = SYSTEM_DIR / "scripts" / "fake_agent.py"

failures = []


def check(name: str, cond: bool, detail: str = "") -> None:
    status = "OK " if cond else "FAIL"
    print(f"  [{status}] {name}{'  ' + detail if detail else ''}")
    if not cond:
        failures.append(name)


# ---- Part A: bus routing, dedupe, no-double-dispatch --------------------
print("== A. LocalBusTransport ==")
bus = LocalBusTransport()

received: dict[str, list[dict]] = {"a": [], "b": []}


def handler_a(msg):
    received["a"].append(msg)


def handler_b(msg):
    received["b"].append(msg)


bus.register_agent(AgentCard("a", "Agent A", ["migrations"]), handler_a)
bus.register_agent(AgentCard("b", "Agent B", ["migrations", "models"]), handler_b)

msg = make_message("task", "dispatcher", to=["a"], task_id="task_001",
                   parts=[{"type": "text", "text": "do it"}])
bus.send(msg)
check("named delivery", len(received["a"]) == 1)

# re-send same messageId -> dedupe
bus.send(msg)
check("dedupe by messageId", len(received["a"]) == 1)

# capability route: a (working) -> b, then a idle -> a
bus.update_status("a", "working", "task_001")
check("capability route skips busy", bus.capability_route("migrations") == "b")
bus.update_status("a", "idle")
check("capability route idle first", bus.capability_route("migrations") == "a")

# no-double-dispatch
bus.update_status("a", "working", "task_001")
try:
    bus.send(make_message("task", "dispatcher", to=["a"], task_id="task_002"))
    check("no-double-dispatch rejects", False, "no RoutingError raised")
except RoutingError:
    check("no-double-dispatch rejects", True, "RoutingError raised")
bus.update_status("a", "idle")

# capability addressing via toCapability
n = len(received["b"])
bus.send(make_message("notify", "dispatcher", to_capability="models",
                      parts=[{"type": "text", "text": "hi"}]))
check("toCapability delivers", len(received["b"]) == n + 1)
check("toCapability skips others", len(received["a"]) == 1)

# broadcast notify
n = len(received["a"]) + len(received["b"])
bus.send(make_message("notify", "dispatcher", parts=[{"type": "text", "text": "all"}]))
check("broadcast notify", len(received["a"]) + len(received["b"]) == n + 2)

# envelope validation
try:
    make_message("bogus", "x")
    check("unknown kind rejected", False)
except ValueError:
    check("unknown kind rejected", True)


# ---- Part B: AgentAdapter against the fake CLI ---------------------------
print("== B. AgentAdapter ==")
adapter = AgentAdapter(
    "fake", {
        "binary": "python",
        "argv": ["python", str(FAKE), "-p", "{prompt}"],
        "model_flag": [],
        "timeout_sec": 600,
        "verified": True,
    },
    bus,
    project_root=SYSTEM_DIR,
)
adapter.register("Fake Agent", ["migrations"])

# argv mode
res = adapter.run_task(make_message(
    "task", "dispatcher", to=["fake"], task_id="task_a",
    parts=[{"type": "text", "text": "hello world"}],
))
check("argv mode completed", res["metadata"]["state"] == "completed")
check("argv mode output", "ECHO: hello world" in res["parts"][0]["text"])

# stdin auto-switch: prompt longer than ARGV_LIMIT (8000)
long_prompt = "x" * 9000
adapter2 = AgentAdapter(
    "fake2", {
        "binary": "python",
        "argv": ["python", str(FAKE), "-p", "{prompt}"],
        "model_flag": [],
        "timeout_sec": 600,
        "verified": True,
    },
    bus,
    project_root=SYSTEM_DIR,
)
adapter2.register("Fake Agent 2", [])
res2 = adapter2.run_task(make_message(
    "task", "dispatcher", to=["fake2"], task_id="task_b",
    parts=[{"type": "text", "text": long_prompt}],
))
check("stdin auto-switch mode", res2["metadata"]["mode"] == "stdin")
check("stdin auto-switch output", res2["metadata"]["state"] == "completed")

# missing binary entirely -> spawn failure recorded, task failed
bad = AgentAdapter(
    "bad", {
        "binary": "python",
        "argv": ["definitely-not-a-real-binary-xyz", "-p", "{prompt}"],
        "model_flag": [],
        "timeout_sec": 600,
        "verified": False,
    },
    bus,
    project_root=SYSTEM_DIR,
)
bad.register("Bad Agent", [])
res3 = bad.run_task(make_message(
    "task", "dispatcher", to=["bad"], task_id="task_c",
    parts=[{"type": "text", "text": "fallback"}],
))
check("missing binary -> failed", res3["metadata"]["state"] == "failed")
check("missing binary -> error text",
      "cannot start" in res3["parts"][0]["text"])

# timeout enforcement
slow = AgentAdapter(
    "slow", {
        "binary": "python",
        "argv": ["python", str(FAKE), "-p", "{prompt}", "--slow"],
        "model_flag": [],
        "timeout_sec": 1,
        "verified": False,
    },
    bus,
    project_root=SYSTEM_DIR,
)
slow.register("Slow Agent", [])
res4 = slow.run_task(make_message(
    "task", "dispatcher", to=["slow"], task_id="task_slow",
    parts=[{"type": "text", "text": "zzz"}],
))
check("timeout kills process", res4["metadata"]["timedOut"] is True)
check("timeout -> failed", res4["metadata"]["state"] == "failed")


# ---- Part C: JSON report extraction --------------------------------------
print("== C. extract_json_block ==")
sample = ("ran 2 suites\n"
          "------------------\n"
          '{"report": {"suitesRun": 2, "passed": 3, "failed": 1, "defects": []}}\n'
          "------------------\n")
block = extract_json_block(sample)
check("delimited JSON parsed", block is not None and block["report"]["suitesRun"] == 2)
check("no JSON -> None", extract_json_block("no json here") is None)


# ---- Part D: Dispatcher ---------------------------------------------------
print("== D. Dispatcher ==")
disp = Dispatcher(bus, {"fake": adapter})
t1 = disp.create_task("create users table", "migrations", owner="fake")
t2 = disp.create_task("create posts table", "migrations", owner="fake", depends_on=(t1.task_id,))
t3 = disp.create_task("orphaned task", "migrations", owner="fake", depends_on=("task_999",))
disp.tick()
check("first task dispatched", t1.state == "done")
check("dependent drains in same tick", t2.state == "done")
check("dep on missing task waits", t3.state == "pending")
check("dependency order", t2.depends_on == (t1.task_id,))


print()
if failures:
    print(f"SMOKE TEST FAILED: {failures}")
    sys.exit(1)
print("SMOKE TEST OK")
