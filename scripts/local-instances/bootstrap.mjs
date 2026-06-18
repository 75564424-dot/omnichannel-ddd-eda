#!/usr/bin/env node
/**
 * Bootstrap N local client silos: .env per instance, dedicated SQLite DB, migrate + seed.
 *
 * Usage:
 *   node scripts/local-instances/bootstrap.mjs
 *   node scripts/local-instances/bootstrap.mjs --force-env
 *   node scripts/local-instances/bootstrap.mjs --only=client-alpha
 */
import {
    ensureEnvFile,
    ensureFleetRegistry,
    ensureInstancesDbDir,
    ensureStorageDirectories,
    envFileForInstance,
    loadManifest,
    root,
    runArtisan,
} from './lib.mjs';

const args = process.argv.slice(2);
const forceEnv = args.includes('--force-env');
const onlyArg = args.find((a) => a.startsWith('--only='));
const onlyId = onlyArg ? onlyArg.split('=')[1] : null;

const instances = loadManifest().filter((i) => (onlyId ? i.id === onlyId : true));

if (instances.length === 0) {
    console.error(onlyId ? `No instance with id "${onlyId}" in manifest.` : 'No instances to bootstrap.');
    process.exit(1);
}

ensureInstancesDbDir();
ensureFleetRegistry();
ensureStorageDirectories();

console.log(`Bootstrapping ${instances.length} local instance(s) from ${root}\n`);

for (const instance of instances) {
    console.log(`=== ${instance.label} (${instance.id}) :${instance.port} ===`);

    const envPath = ensureEnvFile(instance, { force: forceEnv });
    console.log(`  env  → ${envPath.replace(root + (process.platform === 'win32' ? '\\' : '/'), '')}`);
    console.log(`  db   → database/instances/${instance.slug}.sqlite`);

    try {
        runArtisan(instance.id, ['migrate', '--force'], { inherit: true });

        if (instance.role === 'control_plane') {
            runArtisan(instance.id, ['db:seed', '--force'], { inherit: true });
        } else {
            runArtisan(instance.id, ['platform:instance:bootstrap', '--skip-admin'], { inherit: true });
            runArtisan(instance.id, ['db:seed', '--class=Database\\Seeders\\MiddlewareDatabaseSeeder', '--force'], {
                inherit: true,
            });
        }

        console.log(`  login → ${instance.role === 'control_plane' ? instance.adminEmail : instance.adminEmail} / ${instance.adminPassword}`);
        console.log(`  url   → http://127.0.0.1:${instance.port}\n`);
    } catch (error) {
        console.error(`  FAILED: ${instance.id}`);
        if (error.stdout) {
            console.error(error.stdout);
        }
        if (error.stderr) {
            console.error(error.stderr);
        }
        process.exit(1);
    }
}

console.log('Done. Start servers: npm run instances:serve');
console.log('Use compiled assets: npm run build (once) before testing UI on multiple ports.');
