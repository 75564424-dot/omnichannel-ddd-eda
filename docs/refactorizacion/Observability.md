# Auditoria - Observability

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Observability |
| Ruta | `API/Prometheus entrypoints + service provider bindings` |
| Namespace principal | `App\Observability\` |
| Tipo | Bounded Context de telemetria |
| Total archivos | 13 |
| Total clases | 23 |
| LOC aproximado | 431 |
| Tests asociados | 9 (Unit 6 / Feature 2 / Integration 1 / E2E 0) |

## Responsabilidad del modulo

Publica y resume metrics, trazas y texto Prometheus para SLI/SLO y diagnostico de lag.

- Que hace: Publica y resume metrics, trazas y texto Prometheus para SLI/SLO y diagnostico de lag.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Telemetria y SLI
- Dependencias: usa Dashboard, Middleware, Monitoring, Providers como entradas estaticas detectadas y publica hacia Dashboard, Middleware, Monitoring, Shared, Http.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 1 |
| Services | 8 |
| Use Cases | 0 |
| Repositories | 3 |
| DTOs | 0 |
| Events | 0 |
| Jobs | 0 |
| Commands | 0 |
| Policies | 0 |
| Middleware | 0 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 431 |
| LOC promedio por archivo | 33.2 |
| Clase mas grande | PrometheusTextRenderer (app/Observability/Application/Services/Prometheus/PrometheusTextRenderer.php, 55 LOC) |
| Metodo mas largo | PrometheusTextRenderer::render (app/Observability/Application/Services/Prometheus/PrometheusTextRenderer.php, 47 LOC) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Baja |

## Metricas de deuda tecnica

| Indicador | Valor |
| --------- | ----- |
| Codigo limpio | 54% |
| Codigo aceptable | 46% |
| Codigo sucio | 0% |
| Codigo espagueti | 0% |
| Riesgo tecnico | Aceptable |
| Mantenibilidad | Media |
| Acoplamiento | Alto |
| Cohesion | Baja |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Violaciones arquitectonicas

- Facades indebidas o acoplamiento a Facades: 4 archivos fuente.
- Dependencias cruzadas entre BCs: Dashboard, Middleware, Monitoring, Shared, Http.

## Dependencias

### Dependencias entrantes

Dashboard, Middleware, Monitoring, Providers

### Dependencias salientes

Dashboard, Middleware, Monitoring, Shared, Http

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Medio |
| Services | Medio |
| Domain | Medio |
| Infraestructura | Medio |
| Tests | Bajo |

## Cobertura funcional

- Funcionalidades principales: PrometheusMetrics, FeedProjectionLagCalculator, PrometheusGaugeCollector, PrometheusGaugeSnapshot, PrometheusTextRenderer
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Dashboard, Middleware, Monitoring, Shared, Http

## Cobertura de pruebas

- Tests unitarios: 6
- Tests feature: 2
- Tests integracion: 1
- Clasificacion: Media

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Extraer request/presenter para controllers y mover respuestas a DTOs o view models.
- Separar lectura, escritura y mapeo en servicios de soporte.
- Aislar adapters por BC y formalizar ACL o mappers.

### Refactorizacion moderada

- Partir controllers o services grandes manteniendo nombres de rutas y payloads.
- Dividir los servicios mas grandes por responsabilidad.
- Reducir lookup de contenedor y Facades en bordes del modulo.

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo |
| --------- | ------ | ------- | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (PrometheusTextRenderer) | Baja riesgo y hace mas visible la frontera | Medio |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Dashboard, Middleware, Monitoring, Shared, Http y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Dashboard, Middleware, Monitoring, Providers, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Veredicto final

**Aceptable**. El modulo presenta una mezcla de logica estable y puntos de acoplamiento que siguen siendo sensibles. La frontera funcional existe y es trazable, pero la refactorizacion futura debe preservar rutas, payloads y bindings porque siguen siendo contratos publicos reales.
