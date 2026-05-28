# Runbook — DR Drill (disaster recovery)

**Plan:** Plan_Cloud.md Fase 3 | **Frecuencia recomendada:** trimestral

## Objetivo

Validar recuperación de una instancia middleware (instancia-por-cliente) ante pérdida de compute o corrupción parcial de datos.

## Escenario A — Pérdida de pod/VM (sin pérdida de BD)

1. Simular caída: `docker compose stop app nginx worker`
2. Restaurar: `docker compose up -d app nginx worker`
3. Verificar `/up`, `/health/ready`, smoke test
4. **Criterio éxito:** RTO < 15 min, sin restore de BD

## Escenario B — Restore desde backup

1. Crear backup: `scripts/ops/backup-database.sh`
2. Provisionar BD vacía o instancia staging
3. Restore según `Runbook_Backup_Restore.md`
4. Desplegar imagen conocida (`git tag`)
5. `migrate:status`, seed tenant si aplica, sync-config
6. Smoke + validación dashboard

## Escenario C — Multi-AZ (K8s)

1. Drenar un nodo: `kubectl drain <node> --ignore-daemonsets`
2. Confirmar pods re-scheduled, HPA estable
3. Confirmar Ingress sigue enrutando

## Registro del drill

| Campo | Valor |
|-------|-------|
| Fecha | |
| Instancia (`PLATFORM_CLIENT_SLUG`) | |
| Escenario | A / B / C |
| RTO medido | |
| RPO verificado | |
| Incidencias | |
| Acciones correctivas | |

## Multi-AZ checklist (prod enterprise)

- [ ] RDS/Cloud SQL multi-AZ habilitado
- [ ] Redis con réplica o cluster mode
- [ ] ≥2 réplicas app (K8s HPA minReplicas=2)
- [ ] Backups probados en últimos 90 días
