# Framework de Evaluacion, Gobernanza y Evolucion Continua

Este directorio contiene el sistema documental oficial para evaluar, priorizar, evolucionar y mantener trazabilidad del middleware y de su arquitectura de soporte.

## Fuente normativa

La fuente normativa principal es:

- [Middleware_Acceptance_Evaluation_Framework.md](Middleware_Acceptance_Evaluation_Framework.md)

## Estructura del sistema

### Matrices de evaluacion principal

- `01_Matriz_Arquitectura.csv`
- `02_Matriz_Middleware.csv`
- `03_Matriz_Integracion.csv`
- `04_Matriz_Observabilidad.csv`
- `05_Matriz_Seguridad.csv`
- `06_Matriz_Operacion.csv`
- `07_Matriz_IA.csv`
- `08_Matriz_Calidad.csv`

Estas matrices evaluan capacidades, evidencias, brechas, impacto, prioridad, dependencias y trazabilidad documental por criterio.

### Matrices de consolidacion

- `09_Matriz_Madurez_Global.csv`
- `10_Matriz_Aceptacion_Final.csv`

Estas matrices consolidan el puntaje por dominio y la decision ejecutiva de aceptacion o remediacion.

### Matrices especializadas de evolucion

- `11_Matriz_Evolucion.csv`
- `12_Matriz_Prompts.csv`
- `13_Matriz_Trazabilidad.csv`
- `14_Matriz_Dependencias.csv`

Estas matrices convierten cada criterio en un artefacto accionable para:

- identificar brechas;
- proponer mejoras;
- derivar acciones tecnicas y documentales;
- generar prompts reutilizables;
- mantener trazabilidad entre analisis, arquitectura, pruebas y documentacion.

## Guias metodologicas

Las siguientes guias explican como usar el framework de extremo a extremo:

- [01_Guia_Framework_Evaluacion.md](01_Guia_Framework_Evaluacion.md)
- [02_Guia_Uso_Matrices.md](02_Guia_Uso_Matrices.md)
- [03_Guia_Evaluacion_Software.md](03_Guia_Evaluacion_Software.md)
- [04_Guia_Instrumentos_Medicion.md](04_Guia_Instrumentos_Medicion.md)
- [05_Guia_Puntuacion_Global.md](05_Guia_Puntuacion_Global.md)
- [06_Guia_Iteracion_Framework.md](06_Guia_Iteracion_Framework.md)

## Rol de la Matriz de Prompts

`12_Matriz_Prompts.csv` actua como el nucleo operativo para convertir una brecha detectada en un prompt reutilizable.
Su funcion es recoger contexto, restricciones, documentacion, dependencias, riesgos, validaciones, pruebas y resultado esperado.

## Columnas comunes del sistema

Las matrices de evaluacion principal usan esta estructura base:

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

## Como usar el framework

1. Selecciona la matriz del dominio o del artefacto que quieres revisar.
2. Verifica la evidencia documental y tecnica asociada a cada criterio.
3. Asigna un puntaje de `0` a `5`.
4. Registra brechas, dependencias, riesgos e impacto.
5. Usa la matriz de evolucion para convertir la brecha en plan de accion.
6. Usa la matriz de prompts para generar instrucciones reutilizables para Cursor.
7. Consolida el resultado en la matriz global y cierra la decision en la matriz final.

## Escala de puntaje

- `0` = inexistente
- `1` = muy deficiente
- `2` = deficiente
- `3` = aceptable
- `4` = bueno
- `5` = excelente

## Criterio de madurez

- `0-1` = no cumple
- `2` = cumple parcialmente
- `3` = cumple
- `4` = cumple satisfactoriamente
- `5` = cumple de forma sobresaliente

## Regla de gobierno

Ningun criterio debe quedar reducido a una nota aislada. Cada fila debe permitir responder:

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

## Referencias base

- [docs/architecture/Architecture_Blueprint.md](../architecture/Architecture_Blueprint.md)
- [docs/architecture/middleware_database_architecture.md](../architecture/middleware_database_architecture.md)
- [docs/production/Plan_Middleware.md](../production/Plan_Middleware.md)
- [docs/production/Plan_Integraciones.md](../production/Plan_Integraciones.md)
- [docs/production/Plan_Observabilidad.md](../production/Plan_Observabilidad.md)
- [docs/production/Plan_Seguridad.md](../production/Plan_Seguridad.md)
- [docs/production/Plan_Tenants.md](../production/Plan_Tenants.md)
- [docs/production/Plan_Resiliencia.md](../production/Plan_Resiliencia.md)
- [docs/production/Plan_CI_CD.md](../production/Plan_CI_CD.md)
- [docs/production/Plan_Cloud.md](../production/Plan_Cloud.md)
- [docs/production/Plan_APIs.md](../production/Plan_APIs.md)
- [docs/production/Plan_Calidad.md](../production/Plan_Calidad.md)
- [docs/production/Plan_Logs.md](../production/Plan_Logs.md)
- [docs/production/Plan_Monitoreo.md](../production/Plan_Monitoreo.md)
- [docs/production/ADR_001_instancia_por_cliente.md](../production/ADR_001_instancia_por_cliente.md)
- [docs/production/ADR_009_opentelemetry_distributed_tracing.md](../production/ADR_009_opentelemetry_distributed_tracing.md)
- [docs/production/ADR_010_tenant_lifecycle_management.md](../production/ADR_010_tenant_lifecycle_management.md)
- [docs/production/ADR_011_friendly_routing_multitenant.md](../production/ADR_011_friendly_routing_multitenant.md)
- [docs/Analisis_v0.1](../Analisis_v0.1/)
- [docs/Analisis_v0.2](../Analisis_v0.2/)
