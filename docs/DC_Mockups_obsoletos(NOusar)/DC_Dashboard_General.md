# Documentación del Mockup: Dashboard General

## 1. ¿QUÉ SE BUSCA?

El mockup del Dashboard General tiene como propósito proporcionar una visión global del estado del sistema en tiempo real dentro de un entorno omnicanal. Este componente resuelve la necesidad de centralizar la observación de eventos generados por distintos módulos sin intervenir en su lógica interna.

Dentro del flujo del sistema, este dashboard representa la capa de visualización, permitiendo monitorear cómo los eventos son generados, procesados y reflejados en los distintos dominios. Su rol dentro de la arquitectura es el de observador, facilitando la comprensión del comportamiento del sistema sin actuar como controlador ni intermediario lógico.

---

## 2. ¿CÓMO SE ESTÁ MOSTRANDO?

El dashboard utiliza una combinación de componentes visuales estructurados para representar información en tiempo real:

- Cards superiores que muestran métricas clave como stock total, productos críticos, ventas recientes y pedidos activos.
- Un componente central de visualización de flujo de eventos que representa gráficamente la relación entre módulos (Ventas → Middleware → Inventario/Pedidos).
- Un feed de eventos en tiempo real presentado en formato tabular, donde se incluyen atributos como timestamp, tipo de evento, origen, impacto y estado.
- Paneles laterales que indican el estado de los módulos (online, syncing, high load).
- Métricas técnicas relacionadas con el procesamiento de eventos, como latencia, tasa de procesamiento y estado del stream.

Los eventos se visualizan de manera estructurada y trazable, permitiendo identificar su origen, impacto y estado sin necesidad de interpretar lógica interna.

---

## 3. RELACIÓN CON EL MIDDLEWARE

El dashboard mantiene una relación directa con el middleware en términos de visualización del flujo de eventos.

Este mockup no genera ni consume eventos, sino que los visualiza. Se evidencia el paso por el middleware a través de:

- El componente de flujo de eventos que muestra explícitamente la intermediación del middleware entre módulos.
- Métricas como latencia, procesamiento por segundo y estado del stream, que reflejan el comportamiento del middleware.
- Información de trazabilidad dentro del event feed, donde se muestra la ruta parcial del evento (ejemplo: origen y procesamiento).

El middleware es representado como un canal de distribución y procesamiento, sin atribuirle decisiones de negocio.

---

## 4. RELACIÓN CON LA PROPUESTA DE SOLUCIÓN

Este mockup se alinea directamente con la propuesta de:

**Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integración para la Optimización Omnicanal y la Visibilidad de Inventario en Tiempo Real**

Su contribución se evidencia en:

- Arquitectura orientada a dominios: cada módulo (Ventas, Inventario, Pedidos) se representa de manera independiente, reforzando la separación de responsabilidades.
- Arquitectura orientada a eventos: el flujo de eventos y el event feed reflejan claramente la comunicación basada en eventos.
- Visibilidad en tiempo real: los indicadores y métricas permiten observar el estado actual del sistema sin retrasos aparentes.
- Omnicanalidad: se integran múltiples fuentes de eventos (ventas web, pedidos, inventario) en una sola vista.

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

La claridad visual se refleja en la organización jerárquica del dashboard, donde cada sección cumple una función específica sin generar ambigüedad.

La separación de responsabilidades se mantiene al no incluir lógica de negocio en el dashboard ni en el middleware, respetando la independencia de los módulos.

La consistencia de interfaz se observa en el uso uniforme de componentes como cards, tablas y paneles, lo que facilita la navegación y comprensión.

La trazabilidad de eventos se logra mediante el event feed, donde cada evento incluye atributos relevantes que permiten su seguimiento.

La simplicidad se mantiene evitando sobrecargar la interfaz con información innecesaria, enfocándose únicamente en datos relevantes para la observación del sistema.

Todo esto contribuye directamente a reforzar la arquitectura propuesta, haciendo visible el comportamiento del sistema sin comprometer su desacoplamiento.

---

## 7. CORE DEL MOCKUP

El elemento central del mockup es la **visualización del flujo de eventos junto con el event feed en tiempo real**.

Este componente es clave porque:

- Representa la comunicación entre módulos sin acoplamiento directo
- Hace visible el rol del middleware como intermediario
- Permite entender cómo los eventos impactan el sistema

A diferencia de otros módulos, este dashboard no ejecuta acciones, sino que proporciona visibilidad integral del sistema.

---

## 8. FLUJO REPRESENTADO

El flujo principal representado en el mockup es:

Ventas → Evento → Middleware → Inventario/Pedidos → Dashboard

Este flujo refleja la arquitectura desacoplada, donde cada módulo interactúa mediante eventos y el middleware actúa como canal de distribución.

---