**DATOS DE LA FUENTE**


**Título:** Real-Time Inventory Optimization in Retail Using Streaming Data
**Autores:** Shakir Poolakkal Mukkath
**Año:** 2025 (Publicado el 8 de mayo de 2025)
**Tipo de estudio:** Artículo de investigación técnica con estudio de caso
**Sector analizado:** Retail moderno y comercio electrónico (Walmart Global Tech)

**RESUMEN EJECUTIVO**


La fuente analiza la transición de sistemas de inventario tradicionales basados en lotes ( _batch_ )
hacia **arquitecturas de streaming en tiempo real** para resolver problemas de exceso de
existencias y desabastecimiento. Propone el uso de **arquitecturas orientadas a eventos**
**(EDA)** que integran datos de puntos de venta (POS), comercio electrónico, sistemas de
gestión de almacenes (WMS) y sensores IoT. El enfoque principal destaca el procesamiento
continuo mediante tecnologías como **Apache Kafka y Flink**, permitiendo visibilidad
inmediata, acciones automatizadas y estrategias de cumplimiento omnicanal inteligentes
como el envío desde tienda ( _ship-from-store_ ) y la optimización de costos de última milla.

**PUNTOS QUE RESPALDAN EL TÍTULO**


La fuente proporciona un respaldo técnico y empírico de alto nivel para la mayoría de los
componentes del título propuesto, validando la eficacia de las arquitecturas reactivas en el
retail.



**Componente** **del**
**título**


**Arquitectura** **de**
**Software**


**Arquitectura**
**orientada a eventos**


**Middleware** **de**
**integración**


**Optimización**
**Omnicanal**



**Evidencia encontrada en**
**la fuente**


Propone arquitecturas de
flujo de datos en tiempo
real y arquitecturas nativas
de la nube ( _cloud-native_ ).


Define la EDA como el
núcleo de la optimización
del inventario en tiempo
real.


Menciona la integración de
fuentes de datos (POS,
E-commerce, WMS) y el
uso de Kafka como
"sistema nervioso central".


Analiza estrategias como
_ship-from-store_, envíos
divididos e integración
cruzada de canales.



**Nivel** **de**
**respaldo**



**Alto** Valida el uso de estructuras
distribuidas y basadas en
microservicios para gestionar la
complejidad del retail.


**Muy Alto** Detalla cómo cada transacción

       - movimiento genera un evento
que fluye a través del sistema
mediante Kafka.


**Alto** Describe funcionalmente la
capa de middleware como el
motor que unifica silos de datos
desconectados.


**Muy Alto** Sustenta que la omnicanalidad
requiere una visión coherente
del inventario para optimizar el
cumplimiento.



**Explicación**


**Visibilidad** **de**
**Inventario** **en**
**Tiempo Real**


**Escalabilidad** **y**
**modernización**


**Impacto operativo**

**o experiencia del**
**cliente**



Reporta una latencia de
sub-hora y visibilidad
inmediata frente a las 24
horas de los sistemas
batch.


Discute requisitos de
escalado horizontal y el
reemplazo de sistemas
heredados de
procesamiento por lotes.


Documenta reducciones en
costos de mantenimiento y
aumento en la satisfacción
del cliente por velocidad de
entrega.



**Muy Alto** Valida que la visibilidad
instantánea reduce
drásticamente los
"desabastecimientos
fantasmas" y mejora la
precisión.


**Alto** Justifica la modernización
hacia sistemas elásticos
capaces de manejar picos
estacionales como el Black
Friday.


**Alto** Provee métricas de éxito como
la mejora en las tasas de venta
a precio completo y reducción
de desperdicios.



**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**


Aunque la fuente es técnicamente robusta, existen componentes específicos del título que no
aborda de manera explícita o local.



**Elemento**
**faltante o débil**


**Domain-Driven**
**Design (DDD)**


**Caso** **Sifrah**
**Huancayo**


**Middleware**
**(Término**
**literal)**



**Qué muestra la fuente** **Impacto**
**sobre** **mi**
**título**



Menciona microservicios
personalizados para
"aspectos del ciclo de vida
del inventario".


El estudio de caso se refiere
a un "minorista importante"
global (referencia indirecta a
Walmart).


Se refiere a "infraestructura
de eventos", "conectores de
datos" y "brokers de
mensajería".



**Explicación**



**Medio** No utiliza literalmente el
término "Domain-Driven
Design", aunque aplica sus
principios al dividir lógica por
servicios especializados.


**Bajo** No invalida la propuesta, pero la
validación contextual en una
sede específica en Perú es el
aporte original de tu
investigación [no evidenciado].


**Bajo** Describe todas las funciones de
un middleware de integración,
aunque prefiere terminología de
arquitecturas de streaming.


**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


**Actualidad Tecnológica:** Al ser de 2025, valida el uso de **Apache Kafka, Flink y**
**Druid** como el estándar de vanguardia para lo que propones.

**Justificación del Problema:** Provee una comparativa detallada (Tabla 1) entre
sistemas _batch_ y _streaming_ que sirve para fundamentar la necesidad de tu
arquitectura.

**Métricas de Éxito Operativo:** Ofrece datos concretos sobre la reducción de
inventario de seguridad y mejora en la rotación que respaldan tu objetivo de
"Optimización".

**Contras:**


**Ausencia de DDD Formal:** No detalla el proceso metodológico de diseño orientado
a dominios (Event Storming, contextos acotados), lo que requiere otra fuente para esa
base teórica [no evidenciado].

**Enfoque en Grandes Minoristas:** Los desafíos de escalabilidad descritos (billones
de eventos) podrían ser sobredimensionados para el caso específico de Sifrah Sedes
Huancayo.

**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


**Muy Alto:** Esta fuente es el **sustento técnico definitivo** para la parte de "Eventos",
"Optimización Omnicanal" y "Tiempo Real" de tu título. Valida que el enfoque que has
elegido es la solución científica y comercialmente recomendada en 2025 para el sector retail.


**RECOMENDACIÓN ESTRATÉGICA**


Utiliza esta fuente para la **validación técnica y la justificación del problema** en tu tesis:


1.​ **Planteamiento del Problema:** Cita las limitaciones de los sistemas batch (latencia de

decisión, silos de datos) descritas en la fuente para justificar por qué Sifrah necesita
un cambio.
2.​ **Marco Teórico:** Usa la definición de **Arquitectura Orientada a Eventos** como

"sistema nervioso central" del inventario para fundamentar tu elección tecnológica.
3.​ **Diseño de Solución:** Adopta los componentes de la Tabla 2 (Fuentes de datos y

Eventos clave) para estructurar los eventos que tu middleware debe procesar (ventas
POS, transacciones online, movimientos WMS).
4.​ **Justificación de Beneficios:** Utiliza los resultados del estudio de caso (mejora en

precisión de conteo, reducción de quiebres de stock) como hipótesis de los resultados
esperados en Huancayo.


**VEREDICTO FINAL**


La fuente **fortalece significativamente el título de investigación**, proporcionando una base
científica sólida para la combinación de arquitecturas orientadas a eventos e integración
omnicanal. Valida que la visibilidad de inventario en tiempo real no es solo un objetivo
técnico, sino una necesidad operativa para la supervivencia en el retail moderno. Aunque no
menciona explícitamente el término "Domain-Driven Design", la descripción de
microservicios alineados a la lógica de negocio y la orquestación de eventos complejos
respalda implícitamente la viabilidad de tu propuesta metodológica.


