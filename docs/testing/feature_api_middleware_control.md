# Feature — API de control del middleware

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [feature_api_middleware_control.csv](./feature_api_middleware_control.csv)

---

## Objetivo

Verificar los **endpoints HTTP** del módulo de control middleware: registro/sincronización de configuración, publicación de eventos, cola, topología, consulta de estado de evento y enforcement de tenant operacional, alineados al contrato operativo PROC-001/002/003.

## Alcance

- **24 métodos** en `tests/Feature/Middleware/`:
  - `MiddlewareControlApiTest` (11)
  - `MiddlewarePipelineEndToEndTest` (4)
  - `MiddlewarePipelineTest` (2)
  - `ResilienceApiTest` (3)
  - `EnsureTenantOperationalStatusTest` (4)
- Regresiones B.2: sync + publicación + observabilidad vía HTTP.

## Precondiciones

- Aplicación Laravel bootstrapeada en tests Feature.
- `QUEUE_CONNECTION=sync`, SQLite `:memory:`.
- Rutas middleware registradas (`routes/api.php`, `MiddlewareApiRoutes`).

## Postcondiciones

- `POST /api/middleware/registry/sync-config` persiste módulos y es idempotente.
- `POST /api/middleware/events/publish` procesa envelopes válidos.
- `GET` cola, topología y evento por id retornan payloads esperados.
- Tenant suspendido bloquea API con Problem Details (RFC 7807).

## Casos

| Clase | Enfoque | Métodos | Resultado |
|-------|---------|---------|-----------|
| `MiddlewareControlApiTest` | CRUD operativo API | 11 | PASÓ |
| `MiddlewarePipelineEndToEndTest` | Flujo sync→publish→query | 4 | PASÓ |
| `MiddlewarePipelineTest` | Pipeline básico | 2 | PASÓ |
| `ResilienceApiTest` | Idempotency-key, replay | 3 | PASÓ |
| `EnsureTenantOperationalStatusTest` | Tenant suspendido | 4 | PASÓ |

Detalle: [feature_api_middleware_control.csv](./feature_api_middleware_control.csv).

### Flujo representativo

1. `POST /api/middleware/registry/sync-config` con `eventbus.*` y/o catálogo declarativo.
2. `POST /api/middleware/events/publish` con envelope.
3. `GET /api/middleware/queue`, `/topology`, `/events/{id}`.
4. Segunda sync sin romper consistencia.

## Criterios de aceptación

- Códigos HTTP y cuerpos JSON según aserciones de cada test.
- Persistencia de `registered_modules` tras sync.
- Evento visible en cola con estado `PROCESADO` tras dispatch sync.
- Idempotencia en sync repetido y publish con misma clave.
- API v1 espejo legacy cubierta en `tests/Feature/Api/V1RoutesMirrorLegacyTest` (documento separado en matriz maestra).

## Resultados

**2026-06-27:** 24/24 métodos Feature Middleware **PASÓ**.

Comando:

```bash
php vendor/bin/phpunit --testsuite Feature --filter Middleware
```

Suite global: 363 tests, 2 fallos fuera de este alcance (Platform seeding, Identity login).

## Observaciones

- `config()->set` en tests simula distintos clientes/instancias sin despliegue real.
- OpenAPI y Problem Details API v1 en `tests/Feature/Api/` complementan contrato HTTP.
- No se valida semántica de dominio en payloads; middleware transporta sobre opaco.

## Riesgos

| Riesgo | Impacto |
|--------|---------|
| Cambio breaking en rutas legacy/v1 | Integradores rotos |
| Auth API habilitada sin tests M2M | 401 en producción |
| Tenant suspendido mal detectado | Fuga operación en silo inactivo |

## Dependencias

- `docs/architecture/middleware_database_architecture.md`
- `docs/production/` runbooks de despliegue silo
- `docs/evaluation/02_Matriz_Middleware.csv`
- OpenAPI: `storage/api/openapi.yaml` (validado por `OpenApiContractTest`)

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| CSV Feature middleware | `feature_api_middleware_control.csv` |
| Catálogo Feature | `feature_catalogo_autogenerado.md` |
| JUnit | `docs/testing/tools/last_junit.xml` |

## Componentes

| Componente | Endpoint / rol |
|------------|----------------|
| `SyncConfiguredModulesToRegistryUseCase` | `POST .../registry/sync-config` |
| `EventPublisherService` | `POST .../events/publish` |
| `BusMetricsController` | `GET .../queue` |
| `TopologyController` | `GET .../topology` |
| `EnsureTenantOperationalStatus` | Middleware HTTP tenant |

## Trazabilidad BPMN

| Proceso | Documento |
|---------|-----------|
| PROC-001 Publicación eventos | [10_Proceso_Publicacion_Eventos_Bus.md](../Diagrama_BPMN/10_Proceso_Publicacion_Eventos_Bus.md) |
| PROC-002 Sync catálogo | [11_Proceso_Sincronizacion_Catalogo_Registry.md](../Diagrama_BPMN/11_Proceso_Sincronizacion_Catalogo_Registry.md) |
| PROC-003 Consulta bus | [12_Proceso_Consulta_Operativa_Bus.md](../Diagrama_BPMN/12_Proceso_Consulta_Operativa_Bus.md) |
| PROC-018 Multi-tenant | [27_Proceso_Multi_Tenancy_Logico_Fase3.md](../Diagrama_BPMN/27_Proceso_Multi_Tenancy_Logico_Fase3.md) |
| Flujo 5 etapas (ref.) | [26_Proceso_Flujo_Middleware_5_Etapas.md](../Diagrama_BPMN/26_Proceso_Flujo_Middleware_5_Etapas.md) |

Macroproceso: [02_Macroproceso_Operacion_Middleware_Eventos.md](../Diagrama_BPMN/02_Macroproceso_Operacion_Middleware_Eventos.md).
