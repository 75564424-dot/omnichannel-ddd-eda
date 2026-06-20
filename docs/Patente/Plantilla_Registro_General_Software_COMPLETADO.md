# PLANTILLA BASE — VERSIÓN COMPLETADA

> **Fuente de generación:** análisis del repositorio `omnichannel-ddd-eda` (2026-06-10).  
> **Plantilla base:** `Plantilla_Registro_General_Software.docx.md` (intacta).

Esta plantilla ha sido completada con datos reales del proyecto **Plataforma Event Bus Core**.

---

# ANEXO 1: EJEMPLAR DEL SOFTWARE

**Título de la obra:** Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)

**Tipo de software:** Plataforma web — middleware de integración por eventos + dashboard de observabilidad + portal SaaS administración

**Lenguaje(s) de programación:** PHP 8.2, JavaScript, Vue 3, SQL, JSON, YAML

**Entorno / Framework:** Laravel 11, Inertia.js, Vite 5, Tailwind CSS, Laravel Sanctum

**Autor(es):** Brayan Estif Guillen Sanabria; Guillen-Sanabria (ver Documento 4 — [PENDIENTE_VALIDACION si misma persona])

**Empresa o Titular:** [COMPLETAR_MANUALMENTE]

---

## Muestra representativa del código fuente (10–15 páginas)

A continuación se indican las secciones principales del programa que deben incluirse en el anexo impreso. **No se reproduce código completo aquí** — generar PDF al momento del trámite.

| Página sugerida | Archivo | Sección representativa | Estado |
|-----------------|---------|------------------------|--------|
| 1 | `composer.json` | name, description, require | [FRAGMENTO_CODIGO_PENDIENTE] |
| 2 | `app/Middleware/Application/Services/EventPublisherService.php` | publicación eventos | [FRAGMENTO_CODIGO_PENDIENTE] |
| 3 | `app/Shared/Api/Routes/MiddlewareApiRoutes.php` | rutas API middleware | [FRAGMENTO_CODIGO_PENDIENTE] |
| 4 | `app/Dashboard/Listeners/UniversalDashboardFeedListener.php` | observación wildcard | [FRAGMENTO_CODIGO_PENDIENTE] |
| 5 | `app/Control/Application/Services/TenantAdminService.php` | gestión tenants | [FRAGMENTO_CODIGO_PENDIENTE] |
| 6 | `app/Shared/Platform/DatabaseInstanceTenantContext.php` | contexto instancia ADR-001 | [FRAGMENTO_CODIGO_PENDIENTE] |
| 7 | `config/eventbus.php` | suscripciones y driver | [FRAGMENTO_CODIGO_PENDIENTE] |
| 8 | `config/platform.php` | identidad instancia | [FRAGMENTO_CODIGO_PENDIENTE] |
| 9 | `routes/control.php` | portal control plane | [FRAGMENTO_CODIGO_PENDIENTE] |
| 10 | `app/Console/Commands/SimulateClientCommand.php` | simulación E2E | [FRAGMENTO_CODIGO_PENDIENTE] |
| 11 | `resources/js/Pages/Dashboard/Index.vue` | UI dashboard | [FRAGMENTO_CODIGO_PENDIENTE] |
| 12 | `database/migrations/2026_05_21_100001_create_middleware_event_messaging_schema.php` | esquema mensajería | [FRAGMENTO_CODIGO_PENDIENTE] |
| 13 | `tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php` | prueba integrada | [FRAGMENTO_CODIGO_PENDIENTE] |
| 14 | `app/Integration/Application/UseCases/ReceiveWebhookUseCase.php` | webhook ingress | [FRAGMENTO_CODIGO_PENDIENTE] |
| 15 | `app/Providers/EventBusIntegrationServiceProvider.php` | fusión packs eventbus | [FRAGMENTO_CODIGO_PENDIENTE] |

[CAPTURA_PENDIENTE] — Incluir captura de pantalla del IDE mostrando estructura `app/` como contexto visual.

---

# ANEXO 2: DESCRIPCIÓN TÉCNICA O RESUMEN FUNCIONAL

**Título del Software:** Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)

**Versión:** v1.7-beta (Git: commits `6500034` v1.7 + `b175b8d` beta)

**Autores:** Brayan Estif Guillen Sanabria; Guillen-Sanabria — `git shortlog -sn --all`

**Año de creación:** 2026 (primer commit 2026-05-27)

**Lenguajes / Tecnologías empleadas:** PHP 8.2, Laravel 11, Vue 3, Inertia.js, Vite, Tailwind, SQL, Sanctum, Pest/PHPUnit, Playwright, Prometheus

**Tipo de obra:** Plataforma web (software)

---

## 1. Descripción general

La **Plataforma Event Bus Core** es una aplicación web diseñada como **servicio de integración por eventos (EDA)** con **observabilidad operativa** y **portal de administración SaaS**. Permite:

- Publicar y rastrear eventos entre sistemas externos (productores/consumidores).
- Monitorear cola, métricas, topología y dead letters del bus.
- Visualizar feed de eventos, KPIs configurables y estado de nodos.
- Gestionar empresas/instancias, planes comerciales, módulos y operadores.
- Simular clientes para pruebas tipo producción.
- Integrar canales externos vía webhooks y adaptadores.

El núcleo **no contiene reglas de negocio vertical** (retail, OMS); es agnóstico y configurable por JSON y paquetes de integración.

**Fuente:** `composer.json`, `docs/Plan_Desarrollo_Modulos_v0.1/README.md`.

---

## 2. Estructura y funcionamiento

| Capa | Tecnología | Responsabilidad |
|------|------------|-----------------|
| Presentación | Vue 3 + Inertia.js | `/dashboard`, `/middleware`, `/control/*` |
| API REST | Laravel controllers + use cases | `/api/middleware`, `/api/dashboard`, `/api/integrations` |
| Aplicación | Services, UseCases por bounded context | Lógica orquestación sin dominio vertical |
| Dominio | Entities, ValueObjects | Contratos middleware, dashboard |
| Infraestructura | Eloquent, EventBus adapters, Jobs | Persistencia, despacho eventos |
| Datos | SQLite/MySQL | 31 migraciones, ~40 tablas |

**Interacción principal:**

1. Integrador publica evento → Middleware valida y registra → Bus despacha listeners.
2. Dashboard observa wildcard → proyecta feed y métricas.
3. Admin SaaS gestiona tenants → provisioning → simulación.

---

## 3. Características técnicas

- Autenticación Sanctum (API tokens) + sesión web con RBAC por abilities.
- Rate limiting y audit middleware en rutas sensibles.
- Validación JSON Schema en publicación (`opis/json-schema`).
- Catálogo declarativo dual: `eventbus.php` + `modules_config.json` con validación CI.
- Correlation ID y trace logs para observabilidad.
- Endpoint Prometheus `/metrics`.
- API versionada `/api/v1/` paralela a legacy.
- OpenAPI spec en `docs/api/openapi.yaml`.
- Retención automatizada y purga programada.
- Instancia por cliente (ADR-001) con `tenant_id` en repositorios.

---

## 4. Originalidad

El software implementa una **plataforma genérica de bus de eventos** combinada con **dashboard de observabilidad configurable** y **control plane SaaS multi-instancia**, sin acoplar pantallas a módulos de negocio específicos. Las KPIs y topología se definen por JSON (`dashboard_config.json`, `modules_config.json`) en lugar de código hardcodeado por vertical.

La arquitectura DDD con bounded contexts separados (Middleware, Dashboard, Control, Integration) y el modelo de **instancia por cliente** con simulación automatizada constituyen decisiones de diseño documentadas en 9 ADRs.

**Fuente:** `docs/Plan_Desarrollo_Modulos_v0.1/`, `docs/production/ADR_*.md`, `docs/matriz_generada/pmv.csv`.

---

## 5. Entorno de ejecución

| Requisito | Valor |
|-----------|-------|
| SO compatible | Windows 10+, Linux (scripts .sh y .ps1) |
| PHP | ^8.2 |
| Composer | 2.x [PENDIENTE_VALIDACION] |
| Node.js + npm | Para build Vite [PENDIENTE_VALIDACION versión] |
| Base de datos | SQLite (dev) / MySQL (prod documentado) |
| Framework | Laravel 11 |
| Servidor web | PHP built-in / Nginx-Apache [PENDIENTE_VALIDACION prod] |
| Navegador | Moderno con soporte ES modules |

---

## 6. Archivos adjuntos (opcional)

| Anexo visual | Estado |
|--------------|--------|
| [INSERTAR_DIAGRAMA_ARQUITECTURA] | `docs/architecture/er_diagram.md` |
| [INSERTAR_BPMN] | Reconstruido en `docs/matriz_generada/flujo_bpmn.csv` |
| [INSERTAR_CAPTURA_DASHBOARD] | `/dashboard` |
| [INSERTAR_CAPTURA_MIDDLEWARE] | `/middleware` |
| [INSERTAR_CAPTURA_CONTROL] | `/control/overview` |
| [INSERTAR_RESULTADOS_QA] | Ejecutar `composer ci` |

---

*Documento generado automáticamente. Complementa el paquete INDECOPI en `docs/Patente/`.*
