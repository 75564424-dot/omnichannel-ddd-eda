**DATOS DE LA FUENTE**


**Título:** Understanding Event-Driven Architecture: A Framework for Scalable and Resilient
Systems.
**Autores:** Naresh Pala.
**Año:** c. 2025 (Basado en la referencia bibliográfica n.º 10 citada en el texto).
**Tipo de estudio:** Artículo de revisión técnica con estudio de caso detallado.
**Sector analizado:** Retail y comercio electrónico (E-commerce).

**RESUMEN EJECUTIVO**


La fuente analiza la **Arquitectura Orientada a Eventos (EDA)** como un paradigma esencial
para construir sistemas escalables y resilientes en el entorno digital actual. Propone una
reorientación del diseño de software desde modelos tradicionales de solicitud-respuesta hacia
la producción y consumo de eventos, permitiendo el desacoplamiento temporal y funcional de
los componentes. El artículo destaca cómo este enfoque es particularmente transformador en
el **retail**, facilitando la sincronización de inventario en tiempo real a través de múltiples
canales y mejorando la experiencia del cliente mediante notificaciones proactivas y sistemas
de cumplimiento flexibles. Incluye un caso de estudio de un minorista con más de 500 sedes
donde la implementación de EDA mediante **Domain-Driven Design (DDD)** y microservicios
incrementó la precisión del inventario al 99.8%.

**PUNTOS QUE RESPALDAN EL TÍTULO**


La fuente proporciona un respaldo integral y explícito a prácticamente todos los componentes
de la investigación propuesta.



**Componente**
**del título**


**Arquitectura**
**de Software**


**Domain-Driv**
**en Design**


**Arquitectura**
**orientada** **a**
**eventos**



**Evidencia encontrada en la**
**fuente**


Explora la EDA como un
paradigma para sistemas
complejos y distribuidos,
integrando microservicios.


Menciona explícitamente que
DDD y los "contextos acotados"
son pilares para
implementaciones efectivas de
microservicios en retail.


El artículo se centra en el
modelo de producción,
detección y consumo de eventos
para lograr acoplamiento laxo.



**Nivel** **de**
**respaldo**
**(Alto/Medio**
**/Bajo)**



**Alto** Valida el uso de arquitecturas
modernas para manejar la
escala y diversidad de sistemas
heterogéneos.


**Alto** Respalda el uso de DDD para
establecer límites de propiedad
claros para eventos y reglas de
negocio.


**Muy Alto** Es el tema central; detalla
patrones como
_publish-subscribe_, _event_
_sourcing_ y CQRS.



**Explicación**


**Middleware**
**de**
**integración**


**Optimización**
**Omnicanal**


**Visibilidad de**
**Inventario en**
**Tiempo Real**


**Caso aplicado**
**/** **múltiples**
**sedes**


**Escalabilidad**
**y**
**modernizació**
**n**


**Impacto**
**operativo** **/**
**Cliente**



Define los "canales de eventos"
como infraestructura de
middleware (brokers como
Kafka - RabbitMQ) que
garantiza la entrega confiable.


Analiza cómo los patrones de
eventos permiten sincronizar
datos entre tiendas físicas y
puntos táctiles digitales de
forma fluida.


Reporta una mejora en la
precisión del inventario (del
82% al 99.8%) y visibilidad casi
instantánea mediante
sincronización basada en
eventos.


Incluye el caso de un minorista
con más de 500 tiendas físicas
en Norteamérica enfrentando
problemas de discrepancia de
stock.


Examina la transformación de
sistemas monolíticos legados
hacia arquitecturas resilientes y
elásticas.


Documenta una mejora del 28%
en la satisfacción del cliente y
una reducción del 67% en el
tiempo de integración de nuevas
funciones.



**Alto** Valida el rol del middleware
para unificar sistemas
fragmentados y gestionar la
comunicación asíncrona.


**Alto** Destaca la necesidad de
consistencia en operaciones
omnicanal mediante flujos de
datos en tiempo real.


**Muy Alto** La visibilidad de inventario se
presenta como la capacidad
fundamental habilitada por la
arquitectura propuesta.


**Alto** Provee un precedente directo
para un sistema multisede
(análogo a Sifrah Sedes
Huancayo).


**Alto** El título del paper resalta la
escalabilidad como un
beneficio núcleo de EDA.


**Alto** Cuantifica los beneficios
directos en la eficiencia del
negocio y la experiencia del
usuario final.


**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**



**Elemento**
**faltante o débil**


**Caso** **específico**
**Huancayo**


**Detalles** **de**
**algoritmo** **de**
**optimización**


**Middleware**
**heredado**



**Qué muestra la fuente** **Impacto**
**sobre** **mi**
**título**



El estudio de caso se sitúa en
un minorista de Norteamérica.


Se centra en la arquitectura
(EDA/DDD) más que en el
algoritmo matemático
específico de optimización de
rutas o stock.


Se enfoca en brokers de
eventos modernos
(Kafka/RabbitMQ).



**Explicación**



**Bajo** No invalida el título, pero
requiere la adaptación local
del modelo al contexto
peruano.


**Medio** Sugiere que la optimización
es un resultado de la
arquitectura, pero no detalla
la lógica interna del
"optimizador".


**Bajo** Si el caso Sifrah requiere
integración con middlewares
antiguos (ESB), la fuente
provee menos detalles sobre
esa transición específica.



**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


**Validación técnica superior:** Conecta directamente el uso de **DDD (contextos**
**acotados)** con **EDA** para resolver problemas de **retail omnicanal** .

**Métricas cuantitativas:** Ofrece datos reales sobre precisión de inventario (99.8%) y
reducción de caídas del sistema (37%) que pueden usarse para justificar la
investigación.

**Guía de implementación:** Detalla criterios para la selección de brokers de eventos
(Kafka vs. RabbitMQ) y diseño de esquemas.

**Contras:**


**Contexto geográfico:** El estudio de caso es de un mercado desarrollado (USA), lo
que podría presentar diferencias en infraestructura de red frente a Huancayo.

**Falta de "optimizador" específico:** No presenta un modelo algorítmico único para
la optimización, sino una estructura que la habilita.


**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


**Muy Alto:** Esta fuente es **fundamental** . Es un "espejo" académico de tu propuesta, validando
la combinación exacta de **Arquitectura Orientada a Dominios (DDD)** y **Eventos (EDA)**
aplicada específicamente al **retail omnicanal** y la **visibilidad de inventario** .


**RECOMENDACIÓN ESTRATÉGICA**


Esta fuente debe ser el **pilar de tu Marco Teórico y Antecedentes** :


1.​ **Justificación Técnica:** Cita la reducción del 60% en dependencias de

implementación al usar patrones EDA en comparación con modelos directos.
2.​ **Fundamentación de DDD:** Utiliza la sección de "bounded contexts" para justificar

por qué es necesario dividir Sifrah en dominios específicos para manejar la
complejidad.
3.​ **Módulo de Inventario:** Usa los hallazgos del caso de estudio (mejora de precisión

del 82% al 99.8%) para establecer tus metas de éxito e hipótesis.
4.​ **Middleware:** Referencia la sección de "Event Channels" para definir los

requerimientos técnicos de tu middleware de integración.


**VEREDICTO FINAL**


Esta fuente **fortalece significativamente el título propuesto**, ya que valida la sinergia entre
el diseño orientado a dominios (DDD) y la arquitectura orientada a eventos (EDA) como la
solución estándar de la industria para los problemas de inventario y omnicanalidad que
planteas. El artículo demuestra que esta combinación técnica no es solo teórica, sino que
produce resultados operativos drásticos y medibles en entornos de retail multisede similares al
caso de estudio presentado.


