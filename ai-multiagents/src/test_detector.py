"""Test framework detection for the Tester role (docs/06 Phase 6 §1).

Detects runnable test commands from the Laravel root at summon time and
injects them into the Tester prompt.
"""

from __future__ import annotations

import json
from pathlib import Path


def detect_test_commands(project_root: Path) -> dict:
    """Return detected test commands keyed by runner name."""
    root = Path(project_root)
    found: dict[str, str] = {}

    has_phpunit = (root / "phpunit.xml").is_file() or (
        root / "phpunit.xml.dist"
    ).is_file()
    has_pest = (root / "pest.php").is_file()
    has_artisan = (root / "artisan").is_file()
    if (has_phpunit or has_pest) and has_artisan:
        found["php"] = "php artisan test"

    package_json = root / "package.json"
    if package_json.is_file():
        try:
            scripts = json.loads(package_json.read_text(encoding="utf-8")).get(
                "scripts", {}
            )
        except (json.JSONDecodeError, OSError):
            scripts = {}
        test = scripts.get("test") or scripts.get("tests")
        if test:
            found["js"] = f"npm test  # ({test})"
    return found


def tester_environment_note(project_root: Path) -> str:
    """Human-readable summary injected into the Tester prompt."""
    commands = detect_test_commands(project_root)
    if not commands:
        return (
            "No test framework auto-detected (no phpunit.xml/Pest/"
            "package.json test script). Run tests you can, or document why "
            "none are runnable."
        )
    lines = " ".join(f"{k}: {v}" for k, v in commands.items())
    return f"Detected test commands on this project: {lines}."
