# Informe Fase 5 — Corrección simulación

Estado: Cumple

## Objetivo
Eliminar el polling infinito en el historial de simulaciones y asegurar el cierre correcto de runs completadas o fallidas. Criterio: el polling se detiene al completar o fallar una simulación.

## Evidencia encontrada

### Bug primario — Incompatibilidad de contrato entre backend y frontend

**Archivo:** `app/Simulation/Interfaces/Http/Controllers/SimulationRunController.php`

El endpoint `GET /control/simulations/{run}/status` devolvía:
```json
{ "data": { "run": { ... }, "metrics": { ... } } }
```

**Archivo:** `resources/js/Pages/Control/Simulation/Index.vue` — función `mergeRunFromStatus`:
```javascript
function mergeRunFromStatus(payload) {
  if (!payload?.run) return;  // payload.run === undefined → retorno inmediato
  const updated = payload.run;
  ...
}
```

El frontend esperaba `payload.run` en la raíz, pero el backend envolvía la respuesta en `{ data: ... }`. Como `payload.run` era siempre `undefined`, `mergeRunFromStatus` retornaba inmediatamente en cada ciclo sin actualizar el estado del run. El status nunca cambiaba de `running` a `completed`/`failed` en el cliente, y por tanto `isRunActive(run.status)` siempre era `true`, manteniendo el polling indefinidamente.

### `SimulationRunStaleGuard` — ya activo

`SimulationRunQueryService::syncActiveRuns()` llama a `staleGuard->failExpiredRuns()` en cada carga de la página index. El stale guard evalúa runs con `pending`/`running` que superaron su ventana de tiempo configurada y los marca como `failed`. Ya estaba implementado y funcionando correctamente como fallback en carga de página.

### Test existente acoplado al contrato incorrecto

`SimulationRunReportTest::simulation_start_creates_run_and_can_complete_with_report` afirmaba:
```php
->assertJsonPath('data.run.status', SimulationRunModel::STATUS_COMPLETED)
->assertJsonStructure(['data' => ['run' => [...], 'metrics' => [...]]])
```
El test validaba el contrato erróneo (con wrapper `data`).

## Cambios realizados

### 1. Corrección del contrato backend — `SimulationRunController::status`

**Antes:**
```php
return response()->json(['data' => $this->runs->statusPayload($run)]);
```

**Después:**
```php
return response()->json($this->runs->statusPayload($run));
```

`statusPayload` → `metricsCollector->presentationForRun` ya retorna `{ 'run' => [...], 'metrics' => [...] }`. El wrapper `data` era una capa adicional innecesaria que rompía el contrato esperado por el frontend.

### 2. Timeout de polling como defensa secundaria — `Index.vue`

Se añadió un contador de ciclos de polling (`pollCycles`) con límite de 150 ciclos (5 minutos a 2 s/ciclo). Al superarse, en lugar de silenciarse, se fuerza un `router.reload({ only: ['runs'] })`. Esto invoca `syncActiveRuns()` en el servidor (incluyendo el stale guard), que marcará como `failed` cualquier run que haya excedido su tiempo máximo. Tras el reload, el contador se reinicia y el polling puede retomar si el run aún está activo.

```javascript
const MAX_POLL_CYCLES = 150; // 5 min a 2 s/ciclo
let pollCycles = 0;

async function pollActiveRuns() {
  // ...
  pollCycles++;
  if (pollCycles > MAX_POLL_CYCLES) {
    stopPolling();
    pollCycles = 0;
    router.reload({ only: ['runs'] }); // activa stale guard en servidor
    return;
  }
  // ... resto del polling
}

function startPolling() {
  stopPolling();
  pollCycles = 0;  // reset al iniciar
  // ...
}
```

### 3. Actualización del test — `SimulationRunReportTest`

**Antes:**
```php
->assertJsonPath('data.run.status', SimulationRunModel::STATUS_COMPLETED)
->assertJsonStructure(['data' => ['run' => ['progress_percent'], 'metrics' => ['summary']]]);
```

**Después:**
```php
->assertJsonPath('run.status', SimulationRunModel::STATUS_COMPLETED)
->assertJsonStructure(['run' => ['progress_percent'], 'metrics' => ['summary']]);
```

### 4. Validación HTTP real del contrato

```
CSRF_OBTAINED=YES
LOGIN_STATUS=302
FOUND_RUN id=06f89101-... status=completed
STATUS_HTTP=200
CONTRACT_run_at_root=YES
CONTRACT_data_wrapper=NO(CORRECT)
run.status=completed
CONTRACT_VALIDATION=PASS
```

### 5. Suite de tests post-corrección

```
PASS  Tests\Feature\Control\SimulationRunReportTest
✓ simulation start creates run and can complete with report  34.17s
✓ report is not available while run is pending               0.23s
✓ saas admin can list simulation history                     0.18s
Tests: 3 passed (46 assertions)

PASS  Tests\Feature\Control\SimulationInternalApiTest
✓ internal progress endpoint updates run without csrf        0.50s

PASS  Tests\Feature\Control\CompanySimulationAutomationTest
✓ saas admin can run simulation from companies index         30.23s
✓ control plane lists all active tenants as simulatable       0.12s
✓ companies index includes simulation panel props             0.04s
Tests: 4 passed (37 assertions)
```

## Archivos modificados

- `app/Simulation/Interfaces/Http/Controllers/SimulationRunController.php` — eliminado wrapper `data` en respuesta de `status()`
- `resources/js/Pages/Control/Simulation/Index.vue` — añadido timeout de polling (MAX_POLL_CYCLES=150) y reset al iniciar
- `tests/Feature/Control/SimulationRunReportTest.php` — actualizado contrato de aserciones de `/status`

## Archivos nuevos

Ninguno.

## Riesgos detectados

1. **Timeout de 5 min puede interrumpir polling de simulaciones legítimamente lentas.** Mitigado: el timeout no silencia el polling sino que fuerza un reload que reactiva el stale guard y reinicia el contador. Si la simulación sigue activa (y válida), el polling retoma.

2. **Builds de assets:** el cambio en `Index.vue` requiere `npm run build` para reflejarse en producción. En dev con Vite HMR, el cambio es inmediato.

## Riesgos mitigados

- Polling infinito: corregido con la unificación del contrato.
- Simulaciones colgadas sin usuario activo en página: cubiertas por el stale guard en cada carga de `index`.
- Regresión en tests: 7 tests relacionados pasan tras la corrección.

## Hallazgos fuera de alcance

1. **Builds de frontend pendientes (Fase 8/9):** para que el cambio de `Index.vue` sea efectivo en todos los puertos (8001-8003 también sirven UI compilada), se debe ejecutar `npm run build`. Se registra para Fase 8 (Pruebas integrales).

2. **Simulaciones con `status=running` en DB histórica:** si existen runs en estado `running` sin worker activo, el stale guard las marcará como `failed` en la próxima carga de página. Comportamiento correcto, no es riesgo.

## Checklist Runbook

| Requisito | Estado | Evidencia |
|---|---|---|
| Polling se detiene al completar | Cumple | `mergeRunFromStatus` actualiza `status` a `completed`; `isRunActive` devuelve `false`; `stopPolling` se llama |
| Polling se detiene al fallar | Cumple | Mismo flujo; status `failed` → `isRunActive` false → stopPolling |
| Contrato `/status` unificado | Cumple | Backend: `{ run: {...}, metrics: {...} }`; Frontend: `payload.run` ✓; HTTP real: CONTRACT_VALIDATION=PASS |
| Stale guard activo como fallback | Cumple | `syncActiveRuns()` → `staleGuard.failExpiredRuns()` en cada carga de index; timeout de polling fuerza reload que activa stale guard |
| Test actualizado al contrato correcto | Cumple | `assertJsonPath('run.status', ...)` pasa (3/3 tests) |

## Compatibilidad Retroactiva

- **Lifecycle:** sin cambios. No se tocaron controladores, servicios ni middleware de lifecycle.
- **Provisioning:** sin cambios.
- **Login/autenticación:** sin cambios.
- **Fleet/registry:** sin cambios.
- **Control plane:** el endpoint `/status` cambió su estructura JSON pero el único consumidor conocido es `Index.vue` (que esperaba la estructura correcta desde su concepción). El test fue actualizado en consecuencia. Ningún otro endpoint o componente depende de `data.run`.
