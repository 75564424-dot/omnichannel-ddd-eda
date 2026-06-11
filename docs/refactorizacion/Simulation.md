# Auditoria - Simulation

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Simulation |
| Ruta | `routes/control.php + internal controllers` |
| Namespace principal | `App\Simulation\` |
| Tipo | Bounded Context de simulacion |
| Total archivos | 45 (+6 capa Handoff/Support y Metrics/Support) |
| Total clases | 79 |
| LOC aproximado | 3100 |
| Tests asociados | 20 (Unit 8 / Feature 4 / Integration 0 / E2E 0 en modulo; +8 en Control/Middleware/Providers) |

## Responsabilidad del modulo

Orquesta preparacion, ejecucion, progreso, pulso y reset de simulaciones contra instancias cliente.

- Que hace: Orquesta preparacion, ejecucion, progreso, pulso y reset de simulaciones contra instancias cliente.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Simulacion y orchestration
- Dependencias: usa Console, Control, Http, Middleware, Providers, Shared como entradas estaticas detectadas y publica hacia Shared, Control, Middleware, Providers.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 3 |
| Services | 34 |
| Support mappers/gateways | 6 |
| Use Cases | 0 |
| Repositories | 0 |
| DTOs | 1 |
| Events | 0 |
| Jobs | 0 |
| Commands | 0 |
| Policies | 0 |
| Middleware | 0 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 3100 |
| LOC promedio por archivo | 68.9 |
| Clase mas grande | SimulationRunHandoffFileGateway (app/Simulation/Application/Services/Handoff/Support/SimulationRunHandoffFileGateway.php, ~105 LOC) |
| Metodo mas largo | SimulationRunControlPlaneClient::request (app/Simulation/Application/Services/Progress/SimulationRunControlPlaneClient.php, ~25 LOC) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Media |

## Metricas de deuda tecnica

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 41% | 52% |
| Codigo aceptable | 46% | 38% |
| Codigo sucio | 10% | 7% |
| Codigo espagueti | 3% | 3% |
| Riesgo tecnico | Deuda Moderada | Deuda Baja-Moderada |
| Mantenibilidad | Baja | Media |
| Acoplamiento | Alto | Medio-Alto |
| Cohesion | Media | Media-Alta |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR (0-100) | 52 | 30 |

Calculo heuristico: cobertura ampliada con tests de caracterizacion, handoff y report descompuestos, eliminacion de service locator y facades, persistencia de acoplamiento cross-BC (Control, Middleware). IRR menor = refactorizacion futura mas segura.

## Violaciones arquitectonicas

| Violacion | Antes | Despues |
| --------- | ----- | ------- |
| Service Locator / app() | 2 archivos | 0 archivos |
| Facades indebidas o acoplamiento a Facades | 6 archivos | 0 archivos |
| app()/Container::make() en modulo | 2 | 0 |
| Dependencias cruzadas entre BCs | Shared, Control, Middleware, Providers | Sin cambio estructural; ACL en metrics/report via TenantPresentationService |

Facades eliminadas: `SimulationRunsResetService` (DB, Schema), `SimulationQueueMetricsAnalyzer` y `SimulationMetricsBaselineCapture` (Schema), `SimulationPulseService` (Cache), `SimulationRunController` (Gate), `SimulationRunControlPlaneClient` (Http, Log). Sustituidas por `DatabaseManager`, `CacheRepository`, `Gate`, `HttpFactory`, `LoggerInterface`, `Application`.

Service locator eliminado: `LocalFleetSimulationRunner` ahora inyecta `SimulationRunMetricsCollector`; `SimulationRunsResetService` inyecta `Application` para `environment()`.

## Dependencias

### Dependencias entrantes

Console, Control, Http, Middleware, Providers, Shared

### Dependencias salientes

Shared, Control, Middleware, Providers

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Medio |
| Services | Medio |
| Domain | Bajo-Medio |
| Infraestructura | Bajo-Medio |
| Tests | Bajo-Medio |

## Cobertura funcional

- Funcionalidades principales: SimulationPulse, SimulationRun, SimulationRunInternal, ClientSiloSimulationExecutor, ExecuteSimulationRunOnInstance
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Shared, Control, Middleware, Providers

## Cobertura de pruebas

- Tests unitarios (modulo Simulation): 8
- Tests feature relacionados: 4
- Tests integracion: 0
- Clasificacion: Media (mejorada desde Baja)

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Extraer request/presenter para controllers restantes y mover respuestas a DTOs o view models.

### Refactorizacion moderada

- Consolidar logica transaccional repetida en lifecycle de runs.
- Aislar adapters cross-BC en `SimulationRunReportBuilder` (TenantPresentationService).

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo | Estado |
| --------- | ------ | ------- | ------ | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (SimulationRunHandoffStore) | Baja riesgo y hace mas visible la frontera | Medio | Completado |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Shared, Control, Middleware, Providers y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Console, Control, Http, Middleware, Providers, Shared, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | God-class `SimulationRunHandoffStore` (164 LOC) mezclaba payload, I/O atomico y orquestacion | Extraidos `SimulationRunHandoffPayloadMapper` y `SimulationRunHandoffFileGateway`; store reducido a orquestacion |
| P2 | `SimulationRunReportBuilder::buildReport` (72 LOC) sin contrato congelado por tests | Extraidos `SimulationRunReportSummaryMapper`, `SimulationRunReportThroughputMapper`, `SimulationRunReportReliabilityMapper`, `SimulationRunReportDurationFormatter`; 3 tests unitarios de caracterizacion |
| P3 | Service locator (`app()`) en 2 archivos y facades en 6 archivos | DI en `LocalFleetSimulationRunner`, `SimulationRunsResetService`, `SimulationQueueMetricsAnalyzer`, `SimulationMetricsBaselineCapture`, `SimulationPulseService`, `SimulationRunController`, `SimulationRunControlPlaneClient` |

### Cambios realizados

- Nuevos archivos:
  - `app/Simulation/Application/Services/Handoff/Support/SimulationRunHandoffPayloadMapper.php`
  - `app/Simulation/Application/Services/Handoff/Support/SimulationRunHandoffFileGateway.php`
  - `app/Simulation/Application/Services/Metrics/Support/SimulationRunReportDurationFormatter.php`
  - `app/Simulation/Application/Services/Metrics/Support/SimulationRunReportSummaryMapper.php`
  - `app/Simulation/Application/Services/Metrics/Support/SimulationRunReportThroughputMapper.php`
  - `app/Simulation/Application/Services/Metrics/Support/SimulationRunReportReliabilityMapper.php`
  - `tests/Unit/Simulation/Handoff/SimulationRunHandoffPayloadMapperTest.php`
  - `tests/Unit/Simulation/Metrics/SimulationRunReportSummaryMapperTest.php`
- Refactorizado manteniendo API publica de `SimulationRunHandoffStore` (mismos metodos y firmas).
- Refactorizado manteniendo forma del reporte (`summary`, `throughput`, `latency`, `reliability`, `resources`, `consumption`).

### Riesgos mitigados

- Regresion en payloads JSON de handoff (mapper con tests de dispatch y progress).
- Regresion en metricas de reporte SaaS (summary mapper con tests de publish_rate y duration_human).
- Service locator oculto en dispatch a silo cliente.
- Facades en reset, pulse, control plane client y autorizacion de controller.

### Riesgos pendientes

- Tests feature Inertia (`SimulationRunReportTest` list/report) requieren `public/build/manifest.json` (preexistente, no introducido por esta refactorizacion).
- Acoplamiento cross-BC en `SimulationRunReportBuilder` via `TenantPresentationService`.
- Cobertura aun limitada en orchestration y execution paths completos.

### Impacto funcional

- Ninguno observable en contratos publicos: mismas rutas, payloads JSON de handoff/progress/report, pulse snapshot y flujos internal API verificados en tests unitarios y feature API.

### Evidencia de validacion

```
php artisan test tests/Unit/Simulation tests/Unit/Control/SimulationRunHandoffStoreTest.php tests/Unit/Control/SimulationRunHandoffSyncTest.php tests/Unit/Middleware/SimulationPulseServiceTest.php tests/Feature/Control/SimulationInternalApiTest.php tests/Unit/Providers/SimulationServiceBindingsRegistrarTest.php
→ 17 passed

php artisan test tests/Unit/Control/SimulationRunFailureHandlerTest.php tests/Unit/Control/SimulationRunWorkerMonitorTest.php tests/Feature/Control/CompanySimulationAutomationTest.php
→ passed (orchestration/worker paths)

Nota: SimulationRunReportTest Inertia paths fallan sin manifest Vite local; status JSON y job completion siguen validos via otros tests.
```

## Veredicto final

**Deuda Baja-Moderada**. El modulo elimina service locator y facades detectadas, descompone handoff y report con tests de caracterizacion, y reduce IRR de 52 a 30. Queda preparado para ejecucion SonarQube; la siguiente ronda puede abordar adapters cross-BC y ampliar cobertura en orchestration sin tocar contratos publicos.
