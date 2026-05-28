# Módulo: Middleware Sifrah - Event Bus y Gestión de Streams

---

## 1. DESCRIPCIÓN GENERAL DEL MÓDULO

El módulo **Middleware Sifrah** representa el núcleo de la arquitectura basada en eventos, actuando como un **Event Bus** encargado de la recepción, organización y distribución de eventos entre los distintos módulos del sistema.

Este componente permite la comunicación desacoplada entre:

* Ventas (Web / POS)
* Inventario
* Pedidos

### Problema que resuelve

* Eliminación de integraciones punto a punto
* Reducción de acoplamiento entre sistemas
* Sincronización en tiempo real entre módulos
* Manejo ordenado de eventos (FIFO)

### Rol dentro del sistema

> **Canal de comunicación basado en eventos (Event Router / Distributor)**

No ejecuta lógica de negocio, solo transporta eventos.

### Dominio (DDD)

* **Infrastructure Domain (Supporting Domain transversal)**

---

## 2. RESPONSABILIDADES DEL MÓDULO

### Qué hace

* Recibe eventos desde múltiples orígenes
* Organiza eventos en colas FIFO
* Distribuye eventos a los módulos suscritos
* Expone métricas del flujo de eventos
* Permite visualización técnica del sistema

### Qué NO hace

* No interpreta eventos
* No modifica datos
* No toma decisiones de negocio
* No transforma eventos
* No valida lógica de dominio

---

### Información que gestiona

* Eventos (tipo, origen, timestamp, estado)
* Cola de eventos (FIFO)
* Métricas:

  * Latencia
  * Throughput (EPS)
  * Error rate
* Topología de servicios

---

## 3. FUNCIONALIDADES PRINCIPALES

### 1. Monitoreo de métricas del sistema

* Latencia global (ms)
* Eventos por segundo (EPS)
* Tasa de error (%)
* Dead letters

---

### 2. Visualización de topología

Flujo visible:

* Web Gateway (REST API)
* Retail POS (gRPC)
* Event Bus (Middleware)
* Inventory Service
* Order Processor

---

### 3. Gestión de cola de eventos (FIFO)

Visualización en tiempo real de:

* ID del evento
* Tipo de evento (ej: VentaRealizada, StockSync)
* Origen
* Consumidores
* Timestamp
* Estado:

  * Procesado
  * Pendiente
  * Fallido

---

### 4. Estado del bus

* Indicador: **Bus Active**

---

### 5. Búsqueda de eventos

* Campo: **Search event ID**

---

## 4. EVENTOS DEL SISTEMA

### Eventos que el módulo GENERA

> ❌ Ninguno

El middleware **no genera eventos**, solo los transporta.

---

### Eventos que el módulo CONSUME

El middleware recibe todos los eventos del sistema:

---

#### 1. `VentaRealizada`

* Origen: Retail POS
* Consumidores:

  * Inventario
  * Pedidos

---

#### 2. `StockSync`

* Origen: Web Gateway
* Consumidor:

  * Inventario

---

#### 3. `CustomerUpdate`

* Origen: Web Gateway
* Consumidor:

  * CRM

---

### Nota importante

El middleware:

* No interpreta eventos
* No decide consumidores
* Solo enruta según suscripción

---

## 5. RELACIÓN CON EL MIDDLEWARE (AUTODESCRIPCIÓN)

Este módulo ES el middleware.

### Interacción interna

* Recibe eventos desde productores
* Los encola en orden FIFO
* Los distribuye a consumidores

---

## 6. FLUJO DEL MÓDULO DENTRO DEL SISTEMA

```text id="5pxn8k"
Ventas / POS / Web
   ↓
Evento generado
   ↓
Middleware (Event Bus)
   ↓
Cola FIFO
   ↓
Distribución
   ↓
Consumidores (Inventario, Pedidos, etc.)
```

---

## 7. ESTRUCTURA DE DATOS (BASADA EN EL MOCKUP)

### Evento en cola

```json id="b7n4zl"
{
  "id": "A72F9",
  "event_type": "VentaRealizada",
  "origin": "Retail POS",
  "consumers": ["Inventory", "Orders"],
  "timestamp": "10:42:01.442",
  "status": "Procesado"
}
```

---

### Métricas

```json id="n4k8qs"
{
  "latency_ms": 24,
  "events_per_second": 3842,
  "error_rate": 0.02,
  "dead_letters": 3
}
```

---

### Topología

```json id="x9p2we"
{
  "producers": ["Web Gateway", "Retail POS"],
  "bus": "Event Bus",
  "consumers": ["Inventory Service", "Order Processor"]
}
```

---

## 8. RELACIÓN CON OTROS MÓDULOS

### Interacción

* **Ventas**

  * Envía eventos

* **Inventario**

  * Recibe eventos

* **Pedidos**

  * Recibe eventos

---

### Tipo de comunicación

> 100% basada en eventos (EDA)

---

## 9. RELACIÓN CON LA PROPUESTA DE SOLUCIÓN

### DDD

* Pertenece a infraestructura
* No contiene lógica de dominio

---

### Event-Driven Architecture

* Es el núcleo del sistema
* Permite asincronía

---

### Middleware

* Representa la capa de integración

---

### Omnicanalidad

* Conecta todos los canales

---

### Tiempo real

* Procesamiento inmediato de eventos

---

## 10. ESTÁNDARES DE CALIDAD APLICADOS

* Claridad visual
* Separación de responsabilidades
* Consistencia
* Trazabilidad
* Simplicidad

---

## 11. APLICACIÓN DE LOS ESTÁNDARES

### Claridad

* Visualización de flujo y métricas

### Separación

* No contiene lógica de negocio

### Consistencia

* Eventos uniformes

### Trazabilidad

* Seguimiento completo de eventos

### Simplicidad

* Flujo lineal: recibir → encolar → distribuir

---

## 12. CORE DEL MÓDULO

### Naturaleza

> Canal de eventos desacoplado

### Qué lo hace único

* Conecta todos los módulos sin acoplamiento

### Rol arquitectónico

* **Event Bus / Event Router**

### Diferenciador clave

Permite que el sistema funcione como:

> Un ecosistema desacoplado basado en eventos

---