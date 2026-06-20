# Lighthouse CI Performance Gate

Lynk-clone uses [Lighthouse CI](https://github.com/GoogleChrome/lighthouse-ci) to enforce Core Web Vitals budgets on every pull request. The gate runs in GitHub Actions and **blocks merging** if any budget is exceeded.

## What It Enforces

| Audit / Category | Severity | Threshold | Why |
|---|---|---|---|
| `largest-contentful-paint` (LCP) | `error` | ≤ 2500 ms | Google "good" LCP |
| `cumulative-layout-shift` (CLS) | `error` | ≤ 0.1 | Google "good" CLS |
| `total-blocking-time` (TBT) | `error` | ≤ 200 ms | INP lab proxy |
| `interaction-to-next-paint` (INP) | `warn` | ≤ 200 ms | Not in all Lighthouse builds; treat as advisory |
| `categories:performance` | `error` | ≥ 0.9 | Top (green) band |
| `categories:seo` | `error` | ≥ 0.95 | SEO is cheap to keep perfect |
| `categories:accessibility` | `error` | ≥ 0.95 | a11y regressions must block |
| `categories:best-practices` | `error` | ≥ 0.9 | Green band |

## How It Works

1. PR opened (or pushed) → workflow triggers
2. **Build**: PHP deps install, Vite assets build, Laravel config/route/view cached
3. **Start**: `php artisan serve --port=3100 --no-reload --env=production`
4. **Collect**: Lighthouse runs **3 times** against `http://localhost:3100/`
5. **Assert**: Median of the 3 runs is compared against budgets
6. **Upload**: HTML + JSON reports uploaded as CI artifact (even on failure)

## Running Locally

### Prerequisites
- Node 22+
- Chrome / Chromium / Edge installed
- PHP 8.3+ with composer

### One-shot run
```bash
npm install
npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
npm run lhci
```

### Desktop form factor (optional)
```bash
LHCI_FORM_FACTOR=desktop npm run lhci
```

### Without Chrome (CI-only mode)
The gate will fail with "No Chrome installation found" if you don't have a browser. This is expected — the actual gate runs in GitHub Actions which has Chrome pre-installed.

## Tuning Budgets

Edit [`lighthouserc.cjs`](../lighthouserc.cjs). Every budget is a **named constant with a unit** (e.g., `LCP_BUDGET_MS = 2500`):

```js
const LCP_BUDGET_MS = 2500;  // ← change this, not the assertion below
const CLS_BUDGET = 0.1;
const PERFORMANCE_FLOOR = 0.9;
// ...

assertions: {
  'largest-contentful-paint': ['error', { maxNumericValue: LCP_BUDGET_MS }],
  // ...
}
```

**Rules:**
- **Tighten, don't loosen.** A budget you keep raising to make CI pass is a budget that no longer protects anything.
- **Fix the cause, not the threshold.** If perf drops, open the uploaded report, read the failed audit's "Opportunities"/"Diagnostics", fix the underlying issue (oversized image, render-blocking JS, layout shift from unsized media).

## Adding Pages to the Gate

Append the new URL to `LYNK_URLS` in `lighthouserc.cjs`:

```js
const LYNK_URLS = [
    `${BASE_URL}/`,            // Landing
    `${BASE_URL}/login`,       // Login form
    `${BASE_URL}/register`,    // Registration
];
```

Each URL is audited independently against the same budgets. **Note:** authenticated pages would need a separate "logged-in" URL set with a session cookie — out of scope for the initial gate (which audits only public landing).

## Debugging Failures

1. **Open the `lighthouse-reports` artifact** in the failed CI run (Actions → run → bottom of page)
2. **Look at the HTML report** for the failing URL — it shows the full Lighthouse breakdown
3. **Common failures**:
   - LCP too high → oversized hero image, render-blocking CSS/JS
   - CLS too high → unsized images, late-loading fonts
   - TBT too high → long JS tasks, third-party scripts
   - Perf score low → multiple of the above

## References

- Skill: `stareezy-frontend-lighthouse` (installed via preflight-skill-scan)
- Google's CWV thresholds: https://web.dev/vitals/
- Lighthouse CI docs: https://github.com/GoogleChrome/lighthouse-ci
