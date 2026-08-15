# 03 - AI Agents & Model Assignment

## 1. Involved AI Agents

The team is executed by a set of external AI CLI agents. This system spawns them
as subprocesses and wraps each one with an **agent adapter** so they behave like
A2A team members.

Involved agents (team de-scoped 2026-08-14 — opencode is the only CLI with
working credentials on this machine; openclaude/openclaw/claudeCode/gemini/
hermes were removed after credential checks failed):

| Agent | Notes |
|-------|-------|
| opencode | Only headless CLI with working credentials (2026-08-14); model from last session |
| freebuff | Interactive-only fallback (TUI, no headless one-shot); excluded from wiring; model from last session |

## 2. Model Rule (no pinned models)

- If the user does **not** specify a model for an AI agent, the agent uses the
  model from its **last session**.
- Example: if in the last session the user ran `opencode` with **opus**, then
  when this system spawns opencode it should also use **opus**. Same rule for
  every agent.
- Only an explicit user instruction overrides the inherited model.
- The orchestrator persists each agent's last-used model in a state file
  (`config/state.json`), so the rule works across sessions.

## 3. Default Role → Agent Mapping

If the user does not state which AI agent plays which role, the defaults below
apply. Any role may ultimately be played by any agent in any project.

| Role | Default Agent(s) |
|------|------------------|
| Coordinator | chosen by user (fallback: freebuff) |
| Backend Specialist | freebuff, opencode |
| Laravel Specialist | freebuff, opencode |
| PHP Specialist | freebuff, opencode |
| Frontend Specialist | opencode |
| jQuery Specialist | opencode |
| Vue Specialist | opencode |
| React Specialist | opencode |
| CSS Specialist | opencode |
| Bootstrap 5 Specialist | opencode |
| Tailwind 5 Specialist | opencode |
| UI-UX Designer | opencode |
| DB Admin | freebuff, opencode |
| MySQL Specialist | freebuff, opencode |
| MariaDB Specialist | freebuff, opencode |
| MS SQL Server Specialist | opencode, freebuff |
| App Designer | opencode |
| Tester | opencode |
| Bug Fixer | opencode |
| Doc Writer | opencode |

## 4. Config Data Model

`config/agents.yaml` — one entry per AI agent. Command templates are runtime
config, never hard-coded in code. A working schema example lives in
`config/agents.yaml` with **best-guess** invocation shapes marked
`verified: false` until tested:

```yaml
agents:
  - id: opencode
    binary: opencode
    argv: ["opencode", "run", "{prompt}"]   # {prompt} injected (argv) or piped (stdin)
    prompt_mode: argv                         # argv | stdin
    model_flag: ["--model", "{model}"]
    timeout_sec: 600
    verified: true
```

Prompt delivery is config-driven with **argv → stdin fallback**: the template's
`prompt_mode` picks argv injection first; if the spawn fails, the adapter retries
piping the prompt via stdin. Agents run **one-shot per task** (fresh subprocess,
captured output, enforced timeout) — never a persistent REPL.

`config/roles.yaml` — role definitions and default agent assignment:

```yaml
roles:
  coordinator:
    default_agent: user        # chosen by user at summon time
    tier: 1
  backend_specialist:
    default_agent: freebuff
    tier: 2
  laravel_specialist:
    default_agent: freebuff
    tier: 3
    reports_to: backend_specialist
  # ... etc for every role
```

`config/state.json` — persisted per-agent state (model continuity):

```json
{
  "agents": {
    "opencode": { "last_model": "opus" }
  }
}
```

Resolution order when summoning an agent:

1. User-specified model for this session (highest priority) → use it.
2. Else `state.json` last-session model → use it.
3. Else the agent's own default → use it, then record as last session.

### Coordinator selection (spec: "chosen by user")

1. `--coordinator <agent>` flag (one-shot mode) → use it.
2. Else interactive prompt at summon time → use it.
3. Else `state.json` last-session coordinator → use it.
4. Else default to **freebuff**, then record it.

The chosen coordinator agent acts as the **brain** of the Coordinator role; the
Python orchestrator provides the control loop (see `docs/06` Phase 5).
