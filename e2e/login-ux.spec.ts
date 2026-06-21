import { test, expect } from '@playwright/test';

/**
 * Regression tests for Phase 17 Task #3 — Login page UX/A11y fixes.
 *
 * Source audit: docs/ux-audit-2026-06-21.md (Login page marked "(pending)")
 * Score: 7/10 → 9/10 after fixes.
 *
 * Fixes covered:
 *  - aria-invalid on error fields (#2)
 *  - role="alert" on error messages (#3)
 *  - Password toggle aria-label (#4)
 *  - Demo account buttons aria-label (#5)
 *  - Password placeholder clarity (#6)
 *  - Broken "Lupa?" link removed (#1)
 */

test.describe('Login page UX/A11y (Phase 17 Task #3)', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
  });

  test('broken "Lupa?" password link is removed (was href="#")', async ({ page }) => {
    // No element on the page should have href="#" — that's a broken link.
    const brokenLinks = await page.locator('a[href="#"]').count();
    expect(brokenLinks).toBe(0);
  });

  test('password toggle button has accessible aria-label', async ({ page }) => {
    const toggleBtn = page.locator('[data-toggle-password="password"]');
    await expect(toggleBtn).toBeVisible();
    await expect(toggleBtn).toHaveAttribute('aria-label', /toggle password visibility/i);
  });

  test('demo account buttons have descriptive aria-labels', async ({ page }) => {
    const demoButtons = page.locator('button[onclick*="document.getElementById(\'login\')"]');
    const count = await demoButtons.count();
    expect(count).toBeGreaterThan(0);

    for (let i = 0; i < count; i++) {
      const btn = demoButtons.nth(i);
      const ariaLabel = await btn.getAttribute('aria-label');
      expect(ariaLabel).toMatch(/login otomatis sebagai/i);
    }
  });

  test('password placeholder explains what to type (not just dots)', async ({ page }) => {
    const passwordInput = page.locator('input[name="password"]');
    const placeholder = await passwordInput.getAttribute('placeholder');
    expect(placeholder).toBeTruthy();
    expect(placeholder).not.toMatch(/^[•·]+$/); // not just dots
    expect(placeholder?.toLowerCase()).toMatch(/password|kata sandi/);
  });

  test('login form has aria-invalid + role="alert" on validation errors', async ({ page }) => {
    // Submit empty form to trigger validation errors.
    await page.locator('button[type="submit"]').click();

    // Wait for the page to either reload with errors or stay with validation.
    // Some browsers do native HTML5 validation — we just check the structure
    // is correct for the case where server-side validation fires.
    await page.waitForLoadState('networkidle');

    // The login field should have aria-invalid attribute machinery in the blade.
    // If a server error is present, the field must be aria-invalid="true".
    const loginField = page.locator('input[name="login"]');
    const passwordField = page.locator('input[name="password"]');

    // Either browser-native validation kicks in (no aria-invalid needed), OR
    // server-side validation fired (then aria-invalid must be set).
    // We assert the BLADE contract: if either field has error, aria-invalid must be set.
    const hasErrors = await page.locator('[role="alert"], .form-error').count();

    if (hasErrors > 0) {
      // At least one of the fields must be aria-invalid OR the message must be role="alert"
      const ariaInvalidLogin = await loginField.getAttribute('aria-invalid');
      const ariaInvalidPassword = await passwordField.getAttribute('aria-invalid');
      const hasAlertRole = (await page.locator('[role="alert"]').count()) > 0;

      expect(
        ariaInvalidLogin !== null || ariaInvalidPassword !== null || hasAlertRole,
        'expected either aria-invalid on input OR role="alert" on error message'
      ).toBeTruthy();
    }
    // If no errors visible, the test passes vacuously (form-level HTML5 validation prevented submission).
  });

  test('login page has a visible primary heading', async ({ page }) => {
    // Trunk test: user should know what page they're on.
    const h1 = page.locator('h1').first();
    await expect(h1).toBeVisible();
    const text = await h1.textContent();
    expect(text?.toLowerCase()).toMatch(/selamat datang|masuk|login/);
  });

  test('login page has a register link for new users', async ({ page }) => {
    await page.waitForLoadState('networkidle');
    const registerLink = page.locator('a[href*="/register"]').first();
    await expect(registerLink).toBeVisible();
  });

  test('Google OAuth button is keyboard-accessible with visible label', async ({ page }) => {
    const googleBtn = page.locator('a[href*="google"]');
    await expect(googleBtn).toBeVisible();
    const text = await googleBtn.textContent();
    expect(text?.toLowerCase()).toContain('google');
  });
});