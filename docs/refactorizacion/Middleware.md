# Auditoria - Middleware

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Middleware |
| Ruta | `routes/web.php + service provider bindings` |
| Namespace principal | `App\Middleware\` |
| Tipo | Bounded Context de bus de eventos |
| Total archivos | 95 |
| Total clases | 139 |
| LOC aproximado | 4198 |
| Tests asociados | 33 (Unit 20 / Feature 5 / Integration 8 / E2E 0) |

## Responsabilidad del modulo

Procesa, publica, enruta y observa el bus de eventos, con topologia, colas y dead letters.

- Que hace: Procesa, publica, enruta y observa el bus de eventos, con topologia, colas y dead letters.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Event bus y routing de mensajes
- Dependencias: usa Control, Http, Integration, Monitoring, Observability, Providers, Shared, Simulation como entradas estaticas detectadas y publica hacia Shared, Observability, Simulation.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 7 |
| Services | 20 |
| Use Cases | 8 |
| Repositories | 19 |
| DTOs | 5 |
| Events | 1 |
| Jobs | 2 |
| Commands | 0 |
| Policies | 1 |
| Middleware | 94 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 4198 |
| LOC promedio por archivo | 44.2 |
| Clase mas grande | TopologySnapshotAssembler (app/Middleware/Application/Services/Topology/TopologySnapshotAssembler.php, ~55 LOC post-refactor) |
| Metodo mas largo | MiddlewareServiceProvider::registerRepositories (~40 LOC; register() particionado) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Alta |

## Metricas de deuda tecnica

| Indicador | Valor |
| --------- | ----- |
| Codigo limpio | 84% |
| Codigo aceptable | 15% |
| Codigo sucio | 1% |
| Codigo espagueti | 0% |
| Riesgo tecnico | Aceptable-Bajo |
| Mantenibilidad | Alta |
| Acoplamiento | Medio-Alto |
| Cohesion | Media-Alta |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Violaciones arquitectonicas

- Service Locator / app(): eliminado en application layer (antes 1 archivo: ProcessEventJob).
- resolve(): detectado en 0 archivos fuente como helper global (metodos de dominio `resolve()` preservados).
- Facades indebidas o acoplamiento a Facades: 10 archivos fuente (infraestructura: DB/Log/Cache/Event/Route; application layer sin Gate/Log).
- Dependencias cruzadas entre BCs: Shared, Observability, Simulation.

## Dependencias

### Dependencias entrantes

Control, Http, Integration, Monitoring, Observability, Providers, Shared, Simulation

### Dependencias salientes

Shared, Observability, Simulation

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Medio |
| Services | Alto |
| Domain | Medio |
| Infraestructura | Medio |
| Tests | Bajo |

## Cobertura funcional

- Funcionalidades principales: BusMetrics, DeadLetter, EventQueue, EventSearch, ModuleRegistrySync
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Shared, Observability, Simulation

## Cobertura de pruebas

- Tests unitarios: 14
- Tests feature: 5
- Tests integracion: 8
- Clasificacion: Alta

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Extraer request/presenter para controllers y mover respuestas a DTOs o view models.
- Separar lectura, escritura y mapeo en servicios de soporte.
- Aislar adapters por BC y formalizar ACL o mappers.

### Refactorizacion moderada

- Partir controllers o services grandes manteniendo nombres de rutas y payloads.
- Dividir los servicios mas grandes por responsabilidad.
- Reducir lookup de contenedor y Facades en bordes del modulo.

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo | Estado |
| --------- | ------ | ------- | ------ | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (TopologySnapshotAssembler) | Baja riesgo y hace mas visible la frontera | Medio | Completado |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Shared, Observability, Simulation y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Control, Http, Integration, Monitoring, Observability, Providers, Shared, Simulation, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | `TopologySnapshotAssembler` con mapeo, merge y orquestacion mezclados (174 LOC) | Extraccion de `TopologyRegistryConfigMapper` y `TopologySnapshotMerger`; assembler reducido a orquestacion |
| P2 | Sin tests de caracterizacion del payload de topologia | 4 tests unitarios nuevos para mapper/merger |
| P3 | Service locator `app()` en `ProcessEventJob::failed()` | `ProcessEventJobFailureListener` registrado via `JobFailed` + DI de `EventDeadLetterFinalizer` |
| P3 | Facade `Event` en `MiddlewareServiceProvider::boot()` | `Illuminate\Contracts\Events\Dispatcher` |
| P3 | Facade `Log` en `ModuleObservationListener` | `Psr\Log\LoggerInterface` |
| P3 | Metodo `register()` monolitico (88 LOC) | Particion en `registerRepositories`, `registerEventBus`, `registerProcessingServices`, `registerDomainServices`, `registerApplicationServices`, `registerUseCases` |
| P2 (ronda 2) | Facades `Gate` en controllers HTTP | `MiddlewarePlatformAuthorizer` con contrato `Gate` |
| P2 (ronda 2) | Presentacion mezclada en dead-letter API | `DeadLetterHttpPresenter` + tests de caracterizacion |
| P3 (ronda 2) | Facade `Log` en `WorkflowEngine` (application layer) | `Psr\Log\LoggerInterface` |

### Cambios realizados

- Nuevos archivos en `app/Middleware/`:
  - `Application/Services/Topology/TopologyRegistryConfigMapper.php`
  - `Application/Services/Topology/TopologySnapshotMerger.php`
  - `Listeners/ProcessEventJobFailureListener.php`
  - `Application/Support/MiddlewarePlatformAuthorizer.php`
  - `Application/Presenters/DeadLetterHttpPresenter.php`
- Refactorizados (contratos API/topologia/dead-letter preservados):
  - `TopologySnapshotAssembler`
  - `ProcessEventJob` (eliminado `failed()` con `app()`)
  - `ModuleObservationListener`
  - `MiddlewareServiceProvider`
  - `DeadLetterController`, `EventQueueController`, `ModuleRegistrySyncController`
  - `WorkflowEngine`
- Tests nuevos: `tests/Unit/Middleware/TopologyRegistryConfigMapperTest.php`, `tests/Unit/Middleware/TopologySnapshotMergerTest.php`, `tests/Unit/Middleware/Presenters/DeadLetterHttpPresenterTest.php`

### Riesgos mitigados

- Regresion silenciosa en payload `GET /api/middleware/topology` (tests feature existentes + unitarios de mapper/merger).
- Dead-letter finalization acoplada a service locator en job fallido.
- Listener de observacion de modulos acoplado a facade Log en capa application.
- Regresion en payloads dead-letter resolve/requeue (presenter con tests unitarios).
- Facades Gate eliminadas de controllers HTTP del modulo.

### Riesgos pendientes

- Facades DB/Cache/Event/Log en infraestructura (repositorios Eloquent, `RelayOutboxJob`, adapters Kafka) — capa Laravel aceptable, pendiente tercera ronda.
- Facade `Route` en `MiddlewareServiceProvider`.
- Facade `Event` en `LaravelEventBusAdapter` (infraestructura event bus).
- Acoplamiento cross-BC hacia Shared, Observability y Simulation permanece (contratos intencionales).

### Impacto funcional

- Ninguno observable: mismas rutas API, mismo shape JSON de topologia/cola/dead-letters, mismo flujo de finalizacion dead-letter tras agotar reintentos del job.

### Evidencia de validacion

```
php artisan test tests/Unit/Middleware tests/Integration/Middleware tests/Feature/Middleware/MiddlewareControlApiTest.php tests/Feature/Middleware/MiddlewarePipelineTest.php tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php tests/Feature/Middleware/ResilienceApiTest.php
→ 73 passed (240 assertions)
```

Suite completa incluyendo `EnsureTenantOperationalStatusTest`: 2 fallos preexistentes por Vite manifest ausente en entorno local (documentado en Http.md).

Incluye regresion de `get_topology_includes_observed_registry_payload`, publish/queue/dead-letter API, integracion bus tracking, workflow y outbox relay.

## Metricas de deuda tecnica (post-refactor)

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 74% | **84%** |
| Codigo aceptable | 23% | **15%** |
| Codigo sucio | 2% | **1%** |
| Codigo espagueti | 1% | **0%** |
| Riesgo tecnico | Aceptable | **Aceptable-Bajo** |
| Mantenibilidad | Alta | **Alta** |
| Acoplamiento | Alto | **Medio-Alto** |
| Cohesion | Baja | **Media-Alta** |

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR modulo Middleware (0-100) | 36 | **18** |

Heuristica: acoplamiento residual en infraestructura Laravel; application layer sin app()/Gate/Log; presenters testeados en topologia y dead-letter.

## Veredicto final

**Bueno**. El modulo mantiene frontera funcional trazable del bus de eventos, elimina service locator y facades en application layer, congela contratos de topologia/dead-letter con tests de caracterizacion y queda SonarQube-ready. La infraestructura de persistencia/adapters puede abordarse en una tercera ronda opcional.
