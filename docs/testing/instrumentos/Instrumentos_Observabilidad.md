# Instrumento — Observabilidad (REQ-O*, tracing, Prometheus)

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Fuente requisitos:** [requerimientos.csv](../../Patente/matriz_generada/requerimientos.csv) (REQ-O1–O5, REQ-ADR008–009)  
**Matriz evaluation:** [docs/evaluation/04_Matriz_Observabilidad.csv](../../evaluation/04_Matriz_Observabilidad.csv) (C13–C15)

## 1. Propósito

Vincular objetivos de observabilidad del dashboard y del plan de monitoreo con pruebas de logs estructurados, correlación, métricas Prometheus y endpoints de dashboard.

## 2. Resumen REQ-O

| ID | Objetivo | Estado | Tests | Instrumento |
|----|----------|--------|-------|-------------|
| REQ-O1 | Feed con event_id | Implementado | DashboardEndpointsTest, Integration feed | C13 logs |
| REQ-O2 | KPIs desde dashboard_config | Implementado | DashboardEndpointsTest series | C14 métricas |
| REQ-O3 | Métricas bus latencia/EPS/cola | Implementado | LatencyMs*, Prometheus* | C14 SLI |
| REQ-O4 | Topología declarativa+observada | Parcial | DashboardEndpointsTest modules catalog | C15 trazas |
| REQ-O5 | SSE stream vivo | PENDIENTE_VALIDACION | StreamConnectionTrackerTest (unit) | Brecha UI |

## 3. Tracing y ADR-009

- **CorrelationIdMiddleware** — `CorrelationIdMiddlewareTest`
- **trace_logs pipeline** — `TraceLogsPipelineIntegrationTest`
- **OpenTelemetry completo** — diferido (ADR-009 implementado parcial)

## 4. Prometheus

- Endpoint `/metrics` — `PrometheusMetricsEndpointTest`
- Renderer/collector unit — `PrometheusTextRendererTest`, `PrometheusGaugeCollectorTest`
- Config — `docs/monitoring/prometheus/*.yml`, Grafana JSON en `docs/observability/grafana/`

## 5. CSV

[Instrumentos_Observabilidad.csv](./Instrumentos_Observabilidad.csv)

## 6. Ejecución

```bash
php vendor/bin/phpunit tests/Feature/Observability tests/Integration/Observability tests/Unit/Observability tests/Unit/Logging
```
