# Auditoria - Console

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Console |
| Ruta | `Artisan commands and routes/console.php` |
| Namespace principal | `App\Console\` |
| Tipo | Operational command pack |
| Total archivos | 24 (+5 Application layer) |
| Total clases | 27 |
| LOC aproximado | 808 |
| Tests asociados | 18 (Unit 12 / Feature 6 / Integration 0 / E2E indirecto 1) |

## Responsabilidad del modulo

Administra tareas operativas, demo y mantenimiento: bootstrap, purge, reset, validation, token operations y simulacion de plataforma.

- Que hace: Administra tareas operativas, demo y mantenimiento: bootstrap, purge, reset, validation, token operations y simulacion de plataforma.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Operaciones y mantenimiento
- Dependencias: usa Sin referencias estaticas detectadas como entradas estaticas detectadas y publica hacia Shared, Simulation.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 0 |
| Services | 3 |
| Presenters | 2 |
| Use Cases | 0 |
| Repositories | 0 |
| DTOs | 1 (SimulateClientCommandOptions) |
| Events | 0 |
| Jobs | 0 |
| Commands | 19 |
| Policies | 0 |
| Middleware | 0 |

Capa `App\Console\Application\` introducida: orquestador ACL, validadores de opciones CLI y presenters de salida.

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 808 |
| LOC promedio por archivo | 33.7 |
| Clase mas grande | SimulateClientConsoleReporter (app/Console/Application/Presenters/SimulateClientConsoleReporter.php, 82 LOC) |
| Metodo mas largo | SimulateClientConsoleReporter::reportSimulationResult (~30 LOC) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Baja |

## Metricas de deuda tecnica

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 74% | 86% |
| Codigo aceptable | 26% | 14% |
| Codigo sucio | 0% | 0% |
| Codigo espagueti | 0% | 0% |
| Riesgo tecnico | Bueno | Muy bueno |
| Mantenibilidad | Alta | Alta |
| Acoplamiento | Bajo | Bajo |
| Cohesion | Alta | Alta |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR (0-100) | 42 | 18 |

Calculo heuristico: cobertura de tests del modulo, violaciones de facades, acoplamiento cross-BC en el command de mayor superficie, y complejidad cognitiva media por archivo. IRR menor = refactorizacion futura mas segura.

## Violaciones arquitectonicas

| Violacion | Antes | Despues |
| --------- | ----- | ------- |
| Facades indebidas o acoplamiento a Facades | 3 archivos | 0 archivos |
| Dependencias cruzadas entre BCs | Shared, Simulation | Shared, Simulation (aisladas via ACL en simulate-client) |
| app()/resolve()/Container::make() | 0 | 0 |

## Dependencias

### Dependencias entrantes

Sin referencias estaticas detectadas

### Dependencias salientes

Shared, Simulation

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Bajo |
| Services | Bajo |
| Domain | Bajo |
| Infraestructura | Bajo |
| Tests | Medio |

## Cobertura funcional

- Funcionalidades principales: EmitDashboardDemoEvents, EmitMockPlatformEvent, PurgePlatformRetention, ResetDemoIdentity, ResetOperationalDemoData
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Shared, Simulation

## Cobertura de pruebas

- Tests unitarios: 12
- Tests feature: 6
- Tests integracion: 0
- Clasificacion: Media-Alta

## Codigo muerto

- No se confirmo codigo muerto con evidencia concluyente; los entrypoints dinamicos de Laravel dificultan cerrar trazabilidad estatica.
- Candidatos de baja trazabilidad revisados y conservados: `SimulateClientCommand`, `PurgePlatformRetentionCommand` (activos en Artisan y schedule).

## Oportunidades de mejora

### Refactorizacion segura

- Aislar adapters por BC y formalizar ACL o mappers en commands restantes (PrepareSimulation, ValidatePlatformCatalog).

### Refactorizacion moderada

- Extraer presenters para commands Demo/Ops con salida compuesta.

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.
- Refactorizar Simulation BC desde Console sin ampliar cobertura contractual.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo | Estado |
| --------- | ------ | ------- | ------ | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (SimulateClientCommand) | Baja riesgo y hace mas visible la frontera | Medio | Completado |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Shared, Simulation y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Sin referencias estaticas detectadas, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | Acoplamiento directo de `SimulateClientCommand` a servicios Shared | ACL `SimulateClientOrchestrator` + `SimulateClientCommandOptions` |
| P2 | Presentacion mezclada con orquestacion en simulate-client y purge-retention | `SimulateClientConsoleReporter`, `PurgePlatformRetentionConsoleReporter`, `PurgePlatformRetentionTableValidator` |
| P2 | Sin tests de caracterizacion del modulo Console | 12 tests unitarios nuevos + feature tests existentes preservados |
| P3 | Facades indebidas en 3 commands | DI: `Dispatcher`, `LogManager`, `DatabaseManager` |

### Cambios realizados

- Nuevos archivos en `app/Console/Application/`:
  - `Services/Simulation/SimulateClientOrchestrator.php`
  - `Services/Simulation/SimulateClientCommandOptions.php`
  - `Services/Ops/PurgePlatformRetentionTableValidator.php`
  - `Presenters/SimulateClientConsoleReporter.php`
  - `Presenters/PurgePlatformRetentionConsoleReporter.php`
- Commands refactorizados (firmas Artisan, payloads y flujo preservados):
  - `SimulateClientCommand`
  - `PurgePlatformRetentionCommand`
  - `EmitMockPlatformEventCommand`
  - `EnsureInstanceTenantCommand`
  - `ResetSimulationRunsCommand`
- Tests de caracterizacion en `tests/Unit/Console/`.

### Riesgos mitigados

- Regresion silenciosa en salida CLI de simulate-client y purge-retention (presenters con tests unitarios).
- Acoplamiento directo command-to-Shared en el entrypoint de simulacion mas usado.
- Uso de Facades en bordes del modulo (eliminado en los 3 archivos detectados).

### Riesgos pendientes

- Commands Demo/Ops restantes aun mezclan formato de salida con logica de invocacion.
- Dependencia cross-BC Shared/Simulation permanece en orquestador ACL (contrato intencional).
- Cobertura feature de `EmitMockPlatformEventCommand`, `EnsureInstanceTenantCommand` y `ResetSimulationRunsCommand` aun no dedicada.

### Impacto funcional

- Ninguno observable: mismas firmas Artisan, mismos mensajes CLI, mismos codigos de salida, mismos payloads hacia Shared/Simulation.

### Evidencia de validacion

```
php artisan test --filter="SimulateClient|PurgePlatform|PurgePlatformRetention|SimulateClientCommandOptions|SimulateClientOrchestrator|SimulateClientConsoleReporter|PurgePlatformRetentionTableValidator|PurgePlatformRetentionConsoleReporter"
→ 18 passed (45 assertions)

php artisan test tests/E2E/Middleware/MultiClientFixtureSimulationTest.php
→ 1 passed
```

## Veredicto final

**Muy bueno**. El modulo mantiene frontera funcional trazable, elimina facades indebidas en su perimetro, concentra la simulacion de clientes detras de un ACL testeado y queda preparado para una proxima pasada SonarQube con menor IRR y cobertura de caracterizacion ampliada.
