**DATOS DE LA FUENTE**


**Título:** Microservices and event-driven architecture: Revolutionizing e-commerce systems.
**Autores:** George Thomas.
**Año:** 2025.
**Tipo de estudio:** Artículo de revisión (Review Article).
**Sector analizado:** Sistemas de comercio electrónico (E-commerce).

**RESUMEN EJECUTIVO**


La fuente examina cómo la **arquitectura de microservicios y la arquitectura orientada a**
**eventos (EDA)** han transformado los sistemas de comercio electrónico modernos,
especialmente tras los retos de escalabilidad expuestos por la pandemia de COVID-19. El
autor propone la descomposición de sistemas monolíticos en componentes independientes y
especializados que se comunican de forma asíncrona mediante eventos. El enfoque principal
destaca beneficios críticos como la **sincronización en tiempo real de la jornada de compra**,
el aislamiento de servicios para seguridad y la escalabilidad elástica durante picos de
demanda. Asimismo, aborda desafíos de implementación mediante patrones como **Sagas,**
**Event Sourcing y CQRS** para garantizar la consistencia de datos en entornos distribuidos.

**PUNTOS QUE RESPALDAN EL TÍTULO**


Esta fuente ofrece un respaldo técnico y teórico de nivel excepcional para los pilares
arquitectónicos y operativos del título propuesto.



**Componente** **del**
**título**


**Arquitectura** **de**
**Software**


**Domain-Driven**
**Design (DDD)**


**Arquitectura**
**orientada a eventos**



**Evidencia encontrada en la**
**fuente**


Propone el cambio de
paradigma de monolitos hacia
microservicios independientes
y especializados.


Afirma explícitamente que
una implementación exitosa
comienza con DDD para
identificar límites naturales de
servicios.


Describe a la EDA como la
base para sistemas
desacoplados que interactúan
mediante notificaciones de
eventos significativos.



**Nivel** **de**
**respaldo**



**Alto** Valida la necesidad de una
estructura distribuida para
manejar la complejidad de
los ecosistemas de retail
modernos.


**Alto** Respalda el uso de DDD
para asegurar que los
microservicios se alineen
con capacidades de negocio
y no solo técnicas.


**Alto** Valida el uso de eventos para
lograr baja latencia y
procesamiento asíncrono,
críticos en el retail.



**Explicación**


**Visibilidad** **de**
**Inventario** **en**
**Tiempo Real**


**Optimización**
**Omnicanal**


**Escalabilidad** **y**
**Modernización**


**Impacto operativo /**
**Cliente**



Indica que la EDA permite la
propagación en tiempo real de
eventos de inventario,
manteniendo la precisión
durante altos volúmenes de
venta.


Menciona la sincronización a
lo largo de la "jornada de
compra" y la coordinación de
actividades para experiencias
fluidas.


Destaca la elasticidad para
manejar fluctuaciones
dramáticas de tráfico
mediante escalado horizontal
independiente.


Reporta beneficios como
aceleración del tiempo al
mercado, mejora en la
experiencia del cliente y
eficiencia operativa.



**Alto** Provee evidencia de que el
inventario debe actualizarse
asíncronamente en respuesta
a eventos de compra para ser
exacto.


**Medio** Aunque no usa el término
"omnicanal" en cada párrafo,
describe funcionalmente la
orquestación entre sistemas
de pago, stock y envío.


**Alto** Justifica la modernización
tecnológica para evitar la
degradación del rendimiento
que sufren los sistemas
tradicionales.


**Alto** Vincula directamente las
decisiones arquitectónicas
con resultados de negocio y
satisfacción del usuario.



**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**



**Elemento**
**faltante o débil**


**Caso** **aplicado**
**local (Huancayo)**


**Middleware** **de**
**Integración**
**(término)**


**Retail Físico /**
**Sedes**



**Qué muestra la fuente** **Impacto**
**sobre mi**
**título**



Se enfoca en un contexto
global y general de
e-commerce.


Describe la "infraestructura
de mensajería" y "colas de
eventos" como
intermediarios.


Se centra primordialmente
en sistemas de e-commerce
y servicios web.



**Explicación**



**Bajo** No resta validez técnica, pero el caso
"Sifrah Sedes Huancayo" queda
como el aporte de contextualización
del investigador.


**Medio** No utiliza la frase exacta
"Middleware de Integración" de
forma recurrente, aunque describe
sus funciones tecnológicas.


**Medio** La visibilidad de inventario se trata
desde la perspectiva del sistema
distribuido, no detalla la
sincronización física entre sedes
específicas.


**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


**Validación de la tríada técnica:** Conecta directamente DDD, Microservicios y EDA
como la solución estándar para el retail moderno.

**Métricas de Rendimiento:** Provee evidencia de que los sistemas orientados a
eventos logran menor latencia que los modelos tradicionales de solicitud-respuesta.

**Patrones de Solución:** Detalla patrones como **Sagas y CQRS** que son necesarios
para que la arquitectura propuesta en el título sea viable técnicamente.

**Contras:**


**Falta de especificidad geográfica:** No ofrece datos sobre el sector retail en regiones
específicas como Huancayo o Perú [No evidenciado].


**Enfoque Digital:** El peso del análisis está en el e-commerce, dejando la integración
con el punto de venta físico (POS) en tienda como una inferencia necesaria.


**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


**Muy Alto:** Esta fuente es el **cimiento técnico** para tu tesis. Proporciona la justificación
académica de por qué elegir una arquitectura orientada a dominios y eventos es la decisión
correcta para optimizar un sistema de retail que requiere visibilidad en tiempo real.


**RECOMENDACIÓN ESTRATÉGICA**


Esta fuente debe ser el núcleo de tu **Marco Teórico y Validación Técnica** :


1.​ **Definición de Microservicios y EDA:** Cita a Thomas (2025) para definir cómo la

asincronía y el desacoplamiento reducen la latencia y mejoran la conversión.
2.​ **Justificación de DDD:** Utiliza el artículo para argumentar que DDD es el paso previo

indispensable para definir los "Dominios" de tu arquitectura.
3.​ **Módulo de Inventario:** Usa la Tabla 3 ("Key Events in E-Commerce") para

estructurar tus propios eventos de dominio: OrderCreated, InventoryReserved, etc..
4.​ **Resiliencia:** Emplea el concepto de **Event Sourcing** descrito para justificar cómo tu

sistema en Huancayo mantendrá la integridad de datos ante fallos de conexión o picos
de demanda.


**VEREDICTO FINAL**


La fuente **fortalece de manera integral el título propuesto**, validando la combinación de
DDD y EDA como una solución revolucionaria para la gestión de inventarios y la
escalabilidad en el retail moderno. Al demostrar que la sincronización en tiempo real depende
de la propagación de eventos y el desacoplamiento de servicios, el artículo convierte los
componentes técnicos de tu título en una **solución recomendada por la literatura científica**
**de vanguardia** para resolver los problemas de visibilidad y eficiencia operativa que planteas


