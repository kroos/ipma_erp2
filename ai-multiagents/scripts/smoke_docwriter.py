"""Smoke test: Doc Writer (M6/M7) — produces docs, clarifies before inventing.

Run from anywhere:  python scripts/smoke_docwriter.py
"""

from __future__ import annotations

import sys
import tempfile
from pathlib import Path
from types import SimpleNamespace
from unittest import mock

SYSTEM_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(SYSTEM_DIR))

from src.agent_adapter import AgentAdapter
from src.doc_writer import DocError, DocWriter
from src.message_bus import LocalBusTransport

FAKE = SYSTEM_DIR / "scripts" / "fake_agent.py"

failures = []


def check(name: str, cond: bool, detail: str = "") -> None:
    status = "OK " if cond else "FAIL"
    print(f"  [{status}] {name}{'  ' + detail if detail else ''}")
    if not cond:
        failures.append(name)


bus = LocalBusTransport()


def make_adapter(agent_id: str, extra: list[str]):
    adapter = AgentAdapter(
        agent_id,
        {
            "binary": "python",
            "argv": ["python", str(FAKE), "-p", "{prompt}", *extra],
            "model_flag": [],
            "timeout_sec": 60,
            "verified": True,
        },
        bus,
        project_root=SYSTEM_DIR,
    )
    adapter.register(f"Fake {agent_id}", [])
    return adapter


docs_brain = make_adapter("docs_brain", ["--docs"])
ask_brain = make_adapter("ask_brain", ["--docs-ask"])

with tempfile.TemporaryDirectory() as tmp:
    tmp_root = Path(tmp)
    orch = SimpleNamespace(
        adapters={"docs_brain": docs_brain, "ask_brain": ask_brain},
        roles={},
        project_root=tmp_root,
    )

    print("== Doc Writer: produces docs ==")
    writer = DocWriter(orch, brain="docs_brain")
    payload = writer.document("login flow")
    check("1 doc produced", len(payload["docs"]) == 1)
    check("no open questions", payload["questions"] == [])
    check("doc path set", payload["docs"][0]["path"] == "docs/feature-login.md")
    check("doc content set", "Login" in payload["docs"][0]["content"])
    check("doc written to disk under project root",
          (tmp_root / "docs" / "feature-login.md").is_file())

    print("\n== Doc Writer: clarifies instead of inventing ==")
    writer2 = DocWriter(orch, brain="ask_brain")
    with mock.patch("builtins.input", side_effect=["LoginController"]):
        payload2 = writer2.document("login flow")
    check("questions surfaced first", payload2["questions"] == [])
    check("docs produced after answer", len(payload2["docs"]) == 1)
    check("never invents: answer used",
          "LoginController" in payload2["docs"][0]["content"])

    print("\n== Doc Writer: unsafe paths refused ==")
    bad_writer = DocWriter(orch, brain="docs_brain")
    for bad in ["../evil.md", "/etc/passwd", "docs/../../evil.md", "C:evil.md"]:
        try:
            bad_writer._write_doc({"path": bad, "content": "x"})
            check(f"refuses {bad!r}", False)
        except DocError:
            check(f"refuses {bad!r}", True)

    print("\n== Doc Writer: hard failures ==")
    plain_brain = make_adapter("plain_brain", [])
    orch.adapters["plain_brain"] = plain_brain
    bad_writer = DocWriter(orch, brain="plain_brain")  # ECHO mode: no JSON
    with mock.patch("builtins.input", side_effect=[""]):
        try:
            bad_writer.document("no json")
            check("no-JSON docs raises DocError", False)
        except DocError:
            check("no-JSON docs raises DocError", True)

    print("\n== Doc Writer: malformed JSON retried once (M7 E2E) ==")
    class FlakyBrain:
        def __init__(self):
            self.calls = 0

        def run_task(self, message):
            self.calls += 1
            if self.calls == 1:
                # malformed: docs array missing its closing `]`
                text = ('{"docs": [{"path": "docs/x.md", "title": "X", '
                        '"summary": "s", "content": "c"}, "questions": []}')
            else:
                text = ('------------------\n'
                        '{"docs": [{"path": "docs/x.md", "title": "X", '
                        '"summary": "s", "content": "c"}], "questions": []}\n'
                        '------------------')
            return {"parts": [{"type": "text", "text": text}],
                    "metadata": {"state": "completed"}}

    flaky = FlakyBrain()
    orch.adapters["flaky"] = flaky
    retry_writer = DocWriter(orch, brain="flaky")
    payload3 = retry_writer._ask_brain("scope", [])
    check("malformed first response retried once", flaky.calls == 2)
    check("retry produced the docs payload", len(payload3["docs"]) == 1)

print()
if failures:
    print(f"SMOKE DOCWRITER FAILED: {failures}")
    sys.exit(1)
print("SMOKE DOCWRITER OK")
