"""Dispatcher — global task list, lifecycle, dependency tracking.

Owns the A2A Task lifecycle (docs/04 §3.3-§3.4) at the plan level:
  pending -> in_progress -> done | failed
  (plus `input-required`, `canceled`).

Rules enforced here:
  - no-double-dispatch: an agent whose card is `working` is never handed a
    new task (the bus also enforces this centrally)
  - every task has an owner
  - dependency tracking: a task is only dispatched once its dependsOn
    tasks are `done`
  - reassign on failure: pick the next capable idle owner
"""

from __future__ import annotations

import uuid

from .message_bus import RoutingError, make_message

TASK_STATES = ("pending", "in_progress", "done", "failed", "input-required", "canceled")


class Task:
    def __init__(
        self,
        task_id: str,
        title: str,
        capability: str,
        owner: str | None = None,
        depends_on: tuple[str, ...] = (),
        priority: str = "normal",
        metadata: dict | None = None,
    ):
        self.task_id = task_id
        self.title = title
        self.capability = capability
        self.owner = owner          # may be resolved at dispatch by capability
        self.depends_on = depends_on
        self.priority = priority
        self.state = "pending"
        self.result = None          # result envelope when done
        self.error = None
        self.metadata = metadata or {}
        self.artifacts = []

    def to_dict(self) -> dict:
        return {
            "taskId": self.task_id,
            "title": self.title,
            "capability": self.capability,
            "owner": self.owner,
            "dependsOn": list(self.depends_on),
            "priority": self.priority,
            "state": self.state,
            "error": self.error,
        }


class Dispatcher:
    def __init__(self, bus, adapters: dict[str, object]):
        self.bus = bus
        self.adapters = adapters  # agent_id -> AgentAdapter
        self.tasks: dict[str, Task] = {}
        self._seq = 0
        # task_id -> set of agents that already failed it (reassignment
        # bookkeeping). Kept dispatcher-private so it never leaks into the
        # task `data` part sent to agents (M7 finding).
        self._task_failures: dict[str, set[str]] = {}

    # ---- task list ------------------------------------------------------

    def create_task(
        self,
        title: str,
        capability: str,
        owner: str | None = None,
        depends_on: tuple[str, ...] = (),
        priority: str = "normal",
        metadata: dict | None = None,
    ) -> Task:
        self._seq += 1
        task = Task(
            task_id=f"task_{self._seq:03d}",
            title=title,
            capability=capability,
            owner=owner,
            depends_on=depends_on,
            priority=priority,
            metadata=metadata,
        )
        self.tasks[task.task_id] = task
        return task

    def get(self, task_id: str) -> Task:
        return self.tasks[task_id]

    def _deps_done(self, task: Task) -> bool:
        for dep in task.depends_on:
            dep_task = self.tasks.get(dep)
            if dep_task is None or dep_task.state != "done":
                return False
        return True

    def _resolve_owner(self, task: Task) -> str | None:
        if task.owner and self.bus.get_card(task.owner).status == "idle":
            return task.owner
        # explicit owner busy -> try capability routing for an idle agent
        return self.bus.capability_route(task.capability)

    def _next_owner(self, task: Task, exclude: set[str]) -> str | None:
        """Idle capable agent not yet failed for this task (reassignment).

        Excludes every agent that already failed the task, not just the last
        one, so the task walks down the capability list instead of
        ping-ponging between two failing agents (M7 finding).
        """
        for card in self.bus.list_cards():
            if (
                card.status == "idle"
                and card.agent not in exclude
                and task.capability in card.capabilities
            ):
                return card.agent
        return None

    # ---- execution ------------------------------------------------------

    def dispatch(self, task: Task) -> bool:
        """Dispatch one ready task. Returns True when it ran."""
        if task.state != "pending":
            return False
        # a failed/canceled dependency blocks this task forever: fail it
        # explicitly so dependents don't hang as `pending` (M7 E2E finding)
        for dep_id in task.depends_on:
            dep = self.tasks.get(dep_id)
            if dep is not None and dep.state in ("failed", "canceled"):
                task.state = "failed"
                task.error = f"blocked by failed dependency {dep_id}"
                return False
        if not self._deps_done(task):
            return False
        owner = self._resolve_owner(task)
        if owner is None:
            task.error = (
                f"no idle agent with capability {task.capability!r}"
            )
            return False
        task.owner = owner

        message = make_message(
            "task",
            from_="dispatcher",
            to=[owner],
            task_id=task.task_id,
            parts=[
                {"type": "text", "text": task.title},
                {"type": "data", "data": task.metadata},
            ],
        )
        try:
            task.state = "in_progress"
            result = self.adapters[owner].run_task(message)
        except RoutingError as exc:  # no-double-dispatch from the bus
            task.state = "pending"
            task.error = str(exc)
            return False

        task.result = result
        state = result.get("metadata", {}).get("state", "failed")
        if state == "completed":
            task.state = "done"
            task.artifacts = [
                p for p in result.get("parts", []) if p.get("type") == "file"
            ]
        else:
            task.state = "failed"
            task.error = result.get("parts", [{}])[0].get("text", "")[:500]
            # remember every agent that failed this task so reassignment
            # walks forward, never back to a known-failing agent
            self._task_failures.setdefault(task.task_id, set()).add(owner)
            new_owner = self._next_owner(task, self._task_failures[task.task_id])
            if new_owner:
                task.owner = new_owner
                task.state = "pending"
        return True

    def tick(self) -> int:
        """Dispatch every ready pending task. Returns count dispatched."""
        ran = 0
        for task in list(self.tasks.values()):
            if task.state == "pending" and self.dispatch(task):
                ran += 1
        return ran

    def summary(self) -> list[dict]:
        return [t.to_dict() for t in self.tasks.values()]
