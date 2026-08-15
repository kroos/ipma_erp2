# ai-multiagents

A portable, self-contained **multi-agent AI team** that lives at the root of any
Laravel project and works together on development tasks.

- **Team:** Coordinator → tier-2 leads (Backend, Frontend, DB Admin) → specialists
  (Laravel/PHP, jQuery/Vue/React/CSS/Bootstrap/Tailwind/UI-UX, MySQL/MariaDB/MSSQL),
  plus App Designer, Tester, Bug Fixer, and Doc Writer.
- **Communication:** all agents talk over **A2A (Agent2Agent)** routed through
  **Omniroute**; only the Coordinator communicates with the user.
- **Execution:** a Python orchestrator spawns CLI AI agents as subprocesses and
  wraps them as team members. Current team (2026-08-14): **opencode** (the only
  CLI with working credentials on this machine) plus **freebuff** as the
  interactive fallback; openclaude/openclaw/claudeCode/gemini/hermes were
  de-scoped after credential checks failed.
- **Portable:** runs from the root of any Laravel project on **Windows and Linux**;
  Python 3 is the only runtime requirement.
- **Memory:** every agent keeps persistent per-agent memory across sessions.
- **Models:** agents reuse their last-session model unless the user overrides
  (no pinned models).

## Documentation

| Doc | Contents |
|-----|----------|
| [docs/01-architecture.md](docs/01-architecture.md) | System architecture & design decisions |
| [docs/02-team-roles.md](docs/02-team-roles.md) | Team hierarchy, roles, responsibilities, bug loop |
| [docs/03-agent-models.md](docs/03-agent-models.md) | AI agents, default role mapping, model continuity |
| [docs/04-communication-a2a.md](docs/04-communication-a2a.md) | A2A via Omniroute message design |
| [docs/05-folder-structure.md](docs/05-folder-structure.md) | Directory layout |
| [docs/06-implementation-plan.md](docs/06-implementation-plan.md) | Phased build plan & milestones |
| [docs/07-cross-platform-portability.md](docs/07-cross-platform-portability.md) | Windows/Linux portability |

## Quick Start (planned)

```bash
# from the Laravel project root
python ai-multiagents/scripts/setup.py       # bootstrap venv + detect agents
python ai-multiagents/scripts/summon.py      # summon the team (interactive)
python ai-multiagents/scripts/summon.py --instruction "add a users index page"  # one-shot
```

## Status

Milestones M0–M7 complete and verified on Windows (bus, dispatcher,
coordinator, bug loop, doc writer, plus a full E2E against a fresh Laravel
app — smoke tests green). The Linux E2E leg was removed from scope on
2026-08-14 (docs/06, docs/07 §7). See [RESUME.md](RESUME.md) and
[docs/06-implementation-plan.md](docs/06-implementation-plan.md).
