#!/usr/bin/env python3
"""Fake agent CLI for smoke tests (never calls a real AI).

  python scripts/fake_agent.py -p <prompt>     -> echoes prompt (argv mode)
  python scripts/fake_agent.py                  -> echoes stdin (stdin mode)
  python scripts/fake_agent.py -p x --slow      -> sleeps, then echoes
  python scripts/fake_agent.py -p x --report    -> prints delimited JSON report
  python scripts/fake_agent.py -p x --tester    -> Tester brain (report/verify)
  python scripts/fake_agent.py -p x --fix       -> Bug Fixer brain (fix note)
  python scripts/fake_agent.py -p x --docs      -> Doc Writer brain (docs)
  python scripts/fake_agent.py -p x --docs-ask  -> Doc Writer brain (clarify)
  python scripts/fake_agent.py -p x --docs-file <path>
                         -> Doc Writer brain that WRITES the contract JSON
                            to <path> itself (simulates the brain's MCP
                            filesystem write) and replies with prose only
                            (no JSON block) — for the disk-salvage path
  python scripts/fake_agent.py -p x --docs-ask-docs
                         -> Doc Writer brain that returns docs ALONGSIDE a
                            clarifying question (non-interactive runs must
                            keep the docs instead of discarding them)
"""

from __future__ import annotations

import json
import sys
import time

DEFECT = {
    "defectId": "def_001",
    "title": "Login fails for unverified email",
    "severity": "high",
    "component": "backend",
    "stepsToReproduce": ["visit /login", "submit unverified email"],
    "expected": "Redirect to verification notice",
    "actual": "500 error",
    "evidence": {"suite": "AuthTest", "output": "logs/task_001.out"},
    "status": "open",
}


def _wrap(payload: str) -> None:
    print("------------------")
    print(payload)
    print("------------------")


def main() -> None:
    args = sys.argv[1:]
    if "--slow" in args:
        time.sleep(6)
    prompt = ""
    if "-p" in args:
        prompt = args[args.index("-p") + 1]
    elif not args:
        prompt = sys.stdin.read()
    if "--report" in args:
        _wrap('{"report": {"suitesRun": 2, "passed": 3, "failed": 1, "defects": []}}')
    elif "--clarify" in args:
        if "User answers: none" not in prompt:
            _wrap('{"clarifying_questions": [], "tasks": [{"title": "create users migration", "capability": "migrations", "owner": "laravel_specialist", "dependsOn": []}, {"title": "create posts migration", "capability": "migrations", "owner": "laravel_specialist", "dependsOn": ["create users migration"]}]}')
        else:
            _wrap('{"clarifying_questions": ["Which database engine?", "Table prefix?"], "tasks": []}')
    elif "--plan" in args:
        _wrap('{"clarifying_questions": [], "tasks": [{"title": "create users migration", "capability": "migrations", "owner": "laravel_specialist", "dependsOn": []}, {"title": "create posts migration", "capability": "migrations", "owner": "laravel_specialist", "dependsOn": ["create users migration"]}]}')
    elif "--plan-test" in args:
        # plan that dispatches a testing task -> triggers the bug loop
        _wrap('{"clarifying_questions": [], "tasks": [{"title": "run backend tests", "capability": "backend-testing", "owner": "tester", "dependsOn": []}]}')
    elif "--tester" in args:
        if "Fix round" in prompt:
            # verification run: round 1 still fails, later rounds pass
            if "Fix round 1" in prompt:
                _wrap('{"report": {"suitesRun": 1, "passed": 0, "failed": 1, "defects": ['
                      '{"defectId": "def_001", "severity": "high", "component": "backend", "status": "open"}]}}')
            else:
                _wrap('{"report": {"suitesRun": 2, "passed": 5, "failed": 0, "defects": []}}')
        else:
            _wrap(json.dumps({"report": {"suitesRun": 2, "passed": 3, "failed": 1, "defects": [DEFECT]}}))
    elif "--fix" in args:
        _wrap('{"fix": {"defectId": "def_001", "rootCause": "missing redirect after login", '
              '"component": "backend", "routedTo": "backend_specialist", '
              '"changes": ["added verification redirect in LoginController"], '
              '"fixStatus": "submitted"}}')
    elif "--docs" in args:
        _wrap('{"docs": [{"path": "docs/feature-login.md", "title": "Login Flow", '
              '"summary": "Login + email verification flow", '
              '"content": "# Login Flow\\n\\nRedirects unverified users."}], "questions": []}')
    elif "--docs-ask" in args:
        if "Specialist answers: none" in prompt:
            _wrap('{"docs": [], "questions": ["Which controller handles the redirect?"]}')
        else:
            _wrap('{"docs": [{"path": "docs/feature-login.md", "title": "Login Flow", '
                  '"summary": "Login + email verification flow", '
                  '"content": "# Login Flow\\n\\nHandled by LoginController."}], "questions": []}')
    elif "--docs-file" in args:
        # simulates a brain with filesystem MCP: writes the docs contract
        # to <path> itself, then replies with prose (no JSON block)
        target = args[args.index("--docs-file") + 1]
        payload = {"docs": [{"path": "docs/salvaged.md", "title": "Salvaged Doc",
                   "summary": "Written by the brain via filesystem MCP",
                   "content": "# Salvaged\\n\\nRecovered from the brain-written file."}],
                  "questions": []}
        import os
        from pathlib import Path as _Path
        _Path(target).parent.mkdir(parents=True, exist_ok=True)
        _Path(target).write_text(json.dumps(payload), encoding="utf-8")
        print(f"I wrote the docs contract to {os.path.basename(target)} myself. "
              "No JSON block needed.")
    elif "--docs-ask-docs" in args:
        # returns docs ALONGSIDE a clarifying question — one-shot runs must
        # keep the docs (best effort) instead of discarding the payload
        _wrap('{"docs": [{"path": "docs/feature-login.md", "title": "Login Flow", '
              '"summary": "Login + email verification flow", '
              '"content": "# Login Flow\\n\\nRedirects unverified users."}], '
              '"questions": ["Which controller handles the redirect?"]}')
    else:
        print(f"ECHO: {prompt[:200]}")


if __name__ == "__main__":
    main()
