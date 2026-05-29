# Auditoría — Console (Comandos CLI)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Console/Commands/` |
| **Namespace** | `App\Console\Commands\{Simulation,Platform,Ops,Demo,Security}\` |
| **Tipo** | Interface CLI (operaciones) |
| **Archivos PHP (commands)** | 19 |
| **LOC commands ~** | 659 |
| **Servicios extraídos** | 10 (Control + Shared/Platform) |
| **Tests** | ~4 indirectos |

> **Última refactorización:** 2026-05-28 — commands delgados + lógica en application services.

## ¿Qué hace?

Expone **operaciones de plataforma vía Artisan**: bootstrap fleet local, simulación cliente, ejecución de runs en instancia, reset operacional, validación catálogo, purge retención, demo events, tokens API, ensure tenant, etc.

## ¿Para qué sirve?

- DevOps local (`npm run instances:*` suele invocar estos commands).
- Workers de simulación (`platform:simulation:execute-run`).
- Mantenimiento BD y resets entre pruebas.
- Validación CI de configs.

## Estructura actual (post-refactor)

```text
app/Console/Commands/
├── Simulation/     ExecuteSimulationRun, ResetRuns, Prepare, SimulateClient
├── Platform/         Fleet bootstrap/sync/prune, instance bootstrap, catalog
├── Ops/              Reset operational, reset demo identity, purge retention
├── Demo/             Dashboard demo events, emit mock
└── Security/         Issue/list/rotate/revoke API tokens
```

**Patrón:** cada command solo parsea argumentos, imprime salida y delega en un **application service** del BC correspondiente (Control o Shared/Platform).

## Servicios extraídos (lógica fuera de CLI)

| Servicio | BC | Reemplaza lógica en |
|----------|-----|---------------------|
| `ExecuteSimulationRunOnInstanceService` | Control | `ExecuteSimulationRunOnInstanceCommand` |
| `SimulationRunsResetService` | Control | `ResetSimulationRunsCommand` |
| `SimulationInstancePrepareService` | Control | `PrepareSimulationCommand` |
| `ControlPlaneFleetBootstrapService` | Shared/Platform | `BootstrapControlPlaneFleetCommand` |
| `ClientInstanceBootstrapService` | Shared/Platform | `BootstrapClientInstanceCommand` |
| `LocalFleetSyncService` | Shared/Platform | `SyncLocalFleetInstancesCommand` |
| `LocalFleetOrphanPruner` | Shared/Platform | `PruneLocalFleetClientsCommand` |
| `OperationalDataResetService` | Shared/Platform | `ResetOperationalDemoDataCommand` |
| `DemoIdentityResetService` | Shared/Platform | `ResetDemoIdentityCommand` |
| `DashboardDemoEventsEmitter` | Shared/Platform | `EmitDashboardDemoEventsCommand` |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 28% | **12%** | Sin commands >150 LOC; I/O separada de orquestación |
| **% código espagueti** | 24% | **10%** | Flujo simulation execute centralizado en un service |
| **Archivos >150 LOC** | ~3 | **0** | El más largo: `SimulateClientCommand` (~87 LOC) |
| **LOC total commands** | ~1 245 | **~659** | −47% en capa CLI |

### Commands más largos (post-refactor)

| Archivo | LOC |
|---------|-----|
| `Simulation/SimulateClientCommand.php` | 87 |
| `Simulation/PrepareSimulationCommand.php` | ~55 |
| `Simulation/ExecuteSimulationRunOnInstanceCommand.php` | ~48 |
| Resto | ≤60 |

## Resuelto en esta refactorización

1. ~~`ExecuteSimulationRunOnInstanceCommand` orquestador pesado~~ → `ExecuteSimulationRunOnInstanceService`.
2. ~~Commands sin subcarpetas~~ → agrupados por dominio (Simulation, Platform, Ops, Demo, Security).
3. ~~Lógica inline en bootstrap/reset/prune~~ → services testables en Shared/Platform y Control.
4. ~~Código muerto en fleet bootstrap (`demoRetailCompanies` vacío)~~ → eliminado.
5. ~~`app()` en `SyncLocalFleetInstancesCommand`~~ → `LocalFleetSyncService` con DI.

## Pendiente (fuera de Console)

| Prioridad | Acción |
|-----------|--------|
| P2 | Tests unitarios directos de services extraídos (`ExecuteSimulationRunOnInstanceService`, etc.). |
| P3 | Documentar mapa CLI vs `scripts/local-instances/*.mjs`. |
| P3 | Unificar BC Simulation (services aún viven en Control/Shared). |

## Acoplamientos

- Commands → application services (✅ acoplamiento unidireccional).
- Workers `.bat` siguen invocando signatures Artisan (sin cambio).
- Simulation execute sigue dependiendo de Control + Middleware vía services (deuda en BC Simulation, no en Console).

## Veredicto

**Capa CLI limpia.** Los commands son adaptadores delgados; la deuda restante está en los BC subyacentes (Simulation transversal), no en la interfaz Artisan.
