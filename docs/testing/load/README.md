# Load test — publish sustained throughput

**Plan:** Plan_Calidad.md Fase 2 | **Tool:** [k6](https://k6.io/)

## Objetivo

Validar **100 eventos/segundo sostenidos** en `POST /api/middleware/events/publish` (baseline de capacidad).

## Requisitos

- k6 instalado (`choco install k6` / `brew install k6`)
- Middleware en ejecución (`APP_URL`)
- Auth API deshabilitada **o** `PLATFORM_API_KEY` configurada

## Ejecución local

```bash
export APP_URL=http://127.0.0.1:8000
export PLATFORM_LOAD_TEST_EPS=100
export PLATFORM_LOAD_TEST_DURATION=60
bash scripts/ci/run-k6-load-test.sh
```

Variables opcionales:

| Variable | Default | Descripción |
|----------|---------|-------------|
| `PLATFORM_LOAD_TEST_EPS` | 100 | Eventos por segundo |
| `PLATFORM_LOAD_TEST_DURATION` | 60 | Segundos de prueba |
| `PLATFORM_API_KEY` | — | Header `X-API-Key` si auth habilitada |

## Umbrales (k6)

- `http_req_failed` < 5%
- `http_req_duration` p95 < 2000 ms

## CI

Workflow `.github/workflows/quality-load.yml` — `workflow_dispatch` y schedule semanal.
