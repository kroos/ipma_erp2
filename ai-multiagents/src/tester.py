"""Tester role — hybrid: Python loop + a user-chosen agent as the test brain.

The Tester NEVER fixes bugs (docs/02 §5): it runs tests and documents
defects only. Test execution is delegated to the brain CLI agent; the loop
parses and validates the structured report contract.

Report contract (docs/02 §5, docs/06 Phase 6 §2):
    {"report": {"suitesRun": N, "passed": N, "failed": N,
                "defects": [ {defect schema} ]}}

Regression gate: verify_fix() re-runs the affected suite, then the full
suite, before a defect may be marked `verified`.
"""

from __future__ import annotations

import json

from .agent_adapter import JSON_RETRY_HINT, extract_json_block
from .memory import MemoryStore
from .message_bus import make_message
from .test_detector import tester_environment_note

VALID_COMPONENTS = {"backend", "frontend", "db", "config", "architecture"}


class ReportError(Exception):
    """Raised when the brain returns no parseable/valid test report."""


class Tester:
    def __init__(self, orch, brain: str | None = None):
        self.orch = orch
        self.adapters = orch.adapters
        self.roles = orch.roles
        self.project_root = orch.project_root
        self.brain = brain or self._default_brain()
        self.memory = MemoryStore("tester")

    # ---- brain selection -------------------------------------------------

    def _default_brain(self) -> str:
        role = self.roles.get("tester", {})
        for agent_id in role.get("default_agents", []):
            if agent_id in self.adapters:
                return agent_id
        if self.adapters:
            return sorted(self.adapters)[0]
        raise ReportError("no agents available to act as Tester")

    # ---- brain contract --------------------------------------------------

    def _report_prompt(self, scope: str, target: str, extra: str = "") -> str:
        note = tester_environment_note(self.project_root)
        return (
            "You are the Tester of a multi-agent Laravel development team.\n"
            "You run tests and DOCUMENT defects. You NEVER fix bugs.\n"
            f"{note}\n"
            f"Scope: {scope}\n"
            f"Target: {target or '(whole project)'}\n"
            f"Context: {extra or '(none)'}\n\n"
            "Run the tests now. Then reply with ONLY this delimited JSON "
            "report, nothing before or after:\n"
            "------------------\n"
            '{"report": {"suitesRun": 2, "passed": 18, "failed": 1, '
            '"defects": [{"defectId": "def_001", "title": "...", '
            '"severity": "high", "component": "backend", '
            '"stepsToReproduce": ["..."], "expected": "...", '
            '"actual": "...", "evidence": {"suite": "AuthTest", '
            '"output": "logs/..."}, "status": "open"}]}}\n'
            "------------------"
        )

    def _normalize_defect(self, raw) -> dict:
        if not isinstance(raw, dict):
            raise ReportError(f"invalid defect entry: {raw!r}")
        component = str(raw.get("component") or "").strip().lower()
        if component not in VALID_COMPONENTS:
            raise ReportError(
                f"defect {raw.get('defectId', '?')!r} has invalid "
                f"component {component!r} (expected one of "
                f"{sorted(VALID_COMPONENTS)})"
            )
        return {
            "defectId": str(raw.get("defectId") or "def_unknown"),
            "title": str(raw.get("title") or "(untitled defect)"),
            "severity": str(raw.get("severity") or "medium"),
            "component": component,
            "stepsToReproduce": [
                str(s) for s in (raw.get("stepsToReproduce") or [])
            ],
            "expected": str(raw.get("expected") or ""),
            "actual": str(raw.get("actual") or ""),
            "evidence": (
                raw["evidence"]
                if isinstance(raw.get("evidence"), dict)
                else {}
            ),
            "status": str(raw.get("status") or "open"),
        }

    def _validate_report(self, parsed) -> dict:
        if not isinstance(parsed, dict) or not isinstance(
            parsed.get("report"), dict
        ):
            raise ReportError("report JSON missing 'report' object")
        report = parsed["report"]
        try:
            suites = int(report.get("suitesRun") or 0)
            passed = int(report.get("passed") or 0)
            failed = int(report.get("failed") or 0)
        except (TypeError, ValueError):
            raise ReportError(
                f"report counts are not numeric: "
                f"{report.get('suitesRun')!r}/{report.get('passed')!r}/"
                f"{report.get('failed')!r}"
            )
        defects = report.get("defects") or []
        if not isinstance(defects, list):
            raise ReportError("report 'defects' is not a list")
        return {
            "suitesRun": suites,
            "passed": passed,
            "failed": failed,
            "defects": [self._normalize_defect(d) for d in defects],
        }

    # ---- execution -------------------------------------------------------

    def _ask_brain(self, scope: str, target: str, extra: str) -> dict:
        """Ask the brain for a report; retry once when the JSON does not
        parse (real LLMs occasionally emit malformed JSON, M7 E2E)."""
        for attempt in (1, 2):
            prompt = self._report_prompt(scope, target, extra)
            if attempt > 1:
                prompt += JSON_RETRY_HINT
            message = make_message(
                "task",
                from_="tester",
                to=[self.brain],
                task_id=f"test_{scope.replace(' ', '_')}",
                parts=[{"type": "text", "text": prompt}],
            )
            result = self.adapters[self.brain].run_task(message)
            text = result.get("parts", [{}])[0].get("text", "")
            if result.get("metadata", {}).get("state") == "failed":
                raise ReportError(f"tester brain failed: {text[:300]}")
            parsed = extract_json_block(text)
            if parsed is not None:
                return self._validate_report(parsed)
        raise ReportError("tester brain returned no parseable report JSON")

    def run_tests(self, scope: str = "full", target: str | None = None,
                  extra: str = "") -> dict:
        """Run a test scope and return the validated report."""
        report = self._ask_brain(scope, target or "", extra)
        self.memory.remember("report", json.dumps(report, indent=2))
        for defect in report["defects"]:
            self.memory.remember("defect", json.dumps(defect, indent=2))
        print(
            f"[tester] {scope}: {report['suitesRun']} suite(s), "
            f"{report['passed']} passed, {report['failed']} failed, "
            f"{len(report['defects'])} defect(s) documented"
        )
        return report

    @staticmethod
    def _has_failures(report: dict) -> bool:
        return report["failed"] > 0 or bool(report["defects"])

    def verify_fix(self, defect: dict, round_no: int = 1,
                   fix_note: dict | None = None) -> dict:
        """Regression gate (docs/02 §5): affected suite, then full suite."""
        context = f"Fix round {round_no}. Fix note: {json.dumps(fix_note or {})}"
        target = defect.get("defectId")
        affected = self.run_tests(scope="affected suite", target=target, extra=context)
        if self._has_failures(affected):
            return {"verified": False, "stage": "affected", "report": affected}
        full = self.run_tests(scope="full suite", target=target, extra=context)
        if self._has_failures(full):
            return {"verified": False, "stage": "full", "report": full}
        return {"verified": True, "stage": "full", "report": full}
