# Auditoría de la suite — redundancia, obsolescencia y limpieza

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [audit_suite_redundancia.csv](./audit_suite_redundancia.csv)

---

## Objetivo

Registrar decisiones de **limpieza de documentación y pruebas** para evitar duplicación, referencias a flujos eliminados y métricas obsoletas; mantener un único conjunto de catálogos auto-generados y matrices estratégicas alineadas al estado actual del proyecto (Control Plane + silos, ADR-001).

## Alcance

- Directorio `docs/testing/` y convenciones de nombres en `tests/*`.
- **No modifica** código de aplicación; documenta decisiones QA/arquitectura.
- Contraste suite actual vs documentación histórica (2026-05-03, 2026-05-22).

## Precondiciones

- Inventario de tests: `php docs/testing/tools/generate_test_catalogs.php`.
- Salida PHPUnit/JUnit en `docs/testing/tools/last_junit.xml`.
- Matriz maestra exportada: `php docs/testing/tools/export_test_matrix.php`.

## Postcondiciones

- Un solo conjunto de catálogos con prefijo `*_catalogo_autogenerado.md`.
- Matrices estratégicas (8 documentos + CSV) actualizadas a **2026-06-27**.
- Elementos obsoletos marcados explícitamente en CSV (AUD-01…AUD-09).
- Fallos activos documentados con incidencia (AUD-11, AUD-12).

## Casos

| ID | Elemento | Tipo | Estado | Decisión |
|----|----------|------|--------|----------|
| AUD-01 | `catalog_unit.md` | Documento | Obsoleto | Eliminado → `unit_catalogo_autogenerado.md` |
| AUD-02 | `catalog_integration.md` | Documento | Obsoleto | Eliminado → `integration_catalogo_autogenerado.md` |
| AUD-03 | `catalog_feature.md` | Documento | Obsoleto | Eliminado → `feature_catalogo_autogenerado.md` |
| AUD-04 | `catalog_e2e.md` | Documento | Obsoleto | Eliminado → `e2e_catalogo_autogenerado.md` |
| AUD-05 | `architecture_validation_matrix.md` | Documento | Obsoleto | Reemplazado → `matrix_validacion_middleware.md` |
| AUD-06 | `audit_phase1_phase2.md` | Documento | Obsoleto | Fusionado en este documento |
| AUD-07 | Conteo 160 tests (2026-05-22) | Métrica | Obsoleto | Actualizado: 362 métodos / 363 PHPUnit |
| AUD-08 | E2E vs Feature middleware | Duplicación | Vigente | Coexistencia justificada |
| AUD-09 | Flujos omnicanal legacy | Feature código | Obsoleto | No en suite; fixtures `Platform.*` |
| AUD-10 | `matriz_maestra_casos.csv` | Artefacto | Vigente | Fuente maestra 362 filas |
| AUD-11 | `InstanceTenantSeedingIntegrationTest::message_queue_persists_tenant_id_after_seed` | Test | Vigente | **FALLÓ** 2026-06-27 |
| AUD-12 | `OperatorLoginTest::operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled` | Test | Vigente | **FALLÓ** 2026-06-27 |

Detalle en [audit_suite_redundancia.csv](./audit_suite_redundancia.csv).

## Criterios de aceptación

- Ningún documento estratégico referencia catálogos eliminados (`catalog_*.md`).
- README.md refleja conteos por suite (Unit 200, Integration 21, Feature 139, E2E 2).
- Duplicación E2E/Feature documentada con rol diferenciado (AUD-08).
- Features obsoletas omnicanal marcadas como no presentes en suite.

## Resultados

### Suite actual (2026-06-27)

| Suite | Métodos | PHPUnit |
|-------|---------|---------|
| Unit | 200 | 200 PASÓ |
| Integration | 21 | 20 PASÓ, 1 FALLÓ |
| Feature | 139 | 138 PASÓ, 1 FALLÓ |
| E2E | 2 | 2 PASÓ |
| **Total** | **362** | **363 tests, 361 OK, 2 failures** |

### Módulos con cobertura nueva (desde auditoría 2026-05-03)

Control, Dashboard, Simulation, Platform/Fleet, Observability, Monitoring, Quality, Integration, Security, Identity, API v1.

### Fallos activos

1. **`Tests\Integration\Platform\InstanceTenantSeedingIntegrationTest::message_queue_persists_tenant_id_after_seed`** — `tenant_id` no persiste en `message_queue` tras seed.
2. **`Tests\Feature\Identity\OperatorLoginTest::operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled`** — redirect esperado `http://localhost/login`, actual difiere.

### Duplicación funcional aceptada (AUD-08)

- **`MiddlewarePipelineEndToEndTest`** (Feature): regresión B.2 sync + publish + observabilidad un tipo de evento.
- **`ClientProductionLikeSimulationTest`** (E2E): multi-tipo de evento, payloads heterogéneos, simulación cliente productiva.

## Observaciones

- Generador de catálogos actualizado con preámbulo estándar de 7+ secciones por suite.
- Tras eliminar tests versionados, ejecutar `composer dump-autoload`.
- `docs/testing/README.md` enlaza matrices estratégicas y catálogos auto-generados.

## Riesgos

| Riesgo | Impacto |
|--------|---------|
| Métricas desactualizadas en README | Decisiones QA incorrectas |
| Reintroducir docs `catalog_*.md` | Duplicación y confusión |
| Ignorar 2 fallos en CI | Regresión multi-tenant y portal |
| Pruebas load sin ejecutar | Brecha capacidad vs baseline 100 eps |

## Dependencias

- `docs/testing/tools/generate_test_catalogs.php`
- `docs/testing/tools/sync_test_stats.php`
- `docs/testing/tools/export_test_matrix.php`
- `docs/testing/tools/export_strategic_csvs.php`
- `.github/workflows/ci.yml`

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| JUnit | `docs/testing/tools/last_junit.xml` |
| Matriz maestra | `docs/testing/matriz_maestra_casos.csv` |
| Auditoría CSV | `docs/testing/audit_suite_redundancia.csv` |
| Catálogos vigentes | `docs/testing/*_catalogo_autogenerado.md` |

## Componentes

| Área | Tests representativos |
|------|----------------------|
| Control | `CompanySimulationAutomationTest`, `TenantModuleCatalogTest` |
| Dashboard | `DashboardEndpointsTest`, `ConfigModulesCatalogPresentationTest` |
| Simulation | `SimulationRunReportTest`, `ClientProductionLikeSimulationTest` |
| Platform/Fleet | `LocalFleetRegistryTest`, `InstanceTenantSeedingIntegrationTest` |
| Identity | `OperatorLoginTest` (fallo activo) |
| API v1 | `V1RoutesMirrorLegacyTest`, `OpenApiContractTest` |

## Trazabilidad BPMN

| Macroproceso | Documento | Relación auditoría |
|--------------|-----------|-------------------|
| Calidad y validación | [05_Macroproceso_Calidad_Validacion.md](../Diagrama_BPMN/05_Macroproceso_Calidad_Validacion.md) | Gate CI y catálogos |
| Operación middleware | [02_Macroproceso_Operacion_Middleware_Eventos.md](../Diagrama_BPMN/02_Macroproceso_Operacion_Middleware_Eventos.md) | Duplicación E2E/Feature |
| Integración omnicanal | [08_Macroproceso_Integracion_Omnicanal.md](../Diagrama_BPMN/08_Macroproceso_Integracion_Omnicanal.md) | Legacy obsoleto (AUD-09) |
| Multi-tenancy | [27_Proceso_Multi_Tenancy_Logico_Fase3.md](../Diagrama_BPMN/27_Proceso_Multi_Tenancy_Logico_Fase3.md) | Fallo seeding (AUD-11) |
| Portal cliente | [28_Proceso_Portal_Instancia_Cliente.md](../Diagrama_BPMN/28_Proceso_Portal_Instancia_Cliente.md) | Fallo login (AUD-12) |

Brechas documentadas: [99_Validacion_Brechas.md](../Diagrama_BPMN/99_Validacion_Brechas.md).
