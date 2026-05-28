# Fase B.2 — Sync ampliado (`registry/sync-config`)

Documento de referencia sobre la extensión del endpoint **`POST /api/middleware/registry/sync-config`**: incorporación del catálogo declarativo (`modules_config.json`) junto al catálogo del bus (`eventbus.php`) en la persistencia del registry.

---

## 1. Contexto del problema

En el diseño previo coexistían dos configuraciones con responsabilidades distintas:

| Fuente | Rol |
|--------|-----|
| **`config/modules/modules_config.json`** | Catálogo **declarativo** expuesto al Dashboard (productores, suscriptores, metadatos de middleware). Describe “qué módulos existen” en el modelo documentado del cliente. |
| **`config/eventbus.php`** | Catálogo **operativo** del bus: `producers`, `subscriptions`. Define qué `event_type` existen y qué consumidores in-process participan al publicar (`EventPublisherService`, metadatos de cola, topología técnica). |

El sync original persistía en **`middleware_registered_modules`** **solo** lo derivado de `config('eventbus.*')`. Una consecuencia habitual: el Dashboard mostraba módulos definidos en JSON mientras el registry del Middleware quedaba vacío o desactualizado respecto a esa misma intención declarada, obligando a **duplicar mentalmente** la configuración y generando errores por olvidar el sync tras editar solo uno de los archivos.

---

## 2. Solución implementada

**Qué hace ahora `sync-config`**

Sigue siendo un único endpoint sin cambio de contrato HTTP: responde `success` y contadores `producer_bindings` / `consumer_bindings`. Internamente, el caso de uso **`SyncConfiguredModulesToRegistryUseCase`**:

1. Recorre el catálogo **eventbus** (productores y suscripciones) y escribe/actualiza filas en el registry mediante **`ModuleRegistry`** (implementación existente sobre `middleware_registered_modules`).
2. Recorre el catálogo declarativo **`config('modules.catalog')`**, cargado desde `modules_config.json` vía **`config/modules.php`**, y aplica la **misma** semántica de upsert:
   - **Productores:** cada entrada válida (`id`, `name`, `event_types_emitted`) genera vínculos productor → tipo de evento.
   - **Suscriptores:** cada entrada válida (`id`, `name`, `event_types_consumed`) genera vínculos consumidor → tipo de evento (normalización de `logical_id` en línea con consumidores del eventbus).

**Fuentes que consume**

- `config('eventbus.producers')` y `config('eventbus.subscriptions')` — **sin modificar** la estructura ni eliminar `eventbus.php`.
- `config('modules.catalog')` — reflejo en runtime del JSON declarativo.

**Cómo unifica la información**

No fusiona archivos en disco ni introduce una tercera capa de configuración: **unifica en un solo lugar persistido** (`middleware_registered_modules`) las declaraciones que provienen de **ambas** fuentes. Los vínculos duplicados (mismo rol + mismo `logical_id` lógico + mismo `event_type`) se **omitieron en el mismo ciclo de sync** mediante un registro en memoria de claves ya aplicadas, y la capa de persistencia sigue haciendo **merge** de `event_types` por `(logical_id, type)` para mantener una fila coherente por módulo en el registry.

---

## 3. Flujo actualizado

```text
modules_config.json
        │
        ▼
config/modules.php  ──►  config('modules.catalog')
        │                          │
        │                          ├──► SyncConfiguredModulesToRegistryUseCase
        │                          │
eventbus.php  ──►  config('eventbus.*') ──►  (mismo use case)
                                               │
                                               ▼
                              middleware_registered_modules (registry)
                                               │
                     ┌────────────────────────┴────────────────────────┐
                     ▼                                                  ▼
            Middleware (vistas / APIs que leen registry           Dashboard (catálogo
            + topología/registro alineados con lo persistido)       declarativo desde JSON;
                                                                   registry coherente tras sync)
```

**Lectura operativa:** el operador edita JSON y/o `eventbus.php`, ejecuta **`POST /api/middleware/registry/sync-config`** cuando quiera materializar declaraciones en el registry; luego valida UI y tráfico según el runbook del proyecto.

---

## 4. Beneficios obtenidos

- **Reducción de errores humanos:** un solo paso de sync puede reflejar en persistencia tanto el bus declarativo en PHP como el catálogo del cliente en JSON, sin depender de recordar “solo eventbus alimenta el registry”.
- **Coherencia entre UI y ejecución:** el Middleware deja de quedar sistemáticamente “atrás” respecto al catálogo que ya ve el Dashboard por JSON, **en la capa de registry** (módulos y tipos asociados persistidos).
- **Simplificación operativa:** menos ambigüedad sobre “qué archivo alimenta `middleware_registered_modules`”; la respuesta del sync expone contadores de vínculos **únicos** tras la deduplicación en un mismo request.

---

## 5. Consideraciones técnicas

**Idempotencia**

- Ejecutar `sync-config` varias veces con la misma configuración no crea filas duplicadas por restricción única `(logical_id, type)` y por el merge de `event_types` en **`DatabaseModuleRegistry`**.
- Los contadores de la respuesta cuentan vínculos únicos `(rol, logical_id normalizado, event_type)` dentro de **un** `execute()`.

**Manejo de duplicados**

- Si el mismo vínculo aparece en **eventbus** y en **JSON**, solo se cuenta y persiste un efecto equivalente para ese par (evita inflar estadísticas y llamadas redundantes al merge).
- Diferencias de cadenas que no normalizan igual (p. ej. IDs distintos para el “mismo” módulo) pueden seguir produciendo **dos** filas; conviene convención operativa de nombres/IDs alineados entre fuentes.

**Compatibilidad hacia atrás**

- Si `modules_config.json` no declara productores/suscriptores (arreglos vacíos), el sync se comporta como **antes**: solo eventbus alimenta el registry.
- No se eliminaron rutas, tablas ni la forma de `eventbus.php`.

---

## 6. Limitaciones actuales

- **Enrutamiento y cola al publicar:** siguen gobernados por **`eventbus.subscriptions`**. Declarar un consumidor solo en JSON **sí** puede poblar el registry, pero **no** añade por sí solo suscripción in-process ni rellena `consumers` en cola como si existiera una entrada equivalente en `eventbus.php`.
- **Multi-tenant / per-tenant catálogo:** no hay partición de registry por tenant; un solo catálogo global por instancia/BD.
- **UI dinámica:** el Dashboard no edita `modules_config.json`; el sync no sustituye flujos de aprobación o publicación de configuración en caliente.
- **Origen único de verdad “ideal”:** persisten dos archivos de configuración en disco; B.2 **unifica el destino persistido** del registry, no fusiona el modelo de configuración en un solo archivo.
- **Resolución de conflictos de nombre:** si eventbus y JSON aportan nombres distintos para el mismo `logical_id`, el orden de aplicación y las reglas de merge determinan el valor final en filas existentes; no hay política de “precedencia por fuente” documentada en código más allá del merge actual (último valor no vacío en campos actualizables).

---

## Referencia de código (implementación)

| Componente | Ubicación aproximada |
|------------|----------------------|
| Caso de uso del sync | `App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase` |
| Endpoint HTTP | `App\Middleware\Interfaces\Http\Controllers\ModuleRegistrySyncController` |
| Persistencia / merge | `App\Middleware\Infrastructure\Persistence\DatabaseModuleRegistry` |
| Carga del JSON declarativo | `config/modules.php` ← `config/modules/modules_config.json` |

---

*Nota personal / fase B.2 — útil como base para evoluciones (p. ej. precedencia explícita por fuente, sync selectivo, o extensión multi-tenant) sin reabrir el diseño desde cero.*
