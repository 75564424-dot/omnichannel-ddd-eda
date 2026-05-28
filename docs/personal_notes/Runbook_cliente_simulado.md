# Runbook — Cliente simulado en local (Middleware + Dashboard)

Procedimiento operativo para **configurar** productores y consumidores, **sincronizar** el registro del middleware, **generar tráfico** y **validar** en `/middleware` y `/dashboard` sin modificar arquitectura ni código.

**Alcance:** entorno **local** (desarrollo). Mismas rutas y conceptos en otros entornos cambiando solo la URL base (HTTPS, dominio, etc., fuera del detalle de este documento).

---

## 1. Para qué sirve

- Simular un **cliente** con módulos declarados en archivos de configuración.
- Evitar confusiones por **dos fuentes de verdad**: catálogo para la UI del Dashboard vs catálogo del bus en PHP.
- Ejecutar **pruebas controladas** con pasos reproducibles y orden fijo.

---

## 2. Prerrequisitos

1. Repositorio clonado; dependencias instaladas (`composer install`; `npm install` / build del front si el equipo lo exige).
2. **`.env` de local** con `APP_KEY`, `APP_URL` coherente (p. ej. `http://127.0.0.1:8000`), y base de datos accesible.
3. **Migraciones:** `php artisan migrate` (o el procedimiento acordado por el equipo).
4. **Servidor de aplicación** en marcha: p. ej. `php artisan serve` y navegador apuntando a `APP_URL`.

### Base de datos SQLite en local

- Con **`DB_DATABASE=:memory:`**, cada **nueva conexión** de SQLite obtiene una base vacía. Las peticiones HTTP suelen abrir conexión nueva, por lo que **curl, navegador y `artisan` pueden “no verse”** los mismos datos.
- Para este runbook (validar API + UI en paralelo), use un **archivo**, p. ej. `DB_DATABASE=database/database.sqlite` (crear el archivo si hace falta) y migrar de nuevo si era la primera vez.

### Caché de configuración

- Si en local usó `php artisan config:cache`, los cambios en `config/eventbus.php` o en PHP que lee JSON **no se aplican** hasta `php artisan config:clear`.
- En desarrollo suele evitarse `config:cache` para no olvidar este paso.

---

## 3. Secuencia operativa obligatoria (orden)

Ejecute siempre en este orden lógico:

| Paso | Acción | Cuándo es obligatorio |
|------|--------|------------------------|
| A | Editar **`config/modules/modules_config.json`** | Cuando quiera cambiar lo que ve el **Dashboard** (catálogo / topología declarativa). |
| B | Editar **`config/eventbus.php`** | Cuando quiera cambiar **productores**, **suscripciones** del bus, metadatos de consumidores en cola, o el registro persistido vía sync. |
| C | `php artisan config:clear` | Solo si usa **`config:cache`** en local. |
| D | **`POST /api/middleware/registry/sync-config`** | Tras cambios en **`config/eventbus.php`** y/o **`config/modules/modules_config.json`** (vía `config('modules.catalog')`). Idempotente y sin duplicar el mismo vínculo si ambas fuentes coinciden. |
| E | Generar tráfico (HTTP publish o comandos artisan) | Después de tener config cargada; conviene **D antes de E** para que el registro persistido esté alineado con catálogo + bus. |
| F | Validar **`/middleware`** y **`/dashboard`** | Tras tráfico representativo. |

**Regla clara:** la **cola y el enrutamiento** al publicar siguen viniendo de **`eventbus.php`**. El sync además **materializa en el registry** lo declarado en **`modules_config.json`**, para que Middleware y Dashboard compartan módulos en persistencia; si solo declara módulos en JSON y no en `eventbus.subscriptions`, verá filas en registry pero los consumidores en cola pueden seguir vacíos hasta suscribir el tipo en el bus.

---

## 4. Fuentes de verdad (no negociable)

| Qué necesita | Archivo / entrada | Quién lo consume |
|--------------|-------------------|------------------|
| Catálogo declarativo (módulos en UI Dashboard) | **`config/modules/modules_config.json`** (vía `config/modules.php`) | Dashboard (`GET /api/dashboard/modules/catalog`, vistas). |
| Productores y suscripciones del bus (enrutamiento, cola) | **`config/eventbus.php`** | Publicación HTTP, `consumers` en `bus_queue_entries`, listeners. |
| Registro persistido de módulos | Tabla **`middleware_registered_modules`** | **`POST /api/middleware/registry/sync-config`**: fusiona **`config('eventbus.producers'|'subscriptions')`** y el catálogo declarativo **`config('modules.catalog')`** (origen: `modules_config.json`). |
| KPIs, gráficos, series diarias | **`config/dashboard_config.json`** | Dashboard (agregaciones y rutas JSON en payload). |

**`POST /api/middleware/registry/sync-config`** escribe el **registry** a partir de **ambas** fuentes; deduplica por productor/consumidor + `event_type`. **No** sustituye declarar suscripciones en **`eventbus.php`** si se quiere enrutamiento real al publicar.

**Coherencia recomendada:** mismos nombres de módulo y mismos `event_type` en JSON declarativo y en `eventbus.php`, para que catálogo, registry, cola y Dashboard cuadren.

---

## 5. Paso A — `modules_config.json`

- **Ruta:** `config/modules/modules_config.json`
- **Contenido:** `middleware` (objeto), `producers` y `subscribers` (arreglos), opcional `service_contact_message`.
- **Validación mínima:** cada productor/suscriptor con **`id`** y **`name`** no vacíos (filas incompletas se omiten en la API).
- **Referencias de formato:** `config/modules/producer_module.example.json`, `config/modules/consumer_module.example.json`

Tras guardar: recargar el Dashboard; ejecute **`sync-config`** si quiere que el **registry del Middleware** refleje también esos productores/suscriptores sin esperar solo a `eventbus.php`.

---

## 6. Paso B — `eventbus.php`

- **Ruta:** `config/eventbus.php`
- **Relevante para este runbook:**
  - **`producers`**: mapa de productores lógicos y `event_type` que declaran.
  - **`subscriptions`**: mapa `event_type` → lista de `['module' => 'NombreConsumidor', ...]` para enrutamiento observado y columnas de consumidores en cola.

Para ver consumidores concretos en metadatos de cola al publicar, el **`event_type` del evento** debe existir como clave en **`subscriptions`**.

---

## 7. Paso D — Sincronizar registry

**Endpoint:** `POST /api/middleware/registry/sync-config`  
**URL ejemplo:** `http://127.0.0.1:8000/api/middleware/registry/sync-config` (ajustar host/puerto a `APP_URL` / `php artisan serve`).

**Respuesta esperada:** JSON con `success: true` y `data.producer_bindings` / `data.consumer_bindings` (cantidad de vínculos **únicos** productor→tipo y consumidor→tipo tras unir eventbus + catálogo declarativo).

### Ejemplo PowerShell

```powershell
Invoke-RestMethod -Method Post -Uri "http://127.0.0.1:8000/api/middleware/registry/sync-config" -Headers @{ Accept = "application/json" }
```

### Ejemplo curl (una sola línea; Bash o `curl.exe` en Windows)

```bash
curl -s -X POST "http://127.0.0.1:8000/api/middleware/registry/sync-config" -H "Accept: application/json"
```

---

## 8. Paso E — Generar tráfico

### E.1 — HTTP `POST /api/middleware/events/publish` (recomendado para simular productor externo)

**Contrato del cuerpo (JSON):**

- Obligatorios: `event_id`, `event_type`, `payload` (objeto/array asociativo), `occurred_at`.
- Opcional: `origin`.

**Comportamiento interno resumido:**

1. `EventPublisherService` valida el sobre, resuelve consumidores desde **`eventbus.subscriptions`**, crea fila **`PENDING`** en `bus_queue_entries`, luego hace `Event::dispatch($event_type, [$payload])`.
2. El listener de tracking, si encuentra la misma fila por `event_id`, la pasa a **`PROCESADO`**.

**Sobre `payload`:** al bus se envía **solo** el contenido de `payload`. Es recomendable incluir al menos `event_id` (y de ser posible `event` o `event_type` y `occurred_at`) **dentro** de `payload` para que el **feed del Dashboard** y otras proyecciones que lean el cuerpo dispongan de esos campos.

Ejemplo mínimo (`curl.exe` / Bash):

```bash
curl -s -X POST "http://127.0.0.1:8000/api/middleware/events/publish" -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"event_id\":\"11111111-1111-4111-8111-111111111111\",\"event_type\":\"Demo.Cliente.Pedido\",\"occurred_at\":\"2026-05-03T12:00:00+00:00\",\"origin\":\"ClienteSimulado\",\"payload\":{\"event_id\":\"11111111-1111-4111-8111-111111111111\",\"event\":\"Demo.Cliente.Pedido\",\"event_type\":\"Demo.Cliente.Pedido\",\"occurred_at\":\"2026-05-03T12:00:00+00:00\",\"channel\":\"WEB\"}}"
```

Sustituya `event_type` por uno declarado en `eventbus.subscriptions` si necesita consumidores concretos en la fila.

### E.2 — `php artisan platform:emit-mock`

- Opción: `--type=NombreDelEvento` (por defecto `PlatformPing`).
- Hace **`Event::dispatch` directo**; **no** pasa por `EventPublisherService`, por tanto **no** crea la fila `PENDING` previa.
- El tracking puede registrar una fila **`PROCESADO`** si el payload incluye `event_id` (el comando ya lo genera).
- **Uso:** humo rápido del bus y del feed; para paridad con productor HTTP use **E.1**.

### E.3 — `php artisan platform:demo-dashboard-events`

- Opciones: `--count=0..50` (por defecto 5), `--bus-rows` (inserta filas ilustrativas en `bus_queue_entries` para gráficos que leen cola).
- Emite **`Platform.Demo.Measurement`** con payload que incluye `measurement.amount`.
- Para que la **serie diaria** del Dashboard reaccione, el tipo debe coincidir con **`daily_series.event_type`** en **`config/dashboard_config.json`** (en el estado actual del proyecto: `Platform.Demo.Measurement`).

---

## 9. Paso F — Validar Middleware (`/middleware`)

Abrir **`/middleware`** (según `APP_URL`).

**Revisar:**

- Métricas y estado del bus (latencia, EPS, error rate, dead letters según pantalla).
- Topología (productores/consumidores, tráfico observado): depende de `eventbus.php`, registro sincronizado y eventos recientes con `event_id` válido.
- Tabla de cola: filas nuevas tras publicar; transición coherente con el flujo publish (`PENDING` → `PROCESADO`) o filas directamente `PROCESADO` desde CLI si no hubo fila previa.

Si no hay suscripciones para el `event_type` publicado, la fila puede mostrar lista de consumidores vacía: es coherente con la configuración.

---

## 10. Paso F — Validar Dashboard (`/dashboard`)

Abrir **`/dashboard`**.

**Revisar:**

- **Catálogo / módulos:** proviene de **`modules_config.json`**, no del sync.
- **KPIs y gráficos:** definidos en **`config/dashboard_config.json`**; requieren eventos compatibles (tipo, rutas en payload).
- **Feed:** suele esperar payloads con **`event_id`** presente en el cuerpo que se proyecta; si publica por HTTP, mantenga `event_id` dentro de `payload` como en el ejemplo.

**Divergencia esperada si solo editó JSON:** el Dashboard puede verse “completo” en catálogo mientras el Middleware muestra cola/registro incompletos hasta alinear `eventbus.php`, ejecutar sync y publicar con tipos suscritos.

---

## 11. Publicar vs `platform:emit-mock` (resumen)

| Aspecto | `POST .../events/publish` | `platform:emit-mock` |
|--------|---------------------------|----------------------|
| Pasa por `EventPublisherService` | Sí | No |
| Fila `PENDING` antes del dispatch | Sí | No |
| Consumidores en cola desde `eventbus.subscriptions` | Sí (al crear la fila) | Se calculan en tracking si crea fila nueva |
| Similitud con productor externo por HTTP | Alta | Baja |

---

## 12. Problemas frecuentes

| Síntoma | Causa probable | Acción |
|--------|----------------|--------|
| Dashboard muestra módulos; cola sin consumidores esperados | Suscripción solo en JSON, no en **`eventbus.subscriptions`** | Añadir el `event_type` en **`eventbus.php`**, `config:clear` si aplica, **sync-config**, volver a publicar. El sync ya puede poblar registry desde el JSON, pero la cola usa solo el bus. |
| `sync-config` no crea vínculos | `producers` / `subscriptions` vacíos o claves distintas | Revisar `eventbus.php`; repetir sync. |
| `publish` responde **422** | Faltan campos obligatorios o `payload` no es objeto/array | Ajustar JSON al contrato (sección 8). |
| Topología o cola “vacía” | Sin eventos recientes con `event_id`, o tipos sin actividad | Ejecutar paso E; verificar `event_type` y payload. |
| Cambió config y no ve efecto | `config:cache` | `php artisan config:clear` y repetir. |
| Feed del Dashboard vacío | `event_id` ausente en el payload proyectado | Incluir `event_id` dentro de `payload` en publish o usar comandos que ya lo envían. |
| Datos “desaparecen” entre terminal y navegador | SQLite **`:memory:`** por conexión | Cambiar a **archivo** `.sqlite` para pruebas integradas. |

---

## 13. Checklist de cierre (Fase A local)

- [ ] **`modules_config.json`** editado (JSON válido) si el escenario incluye catálogo en Dashboard.
- [ ] **`eventbus.php`** alineado con ese escenario (`producers` / `subscriptions`).
- [ ] **`config/dashboard_config.json`** revisado si se prueban series/KPIs (p. ej. `daily_series.event_type`).
- [ ] `php artisan config:clear` **solo si** usa `config:cache`.
- [ ] **`POST /api/middleware/registry/sync-config`** con `success: true` **tras** cambios en `eventbus.php` y/o `modules_config.json`.
- [ ] Al menos un evento: **`POST .../publish`** (recomendado para paridad) y/o comandos de demo según el caso.
- [ ] **`/middleware`**: cola y topología coherentes con config + tráfico.
- [ ] **`/dashboard`**: catálogo coherente con JSON; feed/KPIs coherentes con `dashboard_config.json` y tipos emitidos.

---

## 14. Nota sobre documentos heredados

Procedimientos más antiguos bajo `docs/personal_notes/` pueden existir con el mismo espíritu operativo; **este archivo en `docs/Runbook_cliente_simulado.md`** es la referencia canónica para el flujo Middleware + Dashboard en local.

---

*Runbook operativo — cliente simulado. Solo documentación; sin cambios de arquitectura ni de código.*
