"""Coordinator role — hybrid: Python control loop + a user-chosen agent as brain.

The loop owns dispatch, monitoring, and reporting (docs/06 Phase 5). The brain
agent is invoked ONE-SHOT only for judgment calls and must return the task-plan
JSON contract; it never issues raw dispatch commands.

Plan contract:
    {"clarifying_questions": ["..."],
     "tasks": [{"title": "...", "capability": "...",
                "dependsOn": ["<earlier task title>"], "owner": "<role id>"}]}

If clarifying_questions is non-empty, the loop asks the user and re-invokes the
brain with the answers.

Bug-loop wiring (docs/06 Phase 6 §3): after a plan completes, any task whose
`capability` is a Tester capability (config/roles.yaml) auto-triggers the
Tester -> Bug Fixer loop; a summary of defects + fixes is reported to the user.

Doc-writer wiring (docs/06 Phase 6 §4): completed implementation work (any
done task whose capability is not a test-run) is then auto-documented by the
Doc Writer; generated doc paths are reported to the user.
"""

from __future__ import annotations

import json
import sys

from .agent_adapter import JSON_RETRY_HINT, extract_json_block
from .bug_fixer import BugFixer, FixError
from .doc_writer import DocError, DocWriter
from .message_bus import make_message
from .tester import ReportError, Tester

MAX_CLARIFY_ROUNDS = 3
MAX_DISPATCH_ROUNDS = 50

# Preferred coordinator brain. freebuff is the spec's preferred fallback, but
# it is interactive-only (`headless: false`) and therefore never wired, so
# select_coordinator falls through to the first alphabetically-sorted wired
# agent (see select_coordinator).
DEFAULT_COORDINATOR = "freebuff"

# Capabilities that mean "run the tests" (as opposed to writing test plans,
# documenting defects, etc.). Only these auto-trigger the bug loop.
TEST_RUN_CAPS = frozenset({
    "backend-testing", "frontend-testing", "db-testing", "api-testing",
    "auth-testing", "validation-testing", "edge-cases", "error-handling",
    "regression", "integration", "responsive-testing", "browser-testing",
})


class PlanError(Exception):
    """Raised when the brain returns no parseable/valid plan."""


class Coordinator:
    def __init__(self, orch, brain: str | None = None,
                 role_brains: dict[str, str] | None = None,
                 ask_roles: bool = False,
                 interactive: bool | None = None):
        self.orch = orch
        self.bus = orch.bus
        self.dispatcher = orch.dispatcher
        self.adapters = orch.adapters
        self.roles = orch.roles
        self.state = orch.state
        self.brain = brain or select_coordinator(orch)
        # role_id -> agent override (user pinning / deterministic tests)
        self.role_brains = role_brains or {}
        # when True, prompt the user to pick a brain for roles that are not
        # pinned and have no last-session choice (REPL mode; one-shot skips)
        self.ask_roles = ask_roles
        # can the loop prompt the user for clarifying answers? One-shot runs
        # (summon --instruction / coordinator_manual) pass False — a
        # backgrounded process must never block on input(). Auto-detected
        # from stdin when not given (isatty() is unreliable under nohup,
        # so callers of one-shot entry points MUST pass it explicitly).
        if interactive is None:
            interactive = sys.stdin.isatty() if hasattr(sys.stdin, "isatty") else True
        self.interactive = interactive
        self._bug_loop_results: dict | None = None
        self._docwriter_results: dict | None = None

    # ---- brain contract -------------------------------------------------

    def _capability_vocabulary(self) -> list[str]:
        return sorted(
            {c for r in self.roles.values() for c in r.get("capabilities", [])}
        )

    def _brain_prompt(self, instruction: str, answers: list[str]) -> str:
        vocab = self._capability_vocabulary()
        role_ids = list(self.roles.keys())
        return (
            "You are the Coordinator of a multi-agent Laravel development team.\n"
            "You decide WHAT tasks to create; the loop dispatches them.\n"
            f"Available capabilities: {', '.join(vocab) or '(none)'}\n"
            f"Available role ids: {', '.join(role_ids) or '(none)'}\n"
            f"Project root: {getattr(self.orch, 'project_root', '') or '<laravel root>'}\n\n"
            f"Instruction: {instruction}\n"
            f"User answers: {answers or 'none'}\n\n"
            "Decompose the instruction into tasks. Reply with ONLY this JSON, "
            "nothing before or after:\n"
            '{"clarifying_questions": ["..."], '
            '"tasks": [{"title": "...", "capability": "<capability from the list>", '
            '"dependsOn": ["<title of an earlier task>"], "owner": "<role id>"}]}'
        )

    def _ask_brain(self, instruction: str, answers: list[str]) -> dict:
        """Ask the brain for a plan; retry once when the JSON does not parse
        (real LLMs occasionally emit malformed JSON, M7 E2E)."""
        for attempt in (1, 2):
            prompt = self._brain_prompt(instruction, answers)
            if attempt > 1:
                prompt += JSON_RETRY_HINT
            message = make_message(
                "task",
                from_="coordinator",
                to=[self.brain],
                task_id="brain_plan",
                parts=[
                    {"type": "text", "text": prompt}
                ],
            )
            result = self.adapters[self.brain].run_task(message)
            text = result.get("parts", [{}])[0].get("text", "")
            if result.get("metadata", {}).get("state") == "failed":
                raise PlanError(f"brain {self.brain!r} failed: {text[:300]}")
            plan = extract_json_block(text)
            if plan is not None:
                return self._validate_plan(plan)
        raise PlanError(
            f"brain {self.brain!r} returned no parseable plan JSON"
        )

    def _validate_plan(self, plan: dict) -> dict:
        if not isinstance(plan, dict):
            raise PlanError("plan is not an object")
        questions = plan.get("clarifying_questions") or []
        tasks = plan.get("tasks")
        if not isinstance(tasks, list):
            raise PlanError("plan has no tasks list")
        vocab = self._capability_vocabulary()
        cleaned = []
        for spec in tasks:
            if not isinstance(spec, dict) or not spec.get("title"):
                raise PlanError(f"invalid task spec: {spec!r}")
            cap = str(spec.get("capability") or "").strip()
            # reject out-of-vocabulary capabilities at plan time (M7 E2E
            # finding: the brain once emitted "execution-plans", which is
            # not a TEST_RUN_CAP, so the bug loop silently skipped it)
            if vocab and cap not in vocab:
                raise PlanError(
                    f"task {spec['title']!r} uses capability {cap!r} which is "
                    f"not in the vocabulary; valid capabilities: "
                    f"{', '.join(vocab)}"
                )
            cleaned.append(
                {
                    "title": str(spec["title"]),
                    "capability": cap,
                    "dependsOn": [
                        str(d) for d in (spec.get("dependsOn") or [])
                    ],
                    "owner": spec.get("owner"),
                }
            )
        return {"clarifying_questions": list(questions), "tasks": cleaned}

    # ---- execution ------------------------------------------------------

    def run_one_shot(self, instruction: str) -> None:
        """Decompose + clarify + dispatch + report for a single instruction."""
        answers: list[str] = []
        # non-interactive (one-shot / backgrounded): never block on input();
        # tell the brain to proceed with assumptions instead
        for _ in range(MAX_CLARIFY_ROUNDS):
            plan = self._ask_brain(instruction, answers)
            questions = plan["clarifying_questions"]
            if not questions:
                break
            if not self.interactive:
                print("[coordinator] no interactive user; asking the brain to proceed")
                answers = ["(user unavailable - proceed with reasonable assumptions)"]
                continue
            print("[coordinator] Clarifying questions:")
            answers = []
            for q in questions:
                try:
                    answers.append(input(f"  Q: {q}\n  > "))
                except EOFError:
                    self.interactive = False
                    answers = ["(user unavailable - proceed with reasonable assumptions)"]
        else:
            raise PlanError("brain kept asking; giving up after "
                            f"{MAX_CLARIFY_ROUNDS} rounds")
        self._execute_plan(plan)

    def _role_to_agent(self, role_id) -> str | None:
        """Map a plan `owner` role to a wired agent.

        Order: explicit pin -> last-session choice (state.json) -> role
        defaults. (The interactive prompt is only used by
        `_select_role_agent` for the pinned roles; plan owners resolve
        without prompting.)
        """
        if not role_id:
            return None
        pinned = self.role_brains.get(role_id)
        if pinned:
            if pinned in self.adapters:
                return pinned
            print(f"[coordinator] pinned brain {pinned!r} for role "
                  f"{role_id!r} is not wired; falling back")
        saved = self.state.get_last_role_agent(role_id)
        if saved and saved in self.adapters:
            return saved
        return self._role_default_agent(role_id)

    def _role_default_agent(self, role_id) -> str | None:
        """First wired default agent for a role (role defaults)."""
        role = self.roles.get(role_id)
        if not role:
            return None
        for agent_id in role.get("default_agents", []):
            if agent_id in self.adapters:
                return agent_id
        return None

    def _select_role_agent(self, role_id: str) -> str | None:
        """Resolve the brain for a pinned role (tester/bug_fixer/doc_writer).

        Selection order (mirrors select_coordinator):
          1. explicit pin from role_brains
          2. interactive user prompt (only when self.ask_roles)
          3. last-session choice from state.json
          4. role defaults -> first wired default agent
        """
        pinned = self.role_brains.get(role_id)
        if pinned:
            if pinned in self.adapters:
                self.state.set_last_role_agent(role_id, pinned)
                return pinned
            print(f"[coordinator] pinned brain {pinned!r} for role "
                  f"{role_id!r} is not wired; falling back")
        if self.ask_roles:
            chosen = _prompt_role_agent(self.adapters, self.roles, role_id)
            if chosen:
                self.state.set_last_role_agent(role_id, chosen)
                return chosen
        saved = self.state.get_last_role_agent(role_id)
        if saved and saved in self.adapters:
            return saved
        return self._role_default_agent(role_id)

    def _execute_plan(self, plan: dict) -> None:
        # fresh per-instruction results (no stale state across REPL rounds)
        self._bug_loop_results = None
        self._docwriter_results = None
        title_to_id: dict[str, str] = {}
        for spec in plan["tasks"]:
            depends = tuple(
                title_to_id[t] for t in spec["dependsOn"] if t in title_to_id
            )
            owner = self._role_to_agent(spec.get("owner"))
            task = self.dispatcher.create_task(
                spec["title"],
                spec["capability"],
                owner=owner,
                depends_on=depends,
            )
            title_to_id[spec["title"]] = task.task_id
            print(
                f"[coordinator] task {task.task_id:<10} "
                f"capability={spec['capability']:<20} owner={owner or '(auto)'} "
                f"'{spec['title']}'"
            )

        rounds = 0
        while not self._all_terminal() and rounds < MAX_DISPATCH_ROUNDS:
            self.dispatcher.tick()
            rounds += 1
        self._report()
        self._run_bug_loop_if_needed()
        self._run_docwriter_if_needed()

    def _all_terminal(self) -> bool:
        return all(
            t.state in ("done", "failed", "canceled")
            for t in self.dispatcher.tasks.values()
        )

    # ---- bug-loop wiring (docs/06 Phase 6 §3) ---------------------------

    def _run_bug_loop_if_needed(self) -> None:
        """Auto-run Tester -> Bug Fixer for completed test-run tasks."""
        test_caps = TEST_RUN_CAPS & set(
            self.roles.get("tester", {}).get("capabilities", [])
        )
        if not test_caps:
            return
        test_tasks = [
            t for t in self.dispatcher.tasks.values()
            if t.state == "done" and t.capability in test_caps
        ]
        if not test_tasks:
            return
        tester_brain = self._select_role_agent("tester")
        fixer_brain = self._select_role_agent("bug_fixer")
        if not tester_brain or not fixer_brain:
            print("[coordinator] bug loop skipped: no Tester/Bug Fixer agent wired")
            return

        tester = Tester(self.orch, brain=tester_brain)
        fixer = BugFixer(self.orch, brain=fixer_brain)
        target = "; ".join(t.title for t in test_tasks)
        print(f"\n[coordinator] bug loop: {len(test_tasks)} testing task(s) done")
        try:
            report = tester.run_tests(scope="full suite", target=target)
        except ReportError as exc:
            print(f"[coordinator] bug loop aborted: {exc}")
            return
        fixes = []
        for defect in report.get("defects", []):
            try:
                fixes.append(fixer.fix(defect, tester))
            except FixError as exc:
                print(f"[coordinator] fix failed for {defect.get('defectId')}: {exc}")
                fixes.append({
                    "defectId": defect.get("defectId"),
                    "status": "failed",
                    "rounds": 0,
                    "error": str(exc),
                })
        self._bug_loop_results = {"report": report, "fixes": fixes}
        self._report_bug_loop()

    def _report_bug_loop(self) -> None:
        """Summarize defects + fixes for the user (Coordinator is the gateway)."""
        data = self._bug_loop_results
        if not data:
            return
        report, fixes = data["report"], data["fixes"]
        print("\n[coordinator] Bug loop summary")
        print(
            f"  tests: {report['suitesRun']} suite(s), "
            f"{report['passed']} passed, {report['failed']} failed, "
            f"{len(report['defects'])} defect(s)"
        )
        for outcome in fixes:
            if outcome["status"] == "verified":
                detail = f"verified after {outcome['rounds']} round(s)"
            elif outcome.get("error"):
                detail = f"fix brain error: {outcome['error'][:80]}"
            else:
                detail = f"NOT verified after {outcome['rounds']} round(s)"
            print(f"  {outcome.get('defectId') or '?'}: {detail}")

    # ---- doc-writer wiring (docs/06 Phase 6 §4) -------------------------

    def _run_docwriter_if_needed(self) -> None:
        """Auto-generate docs for completed implementation work."""
        impl_tasks = [
            t for t in self.dispatcher.tasks.values()
            if t.state == "done" and t.capability not in TEST_RUN_CAPS
        ]
        if not impl_tasks:
            return
        doc_brain = self._select_role_agent("doc_writer")
        if not doc_brain:
            print("[coordinator] doc writer skipped: no Doc Writer agent wired")
            return
        scope = "; ".join(
            f"{t.capability or 'task'}: {t.title}" for t in impl_tasks
        )
        bug = self._bug_loop_results
        if bug:
            fixes = bug.get("fixes", [])
            verified = sum(1 for f in fixes if f.get("status") == "verified")
            scope += f" (bug loop: {verified}/{len(fixes)} defects fixed)"
        writer = DocWriter(self.orch, brain=doc_brain,
                           interactive=self.interactive)
        print(
            f"\n[coordinator] doc writer: documenting "
            f"{len(impl_tasks)} task(s)"
        )
        try:
            payload = writer.document(scope)
        except DocError as exc:
            print(f"[coordinator] doc writer aborted: {exc}")
            return
        self._docwriter_results = payload
        self._report_docwriter()

    def _report_docwriter(self) -> None:
        """Summarize generated docs for the user (Coordinator is the gateway)."""
        payload = self._docwriter_results
        if not payload:
            return
        docs = payload.get("docs", [])
        print("\n[coordinator] Doc Writer summary")
        for doc in docs:
            print(f"  {doc['path']}: {doc['title']} - {doc['summary']}")

    def _report(self) -> None:
        print("\n[coordinator] Task report")
        for task in self.dispatcher.tasks.values():
            if task.state == "done":
                detail = task.artifacts[0].get("name", "") if task.artifacts else ""
            else:
                detail = task.error or ""
            print(
                f"  {task.task_id:<10} {task.title[:46]:<46} "
                f"{task.state:<7} {task.owner or '-':<12} {detail}"
            )

    # ---- interactive ----------------------------------------------------

    def repl(self) -> None:
        print("[coordinator] REPL ready. Type an instruction, or 'quit'.")
        while True:
            try:
                line = input("coordinator> ").strip()
            except (EOFError, KeyboardInterrupt):
                print()
                break
            if not line:
                continue
            if line.lower() in ("quit", "exit", "q"):
                break
            try:
                self.run_one_shot(line)
            except PlanError as exc:
                print(f"[coordinator] {exc}")


def _prompt_role_agent(adapters: dict, roles: dict, role_id: str) -> str | None:
    """Ask the user which wired agent should play `role_id` (REPL mode).

    Candidates: the role's default agents that are wired, plus any other
    wired agent (any role can use any agent per spec). Empty input selects
    the first default. Returns None when no candidates exist.
    """
    defaults = [
        a for a in roles.get(role_id, {}).get("default_agents", [])
        if a in adapters
    ]
    others = sorted(a for a in adapters if a not in defaults)
    candidates = defaults + others
    if not candidates:
        return None
    print(f"\n[coordinator] Which agent should play the {role_id} role?")
    for i, agent_id in enumerate(candidates, 1):
        tag = " (default)" if i == 1 else ""
        print(f"  {i}) {agent_id}{tag}")
    while True:
        try:
            choice = input(f"  [1-{len(candidates)}, Enter={candidates[0]}] > ").strip()
        except (EOFError, KeyboardInterrupt):
            print()
            return None
        if not choice:
            return candidates[0]
        if choice.isdigit() and 1 <= int(choice) <= len(candidates):
            return candidates[int(choice) - 1]
        if choice in adapters:
            return choice
        print(f"  {choice!r} is not wired; choose a number or agent id:")


def select_coordinator(orch, explicit: str | None = None) -> str:
    """Selection order: --coordinator flag -> prompt -> state.json -> fallback.

    Fallback: DEFAULT_COORDINATOR if wired, else the first wired (headless)
    agent in sorted order. Interactive-only agents are never wired, so a
    TUI-only brain like freebuff is never auto-selected.

    (docs/03 §selection; docs/06 Phase 5 §3)
    """
    if explicit:
        if explicit not in orch.adapters:
            raise PlanError(
                f"coordinator agent {explicit!r} is not available on this "
                f"machine (wired: {', '.join(sorted(orch.adapters)) or 'none'})"
            )
        orch.state.set_last_coordinator(explicit)
        return explicit

    saved = orch.state.get_last_coordinator()
    if saved and saved in orch.adapters:
        return saved

    if DEFAULT_COORDINATOR in orch.adapters:
        orch.state.set_last_coordinator(DEFAULT_COORDINATOR)
        return DEFAULT_COORDINATOR

    if orch.adapters:
        first = sorted(orch.adapters)[0]
        orch.state.set_last_coordinator(first)
        return first

    raise PlanError("no agents available to act as coordinator")
