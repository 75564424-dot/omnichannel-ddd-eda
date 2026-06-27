# Integration — flujo productor → bus → trazas

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [integration_flujo_eventos_bus.csv](./integration_flujo_eventos_bus.csv)

---

## Objetivo

Validar el **camino interno** desde la publicación de un evento hasta el estado persistido en cola del bus, proyecciones de dashboard y logs de auditoría, con límites claros entre capa de aplicación e infraestructura (desacoplamiento DDD).

## Alcance

- **17 métodos** Integration:
  - `tests/Integration/Middleware/` — 8 clases, 14 métodos
  - `tests/Integration/Dashboard/` — 3 clases, 3 métodos
  - `tests/Integration/Logging/` — 1 clase, 2 métodos (incluidos por trazabilidad)
- **Excluido de este doc pero en suite:** `tests/Integration/Platform/` (1 fallo activo), `tests/Integration/Observability/`.

## Precondiciones

- SQLite en memoria con migraciones de bus aplicadas.
- `QUEUE_CONNECTION=sync` para dispatch determinista.
- Listeners de tracking y dashboard registrados en contenedor IoC.

## Postcondiciones

- Fila en `bus_queue_entries` con transición a procesado tras listeners sync.
- Idempotencia por `event_id` (constraint / skip segundo dispatch).
- Dashboard observa ping/métricas sin acoplar bounded contexts ajenos.
- Logs de evento y auditoría persistidos.

## Casos

| Clase | Métodos | Resultado |
|-------|---------|-----------|
| `EventPublisherServiceIntegrationTest` | 2 | PASÓ |
| `EventStoreIdempotencyIntegrationTest` | 1 | PASÓ |
| `BusTrackingDirectDispatchIntegrationTest` | 2 | PASÓ |
| `BusTrackingListenerDependencyBoundaryTest` | 1 | PASÓ |
| `OutboxRelayIntegrationTest` | 1 | PASÓ |
| `WorkflowEngineIntegrationTest` | 1 | PASÓ |
| `SubscriptionRegistryAndBusRegistrationIntegrationTest` | 2 | PASÓ |
| `ModuleRegistryObservationIntegrationTest` | 2 | PASÓ |
| `PlatformPingObservedByDashboardIntegrationTest` | 1 | PASÓ |
| `GetGlobalMetricsUsesDashboardRepositoriesTest` | 1 | PASÓ |
| `DashboardFeedListenersDependencyBoundaryTest` | 1 | PASÓ |
| `EventAndAuditLogServiceTest` | 2 | PASÓ |

Detalle: [integration_flujo_eventos_bus.csv](./integration_flujo_eventos_bus.csv).

### Flujo probado

1. Resolver `EventPublisherService`.
2. Publicar envelope `Platform.*` válido.
3. Verificar `bus_queue_entries`, `event_store`, topología observada.
4. Confirmar listeners dashboard/logging sin dependencias de dominios externos.

## Criterios de aceptación

- Segundo publish con mismo `event_id` es idempotente.
- Outbox relay marca publicado y entrega al bus.
- Wildcard `Platform.*` listeners registrados cuando catálogo core vacío.
- Constructor de `BusTrackingListener` excluye capas Application de otros BC.

## Resultados

**2026-06-27:** 17/17 métodos de este alcance **PASÓ**.

Suite Integration completa: 21 métodos — **21 PASÓ** (2026-06-24).

Comando:

```bash
php vendor/bin/phpunit --testsuite Integration
```

## Observaciones

- Semántica de eventual consistency en producción (colas async) difiere de `sync` en tests; documentado en matriz middleware.
- `TraceLogsPipelineIntegrationTest` (Observability) cubre PROC-013 en suite Integration ampliada.
- Payloads de prueba usan tipos `Platform.*` genéricos, no dominios retail legacy.

## Riesgos

| Riesgo | Impacto |
|--------|---------|
| Fallo seeding tenant_id | Multi-tenant incompleto en `message_queue` |
| Listeners no registrados tras refactor DI | Eventos huérfanos |
| Outbox deshabilitado en prod sin tests | Pérdida de garantía entrega |

## Dependencias

- `docs/architecture/middleware_database_dictionary.md`
- `docs/architecture/er_diagram.md`
- Tablas: `bus_queue_entries`, `event_store`, `registered_modules`, `event_feed_entries`
- `docs/evaluation/04_Matriz_Observabilidad.csv`

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| CSV Integration bus | `integration_flujo_eventos_bus.csv` |
| Catálogo Integration | `integration_catalogo_autogenerado.md` |
| JUnit | `docs/testing/tools/last_junit.xml` |

## Componentes

| Componente | Rol |
|------------|-----|
| `EventPublisherService` | Fachada publicación |
| `BusTrackingListener` | Persistencia cola |
| `UniversalDashboardFeedListener` | Proyección feed |
| `OutboxRelay` | Modo outbox → bus |
| `EventAndAuditLogService` | Trazas auditables |
| `WorkflowEngine` | Jobs processing post-publish |

## Trazabilidad BPMN

| Proceso | Documento |
|---------|-----------|
| PROC-001 Publicación eventos | [10_Proceso_Publicacion_Eventos_Bus.md](../Diagrama_BPMN/10_Proceso_Publicacion_Eventos_Bus.md) |
| PROC-002 Sync catálogo | [11_Proceso_Sincronizacion_Catalogo_Registry.md](../Diagrama_BPMN/11_Proceso_Sincronizacion_Catalogo_Registry.md) |
| PROC-004 Dashboard | [13_Proceso_Observabilidad_Dashboard.md](../Diagrama_BPMN/13_Proceso_Observabilidad_Dashboard.md) |
| PROC-013 Monitoreo | [22_Proceso_Monitoreo_Alertas_Plataforma.md](../Diagrama_BPMN/22_Proceso_Monitoreo_Alertas_Plataforma.md) |

Macroproceso: [02_Macroproceso_Operacion_Middleware_Eventos.md](../Diagrama_BPMN/02_Macroproceso_Operacion_Middleware_Eventos.md), [03_Macroproceso_Observabilidad_Monitoreo.md](../Diagrama_BPMN/03_Macroproceso_Observabilidad_Monitoreo.md).
