# Observabilidad — Middleware Omnicanal

**Plan:** `Plan_Observabilidad.md` | **Estado:** Implementado (Fases 1–3)

---

## Three Pillars

| Pilar | Implementación |
|-------|----------------|
| **Logs** | Ver `Plan_Logs.md` — JSON logging, `event_logs`, `audit_logs` |
| **Métricas** | `observability_metrics`, endpoint `/metrics` (Prometheus), SLIs en `config/platform_slos.php` |
| **Trazas** | `trace_logs` + `correlation_id` en HTTP → cola → feed |

---

## Correlación HTTP → Pipeline

```
Cliente HTTP (X-Correlation-ID)
    → CorrelationIdMiddleware
    → EventPublisherService (message_queue.correlation_id)
    → BusTrackingListener (bus.track span)
    → EventFeedProjector (event_feed_projections.correlation_id + feed.project span)
    → trace_logs
```

### Headers

| Header | Dirección | Descripción |
|--------|-----------|-------------|
| `X-Correlation-ID` | Request/Response | UUID v4; generado si ausente |
| `X-Causation-ID` | Request (opcional) | Evento padre en cadenas causales |

---

## Endpoint Prometheus

```
GET /metrics
Content-Type: text/plain; version=0.0.4
```

Métricas exportadas:

| Métrica | Descripción |
|---------|-------------|
| `bus_events_published_total` | Eventos en cola (ventana 1h) |
| `bus_processing_latency_ms` | Latencia media de procesamiento |
| `bus_dlq_unresolved` | Entradas DLQ sin resolver |
| `feed_projection_lag_ms` | Lag promedio feed (received − occurred) |
| `sse_stream_connections_active` | Conexiones SSE activas del dashboard |

Label común: `client="{PLATFORM_CLIENT_SLUG}"`

---

## SLIs / SLOs por instancia

Configuración: `config/platform_slos.php`

| SLI | Variable env | Default |
|-----|--------------|---------|
| Latencia bus p99 | `PLATFORM_SLO_BUS_LATENCY_P99_MS` | 2000 ms |
| DLQ máximo | `PLATFORM_SLO_DLQ_MAX` | 10 |
| Lag feed p99 | `PLATFORM_SLO_FEED_LAG_P99_MS` | 3000 ms |
| SSE conexiones máx | `PLATFORM_SLO_SSE_MAX_CONNECTIONS` | 500 |

---

## Grafana

Dashboard JSON en repo:

```
docs/observability/grafana/middleware_dashboard.json
```

Importar en Grafana → Dashboards → Import → subir JSON. Datasource Prometheus debe scrapear `http://<instancia>/metrics`.

---

## Variables de entorno

| Variable | Default | Descripción |
|----------|---------|-------------|
| `PLATFORM_PROMETHEUS_ENABLED` | `true` | Habilita `/metrics` |
| `PLATFORM_TRACE_SPANS_ENABLED` | `true` | Escribe spans en `trace_logs` |
| `PLATFORM_OBSERVABILITY_SERVICE_NAME` | `PLATFORM_CLIENT_SLUG` | Nombre de servicio en spans |

---

## OpenTelemetry (Fase 3)

Ver `ADR_009_opentelemetry_distributed_tracing.md`.

En esta fase se adopta **trazabilidad ligera** (`trace_logs` + correlation ID) compatible con export OTel futuro vía collector sidecar, sin SDK PHP embebido en runtime.

---

## Arquitectura DDD

- BC **Observability** (`app/Observability/`) — Supporting Domain
- Listeners de Middleware/Dashboard invocan servicios de observabilidad; no invierten dependencias de negocio
- Read models existentes alimentan el exporter Prometheus

---

## Validación local

```bash
composer test
composer analyse
curl -s http://localhost/metrics
```

Tras publicar un evento, verificar:

```sql
SELECT operation_name, correlation_id FROM trace_logs ORDER BY created_at DESC LIMIT 10;
SELECT correlation_id FROM event_feed_projections WHERE event_uuid = '<uuid>';
```
