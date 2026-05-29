# Auditoría — Observability (Métricas / Trazas / SLI)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Observability/` |
| **Namespace** | `App\Observability\` |
| **Tipo** | Bounded Context operacional |
| **Archivos PHP** | 13 |
| **LOC aprox.** | 381 |
| **Tests** | 14 (Unit 9 · Feature 4 · Integration 1) |

> **Última refactorización:** 2026-05-28 — pipeline Prometheus dividido, feed lag ACL, unit tests SLI/trazas.

## ¿Qué hace?

Exporta **métricas Prometheus**, registra **spans de trazas**, calcula **SLI** y rastrea conexiones de streaming. Capa técnica de observabilidad de la plataforma (no confundir con Dashboard, que es UX de observabilidad).

## ¿Para qué sirve?

- Endpoint `/metrics` (Prometheus scrape).
- Persistencia de trace logs vía `EloquentTraceLogRepository`.
- Middleware de correlación (`CorrelationIdMiddleware` alias `platform.correlation.id`).
- Soporte a runbooks y Grafana (`docs/observability/`).

## Estructura DDD (post-refactor)

```text
app/Observability/
├── Domain/
│   ├── ValueObjects/           TraceContext
│   └── Repositories/           TraceLogRepositoryInterface
├── Application/Services/
│   ├── Prometheus/             collector, renderer, feed lag ACL, snapshot VO
│   ├── PrometheusMetricsExporter orquestador delgado
│   ├── TraceSpanService
│   ├── SliMetricsRecorder
│   └── StreamConnectionTracker
├── Infrastructure/Persistence/ EloquentTraceLogRepository
└── Interfaces/
    ├── Http/Controllers/       PrometheusMetricsController
    └── Providers/              ObservabilityServiceProvider
```

| Capa | Archivos | Estado |
|------|----------|--------|
| Domain | 2 | ✅ VO trace + port persistencia |
| Application | 9 | ✅ Prometheus pipeline + spans + SLI |
| Infrastructure | 1 | ✅ Repo trace_logs |
| Interfaces | 2 | ✅ Controller + provider |

## Servicios extraídos en esta refactorización

| Servicio | Reemplaza lógica en |
|----------|---------------------|
| `PrometheusGaugeCollector` | Lectura multi-BC en `PrometheusMetricsExporter` |
| `PrometheusTextRenderer` | HELP/TYPE/lines + escape labels |
| `PrometheusGaugeSnapshot` | DTO de gauges scrapeados |
| `FeedProjectionLagCalculator` | `computeFeedProjectionLagMs()` + acoplamiento Dashboard model |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 12% | **8%** | Exporter ~23 LOC; sin clases >150 LOC |
| **% código espagueti** | 8% | **5%** | Collect → render; feed lag con dueño ACL |
| **Ratio tests/archivos** | 33% | **~108%** | +6 unit tests (renderer, collector, SLI, lag) |
| **Archivos >150 LOC** | 0 | **0** | Mayor: `PrometheusTextRenderer` ~56 LOC |

### Archivos más pesados (post-refactor)

| Archivo | LOC | Notas |
|---------|-----|-------|
| `PrometheusTextRenderer.php` | ~56 | Formato text/plain Prometheus |
| `TraceSpanService.php` | ~52 | Spans + correlación |
| `PrometheusGaugeCollector.php` | ~40 | Agrega gauges de Middleware/Monitoring/Dashboard |

## Resuelto en esta refactorización

1. ~~`PrometheusMetricsExporter` monolítico (~120 LOC)~~ → collector + renderer + exporter delgado.
2. ~~Feed lag mezclado con export~~ → `FeedProjectionLagCalculator` (ACL Dashboard).
3. ~~Gaps SLI / stream / formato métricas~~ → unit tests dedicados.

## Cosas sueltas / inconsistentes (restantes)

1. **OpenTelemetry no implementado** — ADR_009 describe futuro; código actual es Prometheus + trace log propio.
2. **Solapamiento conceptual con Dashboard** — métricas de bus en Dashboard y Prometheus; sin mapa único de source of truth.
3. **Provider registra middleware aliases** — mezcla bootstrap HTTP con dominio observability.
4. **`StreamConnectionTracker`** — contador estático en memoria; no cluster-safe.

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| Middleware | `BusHealthService`, repos cola/DLQ | ✅ Lectura vía interfaces |
| Monitoring | checkers + canary age | ✅ Bajo |
| Dashboard | `EventFeedEntryModel` (feed lag ACL) | ⚠️ Medio |
| Shared | logging, tenant context | ✅ Bajo |

## Cobertura de tests

- **Verificado (2026-05-28):** 14 tests Unit + Feature + Integration — todos pasan.
- **Presente:** endpoint `/metrics`, correlation ID, trace pipeline, renderer, collector, SLI recorder, feed lag.
- **Gaps:** `TraceSpanService` unit test aislado, stream tracker multi-request, regression snapshot métricas completo.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P3 | Mover registro de middleware HTTP a provider dedicado o Shared. |
| P3 | Documentar matriz métricas: Prometheus vs Dashboard vs Middleware API. |
| P4 | Plan migración OTel según ADR_009 (no urgente). |
| P4 | `StreamConnectionTracker` con backend Redis para multi-instancia. |

## Veredicto

**BC sano** tras refactor: export Prometheus con pipeline claro, trazas/SLI acotados, tests unitarios en componentes clave. Deuda restante menor (OTel, matriz métricas, tracker cluster-safe).
