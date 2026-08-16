ADR-2 row update:

Status of the `HttpJsonRpcTransport` entry → **Implemented — 2026-08-17**. Real A2A over HTTP JSON-RPC 2.0 in `ai-multiagents/src/http_transport.py`; default endpoint `127.0.0.1:47500`, `MESH_TRANSPORT=local|http` switch in the orchestrator, agent adapters untouched by the transport swap. See `docs/04-communication-a2a.md` §8.