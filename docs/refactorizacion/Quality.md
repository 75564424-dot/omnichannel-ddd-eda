# Auditoría — Quality (Gates / CI)

| Campo | Valor |
|-------|-------|
| **Ruta** | `app/Quality/` |
| **Namespace** | `App\Quality\` |
| **Tipo** | Bounded Context operacional (QA gates) |
| **Archivos PHP** | 7 |
| **LOC aprox.** | 252 |
| **Tests** | 7 (Unit 5 · Feature 2) |

> **Última refactorización:** 2026-05-28 — gate cobertura Application, settings tipados, command CI, script delegado.

## ¿Qué hace?

Centraliza **umbrales y gates de calidad** de la plataforma: cobertura mínima en capas Application, parámetros load test / UI E2E / security scan (config), y comando Artisan enlazado a CI.

## ¿Para qué sirve?

- `platform:quality-coverage` — verifica cobertura Application contra `platform_quality.coverage`.
- `scripts/ci/check-application-coverage.php` — wrapper CI que delega al command.
- Config `platform_quality.php` — load test k6, UI E2E Playwright, ZAP baseline (workflows `.github/workflows/quality-*.yml`).
- Punto de extensión para futuros gates (static analysis, mutation testing).

## Estructura DDD (post-refactor)

```text
app/Quality/
├── Domain/ValueObjects/           CoverageGateResult
├── Application/Services/
│   ├── QualitySettings            lectura tipada platform_quality
│   ├── Coverage/
│   │   ├── ApplicationCoverageCalculator
│   │   └── ApplicationCoverageGateService
│   └── QualityCoverageConsoleReporter
└── Interfaces/
    ├── Commands/                  CheckApplicationCoverageCommand
    └── Providers/                 QualityServiceProvider
```

| Capa | Archivos | Estado |
|------|----------|--------|
| Domain | 1 | ✅ VO resultado gate |
| Application | 4 | ✅ Calculator + gate + settings + reporter |
| Interfaces | 2 | ✅ Command + provider |

## Servicios extraídos en esta refactorización

| Servicio | Reemplaza lógica en |
|----------|---------------------|
| `QualitySettings` | Lectura dispersa de `platform_quality` |
| `ApplicationCoverageCalculator` | Parser clover en `scripts/ci/check-application-coverage.php` |
| `ApplicationCoverageGateService` | Gate threshold + evaluación |
| `QualityCoverageConsoleReporter` | Output CLI del script CI |
| `CheckApplicationCoverageCommand` | Invocación manual / CI vía Artisan |

## Métricas de deuda (actualizadas)

| Indicador | Antes | **Ahora** | Detalle |
|-----------|-------|-----------|---------|
| **% código sucio** | 5% | **6%** | Módulo pequeño; matcher clover podría extraerse |
| **% código espagueti** | 5% | **4%** | Script CI → command; config tipada |
| **Ratio tests/archivos** | 0% | **100%** | +7 tests (calculator, gate, settings, command) |
| **Archivos >150 LOC** | 0 | **0** | Mayor: `ApplicationCoverageCalculator` ~61 LOC |
| **Implementación real** | ❌ stub | **✅** | Gate cobertura operativo |

## Resuelto en esta refactorización

1. ~~Módulo fantasma (solo merge config)~~ → gate cobertura Application con command.
2. ~~Lógica CI en script suelto~~ → servicios testeables + script delgado.
3. ~~Sin tests~~ → 7 unit/feature con fixture clover.
4. ~~Config sin dueño en código~~ → `QualitySettings` centralizado.

## Cosas sueltas / inconsistentes (restantes)

1. **Load test / UI E2E / ZAP** — config en `platform_quality.php` pero ejecución sigue en workflows bash/k6/Playwright (no integrados al BC aún).
2. **Prefijos Application** — lista hardcodeada en `QualitySettings`; ampliar al refactorizar Control/Shared.
3. **Matcher clover** — paths case-insensitive; validar con clover real de CI pcov.

## Acoplamientos

| Hacia | Tipo | Riesgo |
|-------|------|--------|
| CI / scripts | Wrapper → Artisan command | ✅ Bajo |
| BC Application layers | Solo lectura clover (sin imports) | ✅ Bajo |
| Config | `platform_quality.php` | ✅ Bajo |

## Cobertura de tests

- **Verificado (2026-05-28):** 7 tests Unit + Feature Quality — todos pasan.
- **Presente:** calculator, gate pass/fail, settings, command + JSON output.
- **Gaps:** integration con clover real de CI, gates load/e2e cuando se integren al BC.

## Recomendaciones de refactor (futuro)

| Prioridad | Acción |
|-----------|--------|
| P3 | Command `platform:quality-check` unificado (coverage + smoke config validation). |
| P4 | Integrar lectura load_test/ui_e2e settings en scripts CI existentes. |
| P4 | Ampliar prefijos Application cuando Control/Shared expongan servicios testeables. |

## Veredicto

**BC operativo mínimo** tras refactor: ya no es placeholder — gate de cobertura Application enlazado a CI con tests. Deuda restante en integrar otros gates QA (load, e2e) al mismo BC.
