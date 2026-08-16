"""AgentAdapter — wraps a CLI AI binary as an A2A-speaking agent.

Driven entirely by config/agents.yaml templates (never hard-coded commands).
One-shot subprocess per task, spawned from the Laravel root, stdout/stderr
captured to per-task output files, timeout enforced (kills runaway processes).

Prompt delivery (docs/06 Phase 3):
  - config `prompt_mode` (argv) first; on spawn failure retry via stdin
  - prompts longer than ARGV_LIMIT switch to stdin automatically

Emits `status` messages on lifecycle changes and produces a `result`
envelope with artifacts. Registers its Agent Card at summon.
"""

from __future__ import annotations

import json
import os
import re
import shutil
import subprocess
import sys
import time
from pathlib import Path

from . import config_loader
from .message_bus import make_message

ARGV_LIMIT = 8000  # conservative; Windows argv ceiling is ~32k chars
JSON_BLOCK_RE = re.compile(
    r"-{6,}\s*\n(\{.*?\})\s*\n-{6,}", re.DOTALL
)
# Real LLMs often wrap the contract JSON in a ```json code fence instead of
# the documented `---` delimiters (M7 E2E finding) — accept both.
JSON_FENCE_RE = re.compile(
    r"```(?:json)?\s*\n(\{.*?\})\s*\n```", re.DOTALL
)
# Top-level keys the four brains use in their contract payloads; the
# fallback scanner prefers an object that carries one of these.
CONTRACT_KEYS = frozenset(
    {"report", "docs", "fix", "tasks", "clarifying_questions"}
)
# Appended to a brain prompt on its retry attempt when the first response's
# JSON did not parse (M7 E2E finding: real LLMs occasionally emit malformed
# JSON, e.g. a missing `]`).
JSON_RETRY_HINT = (
    "\n\n(Your previous response's JSON did not parse. Reply with ONLY "
    "the delimited JSON block shown above, nothing before or after.)"
)

LOG_DIR = config_loader.SYSTEM_DIR / "logs"


def _loads_or_repair(text: str) -> dict | None:
    """json.loads, then a light repair pass for common LLM JSON defects
    (trailing commas, truncated closing brackets). Returns a dict or None.
    """
    try:
        parsed = json.loads(text)
        if isinstance(parsed, dict):
            return parsed
    except ValueError:
        pass
    repaired = _repair_json(text)
    if repaired is None:
        return None
    try:
        parsed = json.loads(repaired)
    except ValueError:
        return None
    return parsed if isinstance(parsed, dict) else None


def _repair_json(text: str) -> str | None:
    """Repair common LLM JSON defects (string-aware): strip trailing commas
    before `}`/`]`, append the missing closing brackets of a truncated
    tail. Returns repaired text that parses, else None. The string walking
    skips `"..."` so braces inside string values (e.g. PHP code in doc
    content) never confuse the balance.
    """
    # 1) strip trailing commas (string-aware)
    out: list[str] = []
    in_str = esc = False
    i, n = 0, len(text)
    while i < n:
        ch = text[i]
        if in_str:
            out.append(ch)
            if esc:
                esc = False
            elif ch == "\\":
                esc = True
            elif ch == '"':
                in_str = False
            i += 1
            continue
        if ch == '"':
            in_str = True
            out.append(ch)
            i += 1
            continue
        if ch == ",":
            j = i + 1
            while j < n and text[j] in " \t\r\n":
                j += 1
            if j < n and text[j] in "}]":
                i += 1  # drop the trailing comma
                continue
        out.append(ch)
        i += 1
    stripped = "".join(out)
    try:
        json.loads(stripped)
        return stripped
    except ValueError:
        pass
    # 2) truncated tail: append missing closing brackets (string-aware)
    stack: list[str] = []
    in_str = esc = False
    for ch in stripped:
        if in_str:
            if esc:
                esc = False
            elif ch == "\\":
                esc = True
            elif ch == '"':
                in_str = False
            continue
        if ch == '"':
            in_str = True
        elif ch == "{":
            stack.append("}")
        elif ch == "[":
            stack.append("]")
        elif ch == "}":
            if stack and stack[-1] == "}":
                stack.pop()
        elif ch == "]":
            if stack and stack[-1] == "]":
                stack.pop()
    if stack:
        repaired = stripped + "".join(reversed(stack))
        try:
            json.loads(repaired)
            return repaired
        except ValueError:
            pass
    return None


def _balanced_span(text: str, start: int) -> tuple[int, int] | None:
    """Return (open, close) indexes of the balanced `{...}` object starting
    at `start` (which must be `{`), skipping braces inside JSON strings.
    Returns None when the object never closes (truncated output)."""
    depth = 0
    in_str = esc = False
    i, n = start, len(text)
    while i < n:
        ch = text[i]
        if in_str:
            if esc:
                esc = False
            elif ch == "\\":
                esc = True
            elif ch == '"':
                in_str = False
        else:
            if ch == '"':
                in_str = True
            elif ch == "{":
                depth += 1
            elif ch == "}":
                depth -= 1
                if depth == 0:
                    return start, i + 1
        i += 1
    return None


def extract_json_block(output: str) -> dict | None:
    """Pull a contract JSON object from free text (docs/06 Phase 6).

    Tries, in order: the documented `---` delimiters, a ```json code fence
    (common real-LLM output), then a scan of every balanced `{...}` object
    preferring one that carries a contract key (report/docs/fix/tasks/
    clarifying_questions) and falling back to the last object found. Each
    candidate also gets a light repair pass (trailing commas, truncated
    closers), and the scan is string-aware so braces inside string values
    (e.g. PHP code in doc content) don't break the balance.
    """
    if not output:
        return None
    for pattern in (JSON_BLOCK_RE, JSON_FENCE_RE):
        match = pattern.search(output)
        if match:
            parsed = _loads_or_repair(match.group(1))
            if parsed is not None:
                return parsed
    # fallback: scan every balanced object; an inline `{"status": "ok"}`
    # in prose must not shadow the real payload (M7 E2E finding)
    candidates: list[dict] = []
    pos = 0
    while True:
        start = output.find("{", pos)
        if start == -1:
            break
        span = _balanced_span(output, start)
        if span is None:
            # unbalanced from here: the tail may be a truncated object the
            # repair pass can close (missing `]` / `}`)
            parsed = _loads_or_repair(output[start:])
            if parsed is not None:
                candidates.append(parsed)
            break
        parsed = _loads_or_repair(output[start:span[1]])
        if parsed is not None:
            candidates.append(parsed)
        pos = span[1]
    if not candidates:
        return None
    for obj in candidates:
        if CONTRACT_KEYS.intersection(obj):
            return obj
    return candidates[-1]


class AgentError(Exception):
    """Raised when a CLI cannot be started (spawn failure)."""


ENV_VAR_RE = re.compile(r"\$\{(?:env\.)?([A-Za-z_][A-Za-z0-9_]*)(?::-([^}]*))?}")


class AgentAdapter:
    def __init__(
        self,
        agent_id: str,
        config: dict,
        bus,
        project_root: Path = config_loader.PROJECT_ROOT,
        model: str | None = None,
        mcp_defs: dict | None = None,
    ):
        self.agent_id = agent_id
        self.config = config
        self.bus = bus
        self.project_root = Path(project_root)
        self.model = model  # resolved model or None (CLI default)
        self.timeout_sec = config.get("timeout_sec", 600)
        self.verified = config.get("verified", False)
        # MCP tool layer (docs/03): the adapter renders a per-agent config
        # file from config/agents.yaml `mcp` defs and delivers it to the CLI
        # via env (opencode: OPENCODE_CONFIG) or argv flag (openclaude:
        # --mcp-config). `mcp_env` is merged into the subprocess env;
        # `mcp_flag` is appended to the command line in argv AND stdin mode.
        self.mcp_defs = mcp_defs or {}
        self.mcp_env: dict[str, str] = {}
        self.mcp_flag: list[str] = []
        self._prepare_mcp()

    # ---- MCP tool layer -------------------------------------------------

    def _resolve_config_env(self) -> dict[str, str]:
        """Per-agent `env` config block, with ${VAR} / {project_root}
        placeholders resolved (mirrors _resolve_mcp_def).
        """
        raw = self.config.get("env") or {}
        out: dict[str, str] = {}
        for key, value in raw.items():
            out[str(key)] = str(
                self._resolve_env(value).replace("{project_root}", str(self.project_root))
            )
        return out

    @staticmethod
    def _resolve_env(text) -> str:
        """Resolve ${VAR} / ${env.VAR} / ${VAR:-default} to env values."""

        def _sub(match):
            name, default = match.group(1), match.group(2)
            return os.environ.get(name, default or "")

        return ENV_VAR_RE.sub(_sub, str(text))

    def _resolve_mcp_def(self, defn):
        """Resolve env placeholders + {project_root} in an MCP definition."""
        if isinstance(defn, dict):
            return {k: self._resolve_mcp_def(v) for k, v in defn.items()}
        if isinstance(defn, list):
            return [self._resolve_mcp_def(v) for v in defn]
        return self._resolve_env(defn).replace("{project_root}", str(self.project_root))

    @staticmethod
    def _to_openclaude_shape(defn: dict) -> dict:
        """opencode-schema def -> openclaude mcpServers shape.

        opencode uses {type: local, command: [..]} / {type: remote, url};
        openclaude uses {command, args} for local (cmd /c on Windows, same
        as its own settings.json) and {type: http, url, headers} for remote.
        """
        if defn.get("type") == "local":
            command = list(defn.get("command", []))
            # match ~/.openclaude/settings.json: .cmd shims need cmd /c
            if sys.platform.startswith("win") and command:
                shape = {"command": "cmd", "args": ["/c", *command]}
            else:
                shape = {"command": command[0] if command else "", "args": command[1:]}
            if defn.get("env"):
                shape["env"] = defn["env"]
            return shape
        shape = {"type": "http", "url": defn.get("url", "")}
        if defn.get("headers"):
            shape["headers"] = defn["headers"]
        return shape

    def _prepare_mcp(self) -> None:
        """Render the per-agent MCP config file (once) and set delivery."""
        names = self.config.get("mcp") or []
        if not names or not self.mcp_defs:
            return
        defs = {n: self._resolve_mcp_def(self.mcp_defs[n])
                for n in names if n in self.mcp_defs}
        if not defs:
            return
        delivery = self.config.get("mcp_delivery")
        path = LOG_DIR / f"{self.agent_id}-mcp.json"
        path.parent.mkdir(parents=True, exist_ok=True)
        if delivery == "env":  # opencode: OPENCODE_CONFIG=<file>, mcp key
            # enabled: true is explicit so remote servers are not skipped
            # (opencode defaults unknown servers to enabled, but be explicit)
            for d in defs.values():
                d.setdefault("enabled", True)
            path.write_text(
                json.dumps(
                    {"$schema": "https://opencode.ai/config.json", "mcp": defs},
                    indent=2,
                ),
                encoding="utf-8",
            )
            self.mcp_env = {"OPENCODE_CONFIG": str(path)}
        elif delivery == "flag":  # openclaude: --mcp-config <file>, mcpServers
            path.write_text(
                json.dumps(
                    {"mcpServers": {
                        n: self._to_openclaude_shape(d) for n, d in defs.items()
                    }},
                    indent=2,
                ),
                encoding="utf-8",
            )
            self.mcp_flag = ["--mcp-config", str(path)]
        else:
            print(f"[{self.agent_id}] unknown mcp_delivery {delivery!r}; MCP skipped")

    # ---- cards / status -------------------------------------------------

    def register(self, display_name: str, capabilities: list[str]) -> None:
        from .message_bus import AgentCard

        self.bus.register_agent(
            AgentCard(self.agent_id, display_name, capabilities),
            receive=self.on_message,
        )

    def on_message(self, message: dict) -> None:
        """Bus handler. kind=task runs the one-shot subprocess synchronously."""
        if message["kind"] == "task":
            self.run_task(message)

    # ---- command building ----------------------------------------------

    def _render_command(self, prompt: str) -> list[str]:
        argv = [a.replace("{prompt}", prompt) for a in self.config["argv"]]
        if self.model:
            model_flag = [
                f.replace("{model}", self.model) for f in self.config["model_flag"]
            ]
            argv += model_flag
        argv += self.mcp_flag
        return argv

    def _build_command(self, argv: list[str]) -> list[str]:
        """Resolve the binary on PATH, then wrap Windows .cmd/.bat via cmd.exe.

        Configs use bare binary names (e.g. `opencode`), but on Windows those
        are often `.CMD` shims; subprocess cannot execute a bare name that
        resolves to a .cmd without cmd.exe. Resolve via shutil.which() and
        prefix cmd /c when needed. Avoids shell=True.
        """
        first = argv[0]
        resolved = shutil.which(first) if os.path.sep not in first else first
        if resolved:
            argv = [resolved, *argv[1:]]
            first = resolved
        if sys.platform.startswith("win") and first.lower().endswith((".cmd", ".bat")):
            comspec = os.environ.get("COMSPEC") or "cmd.exe"
            return [comspec, "/c", *argv]
        return argv

    def _stdin_argv(self) -> list[str]:
        """argv for stdin mode: drop the {prompt} token and its flag (e.g. -p).

        Overridable per agent via config `stdin_argv` (list template). MCP
        flag is kept so MCP stays available in stdin fallback mode.
        """
        if self.config.get("stdin_argv"):
            argv = [a.replace("{prompt}", "") for a in self.config["stdin_argv"]]
            return argv + self.mcp_flag
        argv = list(self.config["argv"])
        if "{prompt}" in argv:
            i = argv.index("{prompt}")
            if i > 0:
                del argv[i - 1:i + 1]
            else:
                del argv[i]
        return argv + self.mcp_flag

    def _prompt(self, message: dict) -> str:
        parts = message.get("parts", [])
        text = "\n\n".join(
            p.get("text", "") for p in parts if p.get("type") == "text"
        )
        data = [p.get("data", {}) for p in parts if p.get("type") == "data"]
        if data:
            text += "\n\n[data]\n" + json.dumps(data, indent=2)
        return text.strip()

    # ---- execution ------------------------------------------------------

    def run_task(self, message: dict) -> dict:
        """Execute one task message; returns the `result` envelope."""
        task_id = message.get("taskId") or "untitled"
        prompt = self._prompt(message)

        self.bus.emit_status(self.agent_id, "working", task_id)
        out_file = LOG_DIR / f"{task_id}.out.txt"
        err_file = LOG_DIR / f"{task_id}.err.txt"
        LOG_DIR.mkdir(parents=True, exist_ok=True)

        started = time.monotonic()
        try:
            output, error, exit_code, mode = self._run_once(prompt)
            timed_out = False
        except subprocess.TimeoutExpired:
            output, error, exit_code, timed_out, mode = "", "timed out", None, True, "argv"
        except AgentError as exc:
            output, error, exit_code, timed_out, mode = "", str(exc), None, False, "argv"

        duration = round(time.monotonic() - started, 2)
        out_file.write_text(output, encoding="utf-8", errors="replace")
        err_file.write_text(error or "", encoding="utf-8", errors="replace")

        if timed_out or exit_code != 0 or (error and exit_code is None):
            state = "failed"
        else:
            state = "completed"

        status_text = (output or "").strip() or (error or "").strip() or "(no output)"
        result = make_message(
            "result",
            from_=self.agent_id,
            to=[message["from"]],
            task_id=task_id,
            reply_to=message["messageId"],
            parts=[
                {"type": "text", "text": status_text[:200_000]},
                {
                    "type": "file",
                    "file": str(out_file),
                    "mime": "text/plain",
                    "name": out_file.name,
                },
            ],
        )
        result["metadata"] = {
            "state": state,
            "exitCode": exit_code,
            "timedOut": timed_out,
            "mode": mode,
            "durationSec": duration,
            "outputFile": str(out_file),
            "verified": self.verified,
        }
        result["state"] = state
        self.bus.update_status(self.agent_id, "idle", task_id)
        return result

    def _run_once(self, prompt: str) -> tuple[str, str, int, str]:
        """Run once. Returns (out, err, code, mode).

        Prompt delivery (docs/06 Phase 3):
          - argv mode: prompt injected into the command line, when safe
          - stdin mode: prompt piped via stdin (dangerous/long prompts,
            or argv spawn failure)

        Windows .cmd/.bat shims (launched via cmd.exe) expand %VAR% and
        truncate at newlines/CR inside a quoted prompt arg (M7 finding,
        verified 2026-08-12), so those prompts MUST go via stdin. Embedded
        double quotes DO survive cmd.exe's re-parse through npm-style
        shims — Python's list2cmdline quoting round-trips them exactly
        (verified against a node argv echo shaped like the real npm
        gemini shim) — so quote-only prompts stay in argv. That matters
        for CLIs like gemini whose -p flag reads its value from argv only.
        Real executables (python, node, ...) always use argv.
        """
        argv = self._render_command(prompt)
        command = self._build_command(argv)
        if not self._shim_requires_stdin(command, prompt):
            try:
                out, err, code = self._spawn(command, None)
                return out, err, code, "argv"
            except AgentError as exc:
                print(f"[{self.agent_id}] argv spawn failed ({exc}); retrying via stdin")
        # stdin fallback: binary + fixed args, prompt piped via stdin
        out, err, code = self._spawn(self._build_command(self._stdin_argv()), prompt)
        return out, err, code, "stdin"

    def _shim_requires_stdin(self, command: list[str], prompt: str) -> bool:
        """True when the prompt cannot travel safely in argv for `command`.

        Real executables (python, node, ...) get correct argv quoting from
        subprocess, so they always use argv (except over-long prompts).
        Only .cmd/.bat shims (launched via cmd.exe) are restricted:
        newlines/CR truncate the arg and %VAR% is env-expanded by cmd.exe,
        so those go via stdin. Double quotes survive the shim re-parse
        untouched (verified), so quote-only prompts stay in argv — which
        is required for CLIs like gemini whose -p reads argv only. Long
        prompts always go via stdin.
        """
        if len(prompt) > ARGV_LIMIT:
            return True
        if not self._is_cmd_shim(command):
            return False
        return any(c in prompt for c in ("\n", "\r", "%"))

    def _is_cmd_shim(self, command: list[str]) -> bool:
        """True when `command` is a cmd.exe /c wrapper (Windows .cmd/.bat
        shim). Only these need stdin for quoted/newline prompts.
        """
        if not command or not sys.platform.startswith("win"):
            return False
        comspec = (os.environ.get("COMSPEC") or "cmd.exe").lower()
        return Path(command[0]).name.lower() in ("cmd.exe", "cmd", comspec)

    def _spawn(self, command: list[str], stdin_text: str | None):
        # subprocess env = parent env + per-agent `env` overrides (config
        # wins — e.g. pinning CLAUDE_CODE_USE_POWERSHELL_TOOL=0 so a
        # machine-wide value cannot silently change the CLI's tool schema)
        # + MCP delivery vars (mcp_env wins over config env).
        env = {**os.environ, **self._resolve_config_env(), **self.mcp_env}
        if not env:
            env = None
        try:
            proc = subprocess.run(
                command,
                cwd=str(self.project_root),
                capture_output=True,
                text=True,
                encoding="utf-8",
                errors="replace",
                input=stdin_text,
                timeout=self.timeout_sec,
                env=env,
            )
        except OSError as exc:
            raise AgentError(f"cannot start {command[0]!r}: {exc}") from exc
        return proc.stdout or "", proc.stderr or "", proc.returncode
