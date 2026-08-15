"""Cross-platform bootstrap for ai-multiagents.

Behavior (docs/07):
    1. Print OS + Python version.
    2. Create a virtualenv (.venv/) if not already inside one, then re-exec.
    3. Install requirements.
    4. Detect every agent binary from config/agents.yaml.
    5. Verify the Laravel root (artisan + composer.json present).
    6. Exit non-zero on failure.
"""

from __future__ import annotations

import shutil
import subprocess
import sys
from pathlib import Path

SYSTEM_DIR = Path(__file__).resolve().parent.parent
VENV_DIR = SYSTEM_DIR / ".venv"
REQUIREMENTS = SYSTEM_DIR / "requirements.txt"


def _venv_python() -> Path:
    if sys.platform.startswith("win"):
        return VENV_DIR / "Scripts" / "python.exe"
    return VENV_DIR / "bin" / "python"


def is_in_venv() -> bool:
    return sys.prefix != sys.base_prefix


def ensure_venv() -> None:
    if is_in_venv():
        return
    print(f"[setup] Creating virtualenv at {VENV_DIR}")
    subprocess.run([sys.executable, "-m", "venv", str(VENV_DIR)], check=True)
    print(f"[setup] Re-running under {_venv_python()}")
    subprocess.run([str(_venv_python()), str(Path(__file__))] + sys.argv[1:], check=True)
    sys.exit(0)


def install_requirements() -> None:
    print("[setup] Installing requirements...")
    subprocess.run(
        [sys.executable, "-m", "pip", "install", "-q", "-r", str(REQUIREMENTS)],
        check=True,
    )


def _load_agents_yaml() -> list[dict]:
    try:
        import yaml
    except ImportError:  # pragma: no cover - only hit before install
        print("[setup] ERROR: PyYAML not importable after install.")
        sys.exit(1)
    path = SYSTEM_DIR / "config" / "agents.yaml"
    with path.open("r", encoding="utf-8") as fh:
        return yaml.safe_load(fh).get("agents", [])


def detect_agents() -> list[str]:
    agents = _load_agents_yaml()
    print("\n[setup] Agent binary detection")
    available = []
    for entry in agents:
        bin_name = entry["binary"]
        found = shutil.which(bin_name)
        status = "OK" if found else "MISSING"
        if found:
            available.append(entry["id"])
        print(f"  {entry['id']:<14} {bin_name:<12} {status:<8} {found or ''}")
    return available


def verify_laravel_root() -> Path:
    project_root = SYSTEM_DIR.parent
    has_artisan = (project_root / "artisan").is_file()
    has_composer = (project_root / "composer.json").is_file()
    status = "OK" if has_artisan and has_composer else "WARNING: not a Laravel root"
    print(f"\n[setup] Laravel root: {project_root}  [{status}]")
    return project_root


def main() -> None:
    print(f"[setup] OS: {sys.platform} | Python: {sys.version.split()[0]}")
    ensure_venv()
    install_requirements()
    detect_agents()
    verify_laravel_root()
    print("\n[setup] Done. Run: python scripts/summon.py")


if __name__ == "__main__":
    main()
