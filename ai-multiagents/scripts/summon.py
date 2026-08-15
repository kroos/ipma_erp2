"""ai-multiagents entry point.

  python scripts/summon.py                      interactive REPL
  python scripts/summon.py --instruction "..."  one-shot
  python scripts/summon.py --coordinator freebuff
  python scripts/summon.py --tester openclaude --bug-fixer opencode
  python scripts/summon.py --model freebuff=sonnet --model opencode=sonnet

Boot -> select coordinator (flag -> prompt -> state.json -> freebuff) ->
run -> persist state + shutdown.

Pinned roles (tester / bug_fixer / doc_writer) resolve at the point they
are needed: flag -> interactive prompt (REPL only) -> state.json last
session -> role defaults.
"""

from __future__ import annotations

import argparse
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

from src.coordinator import Coordinator, select_coordinator
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
        prog="summon",
        description="Boot the ai-multiagents team and hand control to the Coordinator.",
    )
    parser.add_argument("--instruction", help="one-shot instruction (else REPL)")
    parser.add_argument("--coordinator", help="agent id to act as Coordinator brain")
    parser.add_argument("--tester", help="agent id to play the Tester role")
    parser.add_argument("--bug-fixer", dest="bug_fixer",
                        help="agent id to play the Bug Fixer role")
    parser.add_argument("--doc-writer", dest="doc_writer",
                        help="agent id to play the Doc Writer role")
    parser.add_argument(
        "--model", action="append", default=[],
        help="agent=model override (repeatable), e.g. --model freebuff=sonnet",
    )
    args = parser.parse_args()

    orch = Orchestrator(model_overrides=parse_model_overrides(args.model))
    orch.boot()
    try:
        brain = select_coordinator(orch, explicit=args.coordinator)
        role_brains = {
            role: agent for role, agent in (
                ("tester", args.tester),
                ("bug_fixer", args.bug_fixer),
                ("doc_writer", args.doc_writer),
            ) if agent
        }
        print(f"\n[coordinator] brain agent: {brain}")
        coordinator = Coordinator(
            orch, brain=brain,
            role_brains=role_brains or None,
            # REPL asks the user for unpinned roles; one-shot stays silent
            ask_roles=not args.instruction,
            # one-shot runs must never block on input() (backgrounded runs
            # may have an open-but-empty stdin where isatty() lies)
            interactive=not args.instruction,
        )
        if args.instruction:
            coordinator.run_one_shot(args.instruction)
        else:
            coordinator.repl()
    except Exception as exc:  # report, still persist state
        print(f"[summon] error: {exc}")
    finally:
        orch.shutdown()


if __name__ == "__main__":
    main()
