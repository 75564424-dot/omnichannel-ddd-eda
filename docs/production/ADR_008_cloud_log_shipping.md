# ADR-008 — Cloud log shipping (ELK / CloudWatch / Loki)

**Estado:** Aceptado | **Fecha:** 2026-05-21

## Contexto

`Plan_Logs.md` Fase 3 requiere agregación cloud. No se embebe agente ELK ni SDK CloudWatch en la aplicación PHP.

## Decisión

**Patrón stdout/stderr JSON**:

1. Producción: `LOG_STACK=stderr_json`
2. Orquestador (K8s/Docker) recoge stderr
3. Agente sidecar o servicio cloud (Fluent Bit, CloudWatch Logs driver, Promtail) envía a backend

## Alternativas diferidas

| Opción | Cuándo |
|--------|--------|
| Monolog CloudWatch handler directo | Cuenta AWS locked, sin sidecar |
| ELK Filebeat sobre archivo | VMs sin contenedor |
| OpenTelemetry exporter | Plan_Observabilidad fase avanzada |

## Consecuencias

- Sin dependencia npm/composer adicional para shipping
- Compatible con Docker Compose y EKS/GKE
- Retención en agregador cloud separada de `platform:purge-retention` BD

## Referencias

- [Logs.md](Logs.md)
- [Plan_Cloud.md](Plan_Cloud.md)
