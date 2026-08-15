# 05 - Folder Structure

## 1. Placement

`ai-multiagents` is a self-contained folder that sits at the **root of any
Laravel project**:

```
laravel-project-root/
├── ai-multiagents/          <-- this system
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── artisan
├── composer.json
└── ...
```

The system never assumes it is elsewhere; all paths are resolved **relative to
the Laravel root** (the parent of `ai-multiagents`), which is how the agents get
access to the real project files.

## 2. Layout

```
ai-multiagents/
├── README.md                     # overview + quick start
├── docs/                         # this documentation set
│   ├── 01-architecture.md
│   ├── 02-team-roles.md
│   ├── 03-agent-models.md
│   ├── 04-communication-a2a.md
│   ├── 05-folder-structure.md
│   ├── 06-implementation-plan.md
│   └── 07-cross-platform-portability.md
│
├── src/                          # Python package (portable, no build step)
│   ├── __init__.py
│   ├── config_loader.py          # reads config/*.yaml + state.json
│   ├── orchestrator.py           # boot / spawn / shutdown the mesh
│   ├── coordinator.py            # hybrid coordinator loop (user gateway, task plan)
│   ├── dispatcher.py             # task lifecycle + no-double-dispatch
│   ├── message_bus.py            # A2A envelope queue / delivery
│   ├── agent_adapter.py          # CLI agent wrapper (subprocess + A2A)
│   ├── memory.py                 # per-agent persistent memory (SQLite)
│   ├── test_detector.py          # auto-detect test frameworks/commands
│   └── state.py                  # last-session model state
│
├── config/
│   ├── agents.yaml               # AI agent definitions (binaries)
│   ├── roles.yaml                # role hierarchy + default agent mapping
│   └── state.json                # persisted last-session models (gitignored)
│
├── memory/                       # per-agent SQLite memory DBs
│   └── <agent_id>.db
│
├── logs/                         # runtime + per-agent logs
│   └── <session>.log
│
├── scripts/
│   ├── summon.py                 # entry point: summon the team
│   ├── setup.py                  # env checks + venv bootstrap
│   ├── setup.sh                  # Linux/macOS bootstrap
│   └── setup.ps1                 # Windows bootstrap
│
├── .gitignore                    # venv/, logs/, memory/, config/state.json
├── requirements.txt              # python deps (yaml, etc.)
└── .editorconfig
```

## 3. What Lives Where

| Path | Purpose | Persisted across sessions? |
|------|---------|---------------------------|
| `config/agents.yaml` | Agent binaries/CLI names | Yes (config) |
| `config/roles.yaml` | Role hierarchy & defaults | Yes (config) |
| `config/state.json` | Last-session model per agent | Yes (runtime state) |
| `memory/*.db` | Per-agent memory (SQLite) | Yes (durable) |
| `logs/` | Session & agent logs | Yes (rotating) |
| `src/` | Python implementation | Code |
| `scripts/` | Entry points & bootstrap | Code |

## 4. Gitignored Files

- `venv/` — local Python virtualenv
- `logs/` — runtime logs
- `memory/*.db` — agent memory (or keep if you want memory to travel with the repo — see note below)
- `config/state.json` — per-machine session state

> Note: the spec requires **persistent memory per agent**. If you want memory to
> survive across machines and clones, commit `memory/*.db`; if memory should stay
> machine-local, gitignore it. Default: **gitignore** (memory is per-machine,
> matching the "last session" model rule which is also machine-local).
