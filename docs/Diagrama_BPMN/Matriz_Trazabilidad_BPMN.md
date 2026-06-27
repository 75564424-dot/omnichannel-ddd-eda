# Matriz de Trazabilidad BPMN

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Propósito:** Relacionar cada proceso BPMN con casos de uso, dominios DDD, servicios, eventos, middleware, APIs y documentación fuente.

---

## Leyenda

| Columna | Descripción |
|---------|-------------|
| Proceso | ID y nombre del proceso BPMN |
| Caso de uso | Caso de uso o escenario operativo documentado |
| Dominio DDD | Bounded context o dominio de referencia |
| Servicio | Clase/servicio de aplicación |
| Microservicio/Instancia | Despliegue (CP o silo cliente) |
| Evento | Eventos generados o consumidos |
| Middleware | Capacidad C1–C5 afectada |
| API | Endpoints o comandos CLI |
| Documentación | Fuentes primarias |
| Matrices | Matrices de evaluación relacionadas |

---

## Matriz principal

| Proceso | Caso de uso | Dominio DDD | Servicio | Instancia | Evento | Middleware | API / CLI | Documentación | Matrices |
|---------|-------------|-------------|----------|-----------|--------|------------|-----------|---------------|----------|
| **PROC-001** Publicación eventos | Publicar evento al bus | Middleware (soporte) | `EventPublisherService` | Silo cliente | `Platform.*`, tipos declarativos | C1, C2, C5 | `POST /api/middleware/events/publish` | `Plan_Modulo_Control_Middleware.md` §6; `procesos.csv` | 02_Middleware C05–C08 |
| **PROC-002** Sync catálogo | Sincronizar registry declarativo | Middleware | `SyncConfiguredModulesToRegistryUseCase` | Silo | — | C4 | `POST /api/middleware/registry/sync-config` | `Plan_de_implementacion.md` §B.2 | 02_Middleware C05 |
| **PROC-003** Consulta bus | Consultar cola/topología/DLQ | Middleware | `BusMetricsController`, `TopologyController` | Silo | — | C3 | `GET /api/middleware/*` | `MiddlewareApiRoutes.php` | 02_Middleware C06–C08 |
| **PROC-004** Dashboard | Observar feed y KPIs | Dashboard (soporte) | `UniversalDashboardFeedListener`, `GetDynamicMetricSeriesUseCase` | Silo | Consume todos (wildcard) | C5 | `GET /api/dashboard/*`, SSE `/stream` | `Plan_Modulo_Dashboard_General.md` §6 | 04_Observabilidad C13–C15 |
| **PROC-005** Auth web | Login operador | Shared/Http | `LoginController` | CP + Silo | — | — | `GET/POST /login` | `config/platform_auth.php`; ADR-002 | 05_Seguridad C11 |
| **PROC-006** Auth API | Autenticar integrador M2M | Shared/Identity | `AuthenticatePlatformApi`, `EnforcePlatformAbility` | Silo | — | — | Bearer Sanctum | `Flujo_M2M_Integradores.md` | 05_Seguridad C11–C12 |
| **PROC-007** Gestión empresas | CRUD tenant y planes | Control | `TenantAdminService` | CP | — | — | `/control/companies/*` | `routes/control.php` | 06_Operacion C17 |
| **PROC-008** Provisioning | Alta nueva instancia | Control | `ProvisioningController` | CP → Silo | Tenant lifecycle | — | `POST /control/provisioning` | `Runbook_Onboarding_Cliente.md`; ADR-001 | 06_Operacion C17–C18 |
| **PROC-009** Simulación E2E | Rehearsal cliente | Console/Quality | `SimulateClientCommand` | Silo | Fixtures `sample_events.json` | C1–C5 | `platform:simulate-client` | `Runbook_Simulacion_Cliente.md` | 08_Calidad C24–C26 |
| **PROC-010** Onboarding tenant | Asegurar fila tenant | Control/Console | `EnsureInstanceTenantCommand` | Silo | — | — | `platform:ensure-instance-tenant` | ADR-004 | 06_Operacion C17 |
| **PROC-011** Webhooks | Ingress canal externo | Integration | `ReceiveWebhookUseCase` | Silo | Transformados a envelope | C1 | `POST webhook ingress` | `Plan_Integraciones.md` §5 | 03_Integracion C09–C10 |
| **PROC-012** Gestión integraciones | CRUD canales | Integration | Controllers Integration | Silo | — | — | `/api/integrations/*` | `Plan_Integraciones.md` | 03_Integracion C09 |
| **PROC-013** Monitoreo | Evaluar alertas | Monitoring/Observability | `EvaluateMonitoringAlertsCommand`, `PrometheusMetricsExporter` | CP + Silo | — | — | `platform:monitoring-evaluate`; `GET /metrics` | `Plan_Monitoreo.md`; `routes/console.php` | 04_Observabilidad C14–C15 |
| **PROC-014** Retención | Purgar datos antiguos | Console | `PurgePlatformRetentionCommand` | CP + Silo | — | — | `platform:purge-retention` | `Plan_Resiliencia.md` | 06_Operacion C19 |
| **PROC-015** Incidentes | Reportar/gestionar soporte | Control | `ClientIncidentReportService` | Silo + CP | — | — | `/support/reports` | `client_incident_reports` migration | 06_Operacion C20 |
| **PROC-016** Validación catálogo | CI alineación config | Quality/Console | `ValidatePlatformCatalogCommand` | Silo | — | C4 | `platform:validate-catalog` | `Plan_de_implementacion.md` B.3 | 08_Calidad C24–C26 |
| **PROC-017** Middleware 5 etapas | Pipeline retail documental | Dominios externos (ref) | Conectores doc | Externo | `Inventario.Events`, `Pedido.Events`… | C1–C5 (doc) | — | `Flujo_Middleware.md` §3 | 02_Middleware; 03_Integracion |
| **PROC-018** Multi-tenant Fase 3 | Aislamiento lógico | Arquitectura | — | — | — | — | — | ADR-001 §Fase 3 | 06_Operacion C17 |
| **PROC-019** Portal cliente | Acceso instancia web | Control/Http | `EnsureInstancePortalAccess` | Silo | — | — | `instance.portal` middleware | `routes/web.php` | 06_Operacion C17 |
| **PROC-020** Simulación CP | Orquestar sim desde CP | Control | `SimulationRunOrchestrator` | CP | — | — | `/control/simulations` | `simulation_runs` migration | 08_Calidad |
| **PROC-030** Deploy VM | Despliegue producción | Infraestructura | — | VM cliente | — | — | Runbook manual | `Runbook_Deploy_VM.md` | 06_Operacion C20 |
| **PROC-031** Backup | Respaldo BD | Infraestructura | — | VM/BD | — | — | Scripts backup | `Runbook_Backup_Restore.md` | 06_Operacion C19 |
| **PROC-032** DR Drill | Recuperación desastre | Infraestructura | — | VM/BD | — | — | Runbook DR | `Runbook_DR_Drill.md` | 06_Operacion C19 |
| **PROC-033** Evaluación | Aceptación middleware | Gobernanza | — | — | — | — | Matrices CSV | `Middleware_Acceptance_Evaluation_Framework.md` | 01–14 evaluación |
| **PROC-034** Espejo CP→Silo | Sincronizar catálogo | Control/Shared | `LocalFleetTenantMirror` | CP → Silo | — | C4 | Interno (mirror) | `Certificacion_Flujo_Operativo_Oficial.md` | 06_Operacion C17 |

---

## Trazabilidad por dominio DDD

| Dominio DDD | Procesos | Agregados / Entidades (doc) | Estado en código |
|-------------|----------|------------------------------|------------------|
| Middleware | PROC-001, 002, 003, 017 | QueueEntry, RegisteredModule, DeadLetter | Implementado |
| Dashboard | PROC-004 | EventFeedEntry, MetricSnapshot | Implementado |
| Control | PROC-007, 008, 015, 020, 034 | Tenant, Company, SimulationRun | Implementado |
| Integration | PROC-011, 012 | Channel, Integration, WebhookRequest | Implementado parcial |
| Observability | PROC-013 | ObservabilityMetric, TraceLog | Implementado |
| Inventario (ref) | PROC-017 | Producto, Stock | Documental |
| Pedidos (ref) | PROC-017 | Pedido, LíneaPedido | Documental |
| Clientes (ref) | PROC-017 | Cliente | Documental |

---

## Trazabilidad eventos de dominio (documentales)

| Evento | Productor (doc) | Consumidor (doc) | Proceso |
|--------|-----------------|------------------|---------|
| `ProductoRegistrado` | Catálogo | Inventario | PROC-017 |
| `StockActualizado` | Inventario | Dashboard retail | PROC-017 |
| `PedidoCreado` | OMS | Logística | PROC-017 |
| `PedidoConfirmado` | Pedidos | Inventario | PROC-017 |
| `Platform.Smoke.Test` | Onboarding | Dashboard | PROC-008, 009 |
| Tipos declarativos `modules_config` | Productores config | Suscriptores config | PROC-001, 002 |

---

## Trazabilidad requisitos (REQ → Proceso)

| Requisito | Proceso(s) | Estado |
|-----------|------------|--------|
| REQ-C1 | PROC-001, 011 | Implementado |
| REQ-C2 | PROC-001, 003 | Implementado |
| REQ-C3 | PROC-003 | Implementado |
| REQ-C4 | PROC-002, 016 | Implementado parcial |
| REQ-C5 | PROC-004 | Implementado |
| REQ-O1–O5 | PROC-004 | Implementado (O5 SSE validado en certificación) |
| REQ-RST-01–04 | Todos core | Implementado |
| REQ-ADR001 | PROC-008, 010, 018, 019 | Implementado (018 diferido) |
| REQ-DYN-01 | — | **Sin proceso — brecha** |
| REQ-FLOW-01 | PROC-017 | Documentado parcial |
| REQ-VAL-01 | PROC-016 | Implementado |
| REQ-SIM-01 | PROC-009, 020 | Implementado |
| REQ-CP-01 | PROC-007 | Implementado |

---

## Trazabilidad casos de uso Dashboard

| Caso de uso | Proceso | Servicio |
|-------------|---------|----------|
| GetRecentEventFeedUseCase | PROC-004 | `GetRecentEventFeedUseCase` |
| GetGlobalMetricsUseCase | PROC-004 | `GetGlobalMetricsUseCase` |
| GetDynamicMetricSeriesUseCase | PROC-004 | `GetDynamicMetricSeriesUseCase` |
| GetMiddlewareBusMetricsUseCase | PROC-004 | `GetMiddlewareBusMetricsUseCase` |
| StreamLiveEventsUseCase | PROC-004 | `StreamLiveEventsUseCase` |

Fuente: `Plan_Modulo_Dashboard_General.md` §4.1

---

## Trazabilidad evaluación (criterios → procesos)

| Criterio | Dominio | Procesos de evidencia |
|----------|---------|----------------------|
| C01–C04, C27 | Arquitectura | PROC-017, 018, todos (desacoplamiento) |
| C05–C08, C28 | Middleware | PROC-001, 002, 003, 017 |
| C09–C10 | Integración | PROC-011, 012, 017 |
| C11–C12, C16 | Seguridad | PROC-005, 006 |
| C13–C15 | Observabilidad | PROC-004, 013 |
| C17–C20 | Operación | PROC-007–010, 030–032, 034 |
| C21–C23 | IA | PROC-033 |
| C24–C26 | Calidad | PROC-009, 016, 033 |

Fuente: `evaluation/Middleware_Acceptance_Evaluation_Framework.md`

---

## Trazabilidad ADR → Proceso

| ADR | Decisión | Proceso |
|-----|----------|---------|
| ADR-001 | Instancia por cliente | PROC-008, 010, 018, 019 |
| ADR-002 | OAuth2 diferido | PROC-005 (gateway ACT-029) |
| ADR-003 | SSO/LDAP diferido | PROC-005, 007 |
| ADR-004 | tenant_id vía seeder | PROC-010 |
| ADR-005 | Particionamiento event_store | PROC-014 (retención) |
| ADR-006 | Sagas diferidas | Sin proceso |
| ADR-007 | Workflow in-process | PROC-001 (parcial) |
| ADR-008 | Logs stdout JSON | PROC-013 |
| ADR-009 | Tracing ligero | PROC-004, 013 |
| ADR-010 | Lifecycle tenant 2D | PROC-007, 008 |
| ADR-011 | Friendly routing | PROC-019 |

---

## Cobertura documental

| Área `docs/` | Procesos derivados | Cobertura |
|--------------|-------------------|-----------|
| `Patente/matriz_generada/` | PROC-001–020 | Alta |
| `architecture/` | MP-02, PROC-017 | Alta |
| `evaluation/` | PROC-033 | Alta |
| `production/` | PROC-005–016, 030–032 | Alta |
| `Plan_Desarrollo_Modulos_v0.1/` | PROC-001–004 | Alta |
| `Plan_Desarrollo_Servicio_v0.1/` | PROC-017 | Media (documental) |
| `refactorizacion_Informes/` | PROC-034 | Alta |
| `testing/` | PROC-009, 016 | Alta |
| `Analisis_v0.1/` | PROC-017 (contexto) | Referencia |
| `Analisis_v0.2/` | PROC-033 (IA) | Referencia |
| `monitoring/` | PROC-013 | Alta |
