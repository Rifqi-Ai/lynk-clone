import { test, expect } from '@playwright/test';

/**
 * E2E: Register form accessibility (WCAG 2.1 compliance).
 *
 * Verifies that error states on the register form use proper ARIA
 * attributes per WCAG 4.1.2 (Name, Role, Value) and 1.3.1 (Info and
 * Relationships). Screen readers must be told when a field is invalid.
 *
 * Fixes verified:
 *   - aria-invalid="true" on input when validation fails
 *   - aria-describedby linking to error message
 *   - role="alert" on error message so it's announced
 */
test('register form with duplicate email shows aria-invalid + accessible error', async ({ page }) => {
    // demo_alice is seeded. Trying to register with her email should fail.
    await page.goto('/register');

    // Fill with conflicting data
    const unique = Date.now();
    await page.locator('input[name="name"]').fill('Test User');
    await page.locator('input[name="username"]').fill(`e2e${unique}`);
    await page.locator('input[name="email"]').fill('alice@demo.linka.id'); // already exists
    await page.locator('input[name="password"]').fill('password123');
    await page.locator('input[name="password_confirmation"]').fill('password123');

    const terms = page.locator('input[name="terms"]');
    if (await terms.isVisible().catch(() => false)) {
        await terms.check();
    }

    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // The email field should now have aria-invalid="true"
    const emailInput = page.locator('input[name="email"]');
    await expect(emailInput).toHaveAttribute('aria-invalid', 'true');
    await expect(emailInput).toHaveAttribute('aria-describedby', /email-error/);

    // The error message must be present and have role="alert"
    const errorMessage = page.locator('#email-error');
    await expect(errorMessage).toBeVisible();
    await expect(errorMessage).toHaveAttribute('role', 'alert');
});

test('register form: invalid username shows accessible error', async ({ page }) => {
    await page.goto('/register');

    const unique = Date.now();
    await page.locator('input[name="name"]').fill('Test User');
    // demo_alice is already seeded → server-side validation will reject this username
    await page.locator('input[name="username"]').fill('demo_alice');
    await page.locator('input[name="email"]').fill(`e2e${unique}@lynk.test`);
    await page.locator('input[name="password"]').fill('password123');
    await page.locator('input[name="password_confirmation"]').fill('password123');

    const terms = page.locator('input[name="terms"]');
    if (await terms.isVisible().catch(() => false)) {
        await terms.check();
    }

    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');

    // Username field must be marked invalid
    const usernameInput = page.locator('input[name="username"]');
    await expect(usernameInput).toHaveAttribute('aria-invalid', 'true');
    await expect(usernameInput).toHaveAttribute('aria-describedby', /username-error/);
});

test('register form: valid form has no aria-invalid on any field', async ({ page }) => {
    // First load: no errors
    await page.goto('/register');

    // No field should have aria-invalid yet
    const invalidFields = page.locator('input[aria-invalid="true"]');
    await expect(invalidFields).toHaveCount(0);
});
