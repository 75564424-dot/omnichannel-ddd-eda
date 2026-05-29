# Auditoría — Control (Control Plane / SaaS Admin)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Control/` |
| **Namespace** | `App\Control\` |
| **Tipo** | Bounded Context (admin multi-tenant) |
| **Archivos PHP** | 54 |
| **LOC aprox.** | 4 873 |
| **Controllers web** | 9 en `Interfaces/Http/Controllers/` (~532 LOC) |
| **Tests** | 16 (Unit 8 · Feature 9) |

> **Última refactorización:** 2026-05-28 — subcarpetas Simulation/Tenants, controllers dentro del BC, servicios web extraídos.

## ¿Qué hace?

Orquesta el **plano de control SaaS**: gestión de empresas/tenants, provisioning de instancias, catálogo de módulos por tenant, incidentes y reportes de soporte, vista global de middleware e infraestructura, y **orquestación de simulaciones de carga** contra instancias cliente.

## ¿Para qué sirve?

- Rutas `/control/*` (Inertia) para operadores de plataforma.
- APIs internas de simulación (`SimulationRunInternalController`).
- Coordinación CP ↔ silo cliente (handoff, workers, métricas de corrida).
- Presentación agregada de dashboards de clientes desde el CP.

## Estructura DDD (post-refactor)

```text
app/Control/
├── Application/
│   ├── DTOs/                    SimulationRunExecutionResult
│   ├── Services/
│   │   ├── Simulation/          24 clases (handoff, orchestrator, metrics, worker…)
│   │   ├── Tenants/             7 clases (admin, catalog, operators, provisioning…)
│   │   └── *.php                incidentes, middleware CP, dashboard proxy
├── Infrastructure/              Jobs, SimulationRunModel
└── Interfaces/
    └── Http/Controllers/        9 controllers delgados (antes en app/Http)
```

| Capa | Archivos | Estado |
|------|----------|--------|
| Domain | **0** | ⚠️ Pendiente (ver recomendaciones) |
| Application | ~45 | ✅ Organizado por subdominio |
| Infrastructure | 2 | ✅ |
| Interfaces | 10 | ✅ Controllers + 1 API support |

## Servicios / use cases extraídos en esta refactorización

| Servicio | Subcarpeta | Reemplaza lógica en |
|----------|------------|---------------------|
| `TenantOperatorService` | Tenants | `CompanyController` (User::create, roles) |
| `CompanyListingService` | Tenants | `CompanyController::index` enrichment |
| `ProvisionNewTenantService` | Tenants | `ProvisioningController::store` |
| `ProvisioningChecklistService` | Tenants | `ProvisioningController` checklist privado |
| `SimulationRunQueryService` | Simulation | `SimulationRunController` list/start/report |
| `SimulationRunInternalApiService` | Simulation | `SimulationRunInternalController` |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 42% | **24%** | Controllers fuera de Http; operadores/provisioning en services |
| **% código espagueti** | 48% | **26%** | Simulation agrupada; web dentro del BC |
| **Ratio tests/archivos** | 44% | **~30%** | 12 unit Control pasan tras migración namespaces |
| **Controllers >150 LOC** | 3 (Http) | **1** | `CompanyController` ~189 (validación; sin Eloquent directo) |
| **Services >150 LOC** | 8 | **6** | Métricas/incidentes aún grandes |

### Archivos más pesados (post-refactor)

| Archivo | LOC | Notas |
|---------|-----|-------|
| `ClientIncidentReportService.php` | 276 | Pendiente split presenter |
| `TenantSimulationAutomationService.php` | 275 | Simulation/ |
| `SimulationRunMetricsCollector.php` | 268 | Simulation/ |
| `TenantModuleCatalogService.php` | 224 | Tenants/ |
| `ClientDashboardModulesService.php` | 173 | Proxy dashboard |
| `CompanyController.php` | 189 | Solo validación + Inertia |

## Resuelto en esta refactorización

1. ~~Controllers en `app/Http/Controllers/Control`~~ → `app/Control/Interfaces/Http/Controllers`.
2. ~~`User::query()->create()` en Company/Provisioning~~ → `TenantOperatorService`, `ProvisionNewTenantService`.
3. ~~Simulation* mezclado en `Services/` plano~~ → subcarpeta `Services/Simulation/` (24 archivos).
4. ~~Tenant* mezclado~~ → subcarpeta `Services/Tenants/`.
5. ~~`SimulationRunController` con queries Eloquent~~ → `SimulationRunQueryService`.
6. ~~`SimulationRunInternalController` orquestación HTTP~~ → `SimulationRunInternalApiService`.
7. ~~ProvisioningController formato espagueti / checklist inline~~ → services dedicados.

## Pendiente

| Prioridad | Acción |
|-----------|--------|
| P1 | Domain mínimo: `SimulationRun`, `Tenant` value objects / estados |
| P2 | Split `ClientIncidentReportService` → presenter + repository |
| P2 | Adelgazar `TenantSimulationAutomationService` y `SimulationRunMetricsCollector` |
| P3 | BC Simulation formal (ver `Simulation.md`) — pulse/drain aún en Middleware |
| P3 | Componentes Vue reutilizables (tenant card, simulation progress) |

## Acoplamientos

| Módulo | Uso | Cambio |
|--------|-----|--------|
| Middleware | Bus, cola, health | Sin cambio (vía services) |
| Dashboard | Node status, catálogo | Sin cambio |
| Monitoring | Alertas en overview/incidents | Sin cambio |
| Shared | TenantModel, Platform | Sin cambio |

Controllers ya no importan Eloquent para operadores ni provisioning completo.

## Cobertura de tests

- **Unit Control:** 12/12 OK tras migración namespaces.
- **Gaps:** feature tests provisioning web, `ClientIncidentReportService` unit, E2E Control UI.

## Veredicto

**Deuda estructural reducida de forma significativa.** El BC tiene frontera HTTP clara y subdominios Simulation/Tenants identificables. La deuda restante está en god services de incidentes/métricas y la ausencia de capa Domain — abordables en iteraciones siguientes.
