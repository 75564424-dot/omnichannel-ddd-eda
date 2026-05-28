import { test, expect } from '@playwright/test';

/**
 * UI smoke — Plan_Calidad Fase 3.
 * Requires PLATFORM_WEB_AUTH_ENABLED=false (phpunit.xml / CI env).
 */
test.describe('Platform UI smoke', () => {
  test('login page renders', async ({ page }) => {
    const response = await page.goto('/login');
    expect(response?.status()).toBe(200);
    await expect(page.locator('body')).toBeVisible();
  });

  test('dashboard page loads', async ({ page }) => {
    const response = await page.goto('/dashboard');
    expect(response?.status()).toBe(200);
    await expect(page.locator('body')).toBeVisible();
  });

  test('middleware console page loads', async ({ page }) => {
    const response = await page.goto('/middleware');
    expect(response?.status()).toBe(200);
    await expect(page.locator('body')).toBeVisible();
  });
});
