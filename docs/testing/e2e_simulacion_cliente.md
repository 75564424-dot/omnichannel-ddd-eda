# E2E — simulación tipo cliente / producción

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [e2e_simulacion_cliente.csv](./e2e_simulacion_cliente.csv)

---

## Objetivo

Ejecutar **escenarios multi-paso** que simulan una instancia cliente real: catálogo declarativo, sincronización registry, publicación de **varios tipos de evento** con payloads heterogéneos, y comprobación de observabilidad (cola, topología, snapshot dashboard), validando reutilización por cliente (ADR-001 silos).

## Alcance

- **2 métodos** E2E en `tests/E2E/Middleware/`:
  - `ClientProductionLikeSimulationTest::multi_event_type_client_sync_publish_queue_catalog_and_event_status_remain_consistent`
  - `MultiClientFixtureSimulationTest::all_versioned_client_fixtures_simulate_successfully`
- Complementa regresiones Feature (`MiddlewarePipelineEndToEndTest`) sin sustituirlas.

## Precondiciones

- Stack completo Laravel en test E2E (HTTP interno o kernel).
- Fixtures cliente en `tests/fixtures/` o catálogos versionados referenciados por slug.
- `QUEUE_CONNECTION=sync`, SQLite en memoria.

## Postcondiciones

- Coherencia extremo a extremo: config declarada ↔ ejecución bus ↔ APIs JSON operación/dashboard.
- Segunda sync idempotente sin romper consistencia.
- Todos los fixtures versionados simulan sin error.

## Casos

| ID | Caso | Clase | Resultado |
|----|------|-------|-----------|
| TC-E2E-01 | Multi-tipo evento + payloads heterogéneos | `ClientProductionLikeSimulationTest` | PASÓ |
| TC-E2E-02 | Todos los fixtures cliente versionados | `MultiClientFixtureSimulationTest` | PASÓ |

Detalle: [e2e_simulacion_cliente.csv](./e2e_simulacion_cliente.csv).

### Flujo TC-E2E-01

1. Definir `modules.catalog` y `eventbus.producers` / `eventbus.subscriptions` para dos tipos y dos consumidores.
2. `POST sync-config`.
3. Validar catálogo vía API dashboard.
4. `POST publish` con `Platform.E2E.Client.OrderPlaced` (`order_ref`) y `Platform.E2E.Client.StockAdjusted` (`sku`/`delta`).
5. Verificar `PROCESADO`, cola, topología y snapshot.

## Criterios de aceptación

- Payloads de dominio distintos procesados sin validación semántica en middleware.
- Estados de evento consultables por API.
- Fixtures multi-cliente pasan en lote (TC-E2E-02).
- Simulación desde Control Plane cubierta en Feature (`CompanySimulationAutomationTest`, PROC-020).

## Resultados

**2026-06-27:** 2/2 métodos E2E **PASÓ**.

Comando:

```bash
php vendor/bin/phpunit --testsuite E2E
```

Relacionado CLI: `platform:simulate-client` (`SimulateClientCommandTest` Feature Platform).

## Observaciones

- Duplicación funcional con Feature documentada en [audit_suite_redundancia.md](./audit_suite_redundancia.md) (AUD-08): E2E enfatiza **multi-tipo** y **fixtures heterogéneos**.
- Simulación orquestada desde Control Plane (PROC-029) no es E2E PHPUnit; ver Feature Control.
- Runbook: `docs/production/` (simulación cliente).

## Riesgos

| Riesgo | Impacto |
|--------|---------|
| Fixtures desactualizados vs catálogo | TC-E2E-02 falla en CI |
| Solo 2 tests E2E | Cobertura camino feliz limitada |
| Diferencias silo local vs VM prod | Falsos positivos en simulación |

## Dependencias

- `docs/Diagrama_BPMN/18_Proceso_Simulacion_Cliente_E2E.md`
- `docs/Diagrama_BPMN/29_Proceso_Simulacion_Desde_Control_Plane.md`
- `docs/evaluation/10_Matriz_Aceptacion_Final.csv`
- Feature Control: `SimulationRunReportTest`, `CompanySimulationAutomationTest`

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| CSV E2E | `e2e_simulacion_cliente.csv` |
| Catálogo E2E | `e2e_catalogo_autogenerado.md` |
| JUnit | `docs/testing/tools/last_junit.xml` |
| Matriz maestra TC-0001, TC-0002 | `matriz_maestra_casos.csv` |

## Componentes

| Componente | Rol |
|------------|-----|
| `SimulateClientCommand` | Orquestación CLI simulación |
| `ClientProductionLikeSimulationTest` | Escenario multi-evento HTTP |
| `MultiClientFixtureSimulationTest` | Regresión fixtures versionados |
| `SimulationRunOrchestrator` | Simulación desde CP (Feature) |

## Trazabilidad BPMN

| Proceso | Documento |
|---------|-----------|
| PROC-009 Simulación E2E cliente | [18_Proceso_Simulacion_Cliente_E2E.md](../Diagrama_BPMN/18_Proceso_Simulacion_Cliente_E2E.md) |
| PROC-020 Simulación CP | [29_Proceso_Simulacion_Desde_Control_Plane.md](../Diagrama_BPMN/29_Proceso_Simulacion_Desde_Control_Plane.md) |
| PROC-033 Evaluación aceptación | [33_Proceso_Evaluacion_Aceptacion_Middleware.md](../Diagrama_BPMN/33_Proceso_Evaluacion_Aceptacion_Middleware.md) |

Macroproceso calidad: [05_Macroproceso_Calidad_Validacion.md](../Diagrama_BPMN/05_Macroproceso_Calidad_Validacion.md).
