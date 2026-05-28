#!/usr/bin/env bash
set -euo pipefail

# Post-deploy client simulation smoke (Plan_SimulacionClientes.md)
# Usage: CLIENT_SLUG=retailco EVENTS=10 bash scripts/ops/simulate-client-smoke.sh

CLIENT_SLUG="${CLIENT_SLUG:-retailco}"
EVENTS="${EVENTS:-10}"
APP_URL="${APP_URL:-http://127.0.0.1:8080}"
APP_URL="${APP_URL%/}"

echo "==> Health checks"
curl -fsS "${APP_URL}/up" >/dev/null
curl -fsS "${APP_URL}/health/ready" | grep -q '"ready"'

echo "==> Validate catalog"
php artisan platform:validate-catalog

echo "==> Simulate client ${CLIENT_SLUG} (${EVENTS} events)"
php artisan platform:simulate-client "${CLIENT_SLUG}" --events="${EVENTS}"

echo "==> API smoke"
bash scripts/ci/smoke-test.sh

echo "Client simulation smoke passed for ${CLIENT_SLUG}."
