"""Smoke test: prompt delivery routing (docs/06 Phase 3, M7-esc finding).

Verifies the adapter's delivery decision matrix:
  - embedded double quotes -> argv (Python list2cmdline survives cmd.exe
    shim re-parse; verified against a node shim shaped like the npm
    gemini shim AND a python-script shim, 2026-08-12)
  - newlines / CR / %VAR% -> stdin (cmd.exe truncates/expands them)
  - over-long prompts -> stdin
  - real executables (python, node) always argv
And the (2026-08-15) agent config: opencode + openclaude are the wired
headless CLIs, freebuff stays interactive-only, openclaw/claudeCode/gemini/
hermes are removed (failed credential checks).

Run from anywhere:  python scripts/smoke_argv_routing.py
"""

from __future__ import annotations

import json
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src import config_loader
from src.agent_adapter import AgentAdapter, ARGV_LIMIT
from src.message_bus import LocalBusTransport, make_message

failures = []


def check(name: str, cond: bool, detail: str = "") -> None:
    status = "OK " if cond else "FAIL"
    print(f"  [{status}] {name}{'  ' + detail if detail else ''}")
    if not cond:
        failures.append(name)


# --- A. routing decisions (unit-level, no subprocess) ----------------------
print("== A. _shim_requires_stdin decisions ==")

bus = LocalBusTransport()

# real executable (python): never stdin except over-long
real = AgentAdapter(
    "real", {"binary": "python", "argv": ["python", "-c", "{prompt}"],
             "model_flag": [], "timeout_sec": 60, "verified": True},
    bus, project_root=SYSTEM_DIR,
)
check("real exe: quotes stay argv",
      not real._shim_requires_stdin(["python"], 'say {"a": "b"}'))
check("real exe: newline stays argv",
      not real._shim_requires_stdin(["python"], "line1\nline2"))
check("real exe: percent stays argv",
      not real._shim_requires_stdin(["python"], "50% off"))
check("real exe: over-long goes stdin",
      real._shim_requires_stdin(["python"], "x" * (ARGV_LIMIT + 1)))

# cmd shim: quotes stay argv, newline/%/long go stdin
shim_cmd = ["cmd.exe", "/c", "C:/fake/path/shim.CMD"]
shim = AgentAdapter(
    "shim", {"binary": "python", "argv": ["python", "-p", "{prompt}"],
             "model_flag": [], "timeout_sec": 60, "verified": True},
    bus, project_root=SYSTEM_DIR,
)
check("cmd shim: quotes stay argv",
      not shim._shim_requires_stdin(shim_cmd, 'say {"a": "b"}'))
check("cmd shim: newline goes stdin",
      shim._shim_requires_stdin(shim_cmd, "line1\nline2"))
check("cmd shim: CR goes stdin",
      shim._shim_requires_stdin(shim_cmd, "line1\rline2"))
check("cmd shim: %VAR% goes stdin",
      shim._shim_requires_stdin(shim_cmd, "use %PATH%"))
check("cmd shim: over-long goes stdin",
      shim._shim_requires_stdin(shim_cmd, "x" * (ARGV_LIMIT + 1)))

# --- B. wired agent config shape -------------------------------------------
print("\n== B. team config (opencode + openclaude wired, freebuff interactive) ==")
agents = config_loader.load_agents()
opencode_cfg = agents.get("opencode")
check("opencode present in agents.yaml", opencode_cfg is not None)
if opencode_cfg:
    check("opencode argv is run {prompt}",
          opencode_cfg.get("argv") == ["opencode", "run", "{prompt}"])
openclaude_cfg = agents.get("openclaude")
check("openclaude present in agents.yaml", openclaude_cfg is not None)
if openclaude_cfg:
    check("openclaude argv uses -p print mode",
          openclaude_cfg.get("argv") == ["openclaude", "-p", "{prompt}"])
    check("openclaude model flag separate from argv",
          openclaude_cfg.get("model_flag") == ["--model", "{model}"])
freebuff_cfg = agents.get("freebuff")
check("freebuff kept but interactive-only",
      freebuff_cfg is not None and freebuff_cfg.get("headless") is False)
for gone in ("openclaw", "claudeCode", "gemini", "hermes"):
    check(f"de-scoped {gone} removed from agents.yaml", gone not in agents)

# --- C. end-to-end through a real cmd shim ---------------------------------
print("\n== C. end-to-end argv round-trip through a real .CMD shim ==")
with tempfile.TemporaryDirectory() as tmp:
    tmp = Path(tmp)
    echo_py = tmp / "echoargv.py"
    echo_py.write_text(
        "import json, sys\nprint(json.dumps(sys.argv[1:]))\n",
        encoding="utf-8",
    )
    shim_file = tmp / "echoargv.CMD"
    shim_file.write_text(
        f'@ECHO off\npython "{echo_py}" %*\n',
        encoding="utf-8",
    )
    shim_path = str(shim_file)

    a = AgentAdapter(
        "routeshim",
        {"binary": shim_path,
         "argv": [shim_path, "-p", "{prompt}"],
         "stdin_argv": [shim_path, "-p", ""],
         "model_flag": [], "timeout_sec": 60, "verified": True},
        bus, project_root=SYSTEM_DIR,
    )
    a.register("RouteShim", [])

    try:
        # quote-only prompt must round-trip via argv, exactly
        prompt = 'Reply with exactly: {"a": "b"} done'
        res = a.run_task(make_message(
            "task", "orchestrator", to=["routeshim"],
            task_id="routing_quote",
            parts=[{"type": "text", "text": prompt}]))
        mode = res["metadata"]["mode"]
        out = res["parts"][0]["text"]
        check("quote prompt used argv mode", mode == "argv", f"mode={mode}")
        try:
            recv = json.loads(out)
            exact = recv == ["-p", prompt]
        except ValueError:
            exact = False
        check("quote prompt arrived intact", exact,
              f"got={out[:80]!r}")

        # newline prompt must take stdin mode (shim echoes argv only)
        res2 = a.run_task(make_message(
            "task", "orchestrator", to=["routeshim"],
            task_id="routing_nl", parts=[{"type": "text", "text": "l1\nl2"}]))
        check("newline prompt used stdin mode",
              res2["metadata"]["mode"] == "stdin",
              f"mode={res2['metadata']['mode']}")

        # percent prompt must take stdin mode
        res3 = a.run_task(make_message(
            "task", "orchestrator", to=["routeshim"],
            task_id="routing_pct", parts=[{"type": "text", "text": "use %PATH%"}]))
        check("percent prompt used stdin mode",
              res3["metadata"]["mode"] == "stdin",
              f"mode={res3['metadata']['mode']}")
    finally:
        # always clean this test's log files, even on an early exception
        for tid in ("routing_quote", "routing_nl", "routing_pct"):
            for ext in (".out.txt", ".err.txt"):
                f = SYSTEM_DIR / "logs" / f"{tid}{ext}"
                if f.exists():
                    f.unlink()

print()
if failures:
    print(f"SMOKE ARGV ROUTING FAILED: {failures}")
    sys.exit(1)
print("SMOKE ARGV ROUTING OK")
