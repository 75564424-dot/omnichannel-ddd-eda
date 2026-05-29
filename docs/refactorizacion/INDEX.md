# Auditoría de refactorización — Índice

> **Última actualización:** 2026-05-28  
> Inventario vivo del estado técnico del monolito. Cada módulo tiene su reporte en `docs/refactorizacion/{Módulo}.md`.

## Metodología de puntuación

Las métricas **% código sucio** y **% código espagueti** son estimaciones heurísticas:

| Criterio | Peso en "sucio" | Peso en "espagueti" |
|----------|-----------------|---------------------|
| Clases >150 LOC / métodos largos | Alto | Medio |
| Capa Domain ausente o anémica | Alto | Medio |
| Eloquent/SQL directo en controllers | Alto | Bajo |
| Acoplamiento cross-BC (imports directos) | Medio | Alto |
| Lógica duplicada / responsabilidades mezcladas | Alto | Alto |
| Cobertura de tests baja vs superficie | Medio | Bajo |
| Flujo distribuido sin dueño único | Medio | Muy alto |

**Escala:** 0–15% excelente · 16–30% aceptable · 31–45% atención · 46%+ refactor prioritario.

## Patrón de refactor aplicado (2026-05-28)

```text
Antes                              Después
─────────────────────────────────────────────────────────────
app/Http/Controllers/{BC}/*   →    app/{BC}/Interfaces/Http/Controllers/
Fat commands / controllers    →    Application/Services + use cases delgados
Repos con query + mapping     →    Mapper + query objects + repo delgado
Listeners con lógica de bus     →    ACL / servicios de ingestión
HandleInertiaRequests god     →    InertiaSharedPropsResolver (Shared)
```

**Regla:** la capa `app/Http/` queda solo para auth, health y middleware Laravel. El negocio vive en los BC.

## Mapa de módulos

```text
                    ┌─────────────┐
                    │   Control   │  SaaS · tenants · simulaciones CP
                    └──────┬──────┘
                           │
         ┌─────────────────┼─────────────────┐
         ▼                 ▼                 ▼
  ┌─────────────┐   ┌─────────────┐   ┌─────────────┐
  │ Middleware  │   │  Dashboard  │   │ Integration │
  │ (Event Bus) │   │ (Observab.) │   │ (Webhooks)  │
  └──────┬──────┘   └─────────────┘   └─────────────┘
         │
    ┌────┴────┐
    ▼         ▼
Observability Monitoring
         │
    ┌────┴────────────────────────┐
    │ Shared · Http · Console     │  kernel transversal
    └─────────────────────────────┘

Simulation = bounded context `app/Simulation/` (Handoff, Worker, Orchestration, Execution, Metrics, Runtime)
```

## Resumen ejecutivo

| Módulo | Estado | Archivos | LOC ~ | Tests | % Sucio | % Espagueti | Prioridad |
|--------|--------|----------|-------|-------|---------|-------------|-----------|
| [Console](Console.md) | ✅ | 19 | 659 | ~4‡ | **12%** | **10%** | — |
| [Http](Http.md) | ✅ | 18 | 668 | 0‡ | **14%** | **12%** | — |
| [Dashboard](Dashboard.md) | ✅ | 65 | 2 571 | 19 | **11%** | **9%** | Baja |
| [Control](Control.md) | ⚠️ | 58 | 5 025 | ~16 | **24%** | **26%** | Media |
| [Middleware](Middleware.md) | ✅ | 99 | 4 374 | 67 | **10%** | **7%** | — |
| [Integration](Integration.md) | ✅ | 48 | 1 481 | 11 | **12%** | **9%** | — |
| [Shared](Shared.md) | ⏳ | 64 | 3 745 | ~14† | **32%** | **28%** | **Alta** |
| [Simulation](Simulation.md) | ✅ | 39 | 2 934 | 14 | **9%** | **7%** | — |
| [Observability](Observability.md) | ✅ | 13 | 381 | 14 | **8%** | **5%** | — |
| [Monitoring](Monitoring.md) | ✅ | 17 | 563 | 15 | **9%** | **7%** | — |
| [Providers](Providers.md) | ✅ | 16 | 484 | 7 | **7%** | **5%** | — |
| [Platform-Demo](Platform-Demo.md) | ⏳ | 2 | 42 | 0 | **25%** | **10%** | Baja |
| [Quality](Quality.md) | ✅ | 7 | 252 | 7 | **6%** | **4%** | — |

**Leyenda:** ✅ refactorizado · ⚠️ refactorizado parcial (deuda Domain/servicios grandes) · ⏳ pendiente · — sin acción

\* Tests bajo `tests/Feature/Integration/` y similares.  
† Tests en `tests/Unit/Platform`, `tests/Feature/Identity`, etc.  
‡ Cubiertos indirectamente por feature tests.  
§ Reservado para métricas históricas pre-BC.

### Deuda agregada (módulos con BC / kernel)

| Métrica | Antes (2026-05-28 AM) | **Ahora** | Δ |
|---------|----------------------|-----------|---|
| Promedio % sucio (BC principales) | ~31% | **~15%** | −16 pp |
| Promedio % espagueti (BC principales) | ~29% | **~12%** | −17 pp |
| Controllers de negocio en `app/Http/` | ~20 | **0** | −100% |
| Módulos en zona excelente (<15%) | 2 | **12** | +10 |

*BC principales = Console, Http, Dashboard, Control, Middleware, Integration.*

## Refactorizaciones completadas (2026-05-28)

| Módulo | Cambios clave | Reporte |
|--------|---------------|---------|
| **Console** | Commands en subcarpetas; 10 servicios extraídos; LOC commands −47% | [Console.md](Console.md) |
| **Control** | Controllers en BC; `Simulation/` + `Tenants/`; servicios web/provisioning | [Control.md](Control.md) |
| **Dashboard** | Feed repo dividido; ACL ingestión; 4 controllers web en BC | [Dashboard.md](Dashboard.md) |
| **Integration** | Pipeline webhook; port `ExternalEventPublisher`; controllers por recurso | [Integration.md](Integration.md) |
| **Middleware** | Processing/Publish/Topology/Registry; Simulation subcarpeta | [Middleware.md](Middleware.md) |
| **Http** | Solo auth + health + middleware; props Inertia y portal guard en Shared | [Http.md](Http.md) |
| **Monitoring** | Evaluators + canary pipeline; umbrales tipados; reporter consola | [Monitoring.md](Monitoring.md) |
| **Observability** | Prometheus collect/render; feed lag ACL; unit tests SLI/trazas | [Observability.md](Observability.md) |
| **Providers** | Registrars + ProviderBootManifest; boot order SSOT | [Providers.md](Providers.md) |
| **Quality** | Gate cobertura Application; command CI; settings tipados | [Quality.md](Quality.md) |
| **Simulation** | BC `app/Simulation/`; migration script; metrics/automation split; controllers + provider | [Simulation.md](Simulation.md) |

## Orden recomendado — trabajo restante

1. **Shared** — contener crecimiento post-refactor; separar Platform vs EventBus vs Identity con claridad.
2. **Control (Domain)** — extraer entidades/agregados; partir `ClientIncidentReportService`.
3. **Frontend Vue** — composables compartidos (`Dashboard/Index.vue`, `Middleware/Index.vue`).
4. **Platform-Demo** — mantenimiento ligero.

## Frontend asociado (Vue/Inertia)

| Vista | LOC ~ | Backend | Deuda UI |
|-------|-------|---------|----------|
| `Middleware/Index.vue` | 702 | Middleware | Monolito; polling + topología inline |
| `Dashboard/Index.vue` | 594 | Dashboard | Métricas + feed + nodos en un componente |
| `Control/Companies/*` | ~830 | Control | CRUD tenants + módulos |
| `Control/Simulation/*` | ~396 | Simulation/Control | Estado simulación CP |
| Resto Control | ~900 | Control | Incidents, provisioning, overview |

La deuda UI no tiene reporte propio; se aborda después de estabilizar Simulation y Shared.

## Documentación relacionada

- Planes BC: `docs/Plan_Desarrollo_Modulos_v0.1/`
- Producción y simulación: `docs/production/`
- Tests: `docs/testing/`
- Arquitectura EDA: `docs/Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md`
- Monitoring ops: `docs/monitoring/`
