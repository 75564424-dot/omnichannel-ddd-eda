# Architecture Blueprint

**Proyecto:** Arquitectura de Software Orientada a Dominios y Eventos con Middleware de Integracion para la Optimizacion Omnicanal y la Visibilidad de Inventario en Tiempo Real

**Objetivo del documento:** servir como blueprint arquitectonico visual para que otra IA, un arquitecto o un equipo de diseno pueda construir diagramas profesionales de arquitectura empresarial, tesis, patente y documentacion tecnica sin inferir componentes fuera de la evidencia documental.

**Criterio rector:** este documento no inventa componentes. Todo componente, flujo o capa descrita aqui esta sustentado por la documentacion existente en `docs/`.

---

## 1. Resumen Arquitectonico

El sistema es una plataforma de integracion omnicanal orientada a eventos, construida alrededor de un **Middleware** que funciona como nucleo operacional, un **Event Bus** interno para distribucion y trazabilidad, un **Control Plane** para gestion de tenants y provisioning, un **Dashboard** para observabilidad y varios servicios de seguridad, APIs, integraciones e infraestructura que hacen posible la operacion por instancia o por cliente.

El problema que resuelve es estructural: la documentacion muestra que el negocio necesitaba sincronizar canales fisicos y digitales, mantener visibilidad de inventario en tiempo real, desacoplar productores y consumidores, y evitar la dependencia de procesos batch o de una arquitectura centrada en estado de negocio. La respuesta documentada es una arquitectura DDD + EDA con middleware central, persistencia operacional, trazabilidad, observabilidad y un modelo operativo de instancia por cliente.

### Por que Middleware

El Middleware se eligio porque la documentacion lo define como la capa que centraliza ingesta, validacion, transformacion, enrutamiento, registro y distribucion de eventos. En la arquitectura vigente, el middleware no es una capa de negocio retail; es el servicio de integracion principal. Su valor visual debe representarse como el bloque protagonista del diagrama principal.

### Por que EDA

La arquitectura orientada a eventos permite desacoplar productores y consumidores, sostener comunicacion asincrona, mantener consistencia eventual y habilitar reintentos, DLQ, trazabilidad y replay. La documentacion de arquitectura de datos, flujo y planes de middleware converge en que los eventos son la unidad central de integracion.

### Por que DDD

DDD se eligio para separar bounded contexts y evitar que el bus absorba logica de negocio. La documentacion clasifica al Middleware y al Dashboard como contextos de soporte, mientras que los dominios de negocio reales viven fuera del core. Esto permite representar cada contexto como una caja separada y evitar un diagrama monolitico de funcionalidades mezcladas.

### Por que se utiliza IA

La IA se utiliza como apoyo documental, arquitectonico y tecnico. La carpeta de analisis cientifico muestra que la IA acelera tareas repetitivas, la generacion de borradores, la clasificacion de evidencias y la exploracion de patrones, pero siempre con validacion humana. En el blueprint visual, la IA debe aparecer como **AI Support**, no como componente central.

### Beneficios principales

- desacoplamiento entre canales y consumidores;
- integracion omnicanal centralizada;
- trazabilidad por evento, correlacion y auditoria;
- observabilidad para operacion y diagnostico;
- seguridad y autenticacion formalizadas;
- onboarding y lifecycle por tenant;
- capacidad de evolucionar hacia cloud y escalamiento futuro;
- metodologia documental suficientemente clara para soportar diagramas de tesis y patente.

**Evidencia documental base:** [middleware_database_architecture.md](middleware_database_architecture.md), [middleware_database_dictionary.md](middleware_database_dictionary.md), [Plan_Middleware.md](../production/Plan_Middleware.md), [Plan_Observabilidad.md](../production/Plan_Observabilidad.md), [Plan_Integraciones.md](../production/Plan_Integraciones.md), [Plan_Tenants.md](../production/Plan_Tenants.md), [Plan_APIs.md](../production/Plan_APIs.md), [ADR_001_instancia_por_cliente.md](../production/ADR_001_instancia_por_cliente.md), [ADR_009_opentelemetry_distributed_tracing.md](../production/ADR_009_opentelemetry_distributed_tracing.md), [Plan_Desarrollo_Modulos_v0.1/README.md](../Plan_Desarrollo_Modulos_v0.1/README.md), [Analisis_v0.2](../Analisis_v0.2/).

---

## 2. Componentes Arquitectonicos

### 2.1 Criterio de clasificacion

Las categorias permitidas para el blueprint son:

- Core Business
- Middleware
- Infrastructure
- Observability
- Security
- Integration
- Administration
- AI Support

### 2.2 Catalogo de componentes

#### A. Portal Comercial

- **Categoria:** Administration
- **Proposito:** capturar alta comercial, onboarding y entrada de la empresa cliente al sistema.
- **Responsabilidades:** registrar el cliente, iniciar provisioning, derivar configuracion inicial, activar la instancia o el tenant segun el modelo operativo.
- **Dependencias:** Control Plane, Tenant Management, provisioning, configuracion de instancia.
- **Icono recomendado:** icono de `Organization` o `Building`.

#### B. Control Plane

- **Categoria:** Administration
- **Proposito:** gobernar tenants, lifecycle, provisioning, estados de activacion y configuracion administrativa.
- **Responsabilidades:** crear tenant, suspender, activar, restaurar, administrar inventario de instancias, exponer capacidad SaaS.
- **Dependencias:** BD de control plane, Tenant Management, Security, Observability, Middleware.
- **Icono recomendado:** icono de `Control Center` o `Dashboard Admin`.

#### C. Tenant Management

- **Categoria:** Administration
- **Proposito:** gestionar ciclo de vida tecnico y comercial del tenant.
- **Responsabilidades:** lifecycle, estado, provisioning, registro, metadata, resolucion de instancia.
- **Dependencias:** Control Plane, instance context, logs, auditoria.
- **Icono recomendado:** icono de `User Group` o `Organization`.

#### D. Middleware Core

- **Categoria:** Middleware
- **Proposito:** ser el nucleo de integracion, bus, tracking y operacion de eventos.
- **Responsabilidades:** publicar eventos, validar envelope, persistir queue, operar DLQ, sincronizar registro, exponer APIs de control.
- **Dependencias:** Event Bus, API Gateway, Database, Security, Observability, Integrations.
- **Icono recomendado:** icono de `Hub`, `Middleware` o `Message Broker`.

#### E. Event Bus

- **Categoria:** Middleware
- **Proposito:** distribuir eventos entre productores y consumidores.
- **Responsabilidades:** enrutar, observar suscriptores, conservar metadatos de consumo, sostener asincronia.
- **Dependencias:** Middleware Core, consumers, producers, queue tables.
- **Icono recomendado:** icono de `Event Bus` o `Kafka`.

#### F. API Gateway

- **Categoria:** Middleware
- **Proposito:** unificar acceso HTTP a capacidades de plataforma.
- **Responsabilidades:** versionado, idempotencia, Problem Details, rate limiting, autenticacion de API.
- **Dependencias:** Security, Middleware Core, Integrations, Dashboard APIs.
- **Icono recomendado:** icono de `Gateway`.

#### G. Integraciones

- **Categoria:** Integration
- **Proposito:** conectar canales, proveedores, adapters, connectors y webhooks.
- **Responsabilidades:** ingestiones externas, outbound connectors, validacion de firmas, transformacion y enriquecimiento.
- **Dependencias:** Middleware Core, Security, API Gateway, BD de integraciones.
- **Icono recomendado:** icono de `Plug`, `Link` o `Integration`.

#### H. Dashboard

- **Categoria:** Observability
- **Proposito:** ofrecer vista operativa y read model de eventos, bus y metricas.
- **Responsabilidades:** feed, topologia, series, KPIs, estado de cola, vista del motor, stream.
- **Dependencias:** Middleware Core, observability tables, config de dashboard.
- **Icono recomendado:** icono de `Dashboard`.

#### I. Observability Platform

- **Categoria:** Observability
- **Proposito:** medir y explicar el estado interno del sistema.
- **Responsabilidades:** metricas, trazas, logs, alertas, SLO, exportacion Prometheus, correlacion.
- **Dependencias:** Middleware Core, Monitoring, Logging, Grafana, Prometheus, trace storage.
- **Icono recomendado:** icono de `Monitoring` o `Analytics`.

#### J. Audit Trail

- **Categoria:** Security
- **Proposito:** conservar evidencia de acciones administrativas y cambios sensibles.
- **Responsabilidades:** audit_logs, evidencias de cambios, actor, accion, entidad, timestamp.
- **Dependencias:** Security, Control Plane, Middleware, Identity.
- **Icono recomendado:** icono de `Audit`, `Shield`, `Clipboard` o `History`.

#### K. Security Layer

- **Categoria:** Security
- **Proposito:** asegurar acceso a API, UI, integraciones y operaciones administrativas.
- **Responsabilidades:** autenticacion, autorizacion, abilities, API keys, session auth, CORS, security headers, rate limiting.
- **Dependencias:** API Gateway, Control Plane, Middleware APIs, Dashboard UI.
- **Icono recomendado:** icono de `Shield Lock`.

#### L. Infrastructure

- **Categoria:** Infrastructure
- **Proposito:** hacer posible la ejecucion reproducible y escalable.
- **Responsabilidades:** Docker, CI/CD, health checks, backups, cloud deployment, storage, queueing, scheduler.
- **Dependencias:** Middleware, Dashboard, Monitoring, Database, Secrets, Cloud.
- **Icono recomendado:** icono de `Cloud`, `Server`, `Container` o `Infrastructure`.

#### M. Producers externos

- **Categoria:** Integration
- **Proposito:** originar eventos de dominio o integracion.
- **Responsabilidades:** emitir ventas, pedidos, inventario, catalogos, notificaciones o eventos tecnicos.
- **Dependencias:** API Gateway, Middleware, Integrations.
- **Icono recomendado:** icono de `Store`, `Shopping Cart`, `Factory`, `ERP System`, `Mobile`.

#### N. Consumers internos o de dominio

- **Categoria:** Core Business
- **Proposito:** consumir eventos y actualizar su propio estado o sus proyecciones.
- **Responsabilidades:** inventario, pedidos, logistica, analitica, servicios de lectura.
- **Dependencias:** Event Bus, middleware outputs, read models.
- **Icono recomendado:** icono de `Service`, `Application`, `Database` o `Module`.

#### O. AI Support

- **Categoria:** AI Support
- **Proposito:** asistir el ciclo documental y tecnico.
- **Responsabilidades:** clasificar evidencia, sugerir borradores, apoyar analisis comparativo, generar utilidades repetitivas.
- **Dependencias:** corpus documental, validacion humana.
- **Icono recomendado:** icono de `Spark`, `Brain`, `AI Assistant`.

---

## 3. Inventario Visual de Componentes

| Componente | Icono | Color sugerido | Criticidad | Visible en diagrama principal |
| ---------- | ----- | -------------- | ---------- | ----------------------------- |
| Portal Comercial | Organization / Building | Azul | Alta | Si |
| Control Plane | Control Center / Admin Panel | Azul oscuro | Alta | Si |
| Tenant Management | Organization / Users | Azul | Alta | Si |
| Middleware Core | Hub / Broker | Naranja | Critica | Si |
| Event Bus | Event Bus / Kafka | Naranja | Critica | Si |
| API Gateway | Gateway | Morado oscuro | Critica | Si |
| Integraciones | Plug / Link | Verde azulado | Alta | Si |
| Dashboard | Dashboard | Verde | Alta | Si |
| Observability Platform | Monitoring / Analytics | Verde | Alta | Si |
| Audit Trail | Audit / History | Rojo suave | Media | Si, en capa secundaria |
| Security Layer | Shield Lock | Rojo oscuro | Critica | Si, como capa transversal |
| Infrastructure | Cloud / Server | Gris | Critica | Si, como base |
| Producers externos | Store / Cart / ERP / Mobile | Gris azul | Alta | Si |
| Consumers internos | Service / Module | Gris azul | Alta | Si |
| AI Support | Brain / Spark | Amarillo | Media | No en diagrama principal, si en diagrama metodologico |

### Criterio de visibilidad

- **Visible en diagrama principal:** componentes necesarios para explicar el flujo end-to-end.
- **Visible solo en diagramas secundarios:** componentes de detalle operativo o apoyo metodologico.
- **No visible en diagrama principal:** elementos de soporte documental o IA, salvo que el objetivo del diagrama sea metodologia.

---

## 4. Capas Arquitectonicas

### Capa 1. Control Plane

Incluye:

- Portal Comercial
- Control Plane
- Tenant Management

**Funcion visual:** representar el plano de administracion y ciclo de vida de clientes. Esta capa debe ubicarse arriba o a la izquierda del diagrama principal, porque inicia el onboarding y gobierna el ciclo de vida.

### Capa 2. Middleware Core

Incluye:

- Middleware Core
- API Gateway
- Event Bus

**Funcion visual:** es el centro del diagrama. Debe ocupar el mayor ancho o el mayor peso visual.

### Capa 3. Integraciones

Incluye:

- Integraciones
- Producers externos
- Connectors
- Adapters

**Funcion visual:** conectar entradas y salidas hacia el core. Debe mostrar diversidad de origen pero sin competir visualmente con el Middleware Core.

### Capa 4. Consumidores

Incluye:

- Consumers internos o de dominio
- Services de negocio
- Proyecciones de lectura

**Funcion visual:** representar los sistemas que reaccionan al evento. Deben ubicarse a la derecha o en la parte inferior del core.

### Capa 5. Observabilidad

Incluye:

- Dashboard
- Observability Platform
- Logging
- Monitoring
- Audit Trail

**Funcion visual:** debe aparecer como una franja transversal o como una capa lateral que observa a todo el sistema, no como flujo principal.

### Capa 6. Security

Incluye:

- Security Layer
- Authentication
- Authorization
- Rate limiting
- CORS

**Funcion visual:** capa transversal o marco de proteccion alrededor del core.

### Capa 7. Infrastructure

Incluye:

- Cloud
- Database
- Storage
- Queues
- CI/CD
- Runtime

**Funcion visual:** base inferior de la arquitectura. Debe sostener el resto, no competir con los componentes funcionales.

---

## 5. Actores

### Administrador de Plataforma

Persona que registra tenants, activa o suspende instancias, revisa observabilidad y opera el control plane. Debe aparecer en diagramas de onboarding, tenant lifecycle y administracion.

### Empresa Cliente

Entidad comercial que recibe una instancia, configura modulos y utiliza el sistema para sus canales. Debe aparecer en el diagrama de onboarding y en la narrativa del blueprint.

### Sistemas Externos

ERP, POS, e-commerce, CRM, WMS, mobile apps y otros productores/consumidores externos. Deben aparecer como actores en el borde del diagrama principal o en diagramas de integracion.

### Servicios Integrados

Servicios internos o dominios consumidores que reaccionan a eventos del bus. Deben aparecer en los diagramas de flujo operacional y de consumo.

### Operador de Observabilidad

Usuario tecnico que revisa dashboards, alertas, logs y trazas. Debe aparecer en diagramas de observabilidad y auditoria.

### Operador de Seguridad

Usuario o proceso que administra autenticacion, claves, permisos y hardening. Debe aparecer en diagramas de seguridad y APIs.

### Sistema de IA de Soporte

No es actor de negocio; es un actor de apoyo documental y tecnico. Debe aparecer solo en diagramas metodologicos o de proceso interno.

---

## 6. Flujos Arquitectonicos

### 6.1 Flujo de Registro de Cliente

- **Origen:** Empresa Cliente o Administrador de Plataforma.
- **Destino:** Control Plane.
- **Componentes involucrados:** Portal Comercial, Tenant Management, BD de control plane, Security.
- **Datos intercambiados:** nombre del tenant, slug, metadata, estado inicial, credenciales o configuracion inicial.
- **Lectura visual:** este flujo debe mostrar alta comercial y creacion del registro administrativo, no el detalle del bus.

### 6.2 Flujo de Provisioning

- **Origen:** Control Plane.
- **Destino:** Infraestructura de instancia por cliente.
- **Componentes involucrados:** Tenant Management, Infrastructure, Database, Cloud, CI/CD, runtime.
- **Datos intercambiados:** `.env`, registros de instancia, BD, seeds, rutas, metadata de despliegue.
- **Lectura visual:** debe verse como una cadena de activacion desde el control plane hacia un silo o instancia.

### 6.3 Flujo de Configuracion de Modulos

- **Origen:** Control Plane o configuracion declarativa.
- **Destino:** Middleware Core y Dashboard.
- **Componentes involucrados:** modules catalog, eventbus config, registry sync, Dashboard config.
- **Datos intercambiados:** catalogos de productores, suscriptores, topologia declarativa y persistencia del registry.
- **Lectura visual:** debe mostrar dos fuentes de verdad coordinadas: catalogo del dashboard y suscripciones del bus.

### 6.4 Flujo de Publicacion de Eventos

- **Origen:** Producer externo o integracion.
- **Destino:** API Gateway / Middleware Core / Event Bus.
- **Componentes involucrados:** API Gateway, Middleware Core, Event Bus, Security, Database.
- **Datos intercambiados:** envelope de evento, event_id, event_type, payload, occurred_at, metadata.
- **Lectura visual:** el flujo mas importante del sistema; debe ser el camino central del diagrama principal.

### 6.5 Flujo de Consumo de Eventos

- **Origen:** Event Bus.
- **Destino:** Consumers internos o de dominio.
- **Componentes involucrados:** Event Bus, consumers, projections, downstream services.
- **Datos intercambiados:** evento, metadatos, estado de cola, confirmacion de procesamiento.
- **Lectura visual:** mostrar salida del bus hacia varios consumidores, no hacia uno solo.

### 6.6 Flujo de Observabilidad

- **Origen:** Middleware Core, Event Bus, Dashboard, Integrations.
- **Destino:** Observability Platform, Dashboard, Monitoring.
- **Componentes involucrados:** logs, metrics, trace_logs, Grafana, Prometheus, alert manager.
- **Datos intercambiados:** correlation_id, spans, snapshots, series, alert conditions.
- **Lectura visual:** debe mostrarse como una capa lateral o inferior que recolecta señales de todo el sistema.

### 6.7 Flujo de Auditoria

- **Origen:** acciones administrativas, cambios de configuracion, operaciones sensibles.
- **Destino:** Audit Trail.
- **Componentes involucrados:** Security, Control Plane, Middleware, APIs administrativas.
- **Datos intercambiados:** actor, accion, entidad, before/after, timestamp, tenant_id.
- **Lectura visual:** debe verse como un flujo de evidencia, no como un flujo de negocio.

### 6.8 Flujo de Integraciones

- **Origen:** sistemas externos y canales.
- **Destino:** Integrations y Middleware Core.
- **Componentes involucrados:** channels, providers, adapters, connectors, webhooks, credentials.
- **Datos intercambiados:** requests inbound/outbound, firmas, payloads, respuestas, estado de salud.
- **Lectura visual:** debe mostrar que la integracion no es solo un endpoint, sino una cadena de adaptacion.

---

## 7. Diagramas que deberian existir

### Diagrama 01. Arquitectura General

- **Objetivo:** mostrar el sistema completo en una sola vista de alto nivel.
- **Componentes:** Control Plane, Middleware Core, Event Bus, Integrations, Dashboard, Observability, Security, Infrastructure, Producers externos, Consumers internos.
- **Nivel de detalle:** medio alto, pero sin entrar en clases, tablas o endpoints.

### Diagrama 02. Onboarding Cliente

- **Objetivo:** mostrar alta, provisioning y activacion de una empresa cliente.
- **Componentes:** Portal Comercial, Tenant Management, Control Plane, Infrastructure, Database, Security.
- **Nivel de detalle:** alto en pasos, medio en infraestructura.

### Diagrama 03. Tenant Lifecycle

- **Objetivo:** mostrar activar, suspender, restaurar y su efecto en la instancia.
- **Componentes:** Tenant Management, Control Plane, Instance Context, Middleware, Security, Audit Trail.
- **Nivel de detalle:** alto en estados, medio en implementacion.

### Diagrama 04. Middleware Interno

- **Objetivo:** mostrar ingreso, validacion, persistencia, bus, tracking y DLQ.
- **Componentes:** API Gateway, Middleware Core, Event Bus, Event Store, Queue, DLQ, Registry Sync.
- **Nivel de detalle:** alto.

### Diagrama 05. Observabilidad

- **Objetivo:** mostrar logs, metricas, trazas, dashboards, alertas y auditoria.
- **Componentes:** Observability Platform, Dashboard, Prometheus, Grafana, trace logs, audit logs, Monitoring.
- **Nivel de detalle:** alto.

### Diagrama 06. Integraciones

- **Objetivo:** mostrar canales, proveedores, adapters, connectors y webhooks.
- **Componentes:** Integrations, channels, providers, adapters, connectors, security, middleware.
- **Nivel de detalle:** alto.

### Diagrama 07. Infraestructura Cloud

- **Objetivo:** mostrar runtime, cloud, CI/CD, database, queue, storage y backups.
- **Componentes:** Infrastructure, Cloud, Docker, CI/CD, DB, Redis, storage, monitoring.
- **Nivel de detalle:** medio.

### Diagrama 08. Arquitectura Documental y AI Support

- **Objetivo:** mostrar el rol de la IA como soporte de analisis y documentacion, no como sistema productivo.
- **Componentes:** AI Support, corpus documental, validacion humana.
- **Nivel de detalle:** bajo a medio.

---

## 8. Diagrama Principal Recomendado

### Componentes que deben aparecer

- Empresa Cliente
- Portal Comercial
- Control Plane
- Tenant Management
- Middleware Core
- API Gateway
- Event Bus
- Integrations
- Producers externos
- Consumers internos
- Dashboard
- Observability Platform
- Security Layer
- Infrastructure

### Componentes que NO deben aparecer

- detalle de clases
- nombres de tablas individuales
- pasos de migracion tecnica
- listas extensas de documentos
- IA como protagonista principal
- demasiados servicios secundarios que saturen la vista

### Tamano relativo

- **Protagonista mayor:** Middleware Core.
- **Protagonistas secundarios grandes:** Control Plane, Event Bus, Dashboard, Observability Platform.
- **Componentes medianos:** Integrations, Security Layer, API Gateway.
- **Componentes pequenos:** Producers, Consumers, Infrastructure, Audit Trail.

### Como deberia verse visualmente

- el Middleware Core debe ir al centro;
- el Control Plane debe quedar arriba o a la izquierda;
- los Producers deben colocarse en el borde izquierdo o superior izquierdo;
- los Consumers deben colocarse a la derecha;
- Dashboard y Observability deben mostrarse como una franja lateral o inferior que lee lo que ocurre en el core;
- Security debe ser una capa transversal, idealmente como borde o escudo alrededor de API Gateway y Middleware;
- Infrastructure debe estar en la base;
- Integrations debe actuar como puente entre producers externos y Middleware Core.

### Lectura esperada del diagrama principal

1. La empresa cliente entra por el Portal Comercial.
2. El Control Plane crea o gestiona el tenant.
3. El tenant activa la instancia y configura modulos.
4. Los productores publican eventos hacia el API Gateway.
5. El Middleware Core valida, registra y publica al Event Bus.
6. Los consumidores procesan eventos o proyectan estado.
7. El Dashboard y la Observability Platform leen el trafico y las metricas.
8. Security e Infrastructure sostienen el flujo como capas transversales.

---

## 9. Leyenda Visual

### Color por categoria

- **Core Business:** azul profundo
- **Middleware:** naranja
- **Infrastructure:** gris acero
- **Observability:** verde
- **Security:** rojo oscuro
- **Integration:** teal o verde azulado
- **Administration:** azul claro
- **AI Support:** amarillo

### Icono por categoria

- **Core Business:** servicio, modulo, application, database
- **Middleware:** hub, broker, event bus
- **Infrastructure:** cloud, server, container
- **Observability:** dashboard, chart, monitoring
- **Security:** shield, lock, audit
- **Integration:** plug, link, webhook
- **Administration:** organization, users, control panel
- **AI Support:** brain, spark, assistant

### Tipo de flecha

- **Flecha solida:** flujo principal de datos o control.
- **Flecha punteada:** dependencia, lectura o observacion.
- **Flecha bidireccional:** intercambio o sincronizacion.
- **Flecha gruesa:** flujo critico o protagonista.

### Tipo de relacion

- **A -> B:** A produce o inicia el flujo hacia B.
- **A <- B:** A consume o recibe informacion desde B.
- **A <-> B:** sincronizacion o dependencia mutua controlada.
- **A -- B:** relacion de soporte o dependencia conceptual.

### Tipo de dependencia

- **Funcion directa:** el componente no opera sin el otro.
- **Lectura derivada:** un componente observa datos de otro sin escribir.
- **Transversal:** seguridad, observabilidad o infraestructura.
- **Orquestacion:** control plane o middleware coordinan el flujo.

---

## 10. Reglas de representacion para otra IA

1. No dibujar el detalle de tablas salvo que el objetivo sea un diagrama de datos.
2. No sobrecargar el diagrama principal con demasiados actores.
3. Mostrar una jerarquia clara: control plane arriba, middleware al centro, consumidores a la derecha, observabilidad al lateral, infraestructura abajo.
4. Usar iconos consistentes por categoria, no por tecnologia puntual.
5. Mantener a la IA como componente de apoyo, nunca como centro del sistema.
6. Si un elemento esta en la documentacion historica pero no en la vigente, marcarlo como historico o excluirlo del diagrama principal.
7. Si un componente aparece como evidencia en planes, ADR o runbooks, puede entrar en el blueprint; si aparece solo una vez y sin continuidad, debe tratarse como secundario.

---

## 11. Referencias documentales

- [docs/architecture/middleware_database_architecture.md](middleware_database_architecture.md)
- [docs/architecture/middleware_database_dictionary.md](middleware_database_dictionary.md)
- [docs/architecture/data_dictionary.md](data_dictionary.md)
- [docs/production/Plan_Middleware.md](../production/Plan_Middleware.md)
- [docs/production/Plan_Observabilidad.md](../production/Plan_Observabilidad.md)
- [docs/production/Plan_Integraciones.md](../production/Plan_Integraciones.md)
- [docs/production/Plan_Tenants.md](../production/Plan_Tenants.md)
- [docs/production/Plan_APIs.md](../production/Plan_APIs.md)
- [docs/production/Plan_Seguridad.md](../production/Plan_Seguridad.md)
- [docs/production/Plan_Cloud.md](../production/Plan_Cloud.md)
- [docs/production/Plan_CI_CD.md](../production/Plan_CI_CD.md)
- [docs/production/Plan_Logs.md](../production/Plan_Logs.md)
- [docs/production/Plan_Monitoreo.md](../production/Plan_Monitoreo.md)
- [docs/production/Auditoria_Produccion.md](../production/Auditoria_Produccion.md)
- [docs/production/ADR_001_instancia_por_cliente.md](../production/ADR_001_instancia_por_cliente.md)
- [docs/production/ADR_004_tenant_id_activation.md](../production/ADR_004_tenant_id_activation.md)
- [docs/production/ADR_009_opentelemetry_distributed_tracing.md](../production/ADR_009_opentelemetry_distributed_tracing.md)
- [docs/production/ADR_010_tenant_lifecycle_management.md](../production/ADR_010_tenant_lifecycle_management.md)
- [docs/production/ADR_011_friendly_routing_multitenant.md](../production/ADR_011_friendly_routing_multitenant.md)
- [docs/Plan_Desarrollo_Modulos_v0.1/README.md](../Plan_Desarrollo_Modulos_v0.1/README.md)
- [docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md](../Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md)
- [docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md](../Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Dashboard_General.md)
- [docs/Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md](../Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md)
- [docs/Plan_Desarrollo_Servicio_v0.1/DDD_en_la_arquitectura.md](../Plan_Desarrollo_Servicio_v0.1/DDD_en_la_arquitectura.md)
- [docs/Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md](../Plan_Desarrollo_Servicio_v0.1/Arquitectura_EDA.md)
- [docs/personal_notes/Analisis_General.md](../personal_notes/Analisis_General.md)
- [docs/personal_notes/Fase_D_arquitectura_cliente.md](../personal_notes/Fase_D_arquitectura_cliente.md)
- [docs/personal_notes/Runbook_cliente_simulado.md](../personal_notes/Runbook_cliente_simulado.md)
- [docs/personal_notes/Observabilidad_pruebas_produccion_local.md](../personal_notes/Observabilidad_pruebas_produccion_local.md)
- [docs/Analisis_v0.1](../Analisis_v0.1/)
- [docs/Analisis_v0.2](../Analisis_v0.2/)

