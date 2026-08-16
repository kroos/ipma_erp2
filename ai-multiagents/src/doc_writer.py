"""Doc Writer role — hybrid: Python loop + a user-chosen agent as docs brain.

Synchronizes documentation with implementation (docs/02 §3.4, docs/06
Phase 6 §4). Never invents undocumented behavior: when information is
missing the brain emits clarifying questions, and the loop surfaces them
instead of guessing.

Docs contract (emitted by the brain as a delimited JSON block):
    {"docs": [{"path": "docs/...", "title": "...", "summary": "...",
               "content": "..."}],
     "questions": ["..."]}

The loop is resilient to two real-LLM failure modes (2026-08-15): the
brain may emit malformed JSON (repaired/retried) or — because it holds
filesystem MCP tools — it may write the docs contract itself to disk and
return prose instead of the delimited block. In the latter case the loop
salvages the contract from disk (_salvage_from_disk) so the run still
self-documents.
"""

from __future__ import annotations

import json
import sys
import time
from pathlib import Path

from . import config_loader
from .agent_adapter import JSON_RETRY_HINT, extract_json_block
from .doc_verifier import DocVerifier, UnverifiedClaim
from .memory import MemoryStore
from .message_bus import make_message

MAX_CLARIFY_ROUNDS = 3
# brain attempts per docs request: initial + 2 retries with escalating
# hints (the doc-writer brain holds MCP filesystem tools and tends to write
# files itself / return prose — more attempts than the other brains)
MAX_BRAIN_ATTEMPTS = 3
# hallucination-guard rounds: initial payload is verified, then the brain
# gets ONE correction round; if claims still fail verification afterwards
# the docs are NOT written (DocError) unless an interactive user accepts
MAX_VERIFY_ATTEMPTS = 2

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
        # hallucination guard: verify generated docs against the code
        # before writing. Auto-disabled when the project root is not a
        # Laravel root (smoke tests use temp dirs) — see doc_verifier.
        self.verifier = DocVerifier(Path(proj))

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
            "Do NOT use any tools and do NOT write any files yourself — "
            "the coordinator writes your docs to disk. Your ENTIRE reply "
            "must be the delimited JSON block below, nothing before or "
            "after it.\n\n"
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

    def _ask_brain(self, scope: str, answers: list[str],
                   salvage_since: float | None = None,
                   prompt: str | None = None) -> dict:
        """Ask the brain for docs; retry when the JSON does not parse
        (real LLMs occasionally emit malformed JSON, M7 E2E). The final
        retry adds a stronger hint: the doc-writer brain holds MCP
        filesystem tools and may otherwise keep writing files instead of
        the JSON block (finding-5 flake). When `salvage_since` is given, a
        docs contract the brain wrote to disk itself is adopted between
        attempts, so a file-writing brain costs one LLM call, not three.
        `prompt` overrides the default docs prompt (used for the
        hallucination-guard correction round).
        """
        base_prompt = prompt or self._docs_prompt(scope, answers)
        for attempt in range(1, MAX_BRAIN_ATTEMPTS + 1):
            prompt = base_prompt
            if attempt > 1:
                prompt += JSON_RETRY_HINT
                if attempt >= MAX_BRAIN_ATTEMPTS:
                    prompt += (
                        " Do NOT use any tools and do NOT write any files. "
                        "Your ENTIRE reply must be the delimited JSON block "
                        "shown above and nothing else."
                    )
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
            # the brain may have written the docs contract to disk itself
            # (MCP filesystem) while returning prose — adopt it early
            if salvage_since is not None:
                salvaged = self._salvage_from_disk(salvage_since)
                if salvaged is not None:
                    return salvaged
        raise DocError("doc writer brain returned no parseable JSON")

    # ---- disk salvage (finding-5 flake) --------------------------------

    def _salvage_from_disk(self, since: float) -> dict | None:
        """Recover a docs contract the brain wrote itself.

        Doc-writer brains that hold filesystem MCP tools often write the
        docs (and the contract JSON) directly to disk and return prose
        instead of the delimited block (observed 2026-08-15: the brain
        wrote docs/response.json + the .md and the run aborted). Any
        `*.json` under self.doc_root written after `since` that parses as
        a valid docs contract is adopted as the payload.
        """
        if not self.doc_root.is_dir():
            return None
        hits: list[tuple[float, Path, dict]] = []
        for path in self.doc_root.rglob("*.json"):
            try:
                mtime = path.stat().st_mtime
            except OSError:
                continue
            if mtime < since:
                continue
            try:
                raw = json.loads(path.read_text(encoding="utf-8"))
            except (OSError, ValueError):
                continue
            if not isinstance(raw, dict) or not raw.get("docs"):
                continue
            try:
                payload = self._validate_payload(raw)
            except DocError:
                continue
            if payload["docs"]:
                hits.append((mtime, path, payload))
        if not hits:
            return None
        hits.sort(key=lambda h: h[0], reverse=True)
        _, path, payload = hits[0]
        print(f"[doc_writer] salvaged docs contract from brain-written "
              f"file {path}")
        return payload

    def document(self, scope: str) -> dict:
        """Produce docs for `scope`, clarifying first; returns the payload.

        When the brain returns no parseable JSON (even after retries) but
        wrote the docs contract to disk itself, the payload is salvaged so
        the run still self-documents (finding-5 flake).
        """
        answers: list[str] = []
        run_started = time.time()
        # non-interactive (one-shot / backgrounded): never block on input();
        # tell the brain to proceed with assumptions instead
        for _ in range(MAX_CLARIFY_ROUNDS):
            try:
                payload = self._ask_brain(scope, answers, run_started)
            except DocError:
                payload = self._salvage_from_disk(run_started)
                if payload is None:
                    raise
            if not payload["questions"]:
                break
            if not self.interactive:
                # no one can answer questions in a one-shot run: tell the
                # brain to proceed with assumptions on the first round; if
                # it still returns docs alongside its question afterwards,
                # accept them as best effort instead of discarding the
                # payload (observed 2026-08-15: brain returned docs + 1
                # question every round and the loop threw them away)
                if answers:
                    if payload["docs"]:
                        print("[doc_writer] non-interactive: using docs despite "
                              "an open question")
                        payload["questions"] = []
                        break
                    print("[doc_writer] non-interactive: brain produced no docs; "
                          "giving up")
                    raise DocError(
                        "brain kept asking with no docs in non-interactive mode"
                    )
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
        # hallucination guard: verify every doc against the code BEFORE
        # writing anything; unverified claims get ONE brain correction
        # round, then the run fails loud unless an interactive user accepts
        payload = self._verify_docs(payload, scope, answers, run_started)
        for doc in payload["docs"]:
            self._write_doc(doc)
            self.memory.remember("doc", json.dumps(doc, indent=2))
        print(
            f"[doc_writer] {len(payload['docs'])} doc(s) for '{scope}': "
            + ", ".join(d["path"] for d in payload["docs"])
        )
        return payload

    # ---- hallucination guard ------------------------------------------

    def _verify_docs(self, payload: dict, scope: str, answers: list[str],
                     salvage_since: float) -> dict:
        """Verify generated docs against the code before writing.

        The Doc Writer brain has been observed inventing code symbols
        (finding-6 doc claimed whitelist field names that match nothing).
        Each doc's code claims (routes, config/env keys, classes, views,
        columns, file paths) are checked against the project. Unverified
        claims get ONE brain correction round; if claims still fail
        verification the docs are NOT written — an interactive user may
        explicitly accept them, otherwise DocError aborts the run.
        """
        if not self.verifier.enabled:
            return payload
        unverified = self.verifier.verify_payload(payload)
        for attempt in range(1, MAX_VERIFY_ATTEMPTS):
            if not unverified:
                return payload
            print(f"[doc_writer] hallucination guard: "
                  f"{len(unverified)} unverified claim(s)")
            for claim in unverified:
                print(f"  - {claim}")
            prompt = self._correction_prompt(scope, answers, unverified)
            try:
                payload = self._ask_brain(scope, answers, salvage_since,
                                          prompt=prompt)
            except DocError:
                print("[doc_writer] brain failed to produce a correction")
                break
            unverified = self.verifier.verify_payload(payload)
        if not unverified:
            return payload
        # claims still unverified: interactive users may accept explicitly
        if self.interactive:
            print("[doc_writer] Docs contain claims that could not be "
                  "verified against the code.")
            try:
                answer = input("  Accept and write anyway? [y/N] > ")
            except EOFError:
                answer = "n"
            if answer.strip().lower() in ("y", "yes"):
                print("[doc_writer] accepted by user despite unverified claims")
                return payload
        raise DocError(
            "hallucination guard: docs contain unverified code claims:\n"
            + "\n".join(f"  - {c}" for c in unverified)
        )

    def _correction_prompt(self, scope: str, answers: list[str],
                           unverified: list[UnverifiedClaim]) -> str:
        """Ask the brain to fix docs whose code claims failed verification."""
        claims = "\n".join(f"- {c}" for c in unverified)
        return (
            "You are the Doc Writer of a multi-agent Laravel development "
            "team.\n"
            "Your previous draft contained code references that could NOT "
            "be verified against the codebase. Fix EVERY one of them: "
            "replace invented identifiers with the real ones, or remove "
            "the claim entirely. Never invent new names to replace them.\n\n"
            f"Unverified claims:\n{claims}\n\n"
            "Do NOT use any tools and do NOT write any files yourself — "
            "the coordinator writes your docs to disk. Your ENTIRE reply "
            "must be the delimited JSON block below, nothing before or "
            "after it.\n\n"
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
