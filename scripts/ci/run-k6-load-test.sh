#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
APP_URL="${APP_URL:-http://127.0.0.1:8000}"
SCRIPT="${ROOT}/docs/testing/load/k6_publish_sustained.js"

if ! command -v k6 >/dev/null 2>&1; then
  echo "k6 is not installed. See docs/testing/load/README.md"
  exit 1
fi

export APP_URL
export PLATFORM_LOAD_TEST_EPS="${PLATFORM_LOAD_TEST_EPS:-100}"
export PLATFORM_LOAD_TEST_DURATION="${PLATFORM_LOAD_TEST_DURATION:-60}"

echo "Running k6 load test against ${APP_URL} (${PLATFORM_LOAD_TEST_EPS} eps, ${PLATFORM_LOAD_TEST_DURATION}s)..."

k6 run "$SCRIPT"
