# Omnichannel DDD + EDA — desarrollo local multi-instancia

Plataforma middleware con **control plane SaaS** (`:8000`) y **un silo Laravel por cliente** (`:8001+`), cada uno con su propia base SQLite, `.env` y catálogo de módulos.

## Puertos base (v1.6)

- **Puerto del control plane:** 8000 (definido en `deploy/local-instances/instances.json`).
- **Base de puertos de tenants:** `PLATFORM_LOCAL_FLEET_PORT_START` (alias conceptual: `BASE_TENANT_PORT`).
- **Convencion:** el primer tenant usa `PLATFORM_LOCAL_FLEET_PORT_START`, el siguiente incrementa en 1.

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+ y npm
- SQLite (incluido en PHP)

## Primera vez

```bash
composer install
npm install
```

No hace falta un `.env` en la raíz: el bootstrap genera `.env.control-plane`, `.env.client-acme-retail`, etc.

## Arranque (orden correcto)

Ejecuta los comandos **en este orden**:

### 1. `npm run instances:bootstrap`

Crea o actualiza el entorno local multi-instancia:

- Genera los `.env` por instancia desde `deploy/local-instances/instances.json` y `fleet-registry.json`
- Crea las bases SQLite en `database/instances/{slug}.sqlite`
- Ejecuta migraciones y seeders en cada silo
- En clientes, escribe `config/modules/instances/{slug}/modules_config.json` (ruta ignorada por git)

**Por qué primero:** cada silo necesita esquema de BD y su tenant local antes de poder recibir datos del control plane.

### 2. `npm run instances:fleet-bootstrap`

Prepara el registro SaaS y sincroniza clientes:

- Importa tenants `acme-retail` y `pruebas-retail` desde `database/database.sqlite` (legacy) al control plane
- Espeja operadores, settings y catálogo de módulos del control plane hacia los silos ya migrados

**Por qué después del bootstrap:** el mirror escribe en silos que ya tienen tablas (`tenants`, `users`, …). Si el silo no está migrado, falla con `no such table: tenants`.

> Si no tienes `database/database.sqlite`, el import legacy se omite con aviso; los silos del `fleet-registry.json` siguen funcionando con configuración mínima.

### 3. `npm run build`

Compila assets de Vite (Vue/Inertia) una vez.

**Por qué:** `instances:serve` levanta varios `php artisan serve` sin el dev server de Vite; sin build la UI no carga CSS/JS en los puertos 8000–8002.

### 4. `npm run instances:serve`

Levanta en paralelo:

| URL | Rol | Login |
|-----|-----|-------|
| http://127.0.0.1:8000 | Control plane SaaS | `saas@local` / `saas-local-dev` |
| http://127.0.0.1:8001 | Acme Retail | `admin@local` / `client-local-dev` |
| http://127.0.0.1:8002 | Pruebas Retail | `prueba@prueba` / `client-local-dev` |

Panel de empresas: http://127.0.0.1:8000/control/companies

## Routing amigable (v1.6)

Con `PLATFORM_FRIENDLY_ROUTING=true` en `.env.control-plane`, el control plane expone URLs de ruta amigable que redirigen (HTTP 302) al silo por puerto:

```
http://127.0.0.1:8000/{slug}/login  →  http://127.0.0.1:800X/login
http://127.0.0.1:8000/{slug}/       →  http://127.0.0.1:800X/login  (root redirige a login)
http://127.0.0.1:8000/{slug}/{path} →  http://127.0.0.1:800X/{path}
```

Requisitos para que un tenant reciba la redirección:
- `status = active` en la tabla `tenants` del control plane.
- `settings.deployment.local_instance.app_url` presente (se escribe al provisionar desde `/control/provisioning`).
- El flag `PLATFORM_FRIENDLY_ROUTING=true` en el `.env` del control plane.

Los silos siguen siendo accesibles directamente por puerto. El routing amigable es un overlay aditivo.

Ver [ADR-011](docs/production/ADR_011_friendly_routing_multitenant.md) para la decisión arquitectónica completa.

## Comandos útiles

| Comando | Cuándo usarlo |
|---------|----------------|
| `npm run instances:sync` | Re-espejar operadores/config a silos pendientes tras cambios en el panel SaaS |
| `npm run instances:reset-operational` | Limpiar datos operativos (colas, métricas) sin borrar tenants |
| `npm run instances:verify` | Comprobar aislamiento entre instancias |
| `npm run dev` | Desarrollo en instancia única con hot reload (puerto 8000) |

## Aislamiento por instancia

- **BD:** `database/instances/{slug}.sqlite`
- **Env:** `.env.{instance-id}` (p. ej. `.env.client-acme-retail`)
- **Catálogo bus:** `config/modules/instances/{slug}/modules_config.json` vía `MODULES_CONFIG_PATH`

## Más detalle

Ver [deploy/local-instances/README.md](deploy/local-instances/README.md) para provisioning automático desde `/control/provisioning` y flujo de mirror.
