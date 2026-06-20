import { test, expect } from '@playwright/test';

/**
 * E2E: Authentication flows.
 *
 * Exercises the full register + login user journey, including:
 *  - Form submission with CSRF token
 *  - Honeypot field (must be empty)
 *  - Session cookie handling
 *  - Redirect to dashboard on success
 *  - Error display on bad credentials
 */
test.describe('Authentication', () => {
    test('register a new account and land on dashboard', async ({ page }) => {
        // Use a unique email per test run to avoid rate-limit conflicts.
        const unique = Date.now();
        const email = `e2e+${unique}@lynk.test`;
        const username = `e2e${unique}`;

        await page.goto('/register');
        await expect(page).toHaveTitle(/Daftar|Register/i);

        // Fill the form.
        await page.locator('input[name="name"]').fill('E2E Test User');
        await page.locator('input[name="username"]').fill(username);
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password123');
        await page.locator('input[name="password_confirmation"]').fill('password123');

        // The "website" field is a honeypot — leave it empty.
        await expect(page.locator('input[name="website"]')).toHaveValue('');

        // Accept terms (if checkbox is present).
        const terms = page.locator('input[name="terms"]');
        if (await terms.isVisible().catch(() => false)) {
            await terms.check();
        }

        // Submit and wait for navigation.
        await page.locator('button[type="submit"]').click();
        await page.waitForLoadState('networkidle');

        // Should land on dashboard — by URL or by visible dashboard marker.
        // The dashboard route is `/dashboard` (route name `dashboard.index`).
        const url = page.url();
        expect(url).not.toContain('/register');

        // The dashboard should show the user's name or a dashboard nav.
        const body = await page.locator('body').textContent();
        expect(body).toContain('E2E Test User');
    });

    test('login with valid credentials lands on dashboard', async ({ page }) => {
        // demo_alice is seeded with password "password123".
        await page.goto('/login');
        await expect(page).toHaveTitle(/Masuk|Login/i);

        await page.locator('input[name="login"]').fill('alice@demo.linka.id');
        await page.locator('input[name="password"]').fill('password123');
        await page.locator('button[type="submit"]').click();
        await page.waitForLoadState('networkidle');

        // Should NOT stay on /login.
        expect(page.url()).not.toContain('/login');

        // Dashboard should show alice's name.
        const body = await page.locator('body').textContent();
        expect(body).toContain('Alice');
    });

    test('login with invalid credentials shows error', async ({ page }) => {
        await page.goto('/login');

        await page.locator('input[name="login"]').fill('alice@demo.linka.id');
        await page.locator('input[name="password"]').fill('wrong-password');
        await page.locator('button[type="submit"]').click();

        // Should stay on login page and show an error.
        await expect(page).toHaveURL(/\/login/);

        // The error should mention credentials (allow any reasonable message).
        const body = await page.locator('body').textContent();
        expect(body).toMatch(/salah|invalid|incorrect|credential/i);
    });
});
