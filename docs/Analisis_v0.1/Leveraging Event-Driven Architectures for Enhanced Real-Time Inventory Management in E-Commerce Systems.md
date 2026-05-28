**DATOS DE LA FUENTE**


**Título:** Leveraging Event-Driven Architectures for Enhanced Real-Time Inventory
Management in E-Commerce Systems.
**Autores:** Amey Pophali.
**Año:** 2025.
**Tipo de estudio:** Artículo de investigación técnica y análisis de impacto.
**Sector analizado:** Sistemas de comercio electrónico (E-commerce) y redes de retail
distribuidas.

**RESUMEN EJECUTIVO**


La fuente examina la implementación de la **Arquitectura Orientada a Eventos (EDA)** para
transformar la gestión de inventarios en tiempo real. El autor propone un modelo basado en
tres componentes núcleo: productores, enrutadores y consumidores de eventos, demostrando
que este enfoque supera las limitaciones de los sistemas tradicionales ante el aumento masivo
de transacciones concurrentes. El estudio reporta mejoras críticas en **escalabilidad,**
**reducción de latencia (85%) y consistencia de datos (99.9%)**, estableciendo a la EDA
como el patrón arquitectónico fundamental para plataformas modernas que operan en redes de
retail distribuidas.

**PUNTOS QUE RESPALDAN EL TÍTULO**


La fuente proporciona un respaldo técnico y empírico de nivel **Muy Alto** para los pilares de
eventos, middleware y visibilidad de inventario.



**Componente** **del**
**título**


**Arquitectura** **de**
**Software**


**Arquitectura**
**orientada a eventos**


**Middleware** **de**
**integración**



**Evidencia encontrada en la**
**fuente**


Define la EDA como un patrón
crucial para gestionar sistemas
de inventario complejos.


El artículo se centra
exclusivamente en cómo EDA
transforma la gestión de
inventario.


El "enrutador de eventos"
(Kafka/RabbitMQ) se describe
como el "sistema nervioso
central".



**Nivel** **de**
**respaldo**



**Alto** Valida el cambio de
paradigma hacia sistemas
desacoplados y
distribuidos.


**Muy Alto** Detalla el flujo completo
de eventos desde la captura
hasta la reacción del
sistema.


**Alto** Valida el uso de un
componente de
infraestructura
(middleware) para la
distribución y consistencia
de datos.



**Explicación**


**Optimización**
**Omnicanal**


**Visibilidad** **de**
**Inventario** **en**
**Tiempo Real**


**Caso** **aplicado al**
**retail / sedes**


**Escalabilidad** **y**
**modernización**


**Impacto operativo**

**o experiencia del**
**cliente**



Menciona la gestión de
inventario a través de
"múltiples canales de venta" y
"redes de retail distribuidas".


Reporta actualizaciones de
inventario "casi instantáneas"
con latencias de distribución
de 25ms.


Analiza la consistencia de
datos en "ubicaciones de retail
geográficamente dispersas".


Reporta un 85% mejor
escalabilidad en arquitecturas
basadas en microservicios y
eventos.


Registra un aumento del 24%
en la satisfacción del cliente y
31% de reducción en ventas
perdidas.



**Alto** Respalda la necesidad de
sincronización entre
canales para evitar
sobreventas y quiebres de
stock.


**Muy Alto** Provee evidencia
cuantitativa de la
viabilidad técnica de la
visibilidad en tiempo real.


**Alto** Es directamente aplicable a
un escenario de múltiples
sedes como el de Sifrah.


**Alto** Justifica la modernización
tecnológica frente a
sistemas monolíticos
tradicionales.


**Alto** Vincula la arquitectura
técnica con resultados de
negocio tangibles y
satisfacción del usuario.



**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**


A pesar del fuerte respaldo tecnológico, la fuente presenta vacíos metodológicos y
contextuales específicos.



**Elemento**
**faltante o débil**


**Domain-Driven**
**Design (DDD)**


**Caso** **Sifrah**
**Huancayo**


**Middleware**
**(término literal)**



**Qué muestra la fuente** **Impacto**
**sobre mi**
**título**



Menciona "dominios" de
forma general en la
conclusión.


El estudio es de alcance
general en EE. UU. y
redes globales.


Usa los términos "Event
Router" y "Message
Broker Clusters".



**Explicación**



**Neutral** **No evidenciado** . El artículo no
menciona explícitamente la
metodología DDD para el diseño de
los servicios.


**Bajo** No invalida la técnica, pero la
aplicación local en Huancayo sigue
siendo el aporte original del
investigador.


**Bajo** Valida la tecnología (middleware),
aunque prefiere terminología de
patrones EDA específicos.


**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


**Métricas Cuantitativas Rigurosas:** Provee datos específicos (85% menos latencia,
92% menos errores de sincronización) para justificar la solución técnica.

**Validación de Patrones Avanzados:** Respalda el uso de **Event-Sourcing**, lo cual es
un complemento ideal para una arquitectura de eventos.

**Enfoque en Resiliencia:** Demuestra que estos sistemas mantienen un 99.99% de
_uptime_ incluso en periodos de alta demanda.

**Contras:**


**Ausencia de DDD:** No aborda el diseño orientado a dominios como método para
definir los límites de los microservicios [No evidenciado].

**Falta de Detalle en "Middleware" de Negocio:** Se enfoca en el middleware de
mensajería (Kafka), pero no en capas de integración de procesos de negocio
superiores.

**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


**Muy Alto:** Esta fuente es el sustento técnico definitivo para la parte de **"Eventos"** y
**"Visibilidad en Tiempo Real"** de tu título. Ofrece la evidencia científica necesaria para
defender por qué una arquitectura reactiva es superior a una tradicional en retail.


**RECOMENDACIÓN ESTRATÉGICA**


Se recomienda utilizar esta fuente en el **Marco Teórico** y la **Justificación Técnica** de la
siguiente manera:


1.​ **Justificación del Middleware:** Cita a Pophali (2025) para validar que el enrutador de

eventos (middleware) es el "sistema nervioso central" que garantiza la entrega de
mensajes con latencias de milisegundos.
2.​ **Sustento de la Visibilidad:** Usa los datos de reducción de errores de sincronización

(92%) para fundamentar tu objetivo de visibilidad de inventario.
3.​ **Definición de Componentes:** Adopta la estructura de Productores, Enrutadores y

Consumidores propuesta en el artículo para el diseño de tu arquitectura.
4.​ **Complemento Técnico:** Utiliza la sección de **Event-Sourcing** para fortalecer la

robustez de los datos en tu propuesta de visibilidad en tiempo real.


**VEREDICTO FINAL**


Esta fuente **fortalece significativamente el título de investigación**, validando de forma
directa y actual (2025) la eficacia de las arquitecturas orientadas a eventos y el uso de
middleware de mensajería para resolver problemas de inventario en tiempo real. Aunque no


menciona explícitamente el diseño orientado a dominios (DDD), la descripción de sistemas
distribuidos y microservicios que reaccionan a eventos de negocio es totalmente compatible
con dicho enfoque, convirtiendo a este artículo en un pilar fundamental para la validación
técnica de la tesis.


