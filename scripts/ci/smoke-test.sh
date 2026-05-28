#!/usr/bin/env bash
set -euo pipefail

# Smoke test post-deploy (Plan CI/CD + Plan_Seguridad).
# Requires APP_URL (default http://127.0.0.1:8080).
# When PLATFORM_API_AUTH_ENABLED=true, set PLATFORM_API_KEY.

APP_URL="${APP_URL:-http://127.0.0.1:8080}"
APP_URL="${APP_URL%/}"

CURL_AUTH=()
if [ -n "${PLATFORM_API_KEY:-}" ]; then
  CURL_AUTH=(-H "X-API-Key: ${PLATFORM_API_KEY}")
fi

echo "==> Smoke: liveness /up"
curl -fsS "${APP_URL}/up" >/dev/null

echo "==> Smoke: readiness /health/ready"
curl -fsS "${APP_URL}/health/ready" | grep -q '"ready"'

echo "==> Smoke: bus status at ${APP_URL}/api/middleware/status"
curl -fsS "${CURL_AUTH[@]}" "${APP_URL}/api/middleware/status" | grep -q '"success"'

echo "==> Smoke: registry sync-config"
curl -fsS -X POST "${APP_URL}/api/middleware/registry/sync-config" \
  "${CURL_AUTH[@]}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" | grep -q '"success"'

if command -v uuidgen >/dev/null 2>&1; then
  EVENT_ID="$(uuidgen)"
elif [ -r /proc/sys/kernel/random/uuid ]; then
  EVENT_ID="$(cat /proc/sys/kernel/random/uuid)"
else
  EVENT_ID="00000000-0000-4000-8000-000000000001"
fi
OCCURRED="$(date -u +%Y-%m-%dT%H:%M:%SZ 2>/dev/null || date -u +%Y-%m-%dT%H:%M:%S+00:00)"
EVENT_TYPE="Platform.Smoke.Probe"

echo "==> Smoke: publish event ${EVENT_ID}"
curl -fsS -X POST "${APP_URL}/api/middleware/events/publish" \
  "${CURL_AUTH[@]}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"event_id\":\"${EVENT_ID}\",\"event_type\":\"${EVENT_TYPE}\",\"occurred_at\":\"${OCCURRED}\",\"payload\":{\"event_id\":\"${EVENT_ID}\",\"event\":\"${EVENT_TYPE}\",\"occurred_at\":\"${OCCURRED}\",\"ref\":\"CI-SMOKE\"}}" \
  | grep -q '"success"'

echo "==> Smoke: lookup event by id"
curl -fsS "${APP_URL}/api/middleware/events/${EVENT_ID}" \
  "${CURL_AUTH[@]}" \
  -H "Accept: application/json" | grep -q "${EVENT_ID}"

echo "Smoke tests passed."
