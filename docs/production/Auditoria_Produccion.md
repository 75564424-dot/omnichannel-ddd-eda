# Auditoría Técnica de Producción — Middleware Omnicanal

**Versión:** 1.0  
**Fecha:** 2026-05-21  
**Alcance:** `platform/event-bus-core` — núcleo middleware + dashboard  
**Tipo:** Auditoría y planificación (sin implementación)

---

## Veredicto ejecutivo

| Dimensión | Estado | Prioridad inmediata |
|-----------|--------|---------------------|
| Núcleo funcional (bus, cola, feed, topología) | **Operativo en laboratorio** | Estabilizar y regularizar |
| Seguridad y acceso | **Crítico — no listo** | Autenticación + rate limiting |
| Cloud / despliegue | **Crítico — no listo** | Docker, CI/CD, `.env.example` |
| Esquema BD vs código | **Desalineado** | Completar capa de aplicación |
| Observabilidad enterprise | **Parcial** | Logs, métricas, trazas |
| Simulación de clientes | **Documentado, no automatizado** | Runbooks + E2E ampliado |

**Modelo de despliegue recomendado:** instancia por cliente (Fase D), no multi-tenant lógico en una sola app.

**Decisión QA previa:** GO con riesgos para core por instancia (`docs/personal_notes/Release_decision_QA.md`).

---

## Mapa de planes

Cada plan sigue la estructura: Estado actual → Objetivo → Problemas → Requerimientos → Propuesta → Roadmap → Prioridad → Riesgo.

| Plan | Área | Prioridad global |
|------|------|------------------|
| [Plan_Seguridad.md](Plan_Seguridad.md) | Hardening, CORS, CSRF, secretos, rate limiting | **Crítico** |
| [Plan_Autenticacion.md](Plan_Autenticacion.md) | JWT, API Keys, OAuth2, sesiones | **Crítico** |
| [Plan_Usuarios.md](Plan_Usuarios.md) | Usuarios, roles, RBAC, operadores | **Crítico** |
| [Plan_Tenants.md](Plan_Tenants.md) | Multi-tenant vs instancia por cliente | **Alto** |
| [Plan_Cloud.md](Plan_Cloud.md) | Docker, K8s, HA, backups, despliegue | **Crítico** |
| [Plan_CI_CD.md](Plan_CI_CD.md) | Pipelines, gates, release | **Crítico** |
| [Plan_Observabilidad.md](Plan_Observabilidad.md) | Métricas, trazas, correlación | **Alto** |
| [Plan_Monitoreo.md](Plan_Monitoreo.md) | Alertas, SLOs, APM | **Alto** |
| [Plan_Logs.md](Plan_Logs.md) | Logging centralizado, audit_logs | **Alto** |
| [Plan_Resiliencia.md](Plan_Resiliencia.md) | Retries, DLQ, idempotencia, fallos | **Alto** |
| [Plan_Middleware.md](Plan_Middleware.md) | Event store, orquestación, versionado | **Alto** |
| [Plan_Integraciones.md](Plan_Integraciones.md) | Channels, providers, adapters, webhooks | **Alto** |
| [Plan_BaseDeDatos.md](Plan_BaseDeDatos.md) | Esquema, migraciones, índices, multi-tenant | **Medio** |
| [Plan_APIs.md](Plan_APIs.md) | Versionado, OpenAPI, contratos | **Medio** |
| [Plan_Calidad.md](Plan_Calidad.md) | Tests, cobertura, performance, stress | **Alto** |
| [Plan_SimulacionClientes.md](Plan_SimulacionClientes.md) | Simulación realista, runbooks, staging | **Alto** |
| [Plan_de_implementacion.md](Plan_de_implementacion.md) | Plan histórico config dinámica (parcialmente obsoleto) | Referencia |

---

## Inventario de lo que EXISTE hoy

### Código y arquitectura

- **Bounded contexts:** Middleware, Dashboard, Shared, Platform/Demo (`app/`)
- **DDD layering:** Interfaces / Application / Domain / Infrastructure
- **APIs:** 11 endpoints middleware + 14 dashboard (sin auth)
- **Event bus:** Laravel Events in-process + wildcard listeners
- **Persistencia activa:** `message_queue`, `dead_letter_queue`, `event_feed_projections`, `observability_metrics`, `registered_modules`, `channel_status_snapshots`
- **Tests:** 86 PHPUnit (317 assertions) — Unit, Integration, Feature, E2E

### Documentación operativa (fuerte)

- Runbooks: `docs/personal_notes/Runbook_cliente_simulado.md`, `Simulacion_escenario_productivo.md`
- QA: `Release_decision_QA.md`, `Estrategia_pruebas_pre_produccion.md`
- Arquitectura BD: `docs/architecture/middleware_database_*`
- Testing: `docs/testing/` + catálogos autogenerados

### Configuración

- `config/eventbus.php`, `config/dashboard.php`, `config/modules.php`
- B.2 sync implementado (`SyncConfiguredModulesToRegistryUseCase`)
- C pack merge implementado (`PackSubscriptionCatalogMerger`)

---

## Vacíos críticos detectados (resumen)

### Seguridad — CRÍTICO

- APIs públicas sin autenticación (`POST /events/publish`, `sync-config`, DLQ resolve)
- SSE `/api/dashboard/stream` abierto
- Sin rate limiting, CORS explícito, ni `.env.example`
- Sin Sanctum/Passport en dependencias

### Cloud — CRÍTICO

- Sin Dockerfile, docker-compose, ni CI en el repo
- Sin health/readiness endpoints configurados
- Sin pipeline de release automatizado

### Arquitectura middleware — ALTO

- Tablas `event_store`, `webhook_*`, `integrations`, `workflows` sin capa de aplicación
- `event_store` no recibe escrituras en publish path
- Cola Laravel (`jobs`) no usada por listeners (sync only)
- Broker externo (Kafka/RabbitMQ) documentado pero no implementado

### Usuarios y acceso — CRÍTICO

- Sin modelo User, sin migración users
- Inertia `auth` siempre vacío
- `audit_logs` en BD sin uso en código

### Calidad — ALTO

- PHPStan/Pint instalados pero sin config ni CI
- Sin OpenAPI/Swagger
- Sin tests de carga, seguridad ni UI E2E en CI

---

## Roadmap global de regularización (sin refactor masivo)

### Fase 1 — Estabilización y gates (4–6 semanas)

1. `.env.example` + documentación de variables
2. CI básico (PHPUnit + lint JSON config)
3. Autenticación API (Sanctum + API keys para integradores)
4. Rate limiting en endpoints de control
5. Health check `/up`
6. Actualizar `Plan_de_implementacion.md` (estado B.2/C)
7. Comando `platform:validate-catalog` (B.3 pendiente)

### Fase 2 — Producción cloud (6–8 semanas)

1. Dockerfile + docker-compose (app + MySQL + Redis)
2. Pipeline CI/CD completo
3. Logging estructurado JSON
4. Wiring `event_store` en publish path
5. Staging automatizado + simulación cliente

### Fase 3 — Enterprise (8–12 semanas)

1. Webhooks ingress/egress
2. Capa integrations/channels en aplicación
3. Observabilidad externa (Prometheus/OTel)
4. RBAC completo
5. Tests de carga y DR

---

## Riesgos si no se ejecuta esta regularización

| Riesgo | Impacto |
|--------|---------|
| Exposición de APIs de control en internet | Compromiso total del bus, inyección de eventos falsos |
| Despliegue manual sin CI | Regresiones en producción no detectadas |
| Esquema BD sin código | Expectativas de producto incumplidas, deuda confusa |
| Sin simulación staging | Incidentes en primer cliente real |
| Sin observabilidad | MTTR alto, imposible diagnosticar flujos distribuidos |

---

## Referencias cruzadas

- Plan histórico config: [Plan_de_implementacion.md](Plan_de_implementacion.md)
- QA release: `docs/personal_notes/Release_decision_QA.md`
- Arquitectura BD: `docs/architecture/middleware_database_architecture.md`
- Fase D instancia/cliente: `docs/personal_notes/Fase_D_arquitectura_cliente.md`

---

*Documento índice. Los planes individuales contienen el detalle técnico por área.*
