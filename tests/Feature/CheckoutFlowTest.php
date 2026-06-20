<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end integration tests for the public checkout flow.
 *
 * Verifies that the HTTP routes render correctly through the full stack:
 * route → controller → view → model accessors. Specifically guards against
 * regression of the Phase A1 fix (Product::checkout_url accessor) by exercising
 * the actual HTML output rather than just the model layer.
 */
class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: create a published digital product owned by a creator with a
     * known username. Returns the [creator, product] pair so each test can
     * build its URL without re-querying.
     */
    private function makeDigitalProduct(string $username = 'demo_alice'): array
    {
        $creator = User::factory()->create([
            'username' => $username,
            'transaction_fee_pct' => 10,
        ]);

        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'title' => 'Test Ebook Checkout Flow',
            'price' => 50000,
            'status' => 'published',
        ]);

        return [$creator, $product];
    }

    /**
     * Test 1 — GET /{username}/{productId} returns 200 for a published product.
     */
    public function test_product_detail_page_returns_200(): void
    {
        [$creator, $product] = $this->makeDigitalProduct();

        $response = $this->get("/{$creator->username}/{$product->id}");

        $response->assertOk();
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test 2 — Product detail page renders the real checkout URL in the CTA
     * button, and does NOT contain the broken `href=""` pattern that the
     * missing `checkout_url` accessor used to produce.
     */
    public function test_product_detail_page_has_checkout_link_with_real_href(): void
    {
        [$creator, $product] = $this->makeDigitalProduct();

        $response = $this->get("/{$creator->username}/{$product->id}");

        $response->assertOk();

        $expectedHref = 'href="http://localhost/'.$creator->username.'/'.$product->id.'/checkout"';

        // The buy-button href must be the fully-qualified checkout URL.
        $response->assertSee($expectedHref, escape: false);

        // Guard against the original bug: an empty href on a btn-cta class.
        $this->assertStringNotContainsString(
            'href="" class="btn-cta',
            $response->getContent(),
            'Product CTA must not have an empty href — checkout_url accessor regression.'
        );
    }

    /**
     * Test 3 — GET /{username}/{productId}/checkout returns 200 and renders the
     * price somewhere in the response (raw or formatted).
     */
    public function test_checkout_page_returns_200_and_shows_price(): void
    {
        [$creator, $product] = $this->makeDigitalProduct();

        $response = $this->get("/{$creator->username}/{$product->id}/checkout");

        $response->assertOk();

        // Price "50000" must be visible in the response — it appears in the
        // hidden amount input value. We also verify the formatted display
        // "Rp 50.000" (Indonesian thousands separator) is present.
        $response->assertSee('50000', escape: false);
        $response->assertSee('Rp 50.000', escape: false);
    }

    /**
     * Test 4 — Donation product renders the "Dukung Creator" support CTA.
     */
    public function test_donation_product_renders_support_button(): void
    {
        $creator = User::factory()->create([
            'username' => 'donor_creator',
        ]);

        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'donation',
            'title' => 'Buy me a coffee',
            'price' => 10000,
            'status' => 'published',
        ]);

        $response = $this->get("/{$creator->username}/{$product->id}");

        $response->assertOk();
        // Donation CTAs use "Dukung Creator" in the product sidebar.
        $response->assertSee('Dukung Creator', escape: false);

        // The checkout link must still be fully-qualified for donations.
        $expectedHref = 'href="http://localhost/'.$creator->username.'/'.$product->id.'/checkout"';
        $response->assertSee($expectedHref, escape: false);
    }

    /**
     * Test 5 — Appointment product renders the "Book Sekarang" CTA.
     */
    public function test_appointment_product_renders_book_button(): void
    {
        $creator = User::factory()->create([
            'username' => 'consultant_bob',
        ]);

        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'appointment',
            'title' => '30-min Consultation',
            'price' => 150000,
            'status' => 'published',
        ]);

        $response = $this->get("/{$creator->username}/{$product->id}");

        $response->assertOk();
        // Appointment CTAs render "Book Sekarang" in the product sidebar.
        $response->assertSee('Book Sekarang', escape: false);

        // And the link still points at the checkout route.
        $expectedHref = 'href="http://localhost/'.$creator->username.'/'.$product->id.'/checkout"';
        $response->assertSee($expectedHref, escape: false);
    }
}
