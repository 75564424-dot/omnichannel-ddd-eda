# Informe — Remediación bug configuración de módulos por tenant

**Fecha:** 2026-06-08  
**Alcance:** Flujo Provisioning → Levantar servicio → Configurar módulos → Guardar → Portal cliente → Middleware  
**Estado:** Corregido y validado

---

## Síntoma observado

En `/control/companies/{id}/modules`, al pulsar **Guardar catálogo de módulos** la red alternaba:

```
PATCH /control/companies/{id}/modules-catalog
GET  /control/companies/{id}/modules
```

El operador percibía que la configuración **no persistía** y el portal del cliente / middleware seguían sin módulos.

---

## Investigación (checklist obligatorio)

| # | Pregunta | Resultado |
|---|----------|-----------|
| 1 | ¿Se ejecuta Guardar? | **Sí.** `ModulesConfig.vue` → `form.patch(.../modules-catalog)` en `@submit.prevent="save"`. |
| 2 | ¿Sale la petición del navegador? | **Sí.** Logs del servidor (`npm run instances:serve`) confirman PATCH + GET por cada intento. |
| 3 | ¿Llega al backend? | **Sí.** `CompanyController::updateModulesCatalog` + validación Laravel. |
| 4 | ¿Persiste? | **Sí en control plane**, **no en silo**. Tenant `pruebas` tenía catálogo completo en CP (`modules_catalog_updated_at`) pero silo con `modules_catalog: null` y `modules_config.json` con `producers: []`. |
| 5 | ¿Respuesta correcta? | **Sí.** Redirect 302 a `control.companies.modules` con flash de éxito. |
| 6 | ¿Loop frontend? | **No automático.** `ModulesConfig.vue` no tiene `watch`, polling ni reload. El patrón PATCH→GET es el redirect estándar de Inertia; las repeticiones corresponden a reintentos del operador al no ver cambios en portal/middleware. |
| 7 | ¿Loop backend? | **No.** Sin redirects encadenados ni re-sync en bucle. |

---

## Causa raíz

**Desacople introducido por el aislamiento multi-instancia (v1.6 / v1.7).**

1. **v1.6 (Fase aislamiento):** cada tenant vive en silo SQLite + `config/modules/instances/{slug}/modules_config.json`. El mirror CP→silo (`LocalFleetTenantMirror`) se ejecuta en **provisioning**, **lifecycle start/suspend/restore** y sync manual — **no** tras guardar catálogo.
2. **v1.7 (Fase 7):** `TenantModuleCatalogService::storedCatalog()` separa catálogo persistido vs fallbacks (fixture, blueprint). El guardado en CP funciona, pero el silo sigue leyendo datos obsoletos del mirror anterior (catálogo vacío al levantar servicio).
3. **Efecto:** Guardar actualiza solo la BD del control plane. Portal cliente (`ClientInstancePortalService::getCatalog`) y middleware del silo consultan su propia BD/archivo → módulos vacíos → sensación de fallo → reintentos PATCH/GET.

**Componente responsable:** `TenantModuleCatalogService::saveCatalog()` — persistía en CP sin propagar al silo desplegado.

**Regresión:** no es un bug de rutas ni de `ModulesConfig.vue`; es una **brecha de sincronización** no cubierta cuando el flujo operativo pasó a fleet aislado.

---

## Archivos afectados

| Archivo | Cambio |
|---------|--------|
| `app/Control/Application/Services/Tenants/TenantModuleCatalogService.php` | Tras guardar, invoca `LocalFleetTenantMirror` si el tenant tiene `deployment.local_instance`. |
| `resources/js/Pages/Control/Companies/ModulesConfig.vue` | Sincroniza formulario con props del servidor tras éxito; muestra errores de validación anidados. |
| `tests/Feature/Control/TenantModuleCatalogTest.php` | Test de mirror obligatorio cuando hay silo. |
| `deploy/local-instances/README.md` | Nota operativa: guardar catálogo propaga al silo automáticamente. |

---

## Seguimiento — regresiones post-fix (2026-06-08)

### Control plane colgado en :8000

**Causa:** `saveCatalog()` invocaba `mirror()` completo (re-sync de operadores) de forma **síncrona** en la petición HTTP. Con `php artisan serve` (un hilo por puerto) y SQLite compartido entre CP + silos, la petición podía bloquearse >15s y dejar el puerto 8000 sin responder.

**Corrección:** `mirrorCatalog()` — solo `syncTenantSettings` + `modules_config.json`, sin `syncOperators`, con `PRAGMA busy_timeout=5000`.

**Operación:** reiniciar `npm run instances:serve` si el proceso quedó bloqueado.

### Middleware sin módulos configurados

**Causas:**
1. `sync-config` solo leía `modules.catalog` cuando eventbus estaba **totalmente vacío**.
2. La topología solo leía `eventbus.php`, no el catálogo declarativo.

**Corrección:**
- `ConfiguredModuleRegistrySyncService` fusiona siempre el catálogo declarativo (con deduplicación).
- `TopologySnapshotAssembler` incluye `modules.catalog` en el snapshot de topología.

### Animaciones dashboard

**Causa:** `middlewareFlowActive` en Dashboard solo miraba `simulationPulse.active`, ignorando actividad reciente del feed.

**Corrección:** alinear con Middleware — pulso de simulación **o** eventos recientes en feed (ventana 15s).

---

## Corrección aplicada (fase 1)

```php
// TenantModuleCatalogService::saveCatalog()
$tenant->update(['settings' => $settings]);

if ($this->hasLocalInstanceDeployment($settings)) {
    $this->tenantMirror->mirrorCatalog($tenant->fresh() ?? $tenant);
}
```

El mirror existente ya:
- Copia `modules_catalog` al SQLite del silo (`syncTenantSettings`).
- Escribe `config/modules/instances/{slug}/modules_config.json` (`writeModulesConfig`).

**Frontend complementario:** `onSuccess` → `form.reset()` desde props del servidor; lista de errores de validación visibles (p. ej. `alpha_dash` en ids de productor).

---

## Riesgo de regresión

| Área | Riesgo | Mitigación |
|------|--------|------------|
| Tenants sin silo (solo registro SaaS) | Bajo | Mirror solo si existe `deployment.local_instance.db_path`. |
| Provisioning / lifecycle | Bajo | Mirror idempotente; no altera spawn ni estados. |
| Simulación | Bajo | `storedCatalog()` sin cambios; tests Control 75/75 verdes. |
| Mirror fallido (silo corrupto) | Medio | Excepción `RuntimeException` propagada al UI vía `withErrors(['catalog' => ...])`. |

---

## Evidencia de validación

### Tests automatizados

```
php artisan test --filter=TenantModuleCatalogTest
✓ saas admin can save tenant module catalog
✓ saving catalog mirrors to local instance when tenant has silo

php artisan test --filter=Control
75 passed
```

### Tenant nuevo (no histórico)

Script de validación E2E programático:

```
Created tenant bugfix-modulos-043818 at http://127.0.0.1:8002
CP producers: 1
Silo producers: 1
JSON producers: 1
VALIDATION PASS
```

### Tenant histórico `pruebas` (post-fix)

Tras `saveCatalog` con mirror:
- Silo `database/instances/pruebas.sqlite` → `modules_catalog.producers` poblado.
- `config/modules/instances/pruebas/modules_config.json` → productores sincronizados.

### Criterios de éxito

| Criterio | Estado |
|----------|--------|
| Guardar funciona | ✓ |
| Módulos persisten (CP + silo) | ✓ |
| Portal cliente actualizado | ✓ (vía mirror → `ClientInstancePortalService`) |
| Middleware actualizado | ✓ (vía `modules_config.json`; sync-config sigue siendo paso operativo para registry) |
| Sin loop automático de consultas | ✓ |
| Sin errores JS/backend en tests | ✓ |
| Sin regresiones Control | ✓ |

---

## Nota operativa post-fix

Tras **Guardar catálogo de módulos**, el silo local se sincroniza automáticamente. En middleware del cliente sigue recomendándose **`POST /api/middleware/registry/sync-config`** para materializar bindings en el registry persistido (sin cambio de contrato).

---

## Regresión — simulaciones bloqueadas en RUNNING 0% (2026-06-08)

### Síntoma

- La simulación se crea y aparece en historial con estado **En curso**.
- El frontend hace polling a `/control/simulations/{id}/status` cada ~2 s.
- El progreso permanece **0 / N (0%)** indefinidamente; el polling no termina.

### Investigación (checklist)

| # | Pregunta | Resultado |
|---|----------|-----------|
| 1 | ¿Se crea la simulación? | **Sí.** Fila en `simulation_runs` con `status=running`, handoff en `storage/app/simulation-handoff/{id}.json`. |
| 2 | ¿Se despacha el worker? | **Sí.** `LocalFleetSimulationRunner::dispatchToClientSilo` escribe dispatch log y lanza `.bat` (Windows). |
| 3 | ¿El worker ejecuta? | **Sí** cuando el CP no está bloqueado. Evidencia: run `ae8fdf13-…` completó 6/6 eventos; worker log confirma publicación. |
| 4–6 | ¿Eventos / middleware / contadores? | **Sí** en silo cliente; progreso llega vía HTTP interno (`PATCH /control/internal/simulation-runs/{id}/progress`) o handoff. |
| 7 | ¿Transición de estado? | **Completa** si el CP responde; **se cuelga** si el hilo HTTP del CP queda bloqueado durante el dispatch. |

### Causa raíz demostrada

**Misma clase de fallo que el bug de módulos:** `LocalFleetSimulationRunner::dispatchToClientSilo()` invocaba `LocalFleetTenantMirror::mirror()` **completo** (sync de operadores + SQLite compartido) de forma **síncrona** en la petición HTTP que inicia la simulación.

Con `php artisan serve` (un hilo por puerto):

1. El POST de simulación bloquea el CP durante el mirror.
2. El worker arranca en proceso separado y publica eventos.
3. Las callbacks de progreso (`/control/internal/.../progress`) y el polling del operador (`/control/simulations/{id}/status`) **no pueden atender** mientras el mirror retiene el hilo → UI congelada en 0%.
4. El **stale guard** existía pero solo corría en `SimulationRunController::index()`, **no** en cada poll de `/status` → simulaciones eternas aunque el CP se recuperara parcialmente.

**Regresión:** introducida con el patrón fleet v1.6/v1.7 (mirror CP→silo antes de cada dispatch); agravada al no ejecutar stale guard en el endpoint de polling.

### Correcciones aplicadas

| Archivo | Cambio |
|---------|--------|
| `LocalFleetSimulationRunner.php` | `mirror()` → `mirrorCatalog()` antes del handoff (sin sync de operadores). |
| `SimulationRunQueryService.php` | `statusPayload()` invoca `SimulationRunStaleGuard::failRunIfExpired()` en cada poll. |
| `SimulationRunStaleGuard.php` | Timeout configurable sin progreso (`PLATFORM_SIMULATION_NO_PROGRESS_TIMEOUT_MINUTES`, default 5 min). |
| `SimulationRunCancellationService.php` | Nuevo: `RUNNING → CANCELLED`, handoff `cancel_requested`, auditoría, métricas parciales. |
| `SimulationRunController.php` + `routes/control.php` | `POST /control/simulations/{run}/cancel`. |
| `ExecuteSimulationRunOnInstanceService.php` | Worker respeta `cancel_requested` en handoff. |
| `Companies/Index.vue`, `Simulation/Index.vue` | Botón **Cancelar** visible solo en `running`; polling se detiene en `cancelled`/`failed`/`completed`. |

### Validación E2E (fleet local)

```
Tenant pruebas      → run ae8fdf13-… completed 6/6 (~48 s)
Tenant bugfix-modulos → run 3c2ab3cf-… completed 4/4 (~48 s)

php artisan test --filter=SimulationRun
13 passed (incl. cancel + stale guard en /status)
```

### Criterios de éxito — simulaciones

| Criterio | Estado |
|----------|--------|
| Progreso aumenta | ✓ |
| Estado finaliza (completed/failed) | ✓ |
| Polling termina | ✓ |
| Cancelación operativa | ✓ |
| Timeout sin progreso (~5 min) | ✓ (configurable) |
| Sin simulaciones eternas en poll | ✓ |

---

## Simulaciones bloqueadas — segunda investigación (2026-06-08)

### Síntoma persistente

Tras la primera remediación siguieron observándose simulaciones **En curso 0%** en UI, mezcladas con otras que completaban correctamente en el **mismo tenant** (`pruebas`).

### Comparativa obligatoria (evidencia en CP + filesystem)

| Aspecto | Simulación **exitosa** (ej. `ae8fdf13`, `3c2ab3cf`) | Simulación **bloqueada / 0%** (ej. `e30f2b92`, canceladas `ddd9df9c…`) |
|---------|------------------------------------------------------|------------------------------------------------------------------------|
| Tenant | `pruebas` / `bugfix-modulos-043818` | **`pruebas` (mismo tenant)** |
| Catálogo CP | 1 productor | 1 productor (idéntico) |
| Silo SQLite | `modules_catalog` + JSON con 1 productor | idéntico |
| `dispatch log` | **Sí** | **Sí** |
| `worker log` | **Sí** (`Simulation … completed`) | **No** (worker nunca escribió log) |
| Handoff post-run | eliminado (`handoff=no`) | huérfano con `phase=cancelled` o ausente |
| Progreso CP | N/N | 0/N hasta timeout o cancelación manual |
| Contaminación silo | event_store/message_queue de runs previos | **No impide** runs nuevos (mismo tenant completó antes y después) |

**Conclusión:** no es diferencia de configuración de tenant/módulos ni de datos históricos en silo. Es **intermitencia de arranque del worker** + **detección incorrecta de “worker vivo”** + **artefactos runtime sin limpiar**.

### Causa raíz adicional demostrada

1. **`SimulationRunWorkerMonitor::isLikelyAlive()`** trataba `handoff.phase = dispatched` como worker activo durante ~8 min (`maxWallClockMinutes`), retrasando el fail del stale guard aunque **no existiera `simulation-worker-{id}.log`**. Mensaje engañoso: *“Tiempo máximo de ejecución del worker superado”* en lugar de *“worker no arrancó”*.

2. **Handoffs huérfanos** tras cancelación manual a 0% (cancel no ejecutaba `forget()` del handoff) → auditorías confundían estado terminal en disco con runs activos.

3. **Contaminación histórica** (runs cancelados/fallidos, métricas, colas) **no bloquea** simulaciones nuevas, pero impide auditorías limpias y mantiene filas `running` percibidas en UI si el operador no recarga tras timeout.

### Correcciones (segunda fase)

| Archivo | Cambio |
|---------|--------|
| `SimulationRunWorkerMonitor.php` | Solo fases `starting`/`publishing`/`simulating` cuentan como worker vivo; `dispatched` requiere log reciente. |
| `SimulationRunCancellationService.php` | `forget()` del handoff tras cancelar. |
| `LocalEnvironmentResetService.php` | Nuevo servicio de saneamiento integral. |
| `ResetLocalEnvironmentCommand.php` | **`php artisan platform:reset-local`** (oficial). |
| `LocalFleetRegistry.php` | `replaceInstances([])` para `--purge-tenants`. |
| `README.md` | Documentación de cuándo usar el comando, qué elimina/preserva, ejemplos. |

### Comando oficial de saneamiento

```bash
# Limpieza estándar (simulaciones + runtime CP/silos, conserva tenants)
php artisan platform:reset-local --force --env=control-plane

# Entorno vacío de clientes (reprovisionar desde /control/provisioning)
php artisan platform:reset-local --purge-tenants --force --env=control-plane
```

**Elimina:** `simulation_runs`, handoffs, logs/launchers de simulación, tablas operativas (colas, event_store, métricas, registry…) en CP y silos del fleet, marcadores `last_simulation`.

**Preserva:** migraciones, configuración base, operador SaaS, tenants (salvo `--purge-tenants`).

### Validación posterior a limpieza (entorno limpio)

Tras `platform:reset-local --force`:

| Tenant | Run | Resultado |
|--------|-----|-----------|
| `pruebas` | `c4606924-…` | **completed 4/4**, worker_log=sí |
| `bugfix-modulos-043818` | `79a33be6-…` | **completed 4/4**, worker_log=sí |
| **Nuevo** `audit-clean-918103` (provisionado post-reset) | `9289bdef-…` | **completed 4/4**, worker_log=sí |

```
php artisan test --filter="SimulationRun|ResetLocalEnvironment"
Control suite: 77 passed
```

### Criterios de éxito — segunda fase

| Criterio | Estado |
|----------|--------|
| Causa raíz restante identificada | ✓ (worker monitor + handoffs huérfanos) |
| Simulaciones OK en entorno limpio | ✓ (3 tenants: históricos + nuevo) |
| Comando oficial de limpieza | ✓ `platform:reset-local` |
| README actualizado | ✓ |
| Sin simulaciones eternas tras poll + stale guard | ✓ |
| No dependencia de datos históricos | ✓ (validado post-reset) |

---

## Regresión — provisioning inoperante post-limpieza (2026-06-08)

### Síntoma

Tras apagar el proyecto, ejecutar `platform:reset-local --purge-tenants` y reiniciar:

- No era posible crear tenants (slug duplicado / lifecycle sin silo).
- `/control/companies` mostraba empresas fantasma sin `.env.client-*` ni SQLite.
- `fleet-registry.json` quedó con `"instances": []`.
- **Levantar servicio** fallaba: *Local instance deployment details not found*.

### Investigación (estado actual vs esperado)

| Elemento | Esperado tras `--purge-tenants` | Estado roto observado |
|----------|----------------------------------|------------------------|
| `fleet-registry.json` | `[]` o solo clientes válidos | `[]` ✓ |
| Tenants CP | solo `platform` | **huérfanos** (`pruebas`, `bugfix-…`, `audit-clean-…`) |
| `.env.client-*` | ninguno hasta reprovisionar | **ninguno** ✓ |
| `database/instances/*.sqlite` | solo `platform.sqlite` | solo `platform.sqlite` ✓ |
| Provisioning nuevo slug | OK | OK en servicio |
| Provisioning slug existente | N/A | **UNIQUE constraint / validación** |

### Causa raíz demostrada

1. **`--purge-tenants` incompleto (regresión del comando):** solo eliminaba tenants listados en `fleet-registry.json`. Si el registry ya estaba vacío (o desincronizado), **no borraba filas huérfanas en `tenants` del CP** aunque sí había eliminado silos en una pasada anterior.

2. **Estado huérfano:** filas CP sin `deployment.local_instance`, sin `.env`, sin SQLite → el operador ve empresas pero no puede levantar servicio ni reutilizar el slug.

3. **El pipeline de provisioning en sí no estaba roto:** `ProvisionNewTenantService` + `LocalFleetInstanceProvisioner` crearon `clean-d45000` correctamente (puerto 8001, registry, env, SQLite, deployment) cuando el CP solo tenía `platform`.

4. **Riesgo operativo:** ejecutar reset **sin** `--env=control-plane` apunta a otra SQLite (`database/database.sqlite` legacy) y no limpia el CP real.

### Corrección

| Archivo | Cambio |
|---------|--------|
| `LocalEnvironmentResetService.php` | `--purge-tenants` elimina **todos** los tenants CP ≠ `platform`, barre `.env.client-*`, SQLite cliente, `config/modules/instances/*` y vacía registry. |
| `ResetLocalEnvironmentCommand.php` | Rechaza ejecución si `platform.control_plane !== true`. |
| `ResetLocalEnvironmentCommandTest.php` | Test: orphan CP sin registry se elimina con purge. |
| `README.md` | Advertencia obligatoria `--env=control-plane` y alcance real del purge. |

### Validación en entorno limpio

```
php artisan platform:reset-local --purge-tenants --force --env=control-plane
→ CP: 1 tenant (platform)

ProvisionNewTenantService → clean-d45000
→ registry port 8001, .env.client-clean-d45000, clean-d45000.sqlite
→ deployment.local_instance presente

TenantLifecycleOrchestrator::start(clean-d45000) → OK
```

```
php artisan test --filter=ResetLocalEnvironmentCommandTest
2 passed
```

### Criterios de éxito — provisioning post-limpieza

| Criterio | Estado |
|----------|--------|
| Provisioning tenant nuevo | ✓ |
| Puerto asignado | ✓ (8001) |
| SQLite creado | ✓ |
| Registro fleet | ✓ |
| Levantar servicio | ✓ |
| Flujo reproducible desde CP limpio | ✓ |

---

## Auditoría de limpieza — tenants históricos (2026-06-08)

### Hallazgos encontrados

Auditoría con `platform:clean-environment --verify` **antes** del fortalecimiento:

```
Tenant soft-deleted en CP: pruebas (y 9 slugs más)
SQLite residual: database/instances/pruebas.sqlite
Mirror modules residual: config/modules/instances/pruebas
```

| Área | Qué sobrevivía | Impacto |
|------|----------------|---------|
| CP `tenants` | **Soft delete** (`deleted_at`) | Slug `pruebas` bloqueado por `unique:tenants,slug` aunque no apareciera en UI |
| CP `users` | Operadores de tenants soft-deleted | Colisión `unique:users,email` al reprovisionar |
| SQLite / `.env` / mirrors | Artefactos en disco | Fleet inconsistente, lifecycle fallido |
| `fleet-registry.json` | Solo slugs listados en purge antiguo | Tenants huérfanos en CP sin silo |
| Simulaciones | OK tras reset | Handoffs en `storage/app/simulation-handoff/` |
| Middleware ops | OK tras reset | Tablas operativas vaciadas; topología se reconstruye |

### Causa raíz — slug histórico no reciclable

`TenantModel` usa `SoftDeletes`. Los comandos de purge llamaban `$tenant->delete()` (soft), **no** `forceDelete()`. La validación HTTP usa `'unique:tenants,slug'` sobre la tabla completa → **el slug `pruebas` seguía ocupado** aunque el tenant pareciera eliminado.

### Referencias históricas eliminadas (corrección)

| Componente | Cambio |
|------------|--------|
| `LocalEnvironmentResetService` | `forceDelete()` de todos los tenants cliente; purge de incidentes; `safeUnlink` para SQLite bloqueadas |
| `LocalEnvironmentAuditService` | **Nuevo** — detecta soft-deleted, fleet huérfano, artefactos residuales |
| `CleanEnvironmentCommand` | **Nuevo** — `platform:clean-environment` (purge completo + auditoría) |
| `LocalFleetOrphanPruner` | `forceDelete()` en lugar de soft delete |
| Tests | Reciclaje de slug `pruebas` post-limpieza |

### Nuevo comando de limpieza

```bash
php artisan platform:clean-environment --force --env=control-plane
php artisan platform:clean-environment --verify --env=control-plane
```

### Validación con tenant histórico reutilizado (`pruebas`)

```
1. platform:clean-environment --verify  → detectó 11 soft-deleted + SQLite/mirror pruebas
2. platform:clean-environment --force     → entorno limpio (auditoría OK)
3. ProvisionNewTenantService(slug=pruebas) → OK puerto 8001, registry, .env, SQLite
4. StartTenantServiceUseCase(pruebas)       → lifecycle running
5. Segundo ciclo clean + recreate pruebas   → OK (con fleet detenido)
```

```
php artisan test --filter="CleanEnvironmentCommandTest|ResetLocalEnvironmentCommandTest"
6 passed
```

### Criterios de éxito — auditoría de limpieza

| Criterio | Estado |
|----------|--------|
| Tenant histórico eliminado completamente (hard) | ✓ |
| Tenant histórico `pruebas` recreable | ✓ |
| Sin soft-deleted en CP | ✓ |
| Sin SQLite/mirrors huérfanos post-clean | ✓ (requiere detener fleet) |
| Sin registros fleet huérfanos | ✓ |
| Entorno reproducible desde cero | ✓ |

---

## Regresión — simulación 0% tras reprovisionar `pruebas` (2026-06-09)

### Síntoma

Tras limpieza y reprovisionamiento, la simulación en tenant `pruebas` quedaba en **En curso 0/10** hasta cancelación manual. El worker a veces no arrancaba o no reportaba progreso.

### Causa raíz demostrada

1. **`mirrorCatalog()` en el dispatch HTTP** (`LocalFleetSimulationRunner`): bloqueaba el hilo del CP y competía por `pruebas.sqlite` con el silo `:8001` ya levantado.
2. **Doble `syncRegistry`**: con `prepare_first=true` se sincronizaba en prepare y otra vez en `simulate()` (`skipSync: $skipPrepare` invertido).
3. **`reportProgress` HTTP bloqueante** al inicio del worker: hasta ~48 s de reintentos si el CP no respondía a tiempo.

### Corrección

| Archivo | Cambio |
|---------|--------|
| `LocalFleetSimulationRunner.php` | Eliminado `mirrorCatalog` en dispatch; catálogo via handoff + prepare en worker |
| `TenantSimulationAutomationService.php` | `skipSync: ! $skipPrepare` (evita doble sync) |
| `SimulationRunControlPlaneClient.php` | Timeouts cortos en progress/complete; progress no aborta worker |
| `ExecuteSimulationRunOnInstanceService.php` | Fase `publishing` en handoff antes de publicar eventos |

### Validación

```
Tenant pruebas → run e476e488-… 
→ progreso 1/10 … 10/10 en ~90 s
→ completed vía handoff sync en /status
php artisan test --filter=SimulationRun → 14 passed
```
