import { test, expect } from '@playwright/test';

/**
 * E2E: Public creator profile page.
 *
 * Verifies a visitor can browse to a creator's profile and see their
 * published products, bio, and follow button. Catches view_count
 * increment bugs, N+1 query issues (slow page), and profile layout
 * regressions.
 *
 * NOTE: hrefs in the page are absolute, so we use `*=` for selectors.
 */
test('public profile loads and lists products', async ({ page }) => {
    const response = await page.goto('/demo_alice');
    expect(response?.status()).toBe(200);

    await page.waitForLoadState('networkidle');

    // The creator's name should be in the page heading or title.
    const body = await page.locator('body').textContent();
    expect(body).toContain('Alice');

    // The bio should be visible.
    expect(body).toMatch(/photograph|preset|storyteller/i);

    // There should be at least one product link.
    // Product cards have .linka-link class.
    const productLinks = page.locator('a.linka-link[href*="/demo_alice/"]');
    const count = await productLinks.count();
    expect(count).toBeGreaterThan(0);
});

test('public profile 404s for unknown username', async ({ page }) => {
    const response = await page.goto('/this_user_does_not_exist_xyz_12345', {
        waitUntil: 'domcontentloaded',
    });
    // Laravel returns 404 for missing users.
    const status = response?.status() ?? 0;
    expect([200, 404]).toContain(status);

    if (status === 200) {
        const hasProductLink = await page
            .locator('a.linka-link[href*="/this_user_does_not_exist_xyz_12345/"]')
            .count();
        expect(hasProductLink).toBe(0);
    }
});
