# Runbook — Fase A: simulación de cliente en local (módulos + validación Middleware / Dashboard)

Este documento es el **procedimiento operativo** para configurar productores y consumidores declarativos, sincronizar el registro del middleware, generar tráfico de prueba y validar el flujo en la UI **sin cambiar la arquitectura del proyecto**. Complementa la matriz de fuentes de verdad para evitar inconsistencias entre `modules_config.json` y `eventbus.php`.

**Alcance:** entorno **local** (desarrollo). Las mismas rutas y conceptos aplican en otros entornos; la sección “Preparación futura” indica qué aspectos se revisarán al desplegar en la nube, con dominio, HTTPS o base de datos externa, **sin instrucciones de implementación** en este runbook.

---

## 1. Introducción breve

**Qué es:** un runbook paso a paso para que un desarrollador **simule un cliente**: definir módulos en configuración, alinear el registro interno del bus con el catálogo de Laravel, publicar eventos y comprobar que **Middleware** y **Dashboard** reflejan el escenario esperado.

**Para qué sirve:** ejecutar **pruebas controladas tipo producción** en máquina local, reduciendo errores por confundir **dos fuentes de configuración** (catálogo declarativo para UI vs. catálogo de suscripciones del bus).

**Alcance:** solo operación sobre el estado actual del código. No introduce capas ni servicios nuevos. Está **preparado conceptualmente** para escalar a nube (variables de entorno, URL pública, TLS, BD gestionada) como evolución documentada, no como tarea de este documento.

---

## 2. Prerrequisitos

Antes de seguir el flujo operativo:

1. **Repositorio clonado** y dependencias instaladas (`composer install`, `npm install` si compila front).
2. **Archivo `.env`** configurado para **local**: al menos `APP_KEY`, `APP_URL` (p. ej. `http://127.0.0.1:8000`), conexión a base de datos y colas coherentes con tu entorno (p. ej. `DB_CONNECTION=sqlite` con `DB_DATABASE=:memory:` o archivo, o MySQL local).
3. **Migraciones aplicadas:** `php artisan migrate` (o el flujo que use el equipo en local).
4. **Aplicación servida** para probar UI y API, p. ej. `php artisan serve` y abrir en navegador la `APP_URL` indicada.

**Nota sobre configuración en caché:** si en local ejecutaste `php artisan config:cache`, los cambios en `config/eventbus.php` o en PHP que lee JSON pueden no verse hasta `php artisan config:clear`. En muchos entornos de desarrollo se trabaja **sin** `config:cache` para evitar este olvido.

**Preparación futura (sin implementar aquí):** en despliegues posteriores se suele ajustar `.env` con URL pública, credenciales de BD en la nube, y opciones TLS detrás de un proxy. Este runbook **no** detalla esos pasos; solo deja explícito que el flujo (mismos endpoints y archivos de config) es el mismo y que la **URL base** de las peticiones dejará de ser `localhost`.

---

## 3. Flujo operativo paso a paso

Ejecutar los pasos **en orden**. Tras editar archivos de configuración PHP/JSON, asegurarse de que la aplicación recargue la configuración según tu uso de `config:cache` (véase prerrequisitos).

### Paso 1 — Configurar módulos (declarativo + bus)

Hay **dos lugares** que debes mantener alineados conceptualmente (véase la matriz en la sección 4).

**1a. `config/modules/modules_config.json`**

- **Ruta en repo:** `config/modules/modules_config.json`
- **Qué define:** el **catálogo declarativo** que consume el Dashboard: nodo `middleware`, arreglos `producers` y `subscribers` (presentación / topología en UI), y opcionalmente `service_contact_message`.
- **Formato:** JSON válido; cada productor/suscriptor suele incluir identificadores y metadatos que la aplicación expone en `GET /api/dashboard/modules/catalog` (no se edita desde la UI).

Edita este archivo para reflejar **quién publica** y **quién escucha** en el modelo que quieres mostrar al operador en el Dashboard.

**1b. `config/eventbus.php`**

- **Ruta en repo:** `config/eventbus.php`
- **Qué define para este runbook:**
  - `producers`: mapa de productores lógicos y los `event_type` que declaran.
  - `subscriptions`: mapa `event_type` → lista de filas con clave `module` (nombre del consumidor) para **enrutamiento observado** y para el **endpoint de sincronización** del registro.
- El core puede arrancar con listas vacías; para simular un cliente con consumidores en cola y topología observada, **debes** declarar aquí los `event_type` y módulos consumidores que quieras ver reflejados al publicar eventos.

**Coherencia recomendada:** los mismos nombres de módulos y tipos de evento que uses en el JSON declarativo deberían existir en `eventbus.php` para que Dashboard y Middleware no muestren historias distintas.

---

### Paso 2 — Sincronizar configuración (registry)

**Endpoint (existente):** `POST /api/middleware/registry/sync-config`  
**URL ejemplo en local:** `http://127.0.0.1:8000/api/middleware/registry/sync-config` (ajustar host y puerto a tu `php artisan serve`).

**Propósito:** persistir en la tabla de registro de módulos (`middleware_registered_modules`) la información derivada de **`config('eventbus.producers')`** y **`config('eventbus.subscriptions')`**. Es decir, **no lee** directamente `modules_config.json`; alinea el **registry** con el catálogo del **event bus** en PHP.

**Ejemplo con `curl`:**

```bash
curl -s -X POST "http://127.0.0.1:8000/api/middleware/registry/sync-config" ^
  -H "Accept: application/json"
```

(En PowerShell puedes usar `curl.exe` con las mismas opciones o `Invoke-WebRequest -Method Post`.)

Respuesta esperada: JSON con `success: true` y contadores en `data` (enlaces productor/consumidor registrados).

Si omites este paso tras cambiar `eventbus.php`, el registro persistido puede quedar desactualizado respecto al catálogo que ves en código.

---

### Paso 3 — Generar eventos de prueba

Objetivo: crear **tráfico observable** (feed, cola, topología observada). Puedes usar **una** de estas vías (todas existen en el proyecto).

**Opción A — HTTP publish (recomendado para simular productor externo)**

- **Endpoint:** `POST /api/middleware/events/publish`
- **Cuerpo JSON (campos requeridos):** `event_id`, `event_type`, `payload` (objeto/array), `occurred_at`. Opcional: `origin`.

El servicio valida la estructura, escribe la fila de tracking y despacha el evento string con Laravel. El arreglo `payload` debe ser el **sobre interno** que recibirán los listeners (convención habitual: incluir de nuevo `event_id`, `event`, `event_type`, `occurred_at` y el cuerpo útil).

**Ejemplo mínimo (`curl`):**

```bash
curl -s -X POST "http://127.0.0.1:8000/api/middleware/events/publish" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"event_id\":\"11111111-1111-4111-8111-111111111111\",\"event_type\":\"Demo.Cliente.Pedido\",\"occurred_at\":\"2026-05-03T12:00:00+00:00\",\"origin\":\"ClienteSimulado\",\"payload\":{\"event_id\":\"11111111-1111-4111-8111-111111111111\",\"event\":\"Demo.Cliente.Pedido\",\"event_type\":\"Demo.Cliente.Pedido\",\"occurred_at\":\"2026-05-03T12:00:00+00:00\",\"channel\":\"WEB\"}}"
```

Sustituye `event_type` por uno que hayas declarado en `eventbus.subscriptions` si quieres ver consumidores concretos en metadatos de cola.

**Opción B — Comando `platform:emit-mock`**

- **Comando:** `php artisan platform:emit-mock`
- **Opción:** `--type=NombreDelEvento` (por defecto `PlatformPing`)
- Despacha por el bus de Laravel un sobre con `event_id`, `event`, `event_type`, `channel`, `occurred_at`. Útil para humo rápido del feed.

**Opción C — Comando `platform:demo-dashboard-events`**

- **Comando:** `php artisan platform:demo-dashboard-events`
- **Opciones:** `--count=N` (0–50), `--bus-rows` (inserta filas ilustrativas en cola para gráficos de origen/consumidor en el Dashboard)

Usa el tipo de evento **Platform.Demo.Measurement** definido en el comando; alinéalo con `dashboard_config.json` si pruebas KPIs.

---

### Paso 4 — Validar en Middleware (`/middleware`)

Abre en el navegador la ruta web **`/middleware`** (según `APP_URL`).

**Qué revisar:**

- **Métricas** (latencia, EPS, error rate, dead letters): deben actualizarse o permanecer coherentes según tráfico reciente.
- **System topology:** productores y consumidores deben reflejar el **snapshot** servido por `GET /api/middleware/topology` (combinación de configuración de `eventbus` y observaciones de tráfico en registros).
- **Cola / tabla de eventos:** nuevas filas tras `publish` o comandos; estados observables según el procesamiento.
- Si usaste **sync-config**, el botón de sincronización debería haber contribuido a que el **registry** coincida con el catálogo PHP antes del tráfico.

Si no enviaste eventos con `event_id` válido o no hay suscripciones declaradas donde las esperas, la topología “observada” puede permanecer vacía o incompleta: es comportamiento esperado del diseño actual.

---

### Paso 5 — Validar en Dashboard (`/dashboard`)

Abre **`/dashboard`**.

**Qué revisar:**

- **Tarjetas KPI y gráficos:** dependen de `config/dashboard_config.json` y de datos en tablas de feed/cola; tras publicar eventos compatibles con métricas configuradas, deberías ver actividad o series según agregaciones definidas.
- **Topología / catálogo de módulos:** debe reflejar lo declarado en **`modules_config.json`** (productores, suscriptores, middleware), obtenido vía API interna del Dashboard.
- **Feed de eventos:** eventos con `event_id` proyectados al read model aparecen en la tabla de feed si pasan las reglas de ingestión del listener de feed.

**Qué debe coincidir:** nombres de módulos y tipos de evento entre lo que mostró el Dashboard (declarativo) y lo que viste en Middleware (cola + topología) **solo si** editaste **ambas** fuentes de forma coherente en el Paso 1. Si solo editaste el JSON, el Dashboard puede verse “completo” mientras el Middleware aún muestra consumidores vacíos hasta que `eventbus.php` y el tráfico estén alineados.

---

## 4. Matriz de fuentes de verdad (crítico)

| Elemento | Fuente principal | Uso |
|----------|------------------|-----|
| Topología **declarativa** en UI Dashboard (módulos conectados al bus en la vista de catálogo) | **`config/modules/modules_config.json`** (expuesto vía `config/modules.php`) | Lectura por el Dashboard; describe el modelo “documentado” del cliente. |
| Suscripciones in-process y productores para el **bus** (lista de consumidores por `event_type`, productores declarados) | **`config/eventbus.php`** | Publicación (`EventPublisherService`), tracking de `consumers` en cola, **entrada de `POST /registry/sync-config`**. |
| Filas persistidas de módulos tras sync | **Tabla `middleware_registered_modules`** + uso de **`POST /api/middleware/registry/sync-config`** | Historial / registro alineado con **`eventbus.php`**, no con el JSON del Dashboard. |
| KPIs, tarjetas, series temporales, texto de sobre | **`config/dashboard_config.json`** + `config/dashboard.php` | Comportamiento del Dashboard sin tocar lógica de negocio. |
| Cola observada, métricas del bus, topología API del Middleware | **API bajo `/api/middleware/*`** + datos en tablas operativas (`bus_queue_*`, etc.) | Vista técnica `/middleware`; refleja tráfico real y config de `eventbus`. |

Regla práctica: **el Dashboard “cree” al JSON declarativo; el Middleware de control “cree” al `eventbus.php` y al tráfico.** Mantener ambos alineados es responsabilidad del operador en este estado del sistema.

---

## 5. Consideraciones de entorno

### Entorno actual (LOCAL)

- Todo el flujo anterior se ejecuta en **máquina de desarrollo**.
- Base de datos y colas según `.env` (p. ej. SQLite, MySQL local, `QUEUE_CONNECTION=sync` o database/redis según el proyecto).
- **Sin** obligación de dominio propio ni HTTPS en origen; `http://127.0.0.1:puerto` es suficiente.
- Rutas relativas a la aplicación: `/middleware`, `/dashboard`, prefijo API `/api/middleware/...`, `/api/dashboard/...`.

### Preparación futura (NO implementar en este runbook)

- Se prevé que el mismo flujo se ejecute contra una **URL pública** (`APP_URL`, CORS, proxies).
- Se prevé **HTTPS** terminado en balanceador o servidor web (los ejemplos `curl` pasarían a `https://` y certificados de confianza).
- Se prevé **base de datos y colas gestionadas** en la nube cambiando solo variables de entorno y conectividad, **sin** alterar los nombres de endpoints documentados aquí.

No se incluyen playbooks de despliegue, Terraform, SSL ni proveedores concretos.

---

## 6. Problemas comunes y errores esperados

| Síntoma | Causa probable | Qué hacer |
|--------|----------------|-----------|
| Dashboard muestra módulos nuevos; Middleware no muestra consumidores en cola | Solo se editó **`modules_config.json`**, no **`eventbus.subscriptions`** | Actualizar `eventbus.php` y repetir publish + sync si aplica. |
| `sync-config` no crea vínculos esperados | **`producers`** / **`subscriptions`** vacíos o con tipos distintos a los esperados | Revisar arrays en `eventbus.php`; ejecutar de nuevo `POST /registry/sync-config`. |
| `publish` responde 422 | Faltan `event_id`, `event_type`, `payload` o `occurred_at`, o `payload` no es objeto | Ajustar cuerpo JSON al contrato del controlador. |
| No aparece topología “observada” | Sin eventos con `event_id` o sin actividad que alimente listeners de observación | Ejecutar Paso 3; verificar tipo de evento y canal. |
| Cambié config y no veo diferencias | **`config:cache`** en local | `php artisan config:clear` y repetir prueba. |
| Feed del Dashboard vacío | Eventos sin `event_id` en el sobre, o listener de feed omitiendo entradas | Usar publish con payload que incluya `event_id` coherente con el validador del feed. |

---

## 7. Checklist de validación (pruebas controladas)

- [ ] **`modules_config.json`** editado y JSON válido (productores/suscriptores/middleware según escenario del cliente simulado).
- [ ] **`eventbus.php`** actualizado de forma coherente (`producers` / `subscriptions` alineados con ese escenario).
- [ ] `php artisan config:clear` ejecutado **si** usas `config:cache` en local.
- [ ] **`POST /api/middleware/registry/sync-config`** ejecutado y respuesta `success: true`.
- [ ] Al menos un evento generado: **`POST /api/middleware/events/publish`** o `php artisan platform:emit-mock` o `php artisan platform:demo-dashboard-events`.
- [ ] **`/middleware`**: métricas/col/topología revisadas; coherente con `eventbus` + tráfico.
- [ ] **`/dashboard`**: catálogo/ topología coherente con **`modules_config.json`**; feed o KPIs según eventos y `dashboard_config.json`.

Cuando todos los ítems están marcados, la simulación del cliente en local está **cerrada** para esta Fase A, sin modificar la arquitectura del repositorio.

---

*Documento operativo — Fase A (runbook + matriz de fuentes de verdad). Alineado al código y rutas existentes en el proyecto platform/event-bus-core.*
