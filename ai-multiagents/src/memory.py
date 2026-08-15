"""Per-agent persistent memory backed by SQLite.

One database file per agent under memory/<agent_id>.db. Survives across
sessions and machines where the memory/ folder is preserved (docs/05).

API:
    remember(agent_id, kind, content)
    recall(agent_id, limit, kind=None)
    save_decision(agent_id, decision, rationale)
    get_context / set_context(agent_id, key, value)
"""

from __future__ import annotations

import sqlite3
from datetime import datetime, timezone
from pathlib import Path

from . import config_loader

MEMORY_DIR = config_loader.SYSTEM_DIR / "memory"


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


class MemoryStore:
    def __init__(self, agent_id: str, memory_dir: Path = MEMORY_DIR):
        memory_dir.mkdir(parents=True, exist_ok=True)
        self.agent_id = agent_id
        self.db_path = memory_dir / f"{agent_id}.db"
        self._init()

    def _connect(self) -> sqlite3.Connection:
        conn = sqlite3.connect(self.db_path)
        conn.row_factory = sqlite3.Row
        return conn

    def _init(self) -> None:
        with self._connect() as conn:
            conn.executescript(
                """
                CREATE TABLE IF NOT EXISTS entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ts TEXT NOT NULL,
                    kind TEXT NOT NULL,
                    content TEXT NOT NULL
                );
                CREATE TABLE IF NOT EXISTS decisions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ts TEXT NOT NULL,
                    decision TEXT NOT NULL,
                    rationale TEXT NOT NULL
                );
                CREATE TABLE IF NOT EXISTS context (
                    key TEXT PRIMARY KEY,
                    value TEXT NOT NULL,
                    ts TEXT NOT NULL
                );
                """
            )

    def remember(self, kind: str, content: str) -> None:
        with self._connect() as conn:
            conn.execute(
                "INSERT INTO entries (ts, kind, content) VALUES (?, ?, ?)",
                (_now(), kind, content),
            )

    def recall(self, limit: int = 50, kind: str | None = None) -> list[dict]:
        with self._connect() as conn:
            if kind is None:
                rows = conn.execute(
                    "SELECT * FROM entries ORDER BY id DESC LIMIT ?", (limit,)
                ).fetchall()
            else:
                rows = conn.execute(
                    "SELECT * FROM entries WHERE kind=? ORDER BY id DESC LIMIT ?",
                    (kind, limit),
                ).fetchall()
        return [dict(r) for r in rows]

    def save_decision(self, decision: str, rationale: str) -> None:
        with self._connect() as conn:
            conn.execute(
                "INSERT INTO decisions (ts, decision, rationale) VALUES (?, ?, ?)",
                (_now(), decision, rationale),
            )

    def get_context(self, key: str):
        with self._connect() as conn:
            row = conn.execute(
                "SELECT value FROM context WHERE key=?", (key,)
            ).fetchone()
        return row["value"] if row else None

    def set_context(self, key: str, value: str) -> None:
        with self._connect() as conn:
            conn.execute(
                "INSERT INTO context (key, value, ts) VALUES (?, ?, ?) "
                "ON CONFLICT(key) DO UPDATE SET value=excluded.value, "
                "ts=excluded.ts",
                (key, value, _now()),
            )
