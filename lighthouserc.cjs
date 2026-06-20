/**
 * Lighthouse CI configuration — Core Web Vitals budgets for Lynk-clone.
 *
 * Enforces Google's mobile "good" CWV thresholds:
 *   - Largest Contentful Paint (LCP) ≤ 2500 ms
 *   - Cumulative Layout Shift (CLS)  ≤ 0.1
 *   - Interaction to Next Paint (INP) ≤ 200 ms (gated via TBT lab proxy)
 *
 * INP is a *field* metric with no direct lab audit, so in the lab we gate on
 * Total Blocking Time (TBT) — Lighthouse's recommended lab proxy — at the same
 * budget, and assert the experimental INP audit directly as a warning where the
 * build exposes it.
 *
 * The collection runs against the production PHP server (artisan serve with
 * cached config/routes/views) on Lighthouse's default mobile emulation.
 */

/** Fixed port the production server is started on for the audit. */
const PORT = 3100;
const BASE_URL = `http://localhost:${PORT}`;

/** Pages whose budgets are enforced in CI. */
const LYNK_URLS = [
    `${BASE_URL}/`, // Landing page
];

/**
 * Core Web Vitals budgets on mobile — Google's "good" thresholds.
 * These are the values that earn the best Lighthouse scores.
 */
const LCP_BUDGET_MS = 2500; // good
const INP_BUDGET_MS = 200;  // good (TBT lab proxy)
const CLS_BUDGET = 0.1;     // good

/**
 * Category score floors — protect against gradual regression in any dimension.
 * These are intentionally tight; loosen only with a recorded reason.
 */
const PERFORMANCE_FLOOR = 0.9;
const SEO_FLOOR = 0.95;
const A11Y_FLOOR = 0.95;
const BEST_PRACTICES_FLOOR = 0.9;

module.exports = {
    ci: {
        collect: {
            // Boot Laravel's production server for the audit.
            // --no-reload avoids the file-watcher consuming CPU and skewing TBT.
            // APP_ENV=production + config:cache/route:cache/view:cache ensure we
            // measure the real optimized output, not dev-mode bloat.
            startServerCommand: `php artisan serve --port=${PORT} --no-reload --env=production`,
            startServerReadyPattern: 'Server running on',
            startServerReadyTimeout: 120000,
            url: LYNK_URLS,
            // Median of multiple runs keeps the gate stable against per-run jitter.
            numberOfRuns: 3,
            settings: {
                // Default mobile emulation; opt into desktop via env for a second run.
                preset:
                    process.env.LHCI_FORM_FACTOR === 'desktop' ? 'desktop' : undefined,
                // Only gate the categories we care about; skip PWA category noise.
                onlyCategories: [
                    'performance',
                    'seo',
                    'accessibility',
                    'best-practices',
                ],
            },
        },
        assert: {
            // Median across runs is the value compared against each budget.
            aggregationMethod: 'median-run',
            assertions: {
                // --- Core Web Vitals budgets (the contract) --------------------
                'largest-contentful-paint': [
                    'error',
                    { maxNumericValue: LCP_BUDGET_MS },
                ],
                'cumulative-layout-shift': [
                    'error',
                    { maxNumericValue: CLS_BUDGET },
                ],
                'total-blocking-time': [
                    'error',
                    { maxNumericValue: INP_BUDGET_MS },
                ],
                // Direct INP audit where the Lighthouse build exposes it (else ignored).
                'interaction-to-next-paint': [
                    'warn',
                    { maxNumericValue: INP_BUDGET_MS },
                ],

                // --- Category floors (target top Lighthouse scores) -----------
                'categories:performance': ['error', { minScore: PERFORMANCE_FLOOR }],
                'categories:seo': ['error', { minScore: SEO_FLOOR }],
                'categories:accessibility': ['error', { minScore: A11Y_FLOOR }],
                'categories:best-practices': ['error', { minScore: BEST_PRACTICES_FLOOR }],
            },
        },
        upload: {
            // Keep reports in the CI run's filesystem; no external LHCI server.
            target: 'filesystem',
            outputDir: './.lighthouseci',
        },
    },
};
