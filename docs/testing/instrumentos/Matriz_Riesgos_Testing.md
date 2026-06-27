# Instrumento — Matriz de riesgos de testing

**Versión:** 1.0  
**Fecha:** 2026-06-24  
**Evidencia de ejecución:** `docs/testing/tools/last_junit.xml` (364 tests, 0 failures, 0 errors)  
**Matriz de evaluación relacionada:** [docs/evaluation/08_Matriz_Calidad.csv](../../evaluation/08_Matriz_Calidad.csv), [docs/evaluation/13_Matriz_Trazabilidad.csv](../../evaluation/13_Matriz_Trazabilidad.csv)

## 1. Propósito

Registrar riesgos activos de la suite de pruebas: fallos abiertos, zonas inestables, brechas de cobertura y dependencias que afectan la decisión de release.

## 2. Resumen ejecutivo

| Categoría | Cantidad | Severidad máxima |
|-----------|----------|------------------|
| Fallos actuales (CI) | 0 | — |
| Áreas flaky / sensibles a config | 4 | Media |
| Brechas de cobertura documentadas | 12 | Alta–Media |
| Deuda documental en tests | 3 | Baja |

## 3. Fallos resueltos (2026-06-24)

| ID | Caso | Corrección |
|----|------|------------|
| RSK-F01 | `operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled` | `PlatformDatabaseReadiness` — SQLite `:memory:` |
| RSK-F02 | `message_queue_persists_tenant_id_after_seed` | Idem |

## 4. Áreas flaky o sensibles

- **Simulación control plane** (`SimulationRunCancellationTest`, `CompanySimulationAutomationTest`): dependen de handoff files y workers; sensibles a timing en entornos lentos.
- **Portal routing** (`TenantPortalRoutingTest`): múltiples flags (`friendly_routing`, host CP vs silo); orden de config puede variar resultado.
- **Dashboard feed reconciliation** (`DashboardEndpointsTest::idle_dashboard_feed_*`): lógica de reconciliación con snapshot middleware — sensible a estado previo en BD.
- **QUEUE_CONNECTION=sync** en `phpunit.xml`: semántica eventual consistency de producción no reproducida en CI (documentado en README testing).

## 5. Brechas de cobertura prioritarias

Referencia cruzada: [99_Validacion_Brechas.md](../../Diagrama_BPMN/99_Validacion_Brechas.md), [priority_tests_matrix.md](../priority_tests_matrix.md).

| Brecha | Requisito | Estado tests |
|--------|-----------|--------------|
| UI E2E (Playwright/Cypress) | Plan_Calidad §2 | No implementado |
| Load/performance (k6 CI gate) | C14 throughput | Script k6 existe; no gate en CI |
| Restore backup E2E | PROC-031 | Manual only |
| SSE feed vivo (REQ-O5) | REQ-O5 | PENDIENTE_VALIDACION |
| OWASP/ZAP API scan | REQ-SEC-03 | Pentest checklist manual |
| Coverage ≥70% gate | REQ-QA-01 | Script existe; PENDIENTE_VALIDACION |
| Pipeline 5 etapas retail | REQ-FLOW-01 | Documentado; no tests core |
| Config dinámica sin redeploy | REQ-DYN-01 | Sin cobertura (no cumple) |
| Provisioning fleet completo | PROC-008 | Parcial — ProvisionNewTenantFleetFallbackHandlerTest |
| Rate limiting HTTP | REQ-SEC-02 | Config existe; sin Feature test dedicado |
| Security headers middleware HTTP | REQ-SEC-03 | Unit CSP; sin Feature end-to-end |
| Multi-tenant lógico Fase 3 | REQ-MT-01 | Diferido |

## 6. Mitigaciones recomendadas

1. Activar `platform:quality-coverage --min=70` en CI (REQ-QA-01).
2. Añadir Feature test de rate limit y headers en respuesta HTTP.
3. Ejecutar LOAD-01 k6 en staging.

## 7. CSV

Detalle completo: [Matriz_Riesgos_Testing.csv](./Matriz_Riesgos_Testing.csv).
