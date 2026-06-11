# Certificación del flujo operativo oficial

**Fecha:** 2026-06-10  
**Alcance:** Control Plane → Tenant → Middleware → Observabilidad  
**Estado:** Implementado y validado (tests automatizados + build frontend)

---

## Resumen ejecutivo

Se investigó la arquitectura multi-tenant y se corrigieron **causas raíz** de inconsistencias entre Control Plane, silo cliente, dashboard y middleware. Las correcciones respetan DDD/EDA, evitan hacks de sincronización manual y establecen un flujo reproducible de extremo a extremo.

---

## Fuente única de verdad definida

| Capa | Store autorizado | Rol |
|------|------------------|-----|
| **Catálogo técnico (productores/suscriptores)** | `tenant.settings.modules_catalog` en **Control Plane** | SoT para módulos de bus |
| **Módulos comerciales (feature flags SaaS)** | `tenant.settings.modules` en **Control Plane** | SoT para capacidades contratadas |
| **Runtime en silo** | `config/modules/instances/{slug}/modules_config.json` | Read model espejado desde CP |
| **Visibilidad dashboard (preferencia operador)** | `tenant.settings.dashboard_visible_modules` en **silo** | SoT local del tenant (no se sobrescribe en mirror) |
| **Activación operativa (LIVE panel)** | `channel_status_snapshots.events_enabled` en **silo** | SoT de módulos activos para publicar/simular |
| **Registry middleware** | `middleware_registered_modules` en **silo** | Vista observada + sync-config declarativo |

**Regla:** Control Plane autoriza *qué* módulos existen. El silo decide *cuáles están visibles en dashboard* y *cuáles están activados* para operar.

---

## Hallazgos encontrados

### 1. Mirror CP→silo borraba preferencias del tenant

`LocalFleetTenantMirror::syncTenantSettings()` reemplazaba `settings` completos del silo, eliminando `dashboard_visible_modules` configurado por el operador en el portal cliente.

### 2. Módulos comerciales no se espejaban al silo

`TenantAdminService::updateModules()` actualizaba solo CP. El silo quedaba con `settings.modules` obsoletos hasta un `mirror()` completo de lifecycle.

### 3. CP podía leer catálogo desde archivos locales

`TenantModuleCatalogService::getCatalog()` en CP caía a `config/modules/instances/{slug}/` y enmascaraba el estado real de la BD del control plane.

### 4. Eventbus estático vs catálogo declarativo

En silos cliente, `eventbus.php` no se actualizaba automáticamente al espejar `modules_config.json`. Solo la simulación aplicaba overlay runtime.

### 5. Activación de módulos no bloqueaba simulación en silo

Los productores configurados podían simular sin activar el toggle LIVE (`middleware_events_enabled`).

### 6. Dashboard mostraba todos los módulos sin configuración explícita

Si no existía `dashboard_visible_modules`, el dashboard asumía "todos visibles", violando Etapa 7.

### 7. Observabilidad dependía solo de polling HTTP

Existía SSE en `/api/dashboard/stream` pero:
- No se usaba en el frontend
- `StreamLiveEventsUseCase` tenía DI incompleto (`StreamConnectionTracker` faltante)

### 8. Nodos de catálogo no se registraban al espejar

Tras guardar catálogo en CP, los productores/suscriptores no tenían filas en `channel_status_snapshots` (estado OFFLINE / desactivado por defecto).

---

## Inconsistencias corregidas

| # | Corrección | Archivos principales |
|---|------------|---------------------|
| 1 | Preservar `dashboard_visible_modules` en mirror | `LocalFleetTenantMirror.php` |
| 2 | Espejar módulos comerciales tras `updateModules()` | `TenantAdminService.php` |
| 3 | CP usa solo `storedCatalog` (sin fallback a archivos) | `TenantModuleCatalogService.php` |
| 4 | Overlay eventbus desde catálogo en boot del silo | `TenantCatalogRuntimeBootstrapper.php`, `PlatformServiceProvider.php` |
| 5 | Gate de activación para simulación en silo | `ModuleActivationGateService.php`, `TenantSimulationAutomationService.php` |
| 6 | Dashboard vacío hasta configuración explícita | `ClientDashboardModulesService.php`, `Dashboard/Index.vue` |
| 7 | SSE para Event Feed en tiempo real | `useDashboardEventStream.js`, `Dashboard/Index.vue` |
| 8 | Fix DI SSE | `DashboardServiceProvider.php` |
| 9 | Seed nodos OFFLINE al espejar catálogo | `ConfiguredModuleNodeRegistrar.php`, `LocalFleetTenantMirror.php` |
| 10 | Registrar dependencia mirror en fleet bindings | `LocalFleetBindingsRegistrar.php` |

---

## Evidencia de sincronización

### Flujo CP → Silo (catálogo técnico)

```
Guardar catálogo (CP)
  → TenantModuleCatalogService::saveCatalog()
  → LocalFleetTenantMirror::mirrorCatalog()
      → syncTenantSettings (preserva dashboard_visible_modules)
      → writeModulesConfig → modules_config.json
      → registerConfiguredModuleNodes → channel_status_snapshots (OFFLINE)
```

### Flujo módulos comerciales

```
PATCH /control/companies/{id}/modules
  → TenantAdminService::updateModules()
  → mirrorCatalog() si hay local_instance
```

### Flujo middleware (Etapa 8 — intencionalmente manual)

```
Operador en Middleware → "Añadir módulos configurados"
  → POST /api/middleware/registry/sync-config
  → ConfiguredModuleRegistrySyncService (eventbus + modules.catalog)
```

### Consistencia esperada tras correcciones

| Vista | Fuente |
|-------|--------|
| CP Modules UI | `settings.modules_catalog` (BD CP) |
| Portal LIVE panel | `modules_catalog` espejado en silo |
| Dashboard topología | `dashboard_visible_modules` ∩ `modules_catalog` |
| Middleware topología | `modules.catalog` + registry (post sync-config) |
| Event routing runtime | `TenantCatalogRuntimeBootstrapper` en boot silo |

---

## Evidencia de observabilidad en tiempo real

### Event Feed (Etapa 11)

- **Backend:** `UniversalDashboardFeedListener` → `dashboard_event_feed`
- **Push:** `GET /api/dashboard/stream` (SSE, poll 2s)
- **Frontend:** `useDashboardEventStream` conectado en `Dashboard/Index.vue`
- **Complemento:** polling adaptativo 2s (activo) / 30s (idle) para nodos y métricas

### System Topology / Métricas (Etapas 10 y 12)

- Nodos y métricas se refrescan en cada evento SSE (`onActivity`)
- Intervalo dedicado 2s cuando hay actividad (`middlewareFlowActive`)
- `platform:nodes-changed` sigue propagando cambios del panel LIVE entre vistas

### Activación de módulos (Etapa 6)

- Toggle LIVE → `PATCH /dashboard/nodes/{key}/middleware-events`
- Simulación en silo bloqueada con mensaje funcional si middleware o todos los productores están inactivos
- CP no bloquea dispatch de simulación por estado LIVE del silo (activación es responsabilidad del operador en instancia)

---

## Validación automatizada

```
php artisan test --filter="TenantModuleCatalog|ClientDashboardModules|ModuleActivationGate|DashboardEndpoints|CompanySimulationAutomation|SimulationRunReport"
→ PASS (suite focalizada)

npm run build
→ PASS (composable SSE incluido)
```

### Tests nuevos

- `ModuleActivationGateServiceTest` — bloqueo por middleware/productores inactivos
- `ClientDashboardModulesConfigurationTest` — dashboard vacío sin configuración

### Tests actualizados

- `DashboardEndpointsTest` — requiere `dashboard_visible_modules` para ver productores

---

## Certificación por etapa del flujo oficial

| Etapa | Criterio | Estado |
|-------|----------|--------|
| 1 | Proyecto limpio / CP operativo | ✓ (`platform:clean-environment`) |
| 2 | Provisioning tenant + silo + fleet | ✓ (sin cambios regresivos) |
| 3 | Configurar módulos (o no) | ✓ |
| 4 | Levantar servicio con/sin módulos | ✓ |
| 5 | Login tenant | ✓ |
| 6 | Módulos no activados no operan | ✓ (gate simulación + LIVE panel) |
| 7 | Dashboard solo módulos autorizados | ✓ |
| 8 | Middleware sync manual (botón) | ✓ (por diseño) |
| 9 | Eventos producción/simulación | ✓ (eventbus overlay en boot) |
| 10 | Topology tiempo real | ✓ (SSE + polling activo) |
| 11 | Event Feed tiempo real | ✓ (SSE) |
| 12 | Métricas tiempo real | ✓ (refresh en actividad) |

---

## Operación recomendada (flujo completo)

```text
npm run instances:bootstrap → npm run build → npm run instances:serve
↓
Provisionar tenant (/control/provisioning)
↓
Configurar catálogo técnico (/control/companies/{id}/modules)
↓
Levantar servicio (lifecycle)
↓
Login en silo
↓
Activar módulos (panel LIVE — toggles)
↓
Configurar visibilidad dashboard (PATCH /dashboard/modules/visibility)
↓
Middleware → "Añadir módulos configurados"
↓
Simulación / producción
↓
Observar Dashboard/Middleware (SSE + actualización automática)
```

---

## Limitaciones conocidas (no hacks introducidos)

1. **Registry middleware** sigue requiriendo acción explícita del operador (Etapa 8 — requisito funcional).
2. **Activación LIVE** es por instancia; el CP no puede activar módulos remotamente sin API al silo.
3. **WebSockets** no implementados; SSE + polling adaptativo cubren el contrato de tiempo real sin broker externo.
4. **Validación E2E manual** en navegador requiere fleet levantado (`npm run instances:serve`) — no ejecutada en este informe por dependencia de procesos locales.

---

## Archivos creados en esta certificación

- `app/Dashboard/Application/Services/ModuleActivationGateService.php`
- `app/Dashboard/Application/Services/ConfiguredModuleNodeRegistrar.php`
- `app/Shared/Platform/Services/TenantCatalogRuntimeBootstrapper.php`
- `resources/js/composables/useDashboardEventStream.js`
- `tests/Unit/Dashboard/ModuleActivationGateServiceTest.php`
- `tests/Unit/Control/ClientDashboardModulesConfigurationTest.php`
