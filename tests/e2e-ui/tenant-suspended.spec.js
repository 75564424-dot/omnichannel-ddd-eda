import { test, expect } from '@playwright/test';
import { spawnSync } from 'node:child_process';
import fs from 'node:fs';

function parseEnvFile(filePath) {
  const out = {};
  const text = fs.readFileSync(filePath, 'utf8');

  for (const rawLine of text.split(/\r?\n/)) {
    const line = rawLine.trim();
    if (!line || line.startsWith('#')) continue;
    const idx = line.indexOf('=');
    if (idx === -1) continue;
    const key = line.slice(0, idx).trim();
    let value = line.slice(idx + 1).trim();
    if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
      value = value.slice(1, -1);
    }
    out[key] = value;
  }

  return out;
}

function phpSingleQuoted(value) {
  return String(value).replaceAll("'", "\\'");
}

function setTenantStatus({ dbPath, slug, status }) {
  const code =
    `$db = new PDO('sqlite:${phpSingleQuoted(dbPath)}');` +
    `$stmt = $db->prepare('UPDATE tenants SET status = :status WHERE slug = :slug');` +
    `$stmt->execute([':status' => '${phpSingleQuoted(status)}', ':slug' => '${phpSingleQuoted(slug)}']);`;

  const res = spawnSync('php', ['-r', code], { stdio: 'inherit' });
  if (res.status !== 0) {
    throw new Error(`Failed to update tenant status. Exit code: ${res.status}`);
  }
}

test.describe('Tenant suspended portal experience', () => {
  test.beforeAll(() => {
    const envFile = process.env.PW_ENV_FILE || '.env.playwright';
    const env = parseEnvFile(envFile);

    setTenantStatus({
      dbPath: env.DB_DATABASE,
      slug: env.PLATFORM_CLIENT_SLUG,
      status: 'suspended',
    });
  });

  test.afterAll(() => {
    const envFile = process.env.PW_ENV_FILE || '.env.playwright';
    const env = parseEnvFile(envFile);

    setTenantStatus({
      dbPath: env.DB_DATABASE,
      slug: env.PLATFORM_CLIENT_SLUG,
      status: 'active',
    });
  });

  test('visiting /login shows dedicated suspended page (503)', async ({ page }) => {
    const response = await page.goto('/login');
    expect(response?.status()).toBe(503);

    await expect(page.locator('body')).toContainText('Servicio suspendido');

    const html = await page.content();
    expect(html).toContain('Tenant\\/Suspended');
  });
});
