**DATOS DE LA FUENTE**


**Título:** A Branch-and-Price Algorithm for an Integrated Online and Offline Retailing
Distribution System with Product Return
**Autores:** Wanchen Jie, Cheng Pei, Jiating Xu y Hong Yan
**Año:** 2024
**Tipo de estudio:** Artículo de investigación (algorítmico y de optimización logística)
**Sector analizado:** Retail de moda rápida (fast fashion) en un entorno de doble canal (online y
offline)

**RESUMEN EJECUTIVO**


La fuente propone un diseño de sistema de distribución logística integrada para empresas de
retail de moda rápida que operan en canales duales. El enfoque principal es la consolidación
de tres sistemas independientes (reabastecimiento de tiendas, pedidos online y devoluciones)
en un solo marco cohesivo para reducir costos operativos y mejorar la utilización de recursos.
Utiliza un algoritmo de **Branch-and-Price (B&P)** para optimizar las rutas y el
emparejamiento entre el inventario de las tiendas físicas y los pedidos de clientes online,
demostrando reducciones de costos de hasta un 49.9%.

**PUNTOS QUE RESPALDAN EL TÍTULO**


La fuente es extremadamente robusta en la validación de la **necesidad operativa** y los
**beneficios de la integración** propuestos en el título.



**Componente** **del**
**título**


**Optimización**
**Omnicanal**


**Visibilidad** **de**
**Inventario** **en**
**Tiempo Real**


**Caso aplicado al**
**retail / múltiples**
**sedes**



**Evidencia encontrada en la**
**fuente**


Propone un sistema que
integra reabastecimiento,
entrega de pedidos online y
devoluciones en un solo
flujo.


El algoritmo realiza un
"emparejamiento global
óptimo" entre el inventario
de tiendas retail y los pedidos
online.


Se basa en una empresa real
con un almacén central y 50
tiendas minoristas dispersas.



**Nivel** **de**
**respaldo**



**Alto** Valida empíricamente que la
integración de canales reduce
el número de vehículos y el
kilometraje innecesario.


**Alto** Demuestra que el inventario
compartido entre sedes es
clave para generar soluciones
factibles y reducir costos.


**Alto** Coincide con la estructura
multisede del caso "Sifrah
Sedes Huancayo" planteado
en el título.



**Explicación**


**Impacto operativo** Reporta un ahorro de costos
de hasta el 49.9% y mejoras
drásticas en la utilización de
capacidad de carga.



**Arquitectura** **de**
**Software**



El estudio formula el sistema
mediante programación
matemática y algoritmos de
optimización (B&P).



**Alto** Provee métricas cuantitativas
que justifican la
implementación de una
solución tecnológica
avanzada.


**Bajo** Respalda la necesidad de una
estructura lógica, pero desde
la perspectiva de algoritmos
matemáticos y no de patrones
de ingeniería de software.



**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**


La fuente, al ser un estudio de optimización matemática y logística, presenta vacíos
significativos en los componentes técnicos de ingeniería de software detallados en el título.



**Elemento faltante o**
**débil**


**Domain-Driven**
**Design (DDD)**


**Arquitectura**
**orientada a eventos**


**Middleware** **de**
**integración**


**Modernización**
**tecnológica**



**Qué muestra la fuente** **Impacto**
**sobre** **mi**
**título**



Gestiona pedidos y
devoluciones (que son
eventos), pero no
menciona el patrón EDA.


Menciona la integración
de sistemas como un
concepto operativo.


Se enfoca en la eficiencia
algorítmica más que en la
infraestructura de TI
moderna.



**Explicación**



**No evidenciado** . Neutral El artículo no aborda patrones
de diseño de software como
DDD; se centra en modelos
matemáticos de rutas.



Medio Valida la naturaleza "orientada a
eventos" del problema de
negocio, pero no la solución
técnica de software.


Bajo No especifica el uso de un
componente tecnológico
_middleware_ para la
comunicación entre capas.


Medio El enfoque es en el algoritmo
(Branch-and-Price), no en la
"modernización" de la
arquitectura empresarial _per se_ .


**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


Provee una justificación matemática y operativa de alto impacto para la
"Optimización Omnicanal" y la "Visibilidad de Inventario"

El concepto de "Inventory Sharing among Multiple Retail Stores" (compartir
inventario entre sedes) es el respaldo teórico perfecto para tu módulo de visibilidad en
tiempo real

Ofrece una base sólida para el planteamiento del problema, citando ineficiencias de
sistemas independientes (fragmentados)

**Contras:**


Falta de detalle en la implementación de patrones de software (DDD/Eventos) [No
evidenciado].

El estudio es puramente de investigación operativa, no de arquitectura empresarial de
TI
.
**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


Alto La fuente es fundamental para validar la sección de optimización e inventario
compartido y para cuantificar los beneficios esperados en el retail multisede.

**RECOMENDACIÓN ESTRATÉGICA**


Esta fuente debe ser usada como Antecedente de Solución y Validación Técnica:


**Justificación del Problema:** Úsala para citar cómo la operación independiente de
canales en retail genera altos costos y baja utilización de recursos

**Validación del Objetivo de Inventario:** Cita el hallazgo de que el "inventario
compartido entre tiendas retail" es el factor clave para la factibilidad en condiciones
de inventario ajustado

**Métricas de Éxito:** Utiliza el dato del 49.9% de ahorro operativo para proyectar el
impacto potencial de tu arquitectura en el caso Sifrah
.
**VEREDICTO FINAL**


Esta fuente **fortalece significativamente el propósito de negocio y los objetivos operativos**
del título (optimización omnicanal y visibilidad de inventario), pero deja un **espacio abierto**
**(vacío de conocimiento)** para tu propuesta técnica. El artículo demuestra _qué_ hay que
optimizar y _cuánto_ se ahorra, pero no especifica _cómo_ construir la arquitectura de software
(DDD, Eventos, Middleware) para lograrlo, lo cual posiciona tu investigación como el


complemento tecnológico necesario para aplicar estos modelos de optimización en el mundo
real.


