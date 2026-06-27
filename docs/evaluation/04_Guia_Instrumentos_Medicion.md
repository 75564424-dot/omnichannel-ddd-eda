# Guia de Instrumentos de Medicion

## 1. Proposito

Este documento define los instrumentos de medicion que necesita el framework para evaluar el proyecto de forma objetiva.

Un instrumento de medicion es cualquier indicador, tecnica, herramienta o formula que permite transformar evidencia en un juicio consistente.

## 2. Principio de diseño

El framework trabaja con dos capas de medicion:

1. **Medicion documental**: cuando la evidencia esta en documentos, planes o ADR.
2. **Medicion tecnica**: cuando la evidencia se puede verificar con pruebas, datos o instrumentacion.

Si un instrumento tecnico no existe todavia, debe proponerse una estructura futura en lugar de solo decir que falta.

## 3. Catalogo de instrumentos por dominio

### 3.1 Arquitectura

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Cobertura DDD | Cuantos bounded contexts y limites estan claramente descritos | Contar contextos definidos sobre contextos requeridos | porcentaje | 0-100 | revision documental + ADR | por iteracion | 70% | 90%+ | C01, C02, C04, C27 |
| Cohesion | Si cada modulo mantiene una responsabilidad estable | Revisar mezcla de responsabilidades por componente | indice 0-5 | 0-5 | revision arquitectonica | por iteracion | 3 | 4+ | C02, C04 |
| Acoplamiento | Dependencias directas entre componentes | Contar dependencias punto a punto y rutas fuera de middleware | indice 0-5 | 0-5 | blueprint + revision tecnica | por iteracion | 3 | 4+ | C01, C03, C27 |
| Modularidad | Claridad de capas y componentes | Verificar separacion entre dominios y capas | indice 0-5 | 0-5 | architecture blueprint | por iteracion | 3 | 4+ | C02, C04 |

### 3.2 Middleware

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Resiliencia | Capacidad de recuperacion ante fallos | Revisar retry, DLQ, recovery y requeue | indice 0-5 | 0-5 | runbooks + tests | por release | 3 | 4+ | C08, C19, C28 |
| Throughput | Volumen de eventos procesados | Eventos por unidad de tiempo | evt/s | numeric | metrics + load tests | continuo / release | definido por escenario | tendencia estable | C14, C28 |
| Latencia | Tiempo desde publish hasta consumo | Medir p95/p99 de pipeline | ms / s | numeric | observability + k6 | continuo / release | < 2s p95 como guia documental | < 1s p95 si el flujo lo permite | C14, C15, C28 |
| Retries | Efectividad de reintentos | % de eventos recuperados tras retry | porcentaje | 0-100 | retries table + jobs | continuo | 80% | 95%+ | C08, C19 |
| Consistencia eventual | Si las proyecciones alcanzan el estado esperado | Medir lag entre evento y proyeccion | ms / s | numeric | event feed + projections | continuo | documentado y estable | lag bajo y predecible | C07, C15 |

### 3.3 Integracion

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Cobertura de contratos | Proporcion de integraciones con contrato documentado | Contar integraciones con esquema y version | porcentaje | 0-100 | OpenAPI + docs | por release | 80% | 100% | C09, C10, C24 |
| Compatibilidad | Si los conectores respetan el contrato esperado | Pruebas de contrato y validacion de payload | indice 0-5 | 0-5 | contract tests | por release | 3 | 4+ | C09, C10, C24 |
| Versionado | Control de cambios en APIs y eventos | Revisar breaking changes y changelog | indice 0-5 | 0-5 | openapi + changelog | por release | 3 | 4+ | C24 |
| Tasa de transformacion correcta | Si adapters y mapeos preservan datos relevantes | % de casos que pasan sin perdida semantica | porcentaje | 0-100 | integration tests | por release | 90% | 100% | C10 |

### 3.4 Observabilidad

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Cobertura de logs | Flujos relevantes con registro util | % de flujos con logs estructurados | porcentaje | 0-100 | logs + auditoria | continuo | 80% | 95%+ | C13, C16 |
| Cobertura de metricas | Componentes con metricas utiles | % de componentes con SLI | porcentaje | 0-100 | observability stack | continuo | 80% | 95%+ | C14, C28 |
| Cobertura de trazas | Eventos con correlation y trace id | % de eventos trazables end to end | porcentaje | 0-100 | OpenTelemetry / trace logs | continuo | 80% | 95%+ | C15 |
| Alertas accionables | Alertas que indican incidentes reales | % de alertas con runbook y utilidad real | porcentaje | 0-100 | monitoring + runbooks | continuo | 70% | 90%+ | C14, C15 |

### 3.5 Seguridad

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Autenticacion | Si las rutas relevantes requieren identidad | % de endpoints protegidos | porcentaje | 0-100 | auth tests | por release | 90% | 100% | C11 |
| Autorizacion | Si el acceso respeta permisos | % de casos con control de acceso | porcentaje | 0-100 | policy tests | por release | 90% | 100% | C11, C16 |
| Secretos | Exposicion de credenciales | Conteo de secretos sin proteccion | conteo | 0-inf | audit de config | continuo | 0 | 0 | C11, C12 |
| Vulnerabilidades | Riesgo tecnico conocido | Conteo/severidad de hallazgos OWASP | conteo / severidad | mixto | OWASP ZAP, composer audit | por release | sin criticas abiertas | criticas cero | C12 |
| Hardening | Controles defensivos activados | headers, CORS, rate limiting, WAF | indice 0-5 | 0-5 | review config | por release | 3 | 4+ | C12 |

### 3.6 Operacion

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Disponibilidad | Tiempo operativo del sistema | Uptime observado | porcentaje | 0-100 | uptime monitor | continuo | 99% segun contexto | 99.5%+ | C19, C20 |
| Exito de despliegue | Despliegues que llegan sanos a destino | despliegues exitosos / total | porcentaje | 0-100 | CI/CD | por release | 90% | 95%+ | C20 |
| Recuperacion | Capacidad de volver a operacion | RTO y RPO documentados | tiempo / tiempo | numeric | runbooks + drills | por iteracion | definido y probado | mejorable y medido | C19, C20 |
| Exito de backup | Restauraciones completadas correctamente | restore success rate | porcentaje | 0-100 | backup drill | por ciclo | 90% | 100% | C19, C20 |

### 3.7 IA

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Trazabilidad de IA | Si la salida de IA puede rastrearse a su fuente | % de salidas con contexto y revision humana | porcentaje | 0-100 | matriz de prompts + trazabilidad | por iteracion | 100% | 100% | C21, C22, C23 |
| Calidad de prompts | Utilidad y precision del prompt | tasa de prompts reutilizables aprobados | porcentaje | 0-100 | revision manual | por iteracion | 80% | 95%+ | C21, C22, C23 |
| Revision humana | Presencia de validacion humana | % de artefactos IA revisados | porcentaje | 0-100 | governance checklist | por iteracion | 100% | 100% | C23 |
| Explicabilidad | Si se entiende por que se genero una salida | indice de claridad documental | indice 0-5 | 0-5 | review metodologico | por iteracion | 3 | 4+ | C21, C22, C23 |

### 3.8 Calidad

| Instrumento | Que mide | Como medirlo | Unidad | Escala | Herramienta recomendada | Frecuencia | Minimo aceptable | Objetivo | Afecta |
|---|---|---|---|---|---|---|---|---|---|
| Cobertura de pruebas | Proporcion de codigo o comportamientos cubiertos | tests ejecutados / tests esperados | porcentaje | 0-100 | PHPUnit, contratos, E2E | por release | 70% para area relevante | 80%+ | C24, C25, C26 |
| Deuda tecnica | Carga de trabajo pendiente por refactor | indice compuesto | indice 0-5 | 0-5 | review tecnico | por iteracion | 3 | 4+ | C04, C26 |
| Complejidad | Facilidad de mantenimiento tecnico | complejidad relativa por modulo | indice 0-5 | 0-5 | static analysis | por release | 3 | 4+ | C04, C26 |
| Mantenibilidad | Facilidad de cambio sin regresion | indice compuesto | indice 0-5 | 0-5 | review tecnica | por iteracion | 3 | 4+ | C04, C26 |

## 4. Instrumentos que no existen aun como medicion operacional

Algunos instrumentos existen como intencion documental, pero no como medicion automatizada en el repositorio.

En esos casos el framework debe proponer una estructura futura.

### 4.1 Ejemplo: Cobertura DDD

- Nombre: `Cobertura DDD`
- Objetivo: medir cuanto del modelo del sistema esta delimitado por bounded contexts claros.
- Metrica: `contextos_definidos / contextos_identificados`
- Formula: `cobertura = contextos_definidos / contextos_identificados * 100`
- Interpretacion: cuanto mas alto, menor mezcla conceptual.
- Nivel esperado: 90% o mas.
- Evidencia requerida: blueprint, ADR y documentos de dominio.
- Automatizacion posible: revision documental asistida y chequeo de consistencia.

### 4.2 Ejemplo: Calidad de prompts

- Nombre: `Calidad de prompts`
- Objetivo: medir si un prompt puede reutilizarse sin reescritura total.
- Metrica: porcentaje de prompts aprobados en revision manual.
- Formula: `prompts_aprobados / prompts_total * 100`
- Interpretacion: un prompt util debe contener objetivo, contexto, restricciones, pruebas y resultado esperado.
- Nivel esperado: 95% o mas.
- Evidencia requerida: matriz de prompts y matriz de trazabilidad.
- Automatizacion posible: generacion de borradores a partir de brechas.

## 5. Como afectan a la puntuacion global

Cada instrumento alimenta un criterio.
Cada criterio alimenta un dominio.
Cada dominio alimenta el indice global.

No todos los instrumentos pesan igual:

- seguridad, middleware y observabilidad tienen peso critico;
- operacion y arquitectura tienen peso estructural;
- calidad sostiene la evolucion;
- IA aporta valor metodologico, pero no debe dominar la decision final.

## 6. Reglas de uso

- Un instrumento documental no debe reemplazar una prueba tecnica si la prueba es posible.
- Un instrumento tecnico no debe usarse si el proyecto no tiene soporte para medirlo; en ese caso debe documentarse como estructura propuesta.
- Todo instrumento debe indicar fuente documental o mecanismo de medicion.

## 7. Fuentes y herramientas sugeridas en el proyecto

La documentacion actual ya menciona o habilita el uso de:

- OpenAPI y changelog de API;
- PHPUnit;
- PHPStan;
- Pint;
- k6;
- Prometheus;
- Grafana;
- Alertmanager;
- OWASP ZAP;
- runbooks y checklists operativos;
- traces y logs estructurados;
- ADR y blueprints.

## 8. Referencias

- [docs/evaluation/Middleware_Acceptance_Evaluation_Framework.md](Middleware_Acceptance_Evaluation_Framework.md)
- [docs/production/Plan_Observabilidad.md](../production/Plan_Observabilidad.md)
- [docs/production/Plan_Logs.md](../production/Plan_Logs.md)
- [docs/production/Plan_Monitoreo.md](../production/Plan_Monitoreo.md)
- [docs/production/Plan_Seguridad.md](../production/Plan_Seguridad.md)
- [docs/production/Plan_Resiliencia.md](../production/Plan_Resiliencia.md)
- [docs/production/Plan_Cloud.md](../production/Plan_Cloud.md)
- [docs/production/Plan_APIs.md](../production/Plan_APIs.md)
- [docs/production/Plan_Calidad.md](../production/Plan_Calidad.md)

