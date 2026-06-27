# Auditoría integral de testing — docs vs código (2026-06-24)

**Versión:** v1.9 | **Fecha:** 2026-06-24  
**Baseline histórico:** documentación auto-generada **2026-05-22** (~160 métodos)  
**Estado actual:** **364 tests PHPUnit** / **363 métodos** en matriz maestra

---

## 1. Objetivo

Comparar la documentación de testing vigente hasta **2026-05-22** con el código y la suite actual tras la evolución de módulos Control Plane, Dashboard, Platform/Fleet, Observability, Identity y API v1.

## 2. Resumen ejecutivo

| Dimensión | 2026-05-22 | 2026-06-24 | Δ |
|-----------|------------|------------|---|
| Métodos documentados | ~160 | 363 | **+203 (+127%)** |
| PHPUnit ejecutados | — | 364 | +1 data provider / test nuevo |
| Clases Test.php | ~90 | 156 | +66 |
| Documentos estratégicos modulares | 6 | **18** | +12 |
| Fallos activos | No documentados | **0** | TC-0070, TC-0161 corregidos |
| Procesos BPMN con tests | ~12 | **22** | Ver Matriz Cobertura |

## 3. Documentación obsoleta vs vigente

### Retirada (2026-05-03 — ver [Funcionalidades_Obsoletas.md](./Funcionalidades_Obsoletas.md))

| Documento | Sustituto |
|-----------|-----------|
| `catalog_unit.md` | `unit_catalogo_autogenerado.md` |
| `catalog_integration.md` | `integration_catalogo_autogenerado.md` |
| `catalog_feature.md` | `feature_catalogo_autogenerado.md` |
| `catalog_e2e.md` | `e2e_catalogo_autogenerado.md` |
| `architecture_validation_matrix.md` | `matrix_validacion_middleware.md` |
| `audit_phase1_phase2.md` | `audit_suite_redundancia.md` + este documento |

### Nueva (2026-06-27)

| Documento | Módulo |
|-----------|--------|
| `feature_control_plane.md` | Control PROC-007,008,015,020,034 |
| `feature_dashboard_observabilidad.md` | Dashboard PROC-004,013 |
| `feature_seguridad_identidad.md` | Security PROC-005,006 |
| `feature_integracion_webhooks.md` | Integration PROC-011,012 |
| `feature_plataforma_fleet_simulacion.md` | Platform PROC-009,010,020 |
| `Matriz_Cobertura_Funcional.md` | PROC-001…034 |
| `Matriz_Trazabilidad_Pruebas.md` | CU→BPMN→Test |
| `Informe_Validacion_Testing.md` | Métricas finales |
| `00_Auditoria_Testing.md` | Este informe |

## 4. Evolución por suite

| Suite | 2026-05-22 (est.) | 2026-06-24 | Nuevas áreas |
|-------|-------------------|------------|--------------|
| Unit | ~80 | **201** | Control, Platform/Fleet, Simulation, Observability Unit |
| Integration | ~15 | **21** | Platform tenant seeding, trace pipeline |
| Feature | ~60 | **139** | Control completo, Dashboard 17 endpoints, Identity, API v1 |
| E2E | ~2 | **2** | Sin cambio count; escenarios enriquecidos |

## 5. Módulos nuevos con cobertura (desde 2026-05-22)

| Módulo | Tests clave | BPMN |
|--------|-------------|------|
| Control Plane | `TenantModuleCatalogTest`, `CompanySimulationAutomationTest` | PROC-007–020, 034 |
| Dashboard REST | `DashboardEndpointsTest` (17 métodos) | PROC-004 |
| Observability | `PrometheusMetricsEndpointTest`, `CorrelationIdMiddlewareTest` | PROC-013 |
| Monitoring | `EvaluateMonitoringAlertsCommandTest`, `CanaryPublishCommandTest` | PROC-013 |
| Identity | `OperatorLoginTest`, `RoleBasedAuthorizationTest` | PROC-005 |
| Security API | `PlatformApiAuthenticationTest` | PROC-006 |
| Integration | `WebhookIngressTest`, `IntegrationAdminApiTest` | PROC-011,012 |
| Platform/Fleet | `SimulateClientCommandTest`, `LocalFleetRegistryTest` | PROC-009,010 |
| Quality | `CheckApplicationCoverageCommandTest` | PROC-016 |
| API v1 | `V1RoutesMirrorLegacyTest`, `OpenApiContractTest` | PROC-003 |

## 6. Funcionalidades eliminadas del core (no en suite)

Documentadas en [Funcionalidades_Obsoletas.csv](./Funcionalidades_Obsoletas.csv):

- Dominios retail **Inventario** / **Pedidos** en core Laravel.
- Tests `Inventario.*`, `Pedido.*` eliminados.
- Eventos omnicanal legacy → fixtures `Platform.*` agnósticos.
- **Multi-tenancy lógico Fase 3** (PROC-018) diferido — silos físicos por tenant (ADR-001).

## 7. Fallos resueltos (2026-06-24)

Ejecución: `php vendor/bin/phpunit` — **2026-06-24**, 364 tests, **0 failures**.

| ID | Test | Proceso | Causa raíz | Corrección |
|----|------|---------|------------|------------|
| **TC-0070** | `OperatorLoginTest::operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled` | PROC-005 | `PlatformDatabaseReadiness::canQuerySchema()` devolvía `false` con SQLite `:memory:` | Permitir `:memory:` como BD lista para consulta |
| **TC-0161** | `InstanceTenantSeedingIntegrationTest::message_queue_persists_tenant_id_after_seed` | PROC-011/010 | Misma causa — `tenantId()` no resolvía slug en tests | Idem |

Incidencias cerradas: INC-613e3b, INC-e36025. JUnit: `docs/testing/tools/last_junit.xml`.

## 8. Brechas de cobertura identificadas

| Brecha | Proceso/Requisito | Estado |
|--------|-------------------|--------|
| Flujo middleware 5 etapas | PROC-017 | Documental; cobertura indirecta PROC-001 |
| Provisioning VM producción | PROC-030 | Sin tests |
| Backup/DR | PROC-031, PROC-032 | Sin tests |
| Evaluación aceptación | PROC-033 | Framework docs/evaluation; no PHPUnit |
| Load sustained 100 eps | REQ-DYN-01 | k6 PENDIENTE ([load/README.csv](./load/README.csv)) |
| Espejo CP→Silo integración | PROC-034 | Parcial (Feature OK; ampliar multi-silo) |
| Multi-tenant lógico | PROC-018 | Diferido |

Detalle: [Matriz_Cobertura_Funcional.md](./Matriz_Cobertura_Funcional.md).

## 9. Duplicación aceptada (E2E vs Feature)

| Test | Rol |
|------|-----|
| `MiddlewarePipelineEndToEndTest` | Regresión sync + publish un tipo evento |
| `ClientProductionLikeSimulationTest` | Multi-tipo, payloads heterogéneos, escenario productivo |

Decisión AUD-08 en [audit_suite_redundancia.md](./audit_suite_redundancia.md).

## 10. Artefactos de evidencia

| Artefacto | Ubicación |
|-----------|-----------|
| Matriz maestra (363 filas) | [matriz_maestra_casos.csv](./matriz_maestra_casos.csv) |
| Catálogos auto-generados | `*_catalogo_autogenerado.md` |
| Mapa procesos BPMN | [00_Mapa_Procesos.md](../Diagrama_BPMN/00_Mapa_Procesos.md) |
| Brechas BPMN | [99_Validacion_Brechas.md](../Diagrama_BPMN/99_Validacion_Brechas.md) |
| Generadores | `docs/testing/tools/*.php` |

## 11. Acciones recomendadas

1. Ejecutar LOAD-01 k6 en staging y registrar en load/README.csv.
2. Añadir tests PROC-012 ampliados (rotación secretos, disable canal).
3. Mantener `export_test_matrix.php` en CI tras cada cambio de suite.

## 12. Referencias cruzadas

- Redundancia: [audit_suite_redundancia.md](./audit_suite_redundancia.md)
- Validación final: [Informe_Validacion_Testing.md](./Informe_Validacion_Testing.md)
- Trazabilidad: [Matriz_Trazabilidad_Pruebas.md](./Matriz_Trazabilidad_Pruebas.md)
