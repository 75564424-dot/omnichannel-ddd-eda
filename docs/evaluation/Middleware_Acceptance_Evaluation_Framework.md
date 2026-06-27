# Governance, Evaluation and Continuous Evolution Framework

**Proyecto:** plataforma de integracion omnicanal orientada a dominios y eventos con middleware de integracion, trazabilidad, observabilidad, seguridad y soporte de IA.

**Proposito:** transformar el sistema de evaluacion en un framework documental capaz de:

- evaluar el estado actual del sistema;
- detectar brechas y riesgos;
- priorizar mejoras;
- generar planes tecnicos y documentales;
- producir prompts reutilizables para Cursor;
- mantener trazabilidad entre analisis, arquitectura, pruebas y evoluciones futuras.

**Regla central:** este framework no inventa capacidades que contradigan la documentacion existente en `docs/`.

---

## 1. Alcance

El framework cubre ocho dominios de evaluacion principal:

- Arquitectura
- Middleware
- Integracion
- Observabilidad
- Seguridad
- Operacion
- IA
- Calidad

Ademas cubre cuatro matrices transversales:

- Evolucion
- Prompts
- Trazabilidad
- Dependencias

---

## 2. Fuentes autorizadas

Las evaluaciones deben basarse en la documentacion existente en:

- `docs/architecture/`
- `docs/production/`
- `docs/Plan_Desarrollo_Modulos_v0.1/`
- `docs/Plan_Desarrollo_Servicio_v0.1/`
- `docs/Analisis_v0.1/`
- `docs/Analisis_v0.2/`
- `docs/testing/`
- `docs/refactorizacion/`
- `docs/personal_notes/` cuando actue como material de apoyo y no contradiga la documentacion oficial

---

## 3. Filosofia de evaluacion

La evaluacion no se limita a puntuar.

Cada criterio debe permitir responder:

- que evalua;
- como se evalua;
- que evidencia necesita;
- que documentos lo respaldan;
- que brecha existe;
- como mejorarla;
- que impacto tendria;
- que prioridad posee;
- que componentes afecta;
- que archivos deberian modificarse;
- que pruebas deberian ejecutarse;
- que documentacion debe actualizarse;
- que prompt puede generarse automaticamente.

---

## 4. Estructura documental

### 4.1 Matrices principales

Las matrices `01` a `08` contienen la evaluacion detallada por criterio.

### 4.2 Matriz global

La matriz `09` consolida madurez por dominio y permite ver el estado agregado del sistema.

### 4.3 Matriz ejecutiva

La matriz `10` decide aceptacion, condicionamiento o rechazo tecnico.

### 4.4 Matrices transversales

Las matrices `11` a `14` convierten la evaluacion en accion:

- `11_Matriz_Evolucion.csv`: brechas, mejoras, acciones tecnicas y documentales.
- `12_Matriz_Prompts.csv`: construccion de prompts reutilizables.
- `13_Matriz_Trazabilidad.csv`: fuentes, evidencias y respaldo documental.
- `14_Matriz_Dependencias.csv`: prerrequisitos, bloqueos e impacto cruzado.

---

## 5. Criterios de normalizacion

Para mantener consistencia, cada matriz debe:

- usar nombres homogeneos de dominios y capacidades;
- evitar sinonimos duplicados para la misma capacidad;
- describir cada criterio en lenguaje operativo;
- referenciar documentos reales del repositorio;
- separar evidencia documental, brecha y propuesta de mejora;
- incluir impacto, prioridad, riesgos y dependencias;
- conservar trazabilidad hacia otras matrices;
- usar un formato reutilizable para generar prompts.

---

## 6. Modelo de columnas

### 6.1 Matrices principales

Las matrices principales deben usar como base estas columnas:

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
- `Archivos_A_Modificar`
- `Pruebas_Requeridas`
- `Documentacion_A_Actualizar`
- `Matrices_Relacionadas`
- `Dependencias`
- `Riesgos`
- `Puntaje`
- `Resultado`
- `Observaciones`

### 6.2 Matriz de evolucion

La matriz de evolucion debe resumir:

- brecha detectada;
- mejora concreta;
- impacto;
- prioridad;
- componentes afectados;
- archivos candidatos;
- pruebas;
- documentacion a actualizar;
- dependencias;
- riesgos;
- accion tecnica;
- accion documental.

### 6.3 Matriz de prompts

La matriz de prompts debe contener toda la informacion necesaria para construir un prompt reutilizable con:

- objetivo;
- contexto;
- restricciones;
- documentacion relacionada;
- componentes afectados;
- archivos posiblemente modificados;
- riesgos;
- validaciones;
- pruebas requeridas;
- documentacion a actualizar;
- resultado esperado.

### 6.4 Matriz de trazabilidad

La matriz de trazabilidad debe registrar:

- fuentes de analisis;
- documentos base;
- evidencia principal;
- relaciones entre matrices;
- cobertura de respaldo documental;
- riesgos de trazabilidad.

### 6.5 Matriz de dependencias

La matriz de dependencias debe explicar:

- de que depende un criterio;
- que bloquea;
- que habilita;
- que afecta;
- que precondiciones necesita;
- que riesgos introduce;
- como mitigarlos.

---

## 7. Escala de evaluacion

- `0` = inexistente
- `1` = muy deficiente
- `2` = deficiente
- `3` = aceptable
- `4` = bueno
- `5` = excelente

### Interpretacion

- `0-1`: no cumple
- `2`: cumple parcialmente
- `3`: cumple
- `4`: cumple satisfactoriamente
- `5`: cumple de forma sobresaliente

---

## 8. Criterios de prioridad

La prioridad debe reflejar tanto riesgo como valor estrategico:

- `Critica`
- `Alta`
- `Media`
- `Baja`

La prioridad se asigna segun:

- impacto operativo;
- impacto arquitectonico;
- riesgo de seguridad;
- dependencia de otros criterios;
- bloqueo para otras mejoras;
- urgencia de documentacion o pruebas.

---

## 9. Modelo de puntuacion

### 9.1 Puntaje por criterio

`puntaje_ponderado = peso x puntaje`

### 9.2 Puntaje por dominio

`puntaje_dominio = suma(puntaje_ponderado) / suma(pesos)`

### 9.3 Puntaje global

`puntaje_global = suma(puntaje_ponderado de todos los criterios) / suma(pesos) `

### 9.4 Umbrales sugeridos

- `0 a 39%` = no cumple
- `40 a 59%` = cumple parcialmente
- `60 a 74%` = cumple
- `75 a 89%` = cumple satisfactoriamente
- `90 a 100%` = cumple de forma sobresaliente

---

## 10. Flujo de gobernanza

1. Identificar el criterio.
2. Revisar documentos base y fuentes de analisis.
3. Evaluar evidencia disponible.
4. Registrar brecha y riesgo.
5. Definir mejora concreta.
6. Asignar prioridad e impacto.
7. Declarar archivos, pruebas y documentacion afectada.
8. Registrar dependencias y relaciones entre matrices.
9. Generar prompt reutilizable.
10. Consolidar en matrices de madurez y aceptacion.

---

## 11. Generacion automatica de prompts

Cada criterio debe poder transformarse en un prompt con esta estructura:

- Objetivo
- Contexto
- Restricciones
- Documentacion relacionada
- Componentes afectados
- Archivos posiblemente modificados
- Riesgos
- Validaciones
- Pruebas requeridas
- Documentacion a actualizar
- Resultado esperado

### Regla de construccion

El prompt no debe suponer capacidad inexistente. Solo puede proponer acciones compatibles con la documentacion vigente.

### Uso esperado

Los prompts deben servir para:

- implementar una mejora;
- optimizar una capacidad;
- refactorizar una zona tecnica;
- actualizar documentacion;
- preparar pruebas;
- cerrar brechas detectadas.

---

## 12. Trazabilidad documental

Cada criterio debe indicar al menos:

- una fuente primaria de `docs/production/` o `docs/architecture/`;
- una fuente de analisis en `docs/Analisis_v0.1/` o `docs/Analisis_v0.2/` cuando aplique;
- una evidencia tecnica o documental concreta;
- la matriz relacionada donde se consolida o se deriva la accion.

---

## 13. Aceptacion

La aceptacion final no depende solo del promedio.

Tambien debe revisar:

- brechas criticas sin cerrar;
- dependencias bloqueadas;
- riesgos sin mitigacion;
- falta de trazabilidad;
- falta de pruebas requeridas;
- inconsistencia documental;
- ausencia de prompt reutilizable para la mejora.

Si un dominio cumple numericamente pero tiene brechas criticas de seguridad, observabilidad o operacion, la solucion no debe considerarse aceptada sin condicionantes.

---

## 14. Referencias documentales

- [docs/architecture/Architecture_Blueprint.md](../architecture/Architecture_Blueprint.md)
- [docs/architecture/middleware_database_architecture.md](../architecture/middleware_database_architecture.md)
- [docs/production/Plan_Middleware.md](../production/Plan_Middleware.md)
- [docs/production/Plan_Integraciones.md](../production/Plan_Integraciones.md)
- [docs/production/Plan_Observabilidad.md](../production/Plan_Observabilidad.md)
- [docs/production/Plan_Seguridad.md](../production/Plan_Seguridad.md)
- [docs/production/Plan_Operacion.md](../production/Plan_Operacion.md)
- [docs/production/Plan_Resiliencia.md](../production/Plan_Resiliencia.md)
- [docs/production/Plan_Tenants.md](../production/Plan_Tenants.md)
- [docs/production/Plan_APIs.md](../production/Plan_APIs.md)
- [docs/production/Plan_CI_CD.md](../production/Plan_CI_CD.md)
- [docs/production/Plan_Cloud.md](../production/Plan_Cloud.md)
- [docs/production/Plan_Calidad.md](../production/Plan_Calidad.md)
- [docs/production/Plan_Logs.md](../production/Plan_Logs.md)
- [docs/production/Plan_Monitoreo.md](../production/Plan_Monitoreo.md)
- [docs/production/ADR_001_instancia_por_cliente.md](../production/ADR_001_instancia_por_cliente.md)
- [docs/production/ADR_009_opentelemetry_distributed_tracing.md](../production/ADR_009_opentelemetry_distributed_tracing.md)
- [docs/production/ADR_010_tenant_lifecycle_management.md](../production/ADR_010_tenant_lifecycle_management.md)
- [docs/production/ADR_011_friendly_routing_multitenant.md](../production/ADR_011_friendly_routing_multitenant.md)
- [docs/Analisis_v0.1](../Analisis_v0.1/)
- [docs/Analisis_v0.2](../Analisis_v0.2/)

