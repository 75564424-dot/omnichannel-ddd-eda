**DATOS DE LA FUENTE**


**Título:** An Omnichannel Retailing Operation for Solving Joint Inventory Replenishment
Control and Dynamic Pricing Problems From the Perspective of Customer Experience.
**Autores:** Guitao Xu, Kai Kang y Mengyao Lu.
**Año:** 2023.
**Tipo de estudio:** Artículo de investigación (Modelado matemático y optimización).
**Sector analizado:** Retail omnicanal (específicamente supermercados y tiendas de moda).

**RESUMEN EJECUTIVO**


La fuente propone un **modelo de optimización dinámica** para sistemas de inventario en retail
omnicanal, utilizando la **teoría de control óptimo** . El estudio analiza cuatro modos de
operación: online, offline, BOPS ( _buy online, pick up in store_ ) y **BOPS-PLUS** (una
integración profunda que incluye distribución logística desde la tienda física). El enfoque
principal es maximizar las ganancias descontadas del sistema equilibrando la inversión en la
**experiencia del cliente**, el control de reposición de inventario y los precios dinámicos. Los
autores concluyen que en situaciones de escasez de inventario, el modelo omnicanal
profundamente integrado es superior al modelo tradicional de doble canal.

**PUNTOS QUE RESPALDAN EL TÍTULO**


El artículo proporciona una base teórica y empírica muy fuerte para justificar la necesidad
operativa de la arquitectura propuesta en el título.



**Componente**
**del título**


**Optimización**
**Omnicanal**


**Visibilidad de**
**Inventario en**
**Tiempo Real**


**Caso aplicado**
**al** **retail** **/**
**múltiples**
**sedes**



**Evidencia encontrada en la**
**fuente**


Se analizan estrategias de
control óptimo para integrar
canales online y físicos
(BOPS-PLUS).


El modelo utiliza el estado
instantáneo del inventario
( _Io_ ( _t_ ) e _In_ ( _t_ )) para ajustar
precios y reposición.


Se utiliza el caso real de
**WUMART** (más de 791
tiendas) para validar la
usabilidad del modelo.



**Nivel** **de**
**respaldo**
**(Alto/Med**
**io/Bajo)**



**Alto** Valida que la integración de canales
mejora la rentabilidad y la eficiencia
operativa frente a canales aislados.


**Alto** Demuestra que conocer el estado del
inventario en tiempo real es crítico
para la toma de decisiones
dinámicas y evitar pérdidas por falta
de stock.


**Alto** Ofrece un precedente directo de
aplicación en una cadena minorista
con múltiples puntos de contacto,
análogo al caso de Sifrah.



**Explicación**


**Impacto**
**operativo** **o**
**experiencia**
**del cliente**


**Arquitectura**
**de Software**



El modelo integra
cuantitativamente la
inversión en la experiencia
del cliente como motor de
demanda.


Menciona componentes
arquitectónicos funcionales
de ecosistemas como
SMARTBUY.



**Alto** Respalda que la tecnología
(arquitectura) debe servir para
mejorar la satisfacción y lealtad del
cliente mediante la conveniencia.


**Bajo** Reconoce que la omnicanalidad
requiere una estructura tecnológica,
aunque se enfoca en el modelo
matemático y no en el diseño de
componentes de software.



**FALENCIAS O LIMITACIONES FRENTE AL TÍTULO**


Al ser un artículo centrado en la investigación operativa y la economía de la gestión, carece
de especificaciones técnicas de ingeniería de software.



**Elemento faltante**

**o débil**


**Domain-Driven**
**Design (DDD)**


**Arquitectura**
**orientada a eventos**


**Middleware** **de**
**integración**


**Escalabilidad** **y**
**modernización**



**Qué muestra la**
**fuente**



Describe cambios
dinámicos en el
inventario como
funciones del
tiempo.


Menciona la
"integración de
canales" y el
"vínculo sin
costuras"
( _seamless link_ ).


Se centra en la
optimización de
beneficios y
costos a largo
plazo.



**Impacto**
**sobre** **mi**
**título**



**Explicación**



**No evidenciado** . Neutral El artículo opera sobre el "dominio" de
retail, pero no utiliza ni menciona el
patrón arquitectónico de diseño de
software orientado a dominios.



Medio Aunque el sistema reacciona a "eventos"
de demanda y retorno, no propone
explícitamente una arquitectura de
software basada en eventos (EDA).


Bajo Valida la necesidad funcional de integrar
sistemas fragmentados, pero no propone
el uso de un middleware como solución
tecnológica.


Medio No discute atributos de calidad de
software como la escalabilidad técnica o
la modernización de sistemas legados.


**PROS Y CONTRAS DEL ARTÍCULO RESPECTO A MI TÍTULO**


**Pros:**


**Justificación del Problema:** Provee el dato estadístico de que el **79% de los**
**minoristas** tienen dificultades con la gestión de inventario omnicanal.


**Validación del Modelo BOPS-PLUS:** Sustenta que la integración de la logística de
última milla con el inventario de la tienda física (punto central de tu título) es el modo
más rentable en crisis de stock.


**Métricas Cuantitativas:** Ofrece fórmulas de "beneficio descontado" que pueden
servir para medir el éxito de la arquitectura propuesta en el caso Sifrah.


**Contras:**


**Ausencia de Ingeniería de Software:** No detalla patrones de código, frameworks o
diagramas de componentes (Middleware/Events).


**Enfoque en Gestión:** El artículo resuelve el "qué" (precios y cantidades), mientras
que tu título busca resolver el "cómo" (arquitectura de software).


**NIVEL DE UTILIDAD PARA MI INVESTIGACIÓN**


**Alto:** Es una fuente excelente para el **planteamiento del problema** y la **justificación**
**económica/operativa** . Permite sustentar por qué una "Visibilidad en Tiempo Real" y una
"Optimización Omnicanal" no son solo deseos técnicos, sino requisitos para la supervivencia
financiera del retail.


**RECOMENDACIÓN ESTRATÉGICA**


**Justificación del problema:** Utiliza la cita sobre la dificultad del 79% de los retailers para
gestionar inventarios para resaltar la relevancia de tu tesis.


**Marco Teórico (Omnicanalidad):** Emplea la definición de **BOPS-PLUS** de esta fuente para
describir el nivel de integración que tu arquitectura de software busca habilitar (donde la
tienda física sirve como centro de distribución y punto de recogida).


**Sustento de la Solución:** Cita el hallazgo de que el "inventario compartido" y la visibilidad
en tiempo real son la clave para optimizar las ganancias cuando hay escasez en las sedes.


**Validación:** Usa el caso de **WUMART** como ejemplo de que este tipo de integraciones son
estándares globales en el retail moderno.


**VEREDICTO FINAL**


La fuente **fortalece significativamente los objetivos de negocio y la relevancia del título**,
validando que la integración omnicanal profunda y la visibilidad de inventario en tiempo real
son los pilares de la rentabilidad retail actual. Sin embargo, deja un **vío técnico absoluto** en
cuanto a la implementación mediante DDD, EDA y Middleware, lo cual posiciona tu


investigación como una contribución técnica necesaria y original que llena el hueco entre el
modelo matemático de optimización y la construcción real del sistema de software.


