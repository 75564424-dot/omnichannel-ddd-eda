# Informe Fase 2

## Estado

Cumple con observaciones.

## Evidencia encontrada

- Orquestador: `app/Control/Application/Services/Tenants/TenantLifecycleOrchestrator.php`.
- Policy: `app/Control/Domain/Policies/TenantLifecyclePolicy.php`.
- Supervisor local: `app/Shared/Platform/LocalFleet/LocalFleetProcessSupervisor.php`.
- Middleware de bloqueo: `app/Http/Middleware/EnsureTenantOperationalStatus.php`.
- Contratos de contexto tenant: `App\Shared\Platform\Contracts\InstanceTenantContextInterface`.

## Correcciones realizadas

- El middleware respeta `platform.lifecycle_v15` y deja pasar control plane, health checks y assets.
- El bloqueo web ahora renderiza `Tenant/Suspended` via Inertia, no HTML embebido.

## Archivos modificados

- `app/Http/Middleware/EnsureTenantOperationalStatus.php`

## Archivos nuevos

- `resources/js/Pages/Tenant/Suspended.vue`

## Riesgos detectados

- El supervisor local usa procesos detached; la validacion real de health depende del entorno Windows/Linux disponible.

## Riesgos mitigados

- La respuesta API suspendida usa Problem Details `tenant_suspended`.

## Deuda tecnica pendiente

- Agregar lock por puerto/proceso en `LocalFleetProcessSupervisor` para evitar carreras concurrentes.

## Checklist Runbook

| Requisito | Estado | Evidencia |
| --------- | ------ | --------- |
| TenantLifecycleOrchestrator | Cumple | Servicio existente |
| TenantLifecyclePolicy | Cumple | Policy existente |
| Contratos | Cumple | Contexto tenant compartido |
| Middleware | Cumple | `EnsureTenantOperationalStatus` |
| Supervisor | Cumple con observaciones | `LocalFleetProcessSupervisor` |
