# ADR-005 — Particionamiento event_store (POC)

**Estado:** Propuesto (POC documentado) | **Fecha:** 2026-05-21

## Contexto

`event_store` crecerá con volumen enterprise. Retención default 90 días vía `platform:purge-retention`; particionamiento mejora purge y queries temporales.

## Decisión POC

**No implementar particiones en código Laravel aún.** Estrategia recomendada cuando volumen > 10M filas/mes:

### MySQL 8

```sql
-- Ejemplo mensual por RANGE(TO_DAYS(occurred_at))
ALTER TABLE event_store
PARTITION BY RANGE (TO_DAYS(occurred_at)) (
  PARTITION p202605 VALUES LESS THAN (TO_DAYS('2026-06-01')),
  PARTITION p202606 VALUES LESS THAN (TO_DAYS('2026-07-01')),
  PARTITION pmax VALUES LESS THAN MAXVALUE
);
```

### Operación

- Crear partición futura mensual (job ops)
- Drop partición antigua en lugar de DELETE masivo
- Coordinar con retención 90–365 días según contrato

## Alternativas

| Opción | Cuándo |
|--------|--------|
| Purge DELETE (actual) | Piloto / volumen bajo |
| Particiones MySQL | Volumen alto single-tenant |
| Tabla archivada S3/Parquet | Compliance largo plazo |

## Consecuencias

- Laravel migrations no gestionan particiones — script DBA manual
- SQLite (tests) sin particiones — comportamiento unchanged

## Referencias

- [Plan_BaseDeDatos.md](Plan_BaseDeDatos.md)
- `docs/architecture/middleware_database_architecture.md`
