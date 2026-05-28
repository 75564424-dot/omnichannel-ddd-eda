# ADR-004 — Activación de tenant_id

**Estado:** Aceptado | **Fecha:** 2026-05-21

## Contexto

Tablas middleware incluyen `tenant_id` UUID nullable. ADR-001 define **instancia por cliente** (aislamiento físico por despliegue).

## Decisión

| Escenario | tenant_id |
|-----------|-----------|
| **Producción instance-per-client** | Poblar vía `InstanceTenantSeeder` + repositorios (`Plan_Tenants`) en `message_queue`, métricas |
| **Tests sin seed tenant** | Permanece `null` — compatibilidad phpunit |
| **Multi-tenant lógico futuro** | ADR-001 Fase 3 — RLS + resolver HTTP |

## Cuándo activar obligatoriamente

1. Cliente exige trazabilidad tenant en BD además de logs
2. Migración a multi-tenant lógico
3. Reporting consolidado multi-instancia (data warehouse)

## Consecuencias

- Nullable evita romper tests y dev rápido
- Producción debe ejecutar `db:seed` o `platform:ensure-instance-tenant`
- Purge/retención no filtra por tenant hoy (una instancia = un tenant)

## Referencias

- [Plan_Tenants.md](Plan_Tenants.md)
- [Plan_BaseDeDatos.md](Plan_BaseDeDatos.md)
