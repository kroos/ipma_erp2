# 01 - System Architecture

## 1. Purpose

`ai-multiagents` is a portable, self-contained multi-agent AI team that lives inside
any Laravel project root and collaborates on development tasks. Agents talk to each
other over **A2A (Agent2Agent)** messaging routed through **Omniroute**. Only the
**Coordinator** speaks to the human user; all other agents communicate exclusively
through the internal A2A mesh.

## 2. High-Level View

```
                  ┌──────────────────────┐
                  │         USER         │
                  └───────────┬──────────┘
                              │  instructions / results
                              ▼
                  ┌──────────────────────┐
                  │      COORDINATOR     │  (User Gateway, Task Dispatcher,
                  │                      │   Team Supervisor)
                  └───────────┬──────────┘
                              │  A2A over Omniroute
        ┌─────────────────────┼─────────────────────┐
        ▼                     ▼                     ▼
┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│ BACKEND LEAD   │  │ FRONTEND LEAD  │  │   DB ADMIN     │
│ (tier 2)       │  │ (tier 2)       │  │ (tier 2)       │
└───────┬────────┘  └───────┬────────┘  └───────┬────────┘
        │                   │                   │
  Laravel / PHP      jQuery / Vue / React  MySQL / MariaDB /
                                          SQL Server
        └───────────────────┼───────────────────┘
                            │
          ┌─────────────────┼─────────────────┐
          ▼                 ▼                 ▼
   ┌────────────┐   ┌────────────┐   ┌────────────┐
   │ APP        │   │ TESTER     │   │ BUG FIXER  │
   │ DESIGNER   │   │            │   │            │
   └────────────┘   └────────────┘   └────────────┘
                            │
                            ▼
                   ┌────────────┐
                   │ DOC WRITER │
                   └────────────┘
```

## 3. Components

| Component | Responsibility |
|-----------|----------------|
| **Orchestrator (Python)** | Boots the team, spawns agents as subprocesses, wires the A2A mesh, tracks task/agent state, persists memory. |
| **A2A Gateway (Omniroute)** | Local smart router: holds Agent Cards, routes by capability and status, enforces no-double-dispatch. A2A semantics over a local bus (see `docs/04`). |
| **Agent Adapter** | Thin Python wrapper around each CLI AI agent (opencode, freebuff, ...). Translates A2A envelopes into CLI invocations and captures CLI output back into A2A responses. |
| **Coordinator Role** | Hybrid: a Python control loop (REPL, task list, dispatch, monitoring, reporting) with the user-chosen AI agent invoked one-shot as the "brain" for decomposition, conflict resolution, and clarifying questions. |
| **Specialist Agents** | Domain workers (backend/frontend/DB tiers and their specialists). Consume tasks, produce results, report back. |
| **Persistent Memory** | Per-agent durable memory stored on disk (see `docs/06` folder layout and `docs/04` memory rules). |

## 4. Core Design Principles

1. **Portable** — runs from the root of any Laravel project; no hard-coded absolute paths.
2. **Cross-OS** — Windows and Linux; Python 3 is the single runtime requirement.
3. **A2A everywhere** — all inter-agent communication uses A2A envelopes via Omniroute.
4. **Coordinator is the only user gateway** — specialists never talk to the user directly.
5. **Persistent per-agent memory** — each agent remembers across sessions.
6. **Model continuity** — agents reuse the model from their last session unless the user overrides.
7. **No idle agents** — the Coordinator keeps the team busy by dispatching work matched to each agent's specialty.

## 5. Key Architectural Decisions (ADR summary)

| # | Decision | Rationale |
|---|----------|-----------|
| ADR-1 | Python orchestrator spawns CLI agents as subprocesses | The involved AI agents are CLI tools; a lightweight Python supervisor is the most portable wrapper. |
| ADR-2 | A2A via Omniroute for all inter-agent messaging; full A2A data model (Agent Card, Message, Task, Artifact, Part, lifecycle) on a local bus; smart routing by capability + status | Uniform protocol-native envelopes regardless of which CLI backs the agent; upgrade path to HTTP JSON-RPC later |
| ADR-2a | Omniroute enforces the no-double-dispatch rule centrally | Routing source of truth is a single registry, not per-agent judgment |
| ADR-3 | SQLite for per-agent persistent memory | Zero-config, single file, works on Windows and Linux without a server. |
| ADR-4 | YAML config files for roles, agents, models | Human-editable; portable; the "last session model" state lives in a JSON state file. |
| ADR-5 | No pinned AI models | Models are inherited from the last session per agent; only explicit user instruction overrides. |
| ADR-6 | Scripts in `setup.sh` + `setup.ps1` | Native cross-OS bootstrap without extra tooling. |
| ADR-7 | Hybrid Coordinator: Python control loop + user-chosen agent as brain; agent emits a structured task-plan JSON | Robust, testable control flow; LLM used only for judgment; avoids fragile REPL/output parsing |
| ADR-8 | Agent invocation is config-driven (`agents.yaml` templates); one-shot subprocess per task; prompt via argv with stdin fallback | Adding/supporting a CLI = editing config, not code; avoids persistent-REPL fragility and Windows argv/quoting issues |
| ADR-9 | Tester delegates test execution to the CLI agent, but must emit a structured JSON test report (defect contract); frameworks auto-detected; regression gate auto-runs affected+full suite on fix submit | Keeps the bug loop automated despite free-text agent output; minimizes adapter logic |

## 6. Runtime Flow

1. User runs the bootstrap command (e.g. `python ai-multiagents/summon.py`) from the Laravel root.
2. Orchestrator loads `config/`, resolves the last-session model state, and spawns every agent as a subprocess, wiring them into the A2A mesh.
3. Coordinator announces readiness to the user and waits for instructions.
4. On instruction: Coordinator decomposes → dispatches tasks → monitors status → coordinates parallel/sequential work → collects results → reports to the user.
5. Tester/Bug Fixer loop runs until defects are resolved.
6. Doc Writer keeps documentation synchronized.
7. Orchestrator persists agent memory and model state, then shuts the mesh down.
