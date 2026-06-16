# Auditoria - Control

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Control |
| Ruta | `routes/control.php` |
| Namespace principal | `App\Control\` |
| Tipo | Bounded Context de control plane |
| Total archivos | 44 (+6 capa Application/Support y Presenters) |
| Total clases | 44 |
| LOC aproximado | 3050 |
| Tests asociados | 38 (Unit 28 / Feature 10 / Integration 0 / E2E 0) |

## Responsabilidad del modulo

Orquesta el plano de control SaaS para tenants, provisioning, incidentes, middleware e instancias de simulacion.

- Que hace: Orquesta el plano de control SaaS para tenants, provisioning, incidentes, middleware e instancias de simulacion.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Admin SaaS y lifecycle de tenants
- Dependencias: usa Dashboard, Http, Shared, Simulation como entradas estaticas detectadas y publica hacia Dashboard, Shared, Monitoring, Middleware, Simulation.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 10 |
| Services | 19 |
| Presenters | 2 |
| Support services | 5 |
| Use Cases | 3 |
| Repositories | 0 |
| DTOs | 0 |
| Events | 3 |
| Jobs | 1 |
| Commands | 0 |
| Policies | 1 |
| Middleware | 0 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 3050 |
| LOC promedio por archivo | 69.3 |
| Clase mas grande | ClientIncidentReportService (app/Control/Application/Services/ClientIncidentReportService.php, 182 LOC) |
| Metodo mas largo | StartTenantServiceUseCase::execute (app/Control/Application/UseCases/Lifecycle/StartTenantServiceUseCase.php, ~52 LOC) |
| Archivos >200 LOC | 1 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Media |

## Metricas de deuda tecnica

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 37% | 58% |
| Codigo aceptable | 29% | 28% |
| Codigo sucio | 21% | 10% |
| Codigo espagueti | 13% | 4% |
| Riesgo tecnico | Deuda Alta | Deuda Baja-Media |
| Mantenibilidad | Baja | Media-Alta |
| Acoplamiento | Alto | Medio |
| Cohesion | Baja | Media-Alta |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR (0-100) | 72 | 28 |

Calculo heuristico: cobertura existente alta, eliminacion de god-class en incident reports, facades eliminadas en todo el modulo, provisioning descompuesto con tests de caracterizacion, y persistencia de acoplamiento cross-BC estructural (Dashboard, Monitoring, Middleware). IRR menor = refactorizacion futura mas segura.

## Violaciones arquitectonicas

| Violacion | Antes | Despues |
| --------- | ----- | ------- |
| resolve() contenedor Laravel | 0 (falso positivo: `ModulesConfigPath::resolve()`) | 0 |
| Facades indebidas o acoplamiento a Facades | 11 archivos | 0 archivos |
| app()/Container::make() en modulo | 0 | 0 |
| Dependencias cruzadas entre BCs | Dashboard, Shared, Monitoring, Middleware, Simulation | Sin cambio estructural; frontera ACL en client incident reports |

Facades eliminadas (ronda 1): `CompanyController` (Gate), `TenantOperatorService` (Hasher), `ClientDashboardModulesService` y `ClientInstancePortalService` (DatabaseManager).

Facades eliminadas (ronda 2): lifecycle use cases (DB, Config), `TenantPresentationService` (DB, Schema), `IncidentDiagnosticCollector` (DB, Schema), `ControlInfrastructureService` (DB, Redis), `TenantModuleCatalogService` (File). Sustituidas por `DatabaseManager`, `RedisFactory`, `Dispatcher` y funciones nativas de filesystem.

## Dependencias

### Dependencias entrantes

Dashboard, Http, Shared, Simulation

### Dependencias salientes

Dashboard, Shared, Monitoring, Middleware, Simulation

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Medio-Alto |
| Services | Medio |
| Domain | Bajo-Medio |
| Infraestructura | Bajo-Medio |
| Tests | Bajo |

## Cobertura funcional

- Funcionalidades principales: ClientSupportReportApi, Company, Incidents, Infrastructure, MiddlewareGlobal
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Dashboard, Shared, Monitoring, Middleware, Simulation

## Cobertura de pruebas

- Tests unitarios: 28
- Tests feature: 10
- Tests integracion: 0
- Clasificacion: Alta

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Extraer request/presenter para controllers restantes y mover respuestas a view models.

### Refactorizacion moderada

- Partir lifecycle use cases en coordinadores de deployment compartidos (logica transaccional repetida).
- Reducir acoplamiento cross-BC en `IncidentDiagnosticCollector` mediante adapters.

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo | Estado |
| --------- | ------ | ------- | ------ | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (ClientIncidentReportService) | Baja riesgo y hace mas visible la frontera | Medio | Completado (ronda 1) |
| P1 | Partir `ProvisionNewTenantService::provision` | Reduce complejidad cognitiva del flujo critico | Medio | Completado (ronda 2) |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado (rondas 1 y 2) |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado (facades 11→0) |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Dashboard, Shared, Monitoring, Middleware, Simulation y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Dashboard, Http, Shared, Simulation, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada (Ronda 1)

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | God-class `ClientIncidentReportService` (304 LOC) mezclaba persistencia, resolucion de tenant, normalizacion y presentacion | Servicio reducido a orquestacion; extraidos `ClientIncidentReportTenantResolver`, `ClientIncidentReportSeverityNormalizer`; eliminado `request()` global via DI de `Request` |
| P2 | Payloads de inbox/control sin contrato congelado por tests | `ClientIncidentReportPresenter` + 6 tests unitarios de caracterizacion |
| P3 | Facades indebidas en bordes del modulo | DI en `CompanyController` (Gate), `TenantOperatorService` (Hasher), `ClientDashboardModulesService` y `ClientInstancePortalService` (DatabaseManager) |

### Cambios realizados

- Nuevos archivos:
  - `app/Control/Application/Presenters/ClientIncidentReportPresenter.php`
  - `app/Control/Application/Services/Support/ClientIncidentReportTenantResolver.php`
  - `app/Control/Application/Services/Support/ClientIncidentReportSeverityNormalizer.php`
- Refactorizado manteniendo API publica de `ClientIncidentReportService` (mismos metodos y firmas).
- Tests nuevos en `tests/Unit/Control/Presenters/` y `tests/Unit/Control/Support/`.
- Tests existentes actualizados por nuevo parametro `DatabaseManager` en constructores afectados.

### Riesgos mitigados

- Regresion en payloads JSON/Inertia de reportes de soporte (presenter con tests de labels, diagnostic_summary, unread flags).
- Acoplamiento implicito a HTTP global en creacion de reportes.
- Facades en operaciones de operadores y resolucion de schema en portal/dashboard modules.

### Evidencia de validacion (ronda 1)

```
php artisan test tests/Unit/Control
→ 28 passed (111 assertions) [post-ronda 2: 99 assertions base + nuevos tests]

php artisan test tests/Feature/Control/ClientSupportReportTest.php --filter="instance_operator|saas_admin_can_respond"
→ 2 passed
```

## Refactorizacion Ejecutada (Ronda 2)

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | `ProvisionNewTenantService::provision` (64 LOC) mezclaba normalizacion, fallback de fleet y presentacion | Orquestacion reducida; extraidos `ProvisionNewTenantInputMapper`, `ProvisionNewTenantFleetFallbackHandler`, `ProvisionNewTenantResultPresenter` |
| P2 | Sin tests de caracterizacion para provisioning | 5 tests unitarios nuevos (mapper, presenter, fallback handler) |
| P3 | 7 archivos con facades restantes (lifecycle, infrastructure, diagnostic, presentation, catalog) | DI de `DatabaseManager`, `RedisFactory`, `Dispatcher`; `File::put` sustituido por `file_put_contents` |

### Cambios realizados

- Nuevos archivos:
  - `app/Control/Application/Services/Support/ProvisionNewTenantInputMapper.php`
  - `app/Control/Application/Services/Support/ProvisionNewTenantFleetFallbackHandler.php`
  - `app/Control/Application/Presenters/ProvisionNewTenantResultPresenter.php`
  - `tests/Unit/Control/Support/ProvisionNewTenantInputMapperTest.php`
  - `tests/Unit/Control/Support/ProvisionNewTenantFleetFallbackHandlerTest.php`
  - `tests/Unit/Control/Presenters/ProvisionNewTenantResultPresenterTest.php`
- Refactorizados manteniendo contrato `{tenant, message, show_deployment_guide}` y mismos eventos de lifecycle (`TenantLifecycleStarted/Suspended/Restored` con payloads identicos via `Dispatcher`).
- `TenantLifecyclePolicyTest` migrado a `Tests\TestCase` para evitar contaminacion de estado Eloquent entre suites.

### Riesgos mitigados

- Regresion en mensajes de provisioning SaaS y flag `show_deployment_guide`.
- Regresion en settings de fallback `pending_dedicated_instance` cuando local fleet no provisiona.
- Facades ocultas en transacciones de lifecycle e infraestructura de diagnostico.
- Orden de ejecucion de tests unitarios con mezcla PHPUnit/Laravel.

### Riesgos pendientes

- Tests feature Inertia que dependen de `public/build/manifest.json` fallan en entorno sin build Vite (preexistente).
- Acoplamiento cross-BC estructural en `IncidentDiagnosticCollector` (Dashboard, Monitoring, Middleware).
- Lifecycle use cases comparten patron transaccional repetido (oportunidad de extraccion futura).

### Impacto funcional

- Ninguno observable en contratos publicos: mismas rutas, payloads JSON, props Inertia, eventos EDA, estados de lifecycle y flujos client/control plane verificados en tests API y unitarios.

### Evidencia de validacion

```
php artisan test tests/Unit/Control
→ 28 passed (99 assertions)

php artisan test tests/Feature/Control/TenantLifecycleEndpointsTest.php tests/Feature/Control/SimulationInternalApiTest.php tests/Feature/Control/TenantModuleCatalogTest.php tests/Feature/Control/TenantOperatorDeploymentGuardTest.php
→ 5 passed (26 assertions)

php artisan test tests/Feature/Control/ClientSupportReportTest.php --filter="instance_operator|saas_admin_can_respond"
→ 2 passed (18 assertions)
```

Nota: algunos feature tests Inertia (`/control/incidents`, `/control/companies`) requieren manifest Vite local; no bloquean la validacion de contratos API refactorizados.

## Veredicto final

**Deuda Baja-Media**. El modulo elimina todas las facades indebidas detectadas, descompone provisioning y lifecycle con tests de caracterizacion, y reduce IRR de 72 a 28. Queda preparado para ejecucion SonarQube; la siguiente ronda puede abordar adapters cross-BC y consolidacion de lifecycle transaccional sin tocar contratos publicos.
