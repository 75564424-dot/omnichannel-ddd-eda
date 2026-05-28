# Runbook — Onboarding de nuevo cliente (instancia dedicada)

**Modelo:** instancia por cliente ([ADR-001](ADR_001_instancia_por_cliente.md))  
**Versión:** 1.0 | **Fecha:** 2026-05-21

---

## Prerrequisitos

- Imagen o artefacto de release etiquetado (mismo repo/commit para todas las instancias)
- BD MySQL vacía dedicada
- Redis (recomendado producción)
- DNS / certificado TLS para URL del cliente
- Slug único acordado (ej. `acme-retail`)

---

## Pasos

### 1. Provisionar infraestructura

1. Crear VM/namespace K8s + BD + Redis
2. Registrar fila en [Inventario_Instancias.md](Inventario_Instancias.md)

### 2. Configurar instancia

1. Copiar [templates/env.client.example](templates/env.client.example) → `.env`
2. Completar `PLATFORM_CLIENT_SLUG`, `PLATFORM_CLIENT_NAME`, `APP_URL`, credenciales BD
3. Desplegar config del cliente:
   - `config/modules/modules_config.json` (catálogo UI)
   - `config/eventbus.php` o overlay por env
   - `consumer_registrars` según packs contratados

### 3. Migraciones y tenant de instancia

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan platform:ensure-instance-tenant
php artisan config:cache
php artisan route:cache
```

Verificar tenant:

```bash
php artisan tinker --execute="echo json_encode(DB::table('tenants')->first());"
```

### 4. Sync registro de módulos

```bash
curl -X POST "$APP_URL/api/middleware/registry/sync-config" -H "Accept: application/json"
```

*(En producción: añadir autenticación cuando Plan_Autenticacion esté activo.)*

### 5. Smoke test

```bash
# Publicar evento de prueba (ajustar event_id UUID)
curl -X POST "$APP_URL/api/middleware/events/publish" \
  -H "Content-Type: application/json" \
  -d '{"event_id":"<uuid>","event_type":"Platform.Smoke.Test","occurred_at":"2026-05-21T12:00:00Z","origin":"Onboarding","payload":{"event_id":"<uuid>","event":"Platform.Smoke.Test","occurred_at":"2026-05-21T12:00:00Z"}}'

curl "$APP_URL/api/middleware/queue?limit=5"
curl "$APP_URL/api/dashboard/events/feed?limit=5"
```

### 6. Validación UI

- Abrir `/middleware` — cola y topología
- Abrir `/dashboard` — feed y métricas

### 7. Cierre

- [ ] Inventario actualizado
- [ ] Backup inicial BD configurado
- [ ] Contacto operativo del cliente documentado
- [ ] Release notes entregadas (alcance: instancia dedicada, no multi-tenant)

---

## Rollback

1. Detener tráfico (DNS / ingress)
2. Restaurar BD desde snapshot pre-migrate si aplica
3. Revertir a imagen anterior

---

## Referencias

- `docs/personal_notes/Runbook_cliente_simulado.md`
- `docs/personal_notes/Simulacion_escenario_productivo.md`
