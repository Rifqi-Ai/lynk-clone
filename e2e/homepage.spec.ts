import { test, expect } from '@playwright/test';

/**
 * E2E: Landing page smoke test.
 *
 * Verifies the public marketing page loads with key product names
 * from the seeded demo creators. Catches broken layout, missing
 * content, and database seeding failures.
 */
test('homepage loads and shows demo creators', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);

    // Wait for the document to be fully rendered (Blade hydration, etc).
    await page.waitForLoadState('networkidle');

    // The landing page links to demo creators by username.
    await expect(page.locator('a[href="/demo_alice"]').first()).toBeVisible();
    await expect(page.locator('a[href="/demo_bob"]').first()).toBeVisible();

    // The register CTA should be present.
    const registerLink = page.locator('a[href*="register"]').first();
    await expect(registerLink).toBeVisible();
});
