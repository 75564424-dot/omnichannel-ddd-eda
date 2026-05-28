**DATOS DE LA FUENTE**


**Título:** Toward a three-dimensional framework for omni-channel.
**Autores:** Soroosh Saghiri, Richard Wilding, Carlos Mena y Michael Bourlakis.
**Año:** 2017.
**Tipo de estudio:** Investigación exploratoria cualitativa (inductiva y fundamentada)
empleando múltiples estudios de caso y entrevistas a expertos.
**Sector analizado:** Retail y cadenas de suministro (incluyendo fabricantes y minoristas en el
Reino Unido).

**RESUMEN EJECUTIVO**


La fuente propone un **marco conceptual tridimensional** para sistemas omnicanal basado en:
(i) etapa del canal (viaje del cliente), (ii) tipo de canal y (iii) agente del canal. Define la
**integración** y la **visibilidad** como los dos habilitadores esenciales para operacionalizar este
modelo. A través de la lente de la **Teoría de Sistemas Adaptativos Complejos (CAS)**, el
artículo argumenta que la omnicanalidad requiere una coordinación total de procesos y
tecnologías para ofrecer una experiencia de cliente fluida y consistente, superando la
fragmentación de los modelos multicanal tradicionales.

**PUNTOS QUE RESPALDAN EL TÍTULO**


El artículo es fundamental para validar la necesidad de integración y visibilidad, pilares
centrales del título de investigación propuesto.



**Componente del**
**título**


**Optimización**
**Omnicanal**


**Visibilidad** **de**
**Inventario**



**Evidencia** **encontrada**
**en la fuente**


Define la omnicanalidad
como la coordinación de
procesos y tecnologías
para servicios
consistentes y confiables.


Identifica la "Visibilidad
de Stock" (stock
visibility) como un
habilitador crítico para el
movimiento
cross-channel.



**Nivel** **de**
**respaldo**
**(Alto/Medio/**
**Bajo)**



**Alto** Valida el objetivo de optimizar la
experiencia eliminando la
fragmentación de canales.


**Alto** Respalda la necesidad técnica de
conocer el estatus y ubicación del
inventario en todo el sistema.



**Explicación**


**Integración** **de**
**Sistemas**


**Arquitectura de**
**Software**


**Caso aplicado al**
**retail / sedes**


**Impacto**
**operativo** **/**
**Cliente**



Propone la "Integración
Total" que abarca
promoción, precio,
información de producto,
transacciones y
fulfillment.


Menciona la necesidad de
investigar "arquitecturas
de información y
operaciones" y
"plataformas de soporte".


Utiliza casos de estudio
como Tesco y Argos
(múltiples tiendas y
centros de distribución).


Enfatiza que la
integración mejora la
utilización de recursos y
la experiencia del
consumidor.



**Alto** Sustenta la necesidad de un
mecanismo (como un
middleware) que unifique estas
funciones.


**Medio** Valida que la omnicanalidad
requiere una base arquitectónica,
aunque no prescribe patrones
específicos.


**Alto** Provee un precedente directo para
aplicaciones en retail multisede
similar al caso "Sifrah".


**Alto** Justifica la investigación desde la
perspectiva de beneficios de
negocio y satisfacción del cliente.



**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**


Al ser un artículo de gestión de operaciones y marketing, carece de especificaciones técnicas
detalladas de ingeniería de software.



**Elemento faltante**

**o débil**


**Domain-Driven**
**Design (DDD)**


**Arquitectura**
**Orientada** **a**
**Eventos (EDA)**


**Middleware** **de**
**Integración**



**Qué muestra la fuente** **Impacto**
**sobre** **mi**
**título**



Habla de "conectividad" e
"interacciones" entre
agentes.


Propone "integración
funcional", pero no
menciona el componente
tecnológico.



**Explicación**



No evidenciado. Neutral La fuente no aborda patrones
de diseño de software a nivel
de código                        - lógica de
dominio.



Medio Describe funcionalmente el
comportamiento de eventos,
pero no menciona el patrón
técnico EDA.


Medio Valida la _necesidad_ del
middleware, pero no la
_herramienta_        - tipo de
middleware a usar.


**Tiempo** **Real**
**(Técnico)**



Menciona el intercambio
de información "oportuno"
(timely information).



Bajo No define "tiempo real" desde
la perspectiva de latencia de
datos         - sincronización
computacional.



**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


**Marco Teórico Sólido:** Provee la teoría de **Sistemas Adaptativos Complejos (CAS)**
para justificar por qué la arquitectura debe ser flexible y reactiva.

**Taxonomía de Integración:** Desglosa exactamente qué debe integrarse (precios,
stock, transacciones), lo que sirve para definir los **Dominios** en el diseño orientado a
dominios (DDD).

**Validación de Visibilidad:** Clasifica los tipos de visibilidad (producto, demanda,
stock), proporcionando una estructura para los **Eventos** que el sistema debe capturar.

**Contras:**


**Ausencia de Detalles de Ingeniería:** No proporciona diagramas de arquitectura de
software, solo modelos conceptuales de negocio.

**Enfoque en Gestión:** Se centra más en el "qué" y "por qué" de la omnicanalidad que
en el "cómo" tecnológico de su implementación.

**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


**Muy Alto:** Es una fuente esencial para el **Marco Teórico** y la **Justificación** . Define
los conceptos de integración y visibilidad que son el núcleo de tu título, permitiéndote
mapear tus "dominios" y "eventos" a necesidades de negocio validadas
académicamente.


**RECOMENDACIÓN ESTRATÉGICA**


Utiliza esta fuente para **estructurar tus dominios de software** .


1.​ **Definición de Dominios (DDD):** Usa la "Integración de Funciones" (Tabla 3 del

artículo) para identificar los subdominios de tu arquitectura: _Promotion, Transaction,_
_Pricing, Fulfillment, Reverse Logistics_ y _Product Information_ .
2.​ **Identificación de Eventos (EDA):** Las "Tipologías de Visibilidad" (Tabla 4) te

permiten definir qué eventos de dominio son críticos (ej. Eventos de Stock, Eventos
de Envío, Eventos de Suministro).
3.​ **Justificación del Middleware:** Cita la necesidad de "total integración" entre las tres

dimensiones (etapa, tipo y agente) para justificar la implementación de un
middleware que actúe como orquestador.


**VEREDICTO FINAL**


Esta fuente **fortalece significativamente el título de investigación** al proporcionar un marco
conceptual tridimensional que demanda exactamente lo que tu título propone: una
arquitectura integrada que garantice visibilidad. Aunque no menciona las tecnologías
específicas (DDD, EDA, Middleware), establece los **requisitos funcionales y teóricos** que
hacen que tu propuesta sea no solo válida, sino necesaria para resolver la complejidad de los
sistemas adaptativos en el retail moderno.


