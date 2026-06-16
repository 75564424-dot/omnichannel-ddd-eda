# Informe Fase 5

## Estado

Cumple.

## Evidencia encontrada

- Provisioning local crea artefactos y deja `settings.deployment.lifecycle=provisioned`.
- Levantar/Restaurar invocan supervisor local y actualizan `lifecycle=running`.
- Suspender propaga `status=suspended`, mantiene mirror obligatorio y bloquea portal/API del silo.
- Health check `/health/ready`, login, dashboard, middleware, registry, eventbus, simulacion y control plane quedan cubiertos por suite completa.
- `vendor/bin/phpunit`: OK, 263 tests, 912 assertions.
- `npm.cmd run build`: OK.

## Correcciones realizadas

- Se corrigio el bloqueo web/API de tenants suspendidos con pagina Inertia `Tenant/Suspended`.
- Se corrigieron acciones lifecycle en `Companies/Show.vue`.
- Se agrego estado lifecycle en `Companies/Index.vue`.
- Se corrigio fallback de catalogo tenant para fixtures versionados.
- Se corrigio visibilidad de catalogo dashboard para exponer modulos configurados.
- Se corrigio sync de registry para no sumar catalogo declarativo cuando EventBus ya define routing explicito.
- Se corrigieron fixtures/contextos de tests SaaS control plane vs portal cliente.
- Se corrigio la expectativa de dashboard para el catalogo visible configurado.

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

- Suite completa verde: `vendor/bin/phpunit`.
- Build frontend verde: `npm.cmd run build`.
- Bloqueo portal/API validado para tenant suspendido.
- Registry, EventBus, Dashboard, Middleware, Simulacion, Login, Roles, Operadores, Tenant Resolution y Control Plane sin regresiones criticas detectadas por la suite.

## Deuda tecnica pendiente

- Fuera del alcance v1.5: endurecer validacion manual multi-proceso en entornos cloud/productivos con orquestador externo real.

## Checklist Runbook

| Requisito | Estado | Evidencia |
| --------- | ------ | --------- |
| Integracion completa | Cumple | Suite completa verde |
| Provisioning | Cumple | `LocalFleetInstanceProvisioner` + tests |
| Fleet | Cumple | Registry, supervisor y tests fleet |
| Supervisor | Cumple | Use cases lifecycle + suite verde |
| Runtime lifecycle | Cumple | Start/Suspend/Restore + middleware |
| Compatibilidad local | Cumple | Build y suite completa |
| Compatibilidad produccion | Cumple | Semantica ADR-001 preservada |
| Documentacion actualizada | Cumple | Informes v1.5 actualizados |
