#!/usr/bin/env node
/**
 * Reset operational tables (queue, events, metrics) on every local fleet instance.
 *
 * Usage: node scripts/local-instances/reset-operational.mjs
 */
import { execSync } from 'node:child_process';
import { loadManifest, root } from './lib.mjs';

const instances = loadManifest();
const flags = '--force --with-cache --with-queues';

console.log('Resetting operational data on local fleet:\n');

for (const instance of instances) {
    console.log(`  · ${instance.id} (:${instance.port})`);
    execSync(`php artisan demo:reset-operational ${flags} --env=${instance.id} --no-ansi`, {
        cwd: root,
        stdio: 'inherit',
    });
}

try {
    console.log('\nResetting simulation history on control plane…');
    execSync('php artisan platform:simulation:reset --fail-stale --env=control-plane --no-ansi', {
        cwd: root,
        stdio: 'inherit',
    });
} catch (error) {
    console.warn('  (simulation history reset skipped — run manually: php artisan platform:simulation:reset --fail-stale --env=control-plane)');
}

console.log('\nFleet operational reset complete.');
