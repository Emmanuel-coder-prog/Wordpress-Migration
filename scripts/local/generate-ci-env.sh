#!/usr/bin/env bash
set -Eeuo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <output-file>" >&2
  exit 1
fi

output="$1"

if [[ ! -f ".env.example" ]]; then
  echo ".env.example not found" >&2
  exit 1
fi

# Preserve only valid environment assignment lines for Compose.
grep -E '^[[:space:]]*[A-Za-z_][A-Za-z0-9_]*=' .env.example >"${output}"
chmod 0644 "${output}"
