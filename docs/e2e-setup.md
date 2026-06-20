# E2E Testing with Playwright

End-to-end tests for Lynk-clone using [Playwright](https://playwright.dev/).

## Why E2E?

PHPUnit/Pest feature tests cover business logic in isolation, but they
don't catch:
- JavaScript errors that crash the UI
- CSRF token / session cookie misconfiguration
- Layout regressions
- View-count / N+1 query issues that only appear under real HTTP load
- Broken form submissions to actual `php artisan serve`

E2E tests run the real application in a real browser, exercising the
full stack from HTML to database.

## What's covered

| Spec | Flow | Catches |
|------|------|---------|
| `homepage.spec.ts` | Landing page loads, demo creators visible | DB seed failures, broken layout |
| `auth.spec.ts` | Register, login, bad credentials | CSRF, session, password hashing, honeypot |
| `public-profile.spec.ts` | View creator profile, 404 for unknown user | View counter, profile controller, 404 handling |
| `product-page.spec.ts` | View product detail, price, buy CTA | Product controller, slug routing |
| `cart-flow.spec.ts` | Add to cart, view cart, **regression test for Task #2 bug** | The cart cookie regex + `?User $buyer` fixes |

## Running locally

### One-time setup

```bash
# Install @playwright/test (dev dependency)
npm install

# Install the Chromium browser
npx playwright install chromium
```

### Run all tests

```bash
npm run e2e
```

This will:
1. Wipe `database/e2e.sqlite`
2. Run migrations and `DemoSeeder`
3. Start `php artisan serve` on port 3100
4. Run all `e2e/*.spec.ts` files against the server
5. Tear down the server and remove the test DB

### Run with UI mode (debug)

```bash
npm run e2e:ui
```

Opens the Playwright UI — pick a test, watch it run, see traces.

### Run a specific test

```bash
npx playwright test e2e/cart-flow.spec.ts
```

### View last test report

```bash
npm run e2e:report
```

## How it works (architecture)

```
┌─────────────────────────────────────────────────────┐
│  e2e/global-setup.ts                                │
│  - Delete database/e2e.sqlite (if exists)           │
│  - Run: php artisan migrate:fresh --force           │
│  - Run: php artisan db:seed --class=DemoSeeder      │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  Playwright webServer:                              │
│  command: php artisan serve --port=3100 --no-reload │
│  env: DB_CONNECTION=sqlite, DB_DATABASE=e2e.sqlite  │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  Specs in e2e/*.spec.ts run in headless Chromium   │
│  - baseURL: http://127.0.0.1:3100                   │
│  - Sequential (workers=1, fullyParallel=false)       │
│  - Retries: 2 in CI, 0 locally                      │
│  - Trace + video + screenshot on failure            │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  e2e/global-teardown.ts                             │
│  - Delete database/e2e.sqlite                       │
└─────────────────────────────────────────────────────┘
```

## CI

`.github/workflows/e2e.yml` runs on every PR that touches:
- `app/`, `routes/`, `resources/`, `database/`
- `e2e/`, `playwright.config.ts`
- `package.json`

It installs PHP 8.3, Node 22, builds the frontend, installs Chromium,
and runs the suite. Reports and traces are uploaded as artifacts (7
days for the HTML report, 3 days for raw test results).

## Writing a new spec

1. Create `e2e/<feature>.spec.ts`.
2. Use the seeded demo data — usernames `demo_alice`, `demo_bob`, etc.
   See `database/seeders/DemoSeeder.php` for what's available.
3. Use semantic selectors (`getByRole`, `getByText`) before CSS selectors.
4. Avoid asserting on exact copy — the test should survive copy edits.
5. Always wait for `networkidle` before asserting on dynamic content.

Example:

```typescript
import { test, expect } from '@playwright/test';

test('my new flow', async ({ page }) => {
    await page.goto('/demo_alice');
    await page.waitForLoadState('networkidle');

    await expect(
        page.getByRole('link', { name: /demo_alice/i }).first(),
    ).toBeVisible();
});
```

## Pitfalls

- **Server not starting in CI** — `php artisan serve` is slow to boot.
  The config has a 60-second timeout. If you see "Timed out waiting for
  http://127.0.0.1:3100", the build is probably OOM. Bump the runner.
- **SQLite race conditions** — the suite runs sequentially (`workers: 1`).
  If you need parallel tests, switch to a MySQL/Postgres container.
- **Sessions leaking between tests** — each test gets a fresh browser
  context, which means a fresh cookie jar. State from one test does NOT
  carry to the next. Use `test.describe.serial` for multi-step flows.
- **Time-sensitive assertions** — avoid asserting on exact timestamps,
  countdown timers, or rate-limit windows. Use `toBeVisible()` with a
  generous timeout instead.
