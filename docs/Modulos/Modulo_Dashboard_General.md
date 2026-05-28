# Módulo: Dashboard de Monitoreo de Eventos (Event-Driven Ops)

---

## 1. DESCRIPCIÓN GENERAL DEL MÓDULO

El módulo **Dashboard de Monitoreo de Eventos** es una interfaz de observabilidad que permite visualizar en tiempo real el flujo de eventos dentro del sistema omnicanal de Sifrah.

Su propósito principal es brindar visibilidad operativa sobre:

* Flujo de eventos
* Estado de los nodos del sistema
* Métricas del middleware
* Procesamiento en tiempo real

### Problema que resuelve

Permite detectar:

* Desincronización entre módulos
* Retrasos en procesamiento
* Fallos en eventos
* Cuellos de botella en el middleware

### Rol dentro del sistema

Este módulo actúa como:

> **Observador del sistema (Read Model / Monitoring Context)**

No ejecuta lógica de negocio ni interviene en el flujo de eventos.

### Dominio (DDD)

Pertenece a un dominio transversal:

* **Monitoring / Observability Context (Supporting Domain)**

---

## 2. RESPONSABILIDADES DEL MÓDULO

### Qué hace

* Visualiza eventos en tiempo real
* Muestra métricas del sistema (latencia, throughput)
* Representa el flujo de eventos entre módulos
* Indica el estado de los nodos (Ventas, Inventario, Pedidos, Middleware)

### Qué NO hace

* No modifica datos del sistema
* No genera eventos de negocio
* No ejecuta lógica de negocio
* No controla otros módulos
* No procesa eventos

### Información que gestiona

* Eventos emitidos por otros módulos
* Métricas del middleware
* Estado de servicios/nodos
* Flujo de eventos

---

## 3. FUNCIONALIDADES PRINCIPALES

### 1. Visualización de métricas globales

* Stock total
* SKUs críticos
* Ventas recientes
* Órdenes activas

### 2. Visualización del flujo de eventos

* Representación gráfica:

  * Ventas / POS → Middleware Bus → Inventario / Pedidos

### 3. Feed de eventos en tiempo real

Muestra:

* Timestamp
* Tipo de evento (ej: `VentaRealizada`, `StockActualizado`)
* Origen (Ventas Web, Inventario, etc.)
* Impacto (ej: -1 SKU, +50 Units)
* Estado (SUCCESS)

### 4. Estado de nodos del sistema

* Ventas Web: ONLINE
* Inventario: SYNCING
* Pedidos: ONLINE
* Middleware: HI-LOAD

### 5. Métricas del motor de eventos

* Latencia promedio (ms)
* Estado del stream
* Tasa de procesamiento (eps)
* Tamaño de cola (FIFO)

---

## 4. EVENTOS DEL SISTEMA

### Eventos que el módulo GENERA

> ❌ Ninguno
> Este módulo **no genera eventos**, solo observa.

---

### Eventos que el módulo CONSUME

#### 1. `VentaRealizada`

* Origen: Ventas Web / POS
* Efecto en el módulo:

  * Se muestra en el feed
  * Se refleja impacto (-1 SKU)

---

#### 2. `StockActualizado`

* Origen: Inventario
* Efecto:

  * Se muestra incremento de stock (+Units)

---

#### 3. `PedidoCreado`

* Origen: Pedidos
* Efecto:

  * Registro en el feed de eventos

---

#### 4. `InventoryDeltaSync` (evento técnico visible)

* Origen: Middleware / Integración
* Efecto:

  * Visualización de flujo técnico (AWS/S3 → Bus → DB)

---

### Nota

El módulo **no interpreta ni transforma eventos**, solo los presenta.

---

## 5. RELACIÓN CON EL MIDDLEWARE

El módulo se conecta al middleware como:

> **Consumidor pasivo de eventos (Event Subscriber)**

### Interacción

* Se suscribe a streams del middleware
* Recibe eventos en orden FIFO
* Muestra métricas del bus (latencia, carga, throughput)

### Importante

* El middleware **NO envía datos personalizados al dashboard**
* El dashboard **consume lo que circula en el bus**

---

## 6. FLUJO DEL MÓDULO DENTRO DEL SISTEMA

```text
Ventas Web / POS
    ↓ (Evento: VentaRealizada)
Middleware (Event Bus - FIFO)
    ↓
Inventario / Pedidos

    ↓
Dashboard (observa evento)
    ↓
Visualización en tiempo real
```

---

## 7. ESTRUCTURA DE DATOS (BASADA EN EL MOCKUP)

### Evento (estructura base)

```json
{
  "timestamp": "10:32:45",
  "event": "VentaRealizada",
  "origin": "Ventas Web",
  "impact": "-1 SKU",
  "status": "SUCCESS"
}
```

---

### Métricas del sistema

```json
{
  "latency_ms": 24,
  "processing_rate_eps": 3842,
  "queue_size": 12,
  "stream_status": "ACTIVE"
}
```

---

### Estado de nodos

```json
{
  "ventas_web": "ONLINE",
  "inventario": "SYNCING",
  "pedidos": "ONLINE",
  "middleware": "HI-LOAD"
}
```

---

## 8. RELACIÓN CON OTROS MÓDULOS

### Interacción

* **Ventas**

  * Observa eventos generados

* **Inventario**

  * Visualiza actualizaciones de stock

* **Pedidos**

  * Visualiza creación de pedidos

* **Middleware**

  * Observa el flujo completo

### Tipo de comunicación

> Exclusivamente mediante eventos

No existe comunicación directa ni dependencias acopladas.

---

## 9. RELACIÓN CON LA PROPUESTA DE SOLUCIÓN

### DDD

* Representa un **Supporting Domain (Observabilidad)**
* No interfiere con el Core Domain

---

### Event-Driven Architecture (EDA)

* Consume eventos en tiempo real
* Permite trazabilidad completa del sistema

---

### Middleware

* Visualiza el comportamiento del bus
* Permite monitorear su rendimiento

---

### Omnicanalidad

* Integra visibilidad de:

  * Ventas físicas
  * Ventas web
  * Inventario
  * Pedidos

---

### Tiempo real

* Actualización continua del feed
* Métricas dinámicas

---

## 10. ESTÁNDARES DE CALIDAD APLICADOS

* Claridad visual
* Separación de responsabilidades
* Consistencia de eventos
* Trazabilidad
* Simplicidad

---

## 11. APLICACIÓN DE LOS ESTÁNDARES

### Claridad visual

* Representación gráfica del flujo de eventos

### Separación de responsabilidades

* No ejecuta lógica de negocio

### Consistencia

* Uso uniforme de eventos

### Trazabilidad

* Seguimiento completo desde origen hasta impacto

### Simplicidad

* Solo lectura y visualización

---

## 12. CORE DEL MÓDULO

### Naturaleza

> Observador puro del sistema

### Qué lo hace único

* Centraliza la visibilidad de toda la arquitectura basada en eventos

### Rol arquitectónico

* **Event Observer / Monitoring Node**

### Diferenciador clave

Permite entender el sistema como:

> Un flujo continuo de eventos en lugar de procesos aislados

---