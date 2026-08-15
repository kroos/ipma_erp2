"""Bug Fixer role — hybrid: Python loop + a user-chosen agent as fix brain.

Flow (docs/02 §5): reproduce -> root-cause route -> implement fix ->
request Tester verification. On fix submit the regression gate
auto-triggers: Tester re-runs the affected suite, then the full suite,
before the defect is marked `verified`. Loop until Tester passes or
MAX_FIX_ROUNDS is exhausted.

Fix-note contract (emitted by the brain as a delimited JSON block):
    {"fix": {"defectId": "...", "rootCause": "...",
             "component": "backend|frontend|db|config|architecture",
             "routedTo": "<role id>", "changes": ["..."],
             "fixStatus": "submitted"}}

The Tester does not fix bugs; the Bug Fixer does not decide verification.
"""

from __future__ import annotations

import json

from .agent_adapter import JSON_RETRY_HINT, extract_json_block
from .memory import MemoryStore
from .message_bus import make_message

MAX_FIX_ROUNDS = 3

# component -> owner role id (docs/02 §5 routing table)
COMPONENT_OWNERS = {
    "backend": "backend_specialist",
    "frontend": "frontend_specialist",
    "db": "db_admin",
    "config": "backend_specialist",      # php/config lives under backend
    "architecture": "app_designer",
}


class FixError(Exception):
    """Raised when the brain returns no parseable/valid fix note."""


class BugFixer:
    def __init__(self, orch, brain: str | None = None):
        self.orch = orch
        self.adapters = orch.adapters
        self.roles = orch.roles
        self.brain = brain or self._default_brain()
        self.memory = MemoryStore("bug_fixer")

    def _default_brain(self) -> str:
        role = self.roles.get("bug_fixer", {})
        for agent_id in role.get("default_agents", []):
            if agent_id in self.adapters:
                return agent_id
        if self.adapters:
            return sorted(self.adapters)[0]
        raise FixError("no agents available to act as Bug Fixer")

    # ---- brain contract --------------------------------------------------

    def _fix_prompt(self, defect: dict, round_no: int,
                    verdict: dict | None) -> str:
        owners = ", ".join(
            f"{k} -> {v}" for k, v in COMPONENT_OWNERS.items()
        )
        regression = ""
        if verdict and not verdict.get("verified"):
            stage = verdict.get("stage", "?")
            report = verdict.get("report", {})
            regression = (
                f"\nPrevious verification FAILED at stage '{stage}' "
                f"({report.get('failed', 0)} failed). The fix did not "
                "resolve the defect - reproduce, re-root-cause, and fix "
                "again.\n"
            )
        return (
            "You are the Bug Fixer of a multi-agent Laravel development "
            "team.\n"
            "Reproduce the defect, identify the root cause, route it to "
            "the owning role, and implement the fix. Avoid unnecessary "
            "changes and regressions.\n"
            f"Component routing: {owners}.\n"
            f"Defect (JSON): {json.dumps(defect, indent=2)}\n"
            f"Fix round: {round_no}"
            f"{regression}"
            "\nImplement the fix, then reply with ONLY this delimited JSON "
            "block, nothing before or after:\n"
            "------------------\n"
            '{"fix": {"defectId": "<same id>", "rootCause": "...", '
            '"component": "backend", "routedTo": "backend_specialist", '
            '"changes": ["..."], "fixStatus": "submitted"}}\n'
            "------------------"
        )

    def _validate_fix_note(self, parsed) -> dict:
        if not isinstance(parsed, dict) or not isinstance(
            parsed.get("fix"), dict
        ):
            raise FixError("fix note JSON missing 'fix' object")
        fix = parsed["fix"]
        component = str(fix.get("component") or "").strip().lower()
        if component not in COMPONENT_OWNERS:
            raise FixError(
                f"fix note has invalid component {component!r} "
                f"(expected one of {sorted(COMPONENT_OWNERS)})"
            )
        return {
            "defectId": str(fix.get("defectId") or ""),
            "rootCause": str(fix.get("rootCause") or ""),
            "component": component,
            "routedTo": str(
                fix.get("routedTo") or COMPONENT_OWNERS[component]
            ),
            "changes": [str(c) for c in (fix.get("changes") or [])],
            "fixStatus": str(fix.get("fixStatus") or "submitted"),
        }

    # ---- execution -------------------------------------------------------

    def _request_fix(self, defect: dict, round_no: int,
                     verdict: dict | None) -> dict:
        """Ask the brain for a fix note; retry once when the JSON does not
        parse (real LLMs occasionally emit malformed JSON, M7 E2E)."""
        for attempt in (1, 2):
            prompt = self._fix_prompt(defect, round_no, verdict)
            if attempt > 1:
                prompt += JSON_RETRY_HINT
            message = make_message(
                "task",
                from_="bug_fixer",
                to=[self.brain],
                task_id=f"fix_{round_no:03d}",
                parts=[{"type": "text", "text": prompt}],
            )
            result = self.adapters[self.brain].run_task(message)
            text = result.get("parts", [{}])[0].get("text", "")
            if result.get("metadata", {}).get("state") == "failed":
                raise FixError(f"bug fixer brain failed: {text[:300]}")
            parsed = extract_json_block(text)
            if parsed is not None:
                return self._validate_fix_note(parsed)
        raise FixError("bug fixer brain returned no parseable fix note")

    def fix(self, defect: dict, tester) -> dict:
        """Run the bug loop against a defect. `tester` is a Tester instance."""
        self.memory.remember("defect", json.dumps(defect, indent=2))
        last_verdict: dict | None = None
        last_note: dict = {}
        for round_no in range(1, MAX_FIX_ROUNDS + 1):
            note = self._request_fix(defect, round_no, last_verdict)
            last_note = note
            self.memory.remember("fix_note", json.dumps(note, indent=2))
            print(
                f"[bug_fixer] round {round_no}: fix submitted "
                f"(routed to {note['routedTo'] or note['component'] or '?'})"
            )
            verdict = tester.verify_fix(defect, round_no, note)
            last_verdict = verdict
            if verdict["verified"]:
                defect["status"] = "verified"
                self.memory.save_decision(
                    f"defect {defect.get('defectId')} verified",
                    f"after {round_no} round(s); "
                    f"root cause: {note['rootCause'] or '(not stated)'}",
                )
                return {
                    "defectId": defect.get("defectId"),
                    "status": "verified",
                    "rounds": round_no,
                    "fix": note,
                    "verdict": verdict,
                }
            print(
                f"[bug_fixer] round {round_no}: verification FAILED at "
                f"stage '{verdict.get('stage')}' - re-fixing"
            )
        defect["status"] = "failed"
        return {
            "defectId": defect.get("defectId"),
            "status": "failed",
            "rounds": MAX_FIX_ROUNDS,
            "fix": last_note,
            "verdict": last_verdict,
        }
