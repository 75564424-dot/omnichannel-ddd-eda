#!/usr/bin/env node
/**
 * Ensure local .env.* files exist and have a unique APP_KEY (never committed to git).
 *
 * Usage:
 *   node scripts/local-instances/ensure-env-keys.mjs
 *   node scripts/local-instances/ensure-env-keys.mjs --force
 *   node scripts/local-instances/ensure-env-keys.mjs --playwright-only
 */
import { copyFileSync, existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import {
    ensureEnvFile,
    envFileForInstance,
    fleetRegistryPath,
    loadManifest,
    root,
    upsertAppKeyInEnvFile,
} from './lib.mjs';

const args = process.argv.slice(2);
const force = args.includes('--force');
const playwrightOnly = args.includes('--playwright-only');

function ensurePlaywrightEnv() {
    const example = join(root, '.env.playwright.example');
    const target = join(root, '.env.playwright');

    if (!existsSync(target)) {
        if (!existsSync(example)) {
            throw new Error('Missing .env.playwright.example — cannot create .env.playwright.');
        }
        copyFileSync(example, target);
        console.log('Created .env.playwright from .env.playwright.example');
    }

    return upsertAppKeyInEnvFile(target, { force });
}

function ensureInstanceEnvKeys() {
    const results = [];

    for (const instance of loadManifest()) {
        const envPath = envFileForInstance(instance.id);

        if (!existsSync(envPath)) {
            ensureEnvFile(instance, { force: false });
            results.push({ path: envPath, action: 'created' });
            continue;
        }

        const result = upsertAppKeyInEnvFile(envPath, { force });
        if (result) {
            results.push(result);
        }
    }

    if (existsSync(fleetRegistryPath)) {
        const fleet = JSON.parse(readFileSync(fleetRegistryPath, 'utf8'));
        for (const row of fleet.instances ?? []) {
            const envPath = envFileForInstance(row.id);
            if (!existsSync(envPath)) {
                continue;
            }
            const result = upsertAppKeyInEnvFile(envPath, { force });
            if (result) {
                results.push(result);
            }
        }
    }

    return results;
}

try {
    const lines = [];

    if (playwrightOnly) {
        const playwright = ensurePlaywrightEnv();
        if (playwright) {
            lines.push(playwright);
        }
    } else {
        lines.push(...ensureInstanceEnvKeys());
        const playwright = ensurePlaywrightEnv();
        if (playwright) {
            lines.push(playwright);
        }
    }

    if (lines.length === 0) {
        console.log('No local env files to update.');
        process.exit(0);
    }

    for (const { path, action } of lines) {
        const label = path.replace(root + (process.platform === 'win32' ? '\\' : '/'), '');
        console.log(`${action.padEnd(9)} ${label}`);
    }
} catch (error) {
    console.error(error.message ?? error);
    process.exit(1);
}
