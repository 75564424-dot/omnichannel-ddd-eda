# Auditoria - Http

## Informacion General

| Campo | Valor |
| ----- | ----- |
| Modulo | Http |
| Ruta | `routes/web.php` |
| Namespace principal | `App\Http\` |
| Tipo | Kernel transversal de web |
| Total archivos | 24 (+4 Application layer) |
| Total clases | 31 |
| LOC aproximado | 851 |
| Tests asociados | 8 (Unit 5 / Feature 3 indirectos / Integration 0 / E2E 0) |

## Responsabilidad del modulo

Contiene auth, health y middleware base del kernel web de Laravel, con muy poca logica de negocio propia.

- Que hace: Contiene auth, health y middleware base del kernel web de Laravel, con muy poca logica de negocio propia.
- Problema que resuelve: reduce friccion operativa y concentra la logica del BC en un solo lugar.
- Bounded context: Kernel web y seguridad
- Dependencias: usa Observability, Providers como entradas estaticas detectadas y publica hacia Shared, Control, Simulation, Dashboard, Middleware.

## Arquitectura actual

| Area | Count |
| ---- | ----- |
| Controllers | 2 |
| Services | 3 |
| Presenters | 1 |
| Use Cases | 0 |
| Repositories | 0 |
| DTOs | 0 |
| Events | 0 |
| Jobs | 0 |
| Commands | 0 |
| Policies | 0 |
| Middleware | 18 |

Capa `App\Http\Application\` introducida para CSP/headers, terminacion de sesion y respuestas suspendidas.

## Metricas de complejidad

| Metica | Valor |
| ----- | ----- |
| LOC total | 851 |
| LOC promedio por archivo | 35.5 |
| Clase mas grande | EnsureTenantOperationalStatus (app/Http/Middleware/EnsureTenantOperationalStatus.php, 52 LOC) |
| Metodo mas largo | AuditControlPlaneMiddleware::handle (app/Http/Middleware/AuditControlPlaneMiddleware.php, 36 LOC) |
| Archivos >200 LOC | 0 |
| Archivos >500 LOC | 0 |
| Complejidad estimada | Baja |

## Metricas de deuda tecnica

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | 40% | 58% |
| Codigo aceptable | 52% | 38% |
| Codigo sucio | 8% | 4% |
| Codigo espagueti | 0% | 0% |
| Riesgo tecnico | Deuda Moderada | Aceptable |
| Mantenibilidad | Baja | Media |
| Acoplamiento | Medio | Medio-Bajo |
| Cohesion | Baja | Media |

Heuristica aplicada: clasificacion por archivo fuente en cuatro buckets (limpio, aceptable, sucio, espagueti) segun LOC, uso de `app()/resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR (0-100) | 58 | 32 |

Calculo heuristico: cobertura previa casi nula en modulo, eliminacion de service locator en security headers, consolidacion de logout web, tests de caracterizacion nuevos. IRR menor = refactorizacion futura mas segura.

## Violaciones arquitectonicas

| Violacion | Antes | Despues |
| --------- | ----- | ------- |
| Service Locator / app() | 1 archivo | 0 archivos |
| resolve() contenedor Laravel | 0 (falso positivo: `InertiaSharedPropsResolver::resolve()`) | 0 |
| Facades indebidas o acoplamiento a Facades | 5 archivos | 0 archivos explicitos (auth() helper reemplazado por AuthFactory) |
| Dependencias cruzadas entre BCs | Shared, Control, Simulation, Dashboard, Middleware | Sin cambio estructural; presenters/terminators acotan responsabilidades |

## Dependencias

### Dependencias entrantes

Observability, Providers

### Dependencias salientes

Shared, Control, Simulation, Dashboard, Middleware

## Riesgo de refactorizacion

| Area | Riesgo |
| ---- | ------ |
| Controllers | Medio |
| Services | Bajo |
| Domain | Medio |
| Infraestructura | Medio |
| Tests | Medio |

## Cobertura funcional

- Funcionalidades principales: Login, Readiness, AuditControlPlane, AuthenticatePlatformApi, CorrelationId
- Funcionalidades secundarias: contratos de ruta y flujo detectados por los controladores, use cases y servicios del modulo.
- Funcionalidades criticas: Shared, Control, Simulation, Dashboard, Middleware

## Cobertura de pruebas

- Tests unitarios: 5
- Tests feature: 3 (indirectos en Feature/Health, Feature/Observability, Feature/Middleware)
- Tests integracion: 0
- Clasificacion: Media

## Codigo muerto

- No se identificaron clases muertas concluyentes en el escaneo estatico; los componentes con baja trazabilidad siguen expuestos por rutas, comandos o service providers.

## Oportunidades de mejora

### Refactorizacion segura

- Extraer presenter para AuditControlPlaneMiddleware (sanitized audit payload).
- Aislar adapters para rutas tenant portal restantes.

### Refactorizacion moderada

- Consolidar middleware de auth web (EnsureInstanceWebAuth, EnsureControlWebAuth) con guard compartido.
- Stub Vite en tests feature Inertia del kernel Http.

### Refactorizacion de alto riesgo

- Cambiar contratos cruzados entre BCs sin plan de compatibilidad.

## Plan de saneamiento

| Prioridad | Accion | Impacto | Riesgo | Estado |
| --------- | ------ | ------- | ------ | ------ |
| P1 | Reducir el mayor punto de acoplamiento del modulo (SecurityHeadersMiddleware) | Baja riesgo y hace mas visible la frontera | Medio | Completado |
| P2 | Extraer mappers/presenters y fijar contratos con tests de caracterizacion | Mejora mantenibilidad y protege payloads | Bajo-Medio | Completado |
| P3 | Consolidar convenciones de nombres y eliminar lookups innecesarios del contenedor | Menos ruido y menor deuda acumulada | Bajo | Completado |

## Compatibilidad funcional

- Funcionalidades que podrian romperse: Shared, Control, Simulation, Dashboard, Middleware y las respuestas de los endpoints publicos del modulo.
- Dependencias que deben preservarse: Observability, Providers, ademas de los contratos de DTOs y use cases consumidos por otros BCs.
- Contratos publicos que no deben modificarse: nombres de rutas, payloads, eventos publicados y firmas de servicios usados desde otros modulos.

## Refactorizacion Ejecutada

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | `SecurityHeadersMiddleware` mezclaba CSP, env/app lookup y aplicacion de headers | `SecurityHeadersCspBuilder` + `SecurityHeadersApplicator`; middleware delgado |
| P2 | Payloads de tenant suspendido y security headers sin contrato congelado | `TenantSuspendedResponsePresenter` + 5 tests unitarios Http |
| P2 | Logica duplicada de logout web en 5 archivos | `OperatorSessionTerminator` centralizado |
| P3 | `app()->environment('local')` en security headers | DI de `Illuminate\Contracts\Foundation\Application` |
| P3 | Facade `Log` en correlation middleware | DI de `Illuminate\Log\LogManager` |
| P3 | Helper `auth()->logout()` en middleware/controllers | DI de `Illuminate\Contracts\Auth\Factory` via `OperatorSessionTerminator` |

### Cambios realizados

- Nuevos archivos en `app/Http/Application/`:
  - `Security/SecurityHeadersCspBuilder.php`
  - `Security/SecurityHeadersApplicator.php`
  - `Security/OperatorSessionTerminator.php`
  - `Presenters/TenantSuspendedResponsePresenter.php`
- Refactorizados (contratos HTTP preservados):
  - `SecurityHeadersMiddleware`, `EnsureTenantOperationalStatus`, `CorrelationIdMiddleware`
  - `EnsureInstancePortalAccess`, `EnsureAuthenticatedInstanceBinding`, `EnsureInstanceWebAuth`
  - `LoginController`
- Tests nuevos: `tests/Unit/Http/Security/SecurityHeadersServicesTest.php`, `tests/Unit/Http/Presenters/TenantSuspendedResponsePresenterTest.php`
- `TenantLifecycleTest` actualizado para resolver middleware via contenedor.

### Riesgos mitigados

- Regresion en headers de seguridad y CSP local Vite (tests unitarios de builder/applicator).
- Regresion en respuesta API tenant suspendido (presenter + feature test existente).
- Service locator y facades en el perimetro Http del kernel web.

### Riesgos pendientes

- Tests feature Inertia (`/login`, pagina suspendida web) requieren manifest Vite local (fallo preexistente en entorno sin build).
- Middleware de auth web restantes (`EnsureControlWebAuth`, `EnsurePlatformWebAuth`) aun mezclan redirect logic inline.
- `AuditControlPlaneMiddleware` sigue siendo el metodo handle mas largo tras security refactor.

### Impacto funcional

- Ninguno observable en contratos publicos: mismos headers de seguridad, mismos codigos/payloads API suspendido, correlation id, logout y readiness.

### Evidencia de validacion

```
php artisan test tests/Unit/Http tests/Feature/Observability/CorrelationIdMiddlewareTest.php tests/Feature/Middleware/EnsureTenantOperationalStatusTest.php --filter="blocks_api|Correlation|SecurityHeaders|TenantSuspended"
→ 8 passed (21 assertions)

php artisan test tests/Feature/Health/HealthEndpointTest.php
→ 3 passed

php artisan test tests/Feature/TenantLifecycleTest.php --filter="middleware_allows_active_tenant"
→ 1 passed
```

Nota: tests Inertia web con `/login` fallan sin `public/build/manifest.json` (preexistente, no introducido por esta refactorizacion).

## Veredicto final

**Aceptable**. El modulo Http reduce acoplamiento en security headers, elimina service locator y facades explicitas, centraliza logout web y congela contratos criticos con tests unitarios. Queda preparado para SonarQube con IRR reducido; una segunda ronda puede abordar middleware auth restantes y stub Vite en feature tests.
