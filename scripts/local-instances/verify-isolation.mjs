#!/usr/bin/env node
/**
 * Smoke test: operator of client-beta must NOT login on client-alpha port.
 */
import { execSync } from 'node:child_process';
import { loadManifest } from './lib.mjs';

const [alpha, beta] = loadManifest().filter((i) => i.role === 'client').slice(0, 2);

if (!alpha || !beta) {
    console.error('Need at least 2 client instances in manifest.');
    process.exit(1);
}

function curlLogin(port, email, password) {
    try {
        execSync(
            `curl -s -o NUL -w "%{http_code}" -c NUL -b NUL -X POST "http://127.0.0.1:${port}/login" -H "Content-Type: application/x-www-form-urlencoded" -d "email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}"`,
            { encoding: 'utf8', shell: true },
        );
    } catch {
        return '000';
    }
    return 'check manually';
}

console.log(`Isolation check (servers must be running):
  1. Open http://127.0.0.1:${alpha.port}/login — login ${alpha.adminEmail} / ${alpha.adminPassword} → OK
  2. Open http://127.0.0.1:${beta.port}/login — same credentials → must FAIL (wrong tenant)
  3. Login ${beta.adminEmail} on :${beta.port} → OK
`);

console.log('Automated PHP test:');
execSync(`php artisan test --filter=operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled`, {
    cwd: new URL('../..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1'),
    stdio: 'inherit',
    shell: true,
});
