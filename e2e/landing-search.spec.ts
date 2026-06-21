import { test, expect } from '@playwright/test';

/**
 * Regression tests for Phase 17 Task #6 — Landing search feature.
 *
 * Closes docs/ux-audit-2026-06-21.md Trunk Test item:
 *   "Search? Not present on landing — Phase 14"
 */

test.describe('Landing search (Phase 17 Task #6)', () => {
  test('landing page nav has a visible search input', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    const searchInput = page.locator('#nav-search');
    await expect(searchInput).toBeVisible();
  });

  test('typing a query and submitting nav search navigates to /search', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await page.locator('#nav-search').fill('alice');
    await page.locator('#nav-search').press('Enter');
    await page.waitForURL(/\/search/, { timeout: 10_000 });
    await expect(page).toHaveURL(/\/search\?q=alice/);
  });

  test('search results page shows the query and a heading', async ({ page }) => {
    await page.goto('/search?q=alice');
    await page.waitForLoadState('networkidle');
    const h1 = page.locator('h1').first();
    await expect(h1).toBeVisible();
    const text = await h1.textContent();
    expect(text).toContain('alice');
  });

  test('search results page has a re-search form', async ({ page }) => {
    await page.goto('/search?q=alice');
    await page.waitForLoadState('networkidle');
    // Use the hero search input on the /search page (id="search-input", not the nav one).
    const searchInput = page.locator('#search-input');
    await expect(searchInput).toBeVisible();
    await expect(searchInput).toHaveValue('alice');
  });

  test('empty search shows helpful state, no crash', async ({ page }) => {
    const response = await page.goto('/search?q=');
    expect(response?.status()).toBe(200);
    await page.waitForLoadState('networkidle');
    // Page should still render with helpful copy
    const h1 = page.locator('h1').first();
    await expect(h1).toBeVisible();
  });

  test('search input has accessible label', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    const searchInput = page.locator('#nav-search');
    // aria-label OR <label for=id>
    const ariaLabel = await searchInput.getAttribute('aria-label');
    expect(ariaLabel).toBeTruthy();
    expect(ariaLabel?.toLowerCase()).toMatch(/cari|search/);
  });
});