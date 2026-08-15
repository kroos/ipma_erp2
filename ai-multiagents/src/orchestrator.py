"""Orchestrator — boot sequence, agent mesh wiring, shutdown.

Boot (docs/06 Phase 4):
  load config/state -> spawn all adapters -> register cards -> hand control
  to the Coordinator (Phase 5).

Shutdown: persist state + memory -> close bus -> report.
"""

from __future__ import annotations

import shutil
import sys
from pathlib import Path

from . import config_loader, state
from .agent_adapter import AgentAdapter
from .dispatcher import Dispatcher
from .message_bus import LocalBusTransport


def agent_capabilities(agent_id: str, roles: dict) -> list[str]:
    """Union of capabilities from every role the agent plays by default."""
    caps: list[str] = []
    for role in roles.values():
        if agent_id in role.get("default_agents", []):
            caps.extend(role.get("capabilities", []))
    return sorted(set(caps))


def build_capability_map(roles: dict) -> dict[str, list[str]]:
    """capability -> ordered agent ids, specialist (tier 3) first."""
    ordered = sorted(
        roles.values(), key=lambda r: r.get("tier", 9), reverse=True
    )
    mapping: dict[str, list[str]] = {}
    for role in ordered:
        for cap in role.get("capabilities", []):
            seen = set(mapping.setdefault(cap, []))
            for agent_id in role.get("default_agents", []):
                if agent_id not in seen:
                    mapping[cap].append(agent_id)
                    seen.add(agent_id)
    return mapping


def available_agents(agents_cfg: dict) -> dict[str, dict]:
    """agent_id -> config for binaries present on PATH (docs/07).

    Interactive-only agents (config `headless: false`, e.g. freebuff) are
    excluded: the adapter drives one-shot subprocesses and cannot use them.
    """
    return {
        agent_id: cfg
        for agent_id, cfg in agents_cfg.items()
        if shutil.which(cfg.get("binary", ""))
        and cfg.get("headless", True)
    }


class Orchestrator:
    def __init__(
        self,
        project_root: Path | None = None,
        model_overrides: dict[str, str] | None = None,
    ):
        self.project_root = Path(project_root or config_loader.resolve_project_root())
        self.agents_cfg = config_loader.load_agents()
        self.roles = config_loader.load_roles()
        self.mcp_defs = config_loader.load_mcp()
        self.state = state.StateStore()
        self.model_overrides = model_overrides or {}
        self.bus = LocalBusTransport(capability_map=build_capability_map(self.roles))
        self.adapters: dict[str, AgentAdapter] = {}
        self.dispatcher = Dispatcher(self.bus, self.adapters)

    # ---- lifecycle ------------------------------------------------------

    def boot(self) -> None:
        """Spawn adapters for available agents and register their cards."""
        for agent_id, cfg in available_agents(self.agents_cfg).items():
            model = self.state.resolve_model(agent_id, self.model_overrides.get(agent_id))
            adapter = AgentAdapter(
                agent_id, cfg, self.bus,
                project_root=self.project_root, model=model,
                mcp_defs=self.mcp_defs,
            )
            adapter.register(
                display_name=cfg.get("display_name") or agent_id.replace("_", " ").title(),
                capabilities=agent_capabilities(agent_id, self.roles),
            )
            self.adapters[agent_id] = adapter
            print(
                f"[orchestrator] wired {agent_id:<12} "
                f"binary={cfg['binary']:<10} model={model or '(cli default)'}"
            )
        self.dispatcher.adapters = self.adapters

    def mesh_status(self) -> list[dict]:
        return [c.to_dict() for c in self.bus.list_cards()]

    def shutdown(self) -> None:
        print("[orchestrator] shutting down: persisting state + closing bus")
        for adapter in self.adapters.values():
            self.bus.unregister_agent(adapter.agent_id)
