# Simulación — Acme Retail Middleware

**Instancia:** `PLATFORM_CLIENT_SLUG=acme-retail`  
**Fixture técnico:** `acmepos` (`tests/fixtures/clients/acmepos/`)  
**Plan maestro:** [Plan_SimulacionClientes.md](Plan_SimulacionClientes.md)  
**Runbook general:** [Runbook_Simulacion_Cliente.md](Runbook_Simulacion_Cliente.md)

---

## 1. Qué simula

| Elemento | Valor |
|----------|--------|
| Productores | Acme POS Terminal, Acme Web Shop |
| Suscriptor | Acme Reporting |
| Evento de muestra (fixture) | `AcmePOS.Sale.Completed` |
| Catálogo en tenant | `settings.modules_catalog` (SaaS → Configurar módulos) |

La simulación **publica eventos reales** en el bus, actualiza cola, feed del dashboard y métricas.

---

## 2. Prerrequisitos (checklist)

- [ ] `.env`: `PLATFORM_CLIENT_SLUG=acme-retail`, `PLATFORM_CLIENT_NAME="Acme Retail Middleware"`
- [ ] Base migrada: `php artisan migrate`
- [ ] Solo Acme + usuarios demo (opcional): `php artisan platform:reset-demo-identity`
- [ ] Catálogo Acme en tenant: `php artisan db:seed --class=AcmeRetailSimulationSeeder`
- [ ] Servidor web: `php artisan serve` (o Docker)
- [ ] Front (Vite): `npm run dev` o `npm run build`
- [ ] Login portal cliente: `admin@local` (contraseña en `PLATFORM_ADMIN_PASSWORD`)

### Panel Live (recomendado antes de simular)

En el portal cliente → icono **Live** → para cada módulo:

1. **Refrescar** (estado ONLINE)
2. Activar **Eventos middleware** en productores y en el bus si aplica

Sin esto, los eventos se publican pero el panel puede seguir en OFFLINE.

### Variables útiles (`.env`)

```env
PLATFORM_SIMULATION_FIXTURE_SLUG=acmepos
EVENTBUS_ASYNC_PROCESSING=false
EVENTBUS_ASYNC_LISTENERS=false
DEMO_PACK_ENABLED=false
```

Con procesamiento **síncrono** (valores anteriores) verás cola y feed al instante sin worker Redis.

---

## 3. Preparar (sin publicar eventos)

Una vez por sesión de prueba o tras cambiar el catálogo de módulos:

```bash
php artisan platform:simulation:prepare --slug=acmepos
```

Hace:

1. Alinea `config/modules/modules_config.json` con el catálogo del tenant (si aplica)
2. `sync-config` → registry de productores/consumidores
3. Marca `simulation_prepared_at` en el tenant

Verificación: en el dashboard del cliente, bloque de simulación / módulos visibles.

---

## 4. Ejecutar simulación

### 4.0 Desde el panel SaaS (automatizado)

**Gestión de empresas** (`/control/companies`) → sección **Automatización de simulación**:

1. Empresa: **Acme Retail Middleware**
2. Eventos por minuto: `10`
3. Duración (minutos): `1`
4. Total eventos: calculado automáticamente (10)
5. **Iniciar simulación** (dejar marcado “Preparar antes”)

Equivalente CLI a los comandos de abajo. La petición **permanece activa** hasta terminar el ritmo configurado.

### 4.1 Ritmo controlado — **10 eventos por minuto** (recomendado)

```bash
php artisan platform:simulate-client acmepos --per-minute=10 --duration-minutes=1
```

- Publica **10 eventos** en ~1 minuto (~1 cada 6 segundos)
- El comando **permanece en ejecución** hasta terminar (no es background)

### 4.2 Ampliar carga después

| Objetivo | Comando |
|----------|---------|
| 10/min durante 5 min (50 eventos) | `php artisan platform:simulate-client acmepos --per-minute=10 --duration-minutes=5` |
| 30/min durante 2 min | `php artisan platform:simulate-client acmepos --per-minute=30 --duration-minutes=2` |
| Ráfaga rápida (10 de golpe) | `php artisan platform:simulate-client acmepos --events=10` |

### 4.3 Validar catálogo antes (opcional)

```bash
php artisan platform:validate-catalog
```

---

## 5. Qué revisar tras ejecutar

| Dónde | Qué esperar |
|-------|-------------|
| `/middleware` | Cola con entradas, topología con productores/consumidor |
| `/dashboard` | Gráfica “Eventos por día”, feed, nodos |
| SaaS `/control/incidents` | Bus ACTIVE o HI-LOAD (no STOPPED por idle si hay tráfico) |
| Panel Live | Módulos ONLINE si se activaron eventos |

---

## 6. Repetir en otro momento

```bash
php artisan platform:simulation:prepare --slug=acmepos
php artisan platform:simulate-client acmepos --per-minute=10 --duration-minutes=1
```

Tras cambios en **Configurar módulos** (SaaS), vuelva a ejecutar **prepare**.

---

## 7. Solución de problemas

| Síntoma | Acción |
|---------|--------|
| `Fixture not found` | Use slug `acmepos`, no `acme-retail` |
| `Tenant not found` | `php artisan db:seed --class=InstanceTenantSeeder` |
| Cola vacía | `platform:simulation:prepare` y activar módulos en Live |
| Dashboard en 0 | Espere fin del comando con `--per-minute`; refresque dashboard |
| Bus STOPPED en SaaS | Normal sin tráfico; tras simular debería subir EPS |

---

## 8. Referencia rápida de comandos

```bash
php artisan platform:reset-demo-identity
php artisan db:seed --class=AcmeRetailSimulationSeeder
php artisan platform:simulation:prepare --slug=acmepos
php artisan platform:simulate-client acmepos --per-minute=10 --duration-minutes=1
```
