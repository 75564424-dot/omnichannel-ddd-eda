# Documentación del Mockup: Módulo de Pedidos

## 1. ¿QUÉ SE BUSCA?

El mockup del módulo de pedidos tiene como propósito representar la gestión de reposición de productos dentro del sistema omnicanal, permitiendo identificar necesidades de abastecimiento y dar seguimiento a las órdenes generadas.

Este componente resuelve la necesidad de reaccionar ante cambios en el estado del inventario, facilitando la generación de solicitudes de reposición cuando se detectan niveles críticos de stock. Dentro del flujo del sistema, este módulo actúa como un intermediario entre el consumo de eventos y la generación de nuevos eventos, participando activamente en la continuidad del flujo de información.

Su rol dentro de la arquitectura es el de módulo que consume eventos y, en determinados casos, genera nuevos eventos relacionados con pedidos.

---

## 2. ¿CÓMO SE ESTÁ MOSTRANDO?

La interfaz del módulo de pedidos se presenta mediante dos secciones principales:

- Un panel de “Items Críticos” que muestra productos con bajo nivel de stock, incluyendo SKU, nombre del producto y cantidad disponible. Este panel incluye acciones para generar solicitudes de reposición.
- Una tabla de “Órdenes Activas” que lista los pedidos generados, mostrando atributos como ID del pedido, producto, origen, cantidad y estado (pendiente, en proceso, recibido).

Adicionalmente, se incluye una acción destacada para la creación manual de solicitudes.

La información se presenta de manera estructurada mediante cards y tablas, permitiendo identificar rápidamente qué productos requieren atención y el estado de las órdenes en curso.

---

## 3. RELACIÓN CON EL MIDDLEWARE

El módulo de pedidos interactúa con el middleware tanto en la recepción como en la generación de eventos.

Por un lado, recibe eventos provenientes del módulo de inventario que indican niveles críticos de stock, lo cual se refleja en la sección de “Items Críticos”. Por otro lado, al generar una solicitud de reposición, este módulo produce nuevos eventos que son enviados al middleware para su distribución.

El paso por el middleware no se muestra explícitamente en la interfaz, pero se evidencia en el comportamiento del sistema, donde los cambios en inventario y las órdenes generadas se mantienen desacoplados entre módulos.

El middleware actúa únicamente como intermediario en la distribución de estos eventos.

---

## 4. RELACIÓN CON LA PROPUESTA DE SOLUCIÓN

Este mockup se alinea con la propuesta de:

**Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integración para la Optimización Omnicanal y la Visibilidad de Inventario en Tiempo Real**

Su contribución se evidencia en:

- Arquitectura orientada a dominios: el módulo de pedidos funciona como un dominio independiente enfocado en la gestión de reposiciones.
- Arquitectura orientada a eventos: reacciona a eventos de inventario y genera nuevos eventos relacionados con pedidos.
- Visibilidad en tiempo real: permite observar el estado actual de los pedidos y las necesidades de reposición.
- Omnicanalidad: integra información proveniente de diferentes fuentes y permite gestionar abastecimiento de manera centralizada.

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

La claridad visual se logra mediante la separación de secciones entre productos críticos y órdenes activas, permitiendo una rápida identificación de necesidades y estados.

La separación de responsabilidades se mantiene al no incluir lógica de inventario ni procesamiento interno de eventos, enfocándose únicamente en la gestión de pedidos.

La consistencia de interfaz se evidencia en el uso uniforme de tablas, tarjetas y etiquetas de estado.

La trazabilidad de eventos se refleja en la relación implícita entre los productos críticos y las órdenes generadas, así como en los estados de cada pedido.

La simplicidad se mantiene al presentar únicamente la información relevante para la toma de decisiones relacionadas con reposición.

Esto contribuye a reforzar la arquitectura desacoplada, donde los módulos interactúan mediante eventos sin dependencias directas.

---

## 7. CORE DEL MOCKUP

El elemento central del mockup es la **gestión de reposición basada en eventos de inventario**.

Este componente es clave porque:

- Permite transformar eventos de bajo stock en acciones concretas (pedidos)
- Mantiene la continuidad del flujo de eventos dentro del sistema
- Refuerza el rol del módulo como intermediario entre consumo y generación de eventos

A diferencia de otros módulos, este actúa como punto de transición dentro del flujo de eventos.

---

## 8. FLUJO REPRESENTADO

El flujo principal representado en este mockup es:

Inventario → Evento (Bajo Stock) → Middleware → Pedidos → Evento (Solicitud de Reposición) → Middleware → Otros módulos

Este flujo muestra cómo el sistema responde a cambios en el inventario mediante la generación de nuevos eventos, manteniendo el desacoplamiento entre módulos.

---