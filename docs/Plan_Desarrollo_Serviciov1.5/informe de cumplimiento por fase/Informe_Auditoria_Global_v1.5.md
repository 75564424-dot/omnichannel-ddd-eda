# Informe Auditoria Global v1.5

## Estado

Cumple.

## Evidencia encontrada

- Backend lifecycle implementado e integrado en rutas control.
- Middleware bloquea portal web y API en silos suspendidos.
- UI control plane usa acciones contextuales de lifecycle.
- Pagina `Tenant/Suspended` creada y compilada.
- Registry sync, EventBus, Dashboard, Middleware, Simulacion, Roles, Operadores, Login, Tenant Resolution y Control Plane pasan suite completa.
- `vendor/bin/phpunit`: OK, 275 tests, 972 assertions.
- `npm.cmd run build`: OK.
- `npm.cmd run test:ui`: OK (Playwright).

## Correcciones realizadas

- Reemplace HTML embebido de suspension por pagina Inertia `Tenant/Suspended`.
- Agregue badges y acciones lifecycle en `Companies/Show.vue`.
- Agregue estado lifecycle en `Companies/Index.vue`.
- Agregue fixture versionado `pruebas-retail`.
- Corregi fallback de catalogo tenant desde fixtures versionados.
- Corregi visibilidad por defecto del dashboard para modulos configurados.
- Corregi sync de registry para evitar doble conteo entre EventBus explicito y catalogo declarativo.
- Corregi fixtures/contextos de pruebas de soporte, control plane y operadores.
- Ajuste pruebas lifecycle para validar respuesta Inertia y contexto silo/control plane.

## Archivos modificados

- `app/Control/Application/Services/ClientDashboardModulesService.php`
- `app/Control/Application/Services/Tenants/TenantModuleCatalogService.php`
- `app/Http/Middleware/EnsureTenantOperationalStatus.php`
- `app/Middleware/Application/Services/Registry/ConfiguredModuleRegistrySyncService.php`
- `resources/js/Pages/Control/Companies/Index.vue`
- `resources/js/Pages/Control/Companies/Show.vue`
- `tests/Feature/Control/ClientSupportReportTest.php`
- `tests/Feature/Control/ControlIncidentsBusStatusTest.php`
- `tests/Feature/Control/TenantModuleCatalogTest.php`
- `tests/Feature/Control/TenantOperatorDeploymentGuardTest.php`
- `tests/Feature/Control/TenantOperatorsScopedTest.php`
- `tests/Feature/Dashboard/DashboardEndpointsTest.php`
- `tests/Feature/Identity/RoleBasedAuthorizationTest.php`
- `tests/Feature/TenantLifecycleTest.php`

## Archivos nuevos

- `resources/js/Pages/Tenant/Suspended.vue`
- `tests/Fixtures/clients/pruebas-retail/modules_config.json`

## Riesgos detectados

- Ningun bloqueante abierto para Fase 5.

## Riesgos mitigados

- Suite completa verde.
- Build frontend verde.
- Playwright UI suite verde.
- Flujo lifecycle completo cubierto por endpoints, middleware, login, dashboard, API y pruebas de integracion.
- No hay regresiones criticas detectadas en Provisioning, Login, Operadores, Roles, Fleet, Registry, EventBus, Dashboard, Middleware, Simulacion, Tenant Resolution ni Control Plane.

## Deuda tecnica pendiente

- Fuera del alcance v1.5: pruebas productivas con orquestador cloud real y health checks externos.

## Checklist Runbook

| Requisito | Estado | Evidencia |
| --------- | ------ | --------- |
| Provisioning -> Provisionado | Cumple | Lifecycle persistido |
| Levantar Servicio | Cumple | Use case + supervisor |
| Health Check | Cumple | Suite completa + build |
| Login | Cumple | Tests login pasan |
| Dashboard | Cumple | Dashboard catalog/endpoints pasan |
| Suspender Servicio | Cumple | Use case + middleware |
| Bloqueo Portal | Cumple | `Tenant/Suspended` |
| Bloqueo API | Cumple | Problem Details 403 |
| Restaurar Servicio | Cumple | Use case lifecycle |
| Acceso Restablecido | Cumple | Middleware permite tenant activo |

## Certificacion final

READY FOR PHASE 7
