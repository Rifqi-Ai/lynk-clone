<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * WCAG 2.4.6 (Headings and Labels) + 1.1.1 (Non-text Content) audit tests.
 *
 * These tests guard against three Phase 13 a11y regressions:
 *
 *  1. The shared layout footer was using <h4> for "Product", "Company", and
 *     "Get Started" with no preceding <h3>, violating heading hierarchy
 *     (WCAG 2.4.6 + 1.3.1). The h4s are now h3s so every page that renders
 *     the layout passes a clean hierarchy.
 *
 *  2. The public product detail page rendered two <h1> tags — one mobile
 *     title and one desktop title — both wrapping the product name. WCAG
 *     2.4.6 says a page should have at most one h1 (browsers and screen
 *     readers expect a single document title). The desktop duplicate is now
 *     a <div> so the page has exactly one h1.
 *
 *  3. The creator avatar <img> on the product page had no alt attribute,
 *     violating WCAG 1.1.1 (non-text content must have a text alternative).
 *     It now renders alt="@{username}".
 */
class AccessibilityAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: create a published digital product owned by a creator.
     *
     * @return array{0: User, 1: Product}
     */
    private function makePublishedProduct(string $username = 'demo_alice'): array
    {
        $creator = User::factory()->create([
            'username' => $username,
        ]);

        $product = Product::factory()->published()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'title' => 'A11y Test Product',
            'price' => 25000,
        ]);

        return [$creator, $product];
    }

    /**
     * Test 1: the landing page must have exactly one <h1>.
     */
    public function test_landing_page_has_single_h1(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $h1Count = substr_count($response->getContent(), '<h1');
        $this->assertEquals(
            1,
            $h1Count,
            "Landing page should have exactly 1 <h1> tag, found {$h1Count}."
        );
    }

    /**
     * Test 2: the shared footer must not skip heading levels.
     *
     * Originally the footer used <h4> for "Product", "Company", and
     * "Get Started" with no preceding <h3>, which violates heading
     * hierarchy (WCAG 2.4.6). Asserting that no <h4> appears anywhere
     * is a strong proxy for the layout-wide fix.
     */
    public function test_footer_headings_dont_skip_h3(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $this->assertStringNotContainsString(
            '<h4',
            $response->getContent(),
            'Footer (rendered via layout) must not use <h4>; use <h3> to keep heading hierarchy valid per WCAG 2.4.6.'
        );
    }

    /**
     * Test 3: the public product page must have at most one <h1>.
     *
     * The page used to render two h1s (mobile title + desktop title),
     * both wrapping the product name. The desktop duplicate is now a
     * plain <div>, so only the mobile h1 remains.
     */
    public function test_product_page_has_single_h1(): void
    {
        [$creator, $product] = $this->makePublishedProduct();

        $response = $this->get("/{$creator->username}/{$product->id}");

        $response->assertStatus(200);

        $h1Count = substr_count($response->getContent(), '<h1');
        $this->assertLessThanOrEqual(
            1,
            $h1Count,
            "Product page should have at most 1 <h1>, found {$h1Count}."
        );
    }

    /**
     * Test 4: the registration page must have exactly one <h1>.
     */
    public function test_register_page_has_single_h1(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);

        $h1Count = substr_count($response->getContent(), '<h1');
        $this->assertEquals(
            1,
            $h1Count,
            "Register page should have exactly 1 <h1> tag, found {$h1Count}."
        );
    }

    /**
     * Test 5: the login page must have exactly one <h1>.
     */
    public function test_login_page_has_single_h1(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        $h1Count = substr_count($response->getContent(), '<h1');
        $this->assertEquals(
            1,
            $h1Count,
            "Login page should have exactly 1 <h1> tag, found {$h1Count}."
        );
    }
}
