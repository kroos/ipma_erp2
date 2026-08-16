"""Hallucination guard for generated docs (doc_writer).

The Doc Writer brain occasionally invents code symbols that do not exist
(field names in `$fillable`-style arrays, route names, config keys, class
names — observed 2026-08-15: the finding-6 doc claimed whitelists like
`['start_date', 'end_date', 'leave_type', 'status']` that match nothing in
the codebase). This module verifies every code reference a generated doc
makes against the actual Laravel project before the doc is written.

Design notes:
- `DocVerifier` builds a lightweight symbol index (classes, route names,
  config keys, env keys, views, DB columns) from the project once, then
  checks doc claims against it. Claims are extracted from *code contexts*
  only (backticks, fenced blocks, explicit call syntax) so prose words are
  never flagged.
- Verification is **auto-disabled when the project root is not a Laravel
  root** (no `artisan`/`composer.json`), matching `config_loader`.
  DocWriter smoke tests use temp dirs and are therefore unaffected;
  production runs always verify.
- The guard is deliberately conservative on *method* names (inherited
  model methods like `find()` are legal even when not declared in the
  class file) but strict on classes, routes, config/env keys, views,
  columns and file paths — those are where invented identifiers appear.
"""

from __future__ import annotations

import re
import subprocess
from functools import lru_cache
from pathlib import Path

from . import config_loader

# Laravel framework facades/aliases and common global classes that docs may
# reference without a local definition — always considered valid.
FRAMEWORK_ALIASES = {
    "App", "Arr", "Artisan", "Auth", "Blade", "Broadcast", "Bus", "Cache",
    "Carbon", "Collection", "Config", "Cookie", "Crypt", "DB", "Date",
    "Eloquent", "Event", "Exception", "File", "Gate", "Hash", "Http",
    "Lang", "Log", "Mail", "Model", "Notification", "Password", "Queue",
    "Redirect", "Redis", "Request", "Response", "Route", "Schema",
    "Session", "Storage", "Str", "URL", "Validator", "View",
    "Controller", "TestCase", "Middleware", "Job", "Seeder", "Migration",
    "ServiceProvider", "Request", "Builder", "Collection", "Form",
    "Html", "Blade", "ExceptionHandler", "ConsoleKernel", "HttpKernel",
    "Application", "UrlGenerator", "Dispatcher", "ValidationException",
    "AuthorizationException", "ModelNotFoundException",
}

# Columns Laravel adds implicitly (timestamps, soft deletes, etc.).
IMPLICIT_COLUMNS = {
    "id", "created_at", "updated_at", "deleted_at",
    "remember_token", "email_verified_at",
}

# Project dirs a doc may reference as a file path.
ROOT_DIRS = (
    "app", "routes", "config", "database", "resources",
    "tests", "storage", "public", "bootstrap",
)

# Generic template placeholders docs use in prose examples (e.g. Laravel's
# `route('name')` / `config('key')`). These are not real claims.
PLACEHOLDER_NAMES = {
    "name", "key", "value", "path", "module", "action", "route",
    "index", "store", "update", "show", "create", "edit", "destroy",
    "id", "home", "login", "logout", "register", "dashboard",
}

_ROUTE_RE = re.compile(r"\broute\s*\(\s*(['\"])([\w.\-]+)\1\s*\)")
_CONFIG_RE = re.compile(r"\bconfig\s*\(\s*(['\"])([\w.*\-]+)\1\s*\)")
_ENV_RE = re.compile(r"\benv\s*\(\s*(['\"])([A-Za-z_][\w]*)\1\s*\)")
_VIEW_RE = re.compile(
    r"\b(?:view|@include|@includeIf|@extends|@component)\s*"
    r"\(\s*(['\"])([\w.\/\-]+)\1\s*\)"
)
# class references in unambiguous code contexts only:
#   Foo::bar(, Foo::$prop, Foo::class, Foo@bar, new Foo, instanceof Foo
_CLASS_CALL_RE = re.compile(
    r"\b([A-Z][A-Za-z0-9]*)(?:::|@)(?:\w+|\$\w+)\s*"
    r"(?:\(|;|\)|\s|$)"
)
_CLASS_STATIC_RE = re.compile(r"\b([A-Z][A-Za-z0-9]*)::class\b")
_NEW_CLASS_RE = re.compile(r"\bnew\s+([A-Z][A-Za-z0-9]*)\b")
_INSTANCEOF_RE = re.compile(r"\binstanceof\s+([A-Z][A-Za-z0-9]*)\b")
_PATH_RE = re.compile(
    r"\b(" + "|".join(ROOT_DIRS) + r")/[\w.\/\-]+\.(?:php|blade\.php|js|json|css|md)\b"
)
_SNAKE_RE = re.compile(r"^[a-z][a-z0-9_]*$")
_STUDLY_RE = re.compile(r"^[A-Z][A-Za-z0-9]*$")
_WHERE_COL_RE = re.compile(
    r"\b(?:where|whereIn|whereNotIn|whereBetween|whereColumn|"
    r"orderBy|groupBy|pluck|value|having)\s*\(\s*(['\"])([\w]+)\1"
)
# `Class::$fillable = ['a', 'b']` — field list tied to a specific model
_MODEL_FILLABLE_RE = re.compile(
    r"\b([A-Z][A-Za-z0-9]*)::\$(?:fillable|guarded|casts|hidden|appends|dates)"
    r"\s*=\s*\[([^\]]*)\]"
)
_TABLE_COL_RE = re.compile(
    r"\$table\s*->\s*\w+\s*\(\s*(['\"])([\w]+)\1"
)

_FENCE_RE = re.compile(r"(```|~~~)")
_BACKTICK_RE = re.compile(r"`([^`]+)`")
_ARRAY_LITERAL_RE = re.compile(r"\[([^\]]*)\]")
_STRING_ITEM_RE = re.compile(r"(['\"])([^'\"]+?)\1")


class UnverifiedClaim:
    """One doc claim that could not be verified against the codebase."""

    __slots__ = ("doc_path", "kind", "value", "reason")

    def __init__(self, doc_path: str, kind: str, value: str, reason: str):
        self.doc_path = doc_path
        self.kind = kind
        self.value = value
        self.reason = reason

    def __str__(self) -> str:
        return (f"doc '{self.doc_path}': unverified {self.kind} "
                f"'{self.value}' — {self.reason}")


class DocVerifier:
    """Verify generated doc claims against the Laravel project."""

    def __init__(self, project_root: Path | None = None,
                 enabled: bool | None = None):
        self.project_root = Path(
            project_root or config_loader.PROJECT_ROOT
        )
        # auto-detect: verify only against a real Laravel root (smoke tests
        # use temp dirs and must not block on an empty index)
        if enabled is None:
            enabled = config_loader.is_laravel_root(self.project_root)
        self.enabled = enabled
        self._index: dict | None = None

    # ---- index ----------------------------------------------------------

    def _build_index(self) -> None:
        idx = {
            "classes": set(),      # short class names defined in app/
            "routes": set(),       # named routes
            "config": {},          # {file_stem: set(top-level keys)}
            "env": set(),          # env keys from .env.example / .env
            "views": set(),        # dotted blade view names
            "columns": set(IMPLICIT_COLUMNS),
            "model_fields": {},    # {ClassName: set(fillable/guarded/casts)}
        }
        root = self.project_root

        # classes defined in app/ + tests/ + the ai-multiagents infra
        # (changelogs legitimately reference test and agent-infra classes)
        for base in (root / "app", root / "tests",
                     root / "ai-multiagents" / "src"):
            if not base.is_dir():
                continue
            for path in base.rglob("*.php"):
                try:
                    text = path.read_text(encoding="utf-8", errors="replace")
                except OSError:
                    continue
                for match in re.finditer(
                        r"^\s*(?:final\s+|abstract\s+)?class\s+(\w+)",
                        text, re.MULTILINE):
                    idx["classes"].add(match.group(1))

        # named routes
        for path in (root / "routes").rglob("*.php"):
            try:
                text = path.read_text(encoding="utf-8", errors="replace")
            except OSError:
                continue
            for match in re.finditer(
                    r"->name\s*\(\s*(['\"])([\w.\-]+)\1\s*\)", text):
                idx["routes"].add(match.group(2))

        # config keys per file ('key' => anywhere in the file)
        for path in (root / "config").glob("*.php"):
            try:
                text = path.read_text(encoding="utf-8", errors="replace")
            except OSError:
                continue
            keys = set(re.findall(r"['\"](\w+)['\"]\s*=>", text))
            idx["config"][path.stem] = keys

        # env keys (allow commented-out lines: '#KEY=value' in .env.example
        # still documents a real key — e.g. SESSION_SECURE_COOKIE)
        for env_file in (".env.example", ".env"):
            p = root / env_file
            if not p.is_file():
                continue
            try:
                text = p.read_text(encoding="utf-8", errors="replace")
            except OSError:
                continue
            for match in re.finditer(
                    r"^\s*#?\s*([A-Za-z_][\w]*)\s*=", text, re.MULTILINE):
                idx["env"].add(match.group(1))

        # blade views -> dotted names (resources/views/a/b.blade.php => a.b)
        views_dir = root / "resources" / "views"
        if views_dir.is_dir():
            for path in views_dir.rglob("*.blade.php"):
                rel = path.relative_to(views_dir).with_suffix("")
                idx["views"].add(str(rel).replace("\\", "/").replace("/", "."))

        # DB columns from migrations + model fillable/guarded/casts arrays
        for path in (root / "database" / "migrations").glob("*.php"):
            try:
                text = path.read_text(encoding="utf-8", errors="replace")
            except OSError:
                continue
            for match in _TABLE_COL_RE.finditer(text):
                idx["columns"].add(match.group(2))
        for path in (root / "app").rglob("*.php"):
            try:
                text = path.read_text(encoding="utf-8", errors="replace")
            except OSError:
                continue
            # class name in this file (for per-model field verification)
            file_class = None
            m = re.search(r"^\s*(?:final\s+|abstract\s+)?class\s+(\w+)",
                          text, re.MULTILINE)
            if m:
                file_class = m.group(1)
            for match in re.finditer(
                    r"\$(?:fillable|guarded|casts|hidden|appends|dates)"
                    r"\s*=\s*\[([^\]]*)\]", text):
                fields = set()
                for item in _STRING_ITEM_RE.findall(match.group(1)):
                    value = item[1].strip()
                    if _SNAKE_RE.match(value):
                        fields.add(value)
                        idx["columns"].add(value)
                if file_class and fields:
                    idx["model_fields"].setdefault(file_class, set()).update(fields)

        self._index = idx

    def _indexed(self) -> dict:
        if self._index is None:
            self._build_index()
        return self._index

    # ---- claim extraction (code contexts only) --------------------------

    @staticmethod
    def _strip_comments(content: str) -> str:
        """Remove // line comments and /* */ block comments so claims in
        commented-out code are not treated as live code references.
        """
        lines = [line.split("//", 1)[0] for line in content.split("\n")]
        cleaned = "\n".join(lines)
        return re.sub(r"/\*.*?\*/", "", cleaned, flags=re.DOTALL)

    def _code_spans(self, content: str) -> list[str]:
        """Return code-context chunks: fenced blocks and backtick spans,
        with comment lines stripped (a doc quoting commented-out code is
        describing stale code, not claiming the symbols exist).
        """
        spans: list[str] = []
        # fenced blocks (``` or ~~~)
        parts = _FENCE_RE.split(content)
        in_fence = False
        for part in parts:
            if part in ("```", "~~~"):
                in_fence = not in_fence
            elif in_fence:
                spans.append(part)
        # inline backtick spans
        for match in _BACKTICK_RE.finditer(content):
            spans.append(match.group(1))
        stripped = []
        for span in spans:
            lines = [
                line.split("//", 1)[0] for line in span.split("\n")
            ]
            cleaned = "\n".join(lines)
            cleaned = re.sub(r"/\*.*?\*/", "", cleaned, flags=re.DOTALL)
            stripped.append(cleaned)
        return stripped

    def _extract_claims(self, content: str) -> list[tuple[str, str]]:
        """Extract (kind, value) claims from doc content.

        Kinds: route, config, env, view, class, file, column.
        Only explicit code references are claimed — prose words never are.
        """
        claims: list[tuple[str, str]] = []

        # strip comment lines for non-file claims: a doc quoting
        # commented-out code is describing stale code, not claiming the
        # symbols exist (e.g. "// $au = SalesGetItem::where(...)")
        code = self._strip_comments(content)
        for match in _ROUTE_RE.finditer(code):
            if match.group(2) not in PLACEHOLDER_NAMES:
                claims.append(("route", match.group(2)))
        for match in _CONFIG_RE.finditer(code):
            if match.group(2) not in PLACEHOLDER_NAMES:
                claims.append(("config", match.group(2)))
        for match in _ENV_RE.finditer(code):
            if match.group(2) not in PLACEHOLDER_NAMES:
                claims.append(("env", match.group(2)))
        for match in _VIEW_RE.finditer(code):
            if match.group(2) not in PLACEHOLDER_NAMES:
                claims.append(("view", match.group(2)))
        for match in _CLASS_CALL_RE.finditer(code):
            claims.append(("class", match.group(1)))
        for match in _CLASS_STATIC_RE.finditer(code):
            claims.append(("class", match.group(1)))
        for match in _NEW_CLASS_RE.finditer(code):
            claims.append(("class", match.group(1)))
        for match in _INSTANCEOF_RE.finditer(code):
            claims.append(("class", match.group(1)))
        for match in _PATH_RE.finditer(content):
            claims.append(("file", match.group(0)))

        # code-context checks (backticks / fenced blocks only)
        for span in self._code_spans(content):
            # class references inside code spans (static calls, ::class,
            # new, instanceof) — but skip path/FQCN segments (e.g.
            # `App\Models\Foo` — namespace segments are not class refs)
            for regex in (_CLASS_CALL_RE, _CLASS_STATIC_RE,
                          _NEW_CLASS_RE, _INSTANCEOF_RE):
                for match in regex.finditer(span):
                    claims.append(("class", match.group(1)))
            # `Class::$fillable = [...]` — verify each field against the
            # model's declared fillable (the finding-6 hallucination:
            # invented whitelist names on HRLeave)
            for match in _MODEL_FILLABLE_RE.finditer(span):
                klass = match.group(1)
                for item in _STRING_ITEM_RE.findall(match.group(2)):
                    value = item[1].strip()
                    if _SNAKE_RE.match(value):
                        claims.append(("model_field", f"{klass}::{value}"))
            # column-ish patterns inside code spans
            for match in _WHERE_COL_RE.finditer(span):
                claims.append(("column", match.group(2)))
            for match in _TABLE_COL_RE.finditer(span):
                claims.append(("column", match.group(2)))
            # pure field-list array literals: ['a', 'b'] (no key=>value).
            # Skip variable subscripts like `$g['ll']` — a `[` preceded by
            # an identifier/`>`/`]` is array ACCESS, not a field list.
            for match in _ARRAY_LITERAL_RE.finditer(span):
                start = match.start()
                prev = span[start - 1] if start > 0 else ""
                if prev.isalnum() or prev in ("$", ">", "]", ")", "_"):
                    continue
                body = match.group(1)
                if "=>" in body:
                    continue
                for item in _STRING_ITEM_RE.findall(body):
                    value = item[1].strip()
                    if _SNAKE_RE.match(value):
                        claims.append(("column", value))

        return claims

    # ---- verification ---------------------------------------------------

    def _check(self, kind: str, value: str) -> tuple[bool, str]:
        idx = self._indexed()
        if kind == "route":
            if value in idx["routes"]:
                return True, ""
            return False, "no such named route"
        if kind == "config":
            segs = value.rstrip(".*").split(".")
            first = segs[0]
            if first not in idx["config"]:
                return False, f"no config file '{first}.php'"
            if len(segs) > 1:
                keys = idx["config"].get(first, set())
                if segs[1] not in keys:
                    return False, f"config('{first}') has no key '{segs[1]}'"
            return True, ""
        if kind == "env":
            if value in idx["env"]:
                return True, ""
            return False, "no such env key (checked .env.example/.env)"
        if kind == "view":
            dotted = value.replace("/", ".").lstrip(".")
            if dotted in idx["views"] or value in idx["views"]:
                return True, ""
            return False, "no such blade view"
        if kind == "class":
            if value in FRAMEWORK_ALIASES or value in idx["classes"]:
                return True, ""
            # classes deleted by a documented migration (e.g. legacy
            # AjaxControllers) are still legitimate references in history
            if self._class_in_git_head(value):
                return True, ""
            return False, "no such class in app/"
        if kind == "file":
            if (self.project_root / value).is_file():
                return True, ""
            # docs may abbreviate a path (e.g. omit the sub-namespace dir);
            # accept when the basename exists anywhere under the same root
            # segment (app/, routes/, ...)
            seg = value.split("/", 1)[0]
            base_dir = self.project_root / seg
            basename = value.rsplit("/", 1)[-1]
            if base_dir.is_dir() and any(
                    p.name == basename for p in base_dir.rglob("*")
                    if p.is_file()):
                return True, ""
            # known-but-deleted files (e.g. legacy files removed by a
            # migration the doc describes) are legitimate references
            if self._in_git_head(value):
                return True, ""
            return False, "no such file in the project"
        if kind == "column":
            if value in idx["columns"]:
                return True, ""
            return False, "no such column (checked migrations + model fillables)"
        if kind == "model_field":
            klass, field = value.split("::", 1)
            declared = idx["model_fields"].get(klass, set())
            if field in declared:
                return True, ""
            if not declared:
                return False, (f"no declared $fillable/$guarded on {klass} "
                               "to verify against")
            return False, (f"'{field}' is not in {klass}'s declared "
                           "$fillable/$guarded/$casts")
        return True, ""  # unknown kind: never block

    def verify_doc(self, doc: dict) -> list[UnverifiedClaim]:
        """Return unverified claims for one doc (empty = verified)."""
        if not self.enabled:
            return []
        doc_path = str(doc.get("path") or "?")
        content = str(doc.get("content") or "")
        claims = self._extract_claims(content)
        out: list[UnverifiedClaim] = []
        seen: set[tuple[str, str]] = set()
        for kind, value in claims:
            if (kind, value) in seen:
                continue
            seen.add((kind, value))
            ok, reason = self._check(kind, value)
            if not ok:
                out.append(UnverifiedClaim(doc_path, kind, value, reason))
        return out

    def verify_payload(self, payload: dict) -> list[UnverifiedClaim]:
        """Verify all docs in a payload; empty list means fully verified."""
        out: list[UnverifiedClaim] = []
        for doc in payload.get("docs") or []:
            out.extend(self.verify_doc(doc))
        return out

    @lru_cache(maxsize=None)
    def _in_git_head(self, rel: str) -> bool:
        """True when `rel` is tracked in the git HEAD tree (allows docs to
        reference files that were recently deleted). No git repo -> False.
        """
        try:
            proc = subprocess.run(
                ["git", "-C", str(self.project_root),
                 "cat-file", "-e", f"HEAD:{rel}"],
                capture_output=True, timeout=10,
            )
            return proc.returncode == 0
        except (OSError, subprocess.SubprocessError):
            return False

    @lru_cache(maxsize=None)
    def _class_in_git_head(self, short: str) -> bool:
        """True when a class with this name is defined somewhere in the git
        HEAD tree (allows docs to reference classes that a documented
        migration deleted, e.g. legacy AjaxControllers).
        """
        try:
            proc = subprocess.run(
                ["git", "-C", str(self.project_root),
                 "grep", "-E", "-l",
                 f"class\\s+{short}\\b", "HEAD"],
                capture_output=True, timeout=15,
            )
            return proc.returncode == 0
        except (OSError, subprocess.SubprocessError):
            return False
