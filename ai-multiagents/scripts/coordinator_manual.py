"""Manual-coordinator driver — an external brain (Buffy/you) authors the plan.

The normal flow asks an agent subprocess for the plan (Coordinator._ask_brain).
This driver skips that: you write the plan JSON (same contract) to a file and
the team executes it — dispatch to specialist agents, then the Tester -> Bug
Fixer loop and the Doc Writer run exactly as in a brain-driven run.

Plan contract (docs/06 Phase 5):
    {
      "clarifying_questions": ["..."],        # unused here; ask the user yourself
      "tasks": [
        {"title": "...", "capability": "<vocab>",
         "dependsOn": ["<title of an earlier task>"], "owner": "<role id>"}
      ]
    }

Usage:
  python scripts/coordinator_manual.py --plan plan.json
  python scripts/coordinator_manual.py --plan plan.json --tester opencode
  python scripts/coordinator_manual.py --plan plan.json --model opencode=sonnet
"""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

# Windows consoles default to cp1252; agent output (titles, summaries)
# frequently carries non-ASCII (emoji, arrows like U+2192) which would
# crash every print(). Reconfigure to UTF-8 with lossy fallback.
for _stream in (sys.stdout, sys.stderr):
    if hasattr(_stream, "reconfigure"):
        try:
            _stream.reconfigure(encoding="utf-8", errors="replace")
        except (OSError, ValueError):
            pass

from src.coordinator import Coordinator
from src.orchestrator import Orchestrator


def parse_model_overrides(values: list[str]) -> dict[str, str]:
    overrides: dict[str, str] = {}
    for item in values or []:
        if "=" not in item:
            raise SystemExit(f"--model expects agent=model, got: {item!r}")
        agent_id, model = item.split("=", 1)
        overrides[agent_id.strip()] = model.strip()
    return overrides


def main() -> None:
    parser = argparse.ArgumentParser(
        prog="coordinator_manual",
        description="Execute a coordinator plan authored by an external brain "
                    "(no agent subprocess is used for the plan itself).",
    )
    parser.add_argument("--plan", required=True, help="path to plan JSON file")
    parser.add_argument("--tester", help="agent id to play the Tester role")
    parser.add_argument("--bug-fixer", dest="bug_fixer",
                        help="agent id to play the Bug Fixer role")
    parser.add_argument("--doc-writer", dest="doc_writer",
                        help="agent id to play the Doc Writer role")
    parser.add_argument(
        "--model", action="append", default=[],
        help="agent=model override (repeatable)",
    )
    args = parser.parse_args()

    try:
        raw = json.loads(Path(args.plan).read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        raise SystemExit(f"[manual-coordinator] cannot read plan {args.plan!r}: {exc}")
    if not isinstance(raw, dict) or not isinstance(raw.get("tasks"), list):
        raise SystemExit("[manual-coordinator] plan must be {tasks: [...]}")

    orch = Orchestrator(model_overrides=parse_model_overrides(args.model))
    orch.boot()
    try:
        role_brains = {
            role: agent for role, agent in (
                ("tester", args.tester),
                ("bug_fixer", args.bug_fixer),
                ("doc_writer", args.doc_writer),
            ) if agent
        }
        # brain stays None: this driver never calls _ask_brain, so the
        # coordinator brain is the external author of the plan (you).
        # interactive=False: backgrounded runs must never block on input().
        coord = Coordinator(orch, brain=None, role_brains=role_brains or None,
                            ask_roles=False, interactive=False)
        plan = coord._validate_plan(raw)   # vocabulary + shape checks
        coord._execute_plan(plan)
    except Exception as exc:  # report, still persist state
        print(f"[manual-coordinator] error: {exc}")
    finally:
        orch.shutdown()


if __name__ == "__main__":
    main()
