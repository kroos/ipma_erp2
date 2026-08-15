"""Doc Writer role — hybrid: Python loop + a user-chosen agent as docs brain.

Synchronizes documentation with implementation (docs/02 §3.4, docs/06
Phase 6 §4). Never invents undocumented behavior: when information is
missing the brain emits clarifying questions, and the loop surfaces them
instead of guessing.

Docs contract (emitted by the brain as a delimited JSON block):
    {"docs": [{"path": "docs/...", "title": "...", "summary": "...",
               "content": "..."}],
     "questions": ["..."]}
"""

from __future__ import annotations

import json
import sys
from pathlib import Path

from . import config_loader
from .agent_adapter import JSON_RETRY_HINT, extract_json_block
from .memory import MemoryStore
from .message_bus import make_message

MAX_CLARIFY_ROUNDS = 3

# Where generated docs land (relative to the Laravel root). The brain's
# `path` contract (e.g. `docs/feature-login.md`) is resolved under this
# directory; absolute paths in the payload are refused (docs/06 Phase 6 §4).
DEFAULT_DOC_ROOT = config_loader.PROJECT_ROOT / "docs"


class DocError(Exception):
    """Raised when the brain returns no parseable/valid docs payload."""


class DocWriter:
    def __init__(self, orch, brain: str | None = None,
                 interactive: bool | None = None):
        self.orch = orch
        self.adapters = orch.adapters
        self.roles = orch.roles
        self.brain = brain or self._default_brain()
        # explicit interactive flag (one-shot runs pass False); auto-detect
        # from stdin only when not given (isatty() is unreliable under
        # nohup, so one-shot entry points MUST pass it explicitly)
        if interactive is None:
            interactive = sys.stdin.isatty() if hasattr(sys.stdin, "isatty") else True
        self.interactive = interactive
        self.memory = MemoryStore("doc_writer")
        # prefer the orchestrator's project root so generated docs land in
        # the same tree the agents work in (smoke tests can point this at
        # a temp dir); falls back to the resolved Laravel root
        proj = getattr(orch, "project_root", None) or config_loader.PROJECT_ROOT
        self.doc_root = Path(proj) / "docs"

    def _default_brain(self) -> str:
        role = self.roles.get("doc_writer", {})
        for agent_id in role.get("default_agents", []):
            if agent_id in self.adapters:
                return agent_id
        if self.adapters:
            return sorted(self.adapters)[0]
        raise DocError("no agents available to act as Doc Writer")

    # ---- brain contract --------------------------------------------------

    def _docs_prompt(self, scope: str, answers: list[str]) -> str:
        return (
            "You are the Doc Writer of a multi-agent Laravel development "
            "team.\n"
            "You keep documentation synchronized with implementation.\n"
            "NEVER invent undocumented behavior: if information is missing, "
            "ask the responsible specialist via clarifying questions.\n\n"
            f"Scope: {scope}\n"
            f"Specialist answers: {answers or 'none'}\n\n"
            "Reply with ONLY this delimited JSON block, nothing before or "
            "after:\n"
            "------------------\n"
            '{"docs": [{"path": "docs/...", "title": "...", '
            '"summary": "...", "content": "..."}], '
            '"questions": ["..."]}\n'
            "------------------"
        )

    def _validate_payload(self, parsed) -> dict:
        if not isinstance(parsed, dict):
            raise DocError("docs JSON is not an object")
        docs = parsed.get("docs") or []
        questions = parsed.get("questions") or []
        if not isinstance(docs, list) or not isinstance(questions, list):
            raise DocError("docs payload needs 'docs' and 'questions' lists")
        cleaned = []
        for doc in docs:
            if not isinstance(doc, dict) or not doc.get("path"):
                raise DocError(f"invalid doc entry: {doc!r}")
            cleaned.append(
                {
                    "path": str(doc["path"]),
                    "title": str(doc.get("title") or ""),
                    "summary": str(doc.get("summary") or ""),
                    "content": str(doc.get("content") or ""),
                }
            )
        return {"docs": cleaned, "questions": [str(q) for q in questions]}

    # ---- execution -------------------------------------------------------

    def _ask_brain(self, scope: str, answers: list[str]) -> dict:
        """Ask the brain for docs; retry once when the JSON does not parse
        (real LLMs occasionally emit malformed JSON, M7 E2E)."""
        for attempt in (1, 2):
            prompt = self._docs_prompt(scope, answers)
            if attempt > 1:
                prompt += JSON_RETRY_HINT
            message = make_message(
                "task",
                from_="doc_writer",
                to=[self.brain],
                task_id="docs",
                parts=[{"type": "text", "text": prompt}],
            )
            result = self.adapters[self.brain].run_task(message)
            text = result.get("parts", [{}])[0].get("text", "")
            if result.get("metadata", {}).get("state") == "failed":
                raise DocError(f"doc writer brain failed: {text[:300]}")
            parsed = extract_json_block(text)
            if parsed is not None:
                return self._validate_payload(parsed)
        raise DocError("doc writer brain returned no parseable JSON")

    def document(self, scope: str) -> dict:
        """Produce docs for `scope`, clarifying first; returns the payload.

        When the brain emits questions the loop asks them (standing in for
        the responsible specialists) before asking for the docs again.
        """
        answers: list[str] = []
        # non-interactive (one-shot / backgrounded): never block on input();
        # tell the brain to proceed with assumptions instead
        for _ in range(MAX_CLARIFY_ROUNDS):
            payload = self._ask_brain(scope, answers)
            if not payload["questions"]:
                break
            if not self.interactive:
                print("[doc_writer] no interactive user; asking the brain to proceed")
                answers = ["(user unavailable - proceed with reasonable assumptions)"]
                continue
            print("[doc_writer] Clarifying questions for specialists:")
            answers = []
            for q in payload["questions"]:
                try:
                    answers.append(input(f"  Q: {q}\n  > "))
                except EOFError:
                    self.interactive = False
                    answers = ["(user unavailable - proceed with reasonable assumptions)"]
        else:
            raise DocError("brain kept asking; giving up after "
                           f"{MAX_CLARIFY_ROUNDS} rounds")
        for doc in payload["docs"]:
            self._write_doc(doc)
            self.memory.remember("doc", json.dumps(doc, indent=2))
        print(
            f"[doc_writer] {len(payload['docs'])} doc(s) for '{scope}': "
            + ", ".join(d["path"] for d in payload["docs"])
        )
        return payload

    def _write_doc(self, doc: dict) -> None:
        """Write one generated doc to disk under self.doc_root.

        The brain emits `path` like `docs/feature-login.md`; we resolve it
        against the project root and write `content`. Absolute paths,
        drive-relative paths, and `..` escapes are refused so generated
        docs stay in the repo. (M7: docs were reported but never actually
        written.)
        """
        raw = str(doc.get("path") or "").replace("\\", "/")
        rel = Path(raw)
        # reject absolute, drive-qualified, root-qualified (/x on Windows
        # has a root but no drive, so is_absolute() is False there), or
        # ..-escaping paths BEFORE any normalization
        if rel.is_absolute() or rel.drive or rel.root or ".." in rel.parts:
            raise DocError(f"refusing unsafe doc path: {raw!r}")
        # strip ./ and redundant leading docs/ segment
        parts = [p for p in rel.parts if p not in ("", ".")]
        if parts and parts[0] == "docs":
            parts = parts[1:]
        target = self.doc_root / Path(*parts) if parts else self.doc_root
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(doc.get("content") or "", encoding="utf-8")
        # normalize to forward slashes so the reported path is portable
        doc["path"] = str(target.relative_to(self.doc_root.parent)).replace("\\", "/")
        # keep the summary in sync with what was actually written
        print(f"[doc_writer] wrote {target}")
