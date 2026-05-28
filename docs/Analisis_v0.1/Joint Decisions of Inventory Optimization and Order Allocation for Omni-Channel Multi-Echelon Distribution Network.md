**DATOS DE LA FUENTE**


**Título:** Joint Decisions of Inventory Optimization and Order Allocation for Omni-Channel
Multi-Echelon Distribution Network.
**Autores:** Ting Qu, Tianxiang Huang, Duxian Nie, Yelin Fu, Lin Ma y George Q. Huang.
**Año:** 2022.
**Tipo de estudio:** Artículo de investigación con modelado matemático y simulación mediante
Algoritmos Genéticos (GA).
**Sector analizado:** Retail / Redes de distribución multi-nivel (basado en una empresa
comercializadora de vinos en China).

**RESUMEN EJECUTIVO**


La fuente aborda el problema de la **insuficiencia en el uso de recursos de inventario** y la
**asignación ineficiente de pedidos** en redes de distribución omnicanal de múltiples niveles
(OMDN). Propone un modelo de optimización conjunta que integra dos estrategias clave: una
**política de inventario integrado** (que incluye el uso compartido de stock online/offline y el
transbordo lateral entre nodos) y un **mecanismo de asignación de pedidos** basado en el costo
mínimo (considerando reposición, almacenamiento, distancia y tiempo). El estudio
demuestra, mediante un Algoritmo Genético, que esta integración no solo reduce los costos
operativos, sino que mejora el nivel de servicio al cliente y la sostenibilidad ambiental al
optimizar el transporte.

**PUNTOS QUE RESPALDAN EL TÍTULO**


La fuente proporciona una validación teórica y cuantitativa robusta sobre la necesidad de
integrar procesos para lograr la optimización omnicanal.



**Componente** **del**
**título**


**Optimización**
**Omnicanal**


**Visibilidad** **de**
**Inventario**



**Evidencia encontrada en**
**la fuente**


Propone un modelo para
lograr operaciones
omnicanal rentables y
sostenibles mediante la
integración de canales de
venta y niveles de
distribución.


Discute la importancia de
integrar datos de inventario
de todos los canales para
proporcionar servicios
consistentes.



**Nivel** **de**
**respaldo**



**Alto** Valida que la optimización
conjunta de inventario y
pedidos es la clave para la
eficiencia en este modelo de
negocio.


**Alto** Respalda que un "inventario
compartido" (shared inventory)
es esencial para reducir costos
de posesión y mejorar el
cumplimiento.



**Explicación**


**Caso aplicado al**
**retail / múltiples**
**sedes**


**Impacto**
**operativo** **o**
**experiencia** **del**
**cliente**


**Middleware** **de**
**integración**



El modelo se valida en una
red de tres niveles: Centros
de Distribución Central
(CDC), Regionales (RDC) y
tiendas físicas.


Utiliza el "Service Level"
(tasa de cumplimiento) y el
"Total Cost" como métricas
principales de éxito.


Menciona la necesidad de
integrar datos de inventario,
clientes y otros flujos de
información de todos los
canales.



**Alto** Coincide con la estructura de
"Sedes" planteada en tu título,
analizando flujos entre
diferentes nodos geográficos.


**Alto** Demuestra que la estrategia
conjunta mejora
significativamente el bienestar
del cliente al asegurar
disponibilidad.


**Bajo** No menciona el término
"middleware", pero valida la
**necesidad funcional** de una
capa de integración tecnológica
para unificar datos
fragmentados.



**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**

El artículo se centra en la **Investigación de Operaciones** y modelos matemáticos, dejando vacíos en
la implementación de ingeniería de software.



**Elemento**
**faltante o débil**


**Arquitectura de**
**Software**


**Domain-Driven**
**Design (DDD)**


**Arquitectura**
**orientada** **a**
**eventos**



**Qué muestra la fuente** **Impacto**
**sobre** **mi**
**título**



Se enfoca en un "joint
optimization model"
matemático y algoritmos.


Divide el problema
lógicamente en "Inventory
Optimization" (IO) y "Order
Allocation" (OA).


Los pedidos se recolectan en
puntos de tiempo y activan
respuestas de inventario.



**Explicación**



**Bajo** No propone una estructura de
software (como microservicios

         - capas), sino la lógica
algorítmica de decisión.


**Neutral** Aunque separa áreas de
interés, **no está evidenciado** el
uso de patrones de diseño
como Contextos Acotados o
Agregados.


**Bajo** Describe el comportamiento
reactivo del sistema, pero el
patrón técnico EDA **no está**
**evidenciado** .


**Tiempo** **Real**
**(Técnico)**



Habla de visibilidad
"confiable" y decisiones
periódicas
(diarias/semanales).



**Medio** No aborda la latencia técnica
ni tecnologías de streaming; el
"tiempo real" se asume como
disponibilidad de información
para el modelo.



**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


**Validación de la lógica de negocio:** Provee un modelo matemático que justifica por
qué la optimización de pedidos debe considerar el inventario compartido.

**Métricas Cuantificables:** Ofrece fórmulas para calcular el impacto en costos de
transporte y almacenamiento que pueden usarse en el caso Sifrah.

**Justificación de Sostenibilidad:** Vincula la arquitectura omnicanal con la reducción
de emisiones y desperdicio de recursos.

**Contras:**


**Ausencia de Especificación Técnica:** No menciona frameworks, lenguajes de
programación ni patrones arquitectónicos de software [No evidenciado].

**Enfoque en Modelado:** Trata el sistema como un conjunto de ecuaciones y no como
un sistema de software distribuido.

**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


**Alto:** La fuente es fundamental para **justificar el "por qué" y el "qué"** de la investigación
(la lógica de optimización y visibilidad), aunque no aporte al "cómo" técnico (arquitectura de
software específica).


**RECOMENDACIÓN ESTRATÉGICA**


Esta fuente debe utilizarse para **justificar la necesidad operativa y económica** de la
arquitectura propuesta.


1.​ **Marco Teórico:** Citar la definición de Red de Distribución Omnicanal Multi-nivel

(OMDN) para situar el caso de las sedes de Sifrah en Huancayo.
2.​ **Justificación del Problema:** Usar la evidencia de que la gestión descentralizada

(fragmentada) aumenta los costos y el stock-out para validar por qué se requiere una
arquitectura integrada.
3.​ **Validación de Componentes:** Utilizar los factores de asignación (reposición,

holding, distancia y tiempo) para definir los **parámetros de los eventos y dominios**
en el diseño de software.


**VEREDICTO FINAL**


La fuente **fortalece significativamente la viabilidad operativa y la relevancia del título**,
demostrando que la integración de inventario y la asignación inteligente de pedidos son
críticas para el retail moderno. Sin embargo, el respaldo es **parcial** en términos de ingeniería
de software, ya que no aborda patrones como DDD o EDA, posicionando tu tesis como la
**solución técnica** necesaria para implementar los modelos matemáticos que esta fuente valida.


