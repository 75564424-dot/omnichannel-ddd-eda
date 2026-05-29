# Auditoría — Providers (Composition Root)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Providers/` |
| **Namespace** | `App\Providers\` |
| **Tipo** | Composition root Laravel |
| **Archivos PHP** | 16 |
| **LOC aprox.** | 484 |
| **Tests** | 7 (Unit) |

> **Última refactorización:** 2026-05-28 — registrars extraídos, boot order centralizado, unit tests composition root.

## ¿Qué hace?

Registra **service providers** globales: SQLite WAL, bindings de plataforma, simulación, identidad, seguridad, logging, integración event bus. Orquesta el arranque antes de los providers por BC.

## ¿Para qué sirve?

- `ProviderBootManifest` — orden único de boot (fuente de verdad para `bootstrap/providers.php`).
- `AppServiceProvider` — registra BC core (Dashboard, Middleware, Integration) + SQLite tuning.
- `PlatformServiceProvider` — tenant context, fleet local, servicios bootstrap.
- `SimulationServiceProvider` — bindings transversales de simulación (hasta BC formal).
- `IdentityServiceProvider` / `SecurityServiceProvider` — gates, auth API, rate limits.
- `EventBusIntegrationServiceProvider` — merge de packs en `eventbus.subscriptions`.

**Nota:** cada BC también tiene provider propio (`MiddlewareServiceProvider`, etc.) en `app/{BC}/Interfaces/Providers/`.

## Estructura (post-refactor)

```text
app/Providers/
├── ProviderBootManifest.php          orden boot (SSOT)
├── Registrars/
│   ├── BoundedContextProviderRegistrar
│   ├── PlatformServiceBindingsRegistrar
│   ├── LocalFleetBindingsRegistrar
│   ├── SimulationServiceBindingsRegistrar
│   ├── PlatformGateRegistrar
│   ├── PlatformRateLimitConfigurator
│   ├── SqliteConcurrencyConfigurator
│   └── EventBusPackSubscriptionBootstrapper
├── AppServiceProvider.php
├── PlatformServiceProvider.php
├── SimulationServiceProvider.php
├── IdentityServiceProvider.php
├── SecurityServiceProvider.php
├── LoggingServiceProvider.php
└── EventBusIntegrationServiceProvider.php

bootstrap/providers.php  →  ProviderBootManifest::providers()
```

| Provider | LOC ~ | Rol |
|----------|-------|-----|
| `AppServiceProvider` | ~25 | BC registrar + SQLite + cookies |
| `PlatformServiceProvider` | ~25 | Config platform + boot log context |
| `SimulationServiceProvider` | ~12 | Delega a `SimulationServiceBindingsRegistrar` |
| `IdentityServiceProvider` | ~18 | Auth service + gates |
| `SecurityServiceProvider` | ~51 | Auth bindings + rate limits + middleware aliases |
| `LoggingServiceProvider` | ~33 | Structured logging bindings |
| `EventBusIntegrationServiceProvider` | ~17 | Pack merge boot |

## Servicios extraídos en esta refactorización

| Registrar / manifest | Reemplaza lógica en |
|----------------------|---------------------|
| `ProviderBootManifest` | Lista duplicada en `bootstrap/providers.php` |
| `BoundedContextProviderRegistrar` | Array + loop en `AppServiceProvider` |
| `SqliteConcurrencyConfigurator` | `configureSqliteConcurrency()` privado |
| `PlatformServiceBindingsRegistrar` | Singletons platform en `PlatformServiceProvider` |
| `LocalFleetBindingsRegistrar` | Fleet factories en `PlatformServiceProvider` |
| `SimulationServiceBindingsRegistrar` | 16 singletons inline |
| `PlatformGateRegistrar` | Gate definitions en `IdentityServiceProvider` |
| `PlatformRateLimitConfigurator` | Rate limiters en `SecurityServiceProvider` |
| `EventBusPackSubscriptionBootstrapper` | Boot body en `EventBusIntegrationServiceProvider` |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 15% | **7%** | Providers delgados; registrars testeables |
| **% código espagueti** | 10% | **5%** | Boot order SSOT; bindings por concern |
| **Ratio tests/archivos** | 0% | **~44%** | +7 unit tests (manifest, gates, simulation, eventbus) |
| **Archivos >150 LOC** | 0 | **0** | Mayor: `SimulationServiceBindingsRegistrar` ~51 LOC |

## Resuelto en esta refactorización

1. ~~Boot order en dos lugares~~ → `ProviderBootManifest` + `bootstrap/providers.php` delegado.
2. ~~`PlatformServiceProvider` denso~~ → platform + local fleet registrars.
3. ~~`SimulationServiceProvider` lista larga~~ → registrar con inventario testeable.
4. ~~Sin tests composition root~~ → 7 unit tests.

## Cosas sueltas / inconsistentes (restantes)

1. **Dos niveles de providers** — `app/Providers` vs `app/{BC}/Interfaces/Providers` — convención correcta; Control aún sin provider propio.
2. **`SimulationServiceBindingsRegistrar`** — indica que Simulation no es BC formal; migrar cuando exista `app/Simulation/`.
3. **Middleware aliases** — repartidos entre Security, Logging, Observability, Api providers.
4. **`LoggingServiceProvider::middlewareAliases()`** — duplica alias `platform.correlation.id` con Observability.

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| Control | Simulation + fleet bindings | ⚠️ Medio (transversal) |
| Shared | Platform, Identity, Security, EventBus | ✅ Esperado en composition root |
| BC Interfaces | Dashboard, Middleware, Integration | ✅ Registro explícito |

## Cobertura de tests

- **Verificado (2026-05-28):** 7 tests Unit Providers — todos pasan.
- **Presente:** boot manifest, BC registrar, simulation bindings resolve, gates auth-off, sqlite config, eventbus noop.
- **Gaps:** rate limit configurator, local fleet factory wiring, integration boot completo end-to-end.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P3 | Tras BC Simulation formal, mover `SimulationServiceBindingsRegistrar` a `app/Simulation/`. |
| P3 | Unificar middleware aliases en un `PlatformMiddlewareAliasManifest`. |
| P4 | `ControlServiceProvider` para bindings Control fuera de Platform/Simulation. |

## Veredicto

**Composition root sano** tras refactor: providers delgados, registrars por concern, boot order documentado en código. Deuda restante ligada a Simulation transversal y aliases middleware dispersos.
