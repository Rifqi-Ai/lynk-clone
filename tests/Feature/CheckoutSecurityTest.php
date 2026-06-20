<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $creator;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create([
            'transaction_fee_pct' => 10,
            'balance' => 0,
        ]);
        $this->product = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'digital',
            'title' => 'Test Product',
            'price' => 100000,
            'status' => 'published',
        ]);
    }

    public function test_donation_amount_min_1000(): void
    {
        $donation = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'donation',
            'title' => 'Donation',
            'price' => 0,
            'status' => 'published',
        ]);

        $response = $this->post("/{$this->creator->username}/{$donation->id}/checkout", [
            'payer_email' => 'donor@example.com',
            'amount' => 500, // below min
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_donation_amount_max_100m(): void
    {
        $donation = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'donation',
            'title' => 'Donation',
            'price' => 0,
            'status' => 'published',
        ]);

        $response = $this->post("/{$this->creator->username}/{$donation->id}/checkout", [
            'payer_email' => 'donor@example.com',
            'amount' => 999999999, // above max 100M
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_donation_with_preset_only_accepts_presets(): void
    {
        $donation = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'donation',
            'title' => 'Donation',
            'price' => 0,
            'status' => 'published',
            'metadata' => [
                'preset_amounts' => [10000, 25000, 50000],
                'allow_custom' => false,
            ],
        ]);

        // Custom amount when not allowed — should fail
        $response = $this->post("/{$this->creator->username}/{$donation->id}/checkout", [
            'payer_email' => 'donor@example.com',
            'amount' => 99999,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_event_quantity_capped_by_capacity(): void
    {
        $event = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'event',
            'title' => 'Concert',
            'price' => 50000,
            'status' => 'published',
            'metadata' => ['capacity' => 2],
        ]);

        // Try to buy 5 tickets when only 2 available
        $response = $this->post("/{$this->creator->username}/{$event->id}/checkout", [
            'payer_email' => 'buyer@example.com',
            'quantity' => 5,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_payer_email_is_required(): void
    {
        $response = $this->post("/{$this->creator->username}/{$this->product->id}/checkout", [
            // payer_email missing
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('payer_email');
    }

    public function test_checkout_creates_order_with_correct_fee_clamped(): void
    {
        // Creator with insane fee_pct (compromised scenario)
        $badCreator = User::factory()->create(['transaction_fee_pct' => 9999]);
        $product = Product::factory()->create([
            'user_id' => $badCreator->id,
            'type' => 'digital',
            'title' => 'Test',
            'price' => 100000,
            'status' => 'published',
        ]);

        $response = $this->post("/{$badCreator->username}/{$product->id}/checkout", [
            'payer_email' => 'buyer@example.com',
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $order = Order::where('product_id', $product->id)->latest()->first();
        $this->assertNotNull($order);
        // Fee must be clamped to max 50%
        $this->assertLessThanOrEqual(50, (float) $order->fee_pct);
        $this->assertGreaterThanOrEqual(0, (float) $order->fee_pct);
    }

    public function test_unpublished_product_cannot_be_checked_out(): void
    {
        $draft = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'digital',
            'title' => 'Draft',
            'price' => 100,
            'status' => 'draft',
        ]);

        $response = $this->post("/{$this->creator->username}/{$draft->id}/checkout", [
            'payer_email' => 'buyer@example.com',
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
    }

    public function test_nonexistent_product_returns_404(): void
    {
        $response = $this->post("/{$this->creator->username}/nonexistent00/checkout", [
            'payer_email' => 'buyer@example.com',
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
    }
}
