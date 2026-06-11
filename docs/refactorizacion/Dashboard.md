# Auditoria - Dashboard

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Dashboard |
| Ruta | `routes/web.php + app/Dashboard/Interfaces/Routes/api.php` |
| Namespace principal | `App\Dashboard\` |
| Tipo | Bounded Context de observabilidad |
| Total archivos | 68 (+3 Application layer) |
| Total clases | 85 |
| LOC aproximado | 2675 |
| Tests asociados | 13 (Unit 8 / Feature 2 / Integration 3 / E2E 0) |

## Responsabilidad del modulo

Expone metricas, feed de eventos, estado de nodos, catalogos y stream en vivo para operadores de instancia.

- Que hace: Expone metricas, feed de eventos, estado de nodos, catalogos y stream en vivo para operadores de instancia.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Observabilidad operativa
- Dependencias: usa Control, Http, Observability, Providers, Shared como entradas estaticas detectadas y publica hacia Shared, Control, Observability.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 10 |
| Services | 4 |
| Presenters | 1 |
| Adapters | 1 |
| Ports | 1 |
| Use Cases | 12 |
| Repositories | 11 |
| DTOs | 4 |
| Events | 1 |
| Jobs | 0 |
| Commands | 0 |
| Policies | 0 |
| Middleware | 0 |

Capa `Application/Presenters`, `Application/Adapters` y `Application/Contracts` introducida para aislar presentacion y frontera cross-BC.

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 2675 |
| LOC promedio por archivo | 39.3 |
| Clase mas grande | DynamicMetricSeriesPresenter (app/Dashboard/Application/Presenters/DynamicMetricSeriesPresenter.php, 98 LOC) |
| Metodo mas largo | DashboardServiceProvider::registerUseCases (app/Dashboard/Interfaces/Providers/DashboardServiceProvider.php, 82 LOC) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Media |

## Metricas de deuda tecnica

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 86% | 91% |
| Codigo aceptable | 9% | 7% |
| Codigo sucio | 5% | 2% |
| Codigo espagueti | 0% | 0% |
| Riesgo tecnico | Aceptable | Bueno |
| Mantenibilidad | Alta | Alta |
| Acoplamiento | Alto | Medio-Alto |
| Cohesion | Media | Alta |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR (0-100) | 38 | 22 |

Calculo heuristico: cobertura media-alta, eliminacion de service locator en dominio/ingestion, ACL hacia Control BC, y facades restantes acotadas a infraestructura. IRR menor = refactorizacion futura mas segura.

## Violaciones arquitectonicas

| Violacion | Antes | Despues |
| --------- | ----- | ------- |
| Service Locator / app() | 2 archivos | 0 archivos |
| Facades indebidas o acoplamiento a Facades | 7 archivos | 4 archivos |
| Dependencias cruzadas entre BCs | Shared, Control, Observability | Aisladas via ACL (`ClientDashboardMetricsPortInterface`, `ControlClientDashboardMetricsAdapter`) y DI en `DashboardKnownNodes` |

Facades eliminadas en: `DashboardServiceProvider` (Event), `RefreshSystemNodeUseCase` (Event), `DashboardBusEventIngestionService` (Log). Facades restantes: infraestructura de persistencia (DB) y listeners/projectors (Log).

## Dependencias

### Dependencias entrantes

Control, Http, Observability, Providers, Shared

### Dependencias salientes

Shared, Control, Observability

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Medio |
| Services | Bajo-Medio |
| Domain | Medio |
| Infraestructura | Medio |
| Tests | Bajo |

## Cobertura funcional

- Funcionalidades principales: Dashboard, EventFeed, EventStream, Metrics, ModulesCatalog
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Shared, Control, Observability

## Cobertura de pruebas

- Tests unitarios: 8
- Tests feature: 2
- Tests integracion: 3
- Clasificacion: Media-Alta

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Extraer request/presenter para controllers web restantes.
- Reemplazar facades DB/Log en repositorios y listeners por DI.

### Refactorizacion moderada

- Partir `DashboardServiceProvider::registerUseCases` en registradores por grupo funcional.
- Formalizar port para `ClientInstancePortalService` en `DashboardKnownNodes`.

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo | Estado |
| --------- | ------ | ------- | ------ | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (DynamicMetricSeriesBuilder) | Baja riesgo y hace mas visible la frontera | Medio | Completado |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado (parcial: 4 facades infra) |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Shared, Control, Observability y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Control, Http, Observability, Providers, Shared, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | `DynamicMetricSeriesBuilder` mezclaba consulta a repositorios y formato de charts | Builder reducido a resolucion de datos; `DynamicMetricSeriesPresenter` para payloads |
| P1 | Use cases acoplados directamente a `ClientDashboardMetricsCatalogService` (Control BC) | Port `ClientDashboardMetricsPortInterface` + adapter `ControlClientDashboardMetricsAdapter` |
| P1 | `DashboardKnownNodes` usaba `app(ClientInstancePortalService::class)` | Clase instanciable con DI de `ClientInstancePortalService`; inyectada en use cases y controllers |
| P2 | Payloads de series dinamicas sin contrato congelado | 5 tests unitarios en presenter y builder |
| P3 | Service locator `app()` en ingestion hooks | `Illuminate\Contracts\Container\Container` inyectado en `DashboardBusEventIngestionService` |
| P3 | Facades Event/Log en bordes de aplicacion | DI de `Dispatcher` y `LoggerInterface`; boot del provider usa `Dispatcher` |

### Cambios realizados

- Nuevos archivos:
  - `Application/Presenters/DynamicMetricSeriesPresenter.php`
  - `Application/Contracts/ClientDashboardMetricsPortInterface.php`
  - `Application/Adapters/ControlClientDashboardMetricsAdapter.php`
- Refactorizados (contratos publicos preservados):
  - `DynamicMetricSeriesBuilder`, `GetDynamicMetricSeriesUseCase`, `GetDashboardMetricCatalogUseCase`
  - `DashboardKnownNodes`, `GetSystemNodeStatusUseCase`, `RefreshSystemNodeUseCase`, `SetNodeMiddlewareEventsUseCase`
  - `NodeStatusController`, `ClientDashboardNodesWebController`
  - `DashboardBusEventIngestionService`, `DashboardServiceProvider`
- Tests nuevos: `tests/Unit/Dashboard/Presenters/DynamicMetricSeriesPresenterTest.php`, `tests/Unit/Dashboard/Services/DynamicMetricSeriesBuilderTest.php`

### Riesgos mitigados

- Regresion en payloads de `/api/dashboard/metrics/series/*` (tests feature + unitarios de presenter/builder).
- Service locator oculto en nodos monitorizados e ingestion post-feed.
- Acoplamiento directo use-case-to-Control en metricas dinamicas.

### Riesgos pendientes

- 4 archivos con facades DB/Log en infraestructura y listeners.
- `DashboardKnownNodes` sigue dependiendo de Control BC via `ClientInstancePortalService` (contrato intencional, no eliminado).
- `DashboardServiceProvider::registerUseCases` permanece como metodo largo de bindings manuales.

### Impacto funcional

- Ninguno observable: mismas rutas API, mismos shapes JSON de metric series, catalog, nodes status y feed; flujos web de nodos cliente verificados.

### Evidencia de validacion

```
php artisan test tests/Unit/Dashboard tests/Feature/Dashboard tests/Integration/Dashboard
→ 42 passed (137 assertions)
```

Incluye regresion de `get_dashboard_metric_series_returns_chart_payload`, reconciliacion SYNCING/ONLINE, modules catalog, node refresh/patch y platform ping integration.

## Veredicto final

**Bueno**. El modulo mantiene alta mantenibilidad, elimina service locator en puntos calientes, formaliza la frontera hacia Control para metricas de tenant y congela contratos de series dinamicas con tests. Queda preparado para SonarQube con IRR reducido; la infraestructura de persistencia puede abordarse en una segunda ronda.
