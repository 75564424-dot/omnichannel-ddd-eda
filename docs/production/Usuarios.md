# Usuarios y RBAC

Implementación según `Plan_Usuarios.md`.

## Roles

| Rol | `platform_role` | Capacidades |
|-----|-----------------|-------------|
| Platform Admin | `platform_admin` | publish, sync, DLQ, dashboard, gestión usuarios |
| Bus Operator | `bus_operator` | publish, sync, DLQ, dashboard |
| Dashboard Viewer | `dashboard_viewer` | lectura bus + dashboard |
| API Integrator | — | Solo vía token/API key (scopes M2M) |

Matriz completa: `config/platform_roles.php`

## Arquitectura DDD

- **Shared/Identity** — roles, policies, `PlatformAuthorizationService`
- Middleware/Dashboard consumen abilities vía `auth.platform` + `platform.ability`
- Policies Laravel: `PublishEventPolicy`, `SyncRegistryPolicy`, `ResolveDeadLetterPolicy`, `ManageUsersPolicy`
- Sin acoplar `User` a entidades del bus EDA

## Gestión de usuarios (admin)

- UI: `/admin/users` (solo `platform_admin`)
- Crear usuario + asignar rol
- Cambiar rol existente

## Seed admin

```bash
php artisan migrate --seed
# admin@local / password (platform_admin)
```

Variables: `PLATFORM_ADMIN_ROLE`, ver `config/platform_auth.php`

## Auditoría

Acciones de control incluyen `actor_label` con email y rol en `audit_logs.changes`.

## Fase 3 diferida

SSO / LDAP / roles custom: `ADR_003_usuarios_enterprise.md`
