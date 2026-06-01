import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e-ui',
  // UI tests mutate shared local SQLite state; keep them serial for determinism.
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: process.env.CI ? 'github' : 'list',
  use: {
    baseURL: process.env.APP_URL || 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
  },
  webServer: {
    command: process.env.PW_WEBSERVER_COMMAND || 'php artisan serve --env=playwright --host=127.0.0.1 --port=8000',
    url: process.env.APP_URL || 'http://127.0.0.1:8000/up',
    reuseExistingServer: !process.env.CI,
    timeout: 120000,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
