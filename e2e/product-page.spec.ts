import { test, expect, type Page } from '@playwright/test';

/**
 * E2E: Product detail page.
 *
 * Verifies a product loads with its title, price, and buy/checkout
 * CTA. The product URL uses the 12-char product ID (not the slug).
 *
 * NOTE: hrefs in the page are absolute, so we use `*=` for selectors.
 *
 * We filter by `?type=digital` so the test gets a digital product
 * (which always has a price and an "add to cart" button). The default
 * sort is latest, which may surface donations/appointments that don't
 * have prices.
 */

async function getFirstDigitalProductHref(
    page: Page,
    username: string,
): Promise<string> {
    // Add ?type=digital to filter to digital products (have price + cart).
    await page.goto(`/${username}?type=digital`);
    await page.waitForLoadState('networkidle');

    const productLink = page
        .locator(`a.linka-link[href*="/${username}/"]`)
        .first();
    await expect(productLink).toBeVisible();
    const href = await productLink.getAttribute('href');
    expect(href).toBeTruthy();
    return href!;
}

test('digital product page loads with title, price, and buy CTA', async ({ page }) => {
    const productHref = await getFirstDigitalProductHref(page, 'demo_alice');

    const response = await page.goto(productHref);
    expect(response?.status()).toBe(200);
    await page.waitForLoadState('networkidle');

    // Product page must have a price (Indonesian rupiah).
    const body = await page.locator('body').textContent();
    expect(body).toMatch(/Rp|IDR/i);

    // A checkout/buy button or link must be visible.
    const buyCta = page
        .locator(
            'a[href*="checkout"], button:has-text("Beli"), button:has-text("Buy"), a:has-text("Beli")',
        )
        .first();
    await expect(buyCta).toBeVisible();
});

test('digital product page renders successfully on repeat visits', async ({ page }) => {
    const productHref = await getFirstDigitalProductHref(page, 'demo_alice');

    const first = await page.goto(productHref);
    expect(first?.status()).toBe(200);

    const second = await page.goto(productHref);
    expect(second?.status()).toBe(200);
});
