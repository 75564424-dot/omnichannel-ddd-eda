# Migraciones greenfield — estrategia squash (Fase 3)

**Plan:** Plan_BaseDeDatos.md Fase 3

## Problema

Fresh install ejecuta cadena legacy → migrate → drop (2026-05-21), generando ruido y tiempo en `migrate`.

## Estrategia recomendada

### Instalaciones nuevas (greenfield)

1. En entorno limpio con esquema estable:

```bash
php artisan migrate:fresh --seed
php artisan schema:dump
```

2. Commit `database/schema/mysql-schema.sql` (o sqlite) generado por Laravel.
3. Nuevos deploys usan dump + migraciones incrementales post-dump.

### Upgrades existentes

- **No eliminar** migraciones históricas ya aplicadas en prod.
- Squash solo para clones greenfield o nuevas instancias cliente.

## Script ops

```bash
bash scripts/ops/dump-greenfield-schema.sh
```

## Estado actual

- POC documentado; squash **no aplicado** en repo para no romper upgrades.
- Próximo hito: tras estabilizar esquema 30 días sin cambios breaking.

## Referencias

- [BaseDeDatos.md](BaseDeDatos.md)
- Laravel docs: Schema Dumping
