# Desarrollo local — fleet multi-instancia

**Control plane** (`:8000`) + **un silo por cliente** (`:8001+`), cada uno con BD SQLite, `.env` y operadores espejo.

## Puertos base y asignación (v1.7)

- **Control plane:** puerto definido en `deploy/local-instances/instances.json` (default `8000`).
- **Tenants:** comienzan en `PLATFORM_LOCAL_FLEET_PORT_START` (default `8001`).
- **Asignación:** `fleet-registry.json` persiste el puerto; la provisión usa `LocalFleetRegistry::nextAvailablePort()`.

## Arranque baseline GitHub Ready (v1.7)

Flujo reproducible sin tenants pre-cargados:

```bash
npm run instances:bootstrap
npm run build
npm run instances:serve
```

Panel SaaS: http://127.0.0.1:8000/control/companies

Provisiona clientes desde `/control/provisioning`. Cada empresa nueva genera silo, `.env`, SQLite y entrada en `fleet-registry.json` (local, no versionado).

**Plantillas versionadas:** `instances.json` (control plane) y `fleet-registry.example.json` (fleet vacío). El bootstrap crea `fleet-registry.json` si no existe.

## Modo demo legacy (opcional)

```bash
npm run instances:bootstrap
npm run instances:fleet-bootstrap   # import legacy desde database/database.sqlite
npm run build
npm run instances:serve
```

Importa `acme-retail` y `pruebas-retail` si existe `database/database.sqlite`. **No** usar para certificación v1.7 GitHub Ready.

## Routing amigable (v1.7 — ADR-011)

`PLATFORM_FRIENDLY_ROUTING=true` se genera en `.env.control-plane` al ejecutar bootstrap. Solo tenants con `settings.deployment.local_instance.app_url` (escrito al provisionar) reciben redirección 302.

```
GET http://127.0.0.1:8000/{slug}/login  →  302  →  http://127.0.0.1:800X/login
```

Ver [ADR-011](../../docs/production/ADR_011_friendly_routing_multitenant.md).

## Provisioning automático y Ciclo de Vida

Al registrar una empresa nueva en `/control/provisioning`:

1. Tenant en BD SaaS + operador del formulario
2. Silo local (puerto, `.env`, SQLite)
3. **Mirror**: copia operadores (email, hash, `platform_role`) y `modules_catalog` al silo

`PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true` en `.env.control-plane`.

**Catálogo técnico de módulos:** al guardar desde `/control/companies/{id}/modules`, el control plane persiste el catálogo y ejecuta **mirrorCatalog** al silo (`modules_catalog` en SQLite del cliente + `config/modules/instances/{slug}/modules_config.json`, sin re-sync de operadores). La topología de middleware y `sync-config` incorporan siempre el catálogo declarativo. Reinicie `instances:serve` si el CP quedó bloqueado tras un guardado con fleet activo.

### Gestión del Ciclo de Vida (sin reinicio)

Desde `/control/companies/{id}`:

- **Levantar servicio**: inicia `php artisan serve` en background para el silo.
- **Suspender servicio**: bloquea portal y API (`403 Tenant Suspended`).
- **Restaurar servicio**: reactiva acceso.

## Comandos

| Comando | Uso |
|---------|-----|
| `npm run instances:fleet-bootstrap` | Modo demo legacy (import + mirror) |
| `npm run instances:sync` | Silos pendientes |
| `php artisan platform:fleet:sync-local --force --env=control-plane` | Re-espejar usuarios/config |

## Aislamiento

- BD: `database/instances/{slug}.sqlite`
- Catálogo bus: `config/modules/instances/{slug}/modules_config.json` (`MODULES_CONFIG_PATH`)
- Login cruzado bloqueado por tenant + BD separada
