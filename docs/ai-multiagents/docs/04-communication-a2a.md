## 8. HttpJsonRpcTransport — real A2A over HTTP JSON-RPC (BUILT)

**Status: implemented — 2026-08-17.** `HttpJsonRpcTransport` lives in `ai-multiagents/src/http_transport.py` and provides real A2A over HTTP JSON-RPC 2.0. It is swappable with `LocalBusTransport` without touching agent adapters; its public API mirrors `LocalBusTransport` (`register_agent`, `unregister_agent`, `get_card`, `list_cards`, `update_status`, `emit_status`, `send`, `capability_route`, `undelivered`).

### HTTP JSON-RPC 2.0 surface

The server (`http.server.ThreadingHTTPServer`, stdlib) listens on a configurable host/port and serves `POST /rpc`, dispatching:

- `message/send` — deliver an A2A envelope to a registered local handler (no-double-dispatch on task messages; SQLite outbox at `ai-multiagents/memory/bus.db` with `messageId` dedupe).
- `task/get` — task status/result.
- `task/cancel` — mark a task canceled.
- `card/get` — agent card by id.
- `card/list` — all agent cards.

### Configuration

- Default endpoint: `127.0.0.1:47500`.
- Orchestrator environment switch: `MESH_TRANSPORT=local|http`.
- Agent adapters remain untouched by the transport swap.

See `docs/05-http-json-rpc-transport.md` for the full transport documentation.