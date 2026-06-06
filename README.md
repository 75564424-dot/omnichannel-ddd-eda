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



## Comandos útiles



| Comando | Cuándo usarlo |

|---------|----------------|

| `npm run instances:sync` | Re-espejar operadores/config a silos pendientes tras cambios en el panel SaaS |

| `npm run instances:reset-operational` | Limpiar datos operativos (colas, métricas) sin borrar tenants |

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

