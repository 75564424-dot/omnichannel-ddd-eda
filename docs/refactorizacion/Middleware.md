# Auditoría — Middleware (Event Bus)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Middleware/` |
| **Namespace** | `App\Middleware\` |
| **Tipo** | Bounded Context principal |
| **Archivos PHP** | 99 |
| **LOC aprox.** | 4 374 |
| **Controllers** | 7 API + 1 web (`Interfaces/Http/`) |
| **Tests** | 67 (Unit 15 · Feature 4 · Integration 8 · E2E 2) |

> **Última refactorización:** 2026-05-28 — processing/publish/topology/registry divididos; Simulation en subcarpeta.

## ¿Qué hace?

Implementa el **bus de eventos** de la plataforma: publicación, cola FIFO, procesamiento, outbox, dead-letter queue (DLQ), registro de módulos productor/consumidor, topología del bus, métricas de latencia/EPS/error rate, validación de esquemas y motor de workflows.

Es el núcleo EDA sobre el que se apoyan Dashboard (lectura) e Integration (ingress externo).

## ¿Para qué sirve?

- Desacoplar productores y consumidores declarados en `config/modules/`.
- Persistir y enrutar eventos entre nodos de una instancia cliente.
- Exponer APIs REST (`/api/middleware/*`) y vistas Inertia (`/middleware`).
- Soportar operaciones de plataforma: sync de registry, DLQ retry, health del bus.

## Estructura DDD (post-refactor)

```text
app/Middleware/
├── Domain/                    entidades, VOs, TopologyService, repos interfaces
├── Application/
│   ├── Services/
│   │   ├── Processing/        dispatch planner, attempt executor, DLQ finalizer
│   │   ├── Publish/           envelope validator, schema resolver, idempotency
│   │   ├── Topology/          TopologySnapshotAssembler
│   │   ├── Registry/          ConfiguredModuleRegistrySyncService
│   │   └── Simulation/        pulse, scope, queue drainer
│   └── UseCases/              delgados; delegan a servicios
├── Infrastructure/            Eloquent, jobs, event bus adapters
└── Interfaces/Http/Controllers/
```

| Capa | Archivos | Estado |
|------|----------|--------|
| Domain | 30 | ✅ Sólida |
| Application | 35 | ✅ Subcarpetas por concern |
| Infrastructure | 22 | ✅ |
| Interfaces | 10 | ✅ Controllers delgados |
| Listeners | 2 | ✅ |

## Servicios extraídos en esta refactorización

| Servicio | Subcarpeta | Reemplaza lógica en |
|----------|------------|---------------------|
| `EventProcessingDispatchPlanner` | Processing | outbox/async/sync en `EventProcessingService` |
| `EventProcessingAttemptExecutor` | Processing | `executeAttempt`, `publishToBus` |
| `EventDeadLetterFinalizer` | Processing | `finalizeDeadLetter` |
| `PublishEnvelopeValidator` | Publish | validación estructural en publisher |
| `PublishEnvelopeSchemaResolver` | Publish | defaults de schema |
| `EventPublishIdempotencyGuard` | Publish | deduplicación por event_id |
| `TopologySnapshotAssembler` | Topology | merge + ensamblado en use case |
| `ConfiguredModuleRegistrySyncService` | Registry | sync eventbus + modules catalog |
| `Simulation/*` | Simulation | reorganización namespace (3 clases) |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 18% | **10%** | Sin servicios monolíticos >160 LOC |
| **% código espagueti** | 12% | **7%** | Pipeline publish/process con dueños claros |
| **Ratio tests/archivos** | 31% | **~34%** | +2 unit tests publish validator |
| **Archivos >150 LOC** | 5 | **1** | `TopologySnapshotAssembler` ~175 (merge puro) |

### Archivos más pesados (post-refactor)

| Archivo | LOC | Notas |
|---------|-----|-------|
| `TopologySnapshotAssembler.php` | ~175 | Merge config + observed topology |
| `EventQueueController.php` | ~131 | API cola (sin cambio) |
| `EventPublisherService.php` | ~95 | Orquestación publish |
| `EventProcessingService.php` | ~75 | Fachada processing |
| `MiddlewareServiceProvider.php` | ~150 | Wiring (sin split) |

## Resuelto en esta refactorización

1. ~~`EventProcessingService` monolítico~~ → Processing/* (3 servicios) + fachada.
2. ~~`EventPublisherService` denso~~ → Publish/* (validator, schema, idempotency).
3. ~~`GetTopologySnapshotUseCase` con merge inline~~ → `TopologySnapshotAssembler`.
4. ~~`SyncConfiguredModulesToRegistryUseCase` largo~~ → `ConfiguredModuleRegistrySyncService`.
5. ~~Simulation suelta en Services/~~ → `Application/Services/Simulation/`.

## Cosas sueltas / inconsistentes (restantes)

1. **Simulation capability** — aún vive en Middleware BC (subcarpeta); BC formal pendiente (`Simulation.md`).
2. **Frontend monolítico** — `Middleware/Index.vue` (702 LOC).
3. **Status mapping duplicado** — PHP `MessageQueueStatusMapper` vs Vue `normalizeQueueStatus`.
4. **2 feature tests frágiles** — `producer_bindings` cuenta catálogo declarativo además de eventbus config (pre-existente).

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| `App\Shared\Contracts\EventBus` | Contratos | ✅ Bajo |
| Dashboard | Solo eventos/listeners indirectos | ✅ Bajo |
| Control | Consumo vía APIs/use cases | ✅ Medio-bajo |
| Integration | Publica vía `EventPublisherService` | ✅ Bajo |

**No importa** `App\Dashboard` ni `App\Control` directamente — buena frontera.

## Cobertura de tests

- **Verificado (2026-05-28):** 65/67 tests Middleware pass (2 feature assertions frágiles en registry sync count).
- **Fuerte:** cola, DLQ, publisher, topology, registry sync, simulation pulse/defer, outbox relay.
- **Gaps:** `WorkflowEngine` E2E completo; stress outbox relay; OpenAPI contract en todos los endpoints.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P2 | BC Simulation formal con ports hacia Middleware publish/process. |
| P3 | Composables Vue: `useMiddlewareQueue`, `useTopologyFlow`, `useSimulationPulse`. |
| P3 | DTO único para status cola (backend ↔ frontend). |
| P4 | Split `MiddlewareServiceProvider` temático (persistence, simulation, api). |

## Veredicto

**Módulo más sano del proyecto**, reforzado con subcarpetas Application por concern. Deuda restante: Simulation como capability externa, UI Vue y provider wiring extenso.
