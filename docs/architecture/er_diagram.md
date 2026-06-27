# Diagrama entidad-relación — Plataforma Middleware (EDA)

**Versión:** 2.0  
**Fecha:** 2026-06-24  
**Fuente:** `database/migrations/` (31 migraciones)  
**Motor:** SQLite (dev) / MySQL (prod)  
**Tablas activas:** 38 (+ `migrations` de Laravel)

> El diagrama retail anterior (PRODUCT, ORDER, INVENTORY…) está **obsoleto**. Ver [`data_dictionary.md`](data_dictionary.md).

---

## 1. Inventario de tablas por dominio

| Dominio | Tablas | Cantidad |
|---------|--------|----------|
| Plataforma e identidad | `tenants`, `users`, `personal_access_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `failed_jobs` | 8 |
| Configuración e integración | `system_configurations`, `channels`, `providers`, `integrations`, `adapters`, `connectors`, `integration_credentials`, `registered_modules` | 8 |
| Eventos y mensajería | `event_store`, `event_logs`, `message_queue`, `dead_letter_queue`, `retries`, `outbox_messages` | 6 |
| Procesamiento | `processing_jobs`, `workflows`, `workflow_steps`, `transactions` | 4 |
| Webhooks y notificaciones | `webhook_requests`, `webhook_responses`, `notifications` | 3 |
| Observabilidad / Dashboard | `audit_logs`, `trace_logs`, `observability_metrics`, `channel_status_snapshots`, `event_feed_projections` | 5 |
| Control plane | `client_incident_reports`, `simulation_runs` | 2 |
| **Total** | | **38** |

### Tablas legacy eliminadas (migración 2026-05-21)

| Obsoleta | Sucesora |
|----------|----------|
| `bus_queue_entries` | `message_queue` |
| `bus_dead_letters` | `dead_letter_queue` |
| `event_feed_entries` | `event_feed_projections` |
| `bus_metrics_snapshots`, `middleware_bus_metrics` | `observability_metrics` |
| `node_status_snapshots` | `channel_status_snapshots` |
| `middleware_registered_modules` | `registered_modules` |
| `system_metrics_snapshots` | *(eliminada — KPIs retail)* |

---

## 2. Plataforma e identidad

```mermaid
erDiagram
    tenants {
        uuid id PK
        string name
        string slug UK
        string status
        json settings
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    users {
        bigint id PK
        uuid tenant_id FK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string platform_role
        string remember_token
        timestamp created_at
        timestamp updated_at
    }

    personal_access_tokens {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string name
        string token UK
        text abilities
        timestamp last_used_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }

    sessions {
        string id PK
        bigint user_id
        string ip_address
        text user_agent
        longtext payload
        int last_activity
    }

    tenants ||--o{ users : employs
    users ||--o{ personal_access_tokens : has_tokens
    users ||--o{ sessions : owns
```

**Infraestructura Laravel (sin FK de negocio):** `cache`, `cache_locks`, `jobs`, `failed_jobs`.

---

## 3. Configuración e integración

```mermaid
erDiagram
    tenants ||--o{ system_configurations : configures
    tenants ||--o{ channels : owns
    tenants ||--o{ providers : owns
    tenants ||--o{ integrations : owns

    channels ||--o{ integrations : links
    providers ||--o{ integrations : links
    integrations ||--o{ adapters : has
    integrations ||--o{ connectors : has
    integrations ||--o{ integration_credentials : secures

    registered_modules {
        bigint id PK
        string logical_id
        string type
        string name
        json event_types
    }

    channels {
        uuid id PK
        uuid tenant_id FK
        string code
        string name
        string channel_type
        string status
        json metadata
    }

    integrations {
        uuid id PK
        uuid tenant_id FK
        uuid channel_id FK
        uuid provider_id FK
        string code
        string direction
        string status
        json config
    }
```

---

## 4. Pipeline de eventos

```mermaid
erDiagram
    tenants ||--o{ event_store : scopes
    channels ||--o{ event_store : sources
    integrations ||--o{ event_store : sources

    event_store {
        bigint id PK
        uuid event_uuid UK
        uuid tenant_id
        uuid correlation_id
        uuid causation_id
        string event_type
        string origin
        json payload
        timestamp occurred_at
        timestamp recorded_at
    }

    message_queue {
        bigint id PK
        uuid event_uuid UK
        uuid tenant_id
        string message_type
        string status
        json payload
        uuid correlation_id
        timestamp published_at
        int attempt_count
    }

    dead_letter_queue {
        bigint id PK
        bigint message_queue_id
        uuid event_uuid UK
        string event_type
        text failure_reason
        timestamp failed_at
    }

    retries {
        uuid id PK
        bigint message_queue_id
        uuid event_uuid
        tinyint attempt_number
        string status
        timestamp scheduled_at
    }

    outbox_messages {
        bigint id PK
        uuid event_uuid
        string event_type
        json payload
        string status
        timestamp created_at
    }

    event_store ||--o| message_queue : correlates_by_event_uuid
    message_queue ||--o{ retries : schedules
    message_queue ||--o| dead_letter_queue : may_become
    event_store ||--o{ event_logs : projects
```

**Correlación lógica:** no hay FK estricta `event_store` → `message_queue`; se une por `event_uuid`.

---

## 5. Procesamiento, webhooks y observabilidad

```mermaid
erDiagram
    tenants ||--o{ workflows : defines
    workflows ||--o{ workflow_steps : contains
    tenants ||--o{ transactions : tracks

    integrations ||--o{ webhook_requests : receives
    webhook_requests ||--o| webhook_responses : answers

    channels ||--o{ channel_status_snapshots : monitors
    event_store ||--o| event_feed_projections : feeds_dashboard

    workflows {
        uuid id PK
        uuid tenant_id FK
        string code
        string trigger_event_type
        string status
    }

    webhook_requests {
        uuid id PK
        uuid integration_id FK
        uuid correlation_id
        string http_method
        json request_body
        timestamp received_at
    }

    event_feed_projections {
        bigint id PK
        uuid event_uuid UK
        uuid correlation_id
        string event_type
        string impact
        string status
        json raw_payload
    }

    audit_logs {
        bigint id PK
        uuid tenant_id
        string action
        string entity_type
        json changes
        timestamp occurred_at
    }

    trace_logs {
        uuid id PK
        uuid trace_id
        uuid span_id
        uuid correlation_id
        uuid event_uuid
        string operation_name
    }
```

---

## 6. Control plane

```mermaid
erDiagram
    tenants ||--o{ simulation_runs : simulates
    users ||--o{ simulation_runs : starts
    tenants ||--o{ client_incident_reports : reports
    users ||--o{ client_incident_reports : files

    simulation_runs {
        uuid id PK
        uuid tenant_id FK
        bigint started_by_user_id FK
        string status
        string fixture_slug
        int planned_total
        int published
        json metrics
    }

    client_incident_reports {
        uuid id PK
        uuid tenant_id
        bigint user_id FK
        string reporter_email
        text description
        string severity
        string status
        json diagnostic_log
        text admin_response
        timestamp responded_at
        timestamp resolved_at
    }
```

---

## 7. Vista de flujo de datos (EDA)

```mermaid
flowchart LR
    subgraph ingress [Ingresa]
        WH[webhook_requests]
        API[connectors HTTP]
    end

    subgraph write [Write model Middleware]
        ES[event_store]
        MQ[message_queue]
        OB[outbox_messages]
    end

    subgraph process [Procesamiento]
        RT[retries]
        DLQ[dead_letter_queue]
        WF[workflows]
    end

    subgraph read [Read model Dashboard]
        EFP[event_feed_projections]
        OM[observability_metrics]
        CSS[channel_status_snapshots]
    end

    WH --> ES
    API --> ES
    ES --> MQ
    ES --> OB
    MQ --> RT
    RT --> DLQ
    ES --> EFP
    MQ --> OM
    CSS --> OM
```

---

## 8. Referencias

| Documento | Contenido |
|-----------|-----------|
| [`middleware_database_dictionary.md`](middleware_database_dictionary.md) | Definición columna a columna (38 tablas) |
| [`middleware_database_architecture.md`](middleware_database_architecture.md) | Principios DDD/EDA, retención, migración legacy |
| [`data_dictionary.md`](data_dictionary.md) | Modelo retail obsoleto (referencia histórica) |
| `database/migrations/` | Fuente de verdad del esquema |
