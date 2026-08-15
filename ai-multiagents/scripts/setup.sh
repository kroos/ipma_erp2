#!/usr/bin/env bash
# Linux/macOS bootstrap: thin wrapper around scripts/setup.py
set -euo pipefail

if ! command -v python3 >/dev/null 2>&1; then
    echo "Python 3 is required but was not found on PATH." >&2
    exit 1
fi

python3 "$(dirname "$0")/setup.py"
