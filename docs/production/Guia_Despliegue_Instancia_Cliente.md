# Guía rápida — Instancia dedicada por empresa

**Modelo:** [ADR-001](ADR_001_instancia_por_cliente.md)  
**Una empresa comercial = un despliegue Laravel + una base de datos + `PLATFORM_CLIENT_SLUG` único.**

---

## Roles de despliegue

| Host | `PLATFORM_CONTROL_PLANE` | `PLATFORM_CLIENT_SLUG` | Uso |
|------|--------------------------|------------------------|-----|
| Panel SaaS (registro) | `true` | `platform` o interno | Gestionar catálogo, planes, provisioning |
| Cliente (silo) | `false` | `acme-retail`, `pruebas-retail`, … | Portal operador + bus de **un** cliente |

En el silo del cliente:

- `PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false`
- Solo existe **un** tenant en BD (seeder elimina otros slugs)
- Operadores con `tenant_id` de esa empresa

---

## Alta de empresa nueva (flujo producción)

### 1. Registro en SaaS (control plane)

1. `/control/provisioning` — crear empresa, plan, módulos, admin.
2. Queda estado `pending_dedicated_instance` en settings.
3. Copiar bloque `.env` desde la ficha de la empresa en `/control/companies/{id}`.

### 2. Desplegar instancia del cliente

1. Infra: VM/K8s, MySQL vacía, Redis, DNS `https://{slug}.middleware.example.com`
2. Copiar `docs/production/templates/env.client.example` → `.env`
3. Ajustar:

```env
PLATFORM_CLIENT_SLUG=pruebas-retail
PLATFORM_CLIENT_NAME="Pruebas Retail"
PLATFORM_CONTROL_PLANE=false
PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false
APP_URL=https://pruebas-retail.middleware.example.com
```

4. Bootstrap:

```bash
php artisan migrate --force
php artisan platform:instance:bootstrap
php artisan config:cache
```

5. Sync módulos y smoke test (ver [Runbook_Onboarding_Cliente.md](Runbook_Onboarding_Cliente.md)).

### 3. Login operador

- URL: `APP_URL` del cliente (no la URL del panel SaaS).
- Usuario creado en bootstrap (`PLATFORM_ADMIN_EMAIL`) o añadido en esa instancia.

---

## Desarrollo local (un solo `artisan serve`)

Para probar varias empresas en la misma máquina **sin** desplegar N instancias:

```env
APP_ENV=local
PLATFORM_CONTROL_PLANE=true
PLATFORM_PORTAL_MULTI_TENANT_LOGIN=true
PLATFORM_CLIENT_SLUG=acme-retail
```

Esto es **solo demo**; no usar en producción.

---

## Comandos útiles

| Comando | Descripción |
|---------|-------------|
| `php artisan platform:ensure-instance-tenant` | Upsert tenant según `PLATFORM_CLIENT_SLUG` |
| `php artisan platform:instance:bootstrap` | Tenant + admin de instancia (post-migrate) |
