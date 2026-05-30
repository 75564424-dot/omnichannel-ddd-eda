# FICHA TÉCNICA DE SOFTWARE

**Documento:** Registro de propiedad intelectual — obra informática  
**Clasificación:** Confidencial / Uso institucional  
**Fecha de elaboración del documento:** 2026-05-30  
**Repositorio analizado:** `omnichannel-ddd-eda` (Composer: `platform/event-bus-core`)

---

## IDENTIFICACIÓN PREVIA DEL PROYECTO

| Campo | Valor identificado | Evidencia |
|-------|-------------------|-----------|
| **Nombre oficial (técnico)** | `platform/event-bus-core` | `composer.json` — campo `name` y `description` |
| **Nombre comercial / producto** | Middleware omnicanal orientado a eventos (EDA) con Dashboard de observabilidad | `docs/production/Propuesta_Comercial_Modelo_Instancia.md`; `README.md` — título «Omnichannel DDD + EDA» |
| **Nombre comercial registrado / marca** | [INFORMACIÓN NO ENCONTRADA EN EL PROYECTO] | No se encontró registro de marca, denominación legal de producto ni razón social del titular en el repositorio |
| **Versión API pública** | 1.0.0 | `docs/api/CHANGELOG.md` — fecha 2026-05-22 |
| **Versión plan de módulos** | v0.1 | `docs/Plan_Desarrollo_Modulos_v0.1/README.md` |
| **Versión plan de implementación** | 1.1 | `docs/production/Plan_de_implementacion.md` |
| **Estado actual** | **Beta / MVP avanzado** — apto para pruebas controladas y despliegue por instancia; no producto SaaS multi-tenant en una URL | Commit inicial Git: «version beta» (2026-05-27); `Plan_de_implementacion.md` §1.1 distingue «listo para laboratorio/staging» vs «configuración dinámica en runtime sin redeploy» |
| **Fecha estimada de inicio** | 2026-05-27 | `git log --reverse` — primer commit |
| **Fecha última versión identificada en repositorio** | 2026-05-28 | Último commit Git registrado al momento del análisis |
| **Licencia del código** | MIT | `composer.json` — campo `license` |
| **Titular / autor legal** | [INFORMACIÓN NO ENCONTRADA EN EL PROYECTO] | No consta archivo AUTHORS, COPYRIGHT ni metadatos de titularidad |

**Recomendación:** Completar denominación comercial definitiva, titular del derecho patrimonial, país de origen y versión semántica unificada del producto (distinta de la versión de API) en un archivo `LEGAL.md` o en la documentación de registro.

---

## 1. TÍTULO DEL SOFTWARE

### Denominación formal

**Nombre oficial del software (identificador técnico):**  
**Platform Event Bus Core** (`platform/event-bus-core`)

**Nombre comercial descriptivo:**  
**Plataforma Middleware Omnicanal DDD + EDA** — núcleo de bus de eventos genérico con panel de observabilidad y plano de control SaaS.

**Módulo principal:**  
**Middleware (Event Bus)** — servicio de ingestión, enrutamiento, persistencia, trazabilidad y resiliencia de eventos de integración omnicanal.

**Versión de referencia para esta ficha:**  
- API REST: **1.0.0** (`docs/api/CHANGELOG.md`)  
- Plan funcional core: **v0.1** (`docs/Plan_Desarrollo_Modulos_v0.1/README.md`)

### Justificación técnica de la denominación

La denominación **Event Bus Core** responde a la naturaleza arquitectónica del sistema: un **núcleo de plataforma** (`composer.json`: «generic event bus middleware + observability dashboard (no business domains)») que no encapsula reglas de negocio verticales (retail, OMS, inventario), sino que provee **infraestructura de mensajería orientada a eventos** reutilizable por múltiples clientes comerciales.

El sufijo **Omnichannel** y la referencia **DDD + EDA** en la documentación de usuario (`README.md`) reflejan:

1. **Omnicanalidad:** ingestión multi-canal (POS, web, ERP, webhooks, APIs) documentada en `docs/architecture/middleware_database_architecture.md` §1.
2. **Domain-Driven Design (DDD):** organización del código en bounded contexts (`app/Control`, `app/Middleware`, `app/Dashboard`, `app/Integration`, `app/Simulation`, etc.) con capas `Application`, `Domain`, `Infrastructure` e `Interfaces`.
3. **Event-Driven Architecture (EDA):** eventos como ciudadanos de primera clase — `event_store`, `message_queue`, `event_feed_projections`, publicación vía `EventBusPort`, listeners y patrón outbox (`RelayOutboxJob`, tabla `outbox_messages`).

La arquitectura de despliegue **instancia por cliente** (ADR-001) complementa semánticamente el nombre: cada despliegue es un **silo** dedicado con BD, configuración (`eventbus.php`, `modules_config.json`) y URL propias, gobernado opcionalmente desde un **control plane** SaaS.

---

## 2. DESCRIPCIÓN GENERAL DEL SOFTWARE

### Naturaleza del sistema

El software objeto de esta ficha constituye una **plataforma de integración empresarial orientada a eventos**, implementada como aplicación web monolítica modular sobre Laravel 11, con interfaz operativa en Vue 3 + Inertia.js. Su propósito es **intermediar, registrar, enrutar, observar y gobernar el flujo de eventos** entre sistemas productores y consumidores externos (terminales POS, tiendas web, ERP, conectores M2M), sin alojar lógica de dominio comercial del cliente final.

Según `docs/Plan_Desarrollo_Modulos_v0.1/README.md`, el producto se compone de dos piezas inseparables en la oferta estándar:

- **Middleware (servicio primario):** núcleo comercializable de integración EDA — APIs de publicación, cola FIFO, registro de módulos, dead-letter queue (DLQ), topología y control operativo del bus.
- **Dashboard (complemento):** capa de observabilidad — feed de eventos en tiempo real (SSE), KPIs configurables, topología declarativa y estado de nodos, alimentada por proyecciones de lectura derivadas del tráfico del Middleware.

Adicionalmente, el repositorio incorpora un **módulo Control (SaaS / control plane)** accesible en rutas `/control/*` (`routes/control.php`), orientado al operador de la empresa proveedora del servicio, con gestión de empresas-cliente (tenants), planes comerciales, provisioning de instancias, simulaciones de tráfico e incidencias.

### Problema que resuelve

Las organizaciones que operan en entornos **omnicanal** (retail, logística, servicios) enfrentan fragmentación de integraciones: cada canal publica o consume datos con contratos heterogéneos, sin trazabilidad unificada ni observabilidad transversal. Soluciones monolíticas acopladas a un vertical de negocio dificultan reutilización; buses de mensajería genéricos carecen de gobierno declarativo de productores/suscriptores y paneles operativos integrados.

Este software aborda dicho vacío mediante:

1. Un **bus de eventos configurable** (`config/eventbus.php`, overlay JSON, packs de consumidores vía `EventConsumerRegistrationInterface`).
2. **Persistencia especializada para middleware** (no OLTP de negocio), documentada en `docs/architecture/middleware_database_architecture.md`, con dominios: configuración, mensajería, procesamiento, webhooks, observabilidad.
3. **Aislamiento por instancia dedicada** (ADR-001): cada cliente comercial recibe proceso, base de datos y catálogo propios, simplificando cumplimiento y auditoría frente a multi-tenant lógico no activo en runtime.
4. **Plano de control SaaS** con aprovisionamiento automático de silos locales en desarrollo (`LocalFleetInstanceProvisioner`, `PLATFORM_LOCAL_FLEET_AUTO_PROVISION`) y espejo de operadores/catálogo (`LocalFleetTenantMirror`).

### Organizaciones y usuarios destinatarios

| Perfil | Uso evidenciado |
|--------|-----------------|
| **Empresa proveedora SaaS** | Rol `saas_admin` — panel `/control/companies`, provisioning, simulaciones (`config/platform_roles.php`) |
| **Administrador de instancia cliente** | Rol `platform_admin` — `/middleware`, `/dashboard`, gestión de integraciones |
| **Operador de bus** | Rol `bus_operator` — publicación y administración del bus |
| **Visualizador** | Rol `dashboard_viewer` — lectura de métricas y feed |
| **Integrador M2M** | Tokens Sanctum / API keys — abilities `events:publish`, `bus:read`, etc. (`.env.example`) |
| **Retail / comercio omnicanal** | Casos documentados: Acme Retail, fixtures `tests/Fixtures/clients/acmepos/` |

### Alcance funcional

**Incluido (con evidencia de implementación):**

- Publicación HTTP de eventos (`POST /api/middleware/events/publish`; rutas versionadas `/api/v1/middleware/*`).
- Cola, métricas, DLQ, requeue, sync de registro desde catálogo (`SyncConfiguredModulesToRegistryUseCase`).
- Dashboard con feed, KPIs JSON (`dashboard_config.json`), topología desde `modules_config.json`.
- Control plane: CRUD empresas, planes (`config/saas_catalog.php`), operadores, simulaciones.
- Simulación de clientes con fixtures y handoff control plane → silo (`app/Simulation/`).
- Integraciones: canales, credenciales, webhooks (`app/Integration/`).
- Observabilidad Prometheus, correlation ID, SLI/SLO configurables.
- Monitoreo: canary probes, evaluadores de alertas.
- Seguridad: RBAC, Sanctum, API keys, auditoría, idempotencia en publish.
- Despliegue Docker Compose (MySQL, Redis, worker, scheduler).

**Parcialmente implementado o diferido (documentado explícitamente):**

- Multi-tenant lógico en una URL — **diferido** (ADR-001 Fase 3).
- Sagas / compensación — **diferido** (ADR-006; tabla `transactions` schema-only).
- Configuración 100% dinámica sin redeploy — **no cumple** (`Plan_de_implementacion.md` §1.1).
- Pasarela de pago — mencionada como pendiente en commit inicial Git.

### Objetivos principales

1. Proveer **middleware de integración event-driven** comercializable por instancia.
2. Ofrecer **observabilidad operativa** nativa (dashboard + métricas + trazas).
3. Habilitar **gobierno centralizado** de clientes desde control plane con aislamiento físico por silo.
4. Mantener **extensibilidad** mediante packs de integración externos sin modificar el núcleo.
5. Garantizar **trazabilidad, resiliencia y seguridad** enterprise documentadas en planes de producción (`docs/production/Plan_*.md`).

---

## 3. FUNCIONALIDAD PRINCIPAL

### 3.1 Middleware — Bus de eventos

El bounded context `app/Middleware/` constituye el núcleo funcional. Implementa el ciclo de vida completo de un evento de integración:

**Ingesta y publicación.** Los integradores externos invocan la API de publicación. El servicio `EventPublisherService` valida payload (opcionalmente contra JSON Schema vía `opis/json-schema`), aplica guardas de idempotencia (`EventPublishIdempotencyGuard`), persiste en `message_queue` y `event_store`, y despacha al bus runtime a través del puerto hexagonal `EventBusPort` (implementación actual: `LaravelEventBusAdapter`; preparado para Kafka según comentarios en `EventBusPort` y variables `EVENTBUS_KAFKA_*` en `.env.example`).

**Enrutamiento y suscripciones.** La configuración declarativa en `config/eventbus.php` define productores, suscripciones y registradores de consumidores (`consumer_registrars`). El componente `PackSubscriptionCatalogMerger` fusiona catálogos de packs que implementan `EventConsumerRegistrationInterface`, permitiendo extensión sin editar el núcleo. Overlays por cliente en `config/eventbus_client_overlay.json` soportan simulaciones (`Plan_SimulacionClientes`).

**Persistencia operativa.** Migraciones desde 2026-05-21 crean esquema omnicanal: `event_store` (append-only), `message_queue`, `dead_letter_queue`, `event_logs`, `registered_modules`, `retries`, entre otras (`database/migrations/2026_05_21_100001_create_middleware_event_messaging_schema.php`).

**Resiliencia.** Políticas de reintento configurables (`config/eventbus.php` — `max_attempts`, `backoff`), DLQ con requeue manual (`RequeueDeadLetterUseCase`), circuit breaker opcional, procesamiento síncrono/asíncrono (`EVENTBUS_ASYNC_PROCESSING`). Patrón **Transactional Outbox**: tabla `outbox_messages`, job `RelayOutboxJob`, activable con `EVENTBUS_OUTBOX_ENABLED`.

**Registro de módulos.** Endpoint `POST /api/middleware/registry/sync-config` ejecuta `SyncConfiguredModulesToRegistryUseCase`, materializando en `middleware_registered_modules` tanto el catálogo del bus PHP como el declarativo JSON (`modules_config.json`) — extensión B.2 documentada en `Plan_de_implementacion.md`.

**Topología.** `GetTopologySnapshotUseCase` expone vista combinada **configurada + observada**, consumida en UI `/middleware` y API `GET /api/middleware/topology`.

**Casos de uso identificados (muestra representativa):**

| Caso de uso | Clase | Función |
|-------------|-------|---------|
| Publicar evento | `EventPublisherService` | Ingesta y encolado |
| Consultar cola | `GetEventQueueUseCase` | Operación FIFO |
| Métricas del bus | `GetBusMetricsUseCase` | KPIs operativos |
| Buscar evento por ID | `SearchEventByIdUseCase` | Trazabilidad |
| Reencolar DLQ | `RequeueDeadLetterUseCase` | Recuperación de fallos |
| Sincronizar catálogo | `SyncConfiguredModulesToRegistryUseCase` | Gobierno declarativo |

### 3.2 Dashboard — Observabilidad

El bounded context `app/Dashboard/` implementa **proyecciones de lectura (CQRS)** sobre datos producidos por el Middleware:

- **Feed de eventos:** `GetRecentEventFeedUseCase`, streaming SSE vía `StreamLiveEventsUseCase` / `EventStreamController`.
- **Métricas globales y series dinámicas:** `GetGlobalMetricsUseCase`, `GetDynamicMetricSeriesUseCase`, configuración en JSON.
- **Topología declarativa:** `GetEventFlowDiagramDataUseCase`, catálogo `GetModulesCatalogUseCase` desde `config/modules.php` ← `modules_config.json`.
- **Estado de nodos:** `GetSystemNodeStatusUseCase`, `RefreshSystemNodeUseCase`, `SetNodeMiddlewareEventsUseCase`.
- **Ingesta reactiva:** listeners como `MiddlewareMetricsListener`, `DashboardBusEventIngestionService` proyectan tráfico del bus hacia tablas de feed y métricas.

La UI web en `/dashboard` (`DashboardWebController`, componentes Vue en `resources/js/`) consume APIs internas bajo autenticación de operador.

### 3.3 Control — Plano SaaS y gestión de clientes

El bounded context `app/Control/` expone el **control plane** en rutas `routes/control.php`:

- **Overview** operativo del fleet (`OverviewController`).
- **Gestión de empresas** (`CompanyController`): alta, suspensión, activación, asignación de plan y módulos comerciales (`config/saas_catalog.php` — planes Starter, Growth, Enterprise).
- **Catálogo de módulos por tenant:** edición de `modules_catalog` en settings, aplicación a instancia (`TenantModuleCatalogService::applyToCurrentInstance`).
- **Operadores por tenant:** creación, roles, contraseñas.
- **Provisioning** (`ProvisioningController`): registro de nueva empresa con auto-provisión de silo local si `PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true`.
- **Simulaciones** (`SimulationRunController`): orquestación de tráfico de prueba desde control plane hacia silos cliente.
- **Incidencias y soporte:** `IncidentsController`, reportes cliente (`client_incident_reports`), notificaciones web.
- **Middleware global e infraestructura:** vistas agregadas para operador SaaS.

### 3.4 Simulation — Rehearsal de clientes

`app/Simulation/` automatiza preparación y ejecución de simulaciones:

- Comandos: `platform:simulation:prepare`, ejecución en silo (`ClientSiloSimulationExecutor`).
- Handoff control plane ↔ instancia (`SimulationRunHandoffStore`, API interna con token `PLATFORM_SIMULATION_INTERNAL_TOKEN`).
- Fixtures en `tests/Fixtures/clients/{acmepos,retailco}/` con `modules_config.json`, `eventbus_overlay.json`, `sample_events.json`.
- Mapeo tenant → fixture en `config/platform.php` → `simulation.tenant_fixture_map`.

### 3.5 Integration — Conectores externos

`app/Integration/` gestiona:

- CRUD de integraciones y canales (Use Cases: `CreateIntegrationUseCase`, `ListChannelsUseCase`, etc.).
- Credenciales cifradas (`IntegrationCredentialCipher`).
- Recepción de webhooks (`ReceiveWebhookUseCase`, `WebhookIngressController`) con verificación de firma (`WebhookSignatureVerifier`).
- Despacho saliente (`DispatchOutboundConnectorUseCase`, `OutboundConnectorInterface`).
- Pipeline de adaptadores (`AdapterPipeline`, `FieldMapAdapter`).

APIs bajo `/api/integrations/*` y `/api/v1/integrations/*` (`docs/api/openapi.yaml`).

### 3.6 Observabilidad, monitoreo y calidad

- **Observability** (`app/Observability/`): métricas SLI, spans, endpoint Prometheus (`PLATFORM_PROMETHEUS_ENABLED`), proyección de lag del feed.
- **Monitoring** (`app/Monitoring/`): canary events (`PLATFORM_CANARY_EVENT_TYPE`), evaluadores de alertas (error rate, latencia, DLQ, profundidad de cola, uso de BD).
- **Quality** (`app/Quality/`): umbral de cobertura mínima (`PLATFORM_QUALITY_COVERAGE_MIN=70`), integración con CI.

### 3.7 Procesos automatizados

| Proceso | Mecanismo | Evidencia |
|---------|-----------|-----------|
| Bootstrap de instancia cliente | `platform:instance:bootstrap` → `ClientInstanceBootstrapService` | Seed tenant + catálogo MODULES_CONFIG_PATH |
| Fleet bootstrap local | `platform:fleet:bootstrap-control-plane --provision` | Import legacy.start legacy, provisiona silos, mirror |
| Validación catálogo CI | `platform:validate-catalog` | `composer.json` script `validate-config` |
| Retención de datos | `platform:purge-retention` (documentado) | `config/platform_retention.php`, `.env.example` |
| Relay outbox | `RelayOutboxJob` | Cola Laravel |
| Emisión mock | `platform:emit-mock` | `EmitMockPlatformEventCommand` |
| Sync fleet | `platform:fleet:sync-local` | `npm run instances:sync` |

### 3.8 Beneficios funcionales

- **Operación unificada** del bus y su observabilidad en un solo despliegue.
- **Reducción de divergencia** entre catálogo declarativo y registro persistente (sync-config ampliado B.2).
- **Onboarding acelerado** mediante scripts `instances:bootstrap`, fleet auto-provision y runbooks (`docs/production/Runbook_Onboarding_Cliente.md`).
- **Pruebas pre-producción** con simulación automatizada y fixtures versionados.
- **Seguridad enterprise** con RBAC, tokens, idempotencia, auditoría y Problem Details RFC 7807.

---

## 4. CARACTERÍSTICAS DIFERENCIADORAS

| Elemento distintivo | Descripción técnica | Originalidad / valor |
|--------------------|---------------------|----------------------|
| **Instancia por cliente con control plane** | ADR-001: silo físico (app+BD+config) gobernado desde registro SaaS; mirror automático de operadores y catálogo (`LocalFleetTenantMirror`) | Modelo híbrido: aislamiento fuerte sin renunciar a gestión centralizada; distinto de SaaS multi-tenant puro |
| **Dual catálogo sincronizado** | Coexistencia `eventbus.php` + `modules_config.json` con sync unificado al registry y validación CI (`PlatformCatalogValidator`) | Aborda problema real de divergencia UI vs enrutamiento documentado en Plan_de_implementacion |
| **Extensión por packs** | `EventConsumerRegistrationInterface` + merge en boot | Integración de clientes sin fork del core |
| **Middleware sin dominio de negocio** | Explícito en composer.json y planes v0.1 | Reutilización vertical-agnóstica vs suites retail monolíticas |
| **Simulación orchestrada** | Control plane dispara tráfico en silo remoto con handoff y reportes | Rehearsal de producción sin mocks manuales |
| **Esquema de persistencia EDA-native** | event_store append-only, outbox, DLQ, correlation_id, proyecciones CQRS | Diseño orientado a trazabilidad y replay, no OLTP retail |
| **Aprovisionamiento local automatizado** | Registry JSON + generación .env + SQLite + bootstrap + mirror | Reduce fricción de desarrollo multi-instancia |
| **API versionada + legacy** | `/api/v1/*` coexistiendo con `/api/*` | Migración gradual para integradores |
| **Observabilidad embebida** | Prometheus, SLI/SLO, SSE feed, Grafana JSON en docs | Producto operable, no solo bus headless |
| **Gobierno comercial de módulos** | Planes Starter/Growth/Enterprise con módulos asignables | Enlace negocio-técnica en `saas_catalog.php` |

**Nota sobre multi-tenancy:** El runtime productivo **no** implementa multi-tenant lógico compartido (ADR-001). La columna `tenant_id` existe preparatoria (ADR-004) pero el aislamiento operativo es **por instancia**. Esto constituye una decisión arquitectónica diferenciadora, no una limitación omitida.

---

## 5. LENGUAJES DE PROGRAMACIÓN Y TECNOLOGÍAS UTILIZADAS

### Tabla de tecnologías

| Tecnología | Versión (evidenciada) | Uso dentro del sistema |
|------------|----------------------|------------------------|
| **PHP** | ^8.2 (`composer.json`) | Backend principal, dominio, APIs, comandos Artisan |
| **Laravel Framework** | ^11.0 | MVC, ORM Eloquent, colas, migraciones, Sanctum, scheduling |
| **JavaScript (ES Modules)** | — (Node 18+ implícito en README) | Scripts de desarrollo local, Vite, Playwright |
| **Vue.js** | ^3.4.21 | SPA/Inertia — UI operador y control plane |
| **Inertia.js** | ^3.0 (PHP + Vue) | Puente server-driven SPA sin API REST separada para UI |
| **Tailwind CSS** | ^3.4.1 | Estilos UI |
| **Vite** | ^5.2.0 | Bundler frontend |
| **SQLite** | — (driver Laravel) | BD por defecto en desarrollo multi-instancia |
| **MySQL** | 8.0 (`docker-compose.yml`) | BD recomendada producción |
| **Redis** | 7-alpine (`docker-compose.yml`) | Colas, cache, sesiones (perfil cloud) |
| **Laravel Sanctum** | 4.0 | Autenticación API/token operadores |
| **Ramsey UUID** | ^4.7 | Identificadores de negocio |
| **OPIS JSON Schema** | 2.3 | Validación opcional de payloads en publish |
| **Pest PHP** | ^3.0 | Framework de pruebas |
| **PHPStan** | ^1.10 | Análisis estático |
| **Playwright** | ^1.49.0 | Pruebas E2E UI |
| **Prometheus/Grafana** | configs en `docs/monitoring/`, `docs/observability/` | Métricas y dashboards (externos) |
| **Docker / Docker Compose** | — | Contenedorización FPM + Nginx + MySQL + Redis + worker |
| **k6** | script en `docs/testing/load/` | Pruebas de carga |
| **OpenAPI / Spectral** | 3.0 (`docs/api/openapi.yaml`) | Contrato API y lint CI |
| **Nginx** | `docker/nginx/default.conf` | Reverse proxy en contenedor |

### Justificación técnica de la selección

**PHP 8.2 + Laravel 11** proporcionan ecosistema maduro para aplicaciones enterprise con ORM, migraciones versionadas, colas, scheduling y autenticación — requisitos del middleware (persistencia, jobs outbox, workers). La tipificación estricta (`declare(strict_types=1)` generalizado en `app/`) refuerza robustez.

**Vue 3 + Inertia** evitan duplicar capa API para UI interna, manteniendo experiencia SPA con estado server-side — adecuado para paneles operativos (`/middleware`, `/dashboard`, `/control`).

**SQLite en desarrollo multi-instancia** (`database/instances/{slug}.sqlite`) evidencia aislamiento real por cliente en local; **MySQL/Redis en producción** (`Plan_Cloud`, `docker-compose.yml`) escalan throughput y concurrencia.

**Sanctum + RBAC propio** (`platform_roles.php`) cubren autenticación web de operadores y M2M con abilities granulares, alineado a ADR-002/003.

**Pest + PHPStan + CI compuesto** (`composer.json` → script `ci`) garantizan regresión automatizada — 121+ archivos de prueba identificados en `tests/`.

---

## 6. ARQUITECTURA DEL SISTEMA

### 6.1 Visión general

El sistema adopta un **monolito modular** organizado por **Bounded Contexts** (DDD táctico), desplegable como **una instancia Laravel por cliente comercial** (ADR-001), con opción de host **control plane** que centraliza metadatos de múltiples clientes sin compartir base de datos operativa entre ellos.

```
┌─────────────────────────────────────────────────────────────────┐
│                    CONTROL PLANE (SaaS)                          │
│  app/Control — /control/* — tenants, planes, provisioning      │
│  BD: platform.sqlite (registro comercial + operadores SaaS)       │
└───────────────────────────┬─────────────────────────────────────┘
                            │ mirror / handoff simulación
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
┌───────────────┐   ┌───────────────┐   ┌───────────────┐
│ Silo Cliente A│   │ Silo Cliente B│   │ Silo Cliente N│
│ Middleware    │   │ Middleware    │   │ ...           │
│ Dashboard     │   │ Dashboard     │   │               │
│ Integration   │   │ Integration   │   │               │
│ BD dedicada   │   │ BD dedicada   │   │ BD dedicada   │
└───────────────┘   └───────────────┘   └───────────────┘
```

Evidencia: `deploy/local-instances/instances.json`, `fleet-registry.json`, `scripts/local-instances/bootstrap.mjs`, `LocalFleetTenantMirror`.

### 6.2 Domain-Driven Design (DDD)

**Evidencia estructural directa** — cada contexto delimitado bajo `app/{Context}/` sigue capas:

| Capa | Responsabilidad | Ejemplo |
|------|-----------------|---------|
| **Domain** | Entidades, value objects, interfaces repositorio | `Middleware/Domain/Entities/StoredEvent.php`, `BusStatus.php` |
| **Application** | Casos de uso, servicios de aplicación, DTOs | `GetEventQueueUseCase`, `EventPublisherService` |
| **Infrastructure** | Eloquent, adaptadores, jobs | `EloquentQueueEntryRepository`, `LaravelEventBusAdapter` |
| **Interfaces** | Controllers HTTP, Service Providers, Routes | `EventQueueController`, `MiddlewareServiceProvider` |

Contextos identificados: **Control**, **Dashboard**, **Middleware**, **Integration**, **Simulation**, **Monitoring**, **Observability**, **Quality**, **Shared**, **Platform** (demo packs).

**Shared Kernel** (`app/Shared/`): contratos (`EventBusPort`), identidad (`AuthenticateOperatorUseCase`), seguridad (`AuditLogWriter`), plataforma (`InstanceDeploymentService`, fleet local).

**Ubiquitous Language:** evento, productor, suscriptor, cola, dead letter, tenant (como metadato de instancia), módulo registrado — consistente en código, migraciones y documentación.

### 6.3 Event-Driven Architecture (EDA)

1. **Publicación:** API → `EventPublisherService` → persistencia `message_queue` + `event_store` → dispatch `EventBusPort`.
2. **Consumo:** listeners Laravel registrados vía catálogo fusionado; tracking en `BusTrackingListener`.
3. **Proyección:** listeners de Dashboard actualizan feed y métricas (read models).
4. **Outbox:** patrón opcional para publicación at-least-once hacia bus externo.
5. **Correlation:** `CorrelationIdMiddleware`, columna `correlation_id` transversal.

No se utiliza broker Kafka en runtime por defecto (`EVENTBUS_DRIVER=laravel`); la arquitectura **anticipa** migración mediante puerto `EventBusPort`.

### 6.4 CQRS (Command Query Responsibility Segregation)

**Separación evidenciada:**

- **Escritura (commands):** publicación, sync registry, requeue DLQ, CRUD integraciones — servicios de aplicación mutan estado operativo.
- **Lectura (queries):** `GetRecentEventFeedUseCase`, `GetGlobalMetricsUseCase`, repositorios de métricas — consultan proyecciones (`event_feed_entries`, `observability_metrics`, `bus_metrics_snapshots`) sin mutar agregados de escritura.

Documentado explícitamente en `middleware_database_architecture.md` §3 objetivo 6 y §5 principio «Separación escritura/lectura».

### 6.5 Arquitectura hexagonal (Ports & Adapters)

- **Puerto:** `EventBusPort`, `OutboundConnectorInterface`, `AuditLogWriterInterface`, repositorios en `Domain/Repositories/*Interface`.
- **Adaptador:** `LaravelEventBusAdapter`, `Eloquent*Repository`, `BusExternalEventPublisher`.

Inversión de dependencias registrada en Service Providers (`MiddlewareServiceProvider`, `IntegrationServiceProvider`).

### 6.6 Multi-tenancy

**Estado real:** modelo **instancia por cliente**, NO multi-tenant compartido en runtime.

- Tabla `tenants`: metadatos de instancia (ADR-001).
- `tenant_id` en tablas operativas: preparación futura (ADR-004).
- Control plane lista múltiples tenants en **su** BD; cada silo tiene **un** tenant activo (`InstanceTenantSeeder` en silos cliente).
- Aislamiento: BD separada, `.env` separado, `MODULES_CONFIG_PATH` por slug.

### 6.7 Microservicios

**No aplica como despliegue actual.** El sistema es monolito modular. La documentación (`docs/Analisis_v0.1/Microservices and event-driven architecture...`) es material de análisis, no evidencia de despliegue microservicio. Escalabilidad horizontal documentada como **réplicas de instancia completa** (ADR-001, Propuesta Comercial).

### 6.8 Flujo de información (publicación → observación)

```
Integrador → POST /api/middleware/events/publish
    → Validación + Idempotencia
    → message_queue + event_store
    → EventBusPort → Listeners
        → BusTrackingListener (estado cola)
        → Dashboard listeners → event_feed_projections
    → SSE /metrics → Operador en /dashboard
```

### 6.9 Seguridad arquitectónica

Capas: middleware HTTP (`AuthenticatePlatformApi`, `EnforcePlatformAbility`, `EnsureControlPlaneHost`, `EnsureInstanceWebAuth`), RBAC, cifrado credenciales integración, headers seguridad (`PLATFORM_SECURITY_HEADERS`), CORS allowlist.

### 6.10 Escalabilidad y mantenibilidad

- **Escalabilidad vertical/horizontal de instancia:** Docker Compose con worker y scheduler; Redis para colas; índices de retención en migraciones.
- **Mantenibilidad:** bounded contexts, ADRs numerados (001–009), planes de producción modulares, CI con lint/analyse/test/validate-config/openapi.
- **Extensibilidad:** packs, overlays JSON, módulos comerciales configurables.

---

## 7. MÓDULOS FUNCIONALES

| Módulo | Objetivo | Funciones principales |
|--------|----------|----------------------|
| **Middleware** | Núcleo bus de eventos | Publish API, cola FIFO, DLQ, requeue, topología, sync registry, métricas bus, outbox, workflows (schema) |
| **Dashboard** | Observabilidad operativa | Feed eventos, SSE stream, KPIs configurables, topología declarativa, estado nodos, visibilidad módulos |
| **Control** | Plano SaaS / gobierno fleet | Empresas, planes, módulos comerciales, operadores, provisioning, simulaciones, incidencias, infraestructura global |
| **Integration** | Conectividad externa | Integraciones, canales, credenciales cifradas, webhooks entrantes, conectores salientes, adaptadores |
| **Simulation** | Rehearsal pre-producción | Prepare/run simulaciones, fixtures cliente, handoff CP→silo, reportes de corrida |
| **Observability** | Telemetría | Prometheus, SLI/SLO, trace spans, lag feed |
| **Monitoring** | Alerting operativo | Canary probes, evaluadores umbrales, alertas bus/DLQ/cola/BD |
| **Quality** | Gobernanza calidad | Cobertura mínima, gates CI |
| **Shared/Identity** | Transversal | Login operador, tokens API, RBAC, auditoría |
| **Shared/Platform** | Despliegue e instancias | Bootstrap silo, fleet local, validación catálogo, portal access guard |
| **Platform/Demo** | Pack demostración | `DemoPackEventConsumers` — registrador ejemplo |

---

## 8. BASE DE DATOS

### Tipo y motor

| Entorno | Motor | Evidencia |
|---------|-------|-----------|
| Desarrollo local multi-instancia | SQLite | `database/instances/{slug}.sqlite`, `.env.example` |
| Producción recomendada | MySQL 8.0 | `docker-compose.yml`, `.env.example` comentarios |
| Cache/colas producción | Redis 7 | `docker-compose.yml` |

### Modelo utilizado

**Modelo relacional** con esquema **modular por dominios lógicos** dentro de una BD por instancia (`docs/architecture/middleware_database_architecture.md`):

1. **Configuración:** `tenants`, `channels`, `providers`, `integrations`, `integration_credentials`, `system_configurations`
2. **Mensajería:** `event_store`, `event_logs`, `message_queue`, `dead_letter_queue`
3. **Procesamiento:** `retries`, `workflows`, `workflow_steps`, `transactions` (schema; sin código saga activo)
4. **Webhooks/notificaciones:** tablas en migración `2026_05_21_100003_*`
5. **Observabilidad:** `observability_metrics`, `event_feed_projections`, `trace_logs`, `audit_logs`
6. **Registro módulos:** `middleware_registered_modules` (evolución de nombre documentada)
7. **Identidad:** `users`, `personal_access_tokens`, `sessions`
8. **Simulación:** `simulation_runs`
9. **Soporte:** `client_incident_reports`
10. **Outbox:** `outbox_messages`

### Entidades principales y relaciones

- **Tenant** (`TenantModel`) 1:N **Users** (con `tenant_id`, migración `2026_05_27_230000_*`).
- **Event store** referencia opcionalmente `tenant_id`, `channel_id`, `integration_id` (FKs en migración).
- **Message queue** vinculada a eventos por `event_uuid` único.
- **Soft delete** en tenants (`SoftDeletes` en `TenantModel`).

### Estrategia de persistencia

- **UUIDs** como IDs de negocio expuestos (Ramsey UUID).
- **Append-only** en `event_store`, logs de auditoría/traza.
- **Proyecciones** para lectura Dashboard (desnormalización controlada).
- **Retención configurable** (`platform_retention.php`, variables `RETENTION_*`).
- **Outbox** para consistencia eventual con bus externo.

### Escalabilidad de datos

Índices en migraciones (`2026_05_21_140000_add_retention_query_indexes`), particionamiento lógico por instancia (no por tenant_id en runtime), ADR-005 sobre particionamiento event store (documentado), purga batch documentada.

---

## 9. SEGURIDAD

| Aspecto | Implementación evidenciada |
|---------|---------------------------|
| **Autenticación web** | Sesión Laravel + middleware `auth.platform.web`; login `LoginController` + `AuthenticateOperatorUseCase` |
| **Autenticación API** | Sanctum tokens (`IssueApiTokenUseCase`), API keys estáticas `PLATFORM_API_KEYS` (`AuthenticatePlatformApi`) |
| **Autorización** | RBAC `config/platform_roles.php`; middleware `EnforcePlatformAbility`; abilities: `events:publish`, `bus:read`, `bus:admin`, `dashboard:read`, `integrations:admin`, `control:manage`, etc. |
| **Roles UI** | `saas_admin`, `platform_admin`, `bus_operator`, `dashboard_viewer` |
| **Roles M2M** | `api_integrator` vía scopes token (documentado, no rol UI) |
| **Aislamiento instancia** | `EnsureAuthenticatedInstanceBinding`, `InstancePortalAccessGuard`, `EnsureControlPlaneHost` |
| **Protección datos** | Credenciales integración cifradas; cookies sesión únicas por instancia local (`scripts/local-instances/lib.mjs`) |
| **Auditoría** | `AuditLogWriter`, `PLATFORM_AUDIT_ENABLED` |
| **Trazabilidad** | `correlation_id` en middleware HTTP y persistencia |
| **Validación entrada** | JSON Schema opcional; validación Laravel requests; Problem Details RFC 7807 |
| **Rate limiting** | `PLATFORM_RATE_LIMIT_*` por tipo operación |
| **Idempotencia** | Header `Idempotency-Key` en publish (`PLATFORM_API_IDEMPOTENCY_ENABLED`) |
| **Headers seguridad** | `PLATFORM_SECURITY_HEADERS`, CSP configurable |
| **CORS** | `CORS_ALLOWED_ORIGINS` allowlist |
| **Webhooks** | Firma HMAC (`WebhookSignatureVerifier`), `INTEGRATIONS_WEBHOOK_REQUIRE_SECRET` |

Documentación complementaria: `docs/production/Plan_Seguridad.md`, `Matriz_Endpoints_Seguridad.md`, `ADR_002_autenticacion_enterprise.md`, `ADR_003_usuarios_enterprise.md`.

---

## 10. ENTORNO DE EJECUCIÓN

### Sistemas operativos

- Desarrollo documentado en **Windows** (scripts PowerShell compatibles, rutas en README) y entornos Unix (Docker, bash CI en `scripts/ci/`).
- [INFORMACIÓN NO ENCONTRADA EN EL PROYECTO] — matriz oficial de SO soportados en producción.

### Navegadores

- [INFORMACIÓN NO ENCONTRADA EN EL PROYECTO] — lista explícita de navegadores certificados.
- Evidencia indirecta: UI Vue/Inertia moderna; Playwright Chromium en `npm run test:ui`; Sanctum stateful domains para `127.0.0.1` y `localhost`.

### Infraestructura requerida

| Componente | Mínimo (dev) | Producción (documentada) |
|------------|--------------|--------------------------|
| PHP | 8.2+ | 8.2+ con extensiones Laravel |
| Node.js | 18+ (README) | Para build assets |
| BD | SQLite | MySQL 8.0 |
| Cache/Queue | database/sync | Redis |
| Servidor web | `artisan serve` / PHP-FPM | Nginx + FPM (Dockerfile) |
| Contenedores | Opcional | `docker-compose.yml` — app, worker, scheduler, mysql, redis |

### Dependencias

- PHP: ver §5 tabla (`composer.json`).
- JS: ver §5 tabla (`package.json`).

### Servicios externos integrados

| Servicio | Estado |
|----------|--------|
| **Kafka** | Preparado (config comentada), no activo por defecto |
| **Prometheus/Grafana** | Configs de ejemplo en `docs/` |
| **MySQL/Redis** | Infraestructura Docker |
| **Pasarela de pago** | [INFORMACIÓN NO ENCONTRADA — pendiente según commit Git inicial] |
| **OpenTelemetry** | ADR-009 propuesto/documentado |

---

## 11. FECHA DE CREACIÓN Y EVOLUCIÓN

### Cronología basada en evidencia Git y documental

| Fecha | Hito | Fuente |
|-------|------|--------|
| 2026-05-21 | ADR-001 instancia por cliente; migraciones esquema middleware omnicanal; Plan_de_implementacion v1.1 | ADRs, migraciones `2026_05_21_*`, docs |
| 2026-05-22 | API v1.0.0 publicada; OpenAPI, idempotencia, Problem Details | `docs/api/CHANGELOG.md` |
| 2026-05-27 | **Inicio repositorio Git** — commit «Initial commit: version beta…» | `git log` |
| 2026-05-28 | Fase aislamiento por cliente; refactor simulación; BD en repo para duplicados | Commits Git |
| 2026-05-28 | Migración `simulation_runs`; normalización canal middleware | `2026_05_28_*`, `2026_05_27_*` migraciones |
| 2026-05-30 | README multi-instancia; correcciones bootstrap fleet | README.md (análisis sesión actual) |

### Versiones identificadas

- API REST: **1.0.0**
- Plan módulos core: **v0.1**
- Plan implementación: **1.1**
- Arquitectura BD middleware: **1.0**

### Estado actual

**Beta / MVP avanzado** — core funcional para pruebas de integración omnicanal por instancia dedicada, con control plane SaaS, simulación y documentación de producción extensa. Elementos enterprise (multi-tenant runtime, sagas, pago) **diferidos o no implementados**.

---

## 12. ALCANCE DEL SOFTWARE

### Qué cubre

- Middleware event bus genérico con APIs, UI y persistencia especializada.
- Dashboard observabilidad acoplado al bus.
- Control plane SaaS para gestión de clientes, planes y provisioning.
- Integraciones (webhooks, canales, credenciales).
- Simulación de tráfico cliente.
- Seguridad RBAC + API tokens + auditoría base.
- Despliegue Docker y desarrollo multi-instancia local.
- Documentación técnica, runbooks, ADRs, OpenAPI.

### Qué no cubre

- Dominios de negocio retail (productos, pedidos, inventario OLTP) — explícitamente excluidos.
- Multi-tenant lógico en una URL — diferido ADR-001 Fase 3.
- Configuración runtime sin redeploy — declarado «No cumple» en Plan_de_implementacion.
- Sagas compensatorias — ADR-006 diferido.
- Pasarela de pago — pendiente (commit inicial).
- Orquestación workflow en producción — schema sin uso completo documentado.

### Límites operativos

- Cola `sync` por defecto en `.env.example` — no distribuido hasta Redis/worker.
- Outbox deshabilitado por defecto.
- Circuit breaker deshabilitado por defecto.

### Restricciones conocidas

- Dual fuente catálogo (`eventbus.php` vs `modules_config.json`) requiere disciplina operativa o sync explícito.
- Divergencia potencial topología declarada vs observada si no hay tráfico.

---

## 13. VALOR TECNOLÓGICO E INNOVACIÓN

### Aporte tecnológico

El software aporta una **implementación integrada** de middleware EDA + observabilidad + gobierno SaaS bajo patrón **instancia dedicada**, con extensión declarativa y validación automatizada de catálogo — conjunto no trivial en soluciones genéricas de mensajería ni suites monolíticas verticales.

### Beneficios empresariales

- Time-to-market en integraciones omnicanal con panel operativo incluido.
- Modelo comercial por instancia + volumen (`Propuesta_Comercial_Modelo_Instancia.md`).
- Cumplimiento facilitado por aislamiento físico de datos por cliente.

### Beneficios operativos

- Visibilidad end-to-end (correlation ID, feed, DLQ, métricas).
- Simulación orchestrada pre-go-live.
- Runbooks y CI reducen error humano en configuración.

### Diferenciación frente a soluciones tradicionales

| Enfoque tradicional | Este software |
|--------------------|---------------|
| ESB monolítico acoplado | Core sin dominio negocio + packs |
| Bus puro sin UI | Bus + Dashboard + Control integrados |
| SaaS multi-tenant único | Instancia dedicada + control plane |
| Config solo en código | Dual catálogo + sync + validación CI |
| Integración ad-hoc | Esquema persistencia EDA-native unificado |

---

## 14. OBSERVACIONES FINALES

La obra informática analizada — identificada técnicamente como **platform/event-bus-core** y comercialmente como plataforma **Middleware Omnicanal DDD + EDA** — constituye un desarrollo **original** en tanto:

1. **Arquitectura propia** documentada en nueve ADRs y planes de producción, con decisiones explícitas (instancia por cliente, diferimiento multi-tenant, outbox opcional).
2. **Código fuente extenso** (~514 archivos PHP en `app/`, 121+ archivos de prueba, 31 migraciones) organizado en bounded contexts DDD con casos de uso identificables.
3. **Esquema de datos especializado** para middleware omnicanal, abandonando modelo retail legacy documentado en `middleware_database_architecture.md`.
4. **Mecanismos diferenciadores** verificables: fleet local auto-provision, mirror tenant, simulación orchestrada, sync dual catálogo, extensión por packs.
5. **Documentación técnica volumétrica** (>169 archivos en `docs/`) incluyendo OpenAPI, runbooks, matrices seguridad y propuesta comercial.

El conjunto supera la mera combinación de librerías de terceros (Laravel, Vue), al implementar **lógica de dominio propia** de integración, gobierno y observabilidad omnicanal. Cumple los criterios habituales de **obra intelectual protegible** en materia de software: originalidad, concreción en código fuente y documentación, y capacidad de reproducción identificada.

Se recomienda al titular completar: denominación comercial registrada, versión de producto unificada, datos del autor/titular, capturas de pantalla certificadas y hash de integridad del código fuente para anexar al expediente INDECOPI.

---

## MATRIZ DE CONFIANZA

| Campo | Nivel de confianza | Evidencia encontrada |
|-------|-------------------|----------------------|
| Nombre técnico oficial | **Alta** | `composer.json` |
| Nombre comercial | **Media** | README, Propuesta Comercial — sin marca registrada |
| Titular / autor legal | **Sin evidencia** | No encontrado en repo |
| Versión API 1.0.0 | **Alta** | `docs/api/CHANGELOG.md` |
| Estado Beta/MVP | **Alta** | Git commit inicial + Plan_de_implementacion |
| Fecha inicio 2026-05-27 | **Alta** | Git log |
| PHP 8.2 / Laravel 11 | **Alta** | `composer.json` |
| Vue 3 / Vite 5 | **Alta** | `package.json` |
| Arquitectura DDD | **Alta** | Estructura `app/{Context}/{Domain,Application,...}` |
| Arquitectura EDA | **Alta** | event_store, message_queue, EventBusPort, listeners |
| CQRS | **Media-Alta** | Read models Dashboard vs write Middleware; doc arquitectura |
| Hexagonal | **Media** | Ports/adapters identificados; no documento ADR exclusivo |
| Multi-tenant runtime | **Alta (negativa)** | ADR-001 — NO activo; instancia por cliente |
| Instancia por cliente | **Alta** | ADR-001, config platform, scripts local-instances |
| Control plane SaaS | **Alta** | `routes/control.php`, `app/Control/` |
| Auto-provision fleet | **Alta** | `LocalFleetInstanceProvisioner`, `.env.example` |
| Microservicios | **Alta (negativa)** | Monolito modular — sin despliegue microservicio |
| Sagas/CQRS completo | **Media (parcial)** | Schema transactions; ADR-006 diferido |
| MySQL/Redis producción | **Alta** | docker-compose, Plan_Cloud |
| Kafka integración | **Baja** | Solo config comentada |
| Pasarela pago | **Sin evidencia** | Mención pendiente commit Git |
| Navegadores soportados | **Sin evidencia** | Inferencia Playwright Chromium |
| SO soportados | **Baja** | Docker multiplataforma; dev Windows documentado |
| 121 archivos prueba | **Alta** | Conteo `tests/**/*.php` |
| OpenAPI 3.0 | **Alta** | `docs/api/openapi.yaml` |
| Originalidad diferenciada | **Media-Alta** | Análisis comparativo basado en componentes propios |
| Planes comerciales Starter/Growth/Enterprise | **Alta** | `config/saas_catalog.php` |
| Licencia MIT | **Alta** | `composer.json` |

---

*Documento generado mediante análisis estático del repositorio `omnichannel-ddd-eda`. Toda afirmación sin fuente explícita ha sido marcada como información no encontrada. Se recomienda revisión jurídica del titular antes de presentación oficial.*
