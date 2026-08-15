"""Per-agent session state: last-session models and coordinator choice.

Persistence contract (docs/03):
- Models are never pinned.
- Model resolution order for an agent:
    1. user-specified model for this session
    2. last-session model from config/state.json
    3. None  -> the CLI's own default (not recorded as a chosen model)
- The chosen model from an explicit session is recorded as the new last-session.

Coordinator selection order (docs/03):
    --coordinator flag -> interactive prompt -> state.json -> fallback freebuff
"""

from __future__ import annotations

import json
from pathlib import Path

from . import config_loader

STATE_FILE = config_loader.CONFIG_DIR / "state.json"


class StateStore:
    def __init__(self, path: Path = STATE_FILE):
        self.path = path
        self._data = self._load()

    def _load(self) -> dict:
        if self.path.is_file():
            try:
                return json.loads(self.path.read_text(encoding="utf-8"))
            except (json.JSONDecodeError, OSError):
                return {}
        return {}

    def _save(self) -> None:
        self.path.parent.mkdir(parents=True, exist_ok=True)
        self.path.write_text(
            json.dumps(self._data, indent=2) + "\n", encoding="utf-8"
        )

    # ---- models ---------------------------------------------------------

    def get_last_model(self, agent_id: str):
        return self._data.get("agents", {}).get(agent_id, {}).get("last_model")

    def set_last_model(self, agent_id: str, model: str) -> None:
        self._data.setdefault("agents", {}).setdefault(agent_id, {})[
            "last_model"
        ] = model
        self._save()

    def resolve_model(self, agent_id: str, user_model=None):
        """1) user-specified  2) last-session  3) None (CLI default)."""
        if user_model:
            return user_model
        return self.get_last_model(agent_id)

    # ---- coordinator ----------------------------------------------------

    def get_last_coordinator(self):
        return self._data.get("coordinator")

    def set_last_coordinator(self, agent_id: str) -> None:
        self._data["coordinator"] = agent_id
        self._save()

    # ---- role -> agent (tester / bug_fixer / doc_writer) ----------------

    def get_last_role_agent(self, role_id: str):
        return self._data.get("roles", {}).get(role_id)

    def set_last_role_agent(self, role_id: str, agent_id: str) -> None:
        self._data.setdefault("roles", {})[role_id] = agent_id
        self._save()
