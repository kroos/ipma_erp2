"""HttpJsonRpcTransport — real A2A over HTTP JSON-RPC (docs/04 §8).

Extends LocalBusTransport with a stdlib-only HTTP JSON-RPC server
(ThreadingHTTPServer, default 127.0.0.1:47500) so the mesh directory
(Agent Cards) and message delivery are reachable over the wire:

    POST /   {"jsonrpc":"2.0","id":1,"method":"mesh.cards","params":{}}
        ->   {"jsonrpc":"2.0","id":1,"result":{"cards":[...]}}

    POST /   {"jsonrpc":"2.0","id":2,"method":"mesh.send",
              "params":{"message":{A2A envelope}}}
        ->   {"jsonrpc":"2.0","id":2,"result":{"delivered":["<agent>",...]}}

All in-process calls (adapters, dispatcher) behave exactly like
LocalBusTransport — named / capability / broadcast routing,
no-double-dispatch, and dedupe by messageId are inherited unchanged, so
the transport can be swapped in without touching agent adapters
(docs/04 §1 Upgrade path).
"""

from __future__ import annotations

import json
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

from .message_bus import LocalBusTransport, RoutingError

DEFAULT_HOST = "127.0.0.1"
DEFAULT_PORT = 47500


class JsonRpcError(Exception):
    """JSON-RPC error carrying a spec code (-32600/-32601/-32602/-32700)."""

    def __init__(self, code: int, message: str):
        super().__init__(message)
        self.code = code


class HttpJsonRpcTransport(LocalBusTransport):
    """LocalBusTransport core + an HTTP JSON-RPC endpoint for the mesh.

    Construction starts the listener (daemon thread), so a mesh built with
    MESH_TRANSPORT=http is immediately reachable on host:port. All routing,
    status ownership, no-double-dispatch and dedupe semantics come from
    LocalBusTransport unchanged.
    """

    def __init__(
        self,
        host: str = DEFAULT_HOST,
        port: int = DEFAULT_PORT,
        **kwargs,
    ):
        super().__init__(**kwargs)
        self.host = host
        self.port = port
        self._httpd: ThreadingHTTPServer | None = None
        self._thread: threading.Thread | None = None
        self.start()

    # ---- lifecycle -----------------------------------------------------

    def start(self) -> None:
        """Bind the JSON-RPC listener and serve on a daemon thread."""
        if self._httpd is not None:
            return
        self._httpd = _JsonRpcServer((self.host, self.port), _JsonRpcHandler)
        self._httpd.transport = self
        self._thread = threading.Thread(
            target=self._httpd.serve_forever, name="a2a-http", daemon=True
        )
        self._thread.start()
        print(
            f"[http_transport] A2A JSON-RPC listening on "
            f"http://{self.host}:{self.port}"
        )

    def stop(self) -> None:
        """Stop the listener (idempotent)."""
        httpd, self._httpd = self._httpd, None
        if httpd is not None:
            httpd.shutdown()
            httpd.server_close()

    def endpoint(self) -> str:
        return f"http://{self.host}:{self.port}"

    # ---- JSON-RPC dispatch ---------------------------------------------

    def _dispatch(self, method: str, params):
        """Route one JSON-RPC method to the mesh. Returns a result object."""
        if method == "mesh.cards":
            return {"cards": [c.to_dict() for c in self.list_cards()]}
        if method == "mesh.send":
            message = params.get("message") if isinstance(params, dict) else None
            if not isinstance(message, dict):
                raise JsonRpcError(
                    -32602, "params.message must be an A2A message envelope"
                )
            try:
                delivered = self.send(message)
            except RoutingError as exc:  # e.g. no-double-dispatch / no route
                raise JsonRpcError(-32000, f"routing error: {exc}") from exc
            return {"delivered": delivered, "status": "ok"}
        if method == "mesh.status":
            return {
                "status": "ok",
                "agents": len(self._cards),
                "host": self.host,
                "port": self.port,
            }
        raise JsonRpcError(-32601, f"method not found: {method!r}")


class _JsonRpcServer(ThreadingHTTPServer):
    daemon_threads = True


class _JsonRpcHandler(BaseHTTPRequestHandler):
    """Single-endpoint JSON-RPC 2.0 handler (POST body is the request)."""

    def log_message(self, fmt, *args):  # keep the smoke output clean
        pass

    def _respond(self, status: int, obj: dict) -> None:
        body = json.dumps(obj).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def _rpc_error(self, rid, code: int, message: str) -> None:
        self._respond(200, {
            "jsonrpc": "2.0",
            "id": rid,
            "error": {"code": code, "message": message},
        })

    def do_GET(self) -> None:
        if self.path in ("/", "/health"):
            self._respond(200, {"status": "ok"})
        else:
            self._rpc_error(None, -32601, "not found")

    def do_POST(self) -> None:
        length = int(self.headers.get("Content-Length") or 0)
        raw = self.rfile.read(length)
        try:
            payload = json.loads(raw.decode("utf-8") if raw else "{}")
        except ValueError:
            self._rpc_error(None, -32700, "parse error")
            return
        if not isinstance(payload, dict) or not isinstance(payload.get("method"), str):
            self._rpc_error(payload.get("id") if isinstance(payload, dict) else None,
                            -32600, "invalid request")
            return
        rid = payload.get("id")
        params = payload.get("params") or {}
        try:
            result = self.server.transport._dispatch(payload["method"], params)
            self._respond(200, {"jsonrpc": "2.0", "id": rid, "result": result})
        except JsonRpcError as exc:
            self._rpc_error(rid, exc.code, str(exc))
        except Exception as exc:  # surface internal errors as JSON-RPC errors
            self._rpc_error(rid, -32000, str(exc))