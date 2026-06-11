# Omnichannel DDD + EDA — desarrollo local multi-instancia



Plataforma middleware con **control plane SaaS** (`:8000`) y **un silo Laravel por cliente** (`:8001+`), cada uno con su propia base SQLite, `.env` y catálogo de módulos.



## Puertos base (v1.7)



- **Puerto del control plane:** 8000 (definido en `deploy/local-instances/instances.json`).

- **Base de puertos de tenants:** `PLATFORM_LOCAL_FLEET_PORT_START` (default `8001`).

- **Convención:** cada tenant provisionado toma el siguiente puerto libre en `fleet-registry.json`.



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



No hace falta un `.env` en la raíz: el bootstrap genera `.env.control-plane` desde `deploy/local-instances/instances.json`.



## Arranque baseline GitHub Ready (v1.7)



Flujo reproducible desde clone limpio **sin datos legacy**. Los silos de cliente se crean únicamente al provisionar desde el panel SaaS.



Ejecuta los comandos **en este orden**:



### 1. `npm run instances:bootstrap`



Crea o actualiza el entorno local del control plane:



- Genera `.env.control-plane` con `PLATFORM_FRIENDLY_ROUTING=true` y auto-provisioning habilitado

- Crea `database/instances/platform.sqlite` y ejecuta migraciones/seeders del CP

- **No** crea silos de cliente ni importa tenants históricos



### 2. `npm run build`



Compila assets de Vite (Vue/Inertia). Obligatorio antes de `instances:serve` (no hay dev server de Vite en multi-instancia).



### 3. `npm run instances:serve`



Levanta el control plane en http://127.0.0.1:8000



| URL | Rol | Login |

|-----|-----|-------|

| http://127.0.0.1:8000 | Control plane SaaS | `saas@local` / `saas-local-dev` |



### 4. Provisionar clientes



Panel de empresas: http://127.0.0.1:8000/control/companies



Registra una empresa nueva en `/control/provisioning`. El sistema:



1. Crea el tenant en la BD del CP

2. Asigna puerto, genera `.env.client-{slug}`, SQLite y catálogo de módulos

3. Espeja operadores y configuración al silo



Los silos provisionados quedan en `deploy/local-instances/fleet-registry.json` y escuchan en `:8001+`.



## Flujo de desarrollo

```text
Levantar proyecto (bootstrap → build → instances:serve)
↓
Provisionar tenant (/control/provisioning)
↓
Levantar servicio (lifecycle en panel SaaS)
↓
Configurar módulos (catálogo tenant)
↓
Middleware (registry / topología)
↓
Simulación
```



## Flujo de limpieza

```text
Detener npm run instances:serve
↓
php artisan platform:clean-environment --force --env=control-plane
↓
php artisan platform:clean-environment --verify --env=control-plane
↓
npm run instances:serve
↓
Provisionar tenant (puede reutilizar slug histórico, p. ej. pruebas)
```

> **Obligatorio:** `--env=control-plane` y detener el fleet antes de limpiar. SQLite bloqueadas impiden borrar silos y la auditoría post-limpieza fallará.



## Modo demo legacy (opcional, no GitHub Ready)



Si necesitas reproducir el entorno histórico con `acme-retail` y `pruebas-retail` importados desde `database/database.sqlite`:



```bash

npm run instances:bootstrap

npm run instances:fleet-bootstrap   # import legacy + mirror a silos demo

npm run build

npm run instances:serve

```



> Este flujo **no** forma parte de la certificación v1.7 GitHub Ready. Requiere `database/database.sqlite` legacy y reintroduce tenants históricos. Usar solo para demos locales o migración forense.



## Routing amigable (v1.7)



Con `PLATFORM_FRIENDLY_ROUTING=true` en `.env.control-plane` (generado por bootstrap), el control plane expone URLs de ruta amigable que redirigen (HTTP 302) al silo por puerto:



```

http://127.0.0.1:8000/{slug}/login  →  http://127.0.0.1:800X/login

http://127.0.0.1:8000/{slug}/       →  http://127.0.0.1:800X/login

http://127.0.0.1:8000/{slug}/{path} →  http://127.0.0.1:800X/{path}

```



Requisitos para que un tenant reciba la redirección:



- `status = active` en la tabla `tenants` del control plane.

- `settings.deployment.local_instance.app_url` presente (se escribe al provisionar).

- `PLATFORM_FRIENDLY_ROUTING=true` en el `.env` del control plane.



Ver [ADR-011](docs/production/ADR_011_friendly_routing_multitenant.md).



## Simulación (v1.7)



La simulación exige módulos activos con productores configurados (`event_types_emitted`) en el catálogo almacenado del tenant. No se usa fixture default como sustituto.

### Limpieza oficial (`platform:clean-environment`)

Comando único recomendado para volver a estado GitHub Ready. Equivalente a `platform:reset-local --purge-tenants` más **auditoría post-limpieza**.

```bash
# Limpieza completa + verificación automática
php artisan platform:clean-environment --force --env=control-plane

# Solo auditar (sin borrar)
php artisan platform:clean-environment --verify --env=control-plane
```

**Elimina:** tenants cliente (hard delete, incluye soft-deleted), usuarios operadores asociados, `simulation_runs`, handoffs, SQLite/`.env`/mirrors de clientes, fleet registry, tablas operativas CP + silos, colas, caché, logs de simulación.

**Preserva:** migraciones, control plane (`platform`), operador SaaS, configuración global.

### Saneamiento parcial (`platform:reset-local`)

Use cuando quiera limpiar simulaciones y datos operativos **sin** eliminar tenants:

```bash
php artisan platform:reset-local --force --env=control-plane
php artisan platform:reset-local --purge-tenants --force --env=control-plane  # sin auditoría
```

### Otros comandos relacionados

| Comando | Limpia | No limpia |
|---------|--------|-----------|
| `platform:clean-environment` | Todo lo anterior + auditoría | CP, SaaS admin, migraciones |
| `platform:reset-local` | Simulaciones, handoffs, tablas operativas | Tenants (salvo `--purge-tenants`) |
| `platform:reset-local --purge-tenants` | + tenants, silos, `.env`, registry | Soft-deleted antes del fix; usar `clean-environment` |
| `platform:simulation:reset` | Solo `simulation_runs` + handoffs | Tenants, silos, fleet |
| `platform:fleet:prune-orphans` | Tenants/silos **no** listados en registry | Soft-deleted en CP; registry vacío |
| `platform:purge-retention` | Filas antiguas por retención en tablas ops | Tenants, silos, simulaciones |
| `demo:reset-operational` | Datos demo operativos | Identidad / tenants |
| `platform:reset-demo-identity` | Usuarios demo e incidentes de otros tenants | Tenant principal demo |
| `npm run instances:reset-operational` | Wrapper operacional por instancia | Tenants |

### Saneamiento del entorno local (`platform:reset-local`) — detalle

Use este comando cuando:

- Queden simulaciones en **En curso 0%** por datos históricos o handoffs huérfanos.
- Necesite repetir una auditoría E2E desde un estado reproducible.
- Tras pruebas manuales intensivas (colas, métricas, event_store saturados).

**Qué elimina (por defecto):**

- Filas `simulation_runs` en el control plane (marca colgadas como fallidas antes).
- Handoffs (`storage/app/simulation-handoff/`), logs `simulation-*.log`, launchers `.bat`, pulso de simulación.
- Tablas operativas en CP y en cada silo del fleet (colas, event_store, métricas, registry, etc.).
- Marcadores `last_simulation` en settings de tenants.

**Qué preserva:**

- Esquema y migraciones, configuración base, operador SaaS (`saas@local`).
- Tenants registrados y silos (salvo `--purge-tenants`).

**Ejemplos:**

```bash
# Limpieza estándar antes de certificar simulaciones
php artisan platform:reset-local --force --env=control-plane
```

> Preferir `platform:clean-environment` cuando necesite eliminar tenants y certificar que no queden referencias históricas (p. ej. slug `pruebas` reutilizable).



## Comandos útiles



| Comando | Cuándo usarlo |

|---------|----------------|

| `npm run instances:sync` | Re-espejar operadores/config a silos pendientes tras cambios en el panel SaaS |

| `npm run instances:reset-operational` | Limpiar datos operativos (colas, métricas) sin borrar tenants |

| `php artisan platform:clean-environment --force --env=control-plane` | **Limpieza oficial completa** + auditoría post-limpieza |

| `php artisan platform:clean-environment --verify --env=control-plane` | Auditar referencias residuales sin borrar |

| `php artisan platform:reset-local --force --env=control-plane` | Saneamiento parcial (simulaciones, colas, métricas) sin borrar tenants |

| `php artisan platform:reset-local --purge-tenants --force --env=control-plane` | Purge de tenants sin auditoría automática |

| `php artisan platform:simulation:reset --fail-stale --env=control-plane` | Solo historial de simulaciones (comando legacy acotado) |

| `npm run instances:verify` | Comprobar aislamiento entre instancias |

| `npm run dev` | Desarrollo en instancia única con hot reload (puerto 8000) |

| `php artisan test` | Suite de pruebas (certificación v1.7) |



## Aislamiento por instancia



- **BD:** `database/instances/{slug}.sqlite`

- **Env:** `.env.{instance-id}` (p. ej. `.env.client-mi-empresa`)

- **Catálogo bus:** `config/modules/instances/{slug}/modules_config.json` vía `MODULES_CONFIG_PATH` (ignorado por git)



## Certificación v1.7



Ver [Runbook v1.7](docs/Plan_Desarrollo_Serviciov1.7/Runbook_v1.7_Certificacion_Operativa_Legacy_Eradication_GitHub_Ready.md) e informes por fase en `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/`.



## Más detalle



Ver [deploy/local-instances/README.md](deploy/local-instances/README.md) para provisioning automático, ciclo de vida y mirror.

