import { test, expect } from '@playwright/test';

/**
 * Regression tests for Phase 17 Task #5 — Help link on landing (Nielsen #10).
 *
 * Source audit: docs/ux-audit-2026-06-21.md — Trunk Test pass: 5/6
 * Missing: "Where can I get help?" (Heuristic #10 — Help and Documentation)
 *
 * Fix:
 *  - Contextual "Punya pertanyaan?" link below hero CTAs
 *  - Dedicated "Bantuan" footer column (was buried in Product section)
 */

test.describe('Landing page Help link (Phase 17 Task #5)', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');
  });

  test('landing page has a contextual help link near the hero CTA', async ({ page }) => {
    // The link should be near "Mulai Gratis" CTA, not just buried in nav.
    // Either "Bantuan", "Punya pertanyaan?", "FAQ", or "Help" — anything
    // that signals "if you have questions, click here".
    const heroSection = page.locator('section').first();
    const helpLink = heroSection.locator('a[href*="faq"], a:has-text("Bantuan"), a:has-text("pertanyaan"), a:has-text("Help")');
    await expect(helpLink.first()).toBeVisible();
  });

  test('landing page footer has a dedicated Help/Bantuan section', async ({ page }) => {
    const footer = page.locator('footer');
    await expect(footer).toBeVisible();

    // Look for a Help heading or section in the footer.
    const helpSection = footer.locator('h3, h2').filter({ hasText: /bantuan|help|dukungan/i });
    await expect(helpSection.first()).toBeVisible();
  });

  test('help links from landing navigate to /faq (not 404)', async ({ page }) => {
    // Click the contextual help link and verify it lands on FAQ page.
    const heroHelpLink = page.locator('section').first().locator('a[href*="faq"]').first();
    await expect(heroHelpLink).toBeVisible();
    await heroHelpLink.click();
    await page.waitForURL(/\/faq/, { timeout: 10_000 });
    await expect(page).toHaveURL(/\/faq$/);
    // FAQ page should have a heading.
    const h1 = page.locator('h1').first();
    await expect(h1).toBeVisible();
  });

  test('FAQ page itself is accessible and shows question content', async ({ page }) => {
    await page.goto('/faq');
    await expect(page).toHaveURL(/\/faq$/);
    // FAQ should have a heading.
    const h1 = page.locator('h1').first();
    await expect(h1).toBeVisible();
    // FAQ page inherits the app layout nav which has the Linka logo → home link.
    const backLink = page.locator('header a').first();
    await expect(backLink).toBeVisible();
  });
});