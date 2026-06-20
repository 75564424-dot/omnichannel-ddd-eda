**Documento 6**  
**ANEXOS TÉCNICOS — VERSIÓN COMPLETADA**

> **Fuente de generación:** análisis del repositorio `omnichannel-ddd-eda` (2026-06-10).  
> **Plantilla base:** `Documento 6 - Anexos técnicos.docx.md` (intacta).

---

## 1. Diagrama de Flujo del Sistema

### 1.1 Flujo implementado — Publicación y observación de eventos

El flujo principal **implementado en código** del Middleware y Dashboard sigue la cadena:

```
[Integrador API / Productor host]
        │ POST /api/middleware/events/publish
        ▼
[EventPublisherService — validación mínima del sobre]
        │
        ├──► Persistencia QueueEntry (bus_queue_entries)
        ├──► Métricas / tracking
        └──► EventBusPort → Laravel Event dispatcher (o Kafka si configurado)
                    │
                    ▼
            [Listeners suscriptores según eventbus.php]
                    │
                    ▼
            [UniversalDashboardFeedListener — wildcard *]
                    │
                    ▼
            [EventFeedProjector → event_feed_entries]
                    │
                    ▼
            [API /api/dashboard/events/feed + UI /dashboard]
```

**Fuente:** `docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md` §6; `docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md` §6; `app/Middleware/Application/Services/EventPublisherService.php`.

### 1.2 Flujo documentado — Pipeline middleware 5 etapas (referencia arquitectónica)

Documentación de servicio describe pipeline conceptual:

| Etapa | Nombre | Descripción | Estado |
|-------|--------|-------------|--------|
| 3.1 | Ingesta | Recepción desde ERP/POS/e-commerce vía conectores | **VALIDADO** |
| 3.2 | Procesamiento y validación | Validación, transformación, enriquecimiento | **VALIDADO** |
| 3.3 | Enrutamiento y publicación | Tópicos Inventario/Pedido/Cliente/Producto/Logística | **VALIDADO** |
| 3.4 | Distribución | Broker de mensajes, colas | **VALIDADO** |
| 3.5 | Consumo | Servicios de dominio independientes | **VALIDADO** |

**Fuente:** `docs/Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md` §3. **VALIDADO**

**Nota:** `docs/production/Plan_Middleware.md` documenta brecha entre flujo 5 etapas y **implementación reducida** en core agnóstico (tracking + publish + observación). Los tópicos retail documentales **no están implementados** como dominios en el núcleo actual.

### 1.3 Flujo Control Plane — Gestión empresa y simulación

```
[Admin SaaS] → /control/companies → CRUD tenant, plan, módulos
            → /control/provisioning → alta instancia
            → POST companies/simulation → SimulationRunOrchestrator
            → platform:simulate-client → sync + publish fixtures
            → /control/simulations/{run}/report → reporte
```

**Fuente:** `routes/control.php`, `app/Control/Application/Services/SimulationRunOrchestrator.php`.

### 1.4 Diagrama gráfico

[INSERTAR_BPMN]

**Artefactos BPMN:** No se encontró archivo `.bpmn` en el repositorio. Flujos reconstruidos desde markdown y código (`docs/matriz_generada/flujo_bpmn.csv` — 31 relaciones).

[INSERTAR_DIAGRAMA_ARQUITECTURA]

**Referencias diagramas existentes:**
- `docs/architecture/er_diagram.md`
- `docs/architecture/middleware_database_architecture.md`

---

## 2. Arquitectura de Software

### 2.1 Visión general

| Aspecto | Descripción | Evidencia | Estado |
|---------|-------------|-----------|--------|
| Estilo | Monolito modular DDD + EDA | `app/` bounded contexts | **VALIDADO** (Fuente: `app/`) |
| Backend | Laravel 11, PHP 8.2 | `composer.json` | **VALIDADO** (Fuente: `composer.json`) |
| Frontend | Vue 3 + Inertia.js + Vite + Tailwind | `package.json`, `resources/js/` | **VALIDADO** (Fuente: `package.json`, `resources/js/`) |
| Persistencia | SQL relacional (31 migraciones) | `database/migrations/` | **VALIDADO** (Fuente: `database/migrations/`) |
| APIs | REST `/api/middleware`, `/api/dashboard`, `/api/integrations`, `/api/v1/*` | Service providers, rutas | **VALIDADO** (Fuente: `routes/api.php`) |
| Auth | Sanctum tokens + sesión web + RBAC abilities | `config/platform_roles.php` | **VALIDADO** (Fuente: `composer.json`, código auth) |
| Observabilidad | Prometheus `/metrics`, trace_logs, correlation ID | `app/Observability/` | **VALIDADO** (Fuente: `app/Observability/`) |
| Despliegue | Instancia por cliente (ADR-001) | `config/platform.php` | **VALIDADO** (Fuente: `config/platform.php`) |

### 2.2 Bounded contexts

| Contexto | Tipo DDD | Responsabilidad | Estado |
|----------|----------|-----------------|--------|
| Middleware | Supporting / Infrastructure | Event bus, cola, DLQ, topología | **VALIDADO** |
| Dashboard | Supporting / Observability | Read models, feed, KPIs, nodos | **VALIDADO** |
| Control | Application / SaaS | Gestión tenants, simulación, incidentes | **VALIDADO** |
| Integration | Supporting | Webhooks, canales, adaptadores | **VALIDADO** |
| Shared/Platform | Supporting | Identidad instancia, tenant context | **VALIDADO** |
| Shared/Identity | Supporting | Auth, policies, Sanctum | **VALIDADO** |
| Observability | Supporting | Métricas, SLI, tracing ligero | **VALIDADO** |
| Monitoring | Supporting | Alertas, canary | **VALIDADO** |

### 2.3 Capas internas (por contexto)

```
Interfaces/     → Controllers, Routes, Providers
Application/    → UseCases, Services, DTOs
Domain/         → Entities, ValueObjects, Repository interfaces
Infrastructure/ → Eloquent, EventBus adapters, Jobs
```

### 2.4 Base de datos — tablas principales

| Grupo | Tablas (evidencia migraciones) | Estado |
|-------|-------------------------------|--------|
| Mensajería | `event_store`, `event_logs`, `message_queue`, `dead_letter_queue`, `bus_queue_entries` | **VALIDADO** |
| Observabilidad | `event_feed_entries`, `event_feed_projections`, `observability_metrics`, `trace_logs` | **VALIDADO** |
| Configuración | `tenants`, `channels`, `integrations`, `registered_modules` | **VALIDADO** |
| Operación | `simulation_runs`, `client_incident_reports`, `users`, `personal_access_tokens` | **VALIDADO** |

**Fuente:** `docs/architecture/middleware_database_dictionary.md`, `database/migrations/`. **VALIDADO**

### 2.5 ADRs arquitectónicos (9)

| ADR | Decisión | Estado | Validación Auditoría |
|-----|----------|--------|----------------------|
| ADR-001 | Instancia por cliente | Aceptado | **VALIDADO** |
| ADR-002 | Defer OAuth2/IdP enterprise | Propuesto | **VALIDADO** |
| ADR-003 | Defer SSO/LDAP | Propuesto | **VALIDADO** |
| ADR-004 | Activación tenant_id | Aceptado | **VALIDADO** |
| ADR-005 | No particionar event_store aún | Propuesto | **VALIDADO** |
| ADR-006 | Defer sagas | Propuesto | **VALIDADO** |
| ADR-007 | Orquestación mínima in-process | Propuesto | **VALIDADO** |
| ADR-008 | Logs stdout JSON + sidecar | Aceptado | **VALIDADO** |
| ADR-009 | Tracing incremental, defer OTel completo | Aceptado | **VALIDADO** |

**Fuente:** `docs/production/ADR_*.md`. **VALIDADO**

### 2.6 Diagrama de arquitectura gráfico

[INSERTAR_DIAGRAMA_ARQUITECTURA]

---

## 3. Capturas de Pantalla de Módulos Clave

Interfaces implementadas (14 páginas Vue). Capturas a adjuntar:

| # | Módulo | Ruta | Marcador |
|---|--------|------|----------|
| 1 | Login | `/login` | [INSERTAR_CAPTURA_LOGIN] |
| 2 | Dashboard observabilidad | `/dashboard` | [INSERTAR_CAPTURA_DASHBOARD] |
| 3 | Middleware consola | `/middleware` | [INSERTAR_CAPTURA_MIDDLEWARE] |
| 4 | Control overview | `/control/overview` | [INSERTAR_CAPTURA_CONTROL_OVERVIEW] |
| 5 | Empresas | `/control/companies` | [INSERTAR_CAPTURA_EMPRESAS] |
| 6 | Detalle empresa | `/control/companies/{tenant}` | [INSERTAR_CAPTURA_EMPRESA_DETALLE] |
| 7 | Config módulos | `/control/companies/{tenant}/modules` | [INSERTAR_CAPTURA_MODULOS] |
| 8 | Simulación reporte | `/control/simulations/{run}/report` | [INSERTAR_CAPTURA_SIMULACION] |
| 9 | Provisioning | `/control/provisioning` | [INSERTAR_CAPTURA_PROVISIONING] |
| 10 | Incidentes | `/control/incidents` | [INSERTAR_CAPTURA_INCIDENTES] |

**Mockups de referencia (no sustituyen capturas reales):**
- `docs/Mokcups_v2.0/*.html` (6 archivos HTML)
- `docs/Mokcups_v1.0/Dashboard_General.html`, `Control_Middleware.html`
- `docs/DC_Mockups_obsoletos(NOusar)/` — **obsoletos, no usar para registro**

---

## 4. Resumen de Pruebas Realizadas (QA)

### 4.1 Estructura de pruebas

| Suite | Directorio | Archivos PHP | Evidencia | Estado |
|-------|------------|--------------|-----------|--------|
| Unit | `tests/Unit` | ~30 | `phpunit.xml` | **VALIDADO** |
| Integration | `tests/Integration` | ~14 | `phpunit.xml` | **VALIDADO** |
| Feature | `tests/Feature` | ~33 | `phpunit.xml` | **VALIDADO** |
| E2E | `tests/E2E` | 2 | `phpunit.xml` | **VALIDADO** |
| UI E2E | `tests/e2e-ui/` | Playwright | `package.json` test:ui | **VALIDADO** |

**Total archivos PHP en tests:** 81.

### 4.2 Pruebas representativas por área

| Área | Archivo test | Qué valida | Estado |
|------|--------------|------------|--------|
| Middleware pipeline | `tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php` | Publish → sync → topología → cola | **VALIDADO** |
| API middleware | `tests/Feature/Middleware/MiddlewareControlApiTest.php` | Endpoints control bus | **VALIDADO** |
| Dashboard | `tests/Feature/Dashboard/DashboardEndpointsTest.php` | APIs dashboard | **VALIDADO** |
| Autenticación | `tests/Feature/Security/PlatformApiAuthenticationTest.php` | Sanctum y abilities | **VALIDADO** |
| RBAC | `tests/Feature/Identity/RoleBasedAuthorizationTest.php` | Roles y permisos | **VALIDADO** |
| Integraciones | `tests/Feature/Integration/WebhookIngressTest.php` | Ingress webhooks | **VALIDADO** |
| OpenAPI | `tests/Feature/Api/OpenApiContractTest.php` | Contrato API | **VALIDADO** |
| E2E simulación | `tests/E2E/Middleware/ClientProductionLikeSimulationTest.php` | Simulación producción-like | **VALIDADO** |
| Catálogo CI | `tests/Unit/Platform/ValidatePlatformCatalogTest.php` | Validación eventbus vs modules_config | **VALIDADO** |
| Tenant instancia | `tests/Integration/Platform/InstanceTenantSeedingIntegrationTest.php` | ADR-001 seeding | **VALIDADO** |

### 4.3 Pipeline de calidad automatizado

| Paso | Comando | Fuente | Estado |
|------|---------|--------|--------|
| Lint | `pint --test` | `composer.json` script `lint` | **VALIDADO** |
| Análisis estático | `phpstan analyse` | script `analyse` | **VALIDADO** |
| Validación config | `validate-config` | JSON + `platform:validate-catalog` | **VALIDADO** |
| OpenAPI lint | `scripts/ci/lint-openapi.sh` | script `validate-openapi` | **VALIDADO** |
| Tests | `phpunit` | script `test` | **VALIDADO** |
| Stats check | `sync_test_stats.php --check` | script `test:stats:check` | **VALIDADO** |
| Carga k6 | `scripts/ci/run-k6-load-test.sh` | script `quality:load` | **VALIDADO** |

### 4.4 Resultados documentados

| Documento | Resultado reportado | Fuente | Estado |
|-----------|---------------------|--------|--------|
| Reporte_Implementacion.md | Planes producción marcados Completado (2026-05-21) | `docs/production/` | **VALIDADO** |
| Release_decision_QA.md | GO con riesgos (instance-per-client); NO-GO multi-tenant runtime | `docs/personal_notes/` | **VALIDADO** |
| Reporte implementación tenants | 90 tests passed, 325 assertions (snapshot Plan_Tenants) | `docs/production/Reporte_Implementacion.md` | **VALIDADO** |

[INSERTAR_RESULTADOS_QA]

**Estado ejecución tests al generar documento:** **PENDIENTE DE VALIDACIÓN** — no se ejecutó suite en esta sesión de generación documental.

### 4.5 Matrices y catálogos de prueba

- `docs/testing/matrix_validacion_middleware.md`
- `docs/testing/priority_tests_matrix.md`
- `docs/testing/e2e_simulacion_cliente.md`
- `docs/testing/feature_api_middleware_control.md`
- Catálogos autogenerados: `docs/testing/*_catalogo_autogenerado.md`

---

## 5. Documentación Complementaria

### 5.1 Documentos técnicos incluidos en el repositorio

| Categoría | Cantidad | Ubicación | Estado |
|-----------|----------|-----------|--------|
| ADRs | 9 | `docs/production/ADR_*.md` | **VALIDADO** |
| Planes producción | 16 | `docs/production/Plan_*.md` | **VALIDADO** |
| Runbooks | 8 | `docs/production/Runbook_*.md`, `docs/monitoring/` | **VALIDADO** |
| Arquitectura | 4 | `docs/architecture/` | **VALIDADO** |
| Planes módulo v0.1 | 3 | `docs/Plan_Desarrollo_Modulos_v0.1/` | **VALIDADO** |
| Testing | 13+ | `docs/testing/` | **VALIDADO** |
| API | OpenAPI, Postman, breaking policy | `docs/api/` | **VALIDADO** |
| Matriz trazabilidad | 12 CSV + reporte | `docs/matriz_generada/` | **VALIDADO** |

### 5.2 Manuales

| Manual | Estado | Validación |
|--------|--------|------------|
| Manual instalación README | [PENDIENTE_VALIDACIÓN] — `README.md` tiene 1 línea | **PENDIENTE DE VALIDACIÓN** |
| Runbook simulación cliente | Completo — `docs/production/Runbook_Simulacion_Cliente.md` | **VALIDADO** |
| Runbook onboarding | Completo — `docs/production/Runbook_Onboarding_Cliente.md` | **VALIDADO** |
| Guía despliegue instancia | Completo — `docs/production/Guia_Despliegue_Instancia_Cliente.md` | **VALIDADO** |
| Manual usuario final | [NO_EVIDENCIADO] | **PARCIALMENTE VALIDADO** |

### 5.3 Historial de versiones

| Versión | Fecha | Evidencia | Estado |
|---------|-------|-----------|--------|
| beta | 2026-05-27 | Commit `b175b8d` | **VALIDADO** (Fuente: git log) |
| v1.7 | 2026-06-06 | Commit `6500034` | **VALIDADO** (Fuente: git log) |
| Fase 6 | 2026-06-01 | Commit `35cd726` | **VALIDADO** (Fuente: git log) |
| post-refactor | 2026-06-13–18 | Commits recientes | **VALIDADO** (Fuente: git log) |

**Tags Git:** ninguno. **CHANGELOG:** `docs/api/CHANGELOG.md` **PENDIENTE DE VALIDACIÓN** contenido.

### 5.4 Observabilidad externa (configs referencia)

| Artefacto | Ruta | Estado |
|-----------|------|--------|
| Dashboard Grafana middleware | `docs/observability/grafana/middleware_dashboard.json` | **VALIDADO** |
| Dashboard Grafana SLO | `docs/observability/grafana/slo_dashboard.json` | **VALIDADO** |
| Prometheus config | `docs/monitoring/prometheus/prometheus.yml` | **VALIDADO** |
| Alert rules | `docs/monitoring/prometheus/alert_rules.yml` | **VALIDADO** |

---

## 6. Observaciones Finales

Los anexos técnicos presentados respaldan la originalidad y funcionalidad del software **Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)**, plataforma de integración por eventos con observabilidad operativa y portal SaaS de gestión de instancias.

**Pendientes para expediente completo:**

1. [INSERTAR_DIAGRAMA_ARQUITECTURA] — exportar desde `docs/architecture/`
2. [INSERTAR_BPMN] — elaborar diagrama formal o recuperar CSV referenciado
3. [INSERTAR_CAPTURA_*] — 10 capturas de pantalla
4. [INSERTAR_RESULTADOS_QA] — ejecutar `composer ci` y adjuntar reporte
5. [COMPLETAR_MANUALMENTE] — datos legales y firmas en documentos 3, 4, 5

**Trazabilidad:** `docs/matriz_generada/` (330 registros), `docs/Patente/Patente_Resumen_Evidencias.md`.

---

*Documento generado automáticamente. Revisar y validar antes de presentación ante INDECOPI.*
