# Plan de Observabilidad

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto

---

## 1. Estado Actual

### Qué existe

- `observability_metrics` — métricas unificadas (bus scope)
- APIs: `/api/middleware/metrics`, `/api/dashboard/metrics/*`, `/middleware/bus`
- Listeners: `MiddlewareMetricsListener`, `BusMetricsService`
- Value objects: `BusStatus`, `LatencyMs`, `ErrorRate`, `StreamStatus`
- Tablas: `trace_logs`, `audit_logs` — **sin escritura desde código**
- Logs vía `Log::` facade → `storage/logs/laravel.log`
- Guía: `docs/personal_notes/Observabilidad_pruebas_produccion_local.md`

### Qué está incompleto

- Sin correlation ID en HTTP requests
- Sin export Prometheus/OpenTelemetry
- `event_store` no alimenta trazabilidad canónica
- Sin dashboards Grafana/CloudWatch en repo
- SSE stream sin métricas de conexiones activas

### Riesgos

| Riesgo | Severidad |
|--------|-----------|
| Imposible correlacionar evento HTTP → cola → feed | **Alto** |
| MTTR alto en incidentes | **Alto** |
| Métricas solo en BD — no alerting externo | **Medio** |

---

## 2. Objetivo

Observabilidad **three pillars** para middleware omnicanal:

1. **Métricas** — throughput, latencia, error rate, queue depth
2. **Trazas** — correlation_id, span por etapa del pipeline
3. **Logs** — estructurados, searchable (ver Plan_Logs.md)

---

## 3. Problemas Detectados

1. Duplicación histórica resuelta (`observability_metrics`) pero sin exporter
2. Dashboard y Middleware escriben métricas con `dimensions.source` distinto — correcto pero no unificado en UI externa
3. No hay SLOs definidos (ej. p99 latency < 2s)

---

## 4. Requerimientos

- [ ] Middleware `CorrelationIdMiddleware` — header `X-Correlation-ID`
- [ ] Propagar correlation a `message_queue`, `event_feed_projections`
- [ ] Escribir spans en `trace_logs` en publish/track/project
- [ ] Endpoint `/metrics` Prometheus (opcional package)
- [ ] OpenTelemetry PHP SDK (fase 3)
- [ ] Documentar SLOs por instancia

---

## 5. Propuesta Técnica

### Flujo de correlación

```
HTTP Publish (correlation_id) → event_store → message_queue → listeners → event_feed_projections
                                      ↓
                                 trace_logs (spans)
```

### Métricas clave (SLI)

| Métrica | Fuente |
|---------|--------|
| `bus_events_published_total` | message_queue insert rate |
| `bus_processing_latency_ms` | observability_metrics |
| `bus_dlq_unresolved` | dead_letter_queue count |
| `feed_projection_lag_ms` | received_at - occurred_at |

### DDD

- Observabilidad como **Supporting Domain** — listeners de infra no invaden Application de negocio
- Read models existentes consumidos por exporters

---

## 6. Roadmap

### Fase 1: Correlation ID + structured context en logs
### Fase 2: trace_logs wiring + Grafana dashboard JSON en repo
### Fase 3: OpenTelemetry + distributed tracing cross-service

---

## 7. Prioridad

**Alto**

---

## 8. Riesgo si no se implementa

Incidentes en producción sin diagnóstico rápido; imposible demostrar SLA a clientes enterprise; debugging manual vía SQL.

---

## Referencias

- [Plan_Monitoreo.md](Plan_Monitoreo.md)
- [Plan_Logs.md](Plan_Logs.md)
- [Plan_Middleware.md](Plan_Middleware.md)
