#!/usr/bin/env node
/**
 * Validates that a fresh clone has the files and policies needed to boot locally.
 *
 * Usage: npm run verify:clone
 */
import { execSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const failures = [];

function check(label, ok, detail = '') {
    console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${detail ? ` — ${detail}` : ''}`);
    if (!ok) {
        failures.push(label);
    }
}

const requiredFiles = [
    'bootstrap/ensure-runtime-dirs.php',
    'bootstrap/cache/.gitignore',
    'package.json',
    'package-lock.json',
    'scripts/local-instances/bootstrap.mjs',
    'scripts/local-instances/ensure-env-keys.mjs',
    'scripts/local-instances/lib.mjs',
    '.env.example',
    '.env.playwright.example',
    'deploy/local-instances/instances.json',
    'database/instances/.gitkeep',
];

for (const rel of requiredFiles) {
    check(`file:${rel}`, existsSync(join(root, rel)));
}

const pkg = JSON.parse(readFileSync(join(root, 'package.json'), 'utf8'));
check('vite-major-8', String(pkg.devDependencies?.vite ?? '').includes('8'));
check('plugin-vue-major-6', String(pkg.devDependencies?.['@vitejs/plugin-vue'] ?? '').includes('6'));
check('laravel-vite-plugin-major-3', String(pkg.devDependencies?.['laravel-vite-plugin'] ?? '').includes('3'));

const gitignore = readFileSync(join(root, '.gitignore'), 'utf8');
check('gitignore-env-wildcard', gitignore.includes('.env.*'));
check('gitignore-bootstrap-cache-php', gitignore.includes('/bootstrap/cache/*.php'));

let tracked = [];
try {
    tracked = execSync('git ls-files', { cwd: root, encoding: 'utf8' })
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);
} catch {
    check('git-available', false, 'git ls-files failed');
}

const forbiddenTracked = tracked.filter((path) => {
    if (path === '.env.example' || path.endsWith('.example')) {
        return false;
    }
    if (/^\.env(\.|$)/.test(path)) {
        return true;
    }
    if (path.includes('/.env.') || path.endsWith('.env')) {
        return true;
    }
    return false;
});
check('git-no-tracked-env-files', forbiddenTracked.length === 0, forbiddenTracked.join(', ') || 'ok');

const secretPattern = /^APP_KEY=base64:[A-Za-z0-9+/=]{20,}$/m;
const allowlistedPaths = new Set([
    'phpunit.xml',
    '.github/workflows/staging.yml',
]);

const leakedSecrets = [];
for (const path of tracked) {
    if (allowlistedPaths.has(path)) {
        continue;
    }
    const full = join(root, path);
    if (!existsSync(full) || full.endsWith('.example')) {
        continue;
    }
    const content = readFileSync(full, 'utf8');
    if (secretPattern.test(content)) {
        leakedSecrets.push(path);
    }
}
check('git-no-leaked-app-keys', leakedSecrets.length === 0, leakedSecrets.join(', ') || 'ok');

console.log(`\nSummary: ${failures.length} failure(s)`);
process.exit(failures.length > 0 ? 1 : 0);
