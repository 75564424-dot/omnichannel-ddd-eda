# Auditoria - Quality

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Quality |
| Ruta | `CLI/CI entrypoints, scripts and workflows` |
| Namespace principal | `App\Quality\` |
| Tipo | Boundary de calidad |
| Total archivos | 32 |
| Total clases | 12 |
| LOC aproximado | 637 |
| Tests asociados | 4 (Unit 3 / Feature 1 / Integration 0 / E2E 0) |

## Responsabilidad del modulo

Agrupa gates de cobertura, settings y reportes de calidad para Application y la tuberia CI.

- Que hace: Agrupa gates de cobertura, settings y reportes de calidad para Application y la tuberia CI.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Calidad, cobertura y gates
- Dependencias: usa Providers como entradas estaticas detectadas y publica hacia Sin dependencias salientes estaticas.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 0 |
| Services | 4 |
| Use Cases | 0 |
| Repositories | 0 |
| DTOs | 0 |
| Events | 0 |
| Jobs | 0 |
| Commands | 1 |
| Policies | 0 |
| Middleware | 0 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 637 |
| LOC promedio por archivo | 19.9 |
| Clase mas grande | QualitySettings (app/Quality/Application/Services/QualitySettings.php, 54 LOC) |
| Metodo mas largo | QualityCoverageConsoleReporter::report (app/Quality/Application/Services/QualityCoverageConsoleReporter.php, 28 LOC) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Baja |

## Metricas de deuda tecnica

| Indicador | Valor |
| --------- | ----- |
| Codigo limpio | 100% |
| Codigo aceptable | 0% |
| Codigo sucio | 0% |
| Codigo espagueti | 0% |
| Riesgo tecnico | Excelente |
| Mantenibilidad | Alta |
| Acoplamiento | Bajo |
| Cohesion | Alta |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Violaciones arquitectonicas

- No se detectaron violaciones arquitectonicas estaticas con evidencia suficiente.

## Dependencias

### Dependencias entrantes

Providers

### Dependencias salientes

Sin dependencias salientes estaticas

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Bajo |
| Services | Medio |
| Domain | Bajo |
| Infraestructura | Bajo |
| Tests | Medio |

## Cobertura funcional

- Funcionalidades principales: ApplicationCoverageCalculator, ApplicationCoverageGate, QualityCoverageConsoleReporter, QualitySettings, CheckApplicationCoverage
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Ninguno

## Cobertura de pruebas

- Tests unitarios: 3
- Tests feature: 1
- Tests integracion: 0
- Clasificacion: Baja

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Separar lectura, escritura y mapeo en servicios de soporte.

### Refactorizacion moderada

- Dividir los servicios mas grandes por responsabilidad.

### Refactorizacion de alto riesgo

- Refactorizar sin ampliar cobertura contractual aumentaria el riesgo de regresion.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo |
| --------- | ------ | ------- | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (QualitySettings) | Baja riesgo y hace mas visible la frontera | Medio |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Ninguno y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Providers, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Veredicto final

**Excelente**. El modulo presenta una mezcla de logica estable y puntos de acoplamiento que siguen siendo sensibles. La frontera funcional existe y es trazable, pero la refactorizacion futura debe preservar rutas, payloads y bindings porque siguen siendo contratos publicos reales.
