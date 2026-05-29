# Auditoría — Shared (Kernel transversal)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Shared/` |
| **Namespace** | `App\Shared\` |
| **Tipo** | Shared Kernel (no es un BC de producto) |
| **Archivos PHP** | 53 |
| **LOC aprox.** | 2 970 |
| **Tests** | ~14 (dispersos: Platform, Identity, Security, Api, EventBus) |

## ¿Qué hace?

Concentra código **reutilizado entre bounded contexts**: identidad y roles, autenticación API, contratos del event bus, despliegue de instancias, fleet local, simulación cliente, logging/auditoría, rutas API compartidas, modelos Eloquent transversales (`TenantModel`) y utilidades de persistencia.

## ¿Para qué sirve?

- Evitar duplicación de ports/contratos entre Middleware, Dashboard, Control, Integration.
- Bootstrap multi-instancia (control-plane vs client silos).
- Seguridad transversal (API tokens, policies, audit log).

## Sub-áreas

| Subcarpeta | LOC ~ | Rol | Deuda |
|------------|-------|-----|-------|
| `Platform/` | ~1 400 | Instancias, fleet, simulación, catálogo | Alta |
| `Identity/` | ~400 | Roles, login, policies | Baja |
| `Security/` | ~350 | API auth, audit | Media |
| `Api/` | ~450 | Route registrars, ProblemDetails, idempotency | Baja |
| `EventBus/` | ~250 | Contratos + catalog merger | Media |
| `Logging/` | ~200 | Structured log, audit | Baja |
| `Persistence/` | ~150 | Mappers status | Baja |
| `Infrastructure/` | ~200 | Models compartidos | Media |
| `Contracts/` | ~100 | Interfaces cross-BC | Baja |

## Métricas de deuda

| Indicador | Valor | Detalle |
|-----------|-------|---------|
| **% código sucio** | **32%** | God classes en Platform/EventBus |
| **% código espagueti** | **28%** | Catch-all; Simulation mezclada |
| **Ratio tests/archivos** | ~26% | No hay carpeta `tests/Shared/` |
| **Archivos >150 LOC** | 6 | Ver abajo |

### Archivos más pesados

| Archivo | LOC | Problema |
|---------|-----|----------|
| `LocalFleetInstanceProvisioner.php` | 224 | Provision + env + DB |
| `ClientSimulationService.php` | 201 | Simulación fuera de BC Simulation |
| `PackSubscriptionCatalogMerger.php` | 170 | Lógica compleja de merge packs |
| `InstanceDeploymentService.php` | 155 | Deploy + validación |
| `PlatformApiAuthenticator.php` | 135 | Auth multi-modo |
| `ClientFixtureLoader.php` | 134 | Fixtures demo |

## Cosas sueltas / inconsistentes

1. **Riesgo "god module"** — cada nueva feature transversal tiende a aterrizar aquí.
2. **Platform ≠ Control** — provisioning de instancias en Shared mientras tenants viven en Control.
3. **`ClientSimulationService`** — duplica/confunde con Control Simulation services.
4. **`TenantModel` en Shared** — agregado de tenant sin domain layer; usado directamente desde Http.
5. **Identity vs Providers** — `IdentityServiceProvider` en `app/Providers/` pero código en Shared/Identity.
6. **Mappers de status** — `MessageQueueStatusMapper` en Shared pero usado por Middleware y Vue duplica reglas.
7. **LocalFleet\*** — mucha lógica específica de dev local en kernel compartido (debería ser adapter infra).

## Acoplamientos

Shared es **hub central** — por diseño muchos BC dependen de él. Riesgo: cambios aquí rompen todo.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| **P1** | Sacar Simulation de Platform a módulo Simulation. |
| **P2** | Mover LocalFleet a `Infrastructure/LocalDev` o paquete opcional. |
| **P2** | Domain mínimo para `Tenant` (aunque viva compartido). |
| P3 | Split Shared en paquetes lógicos documentados (no carpetas planas). |
| P3 | Tests dedicados `tests/Unit/Shared/` por sub-área. |
| P4 | Reducir `PackSubscriptionCatalogMerger` con tests de tabla. |

## Veredicto

**Kernel necesario pero sobrecargado.** Refactor prioritario en Platform/Simulation; resto estable.
