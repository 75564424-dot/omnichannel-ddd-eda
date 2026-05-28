# Plan de Desarrollo Técnico — Módulo: Middleware (Event Bus / Plataforma de integración)

> **Producto:** Plataforma core de integración por eventos (servicio) — **Middleware como solución principal**  
> **Versión del plan:** 2.0  
> **Fecha:** 2026-05  
> **Alcance:** Documenta el bounded context `app/Middleware` — sin lógica de negocio de verticales (retail, OMS, etc.)

> **Relación con el Dashboard:** el Middleware es el **núcleo**; el [Plan del Dashboard](./Plan_Modulo_Dashboard_General.md) describe el **complemento de observabilidad** que consume señales y datos del bus. En la oferta estándar del servicio se entregan **juntos** (véase [README.md](./README.md)).

---

## Índice

1. [Posicionamiento como servicio](#1-posicionamiento-como-servicio)
2. [Identificación del módulo](#2-identificación-del-módulo)
3. [Objetivo técnico](#3-objetivo-técnico)
4. [Componentes a desarrollar](#4-componentes-a-desarrollar)
5. [Eventos y catálogo](#5-eventos-y-catálogo)
6. [Flujo del módulo](#6-flujo-del-módulo)
7. [Estructura de carpetas (implementación real)](#7-estructura-de-carpetas-implementación-real)
8. [Dependencias y configuración](#8-dependencias-y-configuración)
9. [Consideraciones de implementación](#9-consideraciones-de-implementación)
10. [Riesgos técnicos](#10-riesgos-técnicos)

---

## 1. Posicionamiento como servicio

| Aspecto | Descripción |
|---------|-------------|
| **Qué se vende / entrega** | Capacidad de **rutear y observar** eventos entre sistemas externos (productores y consumidores) con trazabilidad operativa. |
| **Independencia del tenant** | El core no asume nombres de eventos de un cliente concreto; los **tipos de evento** y **suscriptores** se declaran por **configuración** y/o **paquetes de integración** que el host registra. |
| **Modo headless** | Los integradores pueden usar únicamente APIs (`/api/middleware/...`) y el modelo de publicación de eventos, sin exponer UI al usuario final. |
| **Complemento UI** | La consola web `/middleware` — monitoreo técnico del bus — forma parte del producto; el Dashboard global (`/dashboard`) amplía observabilidad y métricas configurables en la misma plataforma. |

---

## 2. Identificación del módulo

| Atributo | Valor |
|----------|--------|
| **Nombre** | Middleware — Event Bus, registro y observabilidad de tránsito |
| **Dominio (DDD)** | Supporting / Generic infrastructure (plataforma de integración) |
| **Tipo** | Infraestructura de mensajería **sin** dominio de negocio embebido |
| **Stack** | Laravel (PHP) — APIs REST; frontend de control en Vue/Inertia según el host |

### Naturaleza arquitectónica

- Es el **punto de encuentro** entre publicadores y suscriptores: reduce acoplamiento punto a punto.
- **No interpreta** el significado del `payload` salvo validaciones mínimas de sobre (p. ej. `event_id`, `event_type`, timestamps donde aplique).
- **No aplica** reglas de negocio ni transforma el payload por tipo de evento “conocido” en el core.

---

## 3. Objetivo técnico

### Capabilities

| # | Capability |
|---|------------|
| C1 | Recibir eventos desde productores (HTTP publish, jobs, calls internos al `Event` facade del host). |
| C2 | Registrar el tránsito en almacenamiento operativo (cola observada, métricas, dead letters según diseño). |
| C3 | Exponer **consultas** de cola, topología, estado del bus, búsqueda por `event_id`. |
| C4 | Mantener un **registro declarativo** de suscripciones (`config/eventbus.php` fusionado por packs) para armar metadatos de consumidores en `QueueEntry` y vistas de topología. |
| C5 | Observar tráfico **wildcard** a nivel de plataforma (listeners registrados sobre `*`) para no depender de un catálogo fijo en el repositorio core. |

### Problemas que resuelve

| Problema | Enfoque |
|----------|---------|
| Integraciones N×N | Canal común + contrato de sobre |
| Falta de trazabilidad | Persistencia de entradas de cola / estados |
| Operación a ciegas | APIs de métricas, dead letters, topología |

---

## 4. Componentes a desarrollar

### 4.1 Capa de aplicación (servicios y casos de uso)

**Servicios típicos (alineado al código de referencia):**

| Servicio | Responsabilidad |
|----------|-----------------|
| `EventPublisherService` | Publicación validada hacia el bus / persistencia `PENDING` según flujo del host. |
| `SubscriptionRegistryService` | Resolución de consumidores por `event_type` desde la configuración fusionada (lectura **no cacheada** del config en runtime). |
| `BusMetricsService` / `BusHealthService` | Snapshots operativos y estado del bus. |
| `TopologyService` | Ensambla vista de topología (config + observaciones en registro de módulos). |

**Casos de uso (API de control):**

| Caso de uso | Descripción |
|-------------|-------------|
| `GetBusMetricsUseCase` | Métricas agregadas para operación. |
| `GetEventQueueUseCase` | Vista de cola / historial reciente. |
| `GetTopologySnapshotUseCase` | Productores / bus / consumidores. |
| `SearchEventByIdUseCase` | Trazabilidad por `event_id`. |
| `GetBusStatusUseCase` | Estado global del bus. |
| `GetDeadLetterQueueUseCase` | Errores definitivos. |
| `SyncConfiguredModulesToRegistryUseCase` | Sincroniza catálogo declarativo hacia persistencia de registro (cuando aplica). |

> **Nota:** El **despacho** a listeners de Laravel es responsabilidad del **dispatcher** del framework; el plan del Middleware se centra en **tracking**, **APIs** y **reglas de infraestructura**, no en reimplementar un dispatcher paralelo.

### 4.2 Dominio (infraestructura del bus)

**Entidades / agregados de infraestructura (conceptuales):**

| Concepto | Uso |
|----------|-----|
| `EventEnvelope` | Sobre en tránsito: identificador, tipo, payload, metadatos. |
| `QueueEntry` | Registro de una instancia observada en el bus. |
| `DeadLetterEntry` | Evento / trabajo que agotó reintentos (o equivalente en el host). |

**Value objects:** `EventId`, `EventType`, `EventOrigin`, `ConsumerList`, métricas (`LatencyMs`, `ThroughputEps`, `ErrorRate`, `BusStatus`), etc.

### 4.3 Interfaces

- **HTTP:** prefijo `/api/middleware/...` (métricas, cola, topología, publish, evento por id, dead letters, sync registry).
- **Web:** página `/middleware` — UI de operación técnica (mockups históricos en `docs/Mokcups/` solo como referencia visual, no como contrato).

---

## 5. Eventos y catálogo

### 5.1 Qué hace el core con los tipos de evento

- El **core** puede arrancar con `eventbus.subscriptions` **vacío**: los **packs** del cliente amplían el mapa `event_type → suscriptores`.
- Los listeners de **observación** (`BusTrackingListener`, `ModuleObservationListener`, etc.) se registran de forma que el tráfico **no dependa** de listar manualmente cada string en el core.

### 5.2 Ejemplos de nombres de evento

Cualquier ejemplo (`Platform.Demo.Measurement`, `Order.Confirmed`, etc.) en documentación o demos **no forma parte del contrato del core**: son ilustrativos o vienen del `dashboard_config.json` / comandos `artisan` de prueba.

### 5.3 Contrato de sobre (recomendado para integradores)

Campos mínimos útiles para observabilidad (el detalle exacto puede documentarse en el manual de integración del servicio):

- `event_id` (UUID)
- `event` o `event_type` (nombre de catálogo)
- `occurred_at` (ISO-8601)
- `payload` (JSON opaco para el Middleware)
- Hints de origen: `channel`, `origin`, etc. (opcionales, para panel)

---

## 6. Flujo del módulo

### Flujo lógico (genérico)

```text
[Publicador externo o módulo host]
        │ Event::dispatch(tipo, [payload]) o HTTP /api/middleware/events/publish
        ▼
[Capa Middleware: validación mínima + persistencia tracking]
        │
        ├──► QueueEntry / métricas / dead letter (según resultado)
        └──► Listeners de dominio del cliente (registrados fuera del core)
```

### Interfaz de control (operador)

| Área | Uso |
|------|-----|
| Métricas | Latencia, EPS, errores, estado. |
| Cola | Filas recientes y estado de procesamiento observado. |
| Topología | Productores y consumidores (config + observación). |
| Búsqueda | Por `event_id`. |
| Dead letters | Revisión / reencolado manual (según políticas del producto). |

---

## 7. Estructura de carpetas (implementación real)

Estructura **orientativa** acorde al repositorio (bounded context por carpeta bajo `app/Middleware`):

```text
app/Middleware/
├── Application/
│   ├── DTOs/
│   ├── Services/
│   └── UseCases/
├── Domain/
│   ├── Entities/
│   ├── ReadModels/
│   ├── Repositories/        # interfaces
│   └── ValueObjects/
├── Infrastructure/
│   ├── Models/
│   └── Persistence/
├── Interfaces/
│   ├── Http/Controllers/
│   ├── Providers/
│   └── Routes/
└── Listeners/
```

**Configuración relevante**

- `config/eventbus.php` — suscripciones, colas, umbrales, retries.
- `config/modules/modules_config.json` — catálogo declarativo para vistas de topología (solo lectura desde UI).

---

## 8. Dependencias y configuración

| Dependencia | Motivo |
|-------------|--------|
| Driver de colas Laravel | Procesamiento asíncrono de jobs de consumidores **del cliente** (el tracking puede ser síncrono según decisión de diseño actual). |
| BD operativa | Tablas `bus_*`, registro de módulos, etc. |
| Host app | Registro de service provider (`MiddlewareServiceProvider`) y fusión de config desde packs. |

### Restricciones (NO en el core)

| Restricción | Motivo |
|-------------|--------|
| No reglas de negocio por vertical | Mantener el servicio vendible y portable |
| No mutar payload | Inmutabilidad en tránsito |
| No enrutar por contenido del payload | Solo por `event_type` + registro de suscripciones |

---

## 9. Consideraciones de implementación

### 9.1 Garantías de entrega

- En la práctica suele predominar **at-least-once** en colas; los consumidores deben ser **idempotentes** por `event_id`.

### 9.2 Observación vs. procesamiento

- Separar mentalmente: **observación** (Middleware/Dashboard read models) vs. **procesamiento de negocio** (listeners en packs externos).

### 9.3 Seguridad en modo servicio

- Autenticación en endpoints de publicación (API keys, mTLS, etc.) — definir en roadmap de hardening; listado como riesgo en §10.

---

## 10. Riesgos técnicos

| # | Riesgo | Mitigación |
|---|--------|------------|
| R1 | Saturación bajo pico (HI-LOAD) | Escalar workers, colas dedicadas, alertas por profundidad de cola. |
| R2 | Dead letters sin proceso operativo | Panel visible + alertas + runbook. |
| R3 | Orden FIFO con N workers | Colas por partición / `event_type` cuando el orden sea crítico. |
| R4 | Crecimiento de tablas de tracking | Retención / archivo. |
| R5 | Cambio de nombres de evento en integradores | Catálogo versionado + contratos en paquetes cliente. |
| R6 | Publicación no autenticada | Bloquear en producción; identidad por publicador. |
| R7 | Bus como single point of failure | HA en broker, outbox en productores, healthchecks. |

---

## Resumen ejecutivo

| Ítem | Valor |
|------|--------|
| **Rol** | Servicio de integración EDA — **producto principal** de la plataforma core |
| **Dominio de negocio** | Ninguno en el core; llega vía integraciones |
| **Dashboard** | Complemento; no sustituye al Middleware (ver README y plan Dashboard) |
| **Eventos** | Catálogo **externo/configurable**; core agnóstico |
| **Prohibiciones** | Negocio en el bus, transformación de payload, decisiones por contenido |

---

*Fin del plan — Middleware v2.0 (orientación servicio + alineación repositorio core).*
