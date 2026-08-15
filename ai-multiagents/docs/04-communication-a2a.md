# 04 - Communication: A2A via Omniroute

## 1. Decision Summary

- **Transport:** A2A *semantics* on a **local Python message bus**. Omniroute is
  our in-process gateway module, not an external service.
- **Fidelity:** **Full A2A data model** — Agent Card, Message, Task, Artifact, Part,
  and the standard Task lifecycle.
- **Routing:** **Smart routing** — Omniroute holds Agent Cards, can address by
  capability, tracks agent status (idle/working), and enforces the
  no-double-dispatch rule centrally.
- **Upgrade path:** the bus is behind an interface so transport can later be
  lifted to real A2A over HTTP JSON-RPC without rewriting agents.

## 2. A2A Overview

Every agent publishes an **Agent Card** and exchanges structured messages through
Omniroute:

```
   Coordinator ─┐
   Backend Lead ─┤
   Frontend Lead ─┼── Omniroute (local A2A gateway / smart router)
   DB Admin ──────┤
   ...all agents ─┘
```

Omniroute is the single in-process component that knows who is on the mesh,
what they can do, and whether they are busy.

## 3. A2A Data Model

### 3.1 Agent Card

Published by each adapter at summon time; refreshed on every status change.

```json
{
  "agent": "laravel_specialist",
  "displayName": "Laravel Specialist",
  "capabilities": ["migrations", "models", "controllers", "api", "auth", "queues"],
  "status": "idle",            // idle | working
  "currentTask": null,
  "queueDepth": 0
}
```

Capabilities come from `config/roles.yaml`. Status is owned by Omniroute
(routing source of truth), updated via `status` messages from the adapter.

### 3.2 Message

```json
{
  "protocol": "a2a",
  "version": "0.1",
  "messageId": "msg_9f8e...",
  "timestamp": "2026-08-12T10:30:00Z",
  "from": "coordinator",
  "to": ["laravel_specialist"],        // named agents, or
  "toCapability": "migrations",        // capability-address (see §6)
  "taskId": "task_001",
  "kind": "task",                       // see Message kinds below
  "parts": [
    { "type": "text", "text": "Create a migration for the users table." },
    { "type": "data", "data": { "refs": ["app/Models/User.php"] } }
  ],
  "replyTo": "msg_9f8d..."
}
```

**Message kinds** (task-level kinds map onto the Task lifecycle):

| kind | From | Purpose |
|------|------|---------|
| `task` | Coordinator / tier-2 lead | Assign a task to an agent |
| `result` | Any agent | Deliver completed work output |
| `status` | Any agent | Report idle/working + progress |
| `request` | Any agent | Ask a specialist for input (e.g. API contract) |
| `response` | Any agent | Answer a request |
| `notify` | Any agent | Broadcast a fact to the mesh (e.g. "backend done") |

**Part types** (A2A-shaped): `text` (plain/markdown), `file` (path + mime), `data` (JSON).

### 3.3 Task

The unit of work. Owned by the dispatcher; its state transitions are broadcast as
`status` messages.

```json
{
  "taskId": "task_001",
  "state": "working",          // see lifecycle
  "owner": "laravel_specialist",
  "parentTaskId": null,        // for subtasks
  "kind": "task",
  "input": { "parts": [ ... ] },
  "artifacts": [ ... ],
  "metadata": { "capability": "migrations", "priority": "normal", "dependsOn": ["task_000"] }
}
```

### 3.4 Task Lifecycle (A2A standard states)

```
submitted ──► working ──► input-required ──► working ──► completed
    │            │                              │
    └────────────┴──────────► failed            └──► canceled
```

| State | Meaning | Set by |
|-------|---------|--------|
| `submitted` | Dispatched, queued for the agent | Omniroute on `task` kind |
| `working` | Agent processing | Adapter on start |
| `input-required` | Agent needs clarification (goes via Coordinator) | Adapter |
| `completed` | `result` delivered with artifacts | Adapter on done |
| `failed` | Agent errored; task returns to Coordinator for reassign | Adapter on error |
| `canceled` | Coordinator/tier-2 lead aborts the task | Coordinator |

### 3.5 Artifact

Deliverable attached to a `result`:

```json
{ "name": "2026_08_12_000001_create_users_table.php",
  "path": "database/migrations/...",
  "mimeType": "text/x-php" }
```

## 4. Agent Cards & Status Ownership

- Omniroute owns the **registry of Agent Cards** (the mesh directory).
- Adapters send `status` messages (`idle` / `working`, progress, currentTask) on
  lifecycle changes.
- Omniroute updates the card and enforces the **no-double-dispatch rule
  centrally**: a `task` addressed to an agent with status `working` is **rejected**
  (or queued only when explicitly allowed by the Coordinator).

## 5. Routing Rules

1. **Coordinator → tier-2 leads** for work scoped to backend/frontend/DB.
2. **Tier-2 leads → their specialists** for sub-tasks.
3. **Specialists → tier-2 lead** with results; the lead reviews and reports to the
   Coordinator.
4. **Any agent ↔ any agent** for coordination requests.
5. **Only Coordinator** has a route to the user channel.

## 6. Smart Routing

Omniroute resolves message delivery three ways:

| Address style | Example | Resolution |
|---------------|---------|------------|
| Named agent | `to: ["laravel_specialist"]` | Direct delivery to that agent's queue |
| Capability | `toCapability: "migrations"` | Route to the idle agent whose card lists the capability (respects hierarchy: specialist first, then its lead) |
| Broadcast | `kind: "notify"` | Deliver to all subscribers |

Capability routing fails with a clear `routing` error when no capable idle agent
exists (e.g. "no idle agent with capability `migrations`") — the Coordinator
decides whether to wait, requeue, or escalate.

## 7. Delivery Semantics

- Per-agent **persisted queue** (SQLite under `memory/`) so the mesh survives
  crashes; at-least-once delivery with **dedupe by `messageId`**.
- Every `task` must reach a terminal state (`completed` / `failed` / `canceled`),
  so the Coordinator never leaves an orphaned task.
- Task dependency tracking stays in the Coordinator's global task list; Omniroute
  enforces status/no-double-dispatch but does **not** schedule dependencies.

## 8. Local Transport Design

`src/message_bus.py` exposes an interface (`OmnirouteTransport`) with two
implementations:

1. `LocalBusTransport` — **default**. In-process queues + SQLite persistence.
2. `HttpJsonRpcTransport` — *future* real A2A over HTTP JSON-RPC (Agent Cards,
   `message/send`, `task/get`, `task/cancel`). Not built in Phase 1; the interface
   guarantees swap-in without touching agent adapters.

## 9. Privacy / Security

- The Coordinator may **filter or summarize** internal messages before reporting
  to the user; raw internal chatter is never exposed.
- No secrets in envelopes; reference secret material by config key.
- Envelope fields are validated on receipt; unknown `kind`/`part.type` are
  rejected with an explicit error.
