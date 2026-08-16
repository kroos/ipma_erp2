# HttpJsonRpcTransport — A2A over HTTP JSON-RPC

**Date:** 2026-08-17
**File:** `ai-multiagents/src/http_transport.py`

## Overview

`HttpJsonRpcTransport(OmnirouteTransport)` provides real A2A transport over HTTP JSON-RPC 2.0. It mirrors the public API of `LocalBusTransport` (from `ai-multiagents/src/message_bus.py`) and is swappable with it without touching agent adapters (see `docs/04-communication-a2a.md` §8).

Imports `AgentCard`, `make_message`, `RoutingError` from `.message_bus`. Code style (type hints, docstrings) matches `message_bus.py`.

## Public API (mirrors LocalBusTransport)

`register_agent`, `unregister_agent`, `get_card`, `list_cards`, `update_status`, `emit_status`, `send`, `capability_route`, `undelivered`.

## Design

### 1) HTTP JSON-RPC 2.0 server (stdlib only)

- Built on `http.server.ThreadingHTTPServer` + `json` from the Python standard library; no new dependencies beyond the existing PyYAML.
- Listens on a configurable host/port; defaults `127.0.0.1:47500`.
- Serves `POST /rpc`, dispatching JSON-RPC methods:
  - `message/send` — delivers an A2A envelope to a registered local handler, enforcing no-double-dispatch on task messages exactly like `LocalBusTransport`, persisting to the same SQLite outbox at `ai-multiagents/memory/bus.db` with dedupe by `messageId`.
  - `task/get` — returns task status/result.
  - `task/cancel` — marks a task canceled.
  - `card/get` — returns an agent card by id.
  - `card/list` — returns all agent cards.

### 2) Client

- `send(message)` resolves recipients (named `/` toCapability `/` broadcast) using the same `_resolve_recipients` logic as `LocalBusTransport`.
- POSTs each envelope to the remote agent endpoint as a JSON-RPC `message/send`.
- Honors `RoutingError` on no-double-dispatch.

### 3) SQLite outbox semantics

- At-least-once delivery with `INSERT OR IGNORE` dedupe, reusing the same outbox table (`ai-multiagents/memory/bus.db`).

### 4) Remote endpoint registry

- Maps `agent_id` → base URL.
- Defaults to a single local server URL.
- Agents register their own URL via `register_agent`.

## Verification

- `python -m py_compile ai-multiagents/src/http_transport.py` (expected: no errors; tester confirmation pending).
- Self-test: starts the HTTP server on an ephemeral port, registers a fake handler, sends a `message/send` JSON-RPC request via `urllib`, confirms the handler receives the envelope (tester confirmation pending).