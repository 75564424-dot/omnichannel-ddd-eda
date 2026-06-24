# Middleware Acceptance Evaluation Framework

**Proyecto:** Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integracion para la Optimizacion Omnicanal y la Visibilidad de Inventario en Tiempo Real

**Propósito del documento:** establecer el estándar oficial de evaluación y aceptación arquitectónica del middleware desarrollado, de forma que pueda ser reutilizado para matrices CSV, hojas Excel, dashboards ejecutivos y reportes de aceptación.

**Principio rector:** esta metodología no evalúa marketing, ventas, UX ni volumen comercial. Evalúa calidad arquitectónica, capacidad operativa, madurez técnica y aporte real del middleware a la solución.

**Base documental:** la evaluación se sustenta únicamente en la documentación existente en `docs/`, especialmente arquitectura, ADR, planes de middleware, observabilidad, seguridad, APIs, cloud, CI/CD, tenants, runbooks, análisis de IA y documentación científica.

---

## 1. Introducción

Este framework define cómo evaluar si el middleware cumple su propósito arquitectónico y operativo dentro del sistema omnicanal. El proyecto se diseñó como una plataforma basada en DDD, EDA, middleware de integración, observabilidad, seguridad, APIs, multi-tenant por instancia y soporte de IA para la ingeniería de software.

La evaluación se centra en responder preguntas como:

- ¿La arquitectura es adecuada para el problema real?
- ¿El middleware agrega valor tangible como capa de integración?
- ¿Existe desacoplamiento entre productores y consumidores?
- ¿La trazabilidad permite seguir un evento de principio a fin?
- ¿La observabilidad es suficiente para operación y diagnóstico?
- ¿La integración omnicanal es efectiva y segura?
- ¿La IA aportó valor metodológico o técnico?
- ¿La solución es sostenible y escalable?

### Alcance

El framework cubre:

- arquitectura;
- middleware;
- integración;
- observabilidad;
- seguridad;
- operación;
- IA aplicada;
- calidad de software en sentido arquitectónico y operativo.

### Limitaciones

Este documento no crea casos de prueba, no define pruebas unitarias, no evalúa interfaz de usuario y no mide éxito comercial. Tampoco reemplaza auditorías técnicas de código o pruebas funcionales; las complementa a nivel de aceptación arquitectónica.

### Contexto documental

La documentación del repositorio muestra una evolución desde un sistema de retail hacia una plataforma de integración y observabilidad por eventos, con arquitectura de instancia por cliente, APIs versionadas, monitoreo, auditoría, CI/CD, hardening y runbooks. Este framework formaliza esa evolución en una lógica de aceptación reproducible.

**Fuentes:** [docs/architecture/middleware_database_architecture.md](../architecture/middleware_database_architecture.md), [docs/production/Plan_Middleware.md](../production/Plan_Middleware.md), [docs/production/Plan_Observabilidad.md](../production/Plan_Observabilidad.md), [docs/production/Plan_Integraciones.md](../production/Plan_Integraciones.md), [docs/production/Plan_Seguridad.md](../production/Plan_Seguridad.md), [docs/production/Plan_APIs.md](../production/Plan_APIs.md), [docs/production/Plan_Cloud.md](../production/Plan_Cloud.md), [docs/production/Plan_CI_CD.md](../production/Plan_CI_CD.md), [docs/production/Plan_Tenants.md](../production/Plan_Tenants.md), [docs/production/Plan_Logs.md](../production/Plan_Logs.md), [docs/production/Plan_Monitoreo.md](../production/Plan_Monitoreo.md), [docs/production/Plan_Calidad.md](../production/Plan_Calidad.md), [docs/production/Reporte_Implementacion.md](../production/Reporte_Implementacion.md), [docs/production/ADR_001_instancia_por_cliente.md](../production/ADR_001_instancia_por_cliente.md), [docs/production/ADR_009_opentelemetry_distributed_tracing.md](../production/ADR_009_opentelemetry_distributed_tracing.md), [docs/production/ADR_010_tenant_lifecycle_management.md](../production/ADR_010_tenant_lifecycle_management.md), [docs/production/ADR_011_friendly_routing_multitenant.md](../production/ADR_011_friendly_routing_multitenant.md), [docs/Plan_Desarrollo_Modulos_v0.1/README.md](../Plan_Desarrollo_Modulos_v0.1/README.md), [docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md](../Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md), [docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md](../Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md), [docs/Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md](../Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md), [docs/Plan_Desarrollo_Servicio_v0.1/DDD_en_la_arquitectura.md](../Plan_Desarrollo_Servicio_v0.1/DDD_en_la_arquitectura.md), [docs/Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md](../Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md), [docs/Analisis_v0.2](../Analisis_v0.2/).

---

## 2. Filosofía de Evaluación

La aceptación arquitectónica no depende de:

- cantidad de usuarios;
- ingresos;
- diseño visual;
- actividad comercial;
- popularidad de la solución.

La aceptación depende de:

- calidad arquitectónica;
- cumplimiento funcional del middleware como plataforma;
- valor operativo real;
- mantenibilidad;
- observabilidad;
- escalabilidad;
- trazabilidad;
- seguridad;
- capacidad de evolución.

### Principios

1. La arquitectura se evalúa por su capacidad de sostener el problema real.
2. El middleware se evalúa por su valor como núcleo de integración, no como pantalla.
3. La observabilidad se evalúa por su capacidad de explicar el sistema, no por la presencia decorativa de dashboards.
4. La IA se evalúa por utilidad controlada, validación humana y gobernanza.
5. La aceptación es acumulativa: una capacidad puede estar presente pero no ser suficientemente madura.

---

## 3. Dominios de Evaluación

### Dominio 1. Arquitectura

Evalúa si la solución está bien estructurada como plataforma de integración orientada a dominios y eventos.

### Dominio 2. Middleware

Evalúa si el núcleo de eventos, enrutamiento, tracking y persistencia operativa cumple su propósito.

### Dominio 3. Integración

Evalúa si los canales, proveedores, adapters, connectors y webhooks permiten integración empresarial efectiva.

### Dominio 4. Observabilidad

Evalúa si logs, métricas, trazas, auditoría y alertas permiten operar y diagnosticar el sistema.

### Dominio 5. Seguridad

Evalúa si la superficie de acceso y operación está protegida de forma consistente.

### Dominio 6. Operación

Evalúa si la solución puede desplegarse, gobernarse, mantenerse, respaldarse y recuperarse.

### Dominio 7. IA aplicada

Evalúa si la IA aportó valor real a la ingeniería, la documentación y el diseño, bajo validación humana.

### Dominio 8. Calidad de Software

Evalúa si la solución muestra contratos, versionado, mantenimiento, coherencia y control técnico adecuados.

---

## 4. Capacidades por Dominio

### 4.1 Arquitectura

- desacoplamiento;
- modularidad;
- extensibilidad;
- mantenibilidad;
- consistencia conceptual;
- separación de responsabilidades;
- alineación DDD/EDA;
- capacidad de evolución.

### 4.2 Middleware

- routing;
- validación de envelope;
- transformación;
- orquestación básica;
- persistencia de eventos;
- manejo de DLQ;
- reintentos;
- sincronización de registry;
- trazabilidad técnica.

### 4.3 Integración

- canales;
- providers;
- adapters;
- connectors;
- webhooks;
- credenciales seguras;
- interoperabilidad;
- health de integraciones.

### 4.4 Observabilidad

- logs;
- métricas;
- alertas;
- trazabilidad;
- auditoría;
- dashboards;
- correlación;
- SLO / SLI.

### 4.5 Seguridad

- autenticación;
- autorización;
- hardening;
- rate limiting;
- CORS;
- security headers;
- API keys / sesiones;
- protección de endpoints.

### 4.6 Operación

- provisioning;
- onboarding;
- lifecycle tenant;
- backups;
- restore;
- cloud readiness;
- CI/CD;
- runbooks;
- resiliencia.

### 4.7 IA aplicada

- aceleración documental;
- apoyo arquitectónico;
- generación controlada;
- apoyo a código y utilidades;
- validación humana;
- gobernanza;
- trazabilidad del uso;
- reducción de tiempo en tareas repetitivas.

### 4.8 Calidad de Software

- versionado de APIs;
- consistencia terminológica;
- idempotencia;
- compatibilidad retroactiva;
- cobertura documental;
- gobernanza de cambios;
- claridad de contratos;
- estabilidad de la solución.

---

## 5. Criterios de Evaluación

Cada criterio se evalúa con la siguiente estructura:

- **Nombre**
- **Descripción**
- **Evidencia requerida**
- **Método de medición**
- **Escala**
- **Peso**

### 5.1 Criterios maestros

#### C01. Desacoplamiento

- **Dominio:** Arquitectura
- **Descripción:** capacidad de evitar dependencias punto a punto entre productores y consumidores.
- **Evidencia requerida:** documentación de middleware, EDA, event bus, integraciones, bounded contexts.
- **Método de medición:** revisión documental de separación de responsabilidades y rutas de integración.
- **Escala:** 0 a 5.
- **Peso:** 5.

#### C02. Modularidad

- **Dominio:** Arquitectura
- **Descripción:** separación clara entre módulos, bounded contexts y responsabilidades.
- **Evidencia requerida:** documentación de módulos, control/dashboard, layers, DDD.
- **Método de medición:** consistencia en la división de componentes y ausencia de mezcla de dominios.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C03. Extensibilidad

- **Dominio:** Arquitectura
- **Descripción:** capacidad de incorporar nuevos canales, consumidores o capacidades sin reescritura central.
- **Evidencia requerida:** planes de integraciones, registry sync, adapters, connectors, packs.
- **Método de medición:** evidencia de extensiones por configuración o por pack.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C04. Mantenibilidad

- **Dominio:** Arquitectura
- **Descripción:** facilidad para evolucionar el sistema con control y bajo riesgo.
- **Evidencia requerida:** planes de refactor, reports de modularidad, ADR, runbooks.
- **Método de medición:** calidad de la documentación de evolución y reducción de deuda.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C05. Routing de eventos

- **Dominio:** Middleware
- **Descripción:** capacidad de enrutar eventos a consumidores correctos según el catálogo y la suscripción.
- **Evidencia requerida:** middleware plan, eventbus config, sync-config, queue tracking.
- **Método de medición:** presencia documentada de publish, tracking y subscribers.
- **Escala:** 0 a 5.
- **Peso:** 5.

#### C06. Validación de envelope

- **Dominio:** Middleware
- **Descripción:** aceptación de eventos solo con estructura y metadatos mínimos.
- **Evidencia requerida:** runbooks, APIs, docs de publish.
- **Método de medición:** existencia de contrato de `event_id`, `event_type`, `occurred_at`, `payload`.
- **Escala:** 0 a 5.
- **Peso:** 3.

#### C07. Persistencia operacional

- **Dominio:** Middleware
- **Descripción:** capacidad de almacenar cola, DLQ, event store, feed y registry.
- **Evidencia requerida:** arquitectura de BD, diccionario de datos, planes de middleware.
- **Método de medición:** número y coherencia de tablas operativas documentadas.
- **Escala:** 0 a 5.
- **Peso:** 5.

#### C08. Reintentos y DLQ

- **Dominio:** Middleware
- **Descripción:** manejo de fallos transitorios y definitivos.
- **Evidencia requerida:** architecture DB, resiliency plan, monitoring plan.
- **Método de medición:** existencia de retry, DLQ, resolution actions y alerting.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C09. Integración multicanal

- **Dominio:** Integración
- **Descripción:** capacidad de conectar POS, e-commerce, ERP, CRM, mobile y webhooks.
- **Evidencia requerida:** plan de integraciones, diccionario, runbooks.
- **Método de medición:** variedad de canales y patrones de integración documentados.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C10. Adaptación y transformación

- **Dominio:** Integración
- **Descripción:** uso de adapters para validar, transformar, enriquecer o enrutar.
- **Evidencia requerida:** plan de integraciones, arquitectura de BD.
- **Método de medición:** presencia de adapters, connectors y pipeline de integración.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C11. Seguridad de acceso

- **Dominio:** Seguridad
- **Descripción:** control de acceso a APIs, UI y operaciones administrativas.
- **Evidencia requerida:** plan de seguridad, autenticación, API docs.
- **Método de medición:** presencia de auth, abilities, tokens, sesiones y hardening.
- **Escala:** 0 a 5.
- **Peso:** 5.

#### C12. Hardening

- **Dominio:** Seguridad
- **Descripción:** defensas básicas de capa de plataforma.
- **Evidencia requerida:** CORS, security headers, rate limiting, WAF docs.
- **Método de medición:** cantidad de controles defensivos documentados y su coherencia.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C13. Logs

- **Dominio:** Observabilidad
- **Descripción:** capacidad de registrar eventos relevantes de operación y diagnóstico.
- **Evidencia requerida:** plan de logs, observability docs, runbooks.
- **Método de medición:** presencia de logging estructurado, contexto y retención.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C14. Métricas

- **Dominio:** Observabilidad
- **Descripción:** capacidad de medir latencia, throughput, errores, cola y salud.
- **Evidencia requerida:** plan de observabilidad, monitoreo, dashboards.
- **Método de medición:** existencia de métricas unificadas, SLI y dashboards.
- **Escala:** 0 a 5.
- **Peso:** 5.

#### C15. Trazabilidad

- **Dominio:** Observabilidad
- **Descripción:** capacidad de seguir un evento desde origen hasta destino.
- **Evidencia requerida:** trace logs, correlation_id, dashboards, runbooks.
- **Método de medición:** cobertura documental de event_id, trace_id, spans y correlacion.
- **Escala:** 0 a 5.
- **Peso:** 5.

#### C16. Auditoría

- **Dominio:** Seguridad
- **Descripción:** capacidad de registrar acciones administrativas y cambios sensibles.
- **Evidencia requerida:** audit_logs, plan de logs, security docs.
- **Método de medición:** existencia de flujo de auditoría y evidencias de gobierno.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C17. Provisioning

- **Dominio:** Operación
- **Descripción:** capacidad de crear y preparar una instancia o tenant.
- **Evidencia requerida:** plan de tenants, runbook onboarding, ADR de instancia.
- **Método de medición:** claridad de pasos, artefactos y estados del provisioning.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C18. Lifecycle de tenant

- **Dominio:** Operación
- **Descripción:** capacidad de activar, suspender y restaurar tenants.
- **Evidencia requerida:** ADR de lifecycle, runbook v1.5, planes de tenants.
- **Método de medición:** presencia de estados, transiciones y reglas de operación.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C19. Resiliencia operativa

- **Dominio:** Operación
- **Descripción:** capacidad de tolerar fallos, recuperar y mantener continuidad.
- **Evidencia requerida:** plan de resiliencia, DLQ, retries, runbooks.
- **Método de medición:** existencia de estrategias de recuperación y fallback.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C20. Cloud readiness

- **Dominio:** Operación
- **Descripción:** capacidad de desplegarse de forma reproducible en cloud o contenedor.
- **Evidencia requerida:** plan cloud, CI/CD, deployment guides.
- **Método de medición:** presencia de Docker, pipeline, health checks y backup.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C21. IA documental

- **Dominio:** IA aplicada
- **Descripción:** capacidad de la IA para apoyar síntesis, clasificación y documentación.
- **Evidencia requerida:** corpus de analisis de IA y resultados de consolidacion.
- **Método de medición:** volumen de apoyo documental y calidad de integración con la evidencia.
- **Escala:** 0 a 5.
- **Peso:** 3.

#### C22. IA arquitectónica

- **Dominio:** IA aplicada
- **Descripción:** capacidad de la IA para apoyar analisis y estructuración arquitectónica.
- **Evidencia requerida:** documentos de analisis general y blueprint, revisiones comparativas.
- **Método de medición:** utilidad en la estructuracion de dominios, capas y decisiones.
- **Escala:** 0 a 5.
- **Peso:** 3.

#### C23. Gobernanza de IA

- **Dominio:** IA aplicada
- **Descripción:** control humano, validación y mitigación de riesgos de automatización.
- **Evidencia requerida:** literatura de confianza, oversight y responsible AI.
- **Método de medición:** presencia de revisión humana, riesgos y mitigaciones documentadas.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C24. Versionado de APIs

- **Dominio:** Calidad de Software
- **Descripción:** contrato estable y gestionado de las APIs de plataforma.
- **Evidencia requerida:** OpenAPI, policy de breaking changes, changelog.
- **Método de medición:** claridad de v1, legacy y compatibilidad retroactiva.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C25. Idempotencia

- **Dominio:** Calidad de Software
- **Descripción:** capacidad de evitar efectos duplicados en operaciones repetidas.
- **Evidencia requerida:** plan de APIs, event bus docs, persistence docs.
- **Método de medición:** presencia de claves de idempotencia y unique constraints documentados.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C26. Coherencia documental

- **Dominio:** Calidad de Software
- **Descripción:** consistencia terminológica y alineación entre documentos.
- **Evidencia requerida:** corpus documental, ADR, planes, runbooks.
- **Método de medición:** ausencia de contradicciones graves y trazabilidad de evolución.
- **Escala:** 0 a 5.
- **Peso:** 3.

#### C27. Escalabilidad futura

- **Dominio:** Arquitectura
- **Descripción:** capacidad de evolucionar hacia más carga, más clientes o más componentes.
- **Evidencia requerida:** ADR, planes cloud, observability, middleware, tenants.
- **Método de medición:** claridad del roadmap y de las rutas de evolución.
- **Escala:** 0 a 5.
- **Peso:** 4.

#### C28. Valor técnico del middleware

- **Dominio:** Middleware
- **Descripción:** contribución real del middleware a la solución.
- **Evidencia requerida:** planes, runbooks, diagrams, docs de operacion.
- **Método de medición:** capacidad de centralizar integración, trazabilidad y operación.
- **Escala:** 0 a 5.
- **Peso:** 5.

---

## 6. Modelo de Puntuación

Cada criterio recibe una puntuación entre 0 y 5.

### Escala

- **0 = Inexistente**  
  No existe evidencia documental o la capacidad no está presente.

- **1 = Muy deficiente**  
  Existe una idea o mención aislada, pero sin soporte operativo ni evidencia consistente.

- **2 = Deficiente**  
  La capacidad aparece parcialmente, pero con vacíos graves, contradicciones o dependencia excesiva de trabajo manual.

- **3 = Aceptable**  
  La capacidad existe y es útil, aunque todavía presenta limitaciones o una madurez incompleta.

- **4 = Bueno**  
  La capacidad es sólida, consistente y está respaldada por documentación suficiente.

- **5 = Excelente**  
  La capacidad está completamente documentada, es operativamente madura y constituye una fortaleza clara de la solución.

### Interpretación de la escala

- **0-1:** no aceptable.
- **2:** zona de riesgo.
- **3:** cumple de forma básica.
- **4:** cumple satisfactoriamente.
- **5:** sobresaliente.

---

## 7. Matriz Maestra de Evaluación

La siguiente tabla es la matriz maestra de referencia. Posteriormente puede exportarse a CSV o Excel.

| Dominio | Capacidad | Criterio | Evidencia | Peso | Escala | Valor Esperado |
|---|---|---|---|---:|---|---|
| Arquitectura | Desacoplamiento | C01 | Arquitectura, middleware, EDA, boundaries | 5 | 0-5 | >= 4 |
| Arquitectura | Modularidad | C02 | DDD, modulos, capas, read models | 4 | 0-5 | >= 4 |
| Arquitectura | Extensibilidad | C03 | adapters, connectors, packs, sync | 4 | 0-5 | >= 4 |
| Arquitectura | Mantenibilidad | C04 | ADR, refactor, runbooks, governance | 4 | 0-5 | >= 4 |
| Arquitectura | Escalabilidad futura | C27 | cloud, tenants, observability roadmap | 4 | 0-5 | >= 3 |
| Middleware | Routing de eventos | C05 | event bus, subscriptions, queue tracking | 5 | 0-5 | >= 4 |
| Middleware | Validación de envelope | C06 | publish contract, envelope, API docs | 3 | 0-5 | >= 3 |
| Middleware | Persistencia operacional | C07 | event_store, queue, DLQ, registry | 5 | 0-5 | >= 4 |
| Middleware | Reintentos y DLQ | C08 | retries, DLQ, runbooks, monitoring | 4 | 0-5 | >= 3 |
| Middleware | Valor técnico del middleware | C28 | plans, runbooks, diagrams | 5 | 0-5 | >= 4 |
| Integración | Integración multicanal | C09 | channels, providers, webhooks, integrators | 4 | 0-5 | >= 4 |
| Integración | Adaptación y transformación | C10 | adapters, connectors, pipeline | 4 | 0-5 | >= 3 |
| Seguridad | Seguridad de acceso | C11 | auth, abilities, API keys, sessions | 5 | 0-5 | >= 4 |
| Seguridad | Hardening | C12 | CORS, headers, rate limiting, WAF | 4 | 0-5 | >= 3 |
| Seguridad | Auditoría | C16 | audit_logs, security docs, governance | 4 | 0-5 | >= 3 |
| Observabilidad | Logs | C13 | logging, retention, runbooks | 4 | 0-5 | >= 4 |
| Observabilidad | Métricas | C14 | SLI, dashboards, Prometheus | 5 | 0-5 | >= 4 |
| Observabilidad | Trazabilidad | C15 | correlation_id, trace_logs, spans | 5 | 0-5 | >= 4 |
| Operación | Provisioning | C17 | onboarding, tenant setup, env templates | 4 | 0-5 | >= 3 |
| Operación | Lifecycle de tenant | C18 | suspend/restore, ADR, runbook | 4 | 0-5 | >= 3 |
| Operación | Resiliencia operativa | C19 | DLQ, retries, restore, incident docs | 4 | 0-5 | >= 3 |
| Operación | Cloud readiness | C20 | Docker, CI/CD, backups, health checks | 4 | 0-5 | >= 3 |
| IA aplicada | IA documental | C21 | corpus, synthesis, analysis docs | 3 | 0-5 | >= 3 |
| IA aplicada | IA arquitectónica | C22 | blueprint, architecture synthesis | 3 | 0-5 | >= 3 |
| IA aplicada | Gobernanza de IA | C23 | trust, oversight, responsible AI | 4 | 0-5 | >= 3 |
| Calidad de Software | Versionado de APIs | C24 | OpenAPI, breaking changes, changelog | 4 | 0-5 | >= 4 |
| Calidad de Software | Idempotencia | C25 | idempotency key, unique events | 4 | 0-5 | >= 3 |
| Calidad de Software | Coherencia documental | C26 | corpus, ADR, plans, runbooks | 3 | 0-5 | >= 4 |

---

## 8. Indicadores Arquitectónicos

### 8.1 Desacoplamiento

**Definición:** grado en que productores y consumidores están separados por contratos y eventos, no por dependencias directas.

**Indicadores derivados:**

- número de relaciones punto a punto evitadas;
- porcentaje de flujos mediados por middleware;
- claridad de boundaries DDD.

### 8.2 Cobertura de observabilidad

**Definición:** proporción de componentes críticos cubiertos por logs, métricas, trazas y alertas.

**Indicadores derivados:**

- porcentaje de eventos trazables;
- cobertura de métricas de bus;
- existencia de dashboards por dominio;
- existencia de alertas y runbooks.

### 8.3 Cobertura de auditoría

**Definición:** proporción de acciones sensibles que quedan registradas de forma auditable.

**Indicadores derivados:**

- acciones admin auditadas;
- cambios de configuración auditados;
- operaciones de lifecycle auditadas.

### 8.4 Disponibilidad

**Definición:** capacidad de la arquitectura de mantenerse operativa y recuperable.

**Indicadores derivados:**

- health checks;
- backups;
- restore runbooks;
- resiliencia documentada.

### 8.5 Integración efectiva

**Definición:** capacidad de conectar canales y proveedores con mínima fricción y máxima trazabilidad.

**Indicadores derivados:**

- número de integraciones soportadas;
- presencia de adapters/connectors;
- webhooks documentados;
- credenciales seguras.

### 8.6 Madurez middleware

**Definición:** grado de completitud del middleware como plataforma de integración.

**Indicadores derivados:**

- event store;
- queue tracking;
- DLQ;
- registry sync;
- observability hooks;
- API coverage.

### 8.7 Valor agregado IA

**Definición:** aporte real de la IA a la documentación, arquitectura y desarrollo.

**Indicadores derivados:**

- reducción de tiempo en tareas repetitivas;
- calidad de soporte documental;
- calidad de soporte arquitectónico;
- gobernanza y validación humana.

---

## 9. Indicadores de Middleware

### 9.1 Eventos procesados

Capacidad de medir cuántos eventos entran, se procesan, se enrutan y se completan.

### 9.2 Latencia

Tiempo entre publicación, encolado, procesamiento y consumo.

### 9.3 Throughput

Volumen de eventos procesados por unidad de tiempo.

### 9.4 Errores

Cantidad y proporción de fallos en publicación, encolado, consumo o proyección.

### 9.5 Retries

Número de reintentos y su efectividad.

### 9.6 DLQ

Cantidad de eventos enviados a dead letter queue y porcentaje resuelto.

### 9.7 Capacidad de integración

Cantidad de canales, providers, adapters y connectors documentados y operativos.

---

## 10. Indicadores de Observabilidad

### 10.1 Porcentaje de eventos trazables

Porcentaje de eventos con `event_id`, `correlation_id` y seguimiento en logs o traces.

### 10.2 Cobertura de logs

Porcentaje de acciones o flujos relevantes que dejan evidencia en logs estructurados o contextualizados.

### 10.3 Cobertura de métricas

Porcentaje de dominios o componentes que publican métricas útiles para operación.

### 10.4 Alertas efectivas

Porcentaje de alertas que detectan condiciones reales y no solo ruido.

### 10.5 Cobertura de auditoría

Porcentaje de acciones sensibles registradas en audit logs.

---

## 11. Indicadores de IA

### 11.1 Reducción de tiempo

Comparación entre tarea asistida con IA y tarea manual documentada.

### 11.2 Apoyo documental

Capacidad de la IA para sintetizar, clasificar y estructurar la documentación.

### 11.3 Apoyo arquitectónico

Capacidad de la IA para organizar decisiones, capas y criterios.

### 11.4 Validación humana

Presencia de revisión humana sobre toda salida de IA.

### 11.5 Gobernanza

Existencia de límites, criterios de confianza, trazabilidad y supervisión.

---

## 12. Niveles de Madurez

### Nivel 1. Inicial

- arquitectura poco consolidada;
- documentación parcial;
- observabilidad mínima;
- seguridad básica o dispersa;
- middleware con valor limitado.

### Nivel 2. Funcional

- capacidades principales presentes;
- documentación utilizable;
- middleware operativo pero aún incompleto;
- observabilidad y seguridad parciales.

### Nivel 3. Operativo

- la arquitectura funciona de forma coherente;
- el middleware agrega valor real;
- la trazabilidad es usable;
- existen runbooks y controles de operación.

### Nivel 4. Optimizado

- flujos estables y medibles;
- observabilidad madura;
- seguridad y APIs formalizadas;
- arquitectura bien justificada y mantenible.

### Nivel 5. Enterprise

- la solución es sólida, gobernable y escalable;
- el middleware es un activo de plataforma;
- observabilidad, seguridad y operación están formalizadas;
- la IA se usa de forma controlada y útil;
- la arquitectura demuestra capacidad de evolucionar sin perder coherencia.

---

## 13. Criterios de Aceptación

### No cumple

- la mayoría de capacidades críticas puntúan 0-1;
- el middleware no puede demostrar valor operativo;
- no existe trazabilidad ni observabilidad suficiente;
- la seguridad y la integración son insuficientes.

### Cumple parcialmente

- las capacidades principales existen pero con vacíos importantes;
- el sistema es visible, pero aún no es robusto;
- el middleware agrega valor limitado o inestable.

### Cumple

- la solución demuestra capacidades principales;
- la arquitectura es coherente;
- el middleware opera con valor técnico verificable;
- la observabilidad y seguridad son aceptables.

### Cumple satisfactoriamente

- la mayoría de criterios críticos alcanza 4 o más;
- la arquitectura está bien sustentada;
- el middleware es claramente útil;
- la operación es confiable y el control es razonable.

### Cumple de forma sobresaliente

- casi todos los criterios críticos alcanzan 5;
- la arquitectura es madura, explicable y escalable;
- el middleware es un diferenciador claro;
- la IA, la observabilidad y la seguridad están integradas de forma ejemplar.

---

## 14. Estructura para CSV

La exportación oficial para `evaluation_matrix.csv` debe usar exactamente estas columnas:

| ID | Dominio | Capacidad | Criterio | Evidencia | Peso | Puntaje | Resultado |
|---|---|---|---|---|---:|---:|---|

### Reglas de llenado

- **ID:** código único por criterio, por ejemplo `C01`.
- **Dominio:** arquitectura, middleware, integración, observabilidad, seguridad, operación, IA aplicada o calidad.
- **Capacidad:** nombre de la capacidad evaluada.
- **Criterio:** redacción corta del criterio.
- **Evidencia:** documentos, ADR, planes, runbooks o análisis que sustentan el criterio.
- **Peso:** valor ponderado entre 1 y 5.
- **Puntaje:** valoración obtenida entre 0 y 5.
- **Resultado:** clasificación derivada, por ejemplo `No cumple`, `Cumple parcialmente`, `Cumple`, `Cumple satisfactoriamente`, `Cumple de forma sobresaliente`.

---

## 15. Estructura para Excel

El libro de evaluación oficial debe contener las siguientes hojas:

### Hoja 1. Resumen Ejecutivo

- puntaje total;
- promedio ponderado;
- nivel de madurez;
- estado de aceptación;
- criterios críticos fallidos;
- criterios sobresalientes.

### Hoja 2. Evaluación por Dominio

- dominio;
- puntaje acumulado;
- peso acumulado;
- promedio por dominio;
- estado del dominio.

### Hoja 3. Evaluación por Capacidad

- capacidad;
- criterio;
- evidencia;
- puntaje;
- peso;
- resultado.

### Hoja 4. Indicadores

- indicadores arquitectónicos;
- indicadores de middleware;
- indicadores de observabilidad;
- indicadores de IA.

### Hoja 5. Radar de Madurez

- arquitectura;
- middleware;
- integración;
- observabilidad;
- seguridad;
- operación;
- IA;
- calidad.

### Hoja 6. Aceptación Final

- decisión final;
- fecha;
- responsable;
- observaciones;
- condicionantes;
- siguientes acciones.

---

## 16. Modelo de Cálculo Recomendado

### Puntaje ponderado por criterio

`puntaje_ponderado = peso x puntaje`

### Puntaje total

`puntaje_total = suma(puntaje_ponderado de todos los criterios)`

### Puntaje máximo

`puntaje_maximo = suma(peso x 5 de todos los criterios)`

### Porcentaje de aceptación

`porcentaje = puntaje_total / puntaje_maximo * 100`

### Umbrales sugeridos

- **0 a 39%:** No cumple
- **40 a 59%:** Cumple parcialmente
- **60 a 74%:** Cumple
- **75 a 89%:** Cumple satisfactoriamente
- **90 a 100%:** Cumple de forma sobresaliente

---

## 17. Uso oficial del framework

Este documento debe considerarse la fuente oficial para:

- matrices de aceptación;
- CSV de evaluación;
- reportes ejecutivos;
- tableros de control;
- comparaciones entre versiones;
- análisis de madurez arquitectónica;
- seguimiento de evolución del middleware.

No debe utilizarse para evaluar UX, ventas, marketing o popularidad. Su función es determinar si la solución es arquitectónicamente sólida, operativamente útil y sostenible.

---

## 18. Referencias documentales

- [docs/architecture/middleware_database_architecture.md](../architecture/middleware_database_architecture.md)
- [docs/architecture/middleware_database_dictionary.md](../architecture/middleware_database_dictionary.md)
- [docs/production/Plan_Middleware.md](../production/Plan_Middleware.md)
- [docs/production/Plan_Observabilidad.md](../production/Plan_Observabilidad.md)
- [docs/production/Plan_Integraciones.md](../production/Plan_Integraciones.md)
- [docs/production/Plan_Seguridad.md](../production/Plan_Seguridad.md)
- [docs/production/Plan_APIs.md](../production/Plan_APIs.md)
- [docs/production/Plan_Cloud.md](../production/Plan_Cloud.md)
- [docs/production/Plan_CI_CD.md](../production/Plan_CI_CD.md)
- [docs/production/Plan_Tenants.md](../production/Plan_Tenants.md)
- [docs/production/Plan_Logs.md](../production/Plan_Logs.md)
- [docs/production/Plan_Monitoreo.md](../production/Plan_Monitoreo.md)
- [docs/production/Plan_Calidad.md](../production/Plan_Calidad.md)
- [docs/production/Plan_Resiliencia.md](../production/Plan_Resiliencia.md)
- [docs/production/ADR_001_instancia_por_cliente.md](../production/ADR_001_instancia_por_cliente.md)
- [docs/production/ADR_004_tenant_id_activation.md](../production/ADR_004_tenant_id_activation.md)
- [docs/production/ADR_009_opentelemetry_distributed_tracing.md](../production/ADR_009_opentelemetry_distributed_tracing.md)
- [docs/production/ADR_010_tenant_lifecycle_management.md](../production/ADR_010_tenant_lifecycle_management.md)
- [docs/production/ADR_011_friendly_routing_multitenant.md](../production/ADR_011_friendly_routing_multitenant.md)
- [docs/production/Reporte_Implementacion.md](../production/Reporte_Implementacion.md)
- [docs/production/Auditoria_Produccion.md](../production/Auditoria_Produccion.md)
- [docs/Plan_Desarrollo_Modulos_v0.1/README.md](../Plan_Desarrollo_Modulos_v0.1/README.md)
- [docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md](../Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md)
- [docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md](../Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md)
- [docs/Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md](../Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md)
- [docs/Plan_Desarrollo_Servicio_v0.1/DDD_en_la_arquitectura.md](../Plan_Desarrollo_Servicio_v0.1/DDD_en_la_arquitectura.md)
- [docs/Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md](../Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md)
- [docs/personal_notes/Analisis_General.md](../personal_notes/Analisis_General.md)
- [docs/personal_notes/Observabilidad_pruebas_produccion_local.md](../personal_notes/Observabilidad_pruebas_produccion_local.md)
- [docs/personal_notes/Runbook_cliente_simulado.md](../personal_notes/Runbook_cliente_simulado.md)
- [docs/Analisis_v0.1](../Analisis_v0.1/)
- [docs/Analisis_v0.2](../Analisis_v0.2/)

