# Instrumento — Matriz de evidencias (tests ↔ artefactos)

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Fuente maestra de casos:** [docs/testing/matriz_maestra_casos.csv](../matriz_maestra_casos.csv) (363 filas TC-*)  
**Framework de evaluación:** [docs/evaluation/Middleware_Acceptance_Evaluation_Framework.md](../../evaluation/Middleware_Acceptance_Evaluation_Framework.md)

## 1. Propósito

Vincular cada proceso operativo y requisito formal con la evidencia verificable: tests PHPUnit, comandos, artefactos de documentación y salidas CI.

## 2. Tipos de evidencia

| Código | Descripción |
|--------|-------------|
| **TEST** | Método PHPUnit ejecutado en CI |
| **CMD** | Comando artisan o script ops |
| **DOC** | Documento/plan/runbook como evidencia documental |
| **ART** | Artefacto generado (junit, clover, openapi, grafana json) |
| **CFG** | Archivo de configuración versionado |

## 3. Cobertura por macroproceso BPMN

| Macroproceso | Procesos | Evidencia TEST principal | Artefactos DOC |
|--------------|----------|--------------------------|----------------|
| Middleware eventos | PROC-001–003 | MiddlewareControlApiTest, EventPublisherServiceIntegrationTest | Plan_Middleware.md, Flujo_Middleware.md |
| Observabilidad | PROC-004 | DashboardEndpointsTest, PrometheusMetricsEndpointTest | Plan_Observabilidad.md, ADR_009 |
| Seguridad | PROC-005–006 | PlatformApiAuthenticationTest, RoleBasedAuthorizationTest | Plan_Seguridad.md, Matriz_Endpoints_Seguridad.md |
| Simulación | PROC-009 | ClientProductionLikeSimulationTest, simulate-client-smoke.sh | Runbook_Simulacion_Cliente.md |
| Validación catálogo | PROC-016 | ValidatePlatformCatalogTest | Plan_de_implementacion.md §B.3 |
| Integraciones | PROC-011 | WebhookIngressTest, IntegrationAdminApiTest | Plan_Integraciones.md |

## 4. Artefactos CI y herramientas

| Artefacto | Ruta | Generado por |
|-----------|------|--------------|
| JUnit última ejecución | `docs/testing/tools/last_junit.xml` | `composer test` / sync |
| Matriz maestra casos | `docs/testing/matriz_maestra_casos.csv` | `export_test_matrix.php` |
| Catálogos auto | `docs/testing/*_catalogo_autogenerado.md` | `generate_test_catalogs.php` |
| OpenAPI | `docs/api/openapi.yaml` | OpenApiContractTest |
| Clover (opcional) | `build/coverage/clover.xml` | `platform:quality-coverage` |

## 5. CSV

Filas representativas por proceso — ver [Matriz_Evidencias.csv](./Matriz_Evidencias.csv) para el inventario completo de evidencias por proceso/requisito.

## 6. Referencias evaluation

- [01_Matriz_Arquitectura.csv](../../evaluation/01_Matriz_Arquitectura.csv)
- [02_Matriz_Middleware.csv](../../evaluation/02_Matriz_Middleware.csv)
- [13_Matriz_Trazabilidad.csv](../../evaluation/13_Matriz_Trazabilidad.csv)
