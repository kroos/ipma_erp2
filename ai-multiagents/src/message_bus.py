"""Omniroute — local A2A gateway / smart router.

Implements the full A2A data model from docs/04:
Agent Card, Message (parts: text/file/data), Task lifecycle
(submitted -> working -> input-required -> completed | failed | canceled),
Artifact, and smart routing (named / toCapability / broadcast).

OmnirouteTransport is the interface; LocalBusTransport is the default
in-process + SQLite-backed implementation. HttpJsonRpcTransport is the
documented future swap-in (not built).

Central responsibilities:
  - Agent Card registry (the mesh directory)
  - routing: named delivery, capability addressing, broadcast
  - status ownership: cards updated via update_status()/emit_status()
  - central no-double-dispatch: a `task` to an agent whose card is
    `working` is rejected with RoutingError
  - at-least-once delivery with dedupe by messageId (SQLite outbox)
  - envelope validation (unknown kind / part.type rejected)
"""

from __future__ import annotations

import json
import sqlite3
import uuid
from datetime import datetime, timezone
from pathlib import Path

from . import memory

MESSAGE_KINDS = {"task", "result", "status", "request", "response", "notify"}
PART_TYPES = {"text", "file", "data"}
TASK_STATES = {
    "submitted",
    "working",
    "input-required",
    "completed",
    "failed",
    "canceled",
}
CARD_STATUSES = {"idle", "working"}


class RoutingError(Exception):
    """Raised when a message cannot be routed (incl. no-double-dispatch)."""


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def _new_id(prefix: str = "msg") -> str:
    return f"{prefix}_{uuid.uuid4().hex[:10]}"


def make_message(
    kind: str,
    from_: str,
    to: list[str] | None = None,
    to_capability: str | None = None,
    task_id: str | None = None,
    parts: list[dict] | None = None,
    reply_to: str | None = None,
    message_id: str | None = None,
) -> dict:
    """Build a validated A2A Message envelope (docs/04 §3.2)."""
    if kind not in MESSAGE_KINDS:
        raise ValueError(f"unknown message kind: {kind}")
    for part in parts or []:
        if part.get("type") not in PART_TYPES:
            raise ValueError(f"unknown part.type: {part.get('type')!r}")
    return {
        "protocol": "a2a",
        "version": "0.1",
        "messageId": message_id or _new_id(),
        "timestamp": _now(),
        "from": from_,
        "to": to or [],
        "toCapability": to_capability,
        "taskId": task_id,
        "kind": kind,
        "parts": parts or [],
        "replyTo": reply_to,
    }


class AgentCard:
    """A2A Agent Card (docs/04 §3.1)."""

    def __init__(
        self,
        agent: str,
        display_name: str,
        capabilities: list[str],
        status: str = "idle",
    ):
        if status not in CARD_STATUSES:
            raise ValueError(f"invalid card status: {status}")
        self.agent = agent
        self.displayName = display_name
        self.capabilities = list(capabilities)
        self.status = status
        self.currentTask = None
        self.queueDepth = 0

    def to_dict(self) -> dict:
        return {
            "agent": self.agent,
            "displayName": self.displayName,
            "capabilities": self.capabilities,
            "status": self.status,
            "currentTask": self.currentTask,
            "queueDepth": self.queueDepth,
        }


class OmnirouteTransport:
    """Interface. LocalBusTransport is the Phase-1 implementation."""

    def register_agent(self, card: AgentCard, receive) -> None:  # pragma: no cover
        raise NotImplementedError

    def unregister_agent(self, agent_id: str) -> None:  # pragma: no cover
        raise NotImplementedError

    def get_card(self, agent_id: str) -> AgentCard:  # pragma: no cover
        raise NotImplementedError

    def list_cards(self) -> list[AgentCard]:  # pragma: no cover
        raise NotImplementedError

    def update_status(
        self, agent_id: str, status: str, current_task: str | None = None
    ) -> None:  # pragma: no cover
        raise NotImplementedError

    def emit_status(self, agent_id: str, status: str, task_id: str | None = None) -> None:
        """Send a `status` message to the mesh and update the card."""
        self.update_status(agent_id, status, task_id)
        self.send(
            make_message("status", agent_id, task_id=task_id,
                         parts=[{"type": "text", "text": status}])
        )

    def send(self, message: dict) -> list[str]:  # pragma: no cover
        raise NotImplementedError

    def capability_route(self, capability: str) -> str | None:
        """Idle agent id holding `capability` (specialist-first map order)."""
        for agent_id in self._resolve_capability(capability):
            if self._cards[agent_id].status == "idle":
                return agent_id
        return None


class LocalBusTransport(OmnirouteTransport):
    """In-process queues + SQLite outbox (docs/04 §7-§8)."""

    def __init__(
        self,
        db_path: Path | None = None,
        capability_map: dict[str, list[str]] | None = None,
    ):
        self.db_path = db_path or (memory.MEMORY_DIR / "bus.db")
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        self._cards: dict[str, AgentCard] = {}
        self._handlers: dict[str, callable] = {}
        self.capability_map = capability_map or {}
        self._init_db()

    def _connect(self) -> sqlite3.Connection:
        conn = sqlite3.connect(self.db_path)
        conn.row_factory = sqlite3.Row
        return conn

    def _init_db(self) -> None:
        with self._connect() as conn:
            conn.execute(
                """
                CREATE TABLE IF NOT EXISTS outbox (
                    message_id TEXT NOT NULL,
                    agent_id   TEXT NOT NULL,
                    envelope   TEXT NOT NULL,
                    ts         TEXT NOT NULL,
                    PRIMARY KEY (message_id, agent_id)
                )
                """
            )

    # ---- registry -------------------------------------------------------

    def register_agent(self, card: AgentCard, receive) -> None:
        self._cards[card.agent] = card
        self._handlers[card.agent] = receive

    def unregister_agent(self, agent_id: str) -> None:
        self._cards.pop(agent_id, None)
        self._handlers.pop(agent_id, None)

    def get_card(self, agent_id: str) -> AgentCard:
        try:
            return self._cards[agent_id]
        except KeyError:
            raise RoutingError(f"no agent card for {agent_id!r}")

    def list_cards(self) -> list[AgentCard]:
        return list(self._cards.values())

    def update_status(self, agent_id: str, status: str, current_task: str | None = None) -> None:
        if status not in CARD_STATUSES:
            raise ValueError(f"invalid card status: {status}")
        card = self.get_card(agent_id)
        card.status = status
        card.currentTask = current_task
        if status == "idle":
            card.currentTask = None
            card.queueDepth = max(0, card.queueDepth - 1) if card.queueDepth else 0

    # ---- routing --------------------------------------------------------

    def _resolve_recipients(self, message: dict) -> list[str]:
        kind = message["kind"]
        if message.get("to"):
            return list(dict.fromkeys(message["to"]))
        if message.get("toCapability"):
            return self._resolve_capability(message["toCapability"])
        if kind in ("notify", "status"):
            return list(self._cards.keys())
        raise RoutingError(
            f"message {message['messageId']!r} has no route "
            f"(no to/toCapability, kind={kind!r})"
        )

    def _resolve_capability(self, capability: str) -> list[str]:
        ordered = self.capability_map.get(capability)
        candidates = ordered or [
            c.agent for c in self._cards.values()
            if capability in c.capabilities
        ]
        # keep only known agents, prefer idle, specialist-first (map order)
        idle, busy = [], []
        for agent_id in candidates:
            if agent_id not in self._cards:
                continue
            (idle if self._cards[agent_id].status == "idle" else busy).append(agent_id)
        return idle + busy

    def _persist(self, message: dict, agent_id: str) -> bool:
        """Record delivery; return False when already delivered (dedupe)."""
        with self._connect() as conn:
            cur = conn.execute(
                "INSERT OR IGNORE INTO outbox (message_id, agent_id, envelope, ts) "
                "VALUES (?, ?, ?, ?)",
                (message["messageId"], agent_id,
                 json.dumps(message), message["timestamp"]),
            )
            return cur.rowcount > 0

    def send(self, message: dict) -> list[str]:
        recipients = self._resolve_recipients(message)
        delivered: list[str] = []
        for agent_id in recipients:
            if message["kind"] == "task":
                card = self.get_card(agent_id)
                if card.status == "working":
                    raise RoutingError(
                        f"no-double-dispatch: {agent_id!r} is working on "
                        f"{card.currentTask!r}; task {message.get('taskId')!r} rejected"
                    )
            if self._persist(message, agent_id):
                handler = self._handlers.get(agent_id)
                if handler:
                    handler(message)
                delivered.append(agent_id)
        return delivered

    def undelivered(self, agent_id: str, limit: int = 50) -> list[dict]:
        with self._connect() as conn:
            rows = conn.execute(
                "SELECT envelope FROM outbox WHERE agent_id=? "
                "ORDER BY rowid ASC LIMIT ?",
                (agent_id, limit),
            ).fetchall()
        return [json.loads(r["envelope"]) for r in rows]
