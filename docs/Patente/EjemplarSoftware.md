# EJEMPLAR DEL SOFTWARE

**Documento:** Evidencia técnica para registro de propiedad intelectual  
**Sistema:** Platform Event Bus Core — Plataforma Middleware Omnicanal DDD + EDA  
**Repositorio:** `omnichannel-ddd-eda`  
**Fecha del documento:** 2026-05-30  
**Elaborado a partir de:** análisis estático del código fuente, configuración, documentación y scripts operativos del proyecto

---

## 1. INFORMACIÓN GENERAL

### 1.1 Identificación del software

| Campo | Valor | Fuente |
|-------|-------|--------|
| **Nombre técnico oficial** | `platform/event-bus-core` | `composer.json` — campos `name` y `description` |
| **Nombre descriptivo / comercial** | Omnichannel DDD + EDA — Plataforma Middleware de Integración y Observabilidad | `README.md`; `docs/api/openapi.yaml` — título «Omnichannel Middleware Platform API» |
| **Nombre comercial registrado** | **[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** | No consta marca registrada ni denominación legal de producto |
| **Versión API** | 1.0.0 | `docs/api/CHANGELOG.md` (2026-05-22) |
| **Versión plan funcional core** | v0.1 | `docs/Plan_Desarrollo_Modulos_v0.1/README.md` |
| **Estado actual** | Beta / MVP avanzado — core operativo para pruebas y despliegue por instancia dedicada | Commit Git inicial: «version beta»; `docs/production/Plan_de_implementacion.md` §1.1 |
| **Tipo de software** | Aplicación web empresarial (backend API + SPA); middleware de integración orientado a eventos; panel SaaS de control | Estructura Laravel + Vue/Inertia; bounded contexts en `app/` |
| **Licencia del código** | MIT | `composer.json` |
| **Titular del derecho** | **[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** | No consta archivo LEGAL, AUTHORS ni metadatos de titularidad |

### 1.2 Arquitectura identificada

| Patrón / modelo | Aplicación en el sistema | Evidencia |
|-----------------|--------------------------|-----------|
| **Monolito modular** | Una aplicación Laravel desplegable por instancia | Estructura `app/{Context}/` |
| **Domain-Driven Design (DDD)** | Bounded contexts con capas Domain, Application, Infrastructure, Interfaces | `app/Middleware/`, `app/Dashboard/`, `app/Control/`, etc. |
| **Event-Driven Architecture (EDA)** | Bus de eventos, `event_store`, cola, listeners, outbox | `config/eventbus.php`, migraciones `2026_05_21_*` |
| **CQRS (parcial)** | Escritura en Middleware; lectura vía proyecciones Dashboard | `docs/architecture/middleware_database_architecture.md` §5 |
| **Arquitectura hexagonal** | Puertos (`EventBusPort`, repositorios Interface) y adaptadores (Eloquent, Laravel) | `app/Shared/Contracts/`, `app/Middleware/Infrastructure/` |
| **Instancia por cliente** | Un silo (app + BD + config) por cliente comercial; NO multi-tenant compartido en runtime | `docs/production/ADR_001_instancia_por_cliente.md` |
| **Control plane SaaS** | Host central que registra empresas y espeja configuración a silos | `routes/control.php`, `app/Control/` |

### 1.3 Descripción ejecutiva

El software objeto de este ejemplar constituye una **plataforma de middleware omnicanal orientada a eventos**, diseñada para organizaciones que requieren integrar sistemas heterogéneos (POS, e-commerce, ERP, webhooks, APIs M2M) mediante un **bus de eventos genérico** desprovisto de reglas de negocio verticales. Según `composer.json`, el sistema provee un «generic event bus middleware + observability dashboard (no business domains)».

La solución combina tres capacidades integradas en un mismo artefacto desplegable:

1. **Middleware (núcleo):** ingestión HTTP de eventos, persistencia en cola y event store, enrutamiento a suscriptores, dead-letter queue, topología, registro de módulos y resiliencia (reintentos, outbox opcional, circuit breaker configurable).
2. **Dashboard (observabilidad):** feed de eventos en tiempo real, KPIs configurables, topología declarativa de productores/suscriptores y estado de nodos — alimentado por proyecciones de lectura derivadas del tráfico del bus.
3. **Control (plano SaaS):** gestión de empresas-cliente, planes comerciales, operadores, provisioning de instancias, simulaciones de tráfico, incidencias e infraestructura global — accesible en rutas `/control/*` cuando `PLATFORM_CONTROL_PLANE=true`.

El modelo de despliegue adoptado (ADR-001) es **instancia dedicada por cliente**: cada organización recibe proceso de aplicación, base de datos y archivos de configuración propios (`eventbus.php`, `modules_config.json`, `.env`), gobernados opcionalmente desde un control plane que mantiene el registro comercial sin compartir datos operativos entre clientes en una misma base de datos.

El repositorio contiene, al momento del análisis, **434 archivos PHP** en `app/`, **121 archivos de prueba** en `tests/`, **31 migraciones** de base de datos, documentación técnica extensa en `docs/` (más de 169 archivos), especificación OpenAPI 3.0, contenedorización Docker multi-stage y scripts de automatización para desarrollo multi-instancia local.

---

## 2. EVIDENCIA DEL CÓDIGO FUENTE

### 2.1 Estructura general del proyecto

El proyecto **no utiliza** la convención `/src` típica de algunos frameworks; la estructura real verificada es la siguiente:

```text
omnichannel-ddd-eda/
├── app/                          # Código fuente PHP — bounded contexts (DDD)
│   ├── Control/                  # Plano SaaS — gestión empresas, provisioning
│   ├── Dashboard/                # Observabilidad — feed, métricas, topología UI
│   ├── Middleware/               # Núcleo bus de eventos
│   ├── Integration/              # Webhooks, canales, conectores externos
│   ├── Simulation/               # Simulación orchestrada de tráfico cliente
│   ├── Monitoring/               # Alertas, canary, evaluadores
│   ├── Observability/            # Prometheus, SLI/SLO, trazas
│   ├── Quality/                  # Cobertura y gates de calidad
│   ├── Shared/                   # Kernel compartido — identidad, plataforma, contratos
│   ├── Platform/                 # Packs demo (EventConsumerRegistration)
│   ├── Http/                     # Middleware HTTP, controllers transversales
│   ├── Console/                  # Comandos Artisan
│   ├── Providers/                # Service providers Laravel
│   ├── Events/                   # Eventos Laravel internos
│   └── Models/                   # Modelo User transversal
├── bootstrap/                    # Arranque Laravel
├── config/                       # Configuración — eventbus, platform, roles, módulos
├── database/
│   ├── migrations/               # 31 migraciones — esquema middleware omnicanal
│   ├── seeders/                  # Seeders — tenant instancia, operadores SaaS/admin
│   └── instances/                # SQLite por silo en desarrollo local
├── deploy/
│   ├── local-instances/          # Manifest fleet — instances.json, fleet-registry.json
│   └── k8s/                      # Manifiestos Kubernetes (backup cronjob, etc.)
├── docker/                       # entrypoint, nginx
├── docs/                         # Documentación técnica, ADRs, runbooks, OpenAPI
├── public/                       # Punto de entrada web
├── resources/
│   ├── js/Pages/                 # Vistas Vue 3 + Inertia (Login, Dashboard, Control…)
│   └── css/                      # Estilos Tailwind
├── routes/
│   ├── web.php                   # Rutas UI operador (/dashboard, /middleware)
│   └── control.php               # Rutas control plane (/control/*)
├── scripts/
│   ├── local-instances/          # bootstrap.mjs, serve.mjs, verify-isolation.mjs
│   ├── ops/                      # backup-database.sh
│   └── ci/                       # Pipelines lint, OpenAPI, k6
├── tests/
│   ├── Unit/                     # Pruebas unitarias por contexto
│   ├── Feature/                  # Pruebas HTTP/API
│   ├── Integration/              # Integración bus, dashboard, middleware
│   └── E2E/                      # Simulación multi-cliente
├── Dockerfile                    # Multi-stage: frontend, vendor, fpm, serve
├── docker-compose.yml            # MySQL, Redis, app, nginx, worker, scheduler
├── composer.json                 # Dependencias PHP
├── package.json                  # Dependencias Node / scripts npm
├── artisan                       # CLI Laravel
└── README.md                     # Guía arranque multi-instancia
```

**Nota:** No existe archivo `requirements.txt` en el repositorio. Las dependencias están declaradas en `composer.json` (PHP) y `package.json` (JavaScript).

### 2.2 Responsabilidad de carpetas y relación arquitectónica

Cada bounded context bajo `app/{Context}/` sigue la organización en capas propia de DDD táctico y arquitectura hexagonal:

| Capa | Ubicación típica | Responsabilidad |
|------|------------------|-----------------|
| **Domain** | `{Context}/Domain/` | Entidades, value objects, interfaces de repositorio, reglas de dominio puras |
| **Application** | `{Context}/Application/` | Casos de uso (`*UseCase`), servicios de aplicación, DTOs |
| **Infrastructure** | `{Context}/Infrastructure/` | Implementaciones Eloquent, adaptadores externos, jobs |
| **Interfaces** | `{Context}/Interfaces/` | Controllers HTTP, Service Providers, definición de rutas API |

El contexto **Shared** concentra contratos transversales (`EventBusPort`, `AuditLogWriterInterface`), identidad (`AuthenticateOperatorUseCase`), servicios de plataforma (`InstanceDeploymentService`, `LocalFleetInstanceProvisioner`) y rutas API compartidas (`app/Shared/Api/Routes/`).

La capa **Interfaces** (presentación) incluye tanto controllers REST (`Middleware/Interfaces/Http/Controllers/`) como vistas Inertia servidas desde `resources/js/Pages/`, enlazadas mediante controllers en `Http/Controllers/Web/` y `Control/Interfaces/Http/Controllers/`.

### 2.3 Tecnologías utilizadas

| Tecnología | Versión (evidenciada) | Uso dentro del sistema |
|------------|----------------------|------------------------|
| PHP | ^8.2 | Lenguaje backend principal |
| Laravel Framework | ^11.0 | Framework web, ORM, migraciones, colas, scheduling |
| JavaScript (ES Modules) | — | Scripts Node, bundler, pruebas UI |
| Vue.js | ^3.4.21 | Interfaz SPA del operador y control plane |
| Inertia.js | ^3.0 | Puente server-driven entre Laravel y Vue |
| Tailwind CSS | ^3.4.1 | Estilos de interfaz |
| Vite | ^5.2.0 | Compilación de assets frontend |
| SQLite | driver Laravel | BD por defecto en desarrollo multi-instancia |
| MySQL | 8.0 (Docker) | BD recomendada en producción |
| Redis | 7-alpine (Docker) | Colas, cache y sesiones en perfil cloud |
| Laravel Sanctum | 4.0 | Tokens API y autenticación stateful |
| Ramsey UUID | ^4.7 | Identificadores de negocio |
| OPIS JSON Schema | 2.3 | Validación opcional de payloads en publish |
| Pest PHP | ^3.0 | Framework de pruebas automatizadas |
| PHPStan | ^1.10 | Análisis estático |
| Playwright | ^1.49.0 | Pruebas E2E de interfaz |
| Docker / Compose | — | Contenedorización FPM + Nginx + servicios |
| Nginx | 1.27-alpine | Reverse proxy en contenedor |
| Node.js | 20-alpine (Dockerfile frontend) | Build de assets en imagen |
| OpenAPI | 3.0.3 | Contrato API en `docs/api/openapi.yaml` |
| Prometheus / Grafana | configs en `docs/` | Métricas externas (no embebidas como servicio) |

### 2.4 Dependencias principales

| Dependencia | Propósito |
|-------------|-----------|
| `laravel/framework ^11.0` | Núcleo MVC, Eloquent, migraciones, middleware HTTP, scheduling |
| `inertiajs/inertia-laravel ^3.0` | Renderizado SPA sin API REST duplicada para UI interna |
| `laravel/sanctum 4.0` | Emisión y validación de tokens API; sesiones stateful |
| `ramsey/uuid ^4.7` | Generación de UUID v4 para eventos, tenants e integraciones |
| `opis/json-schema 2.3` | Validación de esquema JSON en publicación de eventos |
| `vue ^3.4.21` + `@inertiajs/vue3 ^3.0` | Componentes de interfaz reactiva |
| `@vitejs/plugin-vue ^5.0.4` + `laravel-vite-plugin ^1.0.0` | Integración Vite-Laravel |
| `pestphp/pest ^3.0` + `pestphp/pest-plugin-laravel ^3.0` | Suite de pruebas |
| `phpstan/phpstan ^1.10` | Análisis estático en CI |
| `@playwright/test ^1.49.0` | Automatización de pruebas de interfaz |
| `axios ^1.6.4` | Cliente HTTP en frontend |
| `@headlessui/vue ^1.7.21` | Componentes UI accesibles |

### 2.5 Componentes relevantes

#### 2.5.1 APIs REST

Registradas en `app/Shared/Api/Routes/` y documentadas en `docs/api/openapi.yaml`:

| Prefijo | Capacidades principales | Controller / Use Case representativo |
|---------|------------------------|--------------------------------------|
| `/api/middleware/*`, `/api/v1/middleware/*` | Publish, cola, métricas, topología, DLQ, sync registry | `EventQueueController`, `GetTopologySnapshotUseCase` |
| `/api/dashboard/*`, `/api/v1/dashboard/*` | Feed eventos, métricas, catálogo módulos, stream SSE | `EventFeedController`, `StreamLiveEventsUseCase` |
| `/api/integrations/*`, `/api/v1/integrations/*` | Integraciones, canales, credenciales, webhooks | `IntegrationController`, `ReceiveWebhookUseCase` |
| `/up`, `/health/ready` | Liveness y readiness | `ReadinessController`; `.env.example` |
| Rutas internas simulación | Handoff control plane ↔ silo | `SimulationPulseController` |

#### 2.5.2 Eventos y bus

| Componente | Ruta / clase | Función |
|------------|--------------|---------|
| Configuración bus | `config/eventbus.php` | Suscripciones, productores, reintentos, circuit breaker |
| Puerto hexagonal | `App\Shared\Contracts\EventBus\EventBusPort` | Abstracción publicación (Laravel hoy, Kafka preparado) |
| Adaptador runtime | `LaravelEventBusAdapter` | Dispatch a listeners Laravel |
| Publicación | `EventPublisherService` | Validación, idempotencia, persistencia cola + store |
| Tracking | `BusTrackingListener` | Seguimiento consumidores en cola |
| Outbox | `RelayOutboxJob`, `EloquentOutboxRepository` | Patrón transactional outbox (opcional) |
| Extensión packs | `EventConsumerRegistrationInterface`, `PackSubscriptionCatalogMerger` | Merge de catálogo de consumidores externos |

#### 2.5.3 Casos de uso (muestra verificada)

| Contexto | Casos de uso |
|----------|--------------|
| **Middleware** | `GetEventQueueUseCase`, `GetBusMetricsUseCase`, `GetTopologySnapshotUseCase`, `RequeueDeadLetterUseCase`, `SyncConfiguredModulesToRegistryUseCase`, `SearchEventByIdUseCase` |
| **Dashboard** | `GetRecentEventFeedUseCase`, `GetGlobalMetricsUseCase`, `StreamLiveEventsUseCase`, `GetEventFlowDiagramDataUseCase`, `GetModulesCatalogUseCase`, `SetNodeMiddlewareEventsUseCase` |
| **Integration** | `ReceiveWebhookUseCase`, `CreateIntegrationUseCase`, `DispatchOutboundConnectorUseCase`, `StoreIntegrationCredentialUseCase` |
| **Identity** | `AuthenticateOperatorUseCase`, `IssueApiTokenUseCase`, `ResolveOperatorHomePathUseCase` |

#### 2.5.4 Servicios de plataforma e infraestructura local

| Servicio | Clase | Función |
|----------|-------|---------|
| Bootstrap silo cliente | `ClientInstanceBootstrapService` | Tenant + catálogo MODULES_CONFIG_PATH |
| Aprovisionamiento fleet | `LocalFleetInstanceProvisioner` | .env + SQLite + bootstrap + mirror |
| Espejo tenant | `LocalFleetTenantMirror` | Operadores y settings CP → silo |
| Validación catálogo | `PlatformCatalogValidator` | Coherencia eventbus ↔ modules_config |
| Despliegue | `InstanceDeploymentService` | Detección control plane vs silo cliente |

#### 2.5.5 Repositorios (persistencia)

Implementaciones Eloquent bajo `{Context}/Infrastructure/Persistence/`, por ejemplo: `EloquentQueueEntryRepository`, `EloquentEventStoreRepository`, `EloquentDeadLetterRepository`, `EloquentOutboxRepository`, `EloquentAdapterRepository`.

#### 2.5.6 Middleware HTTP (seguridad y contexto)

Ubicados en `app/Http/Middleware/`:

- `AuthenticatePlatformApi` — autenticación API keys / Sanctum
- `EnforcePlatformAbility` — RBAC por ability
- `EnsureControlPlaneHost` — restricción rutas `/control/*`
- `EnsureInstanceWebAuth`, `EnsureAuthenticatedInstanceBinding` — aislamiento silo
- `CorrelationIdMiddleware` — trazabilidad request
- `HandleInertiaRequests` — props compartidas SPA

#### 2.5.7 Comandos Artisan (automatización)

33 archivos identificados en `app/Console/Commands/`, incluyendo:

- `platform:instance:bootstrap` — bootstrap silo cliente
- `platform:fleet:bootstrap-control-plane` — import legacy + provision fleet
- `platform:fleet:sync-local` — re-espejo silos
- `platform:validate-catalog` — validación CI catálogo
- `platform:purge-retention` — purga datos operativos
- `platform:issue-api-token` / `platform:rotate-api-token` — gestión tokens M2M
- `platform:simulation:prepare`, `platform:simulate-client` — simulación
- `platform:emit-mock` — tráfico demo

---

## 3. MANUAL TÉCNICO

### 3.1 Requisitos del sistema

#### Hardware recomendado

**[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** — no existe documento con CPU/RAM/disco mínimos certificados.

Inferencia razonable a partir de stack documentado:

| Perfil | CPU | RAM | Almacenamiento | Evidencia base |
|--------|-----|-----|----------------|----------------|
| Desarrollo local multi-instancia | 2+ núcleos | 4 GB+ | 2 GB+ libres (SQLite + node_modules) | README — 3 instancias PHP + build Vite |
| Producción Docker Compose | 2+ vCPU | 4–8 GB | 20 GB+ (MySQL + Redis + logs) | `docker-compose.yml`, `Plan_Cloud.md` |
| Producción Kubernetes | Según HPA | Según HPA | PVC backups | `deploy/k8s/`, `Reporte_Implementacion.md` |

#### Sistema operativo

| Entorno | SO | Evidencia |
|---------|-----|-----------|
| Desarrollo | Windows, Linux, macOS | Scripts PowerShell/bash; paths Windows en logs README |
| Contenedor | Linux Alpine | `Dockerfile` — `php:8.2-fpm-alpine`, `node:20-alpine` |
| Producción certificada | **[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** | — |

#### Base de datos

| Entorno | Motor | Ubicación / configuración |
|---------|-------|---------------------------|
| Desarrollo local | SQLite | `database/instances/{slug}.sqlite`; `DB_CONNECTION=sqlite` |
| Producción | MySQL 8.0 | `docker-compose.yml`; `.env.example` comentarios |
| Legacy import (dev) | SQLite | `database/database.sqlite` — import fleet-bootstrap |

#### Dependencias de software

| Componente | Versión mínima | Verificación |
|------------|----------------|--------------|
| PHP | 8.2+ | `composer.json`; extensiones en Dockerfile: pdo, pdo_mysql, redis, intl, zip, pcntl |
| Composer | 2.x | `Dockerfile` stage vendor |
| Node.js | 18+ (README); 20 (Dockerfile) | `README.md`; `Dockerfile` frontend |
| npm | Incluido con Node | `package.json` scripts |

Extensiones PHP requeridas (inferidas de Dockerfile y Laravel): `pdo`, `pdo_mysql` o `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`; recomendadas: `redis`, `intl`, `zip`, `pcntl`.

#### Navegadores soportados

**[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** — no existe matriz oficial de compatibilidad.

Evidencia indirecta:

- UI Vue 3 + Inertia SPA moderna
- Playwright Chromium en `npm run test:ui`
- Sanctum stateful domains: `127.0.0.1`, `localhost` (`.env.example`)

#### Requisitos de red

| Requisito | Detalle | Fuente |
|-----------|---------|--------|
| Puertos locales (dev multi-instancia) | 8000 (control plane), 8001+ (silos cliente) | `README.md`, `deploy/local-instances/instances.json` |
| Puerto Docker | 8080 (nginx), 3306 (MySQL), 6379 (Redis) | `docker-compose.yml`, `.env.example` |
| CORS | Orígenes en allowlist `CORS_ALLOWED_ORIGINS` | `.env.example` |
| TLS | Recomendado producción | `Runbook_Onboarding_Cliente.md` — certificado TLS |
| Comunicación simulación CP→silo | HTTP interno + token `PLATFORM_SIMULATION_INTERNAL_TOKEN` | `config/platform.php` |
| Acceso integradores M2M | HTTPS + API key o Bearer Sanctum | `Plan_Autenticacion.md`, OpenAPI security schemes |

---

### 3.2 Instalación

#### 3.2.1 Escenario A — Desarrollo local multi-instancia (evidenciado en README)

**Paso 1 — Obtención del código**

```bash
git clone <url-del-repositorio> omnichannel-ddd-eda
cd omnichannel-ddd-eda
```

**[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** — URL remota Git no fijada en documentación analizada; sustituir por la URL real del repositorio del titular.

**Paso 2 — Dependencias**

```bash
composer install
npm install
```

**Paso 3 — Bootstrap de instancias**

No se requiere `.env` en raíz; el script genera `.env.control-plane`, `.env.client-acme-retail`, etc.

```bash
npm run instances:bootstrap
```

Acciones verificadas del script `scripts/local-instances/bootstrap.mjs`:

- Lee manifest desde `deploy/local-instances/instances.json` y `fleet-registry.json`
- Genera archivos `.env.{instance-id}` via `scripts/local-instances/lib.mjs`
- Crea SQLite en `database/instances/{slug}.sqlite`
- Ejecuta `php artisan migrate --force` por instancia
- Control plane: `db:seed` completo
- Silos cliente: `platform:instance:bootstrap --skip-admin` + `MiddlewareDatabaseSeeder`

**Paso 4 — Fleet bootstrap (registro SaaS + mirror)**

```bash
npm run instances:fleet-bootstrap
```

Equivalente a:

```bash
php artisan platform:fleet:bootstrap-control-plane --env=control-plane --provision
```

Importa tenants desde `database/database.sqlite` (si existe) y espeja operadores/catálogo a silos migrados.

**Paso 5 — Compilación frontend**

```bash
npm run build
```

**Paso 6 — Ejecución**

```bash
npm run instances:serve
```

Instancias verificadas en README:

| URL | Rol | Credenciales seed |
|-----|-----|-------------------|
| http://127.0.0.1:8000 | Control plane | `saas@local` / `saas-local-dev` |
| http://127.0.0.1:8001 | Acme Retail | `admin@local` / `client-local-dev` |
| http://127.0.0.1:8002 | Pruebas Retail | `prueba@prueba` / `client-local-dev` |

#### 3.2.2 Escenario B — Instancia única con hot reload (desarrollo)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm run dev
```

Script `npm run dev` → `scripts/vite-dev.mjs` (Vite + Laravel en puerto 8000).

#### 3.2.3 Escenario C — Producción con Docker Compose

Documentado en `docker-compose.yml` y `.env.example`:

```bash
cp .env.example .env
# Completar APP_KEY, credenciales MySQL, etc.
docker compose up -d --build
```

Servicios levantados: `mysql`, `redis`, `app` (PHP-FPM), `nginx`, `worker` (`queue:work redis`), `scheduler` (`schedule:run` cada 60s).

Puerto publicado: `${APP_PUBLISH_PORT:-8080}:80`

#### 3.2.4 Escenario D — Onboarding instancia cliente producción

Según `docs/production/Runbook_Onboarding_Cliente.md`:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan platform:ensure-instance-tenant
php artisan config:cache
php artisan route:cache
```

Smoke test post-instalación:

```bash
curl -X POST "$APP_URL/api/middleware/registry/sync-config" -H "Accept: application/json"
curl -X POST "$APP_URL/api/middleware/events/publish" -H "Content-Type: application/json" -d '{...}'
curl "$APP_URL/api/middleware/queue?limit=5"
curl "$APP_URL/api/dashboard/events/feed?limit=5"
```

#### 3.2.5 Migraciones y seeds

| Comando | Propósito |
|---------|-----------|
| `php artisan migrate --force` | Aplica 31 migraciones en `database/migrations/` |
| `php artisan db:seed --force` | Ejecuta `DatabaseSeeder` → `InstanceTenantSeeder`, `MiddlewareDatabaseSeeder`, `PlatformOperatorSeeder`, `SaasOperatorSeeder` |
| `php artisan platform:instance:bootstrap` | Bootstrap dedicado silo cliente |
| `php artisan db:seed --class=Database\\Seeders\\MiddlewareDatabaseSeeder` | Defaults canal middleware + retention keys |

---

### 3.3 Configuración inicial

#### 3.3.1 Variables de entorno principales

Archivo plantilla: `.env.example` (198 líneas). Variables críticas:

| Variable | Propósito |
|----------|-----------|
| `PLATFORM_CLIENT_SLUG` | Identidad del silo (ej. `acme-retail`) |
| `PLATFORM_CLIENT_NAME` | Nombre descriptivo instancia |
| `PLATFORM_DEPLOYMENT_MODE` | `instance_per_client` (default) |
| `PLATFORM_CONTROL_PLANE` | `true` en host SaaS; `false` en silos |
| `PLATFORM_LOCAL_FLEET_AUTO_PROVISION` | Auto-crear silo al provisionar tenant |
| `MODULES_CONFIG_PATH` | Ruta catálogo UI por instancia |
| `DB_CONNECTION`, `DB_DATABASE` | Motor y ruta BD |
| `PLATFORM_API_AUTH_ENABLED` | Autenticación API |
| `PLATFORM_SEED_SAAS_OPERATOR` / `PLATFORM_SAAS_ADMIN_EMAIL` | Operador SaaS |
| `PLATFORM_SEED_ADMIN_OPERATOR` / `PLATFORM_ADMIN_EMAIL` | Operador instancia |

En desarrollo multi-instancia, estas variables se generan automáticamente en `.env.{instance-id}`.

#### 3.3.2 Creación de usuarios

| Rol | Mecanismo | Credenciales default (dev) |
|-----|-----------|----------------------------|
| **SaaS Admin** | `SaasOperatorSeeder` | `saas@local` / valor `PLATFORM_SAAS_ADMIN_PASSWORD` |
| **Platform Admin (silo)** | `InstanceTenantSeeder` + bootstrap / mirror | `admin@local` / `client-local-dev` |
| **Operador M2M** | `php artisan platform:issue-api-token` | Token Sanctum con scopes |
| **API Key estática** | `PLATFORM_API_KEYS` en `.env` | Formato `key\|ability1,ability2` |

Gestión adicional de operadores por tenant: panel `/control/companies/{tenant}` → POST `operators.store` (`routes/control.php`).

#### 3.3.3 Configuración multi-tenant / multi-instancia

**Aclaración técnica obligatoria:** el sistema **no implementa multi-tenant lógico compartido** en runtime (ADR-001). La configuración relevante es:

| Concepto | Configuración | Comportamiento |
|----------|---------------|----------------|
| **Instancia por cliente** | `PLATFORM_DEPLOYMENT_MODE=instance_per_client` | Un tenant activo por silo |
| **Control plane** | `PLATFORM_CONTROL_PLANE=true` | Registro de múltiples empresas en BD SaaS |
| **Login cross-tenant demo** | `PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false` (prod) | Deshabilitado en producción |
| **Fleet local** | `deploy/local-instances/fleet-registry.json` | Manifest de silos y puertos |
| **Catálogo por cliente** | `config/modules/instances/{slug}/modules_config.json` | Topología declarativa UI |

#### 3.3.4 Configuración de clientes (control plane)

1. Acceder a http://127.0.0.1:8000/control/companies como `saas_admin`
2. Crear empresa vía POST `/control/companies` o formulario UI (`CompanyController::store`)
3. Asignar plan y módulos desde catálogo `config/saas_catalog.php` (Starter, Growth, Enterprise)
4. Editar catálogo módulos: `/control/companies/{tenant}/modules` → PATCH `modules-catalog`
5. Aplicar a instancia desplegada: POST `modules-catalog/apply` → escribe `modules_config.json`
6. Provisioning automático (dev): `/control/provisioning` con `PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true`

#### 3.3.5 Configuración de módulos técnicos (bus)

| Artefacto | Función |
|-----------|---------|
| `config/eventbus.php` | Enrutamiento, suscripciones, reintentos |
| `config/eventbus_client_overlay.json` | Overlay simulación cliente |
| `config/modules/modules_config.json` o `MODULES_CONFIG_PATH` | Catálogo declarativo UI |
| `consumer_registrars` en eventbus | Packs PHP de consumidores |
| `POST /api/middleware/registry/sync-config` | Materializar registry en BD |

Validación coherencia:

```bash
composer validate-config
# o
php artisan platform:validate-catalog
```

---

### 3.4 Uso básico

#### 3.4.1 Inicio de sesión

1. Navegar a `/login` (`routes/web.php` → `LoginController`)
2. Vista: `resources/js/Pages/Auth/Login.vue`
3. Autenticación: `AuthenticateOperatorUseCase` — valida email/password contra tabla `users`
4. Redirección según rol: SaaS admin → `/control/overview`; operador instancia → `/dashboard`
5. Middleware posterior: `auth.platform.web`, `instance.web`, `instance.portal`

#### 3.4.2 Creación de cliente (empresa)

Flujo control plane:

1. Login como `saas@local` en puerto 8000
2. Ir a `/control/companies` (`Companies/Index.vue`)
3. Completar formulario alta empresa (nombre, slug, plan)
4. Si auto-provision activo: se crea silo (.env, SQLite, bootstrap, mirror)
5. Verificar en `/control/companies/{tenant}` — operadores, módulos, URL instancia

Alternativa provisioning dedicado: `/control/provisioning` (`Provisioning/Index.vue`).

#### 3.4.3 Configuración inicial de integración (silo cliente)

1. Login operador instancia (ej. `admin@local` en :8001)
2. Editar catálogo vía SaaS o directamente `modules_config.json`
3. Ajustar `config/eventbus.php` / overlay si aplica
4. Ejecutar sync: botón «Añadir módulos configurados» en `/middleware` o API POST `sync-config`
5. Publicar evento prueba vía UI o API
6. Verificar en `/dashboard` feed y topología

#### 3.4.4 Operación diaria — Middleware

Ruta UI: `/middleware` (`resources/js/Pages/Middleware/Index.vue`)

Operaciones disponibles:

- Visualizar cola de mensajes y métricas del bus
- Consultar topología configurada vs observada
- Gestionar dead letters (requeue vía API/UI)
- Sincronizar registro de módulos
- Publicar eventos (según permiso `events:publish`)

API equivalente: endpoints en `MiddlewareApiRoutes.php`.

#### 3.4.5 Operación diaria — Dashboard

Ruta UI: `/dashboard` (`resources/js/Pages/Dashboard/Index.vue`)

Operaciones:

- Feed de eventos recientes
- KPIs configurables (`dashboard_config.json`)
- Topología declarativa de productores/suscriptores
- Estado y refresh de nodos
- Toggle visibilidad módulos en dashboard

#### 3.4.6 Administración — Control plane

Rutas verificadas en `routes/control.php`:

| Ruta | Función |
|------|---------|
| `/control/overview` | Resumen operativo fleet |
| `/control/companies` | Gestión empresas |
| `/control/companies/{tenant}` | Detalle, operadores, plan, módulos |
| `/control/provisioning` | Alta con auto-provision |
| `/control/simulations` | Historial y reportes simulación |
| `/control/middleware` | Vista global middleware |
| `/control/infrastructure` | Infraestructura |
| `/control/incidents` | Incidencias y reportes cliente |

#### 3.4.7 Consultas vía API

Ejemplos documentados en OpenAPI y runbooks:

```bash
GET  /api/v1/middleware/queue?page=1&limit=50
GET  /api/v1/middleware/topology
GET  /api/v1/middleware/metrics
GET  /api/v1/middleware/events/{eventId}
GET  /api/v1/dashboard/events/feed?limit=20
POST /api/v1/middleware/events/publish
```

Autenticación requerida cuando `PLATFORM_API_AUTH_ENABLED=true`.

#### 3.4.8 Reportes

| Tipo | Ubicación | Evidencia |
|------|-----------|-----------|
| Reporte simulación | `/control/simulations/{run}/report` | `Simulation/Report.vue` |
| Reportes incidencias cliente | `/control/incidents/reports/{report}` | `Incidents/ReportShow.vue` |
| Notificaciones soporte (silo) | `/support/notifications` | `routes/web.php` |
| Métricas Prometheus | Endpoint métricas (config `PLATFORM_PROMETHEUS_ENABLED`) | `PrometheusMetricsEndpointTest` |
| Dashboards Grafana | JSON en `docs/observability/grafana/` | Config externa |

---

### 3.5 Administración y mantenimiento

#### 3.5.1 Respaldos

Documentado en `docs/production/Runbook_Backup_Restore.md`:

```bash
export DB_HOST=127.0.0.1
export DB_DATABASE=platform_middleware
export DB_USERNAME=platform
export DB_PASSWORD=secret
bash scripts/ops/backup-database.sh
```

Salida: `storage/backups/<database>_<timestamp>.sql.gz` — retención default 14 días.

Automatización Kubernetes: `deploy/k8s/cronjob-backup.yaml`.

En desarrollo SQLite: respaldo por copia de archivo `database/instances/{slug}.sqlite`.

#### 3.5.2 Actualizaciones

Procedimiento inferido de runbooks:

1. Desplegar nuevo artefacto (misma imagen/commit documentado en `Inventario_Instancias.md`)
2. `php artisan migrate --force`
3. `php artisan config:cache && php artisan route:cache`
4. Smoke test (`Runbook_Onboarding_Cliente.md` §5)
5. Verificar `/health/ready`

**[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** — procedimiento formal de rollback versionado.

#### 3.5.3 Logs

| Canal | Configuración | Ubicación |
|-------|---------------|-----------|
| Aplicación | `LOG_CHANNEL=stack` (default) | `storage/logs/laravel.log` |
| Producción cloud | `LOG_STACK=stderr_json` (comentado) | stdout contenedor |
| Correlación | `CorrelationIdMiddleware`, `ShareCorrelationLogContext` | Contexto en logs |
| Auditoría | `AuditLogWriter`, `PLATFORM_AUDIT_ENABLED` | Persistencia auditoría |
| Trazas | `trace_logs` (migración observability) | BD |

#### 3.5.4 Monitoreo

| Mecanismo | Configuración | Documentación |
|-----------|---------------|---------------|
| Health liveness | `GET /up` | `docs/api/openapi.yaml` |
| Readiness | `GET /health/ready` | `.env.example` |
| Prometheus | `PLATFORM_PROMETHEUS_ENABLED=true` | `docs/observability/` |
| Alertas | `PLATFORM_MONITORING_ENABLED`, reglas Prometheus | `docs/monitoring/prometheus/alert_rules.yml` |
| Canary | `PLATFORM_CANARY_ENABLED`, intervalo configurable | `config/platform.php` vía `.env.example` |
| SLI/SLO | `PLATFORM_SLO_*` variables | `.env.example` |

Comando evaluación alertas: `EvaluateMonitoringAlertsCommand` (`app/Monitoring/`).

#### 3.5.5 Gestión de errores

| Mecanismo | Función |
|-----------|---------|
| Dead Letter Queue | Eventos fallidos → `dead_letter_queue`; requeue manual |
| Reintentos | `config/eventbus.php` — max 3 intentos, backoff [5, 30, 120]s |
| Circuit breaker | `EVENTBUS_CIRCUIT_BREAKER_ENABLED` (default false) |
| Problem Details | RFC 7807 en errores API (`PLATFORM_API_PROBLEM_DETAILS`) |
| Idempotencia publish | Header `Idempotency-Key` |
| Incidencias | Flujo `/control/incidents` + reportes cliente |

#### 3.5.6 Escalabilidad

- **Horizontal por instancia:** duplicar despliegues completos por cliente (ADR-001)
- **Worker Redis:** `docker-compose.yml` — servicio `worker` con `queue:work redis`
- **HPA Kubernetes:** documentado en `Reporte_Implementacion.md`
- **Purga retención:** `platform:purge-retention` — control crecimiento tablas operativas

#### 3.5.7 Recuperación ante fallos

Documentado en `docs/production/Runbook_DR_Drill.md`:

- Escenario restore desde backup (`Runbook_Backup_Restore.md`)
- Post-restore: `migrate:status`, `/health/ready`, smoke test
- RPO/RTO orientativos en runbook backup

Comando reset operacional (dev, sin borrar tenants):

```bash
npm run instances:reset-operational
```

---

## 4. EVIDENCIA DE FUNCIONALIDAD

| Módulo | Descripción | Evidencia encontrada |
|--------|-------------|----------------------|
| **Middleware** | Bus de eventos: publish, cola, DLQ, topología, sync registry, métricas, outbox | `app/Middleware/` (514 archivos en app total); `MiddlewareApiRoutes.php`; `/middleware` UI; migraciones messaging |
| **Dashboard** | Observabilidad: feed, SSE, KPIs, topología, nodos | `app/Dashboard/`; `DashboardWebController`; `resources/js/Pages/Dashboard/Index.vue` |
| **Control** | Plano SaaS: empresas, planes, operadores, provisioning, simulaciones, incidencias | `app/Control/`; `routes/control.php`; vistas `resources/js/Pages/Control/` |
| **Integration** | Canales, integraciones, credenciales, webhooks, conectores salientes | `app/Integration/`; `IntegrationApiRoutes.php`; migración webhooks `2026_05_21_100003_*` |
| **Simulation** | Preparación y ejecución simulaciones; handoff CP↔silo; reportes | `app/Simulation/`; `SimulationRunController`; tabla `simulation_runs` |
| **Observability** | Prometheus, SLI, trace spans, lag feed | `app/Observability/`; tests `PrometheusMetricsEndpointTest` |
| **Monitoring** | Canary, alertas, evaluadores infraestructura/cola/DLQ | `app/Monitoring/`; `EvaluateMonitoringAlertsCommand` |
| **Quality** | Cobertura mínima, gates CI | `app/Quality/`; `PLATFORM_QUALITY_COVERAGE_MIN=70` |
| **Shared/Identity** | Login, tokens API, RBAC | `AuthenticateOperatorUseCase`; `config/platform_roles.php` |
| **Shared/Platform** | Bootstrap instancia, fleet local, validación catálogo | `ClientInstanceBootstrapService`; `scripts/local-instances/` |
| **Platform/Demo** | Pack demo consumidores | `app/Platform/Demo/DemoPackEventConsumers.php`; `DEMO_PACK_ENABLED` |

---

## 5. EVIDENCIA DE ORIGINALIDAD

### 5.1 Implementación propia vs. ensamblaje de librerías

Si bien el software utiliza frameworks de terceros (Laravel, Vue), la lógica de dominio propia comprende:

- Esquema de persistencia middleware omnicanal diseñado específicamente (`docs/architecture/middleware_database_architecture.md`) — abandono explícito de modelo retail OLTP legacy
- 31 migraciones propias (2026-05-01 a 2026-05-28)
- 33+ comandos Artisan de operación
- 40+ casos de uso identificables
- 121 archivos de prueba automatizada
- 9 ADRs de decisión arquitectónica en `docs/production/ADR_*.md`

### 5.2 Elementos diferenciadores verificados

#### Instancia por cliente con control plane (no multi-tenant clásico)

Implementación original documentada en ADR-001: silo físico por cliente + registro SaaS centralizado + mirror automático (`LocalFleetTenantMirror`). Difiere de SaaS multi-tenant estándar donde múltiples clientes comparten una BD con `tenant_id` en runtime.

#### Domain-Driven Design

Organización verificable en 10+ bounded contexts con capas consistentes. Lenguaje ubicuo: evento, productor, suscriptor, módulo registrado, tenant-institancia, cola, DLQ.

#### Event-Driven Architecture

Pipeline completo: publish → persist → dispatch → listeners → proyecciones. Tablas `event_store`, `message_queue`, `outbox_messages`. Extensión por `EventConsumerRegistrationInterface` sin modificar núcleo.

#### CQRS

Separación escritura (Middleware persiste y procesa) vs lectura (Dashboard consume proyecciones `event_feed_projections`, métricas). Documentado como principio de diseño en arquitectura de BD.

#### Dual catálogo sincronizado

Coexistencia `eventbus.php` + `modules_config.json` con sync unificado (`SyncConfiguredModulesToRegistryUseCase`) y validación CI (`PlatformCatalogValidator`) — solución a problema documentado de divergencia UI vs enrutamiento.

#### Aprovisionamiento automático de fleet local

Pipeline verificable: `LocalFleetInstanceProvisioner` → genera `.env` + SQLite → migrate → bootstrap → mirror → actualiza `fleet-registry.json`. Automatización no trivial sobre Laravel stock.

#### Simulación orchestrada

Módulo `Simulation/` con handoff JSON entre control plane e instancia, fixtures versionados (`tests/Fixtures/clients/`), reportes en `/control/simulations/{run}/report`.

#### Middleware de integración desacoplado

Sin reglas de negocio retail embebidas (`composer.json`). Integración vía configuración declarativa, packs PHP y APIs M2M con idempotencia y Problem Details.

---

## 6. CAPTURAS DE PANTALLA REQUERIDAS

> **Instrucción:** Las siguientes capturas deben obtenerse manualmente del sistema en ejecución. Este documento no incluye imágenes; solo especifica evidencia visual requerida para el expediente.

### Captura 01 — Pantalla de login

**Objetivo:**  
Demostrar mecanismo de autenticación de operadores del sistema.

**URL:** `http://127.0.0.1:8000/login` o silo cliente `:8001/login`

**Elementos visibles requeridos:** formulario email/contraseña, identificación visual del producto.

**Archivo sugerido:** `captura_01_login.png`

**Evidencia código:** `resources/js/Pages/Auth/Login.vue`, `routes/web.php`

---

### Captura 02 — Dashboard principal (silo cliente)

**Objetivo:**  
Mostrar panel de observabilidad operativa con feed de eventos y KPIs.

**URL:** `http://127.0.0.1:8001/dashboard` (login previo como `admin@local`)

**Elementos visibles requeridos:** feed eventos, métricas, topología o nodos.

**Archivo sugerido:** `captura_02_dashboard_cliente.png`

**Evidencia código:** `resources/js/Pages/Dashboard/Index.vue`

---

### Captura 03 — Panel Middleware (control del bus)

**Objetivo:**  
Evidenciar interfaz de operación del bus: cola, métricas, topología.

**URL:** `http://127.0.0.1:8001/middleware`

**Archivo sugerido:** `captura_03_middleware_bus.png`

**Evidencia código:** `resources/js/Pages/Middleware/Index.vue`

---

### Captura 04 — Control plane: Overview

**Objetivo:**  
Demostrar existencia del plano de control SaaS.

**URL:** `http://127.0.0.1:8000/control/overview` (login `saas@local`)

**Archivo sugerido:** `captura_04_control_overview.png`

**Evidencia código:** `resources/js/Pages/Control/Overview/Index.vue`

---

### Captura 05 — Gestión de empresas (clientes)

**Objetivo:**  
Mostrar listado y gestión de empresas-cliente registradas.

**URL:** `http://127.0.0.1:8000/control/companies`

**Archivo sugerido:** `captura_05_gestion_empresas.png`

**Evidencia código:** `resources/js/Pages/Control/Companies/Index.vue`

---

### Captura 06 — Detalle de empresa / operadores

**Objetivo:**  
Evidenciar gestión de operadores, plan y módulos por tenant.

**URL:** `http://127.0.0.1:8000/control/companies/{tenant-id}`

**Archivo sugerido:** `captura_06_detalle_empresa_operadores.png`

**Evidencia código:** `resources/js/Pages/Control/Companies/Show.vue`

---

### Captura 07 — Configuración catálogo de módulos

**Objetivo:**  
Demostrar editor de productores/suscriptores (equivalente `modules_config.json`).

**URL:** `http://127.0.0.1:8000/control/companies/{tenant}/modules`

**Archivo sugerido:** `captura_07_configuracion_modulos.png`

**Evidencia código:** `resources/js/Pages/Control/Companies/ModulesConfig.vue`

---

### Captura 08 — Provisioning de nueva instancia

**Objetivo:**  
Evidenciar flujo de aprovisionamiento automático de silo cliente.

**URL:** `http://127.0.0.1:8000/control/provisioning`

**Archivo sugerido:** `captura_08_provisioning.png`

**Evidencia código:** `resources/js/Pages/Control/Provisioning/Index.vue`

---

### Captura 09 — Simulaciones: listado y reporte

**Objetivo:**  
Demostrar capacidad de simulación orchestrada y generación de reportes.

**URL:** `http://127.0.0.1:8000/control/simulations` y `/control/simulations/{run}/report`

**Archivo sugerido:** `captura_09_simulacion_reporte.png`

**Evidencia código:** `resources/js/Pages/Control/Simulation/Index.vue`, `Report.vue`

---

### Captura 10 — Incidencias y soporte

**Objetivo:**  
Evidenciar gestión de incidencias y reportes de clientes.

**URL:** `http://127.0.0.1:8000/control/incidents`

**Archivo sugerido:** `captura_10_incidentes.png`

**Evidencia código:** `resources/js/Pages/Control/Incidents/Index.vue`

---

### Captura 11 — Infraestructura global

**Objetivo:**  
Mostrar vista de infraestructura del fleet desde control plane.

**URL:** `http://127.0.0.1:8000/control/infrastructure`

**Archivo sugerido:** `captura_11_infraestructura.png`

**Evidencia código:** `resources/js/Pages/Control/Infrastructure/Index.vue`

---

### Captura 12 — Middleware global (control plane)

**Objetivo:**  
Vista agregada del middleware desde operador SaaS.

**URL:** `http://127.0.0.1:8000/control/middleware`

**Archivo sugerido:** `captura_12_middleware_global.png`

**Evidencia código:** `resources/js/Pages/Control/Middleware/Index.vue`

---

### Captura 13 — Respuesta API: publicación de evento

**Objetivo:**  
Evidenciar funcionalidad M2M del bus (captura de herramienta API o terminal).

**Acción:** POST `/api/v1/middleware/events/publish` con payload JSON válido y autenticación.

**Archivo sugerido:** `captura_13_api_publish_evento.png`

**Evidencia código:** `MiddlewareApiRoutes.php`, `docs/api/openapi.yaml`

---

### Captura 14 — Respuesta API: cola y feed

**Objetivo:**  
Demostrar consulta operativa post-publicación.

**Acción:** GET `/api/v1/middleware/queue` y GET `/api/v1/dashboard/events/feed`

**Archivo sugerido:** `captura_14_api_cola_feed.png`

---

### Captura 15 — Health check / métricas

**Objetivo:**  
Evidenciar endpoints de salud y observabilidad.

**Acción:** GET `/up`, GET `/health/ready`, endpoint Prometheus si habilitado.

**Archivo sugerido:** `captura_15_health_metricas.png`

**Evidencia código:** `docs/api/openapi.yaml`; `ReadinessController.php`

---

## 7. ANEXOS TÉCNICOS RECOMENDADOS

Para completar el expediente de registro, se recomienda adjuntar los siguientes anexos (algunos existen parcialmente en `docs/`):

| # | Anexo | Estado en repositorio | Acción recomendada |
|---|-------|----------------------|-------------------|
| A1 | **Diagrama de arquitectura general** | Parcial — mermaid en ADR-001, `Plan_Desarrollo_Modulos_v0.1` | Exportar diagrama control plane + silos |
| A2 | **Diagrama de componentes (bounded contexts)** | Inferible de estructura `app/` | Generar diagrama UML/componentes |
| A3 | **Diagrama de clases (dominio Middleware)** | No encontrado como imagen | Extraer de `app/Middleware/Domain/` |
| A4 | **Diagrama de secuencia: publicación de evento** | No encontrado como imagen | publish → queue → store → listeners → feed |
| A5 | **Modelo de datos (ERD)** | Documentado textualmente en `middleware_database_architecture.md`, `middleware_database_dictionary.md` | Exportar ERD visual |
| A6 | **Flujo de eventos (EDA)** | Descripción en docs; mermaid parcial | Diagrama swimlane productor→bus→consumidor→dashboard |
| A7 | **Flujo instancia por cliente (no multi-tenant runtime)** | ADR-001 | Diagrama provisioning + mirror |
| A8 | **Especificación OpenAPI** | **Existe:** `docs/api/openapi.yaml` | Adjuntar PDF o YAML firmado |
| A9 | **Matriz RBAC** | **Existe:** `config/platform_roles.php`, `Matriz_Endpoints_Seguridad.md` | Adjuntar al expediente |
| A10 | **Inventario de migraciones** | **Existe:** `database/migrations/` (31 archivos) | Listado fechado |
| A11 | **Resultados pruebas automatizadas** | **Existe:** `tests/` (121 archivos); CI en `composer.json` | Adjuntar reporte `composer test` |
| A12 | **Hash de integridad código fuente** | **[INFORMACIÓN NO IDENTIFICADA EN EL PROYECTO]** | Generar SHA-256 del tarball release |
| A13 | **Manual de despliegue producción** | **Existe:** `Runbook_Onboarding_Cliente.md`, `Runbook_Deploy_VM.md`, `Plan_Cloud.md` | Consolidar |
| A14 | **Capturas de pantalla** | Pendiente — ver §6 | Obtener las 15 capturas listadas |

---

## 8. OBSERVACIONES FINALES

Con base en el análisis exhaustivo del repositorio `omnichannel-ddd-eda`, se constata lo siguiente:

1. **Existencia verificable del software.** El repositorio contiene 434 archivos PHP de aplicación, 121 archivos de prueba, 31 migraciones, configuración Docker, scripts operativos, especificación OpenAPI 3.0 y documentación técnica extensa (>169 archivos en `docs/`). La obra informática existe de forma material y reproducible.

2. **Capacidad de instalación.** El proyecto incluye procedimientos de instalación verificables: `composer install`, `npm install`, `npm run instances:bootstrap`, despliegue Docker Compose (`docker compose up -d --build`) y runbook de onboarding producción (`Runbook_Onboarding_Cliente.md`).

3. **Capacidad de ejecución.** El README documenta ejecución concurrente de control plane (:8000) y silos cliente (:8001, :8002) mediante `npm run instances:serve`. Health endpoints `/up` y `/health/ready` documentados en OpenAPI. El sistema ha sido ejecutado en entorno de desarrollo Windows según evidencia de sesión operativa.

4. **Funcionalidades identificables.** Nueve módulos funcionales principales (Middleware, Dashboard, Control, Integration, Simulation, Observability, Monitoring, Quality, Identity/Platform) con rutas web, APIs REST, casos de uso y vistas Vue verificables.

5. **Originalidad como creación informática.** Más allá del uso de frameworks de terceros, el sistema implementa decisiones arquitectónicas propias (ADR-001 a ADR-009), esquema de datos especializado, pipeline EDA completo, mecanismo de fleet provisioning, simulación orchestrada y validación de catálogo — constituyendo una expresión original susceptible de protección como obra del titular.

6. **Validez del presente documento como ejemplar técnico.** Este ejemplar describe fielmente la estructura, instalación, configuración, operación y evidencias del software a partir de fuentes primarias del proyecto, sin atribuir funcionalidades no implementadas. Las lagunas identificadas (titular, marca registrada, matriz de navegadores, hardware mínimo certificado) han sido señaladas explícitamente para completar el expediente ante el organismo de registro de propiedad intelectual.

---

**Referencias internas del proyecto:**

- Ficha técnica complementaria: `docs/Patente/Ficha_Tecnica_Software_INDECOPI.md`
- Guía arranque: `README.md`
- ADR principal: `docs/production/ADR_001_instancia_por_cliente.md`
- API: `docs/api/openapi.yaml`
- Arquitectura BD: `docs/architecture/middleware_database_architecture.md`

---

*Documento elaborado mediante análisis estático del repositorio. Tercera persona. Redacción institucional para fines de registro de software.*
