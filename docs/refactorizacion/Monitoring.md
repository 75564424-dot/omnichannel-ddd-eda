# Auditoría — Monitoring (Alertas / Canary)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Monitoring/` |
| **Namespace** | `App\Monitoring\` |
| **Tipo** | Bounded Context operacional |
| **Archivos PHP** | 17 |
| **LOC aprox.** | 563 |
| **Tests** | 15 (Unit 12 · Feature 3) |

> **Última refactorización:** 2026-05-28 — evaluadores aislados, canary dividido, umbrales tipados, reporter de consola.

## ¿Qué hace?

Evalúa **condiciones de alerta** (métricas del bus, infraestructura, cola, capacidad BD), ejecuta **canary publish** al bus y expone commands Artisan para operaciones de monitoreo programado.

## ¿Para qué sirve?

- `EvaluateMonitoringAlertsCommand` — chequeos periódicos / cron.
- `CanaryPublishCommand` — verificación sintética del pipeline de eventos.
- Alimenta UX de incidentes en Control (`ControlIncidentsService`, `IncidentDiagnosticCollector`).
- Métricas Prometheus vía `PrometheusMetricsExporter` (error rate, canary age, checkers).

## Estructura DDD (post-refactor)

```text
app/Monitoring/
├── Domain/ValueObjects/          MonitoringAlert, AlertSeverity
├── Application/Services/
│   ├── Evaluators/               BusMetrics, BusStopped, Infrastructure
│   ├── Canary/                   envelope, queue verifier, success tracker
│   ├── AlertEvaluationService    orquestador delgado
│   ├── CanaryPublishService      orquestador canary
│   ├── MonitoringAlertThresholds lectura tipada de config
│   ├── MonitoringAlertsConsoleReporter
│   ├── DatabaseCapacityChecker
│   └── QueueDepthChecker
└── Interfaces/
    ├── Commands/                 evaluate + canary
    └── Providers/                MonitoringServiceProvider
```

| Capa | Archivos | Estado |
|------|----------|--------|
| Domain | 2 | ✅ VOs de alerta |
| Application | 13 | ✅ Evaluadores + canary + checkers |
| Interfaces | 3 | ✅ Commands + provider |

## Servicios extraídos en esta refactorización

| Servicio | Reemplaza lógica en |
|----------|---------------------|
| `MonitoringAlertThresholds` | Lectura dispersa de `platform_monitoring.alerts` |
| `BusMetricsAlertEvaluator` | error rate, latency, DLQ en `AlertEvaluationService` |
| `BusStoppedAlertEvaluator` | tracking cache bus STOPPED |
| `InfrastructureAlertEvaluator` | disk + queue en orquestador |
| `CanaryProbeEnvelopeFactory` | construcción envelope en `CanaryPublishService` |
| `CanaryQueueCompletionVerifier` | query `message_queue` post-publish |
| `CanarySuccessTracker` | cache último éxito canary |
| `MonitoringAlertsConsoleReporter` | output + logging en command evaluate |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 20% | **9%** | Sin clases >150 LOC; evaluadores testeables |
| **% código espagueti** | 15% | **7%** | Orquestadores delgados; reglas con dueño único |
| **Ratio tests/archivos** | 22% | **~88%** | +10 unit tests (evaluators, canary, thresholds) |
| **Archivos >150 LOC** | 1 | **0** | Mayor: `MonitoringAlertsConsoleReporter` ~56 LOC |

### Archivos más pesados (post-refactor)

| Archivo | LOC | Notas |
|---------|-----|-------|
| `MonitoringAlertsConsoleReporter.php` | ~56 | Output CLI + structured log |
| `QueueDepthChecker.php` | ~52 | Redis + database drivers |
| `DatabaseCapacityChecker.php` | ~51 | SQLite + MySQL |
| `AlertEvaluationService.php` | ~45 | Solo orquestación |

## Resuelto en esta refactorización

1. ~~`AlertEvaluationService` monolítico (~138 LOC)~~ → 3 evaluadores + orquestador ~45 LOC.
2. ~~Sin tests unitarios de checkers/evaluators~~ → 12 unit + 3 feature.
3. ~~Umbrales mezclados con lógica~~ → `MonitoringAlertThresholds` centralizado.
4. ~~`CanaryPublishService` con múltiples responsabilidades~~ → factory + verifier + tracker.

## Cosas sueltas / inconsistentes (restantes)

1. **Acoplamiento Control** — incidentes dependen de `AlertEvaluationService` directamente; sin evento `AlertRaised`.
2. **Canary vs simulación** — dos mecanismos de evento sintético sin guía única en runbook.
3. **`DatabaseCapacityChecker`** — sin unit test aislado de MySQL (solo vía evaluator + sqlite en tests).

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| Middleware | `BusHealthService`, `EventPublisherService` | ✅ Lectura/publicación vía servicios |
| Control | Consumo en incidentes / overview | ⚠️ Medio (sin port) |
| Observability | `SliMetricsRecorder`, Prometheus exporter | ✅ Bajo |
| Shared | Logging | ✅ Bajo |

## Cobertura de tests

- **Verificado (2026-05-28):** 15 tests Unit + Feature Monitoring — todos pasan.
- **Presente:** evaluators (bus metrics, stopped, infra), thresholds, canary envelope/verifier, commands feature.
- **Gaps:** `DatabaseCapacityChecker` MySQL path, test de reporter P1 vs P2 en CLI.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P3 | Publicar domain events de alerta en lugar de llamadas directas desde Control. |
| P3 | Documentar relación Canary vs Simulation en `docs/monitoring/`. |
| P4 | Port `AlertEvaluationInterface` para desacoplar Control. |

## Veredicto

**BC sano** tras refactor: evaluadores aislados, canary con pipeline claro, commands delgados. Deuda restante menor (acoplamiento Control, documentación Canary vs Simulation).
