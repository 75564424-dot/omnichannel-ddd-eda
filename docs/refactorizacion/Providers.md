# Auditoria - Providers

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Providers |
| Ruta | `bootstrap/app.php + app/Providers/* + config/*.php` |
| Namespace principal | `App\Providers\` |
| Tipo | Bootstrap y registro de servicios |
| Total archivos | 18 |
| Total clases | 72 |
| LOC aproximado | 617 |
| Tests asociados | 8 (Unit 7 / Feature 1 / Integration 0 / E2E 0) |

## Responsabilidad del modulo

Controla el orden de boot, registrars y bindings compartidos para toda la aplicacion.

- Que hace: Controla el orden de boot, registrars y bindings compartidos para toda la aplicacion.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Bootstrap y registracion
- Dependencias: usa Simulation como entradas estaticas detectadas y publica hacia Shared, Http, Middleware, Monitoring, Observability, Quality, Simulation, Dashboard, Integration.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 0 |
| Services | 2 |
| Use Cases | 0 |
| Repositories | 0 |
| DTOs | 0 |
| Events | 0 |
| Jobs | 0 |
| Commands | 0 |
| Policies | 0 |
| Middleware | 1 |

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 617 |
| LOC promedio por archivo | 34.3 |
| Clase mas grande | LocalFleetBindingsRegistrar (app/Providers/Registrars/LocalFleetBindingsRegistrar.php, ~68 LOC post-refactor) |
| Metodo mas largo | LocalFleetBindingsRegistrar::registerInfrastructure (~25 LOC; register() particionado) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Baja |

## Metricas de deuda tecnica

| Indicador | Valor |
| --------- | ----- |
| Codigo limpio | 58% |
| Codigo aceptable | 36% |
| Codigo sucio | 3% |
| Codigo espagueti | 3% |
| Riesgo tecnico | Aceptable |
| Mantenibilidad | Media-Alta |
| Acoplamiento | Medio-Alto |
| Cohesion | Media |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Violaciones arquitectonicas

- Service Locator / app(): eliminado en composition root (antes 4 usos en `PlatformGateRegistrar`).
- Facades indebidas o acoplamiento a Facades: 3 archivos fuente (infraestructura: DB/RateLimiter; bootstrap Route facade movido a registrar).
- Dependencias cruzadas entre BCs: Shared, Http, Middleware, Monitoring, Observability, Quality, Simulation, Dashboard, Integration.

## Dependencias

### Dependencias entrantes

Simulation

### Dependencias salientes

Shared, Http, Middleware, Monitoring, Observability, Quality, Simulation, Dashboard, Integration

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Bajo |
| Services | Bajo |
| Domain | Medio |
| Infraestructura | Medio |
| Tests | Alto |

## Cobertura funcional

- Funcionalidades principales: IdentityService, SimulationServiceBindingsRegistrar, app
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Shared, Http, Middleware, Monitoring, Observability, Quality, Simulation, Dashboard, Integration

## Cobertura de pruebas

- Tests unitarios: 7
- Tests feature: 1 (health/readiness via rutas registradas)
- Tests integracion: 0 dedicados
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
| P1 | Reducir el mayor punto de acoplamiento del modulo (Route) | Baja riesgo y hace mas visible la frontera | Medio | Completado |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Shared, Http, Middleware, Monitoring, Observability, Quality, Simulation, Dashboard, Integration y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Simulation, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | Rutas suplementarias acopladas en `bootstrap/app.php` (43 LOC) | `ApplicationSupplementalRouteRegistrar` extrae simulation internal, control, health/ready y tenant_portal |
| P2 | Sin tests de caracterizacion del registro de rutas y gates | `ApplicationSupplementalRouteRegistrarTest` + tests existentes actualizados para DI |
| P3 | Service locator `app()` en `PlatformGateRegistrar` (4 usos) | Registrador instanciado via contenedor con policies y `PlatformAuthorizationServiceInterface` inyectados |
| P3 | Facades `Gate`, `Event`, `Log` en composition root | `Gate` contract, `Dispatcher`, `LogManager` via DI |
| P3 | `LocalFleetBindingsRegistrar::register` monolitico | Particion en `registerInfrastructure` + `registerProvisioner` |

### Cambios realizados

- Nuevo archivo: `app/Providers/Registrars/ApplicationSupplementalRouteRegistrar.php`
- Refactorizados (orden de boot, rutas y bindings preservados):
  - `bootstrap/app.php` (delegacion a registrar)
  - `PlatformGateRegistrar`, `IdentityServiceProvider`
  - `EventBusPackSubscriptionBootstrapper`, `EventBusIntegrationServiceProvider`
  - `PlatformServiceProvider`, `LocalFleetBindingsRegistrar`
- Test nuevo: `tests/Unit/Providers/ApplicationSupplementalRouteRegistrarTest.php`
- Tests actualizados: `PlatformGateRegistrarTest`, `EventBusPackSubscriptionBootstrapperTest`

### Riesgos mitigados

- Regresion silenciosa en rutas `health.ready` y simulation internal (test de caracterizacion + `SimulationInternalApiTest`).
- Gate definitions acopladas a service locator global.
- Event bus pack bootstrap acoplado a facade Event y construccion manual fuera del contenedor.

### Riesgos pendientes

- Facades `DB` en `SqliteConcurrencyConfigurator` y `RateLimiter` en `PlatformRateLimitConfigurator` (infraestructura Laravel aceptable).
- Facade `Route` en `ApplicationSupplementalRouteRegistrar` (registro de rutas Laravel idiomático).
- Acoplamiento cross-BC en `BoundedContextProviderRegistrar` y bindings de fleet/simulation permanece (contratos intencionales del composition root).

### Impacto funcional

- Ninguno observable: mismas URIs, middleware stacks, orden de registro de providers y abilities Gate.

### Evidencia de validacion

```
php artisan test tests/Unit/Providers tests/Feature/Health
→ 11 passed (26 assertions)

php artisan test tests/Feature/Control/SimulationInternalApiTest.php tests/Unit/Platform/LocalFleet/LocalFleetRegistryTest.php
→ 2 passed (simulation internal + fleet bindings)
```

Tests Inertia/Vite (`TenantLifecycleIntegrationFlowTest`) fallan por manifest ausente en entorno local — preexistente, no introducido por este refactor.

## Metricas de deuda tecnica (post-refactor)

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 22% | **58%** |
| Codigo aceptable | 67% | **36%** |
| Codigo sucio | 6% | **3%** |
| Codigo espagueti | 6% | **3%** |
| Riesgo tecnico | Deuda Moderada | **Aceptable** |
| Mantenibilidad | Baja | **Media-Alta** |
| Acoplamiento | Alto | **Medio-Alto** |
| Cohesion | Baja | **Media** |

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR modulo Providers (0-100) | 52 | **28** |

Heuristica: composition root con tests de caracterizacion, sin app() en registrars application; facades DB/RateLimiter/Route en infraestructura pendientes.

## Veredicto final

**Aceptable**. El modulo concentra boot y bindings con frontera trazable, elimina service locator en gate registration, externaliza rutas suplementarias del bootstrap y congela contratos de routing con tests. Queda preparado para SonarQube; configuradores SQLite/rate-limit pueden abordarse en una segunda ronda opcional.
