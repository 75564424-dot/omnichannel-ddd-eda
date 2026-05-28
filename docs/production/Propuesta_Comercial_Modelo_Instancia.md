# Modelo comercial — Instancia dedicada por cliente

**Audiencia:** ventas, pre-sales, arquitectura  
**Versión:** 1.0 | **Fecha:** 2026-05-21

---

## Qué ofrecemos

Middleware omnicanal **event-driven** (bus + dashboard de observabilidad) desplegado como **instancia dedicada** por cliente comercial.

Cada cliente recibe:

- URL propia (`https://{cliente}.middleware...`)
- Base de datos exclusiva
- Configuración de integraciones y catálogo de eventos aislada
- Panel `/middleware` (control del bus) y `/dashboard` (observabilidad)

---

## Qué NO incluye (v1 regularización)

| Capacidad | Estado |
|-----------|--------|
| Multi-tenant (varios clientes en una URL) | No incluido |
| Portal self-service de configuración sin deploy | No incluido |
| SaaS compartido con selector de tenant | No incluido |

Estas capacidades requieren roadmap explícito y ADR adicional.

---

## Beneficios para el cliente

- **Aislamiento de datos** — cumplimiento y auditoría simplificados
- **Personalización** — event types, consumidores y packs por contrato
- **Escalabilidad** — escala horizontal duplicando instancias, no particionando lógica

---

## Implicaciones comerciales

- Precio puede modelarse por instancia + volumen de eventos
- Onboarding: 1–2 días (provision + config + smoke) según runbook
- SLA recomendado: definir por instancia (uptime, latencia publish)

---

## Referencias técnicas

- [ADR_001_instancia_por_cliente.md](ADR_001_instancia_por_cliente.md)
- [Runbook_Onboarding_Cliente.md](Runbook_Onboarding_Cliente.md)
