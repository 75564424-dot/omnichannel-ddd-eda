**Documento 1**  
**FICHA TÉCNICA DEL SOFTWARE — VERSIÓN COMPLETADA**

> **Fuente de generación:** análisis del repositorio `omnichannel-ddd-eda` (2026-06-10).  
> **Plantilla base:** `Documento 1 - Ficha técnica de software.docx.md` (intacta).

---

## 1. Título del Software

El presente programa de ordenador se denomina **Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)**.

| Campo | Valor | Evidencia | Estado |
|-------|-------|-----------|--------|
| Nombre técnico del paquete | `platform/event-bus-core` | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| Descripción oficial del producto | Core platform: generic event bus middleware + observability dashboard (no business domains) | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| Nombre comercial | [PENDIENTE_VALIDACIÓN] | No existe marca comercial explícita en código ni docs oficiales v0.1 | **PENDIENTE DE VALIDACIÓN** |
| Versión referenciada en desarrollo | v1.7 (commit `6500034`), beta (commit inicial `b175b8d`) | `git log` | **VALIDADO** (Fuente: historial git) |
| Versión documental planes | v0.1 (planes módulo), v2.0 (planes técnicos módulo) | `docs/Plan_Desarrollo_Modulos_v0.1/README.md` | **VALIDADO** (Fuente: `docs/Plan_Desarrollo_Modulos_v0.1/README.md`) |

La denominación incluye el núcleo (**Middleware / Event Bus**), el complemento de observabilidad (**Dashboard**) y la versión identificada en el historial Git, permitiendo diferenciarlo de futuras actualizaciones.

---

## 2. Lenguaje(s) de Programación Utilizado(s)

| Tecnología | Uso en el sistema | Evidencia | Estado |
|------------|-------------------|-----------|--------|
| **PHP ^8.2** | Backend, bounded contexts, APIs, comandos Artisan | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| **JavaScript (ES modules)** | Frontend, build, scripts Node | `package.json` `type: module` | **VALIDADO** (Fuente: `package.json`) |
| **Vue 3** | Interfaz de usuario Inertia | `package.json` dependencies `vue ^3.4.21` | **VALIDADO** (Fuente: `package.json`) |
| **SQL** | Esquema relacional vía migraciones Laravel | `database/migrations/` (31 archivos) | **VALIDADO** (Fuente: `database/migrations/`) |
| **JSON** | Configuración declarativa (eventbus, módulos, dashboard) | `config/modules/modules_config.json`, `config/dashboard_config.json` | **VALIDADO** (Fuente: `config/modules/modules_config.json`) |
| **YAML** | OpenAPI, Prometheus, Alertmanager | `docs/api/openapi.yaml`, `docs/monitoring/prometheus/` | **VALIDADO** (Fuente: `docs/api/openapi.yaml`) |
| **Shell / PowerShell** | CI, smoke tests, ops | `scripts/ci/`, `scripts/ops/` | **VALIDADO** (Fuente: `scripts/`) |

**Frameworks y librerías principales:**

| Componente | Tecnología | Evidencia | Estado |
|------------|------------|-----------|--------|
| Backend framework | Laravel ^11.0 | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| SPA bridge | Inertia.js Laravel ^3.0 + @inertiajs/vue3 | `composer.json`, `package.json` | **VALIDADO** (Fuente: `composer.json`, `package.json`) |
| Autenticación API | Laravel Sanctum 4.0 | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| Validación esquemas | opis/json-schema 2.3 | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| Identificadores | ramsey/uuid ^4.7 | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| Build frontend | Vite ^5.2.0 | `package.json` | **VALIDADO** (Fuente: `package.json`) |
| Estilos | Tailwind CSS ^3.4.1 | `package.json` | **VALIDADO** (Fuente: `package.json`) |
| Testing PHP | Pest ^3.0 / PHPUnit | `composer.json`, `phpunit.xml` | **VALIDADO** (Fuente: `composer.json`) |
| Testing UI | Playwright ^1.49.0 | `package.json` | **VALIDADO** (Fuente: `package.json`) |
| Análisis estático | PHPStan ^1.10 | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |

**Bases de datos soportadas (evidenciadas):**

- **SQLite** — entorno local/tests (`database/database.sqlite` referenciado en migraciones y troubleshooting) - **VALIDADO** (Fuente: `database/database.sqlite`)
- **MySQL** — producción documentada en planes y scripts (`docs/production/Plan_BaseDeDatos.md`) - **VALIDADO** (Fuente: `docs/production/Plan_BaseDeDatos.md`)

**Redis:** [PENDIENTE_VALIDACIÓN] — no aparece como dependencia directa en `composer.json`; colas Laravel pueden usar driver configurable vía `.env` (no verificado en repositorio sin `.env`). **PARCIALMENTE VALIDADO**

**Kafka (opcional):** adaptador de bus de eventos como driver alternativo — `app/Middleware/Infrastructure/EventBus/KafkaEventBusAdapter.php`; default `laravel` en `config/eventbus.php`. **VALIDADO** (Fuente: `app/Middleware/Infrastructure/EventBus/KafkaEventBusAdapter.php`)

---

## 3. Funcionalidad Principal

El sistema es una **plataforma de integración y observabilidad basada en eventos (EDA)**, sin dominios de negocio vertical embebidos. Su funcionalidad principal comprende:

### 3.1 Middleware (Event Bus) — núcleo

| Capability | Descripción | Evidencia | Estado |
|------------|-------------|-----------|--------|
| C1 | Recibir eventos desde productores (HTTP publish, jobs, Event facade) | `app/Middleware/Application/Services/EventPublisherService.php` | **VALIDADO** (Fuente: `app/Middleware/Application/Services/EventPublisherService.php`) |
| C2 | Registrar tránsito en cola, métricas y dead letters | Migraciones `bus_queue_entries`, `bus_dead_letters` | **VALIDADO** (Fuente: `database/migrations/`) |
| C3 | Exponer consultas de cola, topología, estado del bus, búsqueda por `event_id` | `app/Shared/Api/Routes/MiddlewareApiRoutes.php` | **VALIDADO** (Fuente: `app/Shared/Api/Routes/MiddlewareApiRoutes.php`) |
| C4 | Mantener registro declarativo de suscripciones (`eventbus.php` + packs) | `app/Providers/EventBusIntegrationServiceProvider.php` | **VALIDADO** (Fuente: `app/Providers/EventBusIntegrationServiceProvider.php`) |
| C5 | Observación wildcard de tráfico a nivel plataforma | `app/Dashboard/Listeners/UniversalDashboardFeedListener.php` | **VALIDADO** (Fuente: `app/Dashboard/Listeners/UniversalDashboardFeedListener.php`) |

**API principal:** `POST /api/middleware/events/publish`, `POST /api/middleware/registry/sync-config`, `GET /api/middleware/topology`, `GET /api/middleware/queue`.

### 3.2 Dashboard — observabilidad

| Objetivo | Descripción | Evidencia | Estado |
|----------|-------------|-----------|--------|
| O1 | Feed de eventos observados | `GetRecentEventFeedUseCase` | **VALIDADO** (Fuente: `app/Dashboard/Application/UseCases/GetRecentEventFeedUseCase.php`) |
| O2 | KPIs configurables desde JSON | `config/dashboard_config.json` | **VALIDADO** (Fuente: `config/dashboard_config.json`) |
| O3 | Métricas del bus (latencia, EPS, cola) | `GetMiddlewareBusMetricsUseCase` | **VALIDADO** (Fuente: `app/Dashboard/Application/UseCases/GetMiddlewareBusMetricsUseCase.php`) |
| O4 | Topología declarativa + observada | `modules_config.json` + APIs dashboard | **VALIDADO** (Fuente: `config/modules/modules_config.json`) |
| O5 | Stream SSE opcional | [PENDIENTE_VALIDACIÓN] implementación UI | **PENDIENTE DE VALIDACIÓN** |

**UI:** `/dashboard`, `/middleware` (portal instancia); APIs `/api/dashboard/*`.

### 3.3 Control Plane (SaaS Admin)

- Gestión de empresas/tenants, planes, módulos, operadores, suspensión/activación.
- Provisioning, simulaciones, incidentes, infraestructura global.
- **Evidencia:** `routes/control.php`, `app/Control/Application/Services/`. **VALIDADO** (Fuente: `routes/control.php`, `app/Control/Application/Services/`)

### 3.4 Integraciones

- CRUD canales, integraciones, credenciales cifradas, webhooks ingress, conectores outbound.
- **Evidencia:** `app/Integration/`, `tests/Feature/Integration/`. **VALIDADO** (Fuente: `app/Integration/`)

### 3.5 Multi-tenancy / instancia por cliente

- Modelo **instancia por cliente** (ADR-001): un despliegue = un silo con BD y config propia.
- Comando `platform:ensure-instance-tenant`, contexto `InstanceTenantContext`.
- **Evidencia:** `docs/production/ADR_001_instancia_por_cliente.md`, `config/platform.php`. **VALIDADO** (Fuente: `docs/production/ADR_001_instancia_por_cliente.md`)

### 3.6 Simulación y calidad

- `platform:simulate-client`, fixtures de clientes, E2E producción-like.
- `platform:validate-catalog` para CI.
- **Evidencia:** `app/Console/Commands/SimulateClientCommand.php`, `tests/E2E/Middleware/`. **VALIDADO** (Fuente: `app/Console/Commands/SimulateClientCommand.php`)

### 3.7 Observabilidad y monitoreo

- Correlation ID, trace logs, endpoint `/metrics` Prometheus, alertas y canary publish.
- **Evidencia:** `app/Observability/`, `app/Monitoring/`, `routes/console.php`. **VALIDADO** (Fuente: `app/Observability/`)

---

## 4. Fecha de Creación

| Hito | Fecha | Evidencia | Estado |
|------|-------|-----------|--------|
| Primer commit en repositorio | **2026-05-27** | `git log --reverse` — commit `b175b8d` | **VALIDADO** (Fuente: historial git) |
| Mensaje commit inicial | "version beta, ... el core se encuentra listo para hacer pruebas" | `git log` | **VALIDADO** (Fuente: historial git) |
| ADR-001 y planes producción | **2026-05-21** (documentación) | `docs/production/ADR_001_instancia_por_cliente.md` | **VALIDADO** (Fuente: `docs/production/ADR_001_instancia_por_cliente.md`) |
| Versión 1.7 referenciada | **2026-06-06** | commit `6500034` | **VALIDADO** (Fuente: historial git) |
| Fase 6 certificación UI | **2026-06-01** | commit `35cd726` | **VALIDADO** (Fuente: historial git) |
| Última actividad registrada | **2026-06-18** | commit `2c552e0` | **VALIDADO** (Fuente: historial git) |
| Total commits analizados | 37 | `git rev-list --all --count` | **VALIDADO** (Fuente: historial git) |

**Primera versión funcional (inferida):** entre **2026-05-27** (commit beta con core listo) y **2026-06-06** (v1.7 operativa según mensaje de commit). Fecha exacta de "versión funcional cerrada": **[PENDIENTE_VALIDACIÓN]** — no existe tag de release ni acta formal. **PENDIENTE DE VALIDACIÓN**

**Periodo de desarrollo documentado en Git (humanos):** aproximadamente **2026-05-27 a 2026-06-18**.

---

## 5. Arquitectura del Sistema

### 5.1 Paradigma arquitectónico

| Patrón | Aplicación | Evidencia | Estado |
|--------|------------|-----------|--------|
| **Domain-Driven Design (DDD)** | Bounded contexts por carpeta bajo `app/` | `docs/Plan_Desarrollo_Servicio_v0.1/DDD_en_la_arquitectura.md` | **VALIDADO** (Fuente: `app/`) |
| **Event-Driven Architecture (EDA)** | Bus de eventos, publicación, suscripciones, observación | `docs/Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md` | **VALIDADO** (Fuente: configuración eventbus) |
| **CQRS (parcial)** | Dashboard como read models; Middleware tracking | `docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md` | **VALIDADO** (Fuente: `app/Dashboard/`) |
| **Instancia por cliente** | Aislamiento físico por despliegue (no multi-tenant lógico activo) | ADR-001 Aceptado | **VALIDADO** (Fuente: `docs/production/ADR_001_instancia_por_cliente.md`) |

### 5.2 Bounded contexts implementados (14 carpetas `app/`)

| Contexto | Rol |
|----------|-----|
| `Middleware` | Event bus, cola, topología, DLQ, workflow |
| `Dashboard` | Observabilidad, feed, métricas, nodos |
| `Control` | Portal SaaS, tenants, simulación, incidentes |
| `Integration` | Canales, webhooks, adaptadores |
| `Shared` | Platform, Identity, EventBus, Logging, API |
| `Observability` | Prometheus, trace logs, SLI |
| `Monitoring` | Alertas, canary |
| `Http` | Controllers, middleware HTTP |
| `Console` | Comandos Artisan plataforma |
| `Quality` | Config calidad |
| `Platform` | Demo pack extensibilidad |
| `Events` | Eventos Laravel plataforma |
| `Models` | User (operador Sanctum) |

### 5.3 Capas por bounded context

```
Interfaces/ (HTTP, Routes, Providers)
    ↓
Application/ (UseCases, Services, DTOs)
    ↓
Domain/ (Entities, ValueObjects, Repositories interfaces)
    ↓
Infrastructure/ (Persistence, Models, EventBus adapters)
```

**Evidencia:** estructura en `app/Middleware/`, `app/Dashboard/`, `docs/Plan_Desarrollo_Modulos_v0.1/`. **VALIDADO** (Fuente: estructura del proyecto `app/`)

### 5.4 Despliegue y ejecución

- Aplicación web Laravel + assets Vite compilados.
- APIs REST bajo `/api/middleware`, `/api/dashboard`, `/api/integrations` y versionadas `/api/v1/*`.
- Scheduler: purga retención, evaluación alertas, canary publish (`routes/console.php`).
- Health: `/up`, `/health/ready` (`bootstrap/app.php`).

### 5.5 Diagrama de arquitectura

[INSERTAR_DIAGRAMA_ARQUITECTURA]

**Referencia textual:** `docs/architecture/er_diagram.md`, `docs/architecture/middleware_database_architecture.md`. **VALIDADO** (Fuente: `docs/architecture/`)

---

## 6. Historial de Versiones (evidenciado)

| Versión / hito | Fecha aprox. | Evidencia | Estado |
|----------------|--------------|-----------|--------|
| beta | 2026-05-27 | Commit inicial `b175b8d` | **VALIDADO** (Fuente: historial git) |
| v0.1 (planes) | 2026-05 | `docs/Plan_Desarrollo_Modulos_v0.1/` | **VALIDADO** (Fuente: `docs/Plan_Desarrollo_Modulos_v0.1/`) |
| v1.1 (plan implementación) | 2026-05-21 | `docs/production/Plan_de_implementacion.md` | **VALIDADO** (Fuente: `docs/production/Plan_de_implementacion.md`) |
| v1.7 | 2026-06-06 | Commit `6500034` | **VALIDADO** (Fuente: historial git) |
| Fase 6 | 2026-06-01 | Commit `35cd726` | **VALIDADO** (Fuente: historial git) |
| post-refactorización | 2026-06-13 a 2026-06-18 | Commits `63c0fa4`, `05c6714`, merges | **VALIDADO** (Fuente: historial git) |

**Tags Git:** ninguno (`git tag -l` vacío).

---

## 7. Observaciones Finales

La presente ficha técnica describe el software **Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)**, obra original de integración por eventos y observabilidad operativa, **sin lógica de negocio vertical embebida** (retail, inventario, pedidos legados eliminados del alcance core según `docs/testing/matrix_validacion_middleware.md`).

**Titular del registro:** [COMPLETAR_MANUALMENTE] — razón social / universidad / persona natural. **PENDIENTE DE VALIDACIÓN**

**Limitaciones conocidas documentadas:**

- Configuración dinámica por cliente sin redeploy: **No cumple** (`docs/production/Plan_de_implementacion.md` §1.1). **VALIDADO** (Fuente: `docs/production/Plan_de_implementacion.md`)
- Pasarela de pago: pendiente según commit inicial. **VALIDADO** (Fuente: historial git)
- OAuth2/SSO enterprise: diferido ADR-002/003. **VALIDADO** (Fuente: `docs/production/ADR_002_defer_oauth2.md`)

**Trazabilidad:** matriz generada en `docs/matriz_generada/` (330 registros, reporte 2026-06-10). **VALIDADO** (Fuente: `docs/matriz_generada/`)

---

*Documento generado automáticamente. Revisar y validar antes de presentación ante INDECOPI.*
