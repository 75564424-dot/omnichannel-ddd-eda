# Checklist — Staging pre-GO (simulación cliente)

**Plan:** Plan_SimulacionClientes.md | Completar antes del primer GO por instancia.

## Infraestructura

- [ ] Instancia staging dedicada (`PLATFORM_CLIENT_SLUG` único)
- [ ] BD y Redis aislados de otros clientes
- [ ] `/up` y `/health/ready` responden 200
- [ ] Auth habilitada (`PLATFORM_*_AUTH_ENABLED=true`)
- [ ] Password admin cambiado

## Configuración cliente

- [ ] Fixture aplicado o config equivalente revisada en PR
- [ ] `php artisan platform:validate-catalog` — OK
- [ ] `consumer_registrars` solo packs contratados (+ demo si laboratorio)
- [ ] `DEMO_PACK_ENABLED=false` en staging pre-GO (salvo prueba explícita)

## Simulación automatizada

- [ ] `php artisan platform:simulate-client <slug> --events=50` — OK
- [ ] Eventos visibles en `GET /api/middleware/queue`
- [ ] Status `PROCESADO` en lookup por `event_id`
- [ ] `GET /api/middleware/topology` — success
- [ ] `GET /api/dashboard/modules/catalog` — productores/suscriptores esperados
- [ ] `bash scripts/ops/simulate-client-smoke.sh` — OK

## UI manual

- [ ] `/middleware` — cola y topología coherentes
- [ ] `/dashboard` — KPIs/feed sin errores
- [ ] Operador puede login y ejecutar sync (rol adecuado)

## Seguridad y ops

- [ ] API keys M2M rotadas vs local
- [ ] Backup BD probado (ver Runbook_Backup_Restore.md)
- [ ] Runbook deploy VM revisado por ops

## Decisión

| Campo | Valor |
|-------|-------|
| Cliente / slug | |
| Fixture simulado | |
| Fecha | |
| Responsable QA | |
| GO / NO-GO | |
