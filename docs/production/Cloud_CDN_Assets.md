# CDN y assets estáticos en cloud

**Plan:** Plan_Cloud.md Fase 3

## Contexto

Vite compila assets a `public/build/` en build time (Dockerfile stage `frontend`). En producción cloud, servir estáticos desde CDN reduce carga en php-fpm/nginx.

## Estrategia recomendada

### Fase actual (sin CDN)

- Assets embebidos en imagen Docker
- Nginx sirve `/build/*` desde `public/`

### Fase CDN (cuando tráfico UI lo justifique)

1. **Build CI** sube `public/build/` a object storage (S3 / Azure Blob / GCS)
2. Configurar `ASSET_URL=https://cdn.cliente.com` en `.env`
3. Laravel Vite helper usará URL absoluta para manifests
4. CDN origin: bucket privado + OAI/OIDC o signed URLs para `/build/assets/*`
5. Cache-Control: `public, max-age=31536000, immutable` en assets fingerprinted

## Configuración Laravel

```env
ASSET_URL=https://cdn.middleware.cliente.com
```

## Consideraciones

- **Inertia:** páginas dinámicas siguen en origin; solo JS/CSS/fonts van a CDN
- **CORS:** no requerido para assets same-site; verificar si dominio CDN distinto
- **Rollback:** versionar build por git SHA en path (`/build/<sha>/`) opcional

## Proveedores

| Cloud | Servicio |
|-------|----------|
| AWS | CloudFront + S3 |
| Azure | Azure CDN + Blob |
| GCP | Cloud CDN + Cloud Storage |

## No implementado en repo

Integración automática upload CDN — configurar en pipeline deploy del cliente (Plan_CI_CD release job).
