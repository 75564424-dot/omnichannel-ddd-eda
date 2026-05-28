# Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integración para la Optimización Omnicanal y la Visibilidad de Inventario en Tiempo Real: Caso Sifrah Sedes Huancayo

---

## 1. Contexto del Negocio y Dominio (Domain Context)

Sifrah opera en el sector retail de accesorios y moda bajo un modelo omnicanal, integrando tiendas físicas, e-commerce y canales digitales. Su propuesta de valor se centra en la disponibilidad inmediata de productos de alta rotación alineados a tendencias del mercado.

Desde la perspectiva de **Domain-Driven Design (DDD)**, el núcleo del negocio (*Core Domain*) se identifica como:

> **Gestión de Inventario en Tiempo Real para Venta Omnicanal**

Este dominio es crítico, ya que impacta directamente en la experiencia del cliente, la conversión de ventas y la eficiencia operativa.

### Subdominios identificados

* **Core Domain**

  * Inventario en tiempo real
  * Disponibilidad de productos

* **Supporting Domains**

  * Gestión de pedidos (OMS)
  * Logística y distribución
  * Checkout y ventas

* **Generic Domains**

  * Autenticación
  * Notificaciones
  * Reportes y analítica

---

## 2. Análisis de Procesos desde una Perspectiva Orientada a Eventos

El flujo actual de negocio puede reinterpretarse como una secuencia de eventos de dominio.

### Eventos clave identificados

* `ProductoRegistrado`
* `StockActualizado`
* `ProductoVisualizado`
* `ProductoAgregadoAlCarrito`
* `PedidoCreado`
* `StockValidado`
* `StockReservado`
* `PedidoConfirmado`
* `PedidoDespachado`
* `PedidoEntregado`

### Problema estructural

Actualmente, estos eventos no son gestionados explícitamente, sino que dependen de:

* Integraciones batch
* Validaciones tardías
* Procesos manuales

Esto genera:

* Desfase de inventario
* Sobreventa
* Baja trazabilidad

---

## 3. Análisis Organizacional y Bounded Contexts

Desde DDD, la organización puede mapearse en **Bounded Contexts**:

### Contextos identificados

* **Inventario Context**

  * Gestión de stock
  * Disponibilidad en tiempo real

* **Ventas Context**

  * POS (tiendas físicas)
  * E-commerce

* **Pedidos Context (OMS)**

  * Ciclo de vida del pedido
  * Estados y trazabilidad

* **Logística Context**

  * Picking
  * Envíos
  * Última milla

* **Postventa Context**

  * Devoluciones
  * Reclamos

### Problema actual

Los contextos están **débilmente integrados**, generando:

* Inconsistencias de datos
* Retrasos en sincronización
* Falta de una fuente única de verdad

---

## 4. Arquitectura Tecnológica Actual vs Arquitectura Objetivo

### Arquitectura actual (As-Is)

* ERP como núcleo transaccional
* POS para ventas físicas
* E-commerce desacoplado parcialmente

**Tipo:** Arquitectura semi-integrada (batch-driven)

**Limitaciones:**

* Sincronización no inmediata
* Alto acoplamiento lógico
* Baja resiliencia ante alta demanda

---

### Arquitectura propuesta (To-Be)

> **Arquitectura basada en eventos (Event-Driven Architecture) con soporte de DDD y un Middleware de Integración**

Características:

* Comunicación mediante eventos de dominio
* Desacoplamiento entre contextos
* Procesamiento en tiempo real
* Escalabilidad progresiva

---

## 5. Middleware Sifrah como Núcleo de Integración

El **Middleware Sifrah** se plantea como una capa central que permite:

### Responsabilidades

* Orquestación de eventos
* Sincronización en tiempo real
* Integración entre ERP, POS y e-commerce
* Exposición de APIs

### Eventos gestionados

* `StockActualizado`
* `StockReservado`
* `PedidoCreado`
* `PedidoConfirmado`

### Beneficios

* Eliminación de dependencias batch
* Fuente única de verdad para inventario
* Reducción de inconsistencias

---

## 6. Relación con Mockups y Diseño Funcional

Los mockups desarrollados representan la materialización visual de los bounded contexts y eventos definidos.

### Ejemplo de alineación

* **Vista de producto (Mockup)**

  * Evento: `ProductoVisualizado`
  * Consulta: disponibilidad en tiempo real (Inventario Context)

* **Carrito de compra**

  * Evento: `ProductoAgregadoAlCarrito`
  * Acción: validación temprana de stock

* **Checkout**

  * Evento: `PedidoCreado`
  * Acción: reserva de stock (`StockReservado`)

* **Tracking de pedido**

  * Eventos:

    * `PedidoConfirmado`
    * `PedidoDespachado`
    * `PedidoEntregado`

Esto asegura coherencia entre:

> UX (Mockups) ↔ Lógica de Dominio ↔ Eventos del Sistema

---

## 7. Análisis de Problemas desde DDD + EDA

### Problema: Sobreventa

* Causa:

  * Falta de evento `StockReservado` en tiempo real
* Solución:

  * Implementar reserva de stock basada en eventos

---

### Problema: Retrasos en entrega

* Causa:

  * Falta de eventos en logística
* Solución:

  * Flujo basado en:

    * `PedidoConfirmado → PedidoDespachado → PedidoEntregado`

---

### Problema: Mala experiencia de usuario

* Causa:

  * Validaciones tardías
* Solución:

  * Eventos tempranos en el flujo de compra

---

### Problema: Fragmentación de datos

* Causa:

  * Múltiples fuentes sin sincronización
* Solución:

  * Middleware como hub de eventos

---

## 8. Propuesta de Arquitectura Basada en Dominios y Eventos

### Componentes principales

* API Gateway
* Middleware Sifrah (Event Bus / Orquestador)
* Microservicios por contexto:

  * Inventario
  * Pedidos (OMS)
  * Checkout
  * Logística
  * Postventa

---

### Flujo simplificado

```text
Ecommerce / POS
      ↓
API Gateway
      ↓
Middleware Sifrah (Eventos)
      ↓
Microservicios (Bounded Contexts)
      ↓
ERP / Sistemas externos
```

---

### Características clave

* Arquitectura desacoplada
* Comunicación asíncrona
* Escalabilidad horizontal
* Evolución incremental

---

## 9. Valor Estratégico de la Solución

### Corto plazo

* Reducción de sobreventa
* Mejora en disponibilidad de stock
* Disminución de reclamos

### Mediano plazo

* Integración real omnicanal
* Optimización de procesos operativos

### Largo plazo

* Base sólida para escalabilidad
* Capacidad de adaptación a nuevos canales

---

## 10. Evaluación Final

El problema de Sifrah no es únicamente tecnológico, sino arquitectónico.

Actualmente opera bajo un modelo **multicanal débilmente integrado**, mientras que su necesidad real es una **arquitectura omnicanal basada en eventos y dominios**.

La solución propuesta no implica reemplazar completamente los sistemas existentes, sino:

> **Introducir un Middleware de Integración basado en eventos que actúe como núcleo del sistema, alineando los dominios de negocio con una arquitectura escalable, desacoplada y en tiempo real.**

---