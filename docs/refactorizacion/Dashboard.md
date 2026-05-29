# Auditoría — Dashboard (Observabilidad)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Dashboard/` |
| **Namespace** | `App\Dashboard\` |
| **Tipo** | Bounded Context complementario |
| **Archivos PHP** | 65 |
| **LOC aprox.** | 2 573 |
| **Controllers web** | 4 en `Interfaces/Http/Controllers/Web/` (~120 LOC) |
| **Tests** | 19 (Unit 6 · Feature 2 · Integration 3) |

> **Última refactorización:** 2026-05-28 — persistencia feed dividida, ACL de ingestión, controllers web dentro del BC.

## ¿Qué hace?

Proporciona la **capa de observabilidad** para operadores de instancia cliente: métricas globales, feed de eventos, estado de nodos, catálogo de módulos, streaming en vivo y diagrama de flujo de eventos. Consume read models alimentados por el bus y por configuración declarativa.

## ¿Para qué sirve?

- Panel principal `/dashboard` para ver salud del sistema en tiempo (casi) real.
- APIs `/api/dashboard/*` para métricas, feed, nodos y módulos.
- Proyección de eventos del bus hacia UI sin que Dashboard escriba en la cola.

## Estructura DDD (post-refactor)

```text
app/Dashboard/
├── Application/
│   ├── Services/              ACL ingestión, series dinámicas, página índice
│   ├── UseCases/              12 casos de uso delgados
│   └── DTOs/
├── Domain/                    Read models + interfaces repo
├── Infrastructure/
│   ├── Persistence/           Repo + mapper + queries calendario + latencia
│   └── Modules/               TenantAware + Config + ModulesCatalogNormalizer
├── Interfaces/
│   ├── Http/Controllers/      API dashboard
│   └── Http/Controllers/Web/  4 controllers Inertia/JSON (antes en app/Http)
└── Listeners/                 Delgados; delegan a Application/Services
```

| Capa | Archivos | Estado |
|------|----------|--------|
| Domain | 15 | ✅ Read models + interfaces repo |
| Application | 22 | ✅ Use cases + servicios extraídos |
| Infrastructure | 15 | ✅ Repos delegados a query objects |
| Interfaces | 12 | ✅ API + web dentro del BC |
| Listeners | 2 | ✅ Listener universal ~35 LOC |

## Servicios extraídos en esta refactorización

| Servicio | Reemplaza lógica en |
|----------|---------------------|
| `EventFeedEntryMapper` | `EloquentEventFeedRepository::toDomain` |
| `EventFeedCalendarSeriesQuery` | Agregaciones por día en el repo |
| `EventFeedLatencyCalculator` | `computeAverageLatencyMs` en el repo |
| `DynamicMetricSeriesBuilder` | Builders de series en `GetDynamicMetricSeriesUseCase` |
| `DashboardIngestionGateService` | `passesIngestionGate` en listener |
| `DashboardBusEventIngestionService` | Proyección + hooks post-insert |
| `DashboardIndexPageService` | Agregación props de `DashboardWebController` |
| `ModulesCatalogNormalizer` | Normalización en `ConfigModulesCatalogDataProvider` |

## Métricas de deuda

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 24% | **11%** | Repos y use cases sin métodos >80 LOC |
| **% código espagueti** | 22% | **9%** | Ingestión bus aislada en ACL + servicio |
| **Ratio tests/archivos** | 21% | 29% | Sin regresiones en suite Dashboard |
| **Archivos >150 LOC** | 2 | **0** | Repo máx. ~95 LOC; builder series ~115 LOC |

### Archivos más pesados (post-refactor)

| Archivo | LOC | Notas |
|---------|-----|-------|
| `DynamicMetricSeriesBuilder.php` | ~115 | Lógica de agregación de charts |
| `ModulesCatalogNormalizer.php` | ~105 | Normalización declarativa compartida |
| `DashboardBusEventIngestionService.php` | ~85 | ACL + proyección feed |
| `EventFeedCalendarSeriesQuery.php` | ~85 | Queries de series temporales |

## Cosas sueltas / inconsistentes (restantes)

1. **UI monolítica** — `Dashboard/Index.vue` (594 LOC) mezcla métricas, feed, polling de simulación y nodos.
2. **Duplicación con Middleware UI** — conceptos de pulse, cola y EPS repetidos entre Dashboard y Middleware frontend.
3. **MiddlewareMetricsListener** — aún acoplado al schema de eventos del bus (sin ACL dedicado).
4. **Catálogo dual documentado** — runtime usa `TenantAwareModulesCatalogDataProvider`; tests/config usan `ConfigModulesCatalogDataProvider` + `ModulesCatalogNormalizer`.

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| Middleware | Eventos / DB read models | ⚠️ Medio (mitigado con ACL ingestión) |
| Shared/ControlPlane | `NodeIngestionGateReaderInterface` | ✅ Bajo |
| Control | `ClientDashboardModulesService`, métricas tenant | ✅ Bajo (contrato explícito) |

## Cobertura de tests

- **Verificado (2026-05-28):** 19 tests Unit + Integration Dashboard — todos pasan.
- **Gaps:** `StreamLiveEventsUseCase`, edge cases de `DynamicMetricSeriesBuilder`, UI E2E del dashboard cliente.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P3 | Extraer composables Vue compartidos con Middleware (`useSimulationPulse`). |
| P3 | ACL simétrico para `MiddlewareMetricsListener`. |
| P4 | Tests de contrato para payloads de ingestión (fixtures versionados). |

## Veredicto

**BC bien estructurado** tras refactor: capa web y persistencia de feed alineadas con Control/Console. Deuda restante concentrada en frontend Vue y listener de métricas middleware.
