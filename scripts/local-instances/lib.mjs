import { execSync, spawn, spawnSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

export const root = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
export const manifestPath = join(root, 'deploy', 'local-instances', 'instances.json');
export const fleetRegistryPath = join(root, 'deploy', 'local-instances', 'fleet-registry.json');
export const instancesDbDir = join(root, 'database', 'instances');

export function loadManifest() {
    const staticData = JSON.parse(readFileSync(manifestPath, 'utf8'));
    const controlPlane = (staticData.instances ?? []).filter((row) => row.role === 'control_plane');
    let fleetClients = [];

    if (existsSync(fleetRegistryPath)) {
        const fleetData = JSON.parse(readFileSync(fleetRegistryPath, 'utf8'));
        fleetClients = fleetData.instances ?? [];
    }

    const merged = [...controlPlane, ...fleetClients];
    if (merged.length === 0) {
        throw new Error('No instances defined in instances.json or fleet-registry.json.');
    }

    return merged;
}

export function envFileForInstance(instanceId) {
    return join(root, `.env.${instanceId}`);
}

export function generateAppKey() {
    return execSync('php artisan key:generate --show', {
        cwd: root,
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'pipe'],
    }).trim();
}

export function readExistingAppKey(envPath) {
    if (!existsSync(envPath)) {
        return null;
    }
    const match = readFileSync(envPath, 'utf8').match(/^APP_KEY=(.+)$/m);
    const value = match?.[1]?.trim();
    return value && value !== '' ? value : null;
}

/**
 * @param {object} instance
 * @param {string} appKey
 */
export function buildEnvContent(instance, appKey) {
    const { id, label, slug, port, role, adminEmail, adminPassword, adminName } = instance;
    const isControlPlane = role === 'control_plane';
    const appUrl = `http://127.0.0.1:${port}`;
    ensureSqliteDatabase(slug);
    const dbAbsolute = sqlitePathForSlug(slug).replace(/\\/g, '/');
    const sessionCookie = `platform_session_${slug.replace(/-/g, '_')}`;
    const xsrfCookie = sessionCookie.replace('_session', '_xsrf');
    const adminPasswordValue = adminPassword && String(adminPassword).trim() !== ''
        ? adminPassword
        : 'client-local-dev';
    const modulesConfigPath = `config/modules/instances/${slug}/modules_config.json`;
    const displayName = isControlPlane ? 'SaaS' : label;

    return `# Generated for local multi-instance dev (${id}). Edit deploy/local-instances/instances.json and re-run bootstrap.
APP_NAME="${displayName}"
APP_ENV=${id}
APP_KEY=${appKey}
APP_DEBUG=true
APP_URL=${appUrl}
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_LEVEL=debug

PLATFORM_CLIENT_SLUG=${slug}
PLATFORM_CLIENT_NAME="${label}"
PLATFORM_DEPLOYMENT_MODE=instance_per_client
PLATFORM_CONTROL_PLANE=${isControlPlane ? 'true' : 'false'}
PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false
PLATFORM_SEED_INSTANCE_TENANT=true
PLATFORM_SIMULATION_ENABLED=false
PLATFORM_CONTROL_PLANE_URL=http://127.0.0.1:8000
PLATFORM_SIMULATION_INTERNAL_TOKEN=local-dev-simulation-token
${isControlPlane ? `PLATFORM_LOCAL_FLEET_AUTO_PROVISION=true
PLATFORM_LOCAL_FLEET_PORT_START=8001
PLATFORM_LOCAL_FLEET_DEFAULT_ADMIN_PASSWORD=client-local-dev
` : ''}
DB_CONNECTION=sqlite
DB_DATABASE=${dbAbsolute}

QUEUE_CONNECTION=sync
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_COOKIE=${sessionCookie}
SESSION_XSRF_COOKIE=${xsrfCookie}
${isControlPlane ? '' : `MODULES_CONFIG_PATH=${modulesConfigPath}\n`}

PLATFORM_API_AUTH_ENABLED=true
PLATFORM_WEB_AUTH_ENABLED=true
CORS_ALLOWED_ORIGINS=${appUrl},http://localhost:${port}
SANCTUM_STATEFUL_DOMAINS=127.0.0.1:${port},localhost:${port}

PLATFORM_OBSERVABILITY_SERVICE_NAME=${slug}

PLATFORM_SEED_SAAS_OPERATOR=${isControlPlane ? 'true' : 'false'}
PLATFORM_SAAS_ADMIN_NAME="${isControlPlane ? adminName : 'SaaS Admin'}"
PLATFORM_SAAS_ADMIN_EMAIL=${isControlPlane ? adminEmail : 'saas@local'}
PLATFORM_SAAS_ADMIN_PASSWORD=${isControlPlane ? adminPassword : 'unused'}

PLATFORM_SEED_ADMIN_OPERATOR=${isControlPlane ? 'false' : 'true'}
PLATFORM_ADMIN_NAME="${isControlPlane ? 'Platform Admin' : adminName}"
PLATFORM_ADMIN_EMAIL=${isControlPlane ? 'admin@unused-local' : adminEmail}
PLATFORM_ADMIN_PASSWORD=${isControlPlane ? 'unused' : adminPasswordValue}
PLATFORM_ADMIN_ROLE=platform_admin
`;
}

export function ensureEnvFile(instance, { force = false } = {}) {
    const envPath = envFileForInstance(instance.id);
    const appKey = force ? generateAppKey() : readExistingAppKey(envPath) ?? generateAppKey();
    const content = buildEnvContent(instance, appKey);
    writeFileSync(envPath, content, 'utf8');
    return envPath;
}

export function runArtisan(instanceId, args, { inherit = false } = {}) {
    const result = spawnSync('php', ['artisan', `--env=${instanceId}`, ...args], {
        cwd: root,
        stdio: inherit ? 'inherit' : 'pipe',
        encoding: 'utf8',
    });

    if (result.status !== 0) {
        const err = new Error(`artisan ${args.join(' ')} failed for --env=${instanceId}`);
        err.stdout = result.stdout;
        err.stderr = result.stderr;
        throw err;
    }

    return result.stdout;
}

export function spawnArtisanServe(instance, onExit) {
    const child = spawn(
        'php',
        ['artisan', `--env=${instance.id}`, 'serve', '--host=127.0.0.1', `--port=${instance.port}`],
        {
            cwd: root,
            stdio: 'inherit',
            shell: false,
            env: {
                ...process.env,
                APP_ENV: instance.id,
            },
        },
    );

    child.on('exit', (code, signal) => {
        onExit?.(instance, code, signal);
    });

    return child;
}

export function sqlitePathForSlug(slug) {
    return join(instancesDbDir, `${slug}.sqlite`);
}

export function ensureInstancesDbDir() {
    if (!existsSync(instancesDbDir)) {
        mkdirSync(instancesDbDir, { recursive: true });
    }
}

export function ensureSqliteDatabase(slug) {
    ensureInstancesDbDir();
    const dbPath = sqlitePathForSlug(slug);
    if (!existsSync(dbPath)) {
        writeFileSync(dbPath, '');
    }
    return dbPath;
}
