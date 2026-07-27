#!/usr/bin/env bash
set -Eeuo pipefail

output="${1:-environments/local/.env}"
mkdir -p "$(dirname "$output")"

if [[ ! -f ".env.example" ]]; then
  echo ".env.example not found" >&2
  exit 1
fi

cp .env.example "$output"
chmod 0644 "$output"

