# Plan de Desarrollo Técnico — Módulo: Dashboard (Observabilidad)

> **Producto:** Complemento de **observabilidad** de la plataforma core  
> **Versión del plan:** 2.0  
> **Fecha:** 2026-05  
> **Alcance:** Bounded context `app/Dashboard` — **read models** y UI; sin dominios de negocio embebidos

> **Dependencia:** este módulo **presupone** el [Middleware](./Plan_Modulo_Control_Middleware.md): las métricas de motor, la cola analizada y el registro de eventos observados provienen del mismo tráfico y tablas operativas. En la **oferta estándar del servicio**, Middleware y Dashboard se entregan **juntos** (véase [README.md](./README.md)).

---

## Índice

1. [Rol respecto al Middleware](#1-rol-respecto-al-middleware)
2. [Identificación del módulo](#2-identificación-del-módulo)
3. [Objetivo técnico](#3-objetivo-técnico)
4. [Componentes a desarrollar](#4-componentes-a-desarrollar)
5. [Ingesta de señales (eventos)](#5-ingesta-de-señales-eventos)
6. [Flujo del módulo](#6-flujo-del-módulo)
7. [Configuración sin código](#7-configuración-sin-código)
8. [Estructura de carpetas (implementación real)](#8-estructura-de-carpetas-implementación-real)
9. [Dependencias](#9-dependencias)
10. [Consideraciones de implementación](#10-consideraciones-de-implementación)
11. [Riesgos técnicos](#11-riesgos-técnicos)

---

## 1. Rol respecto al Middleware

| Pregunta | Respuesta |
|----------|-----------|
| ¿Es el Dashboard un producto independiente? | **No** en esta línea: depende de datos/APIs del mismo host que ejecuta el Middleware. |
| ¿Puede un cliente usar solo Middleware? | **Sí (modo headless / API-only)** — sin obligar a usuarios finales a abrir la UI; el Dashboard sigue siendo parte del **artefacto** desplegable. |
| ¿Qué aporta entonces? | **Consolidación visual**: feed, KPIs configurables, topología declarativa, nodos, SSE/stream opcional — todo sobre **read models**, no sobre BDs de verticales externos. |
| ¿Acoplamiento permitido? | Solo hacia **contratos estables**: repositorios de lectura, APIs `/api/dashboard/*`, tablas `event_feed_*`, `bus_queue_*`, métricas de middleware — **no** llamadas directas a módulos de negocio de terceros. |

---

## 2. Identificación del módulo

| Atributo | Valor |
|----------|--------|
| **Nombre** | Dashboard — Observabilidad y métricas |
| **Dominio (DDD)** | Supporting — Monitoring / Observability context |
| **Tipo** | Consumer pasivo + proyección a read models |
| **Stack** | Laravel + Vue/Inertia (`/dashboard`) |

### Naturaleza

- **Query-side / CQRS:** no es fuente de verdad de negocio.
- Las cifras son **eventualmente consistentes** con el tráfico observado.
- La presentación es **agnóstica del vertical**: no “Ventas/Inventario/Pedidos” como requisito del core; esos nombres pueden aparecer solo como datos de un cliente concreto en eventos o config.

---

## 3. Objetivo técnico

| # | Objetivo |
|---|----------|
| O1 | Mostrar **feed** de eventos observados (sobre con `event_id`). |
| O2 | Mostrar **KPIs** y gráficos definidos en configuración JSON, no hardcodeados por dominio. |
| O3 | Exponer **métricas del bus** (latencia, EPS, cola, estado) en paneles alineados al Middleware. |
| O4 | Representar **topología** (productores / suscriptores / middleware) desde `modules_config.json` + datos observados cuando existan. |
| O5 | Opcional: **stream** (SSE) para actualización en vivo del feed. |

---

## 4. Componentes a desarrollar

### 4.1 Casos de uso (Application)

Representativos (pueden extenderse sin romper el rol de observador):

| Caso de uso | Descripción |
|-------------|-------------|
| `GetRecentEventFeedUseCase` | Últimas entradas del feed. |
| `GetGlobalMetricsUseCase` | Tarjetas KPI desde agregaciones configuradas. |
| `GetDynamicMetricSeriesUseCase` | Series para gráficos según `dashboard_config.json`. |
| `GetDashboardMetricCatalogUseCase` | Lista habilitada de métricas para la UI. |
| `GetConfiguredDailySeriesUseCase` | Serie diaria opcional desde config (p. ej. suma por día sobre path JSON del payload). |
| `GetMiddlewareBusMetricsUseCase` | Panel “engine” alimentado por snapshots/read models del bus. |
| `GetSystemNodeStatusUseCase` | Estado de nodos monitoreados (`dashboard.monitored_node_keys`). |
| `GetEventFlowDiagramDataUseCase` | Grafo derivado del **feed reciente** (orígenes → bus), no de un diagrama estático de negocio. |
| `GetModulesCatalogUseCase` | Catálogo declarativo para UI. |
| `StreamLiveEventsUseCase` | Soporte SSE. |

### 4.2 Listeners / proyección

| Patrón | Descripción |
|--------|-------------|
| **Listener genérico** (`UniversalDashboardFeedListener`) | Proyecta al feed cualquier evento que cumpla el contrato mínimo (`event_id`, etc.), sin una clase por cada `VentaRealizada` del vertical. |
| **Listener de métricas** (`MiddlewareMetricsListener`) | Refresca snapshots de métricas del bus en función de señales observadas (diseño actual del core). |

> **Obsoleto en el core:** una matriz de listeners “uno por evento de retail” como requisito del producto base.

### 4.3 Read models principales

| Read model | Origen típico |
|------------|----------------|
| `EventFeedEntry` | Proyección desde eventos observados |
| `MiddlewareBusMetrics` | Snapshots / tablas de métricas del motor |
| `NodeStatusSnapshot` | Panel de nodos (middleware y extensiones declaradas) |
| `EventFlowDiagramData` | Construcción dinámica desde feed + estado |

### 4.4 Frontend

| Ruta | Vista |
|------|--------|
| `/dashboard` | `Dashboard/Index` — KPIs configurables, gráfico dinámico, topología, feed, nodos, motor |

Componentes Vue alineados a la implementación (nombres pueden variar): topología (`EventFlowTopology`), panel de módulos, tablas de feed, etc.

---

## 5. Ingesta de señales (eventos)

### Contrato mínimo para aparecer en feed

- Presencia de **`event_id`** válido.
- Tipo de evento identificable (`event` / `event_type`).
- Cumplimiento de **gates** de ingestión si el host define `dashboard.ingestion_gates` (opcional).

### Qué NO hace el Dashboard

- No valida reglas de negocio sobre SKUs, stock, descuentos, etc.
- No escribe en sistemas externos.
- No reemplaza al Middleware como bus.

---

## 6. Flujo del módulo

```text
[Evento observado en el host]
        │
        ▼
[UniversalDashboardFeedListener u otros hooks]
        │
        ▼
[EventFeedProjector → Read Store (event_feed_entries)]
        │
        ├──► GET /api/dashboard/events/feed
        ├──► GET /api/dashboard/metrics
        ├──► GET /api/dashboard/metrics/series/{id}
        └──► Opcional: GET /api/dashboard/stream (SSE)

[Métricas de bus / cola]
        │
        ▼
[Repositorios de analytics sobre tablas bus_*]
        │
        └──► Paneles "Engine" + gráficos dual-bar (origen/consumidor) según config
```

---

## 7. Configuración sin código

| Archivo | Propósito |
|---------|-----------|
| `config/dashboard_config.json` | KPIs (`counter_cards`), mégricas (`metrics`), sobre documental (`event_envelope`), serie diaria opcional (`daily_series`). |
| `config/dashboard.php` | Fusión PHP + defaults (`monitored_node_keys`, hooks, UI). |
| `config/modules/modules_config.json` | Topología declarativa (middleware, producers, subscribers, mensaje de contacto). |

---

## 8. Estructura de carpetas (implementación real)

```text
app/Dashboard/
├── Application/
│   ├── Contracts/
│   ├── DTOs/
│   └── UseCases/
├── Domain/
│   ├── Hooks/
│   ├── ReadModels/
│   ├── Repositories/
│   └── ValueObjects/
├── Infrastructure/
│   ├── Models/
│   ├── Modules/
│   ├── Persistence/
│   └── Projectors/
├── Interfaces/
│   ├── Http/Controllers/
│   ├── Providers/
│   └── Routes/
└── Listeners/
```

---

## 9. Dependencias

| Dependencia | Motivo |
|-------------|--------|
| Middleware / tablas compartidas | Analytics de cola, correlaciones feed ↔ bus |
| `config/*.json` | Comportamiento de producto sin redeploy de lógica |
| Laravel Events | Ingesta de señales vía dispatcher |

### Prohibido

- Acceso directo a esquemas de negocio de verticales externos (Ventas/Inventario/OMS) como fuente del dashboard core.

---

## 10. Consideraciones de implementación

- **Asincronía:** los listeners pueden ser síncronos o asíncronos según decisión del host; el plan favorece **no bloquear** el dispatcher (evaluar cola dedicada para proyecciones si el volumen lo exige).
- **Idempotencia:** `event_id` único en `event_feed_entries`.
- **Consistencia eventual:** timestamps y mensajes en UI deben comunicar que es una **vista operativa**, no contabilidad legal.
- **SSE vs WebSocket:** SSE suele bastar para lectura; documentar límites de conexiones.

---

## 11. Riesgos técnicos

| # | Riesgo | Mitigación |
|---|--------|------------|
| R1 | Alto volumen en feed | Paginación, ventanas, throttle de broadcast. |
| R2 | Lag de proyección | Colas de métricas separadas, monitorizar retraso. |
| R3 | Payload cambia en integrador | Paths configurables tolerantes; `impact_hint` opcional en sobre. |
| R4 | Muchas conexiones SSE | Canales compartidos, límites, escalado horizontal del worker HTTP. |
| R5 | Usuario confunde dashboard con fuente de verdad | Documentación / tooltips de “operational view”. |

---

## Resumen ejecutivo

| Ítem | Valor |
|------|--------|
| **Rol** | **Complemento** de observabilidad — no vendible como sustituto del Middleware |
| **Empaquetado** | Incluido en oferta estándar **con** Middleware |
| **Dominio negocio** | Ninguno en el core — configurable / externo |
| **Listeners** | **Genéricos** / wildcard, no matriz rígida por evento de retail |

---

*Fin del plan — Dashboard v2.0 (complemento del Middleware + configuración como producto).*
