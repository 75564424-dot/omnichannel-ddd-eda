# Desarrollo local — fleet multi-instancia

**Control plane** (`:8000`) + **un silo por cliente** (`:8001+`), cada uno con BD SQLite, `.env` y operadores espejo.

## Arranque

```bash
npm run instances:bootstrap
npm run instances:fleet-bootstrap   # import legacy + retail-norte/sur + mirror usuarios
npm run build
npm run instances:serve
```

Panel SaaS: http://127.0.0.1:8000/control/companies

## Empresas (panel + silos)

| Puerto | Slug | Operador en el silo | Contraseña |
|--------|------|---------------------|------------|
| 8001 | acme-retail | `admin@local` | `client-local-dev` |
| 8002 | pruebas-retail | `prueba@prueba` | `client-local-dev` |
| 8003 | retail-norte | `admin@retail-norte` |
| 8004 | retail-sur | `admin@retail-sur` |

Retail Norte/Sur usan la **misma configuración** que Pruebas Retail (plan, módulos, catálogo 4P/4S). Norte/Sur comparten la contraseña de `prueba@prueba`.

## Provisioning automático

Al registrar una empresa nueva en `/control/provisioning`:

1. Tenant en BD SaaS + operador del formulario
2. Silo local (puerto, `.env`, SQLite)
3. **Mirror**: copia todos los operadores (email, hash, `platform_role`) y `modules_catalog` al silo

`PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true` en `.env.control-plane`.

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
