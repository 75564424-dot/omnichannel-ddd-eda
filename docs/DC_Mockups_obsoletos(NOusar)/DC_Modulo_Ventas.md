# Documentación del Mockup: Módulo de Ventas (Web / POS)

## 1. ¿QUÉ SE BUSCA?

El mockup del módulo de ventas tiene como propósito representar el punto de origen de los eventos dentro del sistema omnicanal. Este componente resuelve la necesidad de registrar transacciones de venta de manera clara y estructurada, permitiendo la generación de eventos que serán posteriormente procesados por el sistema.

Dentro del flujo general, este módulo representa la fase inicial, donde se origina la información que impactará a otros dominios como inventario y pedidos. Su rol dentro de la arquitectura es el de generador de eventos, manteniendo independencia respecto a los demás módulos.

---

## 2. ¿CÓMO SE ESTÁ MOSTRANDO?

La interfaz se presenta mediante una estructura clara y orientada a la acción:

- Un conjunto de categorías de productos (relojes, joyas, lentes, bolsos) que permiten contextualizar la selección.
- Un carrito de compra central que muestra los productos seleccionados, incluyendo nombre, SKU, cantidad y precio.
- Controles de incremento y decremento para gestionar cantidades de forma directa.
- Un panel lateral que resume la venta, incluyendo subtotal, impuestos y total final.
- Un botón principal de acción (“Confirmar Venta”) que representa el punto de generación del evento.

Adicionalmente, se incluye un indicador visual en la parte superior del resumen que muestra el estado del evento (“Evento: VentaRealizada Enviado” con estado “Procesando”), lo cual permite evidenciar la generación del evento sin mostrar lógica interna.

---

## 3. RELACIÓN CON EL MIDDLEWARE

El módulo de ventas interactúa con el middleware a través de la generación de eventos.

Al confirmar una venta, se produce un evento que es enviado al middleware para su posterior distribución. Este mockup no muestra el procesamiento interno del middleware, pero sí evidencia que el evento ha sido emitido mediante el indicador visual de estado.

El middleware actúa como intermediario, recibiendo el evento generado por este módulo y distribuyéndolo hacia los módulos correspondientes, sin que el módulo de ventas tenga conocimiento directo de dichos destinos.

---

## 4. RELACIÓN CON LA PROPUESTA DE SOLUCIÓN

Este mockup se integra directamente con la propuesta de:

**Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integración para la Optimización Omnicanal y la Visibilidad de Inventario en Tiempo Real**

Su contribución se evidencia en:

- Arquitectura orientada a dominios: el módulo de ventas se mantiene como un dominio independiente enfocado en la gestión de transacciones.
- Arquitectura orientada a eventos: la acción de confirmar una venta genera un evento que será utilizado por otros módulos.
- Visibilidad en tiempo real: el indicador de estado del evento permite evidenciar que la operación está siendo procesada.
- Omnicanalidad: este mismo flujo puede aplicarse tanto a ventas web como a ventas en tienda física, unificando el origen de eventos.

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

La claridad visual se refleja en la organización del carrito y el resumen de venta, facilitando la comprensión inmediata del proceso.

La separación de responsabilidades se mantiene al no incluir lógica de inventario ni procesamiento de eventos dentro del módulo de ventas, respetando su rol exclusivo de generación de eventos.

La consistencia de interfaz se evidencia en el uso de componentes uniformes como tarjetas, listas y botones de acción.

La trazabilidad de eventos se introduce mediante el indicador visual que muestra el estado del evento generado.

La simplicidad se conserva evitando la inclusión de información innecesaria, permitiendo que el usuario se enfoque en la acción principal: confirmar la venta.

Todo esto refuerza la arquitectura desacoplada, donde cada módulo cumple una función específica sin interferir en otros dominios.

---

## 7. CORE DEL MOCKUP

El elemento central del mockup es la **acción de confirmación de venta como generador de eventos**.

Este componente es clave porque:

- Representa el inicio del flujo de eventos en el sistema
- Permite la propagación de información hacia otros módulos
- Refuerza el concepto de desacoplamiento

A diferencia de otros módulos, este no consume ni visualiza eventos, sino que actúa como punto de origen dentro de la arquitectura basada en eventos.

---

## 8. FLUJO REPRESENTADO

El flujo principal representado en este mockup es:

Ventas → Evento (VentaRealizada) → Middleware → Inventario / Pedidos → Dashboard

Este flujo refleja cómo una acción simple dentro del módulo de ventas desencadena una serie de procesos en otros módulos sin acoplamiento directo.

---