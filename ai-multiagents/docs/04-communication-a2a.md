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

## 8. Transport Design

`src/message_bus.py` exposes an interface (`OmnirouteTransport`) with two
implementations. The mesh transport is selected at Orchestrator construction
by the `MESH_TRANSPORT` environment switch in
`src/orchestrator.py::build_transport` — the swap only changes the bus the
adapters are wired onto; agent adapters are never touched.

1. `LocalBusTransport` — **default** (`MESH_TRANSPORT=local`). In-process
   queues + SQLite persistence.
2. `HttpJsonRpcTransport` — **built (2026-08-17)**, `src/http_transport.py`.
   Real A2A over HTTP JSON-RPC 2.0, selected with `MESH_TRANSPORT=http`
   (listener host/port overridable via `MESH_HOST` / `MESH_PORT`, defaults
   `127.0.0.1:47500`). Subclasses `LocalBusTransport`, so named /
   capability / broadcast routing, status ownership, the central
   no-double-dispatch rule and SQLite outbox dedupe by `messageId` are all
   inherited unchanged.

### 8.1 HTTP JSON-RPC 2.0 surface

`HttpJsonRpcTransport` extends the local core with a stdlib
`ThreadingHTTPServer` listener on a daemon thread, reachable as soon as the
transport is constructed (default endpoint `http://127.0.0.1:47500`):

- `GET /` and `GET /health` → `{"status": "ok"}`.
- `POST` with a JSON-RPC 2.0 request body (`{"jsonrpc":"2.0","id":…,
  "method":…,"params":…}`) dispatches the mesh methods (the POST path is not
  validated; the code examples use `POST /`):

| Method | Params | Result |
|--------|--------|--------|
| `mesh.cards` | `{}` | `{"cards": [ … ]}` — the Agent Card mesh directory |
| `mesh.send` | `{"message": {A2A envelope}}` | `{"delivered": ["<agent>", …], "status": "ok"}` — delivers like the in-process `send()` (named / `toCapability` / broadcast), persists to the same SQLite outbox (`memory/bus.db`) with dedupe by `messageId`; a `RoutingError` (e.g. no-double-dispatch) comes back as JSON-RPC error `-32000` |
| `mesh.status` | `{}` | `{"status": "ok", "agents": <n>, "host": …, "port": …}` — listener health |

JSON-RPC 2.0 errors are returned in the standard shape: `-32700` parse
error, `-32600` invalid request, `-32601` method not found, `-32602`
invalid params, `-32000` routing / internal error.

### 8.2 Verification (2026-08-17)

Live check `a2a_http_verify.py` boots the real Orchestrator with
`MESH_TRANSPORT=http` (ephemeral port `MESH_PORT=47511`), then `mesh.cards`
lists the registered agent cards, `mesh.send` delivers an A2A task envelope
to an agent over HTTP, and the envelope is confirmed persisted in the SQLite
outbox (`memory/bus.db`, `message_id=msg_http_live_001`).

## 9. Privacy / Security

- The Coordinator may **filter or summarize** internal messages before reporting
  to the user; raw internal chatter is never exposed.
- No secrets in envelopes; reference secret material by config key.
- Envelope fields are validated on receipt; unknown `kind`/`part.type` are
  rejected with an explicit error.
