# Desarrollo local — fleet multi-instancia

**Control plane** (`:8000`) + **un silo por cliente** (`:8001+`), cada uno con BD SQLite, `.env` y operadores espejo.

## Puertos base y asignacion (v1.6)

- **Control plane:** puerto definido en `deploy/local-instances/instances.json`.
- **Tenants:** comienzan en `PLATFORM_LOCAL_FLEET_PORT_START` (alias conceptual: `BASE_TENANT_PORT`).
- **Asignacion:** `fleet-registry.json` persiste el puerto asignado; la provision usa `LocalFleetRegistry::nextAvailablePort()`.

## Arranque

```bash
npm run instances:bootstrap
npm run instances:fleet-bootstrap   # import legacy + retail-norte/sur + mirror usuarios
npm run build
npm run instances:serve
```

Panel SaaS: http://127.0.0.1:8000/control/companies

## Empresas (panel + silos)

Tenants registrados en `deploy/local-instances/fleet-registry.json`:

| Puerto | Slug | Operador en el silo | Contraseña |
|--------|------|---------------------|------------|
| 8001 | acme-retail | `admin@local` | `client-local-dev` |
| 8002 | pruebas-retail | `prueba@prueba` | `client-local-dev` |
| 8003+ | (nuevos por provisioning) | operador del formulario | `client-local-dev` |

Los puertos se asignan automáticamente desde `PLATFORM_LOCAL_FLEET_PORT_START` (default 8001). Cada nuevo tenant provisionado desde `/control/provisioning` toma el siguiente puerto disponible.

## Routing amigable (v1.6 — ADR-011)

Con `PLATFORM_FRIENDLY_ROUTING=true` en `.env.control-plane`, el control plane expone URLs de ruta amigable. Solo tenants con `settings.deployment.local_instance.app_url` (escrito al provisionar) reciben la redirección.

```
GET http://127.0.0.1:8000/{slug}/login  →  302  →  http://127.0.0.1:800X/login
```

Los silos siguen funcionando en sus puertos individuales (retrocompatible). Ver [ADR-011](../../docs/production/ADR_011_friendly_routing_multitenant.md).

## Provisioning automático y Ciclo de Vida (v1.5)

Al registrar una empresa nueva en `/control/provisioning`:

1. Tenant en BD SaaS + operador del formulario
2. Silo local (puerto, `.env`, SQLite)
3. **Mirror**: copia todos los operadores (email, hash, `platform_role`) y `modules_catalog` al silo

`PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true` en `.env.control-plane`.

### Gestión del Ciclo de Vida (Sin reinicio)

Desde el panel SaaS (`/control/companies/{id}`), puedes gestionar el estado operativo del silo **sin necesidad de reiniciar `npm run instances:serve`**:

- **Levantar servicio**: Inicia un proceso `php artisan serve` en background (detached) para el silo provisionado.
- **Suspender servicio**: Bloquea el acceso al portal del silo (muestra página de mantenimiento) y bloquea la API (`403 Tenant Suspended`).
- **Restaurar servicio**: Reactiva el acceso al portal y API del silo.

## Comandos

| Comando | Uso |
|---------|-----|
| `npm run instances:fleet-bootstrap` | Import legacy + demo retail + silos |
| `npm run instances:sync` | Silos pendientes |
| `php artisan platform:fleet:sync-local --force --env=control-plane` | Re-espejar usuarios/config a silos existentes |

## Aislamiento

- BD: `database/instances/{slug}.sqlite`
- Catálogo bus: `config/modules/instances/{slug}/modules_config.json` (`MODULES_CONFIG_PATH`)
- Login cruzado bloqueado por tenant + BD separada
