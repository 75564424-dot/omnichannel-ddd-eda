# Matriz de validación — middleware y arquitectura

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [matrix_validacion_middleware.csv](./matrix_validacion_middleware.csv)

---

## Objetivo

Proveer una vista única de **criterios de arquitectura** del middleware genérico (bus observable, sin lógica de negocio de dominio en publicación) y su trazabilidad hacia la suite automatizada `tests/*`, alineada con ADR-001 (Control Plane + silos por cliente) y los procesos BPMN documentados.

## Alcance

- Repositorio **omnichannel-ddd-eda** en estado actual: plataforma SaaS con middleware, dashboard, control plane, observabilidad y multi-tenancy lógico.
- **15 criterios** arquitectónicos mapeados a clases representativas (ver CSV).
- **Excluye** flujos omnicanal retail legacy (`Inventario.Events`, `Pedido.Events`) eliminados del código; no se validan con esta matriz.
- Suite total: **362 métodos** de test / **363 ejecuciones PHPUnit** (2026-06-27).

## Precondiciones

- Documentación de referencia disponible: `docs/architecture/Architecture_Blueprint.md`, `docs/Diagrama_BPMN/Matriz_Trazabilidad_BPMN.md`, `docs/evaluation/Middleware_Acceptance_Evaluation_Framework.md`.
- Entorno PHPUnit: `QUEUE_CONNECTION=sync`, SQLite en memoria (`phpunit.xml`).
- Evidencia JUnit reciente en `docs/testing/tools/last_junit.xml`.

## Postcondiciones

- Cada criterio CRIT-01…CRIT-15 tiene al menos una clase representativa con tests activos.
- Ausencias explícitas quedan registradas en backlog QA (p. ej. PROC-017 pipeline 5 etapas retail — solo documental).
- Matriz maestra `matriz_maestra_casos.csv` mantiene trazabilidad fina método a método.

## Casos

| ID | Criterio | Clase representativa | Tests | Resultado |
|----|----------|---------------------|-------|-----------|
| CRIT-01 | Desacoplamiento productor-consumidor | `EventPublisherServiceIntegrationTest` | 2 | PASÓ |
| CRIT-02 | Rol middleware sin lógica de negocio | `ClientProductionLikeSimulationTest` | 1 | PASÓ |
| CRIT-03 | Propagación bus y cola | `BusTrackingDirectDispatchIntegrationTest` | 2 | PASÓ |
| CRIT-04 | Config declarativa ↔ presentación | `ConfigModulesCatalogPresentationTest` | 2 | PASÓ |
| CRIT-05 | sync-config idempotente | `MiddlewarePipelineEndToEndTest` | 4 | PASÓ |
| CRIT-06 | API control cola/topología/publish | `MiddlewareControlApiTest` | 11 | PASÓ |
| CRIT-07 | Coherencia config-ejecución-dashboard | `MiddlewarePipelineEndToEndTest` | 4 | PASÓ |
| CRIT-08 | Reutilización por cliente/instancia | `MultiClientFixtureSimulationTest` | 1 | PASÓ |
| CRIT-09 | Idempotencia event_store | `EventStoreIdempotencyIntegrationTest` | 1 | PASÓ |
| CRIT-10 | Registro suscripciones y bus | `SubscriptionRegistryAndBusRegistrationIntegrationTest` | 2 | PASÓ |
| CRIT-11 | Outbox relay | `OutboxRelayIntegrationTest` | 1 | PASÓ |
| CRIT-12 | Resiliencia publish/idempotency-key | `ResilienceApiTest` | 3 | PASÓ |
| CRIT-13 | Tenant operacional en middleware | `EnsureTenantOperationalStatusTest` | 4 | PASÓ |
| CRIT-14 | Observabilidad dashboard desde bus | `PlatformPingObservedByDashboardIntegrationTest` | 1 | PASÓ |
| CRIT-15 | Validación catálogo CI | `ValidatePlatformCatalogTest` | 4 | PASÓ |

Detalle completo en [matrix_validacion_middleware.csv](./matrix_validacion_middleware.csv).

## Criterios de aceptación

- Todo criterio CRIT-* con `Resultado_Agregado = PASÓ` en el CSV.
- Ningún criterio middleware crítico (PROC-001, PROC-002, PROC-003) sin cobertura Feature o Integration.
- Coherencia con definición de middleware: transporte, enrutado y trazabilidad; payloads opacos por `event_type`.

## Resultados

**Ejecución PHPUnit 2026-06-27** (`docs/testing/tools/last_junit.xml`):

| Métrica | Valor |
|---------|-------|
| Tests ejecutados | 363 |
| Aserciones | 1 198 |
| Pasaron | 361 |
| Fallaron | 2 |
| Errores | 0 |

Los **15 criterios de esta matriz** están en verde (clases representativas PASÓ). Suite global PHPUnit: **364/364 OK** (2026-06-24).

Comando de verificación:

```bash
php vendor/bin/phpunit
```

## Observaciones

- Los catálogos auto-generados (`*_catalogo_autogenerado.md`) detallan **cada método**; esta matriz prioriza **criterios de negocio/arquitectura**.
- Documentación legacy `docs/Modulos/Modulo_Control_Middleware.md` y `Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md` complementan pero la trazabilidad oficial BPMN está en `docs/Diagrama_BPMN/`.
- Conteo histórico **160 tests (2026-05-22)** quedó obsoleto; suite actual **362 métodos**.

## Riesgos

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Fallos en seeding tenant (`message_queue.tenant_id`) | Integración multi-tenant incompleta | Corregir `InstanceTenantSeedingIntegrationTest` |
| PROC-017 (pipeline 5 etapas retail) sin tests | Brecha documentación vs código | Mantener como referencia externa; fixtures `Platform.*` |
| `QUEUE_CONNECTION=sync` en tests vs colas async en prod | Falsa confianza en latencia | Pruebas de carga k6 (`load/README.md`) |

## Dependencias

- `composer test` / `.github/workflows/ci.yml`
- `config/modules/modules_config.json` y `config/modules.php`
- Migraciones SQLite de bus: `bus_queue_entries`, `event_store`, `registered_modules`
- Generador: `php docs/testing/tools/export_test_matrix.php`

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| JUnit última corrida | `docs/testing/tools/last_junit.xml` |
| Matriz maestra (362 casos) | `docs/testing/matriz_maestra_casos.csv` |
| Matriz criterios (este doc) | `docs/testing/matrix_validacion_middleware.csv` |
| CI workflow | `.github/workflows/ci.yml` |

## Componentes

| Componente | Rol en validación |
|------------|-------------------|
| `EventPublisherService` | Publicación y ciclo cola |
| `SyncConfiguredModulesToRegistryUseCase` | sync-config declarativo |
| `BusMetricsController`, `TopologyController` | API consulta PROC-003 |
| `UniversalDashboardFeedListener` | Proyección feed PROC-004 |
| `ValidatePlatformCatalogCommand` | Gate CI catálogo PROC-016 |
| `ConfigModulesCatalogDataProvider` | Presentación catálogo dashboard |

## Trazabilidad BPMN

| Proceso | Documento BPMN | Criterios CRIT |
|---------|----------------|----------------|
| PROC-001 Publicación eventos | [10_Proceso_Publicacion_Eventos_Bus.md](../Diagrama_BPMN/10_Proceso_Publicacion_Eventos_Bus.md) | CRIT-01, 02, 03, 09, 11, 12 |
| PROC-002 Sync catálogo | [11_Proceso_Sincronizacion_Catalogo_Registry.md](../Diagrama_BPMN/11_Proceso_Sincronizacion_Catalogo_Registry.md) | CRIT-05, 10 |
| PROC-003 Consulta bus | [12_Proceso_Consulta_Operativa_Bus.md](../Diagrama_BPMN/12_Proceso_Consulta_Operativa_Bus.md) | CRIT-06, 12 |
| PROC-004 Dashboard | [13_Proceso_Observabilidad_Dashboard.md](../Diagrama_BPMN/13_Proceso_Observabilidad_Dashboard.md) | CRIT-07, 14 |
| PROC-009 Simulación E2E | [18_Proceso_Simulacion_Cliente_E2E.md](../Diagrama_BPMN/18_Proceso_Simulacion_Cliente_E2E.md) | CRIT-08 |
| PROC-016 Validación catálogo | [25_Proceso_Validacion_Catalogo_CI.md](../Diagrama_BPMN/25_Proceso_Validacion_Catalogo_CI.md) | CRIT-04, 15 |
| PROC-018 Multi-tenant Fase 3 | [27_Proceso_Multi_Tenancy_Logico_Fase3.md](../Diagrama_BPMN/27_Proceso_Multi_Tenancy_Logico_Fase3.md) | CRIT-13 |

Referencia transversal: [Matriz_Trazabilidad_BPMN.md](../Diagrama_BPMN/Matriz_Trazabilidad_BPMN.md), [26_Proceso_Flujo_Middleware_5_Etapas.md](../Diagrama_BPMN/26_Proceso_Flujo_Middleware_5_Etapas.md) (documental).
