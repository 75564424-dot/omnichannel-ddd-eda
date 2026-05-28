# Terraform skeleton — Plan_Cloud Fase 3

Esqueleto IaC para instancia middleware por cliente. **No despliega automáticamente** — adaptar por cloud provider.

## Estructura

```
deploy/terraform/
  main.tf          # módulos placeholder
  variables.tf     # inputs por instancia
  outputs.tf       # endpoints
  README.md
```

## Uso previsto

1. Un workspace/state por cliente (`PLATFORM_CLIENT_SLUG`)
2. Módulos: VPC, RDS MySQL, ElastiCache Redis, EKS/ECS, ALB, Secrets Manager
3. Output: `APP_URL`, connection strings → Kubernetes secrets

## Estado

Placeholder documentado. Implementación completa cuando exista target cloud acordado (AWS/Azure/GCP).

Ver `variables.tf` para parámetros mínimos por instancia.
