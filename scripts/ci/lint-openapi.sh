#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
SPEC="${ROOT}/docs/api/openapi.yaml"

if ! command -v npx >/dev/null 2>&1; then
  echo "npx not found — skip OpenAPI Spectral lint"
  exit 0
fi

echo "Linting OpenAPI spec with Spectral..."
npx --yes @stoplight/spectral-cli lint "$SPEC" --ruleset "${ROOT}/docs/api/spectral.yaml"
