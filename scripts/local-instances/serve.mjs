#!/usr/bin/env node
/**
 * Run all local instances in parallel (one php artisan serve per client silo).
 *
 * Usage:
 *   node scripts/local-instances/serve.mjs
 *   node scripts/local-instances/serve.mjs --only=client-alpha,client-beta
 */
import { existsSync } from 'node:fs';
import { loadManifest, spawnArtisanServe, envFileForInstance } from './lib.mjs';

const args = process.argv.slice(2);
const onlyArg = args.find((a) => a.startsWith('--only='));
const onlyIds = onlyArg ? new Set(onlyArg.split('=')[1].split(',').map((s) => s.trim())) : null;

let instances = loadManifest();
if (onlyIds) {
    instances = instances.filter((i) => onlyIds.has(i.id));
}

if (instances.length === 0) {
    console.error('No instances to serve. Run: npm run instances:bootstrap');
    process.exit(1);
}

for (const instance of instances) {
    if (!existsSync(envFileForInstance(instance.id))) {
        console.error(`Missing ${envFileForInstance(instance.id)} — run: npm run instances:bootstrap`);
        process.exit(1);
    }
}

console.log('Starting local instance fleet (Ctrl+C stops all):\n');
for (const instance of instances) {
    console.log(`  :${instance.port}  ${instance.label}  (${instance.id})`);
}
console.log('\nTip: run `npm run build` once so UI assets load on every port.\n');

const children = [];
let shuttingDown = false;

function shutdown(code = 0) {
    if (shuttingDown) {
        return;
    }
    shuttingDown = true;
    for (const child of children) {
        if (!child.killed) {
            child.kill('SIGTERM');
        }
    }
    setTimeout(() => process.exit(code), 150);
}

process.on('SIGINT', () => shutdown(0));
process.on('SIGTERM', () => shutdown(0));

for (const instance of instances) {
    children.push(
        spawnArtisanServe(instance, (inst, code, signal) => {
            if (shuttingDown) {
                return;
            }
            if (signal) {
                console.error(`[${inst.id}] stopped (${signal})`);
            } else if (code !== 0) {
                console.error(`[${inst.id}] exited with code ${code}`);
            }
            shutdown(code ?? 1);
        }),
    );
}
