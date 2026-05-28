# Documentación del Mockup: Módulo de Inventario

## 1. ¿QUÉ SE BUSCA?

El mockup del módulo de inventario tiene como propósito representar la visualización y actualización del estado del stock en tiempo real dentro del sistema omnicanal. Este componente resuelve la necesidad de mantener un control actualizado de los productos disponibles, así como evidenciar el impacto de los eventos generados por otros módulos.

Dentro del flujo del sistema, este módulo representa la fase de consumo de eventos, donde la información generada desde ventas u otros dominios es reflejada en el estado del inventario. Su rol dentro de la arquitectura es el de receptor de eventos, actuando sobre la información sin depender directamente de los módulos que la generan.

---

## 2. ¿CÓMO SE ESTÁ MOSTRANDO?

La interfaz del módulo de inventario se presenta mediante una estructura organizada en varias secciones:

- Un encabezado que indica el estado del flujo de eventos en tiempo real (“Live Event Stream Active”), junto con métricas como cantidad de eventos y latencia promedio.
- Un componente visual de flujo que representa la relación entre Ventas/POS, Middleware e Inventario.
- Cards informativas que muestran métricas clave como total de unidades, alertas de bajo stock y estado del sistema.
- Una tabla central de inventario que presenta productos con atributos como SKU, nombre, stock por sede y estado.
- Un panel lateral de eventos de dominio que muestra un timeline de eventos recientes, incluyendo su origen y su impacto (incremento o decremento de unidades).
- Una sección de metadatos del evento seleccionado, donde se visualiza información como tipo de evento, origen, tiempo de procesamiento y contenido del evento.

Los eventos se representan de forma explícita mediante indicadores visuales, permitiendo observar su impacto directo sobre el inventario.

---

## 3. RELACIÓN CON EL MIDDLEWARE

El módulo de inventario mantiene una relación directa con el middleware en su rol de consumidor de eventos.

Este mockup no genera eventos, sino que recibe los eventos distribuidos por el middleware y refleja sus efectos en la interfaz. La evidencia del paso por el middleware se observa en:

- El componente de flujo visual que incluye al middleware como intermediario entre ventas y el inventario.
- La información de metadatos del evento, donde se muestra el tiempo de procesamiento, reflejando el tránsito del evento.
- El panel de eventos, donde se registran eventos provenientes de distintos orígenes.

El middleware es representado únicamente como un canal de distribución, sin atribuirle lógica de negocio.

---

## 4. RELACIÓN CON LA PROPUESTA DE SOLUCIÓN

Este mockup se alinea con la propuesta de:

**Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integración para la Optimización Omnicanal y la Visibilidad de Inventario en Tiempo Real**

Su aporte se evidencia en:

- Arquitectura orientada a dominios: el módulo de inventario actúa como un dominio independiente enfocado en la gestión de stock.
- Arquitectura orientada a eventos: el estado del inventario se actualiza a partir de eventos generados por otros módulos.
- Visibilidad en tiempo real: los cambios en el stock y los eventos se reflejan de manera inmediata en la interfaz.
- Omnicanalidad: se integran eventos provenientes de diferentes fuentes (ventas POS, sincronización externa, etc.).

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

La claridad visual se logra mediante la organización jerárquica de la información, permitiendo identificar rápidamente el estado del inventario y los eventos asociados.

La separación de responsabilidades se mantiene al no incluir lógica de generación de eventos ni procesamiento interno, limitando el módulo a su función de consumo y visualización.

La consistencia de interfaz se refleja en el uso uniforme de componentes como cards, tablas y paneles laterales.

La trazabilidad de eventos se garantiza mediante el panel de eventos y la sección de metadatos, donde se puede identificar el origen y características de cada evento.

La simplicidad se conserva evitando sobrecargar la interfaz con información innecesaria, manteniendo el enfoque en el impacto de los eventos sobre el inventario.

Estos elementos refuerzan la arquitectura propuesta, permitiendo observar claramente el comportamiento del sistema sin romper el desacoplamiento.

---

## 7. CORE DEL MOCKUP

El elemento central del mockup es la **representación del impacto de los eventos sobre el estado del inventario**.

Este componente es clave porque:

- Permite visualizar cómo los eventos modifican el stock en tiempo real
- Refuerza el rol del módulo como consumidor de eventos
- Conecta visualmente el flujo entre módulos sin acoplamiento directo

A diferencia de otros módulos, este no inicia el flujo ni lo observa globalmente, sino que actúa como punto de transformación del estado basado en eventos.

---

## 8. FLUJO REPRESENTADO

El flujo principal representado en este mockup es:

Ventas → Evento → Middleware → Inventario → Actualización de Stock → Dashboard

Este flujo refleja cómo el módulo de inventario recibe y aplica los eventos dentro de una arquitectura desacoplada basada en eventos.

---