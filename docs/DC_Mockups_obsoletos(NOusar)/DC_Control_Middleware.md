# Documentación del Mockup: Módulo de Middleware

## 1. ¿QUÉ SE BUSCA?

El mockup del módulo de middleware tiene como propósito representar el procesamiento, organización y distribución de eventos dentro del sistema omnicanal. Este componente resuelve la necesidad de gestionar la comunicación entre módulos de forma desacoplada, permitiendo que los eventos fluyan sin dependencias directas entre los dominios.

Dentro del flujo del sistema, el middleware actúa como el canal de intermediación, asegurando que los eventos generados por los módulos sean distribuidos correctamente hacia sus consumidores. Su rol dentro de la arquitectura es el de facilitador del flujo de eventos, sin intervenir en la lógica de negocio.

---

## 2. ¿CÓMO SE ESTÁ MOSTRANDO?

La interfaz del módulo de middleware se presenta mediante una vista técnica estructurada en varias secciones:

- Cards superiores que muestran métricas del sistema, como latencia global, eventos por segundo y tasa de error.
- Un componente de topología que representa visualmente la conexión entre productores de eventos (Web Gateway, POS), el Event Bus y los consumidores (servicios de inventario y procesamiento de órdenes).
- Una tabla de cola de eventos en tiempo real (FIFO), donde se muestran atributos como ID del evento, tipo, origen, consumidores, timestamp y estado (procesado, pendiente, fallido).

La información se organiza de manera clara para permitir el seguimiento del flujo de eventos, mostrando su tránsito a través del sistema sin interpretar su contenido.

---

## 3. RELACIÓN CON EL MIDDLEWARE

Este mockup representa directamente al middleware, por lo que su relación con los eventos es central.

El módulo:

- Recibe eventos generados por distintos orígenes (Ventas, Web Gateway, POS).
- Organiza los eventos en una cola siguiendo un orden FIFO.
- Distribuye los eventos hacia los módulos consumidores correspondientes.

El mockup evidencia el paso de los eventos a través del middleware mediante:

- La visualización de la topología del sistema.
- La cola de eventos que muestra el estado de cada evento.
- La identificación de consumidores asociados a cada evento.

En ningún momento se muestra lógica de negocio, ya que el middleware únicamente gestiona el flujo de eventos.

---

## 4. RELACIÓN CON LA PROPUESTA DE SOLUCIÓN

Este mockup es el componente más representativo de la propuesta:

**Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integración para la Optimización Omnicanal y la Visibilidad de Inventario en Tiempo Real**

Su aporte se evidencia en:

- Arquitectura orientada a dominios: permite la comunicación entre módulos independientes sin acoplamiento directo.
- Arquitectura orientada a eventos: centraliza el flujo de eventos, facilitando su distribución.
- Visibilidad en tiempo real: permite monitorear el estado, procesamiento y tránsito de los eventos.
- Omnicanalidad: integra múltiples fuentes de eventos (web, POS, servicios) en un único canal de comunicación.

Este módulo es clave para garantizar la coherencia del sistema distribuido.

---

## 5. ESTÁNDARES DE CALIDAD APLICADOS

En el diseño del mockup se aplican los siguientes estándares:

- Claridad visual  
- Separación de responsabilidades  
- Consistencia de interfaz  
- Trazabilidad de eventos  
- Simplicidad y no sobrecarga visual  

---

## 6. APLICACIÓN DE LOS ESTÁNDARES Y RELACIÓN CON LA PROPUESTA

La claridad visual se logra mediante la separación de métricas, topología y cola de eventos, permitiendo una lectura estructurada del sistema.

La separación de responsabilidades es evidente al no incluir lógica de negocio dentro del middleware, limitándose a la gestión de eventos.

La consistencia de interfaz se mantiene con el uso de componentes uniformes como tarjetas y tablas.

La trazabilidad de eventos se garantiza mediante la identificación de cada evento, su origen, sus consumidores y su estado dentro del sistema.

La simplicidad se conserva al presentar únicamente información relevante para el monitoreo del flujo de eventos.

Estos elementos refuerzan la arquitectura propuesta, asegurando que el middleware funcione como un canal eficiente y observable sin generar acoplamiento.

---

## 7. CORE DEL MOCKUP

El elemento central del mockup es la **visualización del flujo y procesamiento de eventos a través del middleware**.

Este componente es clave porque:

- Representa el punto de conexión entre todos los módulos
- Permite observar cómo los eventos son organizados y distribuidos
- Refuerza el concepto de desacoplamiento en la arquitectura

A diferencia de otros módulos, este no genera ni consume lógica de negocio, sino que gestiona el tránsito de la información.

---

## 8. FLUJO REPRESENTADO

El flujo principal representado en este mockup es:

Ventas / Web / POS → Evento → Middleware (Event Bus / Cola FIFO) → Módulos Consumidores (Inventario / Pedidos)

Este flujo evidencia cómo el middleware actúa como intermediario, permitiendo la comunicación entre módulos sin dependencias directas.

---