# Auditoría — Simulation (Bounded Context)

| Campo | Valor |
|-------|-------|
| **Tipo** | **Bounded Context** — `app/Simulation/` |
| **Archivos PHP** | 39 |
| **LOC aprox.** | ~2 934 |
| **Tests** | 14 (handoff, worker, pulse, feature CP, provider bindings) |

## ¿Qué hace?

Orquesta **simulaciones de carga de tenant**: el control plane lanza una corrida, un worker en la instancia cliente publica eventos al bus, sincroniza progreso vía handoff/archivo y HTTP, drena cola en tiempo real, expone pulse para UI, y marca terminal COMPLETADA/FALLIDA.

## ¿Para qué sirve?

- Probar instancias cliente sin tráfico real.
- Validar middleware, dashboard y métricas bajo carga.
- Demo comercial y QA pre-producción (`docs/production/SimulacionClientes.md`).

## Estructura del BC

```text
app/Simulation/
├── Application/
│   ├── DTOs/SimulationRunExecutionResult.php
│   └── Services/
│       ├── Handoff/          HandoffStore, HandoffSync
│       ├── Worker/           Launcher, env factory, tenant bootstrap, monitor
│       ├── Orchestration/    Orchestrator, QueryService, stale guards, LocalFleet runner
│       ├── Execution/        ClientSilo executor, automation, fixture resolver, eligibility
│       ├── Progress/         CP client, reporter, completion, failure, internal API
│       ├── Metrics/          Baseline capture, queue analyzer, report builder, collector facade
│       ├── Prepare/          Instance prepare, diagnostics, readiness, tenant settings sync
│       ├── Reset/            Runs reset
│       └── Runtime/          Pulse, queue drainer, publish scope
├── Domain/ValueObjects/SimulationMessages.php
└── Interfaces/
    ├── Http/Controllers/     SimulationRun*, SimulationPulse
    └── Providers/SimulationServiceProvider.php
```

## Flujo (dueño único)

```text
Control Plane (:8000)
  SimulationRunController → SimulationRunQueryService
  SimulationRunOrchestrator → LocalFleetSimulationRunner / RunTenantSimulationJob
  HandoffStore / HandoffSync / WorkerLauncher
        │
        ▼ handoff JSON + HTTP internal API (SimulationRunInternalController)
Instance Client (:8001/:8002)
  ExecuteSimulationRunOnInstanceCommand → ExecuteSimulationRunOnInstanceService
  TenantSimulationAutomationService → ClientSiloSimulationExecutor
        │
        ▼ publish + drain (SimulationPulseService, SimulationQueueDrainer)
Middleware UI
  SimulationPulseController → pulse snapshot
Vue: Control/Simulation/*, Middleware/Index.vue (polling pulse)
```

## Métricas de deuda

| Indicador | Valor | Detalle |
|-----------|-------|---------|
| **% código sucio** | **9%** | Servicios <200 LOC; métricas y automation partidos; controllers delgados |
| **% código espagueti** | **7%** | Ciclo de vida en un BC; subcarpetas por responsabilidad; CP↔cliente documentado |
| **Dueño del BC** | ✅ `app/Simulation/` | Provider propio + manifest |

### Clusters de clases (post-refactor)

| Subcarpeta | Clases clave | LOC cluster ~ |
|------------|--------------|---------------|
| `Execution/` | Automation, ClientSilo, fixture/eligibility | ~650 |
| `Orchestration/` | Orchestrator, Query, LocalFleet, stale guards | ~520 |
| `Progress/` | CP client, completion, internal API | ~480 |
| `Metrics/` | Baseline, analyzer, report builder, facade | ~420 |
| `Worker/` + `Handoff/` | Launcher, handoff store/sync | ~450 |
| `Runtime/` | Pulse, drainer, publish scope | ~180 |
| `Interfaces/` | 3 controllers + provider | ~200 |

## Acoplamientos restantes (aceptables)

- **Control** — `SimulationRunModel`, `TenantModuleCatalogService`, dashboard modules (lectura CP).
- **Middleware** — `BusHealthService`, `EventProcessingService`, `SyncConfiguredModulesToRegistryUseCase`.
- **Shared** — fixtures, local fleet, `ClientSimulationService`.

## Cobertura de tests

- **Bueno:** handoff store/sync, worker monitor, pulse unit, internal API feature, run report E2E CP, provider bindings.
- **Gaps:** worker `.bat` en CI, concurrencia handoff multi-instancia automatizada.

## Veredicto

**Refactor completado (2026-05-28).** Simulation es BC formal con provider, controllers y servicios agrupados. Deuda residual: Domain anémico (solo `SimulationMessages`), acoplamiento CP/Middleware vía ports futuros.
