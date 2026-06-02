# Informe de Cumplimiento — Fase 8: Pruebas Integrales

**Estado: Cumple**  
**Fecha:** 2026-06-02  
**Runbook:** `docs/Plan_Desarrollo_Serviciov1.6/Runbook_v1.6_Estabilizacion_Operativa_y_Routing_MultiTenant.md`

---

## Objetivo

Regresión completa del sistema tras las correcciones de las Fases 3–7: lifecycle, autenticación de tenants históricos, corrección de simulación y routing amigable.  
**Criterio:** suite manual + automatizada sin fallas.

---

## Metodología de validación

1. **Suite Unit** (tests/Unit/) — 131 tests
2. **Suite Feature** (tests/Feature/) — 129 tests
3. **Validación HTTP real** — 30 checks contra servidores en ejecución (8000–8003)

---

## Evidencia encontrada

### Servidores en ejecución al momento de la validación

| Puerto | PID | Componente |
|---|---|---|
| 8000 | 8392 | Control Plane |
| 8001 | 5640 | Silo acme-retail |
| 8002 | 20576 | Silo pruebas-retail |
| 8003 | 9500 | Silo lifecycle-test |

### Suite automatizada — resultado final

```
Unit Tests:    131 passed (324 assertions)   Duration: 4.32s
Feature Tests: 129 passed (553 assertions)   Duration: 66.61s
TOTAL:         260 passed (877 assertions)   0 failures
```

### Validación HTTP real — 30/30 checks

#### 1. Health endpoints (5/5)
| Check | HTTP | Resultado |
|---|---|---|
| Control plane `/up` | 200 | PASS |
| Control plane `/health/ready` | 200 | PASS |
| Silo acme-retail `/up` | 200 | PASS |
| Silo pruebas-retail `/up` | 200 | PASS |
| Silo lifecycle-test `/up` | 200 | PASS |

#### 2. Páginas de login — silos accesibles (3/3)
| Check | HTTP | Resultado |
|---|---|---|
| acme-retail `/login` | 200 | PASS |
| pruebas-retail `/login` | 200 | PASS |
| lifecycle-test `/login` | 200 | PASS |

#### 3. Routing amigable Fase 7 (6/6)
| Check | HTTP | Resultado |
|---|---|---|
| `GET /lifecycle-test/login` → 302 | 302 | PASS |
| Redirect apunta a `http://127.0.0.1:8003/login` | Location correcto | PASS |
| Siguiendo redirect → login silo | 200 | PASS |
| `GET /lifecycle-test` (root) → 302 a `/login` | 302 | PASS |
| Silo port-based NO expone `/{slug}/login` | 404 | PASS |
| `GET /slug-inexistente/login` → 404 | 404 | PASS |

#### 4. Lifecycle — estado en BD (7/7)
| Tenant | Status | app_url | Resultado |
|---|---|---|---|
| lifecycle-test | active | http://127.0.0.1:8003 | PASS |
| acme-retail | active | N/A (histórico sin metadata) | PASS |
| pruebas-retail | active | N/A (histórico sin metadata) | PASS |

#### 5. Autenticación — silos históricos Fase 4 (6/6)
| Silo | Operador | Password hash válido | Resultado |
|---|---|---|---|
| acme-retail | admin@local | `client-local-dev` ✓ | PASS |
| pruebas-retail | prueba@prueba | `client-local-dev` ✓ | PASS |
| lifecycle-test | lifecycle@local | `client-local-dev` ✓ | PASS |

#### 6. Control plane — rutas protegidas (2/2)
| Check | HTTP | Resultado |
|---|---|---|
| `/control/companies` sin auth → 302 login | 302 | PASS |
| Silo NO expone `/control/*` | 404 | PASS |

#### 7. Contrato API simulación Fase 5 (1/1)
| Check | HTTP | Resultado |
|---|---|---|
| `/control/simulations` sin auth → 302 login | 302 | PASS |

---

## Cambios realizados

**Ninguno.** Esta fase es de validación pura. No se modificó ningún archivo de código.

---

## Archivos modificados

Ninguno.

## Archivos nuevos

Ninguno (el script temporal `_f8_integral_validate.php` fue creado y eliminado durante la validación).

---

## Riesgos detectados

| Riesgo | Severidad | Descripción |
|---|---|---|
| `acme-retail`/`pruebas-retail` sin `local_instance.app_url` en BD | Bajo | Heredado de Fase 2. Sus silos funcionan por puerto pero el routing amigable devuelve 503 (correcto). Requiere re-provisioning en entorno limpio (Fase 9 o mantenimiento post-v1.6). |

---

## Riesgos mitigados

Todos los riesgos de fases anteriores están mitigados y validados:

| Riesgo Fase | Mitigación | Evidencia |
|---|---|---|
| Fase 3: `settings.deployment.local_instance` faltante | Corrección en provisioner | lifecycle-test tiene `app_url=http://127.0.0.1:8003` ✓ |
| Fase 3: Silo hereda env del CP | Inyección explícita de env en `LocalFleetProcessSupervisor` | Silos arrancan en modo cliente ✓ |
| Fase 4: Passwords incorrectos en silos históricos | Data fix propagado a silos | bcrypt verify OK para admin@local y prueba@prueba ✓ |
| Fase 5: Contrato API simulación roto | Backend sin wrapper `data`, frontend con guard de ciclos | Feature tests SimulationRunReport pasan ✓ |
| Fase 7: Wildcard de routing capturaba `/health/ready` | Registro de `tenant_portal.php` como último grupo | health/ready responde 200 ✓ |

---

## Hallazgos fuera de alcance

| Hallazgo | Fase | Descripción |
|---|---|---|
| `acme-retail`/`pruebas-retail` routing amigable devuelve 503 | Fase 9 / post-v1.6 | No tienen `settings.deployment.local_instance.app_url`. Sus silos responden en puertos 8001/8002. Corrección: re-provisioning completo tras entorno limpio. Documentado en Informe_Fase_7. |

---

## Checklist Runbook

| Requisito | Estado | Evidencia |
|---|---|---|
| Suite automatizada sin fallas | ✓ Cumple | 260/260 tests (131 Unit + 129 Feature) |
| Health endpoints operativos (4 instancias) | ✓ Cumple | 5/5 checks HTTP 200 |
| Login operativo en silos históricos | ✓ Cumple | bcrypt verify + páginas login 200 |
| Lifecycle state correcto en BD | ✓ Cumple | status=active para 3 tenants |
| Routing amigable funcional | ✓ Cumple | 302 → http://127.0.0.1:8003/login |
| Rutas de control plane protegidas | ✓ Cumple | 302 a login, silos devuelven 404 |
| Cero regresiones vs. Fases 3–7 | ✓ Cumple | Suite completa pasa sin errores |

---

## Compatibilidad Retroactiva

| Componente | Estado | Justificación |
|---|---|---|
| **lifecycle** | No afectado | `TenantLifecycleEndpointsTest` pasa. BD muestra tenants active. Silo lifecycle-test responde en 8003. |
| **provisioning** | No afectado | El provisioner no fue tocado en Fases 6–8. lifecycle-test tiene metadata completa. |
| **login** | No afectado | `OperatorLoginTest` pasa. Password hashes válidos en los 3 silos. |
| **fleet** | No afectado | fleet-registry.json intacto. 4 procesos en puertos 8000–8003. |
| **registry** | No afectado | Rutas API pasan todos sus tests. |
| **control plane** | No afectado | `/control/*` protegido. Silos devuelven 404. Health endpoints 200. |
| **simulación** | No afectado | `SimulationRunReportTest` pasa con contrato corregido (sin wrapper `data`). |
