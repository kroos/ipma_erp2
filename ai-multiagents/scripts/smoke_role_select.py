"""Smoke test: interactive role->agent selection (docs/06 Phase 6).

Selection order for tester/bug_fixer/doc_writer brains:
    explicit pin -> interactive prompt (ask_roles) -> state.json last
    session -> role defaults.

Run from anywhere:  python scripts/smoke_role_select.py
"""

from __future__ import annotations

import sys
import tempfile
from pathlib import Path
from types import SimpleNamespace
from unittest import mock

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src import config_loader
from src.agent_adapter import AgentAdapter
from src.coordinator import Coordinator, _prompt_role_agent
from src.dispatcher import Dispatcher
from src.message_bus import LocalBusTransport
from src.orchestrator import available_agents
from src.state import StateStore

FAKE = SYSTEM_DIR / "scripts" / "fake_agent.py"

failures = []


def check(name: str, cond: bool, detail: str = "") -> None:
    status = "OK " if cond else "FAIL"
    print(f"  [{status}] {name}{'  ' + detail if detail else ''}")
    if not cond:
        failures.append(name)


roles = config_loader.load_roles()


def make_adapter(bus, agent_id: str, extra: list[str], capabilities: list[str]):
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


def build_orch(bus, adapters, state_path: Path):
    dispatcher = Dispatcher(bus, adapters)
    return SimpleNamespace(
        adapters=adapters,
        bus=bus,
        dispatcher=dispatcher,
        roles=roles,
        project_root=SYSTEM_DIR,
        state=StateStore(state_path),
    ), dispatcher


with tempfile.TemporaryDirectory() as tmp:
    state_path = Path(tmp) / "state.json"

    print("== A. selection order ==")
    bus = LocalBusTransport()
    opencode = make_adapter(bus, "opencode", [], ["backend-testing"])
    openclaude = make_adapter(bus, "openclaude", [], ["backend-testing"])
    gemini = make_adapter(bus, "gemini", [], ["backend-testing"])
    adapters = {"opencode": opencode, "openclaude": openclaude, "gemini": gemini}
    orch, _ = build_orch(bus, adapters, state_path)

    # 1) explicit pin
    coord = Coordinator(orch, brain="opencode",
                        role_brains={"tester": "openclaude"})
    check("explicit pin wins", coord._select_role_agent("tester") == "openclaude")
    check("explicit pin persisted",
          orch.state.get_last_role_agent("tester") == "openclaude")

    # 2) interactive prompt (ask_roles)
    coord2 = Coordinator(orch, brain="opencode", ask_roles=True)
    with mock.patch("builtins.input", side_effect=["2"]):  # gemini
        check("prompt picks numbered choice",
              coord2._select_role_agent("tester") == "gemini")
    with mock.patch("builtins.input", side_effect=["opencode"]):
        check("prompt accepts agent id",
              coord2._select_role_agent("bug_fixer") == "opencode")
    with mock.patch("builtins.input", side_effect=[""]):  # Enter -> default
        check("prompt Enter uses first default",
              coord2._select_role_agent("doc_writer") in ("opencode", "openclaude", "gemini"))

    # 3) state.json last session (no pin, no prompt)
    orch.state.set_last_role_agent("tester", "openclaude")
    coord3 = Coordinator(orch, brain="opencode")  # ask_roles=False
    check("last session used", coord3._select_role_agent("tester") == "openclaude")

    # 4) role defaults fallback (nothing pinned, saved, or prompted)
    orch.state.set_last_role_agent("tester", "not-wired-agent")
    coord4 = Coordinator(orch, brain="opencode")
    selected = coord4._select_role_agent("tester")
    check("falls back to a wired default",
          selected in ("opencode", "openclaude", "gemini"))

    # 5) no candidates -> None
    check("no agents -> None", _prompt_role_agent({}, roles, "tester") is None)

    print("\n== B. headless-only wiring (freebuff interactive-only) ==")
    wired = available_agents(config_loader.load_agents())
    check("interactive freebuff excluded from wiring", "freebuff" not in wired)
    # 2026-08-15: openclaude restored after its credential check passed
    check("opencode + openclaude are the wired headless agents",
          set(wired) == {"opencode", "openclaude"})
    check("de-scoped agents not wired",
          not (set(wired) & {"openclaw", "claudeCode", "gemini", "hermes"}))

    print("\n== C. full coordinator run with user-chosen brains ==")
    bus2 = LocalBusTransport()
    brain = make_adapter(bus2, "brain", ["--plan-test"], [])
    tester_brain = make_adapter(bus2, "tester_brain", ["--tester"],
                                roles.get("tester", {}).get("capabilities", []))
    fixer_brain = make_adapter(bus2, "fixer_brain", ["--fix"],
                               roles.get("bug_fixer", {}).get("capabilities", []))
    adapters2 = {"brain": brain, "tester_brain": tester_brain,
                 "fixer_brain": fixer_brain}
    orch2, _ = build_orch(bus2, adapters2, state_path)
    coord5 = Coordinator(orch2, brain="brain", ask_roles=True)
    # user answers the two role prompts (tester then bug fixer)
    with mock.patch("builtins.input", side_effect=["tester_brain", "fixer_brain"]):
        coord5.run_one_shot("run the backend test suite")
    check("user-chosen tester brain used",
          orch2.state.get_last_role_agent("tester") == "tester_brain")
    check("user-chosen fixer brain used",
          orch2.state.get_last_role_agent("bug_fixer") == "fixer_brain")
    check("bug loop ran", coord5._bug_loop_results is not None)
    fixes = (coord5._bug_loop_results or {}).get("fixes", [])
    check("defect verified via chosen brains",
          bool(fixes) and fixes[0]["status"] == "verified")

print()
if failures:
    print(f"SMOKE ROLE SELECT FAILED: {failures}")
    sys.exit(1)
print("SMOKE ROLE SELECT OK")
