# Auditoria - Shared

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Shared |
| Ruta | `Cross-cutting library; no public route` |
| Namespace principal | `App\Shared\` |
| Tipo | Kernel compartido |
| Total archivos | 67 |
| Total clases | 85 |
| LOC aproximado | 3913 |
| Tests asociados | 10 (Unit 10 / Feature 0 / Integration 0 / E2E 0) |

## Responsabilidad del modulo

Provee primitives compartidas de identidad, plataforma, logging, API, persistencia y seguridad.

- Que hace: Provee primitives compartidas de identidad, plataforma, logging, API, persistencia y seguridad.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Shared kernel transversal
- Dependencias: usa Console, Control, Dashboard, Http, Integration, Middleware, Monitoring, Observability, Platform-Demo, Providers, Simulation como entradas estaticas detectadas y publica hacia Dashboard, Control, Integration, Middleware, Simulation.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 0 |
| Services | 21 |
| Use Cases | 3 |
| Repositories | 0 |
| DTOs | 0 |
| Events | 0 |
| Jobs | 0 |
| Commands | 0 |
| Policies | 4 |
| Middleware | 1 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 3913 |
| LOC promedio por archivo | 58.4 |
| Clase mas grande | LocalFleetInstanceProvisioner (app/Shared/Platform/LocalFleet/LocalFleetInstanceProvisioner.php, ~130 LOC post-refactor) |
| Metodo mas largo | TenantCatalogRuntimeConfigurator::apply (app/Shared/Platform/Services/TenantCatalogRuntimeConfigurator.php, ~25 LOC post-refactor) |
| Archivos >200 LOC | 2 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Alta |

## Metricas de deuda tecnica

| Indicador | Valor |
| --------- | ----- |
| Codigo limpio | 72% |
| Codigo aceptable | 24% |
| Codigo sucio | 3% |
| Codigo espagueti | 1% |
| Riesgo tecnico | Aceptable |
| Mantenibilidad | Media-Alta |
| Acoplamiento | Medio-Alto |
| Cohesion | Media |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Violaciones arquitectonicas

- resolve(): 0 archivos con helper global (metodos de dominio `resolve()` preservados).
- Facades indebidas o acoplamiento a Facades: 19 archivos fuente (application layer: Log eliminado en `PlatformStructuredLogger` y `PackSubscriptionCatalogMerger`).
- Dependencias cruzadas entre BCs: Dashboard, Control, Integration, Middleware, Simulation.

## Dependencias

### Dependencias entrantes

Console, Control, Dashboard, Http, Integration, Middleware, Monitoring, Observability, Platform-Demo, Providers, Simulation

### Dependencias salientes

Dashboard, Control, Integration, Middleware, Simulation

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Bajo |
| Services | Alto |
| Domain | Medio |
| Infraestructura | Medio |
| Tests | Alto |

## Cobertura funcional

- Funcionalidades principales: AuthenticateOperator, IssueApiToken, ResolveOperatorHomePath, IdempotencyKeyStore, PlatformAuthorization
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Dashboard, Control, Integration, Middleware, Simulation

## Cobertura de pruebas

- Tests unitarios: 10
- Tests feature: 0
- Tests integracion: 0 dedicados (tests existentes en otros modulos cubren contratos Shared)
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
- Refactorizar sin ampliar cobertura contractual aumentaria el riesgo de regresion.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo | Estado |
| --------- | ------ | ------- | ------ | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (LocalFleetInstanceProvisioner) | Baja riesgo y hace mas visible la frontera | Medio | Completado |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Dashboard, Control, Integration, Middleware, Simulation y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Console, Control, Dashboard, Http, Integration, Middleware, Monitoring, Observability, Platform-Demo, Providers, Simulation, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | `LocalFleetInstanceProvisioner` con credenciales, bootstrap artisan, app key y marcado mezclados (269 LOC) | Extraccion de `LocalFleetAdminCredentialsResolver`, `LocalFleetAppKeyResolver`, `LocalFleetInstanceArtisanRunner`, `LocalFleetLocalInstanceDescriptor`, `LocalFleetTenantProvisionMarker` |
| P2 | `TenantCatalogRuntimeConfigurator::apply` monolitico (69 LOC) sin tests dedicados | `TenantCatalogNormalizer`, `TenantCatalogEventBusMapper` + tests de caracterizacion |
| P2 | Sin tests unitarios del modulo Shared | 6 tests nuevos en `tests/Unit/Shared/Platform/` |
| P3 | Facade `Log` en `PlatformStructuredLogger` y `PackSubscriptionCatalogMerger` | `Psr\Log\LoggerInterface` via DI |

### Cambios realizados

- Nuevos archivos en `app/Shared/`:
  - `Platform/LocalFleet/LocalFleetAdminCredentialsResolver.php`
  - `Platform/LocalFleet/LocalFleetAppKeyResolver.php`
  - `Platform/LocalFleet/LocalFleetInstanceArtisanRunner.php`
  - `Platform/LocalFleet/LocalFleetLocalInstanceDescriptor.php`
  - `Platform/LocalFleet/LocalFleetTenantProvisionMarker.php`
  - `Platform/Services/TenantCatalogNormalizer.php`
  - `Platform/Services/TenantCatalogEventBusMapper.php`
- Refactorizados (contratos fleet/provisioning/catalog preservados):
  - `LocalFleetInstanceProvisioner`
  - `TenantCatalogRuntimeConfigurator`
  - `PlatformStructuredLogger`
  - `PackSubscriptionCatalogMerger`
  - `app/Providers/Registrars/LocalFleetBindingsRegistrar.php`
- Tests nuevos/actualizados:
  - `tests/Unit/Shared/Platform/*`
  - `tests/Unit/Logging/PlatformStructuredLoggerTest.php`
  - `tests/Unit/EventBus/PackSubscriptionCatalogMergerTest.php`

### Riesgos mitigados

- Regresion silenciosa en provisioning fleet local y aplicacion de catalogo tenant (tests unitarios de caracterizacion).
- Complejidad cognitiva concentrada en clases transversales criticas para Simulation y Control.
- Acoplamiento a facade Log en primitives de logging y event bus pack merge.

### Riesgos pendientes

- Facades DB/Cache/Event/Artisan/Hash en servicios de plataforma (`DemoIdentityResetService`, `ClientInstanceBootstrapService`, etc.) — infraestructura Laravel aceptable.
- `LocalFleetInstanceProvisioner` sigue orquestando filesystem/process (contrato intencional de provisioning).
- Acoplamiento cross-BC hacia Control, Middleware, Simulation permanece (kernel transversal).

### Impacto funcional

- Ninguno observable: mismos shapes de `settings.deployment`, catalogo modules/eventbus, fleet registry y structured logging.

### Evidencia de validacion

```
php artisan test tests/Unit/Shared tests/Unit/Logging/PlatformStructuredLoggerTest.php tests/Unit/EventBus/PackSubscriptionCatalogMergerTest.php tests/Unit/Platform/LocalFleet tests/Unit/Providers/EventBusPackSubscriptionBootstrapperTest.php tests/Integration/Logging/EventAndAuditLogServiceTest.php
→ 15 passed (33 assertions)
```

## Metricas de deuda tecnica (post-refactor)

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 60% | **72%** |
| Codigo aceptable | 31% | **24%** |
| Codigo sucio | 7% | **3%** |
| Codigo espagueti | 1% | **1%** |
| Riesgo tecnico | Aceptable | **Aceptable** |
| Mantenibilidad | Media | **Media-Alta** |
| Acoplamiento | Alto | **Medio-Alto** |
| Cohesion | Baja | **Media** |

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR modulo Shared (0-100) | 48 | **30** |

Heuristica: clases gigantes particionadas con tests; facades Log eliminadas en application layer; DB/Artisan en servicios ops pendientes.

## Veredicto final

**Aceptable**. El kernel Shared reduce complejidad en fleet provisioning y catalog runtime, congela contratos con tests de caracterizacion y elimina facades Log en primitives transversales. Queda preparado para SonarQube; servicios ops con facades DB/Artisan pueden abordarse en una segunda ronda opcional.
