# Informe Fase 3

## Estado

Cumple con observaciones.

## Evidencia encontrada

- Use cases: `StartTenantServiceUseCase`, `SuspendTenantServiceUseCase`, `RestoreTenantServiceUseCase`.
- Endpoints: `routes/control.php` (`/lifecycle/start`, `/lifecycle/suspend`, `/lifecycle/restore`, `/lifecycle/status`).
- Eventos: `TenantLifecycleStarted`, `TenantLifecycleSuspended`, `TenantLifecycleRestored`.
- Mirror obligatorio: use cases invocan `LocalFleetTenantMirror::mirror`.
- Persistencia: `settings.deployment.lifecycle` y `tenants.status`.

## Correcciones realizadas

- Se preservo bloqueo API/web por estado suspendido en middleware transversal.
- Se mantuvieron rutas legacy `suspend`/`activate` como alias hacia lifecycle.

## Archivos modificados

- `app/Http/Middleware/EnsureTenantOperationalStatus.php`
- `tests/Feature/TenantLifecycleTest.php`

## Archivos nuevos

- Ninguno backend.

## Riesgos detectados

- `StartTenantServiceUseCase` no ejecuta provisioning si faltan detalles `local_instance`; en cloud/prod depende de orquestador externo documentado.

## Riesgos mitigados

- Tests lifecycle pasan: `tests/Feature/TenantLifecycleTest.php`.

## Deuda tecnica pendiente

- Ampliar tests de endpoints lifecycle con usuario SaaS y mock de supervisor.

## Checklist Runbook

| Requisito | Estado | Evidencia |
| --------- | ------ | --------- |
| Use cases | Cumple | `app/Control/Application/UseCases/Lifecycle/*` |
| Endpoints | Cumple | `routes/control.php` |
| Eventos | Cumple | `app/Control/Domain/Events/*` |
| Persistencia | Cumple | `settings.deployment.lifecycle` |
| Auditoria | Cumple con observaciones | Eventos internos; sin tabla historial |
| Mirror | Cumple | Use cases lifecycle |
| Supervisor | Cumple con observaciones | Supervisor local detached |
