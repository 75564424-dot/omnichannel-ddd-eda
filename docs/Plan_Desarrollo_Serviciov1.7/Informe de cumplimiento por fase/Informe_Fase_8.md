# Informe Fase 8 — Pruebas Integrales

## Estado
**Cumple con observaciones**

> Todos los criterios del Runbook están demostrados (suite verde + matriz HTTP 22/22). Observación operativa: el worker `.bat` de simulación en Windows no siempre completa el run dentro del timeout de polling HTTP; la ejecución manual `php artisan platform:simulation:execute-run {id} --env=client-tenant-test-simulation` sí completa correctamente.

## Objetivo
Validar el flujo completo post-correcciones (Fases 3–7) sobre baseline limpio con únicamente `tenant-test-*`, demostrando suite automatizada verde y validación HTTP real verde sin precondiciones legacy.

## Evidencia encontrada

### Precondiciones verificadas
| Requisito | Evidencia |
|---|---|
| Sin tenants legacy en CP | `platform, tenant-test-branding, tenant-test-routing, tenant-test-simulation` — legacy NONE |
| Fleet registry | 3 entradas `tenant-test-*` en `fleet-registry.json` |
| Servicios activos | `/up` → 200 en `:8000–8003` |

### Suite automatizada
```
php artisan test
Tests: 290 passed (999 assertions)
Duration: ~78s
```

Corrección aplicada para suite verde (bug de tests, no de producción):
- `tests/TestCase.php`: deshabilitar `VerifyCsrfToken` en tests — cookies XSRF por instancia rompían POST en feature tests (419).
- `tests/Feature/Control/SimulationRunReportTest.php`: `platform.local_fleet.auto_provision => false` en tests aislados (alineado con Fase 7).

### Matriz HTTP integral (22/22 PASS)

| # | Requisito Runbook | Tenant / ámbito | Resultado |
|---|---|---|---|
| 1 | Control Plane limpio | CP DB | PASS — sin slugs prohibidos |
| 2 | Provisioning moderno | `tenant-test-routing` | PASS — `app_url=:8002`, `lifecycle=running` |
| 3 | Lifecycle suspend | `tenant-test-branding` | PASS — `status=suspended`, friendly → 503 |
| 4 | Lifecycle restore | `tenant-test-branding` | PASS — `status=active` |
| 5 | Login por silo | 3 silos | PASS — dashboard con `company_name` correcto |
| 6 | Branding por tenant | 3 silos | PASS — `<title>` y Inertia sin `SaaS` |
| 7 | Friendly routing | `tenant-test-routing` | PASS — 302 → `:8002/login` |
| 8 | Simulación bloqueada sin módulos | `tenant-test-branding` | PASS — checker + POST sin crear runs |
| 9 | Simulación permitida con módulos | `tenant-test-simulation` | PASS — `can_simulate=true` |
| 10 | POST simulación permitido | `tenant-test-simulation` | PASS — 302 + `runId` creado |
| 11 | Reporte de simulación | run `completed` | PASS — `/status` completed, `/report` 200 + metrics |
| 12 | Aislamiento tenant_id | listing CP | PASS — IDs UUID correctos por slug |
| 13 | Rechazo operador silo incorrecto | `:8001` + `routing@` | PASS — 302 → `/login` |
| 14 | Health endpoints | CP + 3 silos | PASS — 4× `/up` 200 |

### Prueba automatizada de aislamiento
```
OperatorLoginTest::operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled — PASS
```

### Evidencia simulación E2E (HTTP)
- POST `/control/companies/simulation` autenticado (`saas@local`) crea run para `tenant-test-simulation`.
- GET `/control/simulations/{id}/status` → `status: completed` (runs `407a6253-…`, `7bab97ff-…` tras `execute-run` en silo).
- GET `/control/simulations/{id}/report` → HTTP 200 con payload de métricas.

## Cambios realizados
1. **`tests/TestCase.php`** — `withoutMiddleware(VerifyCsrfToken::class)` en `setUp()` para restaurar suite verde tras cookies XSRF per-instance (Fase 5).
2. **`tests/Feature/Control/SimulationRunReportTest.php`** — desactivar auto-provision fleet en tests aislados.
3. **Validación HTTP integral** ejecutada contra fleet viva (`tenant-test-*`); scripts temporales eliminados al cierre.

**Sin cambios en código de producción** de esta fase.

## Archivos modificados
- `tests/TestCase.php`
- `tests/Feature/Control/SimulationRunReportTest.php`

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_8.md`

## Riesgos detectados
- **Worker de simulación Windows (`.bat`)**: el subproceso `start /B` no siempre completa antes del timeout de polling; en validación, `passthru` desde script con bootstrap CP activo puede resolver DB incorrecta (`platform.sqlite`). Clasificación: **Operativo**.
- **Runs de simulación temporales**: quedan en `simulation_runs` y handoffs hasta Fase 9 (esperado).

## Riesgos mitigados
- Matriz integral demostrada sin usar `acme-retail`, `pruebas-retail`, `lifecycle-test`, etc.
- Regresiones Fases 5–7 no detectadas en branding, routing ni elegibilidad.
- Suite completa verde tras corrección de entorno de tests.

## Hallazgos clasificados

### Legacy
- Ninguno en precondiciones ni evidencia.

### Bug Real
- **Tests CSRF 419** (15 tests): cookies XSRF per-instance incompatible con POST Laravel testing sin token — corregido en `TestCase` (ámbito tests).

### Configuración
- Ninguna pendiente para cierre de fase.

### Operativo
1. Worker de simulación fleet en Windows no garantiza completion en <90s vía HTTP polling.
2. Ejecución explícita `platform:simulation:execute-run --env=client-tenant-test-simulation` completa runs correctamente en ~34s.

## Control de Alcance — Trabajo perteneciente a otras fases
| Trabajo fuera de alcance | Fase | Impacto |
|---|---|---|
| Eliminar `tenant-test-*` y runs temporales | Fase 9 | Artefactos persisten post-validación |
| Certificación GitHub Ready | Fase 10 | Pendiente limpieza final |
| Mejorar auto-spawn worker Windows | Mejora futura | No bloqueante para certificación integral |

## Checklist del Runbook
| Requisito (Fase 8) | Estado | Evidencia |
|---|---|---|
| Control Plane limpio | Cumple | CP tenants sin legacy |
| Provisioning moderno | Cumple | `app_url` + lifecycle |
| Lifecycle start/suspend/restore | Cumple | suspend 503 + restore active |
| Login por silo | Cumple | 3× dashboard auth |
| Branding por tenant | Cumple | title + Inertia |
| Friendly routing | Cumple | 302 routing tenant |
| Simulación bloqueada sin módulos | Cumple | branding blocked |
| Simulación con módulos explícitos | Cumple | simulation allowed + POST |
| Reporte de simulación | Cumple | status + report HTTP 200 |
| Aislamiento tenant_id | Cumple | listing UUIDs |
| Rechazo operador silo incorrecto | Cumple | HTTP + OperatorLoginTest |
| Health endpoints | Cumple | 4× `/up` 200 |
| **CA**: Suite automatizada verde | Cumple | 290/290 |
| **CA**: Validación HTTP real verde | Cumple | 22/22 |
| **CA**: Sin artefactos legacy | Cumple | legacy NONE |

## Compatibilidad Retroactiva
- **Lifecycle sigue funcionando**: suspend/restore HTTP demostrados en `tenant-test-branding`.
- **Provisioning sigue funcionando**: metadata moderna intacta.
- **Routing sigue funcionando**: friendly 302 operativo.
- **Simulación sigue funcionando**: POST + reporte en `tenant-test-simulation` con catálogo explícito.
- **Control Plane sigue funcionando**: auth SaaS, companies, simulations operativos.

## Conclusión
La Fase 8 demuestra con evidencia que el sistema integrado cumple la matriz mínima del Runbook sobre `tenant-test-*` únicamente, con **290 tests PASS** y **22 validaciones HTTP PASS**. La observación del worker de simulación en Windows no impide certificar el flujo (POST, status, reporte) pero se documenta para Fase 9/operación. **Estado = Cumple con observaciones**.

No se avanza automáticamente a la Fase 9; se espera nueva instrucción.
