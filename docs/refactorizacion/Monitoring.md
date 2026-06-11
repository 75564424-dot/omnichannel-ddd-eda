# Auditoria - Monitoring

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Monitoring |
| Ruta | `service provider bindings + console/reporting entrypoints` |
| Namespace principal | `App\Monitoring\` |
| Tipo | Bounded Context de health checks |
| Total archivos | 17 |
| Total clases | 21 |
| LOC aproximado | 603 |
| Tests asociados | 8 (Unit 6 / Feature 2 / Integration 0 / E2E 0) |

## Responsabilidad del modulo

Evalua alertas, canary checks, capacidad y estado operativo de colas, base de datos y runtime.

- Que hace: Evalua alertas, canary checks, capacidad y estado operativo de colas, base de datos y runtime.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Salud operativa y alertas
- Dependencias: usa Control, Observability, Providers como entradas estaticas detectadas y publica hacia Middleware, Shared, Observability.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 0 |
| Services | 12 |
| Use Cases | 0 |
| Repositories | 0 |
| DTOs | 0 |
| Events | 0 |
| Jobs | 0 |
| Commands | 2 |
| Policies | 0 |
| Middleware | 0 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 603 |
| LOC promedio por archivo | 35.5 |
| Clase mas grande | MonitoringAlertsConsoleReporter (app/Monitoring/Application/Services/MonitoringAlertsConsoleReporter.php, 57 LOC) |
| Metodo mas largo | BusMetricsAlertEvaluator::evaluate (app/Monitoring/Application/Services/Evaluators/BusMetricsAlertEvaluator.php, 38 LOC) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Baja |

## Metricas de deuda tecnica

| Indicador | Valor |
| --------- | ----- |
| Codigo limpio | 59% |
| Codigo aceptable | 41% |
| Codigo sucio | 0% |
| Codigo espagueti | 0% |
| Riesgo tecnico | Aceptable |
| Mantenibilidad | Media |
| Acoplamiento | Medio |
| Cohesion | Media |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Violaciones arquitectonicas

- Facades indebidas o acoplamiento a Facades: 5 archivos fuente.
- Dependencias cruzadas entre BCs: Middleware, Shared, Observability.

## Dependencias

### Dependencias entrantes

Control, Observability, Providers

### Dependencias salientes

Middleware, Shared, Observability

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Bajo |
| Services | Medio |
| Domain | Medio |
| Infraestructura | Bajo |
| Tests | Bajo |

## Cobertura funcional

- Funcionalidades principales: AlertEvaluation, CanaryProbeEnvelopeFactory, CanaryQueueCompletionVerifier, CanarySuccessTracker, CanaryPublish
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Middleware, Shared, Observability

## Cobertura de pruebas

- Tests unitarios: 6
- Tests feature: 2
- Tests integracion: 0
- Clasificacion: Media

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Separar lectura, escritura y mapeo en servicios de soporte.
- Aislar adapters por BC y formalizar ACL o mappers.

### Refactorizacion moderada

- Dividir los servicios mas grandes por responsabilidad.
- Reducir lookup de contenedor y Facades en bordes del modulo.

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo |
| --------- | ------ | ------- | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (MonitoringAlertsConsoleReporter) | Baja riesgo y hace mas visible la frontera | Medio |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Middleware, Shared, Observability y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Control, Observability, Providers, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Veredicto final

**Aceptable**. El modulo presenta una mezcla de logica estable y puntos de acoplamiento que siguen siendo sensibles. La frontera funcional existe y es trazable, pero la refactorizacion futura debe preservar rutas, payloads y bindings porque siguen siendo contratos publicos reales.
