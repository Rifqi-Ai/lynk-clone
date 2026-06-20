import { defineConfig, devices } from '@playwright/test';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const E2E_DB_PATH = path.join(__dirname, 'database', 'e2e.sqlite');

/**
 * Playwright E2E config for Lynk-clone (Laravel 13 SaaS).
 *
 * Runs the actual application via `php artisan serve` and exercises
 * real user flows in a real browser. Catches integration bugs that
 * feature tests (PHPUnit/Pest) miss — e.g. JavaScript errors, layout
 * issues, CSRF token mismatches, session cookie problems.
 *
 * Architecture:
 *   - File-based SQLite at database/e2e.sqlite (shared between server + tests)
 *   - global-setup.ts wipes + migrates + seeds before all tests run
 *   - global-teardown.ts deletes the file after all tests
 *
 * @see docs/e2e-setup.md
 * @see e2e/global-setup.ts
 */
export default defineConfig({
    testDir: './e2e',
    testMatch: /.*\.spec\.ts$/,
    // Each spec is a real user flow — they touch the same DB and shouldn't race.
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    reporter: process.env.CI
        ? [['github'], ['list']]
        : [['list'], ['html', { open: 'never', outputFolder: 'playwright-report' }]],

    timeout: 30_000,
    expect: { timeout: 5_000 },

    use: {
        baseURL: process.env.BASE_URL ?? 'http://127.0.0.1:3100',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
        actionTimeout: 10_000,
        navigationTimeout: 15_000,
    },

    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],

    globalSetup: path.join(__dirname, 'e2e', 'global-setup.ts'),
    globalTeardown: path.join(__dirname, 'e2e', 'global-teardown.ts'),

    // Start the Laravel dev server before tests, stop it after.
    // Note: we don't set `url` here because `php artisan serve` has a slow
    // first request (~500ms boot of Laravel), and Playwright's URL health
    // check can hit a stale response during boot and time out. Instead we
    // use `reuseExistingServer: !CI` so locally you can start the server
    // yourself (e.g. via `php artisan serve --port=3100`) and skip the boot.
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=3100 --no-reload',
        reuseExistingServer: !process.env.CI,
        timeout: 60_000,
        stdout: 'pipe',
        stderr: 'pipe',
        env: {
            APP_ENV: 'testing',
            APP_DEBUG: 'true',
            // File-based SQLite so the server process and test setup share the DB.
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: E2E_DB_PATH,
        },
    },
});
