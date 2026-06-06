# Informe Fase 7 — Corrección Elegibilidad Simulación

## Estado
**Cumple**

## Objetivo
Impedir que empresas sin módulos configurados explícitamente participen en simulaciones, distinguiendo catálogo persistido vs fallbacks (blueprint, fixture default `acmepos`), y rechazando POST aunque el frontend sea manipulado.

## Evidencia encontrada

### Hallazgo heredado (diagnóstico Runbook / Fase 3)
`SimulationTenantEligibilityChecker` delegaba a `SimulationFixtureResolver::hasSimulationSource()`, que retornaba `true` si existía el fixture default `acmepos`, aunque el tenant no tuviera `settings.modules_catalog`. Clasificación: **Bug real** (lógica permisiva), no legacy.

### Componentes analizados
| Componente | Rol previo | Problema |
|---|---|---|
| `SimulationTenantEligibilityChecker` | Valida status + `hasSimulationSource()` | Aceptaba fixture default |
| `SimulationFixtureResolver::hasSimulationSource()` | true si fixture existe | Ignora explicititud de catálogo |
| `TenantModuleCatalogService::getCatalog()` | Fallback a blueprint/fixture | UI ok; elegibilidad no debe usar fallback |
| `CompanyListingService` | Expone `can_simulate` / `simulate_block_reason` | Heredaba checker permisivo |
| `SimulationRunOrchestrator::start()` | Rechaza vía `simulationBlockReason()` | Backend ya tenía hook; mensaje incorrecto |
| `resources/js/Pages/Control/Companies/Index.vue` | Deshabilita tenant si `!can_simulate` | UI correcta; dependía del checker |

### Tenants de certificación (`tenant-test-*` post-limpieza)
| Slug | Estado pre-corrección | Estado post-corrección |
|---|---|---|
| `tenant-test-branding` | Simulable (fixture default) | **No simulable** — sin `modules_catalog` |
| `tenant-test-routing` | Simulable (fixture default) | **No simulable** — sin `modules_catalog` |
| `tenant-test-simulation` | Simulable (fixture default) | **No simulable** sin catálogo; **simulable** tras `saveCatalog` explícito |

### Evidencia HTTP / runtime (CP `:8000`)
```
tenant-test-branding can_simulate=false
  reason=No hay catálogo de módulos configurado explícitamente para esta empresa.

tenant-test-simulation can_simulate=false (antes de configurar módulos)
  reason=No hay catálogo de módulos configurado explícitamente para esta empresa.

tenant-test-simulation can_simulate=true (tras saveCatalog con productor + event_types_emitted)
  reason=null

CompanyListingService listing:
  tenant-test-branding → false
  tenant-test-routing → false
  tenant-test-simulation → true (con catálogo explícito)
```

### Pruebas automatizadas
```
Tests\Unit\Simulation\SimulationTenantEligibilityCheckerTest — 5 passed
  ✓ sin modules_catalog
  ✓ solo middleware (sin productores)
  ✓ productor sin event_types_emitted
  ✓ catálogo explícito con eventos → elegible
  ✓ fixture default no hace simulable

Tests\Feature\Control\CompanySimulationAutomationTest — 4 passed
  ✓ POST rechazado sin catálogo explícito (0 simulation_runs)
  ✓ index marca can_simulate=false sin catálogo
  ✓ simulate_block_reason en Inertia
  ✓ flujo feliz con modules_catalog explícito sigue operativo
```

## Cambios realizados
1. **`TenantModuleCatalogService::storedCatalog()`** — nuevo método que devuelve solo `settings.modules_catalog` persistido (sin fallback a blueprint, archivo de instancia ni fixture versionado).
2. **`SimulationTenantEligibilityChecker`** — reescrito para validar:
   - `tenant.status === active`
   - `storedCatalog` no nulo
   - al menos un productor en catálogo explícito
   - al menos un `event_types_emitted` no vacío (vía `TenantCatalogSampleEventBuilder`)
   - silo moderno (`local_instance.app_url` + registro en flota) cuando `LocalFleetInstanceProvisioner` está habilitado
   - restricción de silo dedicado en procesos no-CP (sin cambio)
3. **Eliminada dependencia de `SimulationFixtureResolver`** en el checker (fixture default ya no influye en elegibilidad).
4. **Tests** — nueva suite unitaria + actualización de tests de feature que asumían elegibilidad permisiva.

## Archivos modificados
- `app/Control/Application/Services/Tenants/TenantModuleCatalogService.php`
- `app/Simulation/Application/Services/Execution/SimulationTenantEligibilityChecker.php`
- `tests/Feature/Control/CompanySimulationAutomationTest.php`

## Archivos nuevos
- `tests/Unit/Simulation/SimulationTenantEligibilityCheckerTest.php`
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_7.md`

## Riesgos detectados
- **`getCatalog()` sigue con fallbacks** para UI/dashboard; solo elegibilidad usa `storedCatalog()`. Riesgo de confusión si otro código usa `getCatalog()` para decisiones de simulación.
- **`TenantSimulationAutomationService::run()`** aún puede resolver templates vía fixture en ejecución; mitigado porque `SimulationRunOrchestrator::start()` bloquea antes.
- **Configuración de módulos en `tenant-test-simulation`** realizada para demostrar criterio de aceptación (catálogo explícito → simulable); permanece como dato de prueba hasta Fase 9.

## Riesgos mitigados
- Empresas sin módulos ya no aparecen como simulables en `/control/companies`.
- POST manipulado a `/control/companies/simulation` rechazado con error de sesión (sin crear `simulation_runs`).
- Fixture `acmepos` no sustituye ausencia de catálogo explícito.

## Hallazgos clasificados

### Legacy
- Ninguno. El fallo se reproducía en `tenant-test-*` creados post-Fase 1/2.

### Bug Real (corregido)
1. **`hasSimulationSource()` trataba fixture default como fuente válida** → tenants sin `modules_catalog` eran simulables.
2. **`getCatalog()` fallback** mezclado con decisión de elegibilidad.

### Configuración
- Ninguna pendiente para elegibilidad.

### Operativo
- Ninguno bloqueante en esta fase.

## Control de Alcance — Trabajo perteneciente a otras fases
| Trabajo fuera de alcance | Fase | Impacto |
|---|---|---|
| Ejecución E2E completa de simulación con reporte | Fase 8 | No evaluado aquí |
| Limpieza de `tenant-test-simulation` modules_catalog | Fase 9 | Dato de prueba temporal |
| Friendly routing | Fase 6 | Sin regresión (`/up` 200) |

## Checklist del Runbook
| Requisito (Fase 7) | Estado | Evidencia |
|---|---|---|
| Checker distingue catálogo explícito vs fallback | Cumple | `storedCatalog()` + checker sin `hasSimulationSource` |
| UI muestra motivo de bloqueo | Cumple | `simulate_block_reason` en Inertia; `Index.vue` líneas 143–155 |
| Backend rechaza POST sin elegibilidad | Cumple | `CompanySimulationAutomationTest` POST rechazado |
| Pruebas: sin módulos, solo middleware, producer sin eventos, elegible | Cumple | `SimulationTenantEligibilityCheckerTest` (5 casos) |
| `tenant-test-branding` sin módulos → no simulable | Cumple | HTTP/runtime validation |
| `tenant-test-simulation` sin módulos → no simulable | Cumple | HTTP/runtime validation |
| `tenant-test-simulation` con módulos explícitos → simulable | Cumple | `saveCatalog` + `can_simulate=true` |
| Simulación no arranca por fixture default sin módulos | Cumple | Checker + POST rechazado; 0 runs |
| **CA**: Criterios de aceptación Runbook | Cumple | Matriz anterior |

## Compatibilidad Retroactiva
- **Lifecycle sigue funcionando**: `/up` 200 en CP y silos; sin cambios en lifecycle.
- **Provisioning sigue funcionando**: flujo intacto; nuevos tenants sin catálogo explícito no son simulables hasta configurar módulos (comportamiento deseado).
- **Routing sigue funcionando**: sin cambios en ADR-011; CP operativo.
- **Simulación sigue funcionando**: flujo feliz con `modules_catalog` explícito pasa tests (32s E2E en feature test).
- **Control Plane sigue funcionando**: `/control/companies` operativo con props de elegibilidad corregidas.

## Conclusión
La Fase 7 corrige la elegibilidad permisiva demostrada en el diagnóstico del Runbook. Los `tenant-test-*` sin catálogo explícito quedan bloqueados; `tenant-test-simulation` con catálogo configurado vía `saveCatalog` es elegible. El fixture default `acmepos` ya no habilita simulación por sí solo. **Estado = Cumple**.

No se avanza automáticamente a la Fase 8; se espera nueva instrucción.
