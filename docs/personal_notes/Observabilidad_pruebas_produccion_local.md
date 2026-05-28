# Observabilidad en pruebas tipo producción (modo local / sin SaaS externo)

**Alcance:** depuración rápida del **bus**, **middleware** y **dashboard** con herramientas mínimas (archivos de log Laravel, SQL opcional, APIs existentes). **No** requiere Datadog, Grafana ni agentes.

---

## 1. Logs necesarios

### 1.1 Dónde mirar

| Origen | Ubicación típica | Cuándo usarla |
|--------|------------------|---------------|
| Aplicación Laravel | `storage/logs/laravel.log` (canal `stack` / default) | Desarrollo y staging con `LOG_CHANNEL=single` o `daily` |
| Salida consola | `php artisan serve`, workers | Errores fatales o stack traces en primer plano |

**Ajuste útil en `.env` (local / staging):**

```env
LOG_LEVEL=debug
LOG_CHANNEL=single
```

En producción suele usarse `daily` + rotación; el **contenido** recomendado de búsqueda es el mismo.

### 1.2 Eventos emitidos

| Mensaje / prefijo | Nivel | Contenido clave |
|-------------------|-------|------------------|
| `[EventBus] Event published` | `info` | `event_id`, `event_type`, `origin`, `consumers` (lista de módulos suscritos según config) |

**Ejemplo de línea útil (conceptual):**

```text
[EventBus] Event published {"event_id":"…","event_type":"Acme.Order.Placed","origin":"POS","consumers":["AnalyticsSink"]}
```

*Origen en código:* `App\Middleware\Application\Services\EventPublisherService`.

### 1.3 Consumidores / ingestión (no siempre un log por listener)

Los listeners de negocio **no** están obligados a loguear; la plataforma sí deja rastro en:

| Mensaje | Nivel | Significado |
|---------|-------|-------------|
| `[EventBus][Tracking] Received event with no event_id — skipping.` | `warning` | Evento observado sin `event_id` en payload |
| `[EventBus][Tracking] Failed to track event` | `error` | Fallo al persistir tracking en cola |
| `Dashboard: UniversalDashboardFeedListener skipped (no event_id)` | `info` | Feed no proyectó fila |
| `Dashboard: UniversalDashboardFeedListener skipped (ingestion gate)` | `info` | Payload no pasó reglas de ingestión |
| `Dashboard: EventFeedEntry projected` | `info` | `event_id` / `event_type` proyectados al feed |
| `[EventBus] Pack registrar … — skipped` | `warning` | Fase C: clase pack inválida o catálogo malo |

**Debugging real:** si el evento **sí** aparece en `Event published` pero no en el feed o la cola, encadenar con estos mensajes.

### 1.4 Errores

| Tipo | Dónde aparece |
|------|----------------|
| Validación `publish` (422) | Respuesta HTTP + a veces sin log si no se reporta la excepción |
| Excepciones no capturadas | `laravel.log` + stack trace |
| Dead letters / sync BD | `[EventBus] Dead letter …`, errores de proyección |

**Búsqueda rápida en log (PowerShell):**

```powershell
Select-String -Path storage\logs\laravel.log -Pattern "EventBus","Tracking","Dashboard:"
```

**Bash:**

```bash
grep -E 'EventBus|Tracking|Dashboard:' storage/logs/laravel.log | tail -n 80
```

---

## 2. Métricas clave

### 2.1 Ya expuestas por la API (sin Prometheus)

| Métrica / dato | Cómo obtenerla | Notas |
|----------------|----------------|-------|
| Snapshots de bus | `GET /api/middleware/metrics` | Latencia, EPS, error rate, dead letters, `bus_status` (forma depende del snapshot actual) |
| Refresco explícito | `POST /api/middleware/metrics/refresh` | Fuerza un ciclo de actualización útil en demos |
| Cola FIFO (vista) | `GET /api/middleware/queue?limit=50` | Cuenta implícita de eventos recientes; campos `status`, `processing_time_ms`, `consumers` |
| Evento puntual | `GET /api/middleware/events/{eventId}` | Estado `PENDING` vs `PROCESADO`, tipo, origen |
| Dead letters | `GET /api/middleware/dead-letters` | Errores encolables / DLQ según implementación |

### 2.2 Número de eventos

| Vista | Uso |
|-------|-----|
| API cola | Conteo de filas devueltas o agregación externa en scripts |
| Dashboard | KPIs configurables en `config/dashboard_config.json` (ventanas 60s / 24h) vía `GET /api/dashboard/metrics` |
| BD (local) | `SELECT COUNT(*) FROM bus_queue_entries WHERE …` para auditoría puntual |

### 2.3 Tiempos de procesamiento

| Fuente | Campo / idea |
|--------|----------------|
| Cola API / entidad | `processing_time_ms`, `published_at`, `dispatched_at` |
| Logs | Correlacionar timestamp del log `Event published` con hora de respuesta HTTP |

### 2.4 Errores

| Fuente | Uso |
|--------|-----|
| `error_rate`, `dead_letters` en métricas middleware | Tendencia en UI `/middleware` |
| Tabla / API dead letters | Detalle por ítem |

---

## 3. Trazabilidad (fin a fin)

**Correlación principal:** `event_id` (UUID). Debe viajar en el sobre HTTP (`event_id` + dentro de `payload` para feed).

### 3.1 Camino recomendado (checklist manual)

1. **Publicar** (HTTP o comando) y anotar `event_id` de la respuesta o del cuerpo.
2. **Log:** buscar `[EventBus] Event published` con ese `event_id`.
3. **Middleware:** `GET /api/middleware/events/{event_id}` → estado y metadatos.
4. **Cola:** `GET /api/middleware/queue` → confirmar fila y `consumers` esperados según `eventbus.subscriptions`.
5. **Dashboard:** `GET /api/dashboard/events/feed?limit=…` o UI → si el feed ingestó el mismo `event_id`.
6. **Logs dashboard:** `EventFeedEntry projected` vs `skipped` con el mismo id/tipo.

### 3.2 Contexto operativo por instancia (Fase D)

Opcional en `.env`:

```env
PLATFORM_CLIENT_SLUG=acme-staging
```

Útil para **copiar/pegar** en tickets cuando hay varias instancias; puede añadirse manualmente al mensaje de búsqueda en logs si el equipo lo concatena en mensajes custom (hoy el core no lo agrega automáticamente a cada línea).

---

## 4. Herramientas en modo local (sin externos)

| Herramienta | Uso |
|-------------|-----|
| `laravel.log` + grep / `Select-String` | Inspección de eventos, skips y errores |
| Navegador | `/middleware`, `/dashboard` |
| `curl` / Thunder Client | APIs de métricas, cola, evento por id |
| SQLite / cliente SQL | Consultas puntuales a `bus_queue_entries`, `event_feed_entries` (si se usa SQLite en local) |
| `php artisan pail` (Laravel 11+) | Stream de logs en consola si está disponible en el proyecto |

**Ejemplo mínimo post-publicación:**

```bash
curl -s "http://127.0.0.1:8000/api/middleware/events/EVENT_UUID" | jq .
```

(sustituir URL y UUID; sin `jq`, ver JSON crudo.)

---

## 5. Estructura de logs sugerida (si se amplía en el futuro)

Hoy el sistema ya usa prefijos fijos. Convención recomendada para **nuevos** logs de packs:

```text
[EventBus][Pack.<Nombre>][<Operación>] … contextual: event_id, event_type, origin
```

Evitar PII en texto claro; usar ids y tipos.

---

## 6. Resumen: qué mirar primero cuando algo falla

| Síntoma | Primer paso |
|--------|-------------|
| 422 al publicar | Cuerpo JSON y mensaje de error; validar `payload` array |
| Cola vacía | ¿Se llamó `sync-config`? ¿`event_type` está en `subscriptions`? |
| Feed vacío | Logs `UniversalDashboardFeedListener skipped`; ¿`event_id` en payload proyectado? |
| Consumers vacíos en fila | Config `eventbus.subscriptions` vs tipo publicado |
| Errores al cargar packs | Warnings `[EventBus] Pack registrar` |

---

*Guía práctica — observabilidad local y staging; compatible con despliegue instancia-por-cliente (Fase D).*
