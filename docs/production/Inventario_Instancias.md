# Inventario de instancias — Plantilla

Registro operativo del parque de instancias (una fila = un cliente comercial).

| client_slug | client_name | APP_URL | entorno | BD host | BD name | versión release | fecha GO | owner ops | notas |
|-------------|-------------|---------|---------|---------|---------|-----------------|----------|-----------|-------|
| acme-retail | Acme Retail | https://acme.example.com | staging | db-stg-01 | platform_acme | v0.1.0 | | | |
| beta-corp | Beta Corp | https://beta.example.com | prod | db-prod-02 | platform_beta | | | | |

---

## Convenciones

- **client_slug:** minúsculas, guiones, coincide con `PLATFORM_CLIENT_SLUG`
- **versión release:** tag git desplegado
- **owner ops:** responsable on-call

---

## Acciones

- Actualizar al completar [Runbook_Onboarding_Cliente.md](Runbook_Onboarding_Cliente.md)
- Revisar mensualmente en reunión de operaciones

---

*Puede migrarse a CMDB/Spreadsheet externo; esta plantilla vive en repo como referencia mínima.*
