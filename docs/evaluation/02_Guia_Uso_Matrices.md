# Guia de Uso de las Matrices

## 1. Objetivo

Esta guia explica que representa cada matriz, como debe completarse y como debe interpretarse.

La regla principal es simple: cada matriz debe servir para evaluar y para actuar.

## 2. Inventario de matrices

### 2.1 Matrices de dominio

#### `01_Matriz_Arquitectura.csv`

Evalua desacoplamiento, modularidad, extensibilidad, mantenibilidad y escalabilidad futura.

#### `02_Matriz_Middleware.csv`

Evalua routing, validacion de envelope, persistencia operacional, retries, DLQ y valor tecnico del middleware.

#### `03_Matriz_Integracion.csv`

Evalua integracion multicanal y pipeline de adaptacion o transformacion.

#### `04_Matriz_Observabilidad.csv`

Evalua logs, metricas y trazabilidad.

#### `05_Matriz_Seguridad.csv`

Evalua seguridad de acceso, hardening y auditoria.

#### `06_Matriz_Operacion.csv`

Evalua provisioning, lifecycle de tenant, resiliencia operativa y cloud readiness.

#### `07_Matriz_IA.csv`

Evalua IA documental, IA arquitectonica y gobernanza de IA.

#### `08_Matriz_Calidad.csv`

Evalua versionado de APIs, idempotencia y coherencia documental.

### 2.2 Matrices de consolidacion

#### `09_Matriz_Madurez_Global.csv`

Resume la madurez por dominio.

#### `10_Matriz_Aceptacion_Final.csv`

Resume la decision ejecutiva por dominio.

### 2.3 Matrices especializadas

#### `11_Matriz_Evolucion.csv`

Convierte la brecha en plan de mejora.

#### `12_Matriz_Prompts.csv`

Convierte el analisis en prompts reutilizables.

#### `13_Matriz_Trazabilidad.csv`

Expone las fuentes y evidencias de respaldo.

#### `14_Matriz_Dependencias.csv`

Explica dependencias, bloqueos y mitigaciones.

## 3. Estructura de una fila

Las matrices de dominio usan como base estas columnas:

| Columna | Significado |
|---|---|
| `ID` | Identificador unico del criterio |
| `Dominio` | Area del proyecto que se evalua |
| `Capacidad` | Capacidad evaluada |
| `Criterio` | Enunciado resumido del criterio |
| `Descripcion` | Definicion operacional del criterio |
| `Que_Evalua` | Que se esta midiendo o revisando |
| `Como_Se_Evalua` | Metodo de revision |
| `Evidencia_Necesaria` | Tipo de evidencia requerida |
| `Documentos_Base` | Documentos oficiales que soportan el criterio |
| `Fuentes_Analisis` | Documentos del corpus de analisis que ayudan a justificarlo |
| `Brecha` | Diferencia entre el estado deseado y el actual |
| `Mejora` | Accion para cerrar la brecha |
| `Impacto` | Efecto esperado de la mejora |
| `Prioridad` | Urgencia relativa |
| `Componentes_Afectados` | Subsistemas impactados |
| `Archivos_A_Modificar` | Archivos que probablemente cambiarian |
| `Pruebas_Requeridas` | Verificaciones necesarias |
| `Documentacion_A_Actualizar` | Documentacion que debe sincronizarse |
| `Matrices_Relacionadas` | Otras matrices a las que se conecta |
| `Dependencias` | Requisitos previos o bloqueos |
| `Riesgos` | Riesgos de no actuar o actuar mal |
| `Puntaje` | Valor entre 0 y 5 |
| `Resultado` | Interpretacion del puntaje |
| `Observaciones` | Notas del evaluador |

## 4. Como completar una matriz

1. Seleccionar el criterio.
2. Leer la documentacion base y las fuentes de analisis.
3. Confirmar si la capacidad existe o solo esta documentada parcialmente.
4. Registrar la brecha detectada.
5. Definir una mejora concreta y verificable.
6. Identificar impacto, prioridad, dependencias y riesgos.
7. Enumerar componentes, archivos, pruebas y documentacion afectada.
8. Asignar puntaje.
9. Guardar observaciones.

## 5. Interpretacion de las columnas

### 5.1 Columnas descriptivas

`ID`, `Dominio`, `Capacidad`, `Criterio` y `Descripcion` identifican el criterio y no deben usarse para registrar hallazgos.

### 5.2 Columnas de evaluacion

`Que_Evalua`, `Como_Se_Evalua`, `Evidencia_Necesaria`, `Documentos_Base` y `Fuentes_Analisis` definen el juicio metodologico.

### 5.3 Columnas de accion

`Brecha`, `Mejora`, `Impacto`, `Prioridad`, `Componentes_Afectados`, `Archivos_A_Modificar`, `Pruebas_Requeridas`, `Documentacion_A_Actualizar`, `Dependencias` y `Riesgos` convierten el hallazgo en plan.

### 5.4 Columnas de control

`Puntaje`, `Resultado`, `Observaciones` registran la evaluacion final.

## 6. Interpretacion de la puntuacion

La escala es:

- `0` = inexistente
- `1` = muy deficiente
- `2` = deficiente
- `3` = aceptable
- `4` = bueno
- `5` = excelente

### 6.1 Lectura operativa

- `0-1`: no cumple.
- `2`: cumple parcialmente.
- `3`: cumple.
- `4`: cumple satisfactoriamente.
- `5`: cumple de forma sobresaliente.

## 7. Columnas obligatorias

No se debe dejar vacias estas columnas en las matrices de dominio:

- `ID`
- `Dominio`
- `Capacidad`
- `Criterio`
- `Descripcion`
- `Que_Evalua`
- `Como_Se_Evalua`
- `Evidencia_Necesaria`
- `Documentos_Base`
- `Fuentes_Analisis`
- `Brecha`
- `Mejora`
- `Impacto`
- `Prioridad`
- `Componentes_Afectados`
- `Pruebas_Requeridas`
- `Documentacion_A_Actualizar`
- `Matrices_Relacionadas`
- `Dependencias`
- `Riesgos`
- `Puntaje`
- `Resultado`

`Archivos_A_Modificar` y `Observaciones` pueden quedar vacias solo si no aplica, pero es preferible completar ambas.

## 8. Columnas que nunca deben modificarse manualmente

En las matrices de consolidacion, estas columnas deben derivarse, no editarse a mano:

- `Puntaje`
- `Resultado`
- `Puntaje_Ponderado`
- `Puntaje_Promedio`
- `Peso_Acumulado`
- `Puntaje_Maximo`
- `Porcentaje`
- `Nivel_Madurez`
- `Estado`
- `Decision`

En las matrices de dominio, `ID`, `Dominio` y `Capacidad` deben permanecer estables para no romper la trazabilidad historica.

## 9. Columnas que pueden extenderse

Las siguientes columnas admiten enriquecimiento si se mantiene el significado original:

- `Observaciones`
- `Riesgos`
- `Dependencias`
- `Documentacion_A_Actualizar`
- `Archivos_A_Modificar`
- `Pruebas_Requeridas`
- `Componentes_Afectados`

Si se necesitan nuevos campos, el cambio debe aplicarse de forma uniforme en las matrices relacionadas.

## 10. Matrices de consolidacion

### 10.1 `09_Matriz_Madurez_Global.csv`

Esta matriz se alimenta de las matrices de dominio.

No se usa para listar evidencia nueva.
Se usa para consolidar el estado del proyecto.

### 10.2 `10_Matriz_Aceptacion_Final.csv`

Esta matriz traduce la madurez en una decision de aceptacion o condicionamiento.

No reemplaza la evaluacion detallada.
La resume.

## 11. Matriz de prompts

La matriz de prompts es el puente entre evaluacion y ejecucion.

### Consume

- matrices de dominio;
- matriz de evolucion;
- matriz de trazabilidad;
- matriz de dependencias;
- documentacion de analisis;
- documentacion tecnica y operacional.

### Produce

- contexto;
- restricciones;
- componentes afectados;
- archivos potenciales;
- riesgos;
- validaciones;
- pruebas;
- documentacion a actualizar;
- prompt listo para usar.

## 12. Ejemplo completo

### Ejemplo de criterio

Supongamos un criterio de middleware:

- `Dominio`: Middleware
- `Capacidad`: Reintentos y DLQ
- `Criterio`: Manejo de fallos transitorios y definitivos

### Ejemplo de completado

| Columna | Valor de ejemplo |
|---|---|
| `Que_Evalua` | Gestion de errores transitorios y definitivos |
| `Como_Se_Evalua` | Revisar si existe politica de retry, DLQ y acciones de resolucion |
| `Evidencia_Necesaria` | `Plan_Resiliencia.md`, `Plan_Middleware.md`, runbooks |
| `Brecha` | DLQ manual o reintentos no automatizados |
| `Mejora` | Introducir politica de retry y requeue operable |
| `Impacto` | Reduce perdida de eventos y MTTR |
| `Prioridad` | Alta |
| `Componentes_Afectados` | Retry policy, DLQ, jobs, monitoring |
| `Pruebas_Requeridas` | Tests de retry, DLQ, requeue e idempotencia |
| `Documentacion_A_Actualizar` | `Plan_Resiliencia.md` |
| `Puntaje` | 2 |
| `Resultado` | Cumple parcialmente |

## 13. Ejemplo de uso de la matriz de prompts

### Entrada resumida

- brecha: DLQ manual o reintentos no automatizados
- impacto: perdida de eventos y MTTR alto
- prioridad: alta
- pruebas: retry, DLQ, idempotencia

### Salida esperada

Un prompt que pida:

1. revisar `Plan_Resiliencia.md`;
2. identificar archivos donde se implementa la politica de retry;
3. proponer cambios sobre la cola y los jobs;
4. actualizar los runbooks;
5. preparar pruebas.

## 14. Regla de consistencia

Si una columna se agrega en una matriz de dominio, se debe revisar:

- si afecta consolidacion;
- si afecta evolucion;
- si afecta prompts;
- si afecta trazabilidad;
- si afecta dependencias.

## 15. Referencias

- [docs/evaluation/Middleware_Acceptance_Evaluation_Framework.md](Middleware_Acceptance_Evaluation_Framework.md)
- [docs/evaluation/12_Matriz_Prompts.csv](12_Matriz_Prompts.csv)
- [docs/evaluation/13_Matriz_Trazabilidad.csv](13_Matriz_Trazabilidad.csv)
- [docs/evaluation/14_Matriz_Dependencias.csv](14_Matriz_Dependencias.csv)

