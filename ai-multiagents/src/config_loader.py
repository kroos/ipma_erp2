"""Configuration loading and Laravel root resolution.

All paths in this system resolve relative to the `ai-multiagents/` folder,
which itself sits at the root of a Laravel project:
    SYSTEM_DIR   = .../laravel-root/ai-multiagents
    PROJECT_ROOT = .../laravel-root
"""

from __future__ import annotations

from pathlib import Path

import yaml

SYSTEM_DIR = Path(__file__).resolve().parent.parent
CONFIG_DIR = SYSTEM_DIR / "config"
PROJECT_ROOT = SYSTEM_DIR.parent


def is_laravel_root(path: Path = PROJECT_ROOT) -> bool:
    """True when the path looks like a Laravel root (artisan + composer.json)."""
    return (path / "artisan").is_file() and (path / "composer.json").is_file()


def resolve_project_root() -> Path:
    """Return the Laravel root, warning when it does not look like one."""
    if not is_laravel_root():
        print(
            f"[config] Warning: {PROJECT_ROOT} does not look like a Laravel root "
            f"(no artisan / composer.json). Agents will run here anyway."
        )
    return PROJECT_ROOT


def _load_yaml(name: str) -> dict:
    path = CONFIG_DIR / name
    with path.open("r", encoding="utf-8") as fh:
        return yaml.safe_load(fh)


def load_agents() -> dict:
    """Load config/agents.yaml, merging per-agent entries with defaults."""
    data = _load_yaml("agents.yaml")
    defaults = data.get("defaults", {})
    agents = {}
    for entry in data.get("agents", []):
        merged = {**defaults, **entry}
        agents[merged["id"]] = merged
    return agents


def load_roles() -> dict:
    """Load config/roles.yaml."""
    data = _load_yaml("roles.yaml")
    return data.get("roles", {})
