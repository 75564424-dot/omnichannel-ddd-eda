# Simulación de escenario productivo — cliente con múltiples eventos y consumidores

**Objetivo:** reproducir un uso **creíble** (no toy) del sistema: **un cliente**, **varios tipos de evento**, **varios consumidores** en el catálogo del bus, **varias publicaciones** y validación en Middleware + Dashboard + logs.  
**Base operativa:** misma secuencia que `Runbook_cliente_simulado.md` (A→F).  
**Entorno:** local o staging; use **SQLite en archivo** (`DB_DATABASE=database/database.sqlite`) si mezcla `curl` + navegador + UI.

---

## 1. Escenario (historia)

**Cliente ficticio:** *RetailCo* — comercio omni-canal.

| Actor | Rol |
|-------|-----|
| **POS In‑store** | Emite `RetailCo.Order.Created` desde tienda. |
| **Partner API** | Emite `RetailCo.Inventory.Adjusted` desde integración de stock. |
| **Módulo Analytics** | Consumidor declarado de **ambos** tipos (agregaciones). |
| **Módulo Fulfillment** | Consumidor solo de `RetailCo.Order.Created` (envío). |
| **Módulo StockGuard** | Consumidor solo de `RetailCo.Inventory.Adjusted` (alertas de stock). |

Objetivo de la simulación: comprobar que **config + sync + carga** muestran en cola los **consumidores correctos por tipo**, que el **registry** refleja productores/suscriptores y que el **Dashboard** presenta catálogo y actividad coherente tras el tráfico.

---

## 2. Artefactos de configuración (ejemplo reproducible)

### 2.1 `config/modules/modules_config.json`

Sustituir o fusionar `producers` / `subscribers` con algo equivalente a:

```json
{
  "middleware": {
    "id": "middleware",
    "name": "Middleware bus",
    "description": "Simulación RetailCo.",
    "role": "routing"
  },
  "producers": [
    {
      "id": "retailco_pos",
      "name": "RetailCo POS",
      "event_types_emitted": ["RetailCo.Order.Created"]
    },
    {
      "id": "retailco_partner_api",
      "name": "RetailCo Partner API",
      "event_types_emitted": ["RetailCo.Inventory.Adjusted"]
    }
  ],
  "subscribers": [
    {
      "id": "analytics_core",
      "name": "Analytics Core",
      "event_types_consumed": ["RetailCo.Order.Created", "RetailCo.Inventory.Adjusted"]
    },
    {
      "id": "fulfillment_svc",
      "name": "Fulfillment Service",
      "event_types_consumed": ["RetailCo.Order.Created"]
    },
    {
      "id": "stock_guard",
      "name": "Stock Guard",
      "event_types_consumed": ["RetailCo.Inventory.Adjusted"]
    }
  ]
}
```

### 2.2 Fragmento `config/eventbus.php` (sin borrar claves existentes)

Dentro del array de retorno, definir **`producers`** y **`subscriptions`** alineados con lo anterior (los `event_type` deben coincidir **exactamente**):

```php
'producers' => [
    'retailco_pos' => [
        'label'    => 'RetailCo POS',
        'produces' => ['RetailCo.Order.Created'],
    ],
    'retailco_partner_api' => [
        'label'    => 'RetailCo Partner API',
        'produces' => ['RetailCo.Inventory.Adjusted'],
    ],
],

'subscriptions' => [
    'RetailCo.Order.Created' => [
        ['module' => 'Analytics Core'],
        ['module' => 'Fulfillment Service'],
    ],
    'RetailCo.Inventory.Adjusted' => [
        ['module' => 'Analytics Core'],
        ['module' => 'Stock Guard'],
    ],
],
```

*(Los demás valores del archivo — umbrales, colas, `consumer_registrars` — déjelos como estén o vacíos si no usa packs.)*

### 2.3 Coherencia con el Dashboard

Si quiere que gráficos reaccionen sin tocar `dashboard_config.json`, puede:

- usar **`platform:demo-dashboard-events`** para la serie demo (`Platform.Demo.Measurement`), **o**
- alinear temporalmente un tipo del escenario con la serie configurada (avanzado), **o**
- limitarse a validar **catálogo, feed de eventos y cola** sin cambiar KPIs.

Para la simulación “productiva” descrita, basta validar **cola + topología + catálogo + feed** si los payloads llevan `event_id`.

---

## 3. Flujo completo (pasos ejecutables)

Ejecutar **en orden**.

| # | Paso | Acción |
|---|------|--------|
| 1 | Preparar entorno | `composer install`; `.env` con `APP_URL`; **BD archivo** recomendado; `php artisan migrate` |
| 2 | Configurar cliente | Pegar/merge §2.1 y §2.2; validar JSON |
| 3 | Caché | `php artisan config:clear` **si** usa `config:cache` |
| 4 | Arrancar app | `php artisan serve` (u host staging) |
| 5 | **Sync** | `POST /api/middleware/registry/sync-config` → `success: true` |
| 6 | **Carga de eventos** | Varios `POST /api/middleware/events/publish` (ver §4) |
| 7 | Validación | §5 |

---

## 4. Carga de eventos (múltiples publicaciones)

### 4.1 Convención del cuerpo

Para cada POST:

- `event_id`: UUID **único** por evento.
- `event_type`: `RetailCo.Order.Created` **o** `RetailCo.Inventory.Adjusted`.
- `occurred_at`: ISO‑8601.
- `origin`: `POS` u `PARTNER_API` según el caso.
- `payload`: objeto que incluya al menos `event_id`, `event` / `event_type`, `occurred_at` (para feed).

### 4.2 Ejemplo único (curl)

```bash
curl -s -X POST "http://127.0.0.1:8000/api/middleware/events/publish" -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"event_id\":\"aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeee0001\",\"event_type\":\"RetailCo.Order.Created\",\"occurred_at\":\"2026-05-03T14:00:00+00:00\",\"origin\":\"POS\",\"payload\":{\"event_id\":\"aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeee0001\",\"event\":\"RetailCo.Order.Created\",\"event_type\":\"RetailCo.Order.Created\",\"occurred_at\":\"2026-05-03T14:00:00+00:00\",\"channel\":\"STORE\",\"order_ref\":\"SO-1001\"}}"
```

### 4.3 Ráfaga ligera (PowerShell — 10 pedidos + 10 ajustes de stock)

Genera UUIDs y alterna tipos; ajuste `$base` al host correcto.

```powershell
$base = "http://127.0.0.1:8000"
for ($i = 1; $i -le 10; $i++) {
  $id = [guid]::NewGuid().ToString()
  $ts = (Get-Date).ToUniversalTime().ToString("o")
  $type = if ($i % 2 -eq 1) { "RetailCo.Order.Created" } else { "RetailCo.Inventory.Adjusted" }
  $origin = if ($type -eq "RetailCo.Order.Created") { "POS" } else { "PARTNER_API" }
  $body = @{
    event_id = $id
    event_type = $type
    occurred_at = $ts
    origin = $origin
    payload = @{
      event_id = $id
      event = $type
      event_type = $type
      occurred_at = $ts
      seq = $i
    }
  } | ConvertTo-Json -Depth 5
  Invoke-RestMethod -Method Post -Uri "$base/api/middleware/events/publish" -ContentType "application/json" -Body $body | Out-Null
}
```

### 4.4 Procesamiento

Con **`QUEUE_CONNECTION=sync`** (típico en local), el dispatch es **síncrono**: cada `publish` procesa listeners en la misma petición. Si en staging usa **database**/**redis**, debe tener **worker** (`php artisan queue:work`) para consumidores encolados (`ShouldQueue`).

Esta simulación valida sobre todo **routing en cola + tracking + feed**, no la escala de colas asíncronas, salvo que active workers.

---

## 5. Validación — qué observar y criterio de éxito

### 5.1 API y datos

| Comprobación | Cómo | Éxito |
|--------------|------|--------|
| Sync | Respuesta `sync-config` | `success: true`; contadores > 0 acordes al catálogo |
| Cola | `GET /api/middleware/queue?limit=50` | Aparecen las filas recientes; `consumers` acorde al `event_type` (p. ej. pedido → Analytics + Fulfillment) |
| Evento puntual | `GET /api/middleware/events/{event_id}` | `PROCESADO` para un `event_id` publicado |
| Topología | `GET /api/middleware/topology` | `success: true`; estructura `config` / `observed` coherente tras tráfico |
| Catálogo declarativo | `GET /api/dashboard/modules/catalog` | Productores/suscriptores como en JSON |

### 5.2 UI

| Pantalla | Qué mirar |
|----------|-----------|
| **`/middleware`** | Cola con nuevas entradas; métricas sin error; topología |
| **`/dashboard`** | Catálogo de módulos; feed con entradas si el listener de feed aceptó el payload |

### 5.3 Logs (`storage/logs/laravel.log`)

| Buscar | Significado |
|--------|-------------|
| `[EventBus] Event published` | Una línea por publish con `event_id`, `event_type`, `consumers` |
| `[EventBus][Tracking]` / `Dashboard: … skipped` | Diagnóstico si falta `event_id` o falla ingestión |

**Criterio global “funciona como en producción”:**

1. Sin errores 500 en la ráfaga.  
2. Cola muestra **N** eventos con **consumidores** correctos por tipo.  
3. Registry/topología reflejan el **mismo** universo de módulos que el JSON + `eventbus`.  
4. Dashboard muestra **actividad o catálogo** acorde; si el feed falla, el motivo debe ser **identificable** en logs (payload / reglas), no silencio total sin trazas.

---

## 6. Limpieza y repetición

- Para repetir el escenario en la misma BD: borrar tablas operativas o usar BD nueva y `migrate:fresh` **solo** en entorno de prueba.
- Versionar en Git (rama o commit) el `modules_config.json` y el diff de `eventbus.php` del escenario para **reproducibilidad** entre miembros del equipo.

---

## 7. Referencias

- `docs/personal_notes/Runbook_cliente_simulado.md` — orden A→F y contratos HTTP.
- `docs/personal_notes/Observabilidad_pruebas_produccion_local.md` — logs y métricas API.
- `docs/personal_notes/Estrategia_pruebas_pre_produccion.md` — criterios de salida y automatización.

---

*Simulación orientada a validación pre-producción; ajuste nombres y tipos a su convención de `event_type` real.*
