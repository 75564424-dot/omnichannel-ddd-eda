# PROC-031 — Backup y restauración

**ID:** PROC-031  
**Versión documento:** 1.0  
**Fecha:** 2026-06-27  
**Estado:** Documentado (runbook operativo)  
**Tipo:** Técnico — Operación / Apoyo  
**Macroproceso:** MP-06 Operaciones e Infraestructura

---

## Descripción

Proceso operativo de backup manual y automatizado de base de datos middleware, y procedimiento de restore documentado en `Runbook_Backup_Restore.md` (ART-027). Aplica modelo instancia-por-cliente (ADR-001): cada silo tiene BD dedicada y política RPO/RTO orientativa.

---

## Objetivo

Garantizar recuperabilidad de datos operativos (cola, event store, tenants, integraciones) mediante backups repetibles y restore verificable, complementando PROC-014 (retención) sin sustituirla.

---

## Alcance

**Incluye:**

- Backup manual: `scripts/ops/backup-database.sh`.
- Salida: `storage/backups/<database>_<timestamp>.sql.gz`.
- Retención backups: 14 días default (`RETENTION_DAYS`).
- Cron automatizado (Docker host, K8s cronjob referencia).
- Cloud managed: RDS/Azure/GCP recomendaciones.
- Restore: gunzip + mysql pipe.
- Post-restore: migrate:status, health, smoke, validar cola.

**Excluye:**

- Backup código/config git (fuera alcance BD).
- PROC-032 ejercicio DR completo.
- Particionamiento event_store (ADR-005).

---

## Actores

| Actor | Rol |
|-------|-----|
| Ops / DBA | Ejecuta backup/restore |
| Cron / K8s CronJob | Automatización |
| Cloud provider | Snapshots managed |
| Scripts ops | backup-database.sh |

---

## Entradas

| Entrada | Origen |
|---------|--------|
| DB_HOST, DB_DATABASE, credentials | .env |
| Schedule cron | 03:00 daily ejemplo |
| Archivo backup | storage/backups/ |

---

## Salidas

| Salida | Descripción |
|--------|-------------|
| `.sql.gz` backup | Archivo comprimido |
| BD restaurada | Post-restore |
| Health/smoke OK | Validación |
| RPO/RTO documentados | Tabla runbook |

---

## Reglas de negocio

| ID | Regla | Evidencia |
|----|-------|-----------|
| RN-031-01 | Retención backup default 14 días | Runbook_Backup_Restore.md |
| RN-031-02 | Post-restore: migrate:status + health + smoke | Runbook §Procedimiento post-restore |
| RN-031-03 | RPO/RTO ajustar por SLA cliente ADR-001 | Runbook tabla |
| RN-031-04 | Complementa purge PROC-014 — no reemplaza | Plan_Resiliencia |

---

## Precondiciones

1. BD accesible desde host script.
2. Espacio disco storage/backups.
3. Credenciales válidas.
4. Para restore: ventana mantenimiento acordada.

---

## Postcondiciones

1. Backup file generado y retenido.
2. Tras restore: aplicación operativa; cola/métricas coherentes.
3. PROC-003 consultas funcionales.

---

## Flujo principal — Backup

| Paso | Actividad | Descripción |
|------|-----------|-------------|
| 1 | Configurar env DB | DB_HOST, DB_DATABASE, etc. |
| 2 | Ejecutar script | backup-database.sh |
| 3 | Verificar salida | .sql.gz en storage/backups |
| 4 | **Fin backup** | Archivo retenido 14d |

---

## Flujo principal — Restore

| Paso | Actividad | Descripción |
|------|-----------|-------------|
| 1 | Seleccionar backup | timestamp correcto |
| 2 | gunzip + mysql | Pipe restore |
| 3 | migrate:status | Verificar migraciones |
| 4 | curl /health/ready | Health check |
| 5 | Smoke test | scripts/ci/smoke-test.sh |
| 6 | Validar cola | GET /middleware queue |
| 7 | **Fin restore** | Instancia recuperada |

---

## Flujos alternativos

### FA-01 — Cron Docker host

- **Schedule:** `0 3 * * *` cd repo && backup script.

### FA-02 — K8s CronJob

- **Ref:** deploy/k8s/cronjob-backup.yaml — PENDIENTE_VALIDACION en workspace.

### FA-03 — Cloud snapshot pre-release

- **Proveedor:** RDS manual snapshot antes deploy.

---

## Excepciones

| Escenario | Tratamiento |
|-----------|-------------|
| Backup corrupto | Verificar integridad; retry |
| Restore parcial | Rollback; nuevo restore |
| RPO excedido | Escalar frecuencia backup |

---

## Eventos

| Evento | Tipo |
|--------|------|
| Trigger backup | Inicio |
| Backup file created | Intermedio |
| Restore completed | Fin |

---

## Dependencias

| Dependencia | Proceso |
|-------------|---------|
| PROC-030 | Deploy estable previo |
| PROC-014 | Retención datos operativos |
| PROC-032 | DR drill usa restore |
| ART-027 | Runbook |

---

## Riesgos

| ID | Riesgo | Mitigación |
|----|--------|------------|
| R1 | Backups no probados | PROC-032 trimestral |
| R2 | RPO 24h backup diario | Ajustar frecuencia |
| R3 | Sin backup pre-purge | Backup antes PROC-014 agresivo |

---

## Indicadores

| Indicador | Fuente |
|-----------|--------|
| Último backup exitoso | storage/backups/ |
| RPO medido | Ops registro |
| C19 | Matriz Operación |

---

## Relación con otros procesos

| Proceso | Relación |
|---------|----------|
| PROC-014 | Purga vs backup |
| PROC-032 | Escenario B restore drill |
| PROC-030 | Post-deploy primer backup |

---

## Documentación relacionada

- `docs/production/Runbook_Backup_Restore.md` (ART-027)
- `scripts/ops/backup-database.sh`
- `docs/production/Plan_Cloud.md` Fase 2

---

## Trazabilidad

| Elemento | Evidencia |
|----------|-----------|
| PROC-031 | 00_Mapa_Procesos.md; Matriz_Trazabilidad_BPMN.md |
| ART-027 | artefactos.csv |
| ADR-001 | Instancia BD dedicada |

---

## Diagrama Mermaid

```mermaid
flowchart TD
    subgraph BACKUP
        B1([Trigger backup]) --> B2[backup-database.sh]
        B2 --> B3[storage/backups/*.sql.gz]
    end
    subgraph RESTORE
        R1([Seleccionar backup]) --> R2[gunzip | mysql]
        R2 --> R3[migrate:status]
        R3 --> R4[health + smoke]
        R4 --> R5[validar cola PROC-003]
        R5 --> END([Fin restore OK])
    end
```

---

## BPMN Mapping

| Elemento BPMN | Descripción |
|---------------|-------------|
| **Subprocesos** | Backup; Restore |
| **Evento Final** | BD recuperada operativa |
| **Artefactos** | Runbook_Backup_Restore.md; backup-database.sh |

---

*Fin del documento PROC-031*
