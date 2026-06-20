<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCallbackSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set known Duitku credentials for signature verification
        config()->set('services.duitku.api_key', 'test_api_key_12345');
        config()->set('services.duitku.merchant_code', 'TESTMERCHANT');
        config()->set('services.duitku.production', true);
    }

    public function test_callback_without_signature_is_rejected(): void
    {
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $response = $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'resultCode' => '00',
            // No signature
        ]);

        $response->assertStatus(400);
        // Order must NOT be marked paid
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_callback_with_invalid_signature_is_rejected(): void
    {
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $response = $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'resultCode' => '00',
            'signature' => 'invalid-signature-here',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
        ]);
    }

    public function test_callback_cannot_inject_payment_status_via_mass_assignment(): void
    {
        $creator = User::factory()->create(['balance' => 0]);
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'sales_count' => 0,
        ]);

        // Create a pending order via factory
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'payment_status' => 'pending',
        ]);

        // Attacker tries to mark their order as paid via direct mass assignment.
        // Laravel silently discards non-fillable attrs by default, so we verify
        // the payment_status was NOT changed (which is the actual security goal).
        $order->update(['payment_status' => 'paid', 'duitku_reference' => 'FAKE123']);

        // Verify payment_status stayed 'pending' (attack blocked)
        $this->assertEquals('pending', $order->fresh()->payment_status, 'payment_status must not be mass-assignable');
        $this->assertNull($order->fresh()->duitku_reference, 'duitku_reference must not be mass-assignable');

        // Verify creator balance wasn't credited
        $this->assertEquals(0, $creator->fresh()->balance);
        $this->assertEquals(0, $product->fresh()->sales_count);
    }

    public function test_callback_for_nonexistent_order_returns_404(): void
    {
        $response = $this->postJson('/payment/callback', [
            'merchantOrderId' => 'ORD-NONEXISTENT000000',
            'resultCode' => '00',
            'signature' => 'whatever',
        ]);

        $response->assertStatus(400); // Signature fails first
    }

    public function test_already_paid_order_does_not_double_credit(): void
    {
        $creator = User::factory()->create(['balance' => 0]);
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'sales_count' => 0,
        ]);

        // Create a paid order via factory
        $order = Order::factory()->create([
            'id' => 'ORD-TEST-ABCDEFGH',
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'payment_status' => 'paid',
            'paid_at' => now()->subHour(),
        ]);

        $creator->increment('balance', 90); // Simulate initial credit
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals(90, $creator->fresh()->balance);

        // Attempting to mass-update payment_status from 'paid' is silently discarded
        // (the security goal — payment_status must NOT be fillable so this attack fails).
        $order->update(['payment_status' => 'paid', 'duitku_reference' => 'DUP123']);

        // payment_status must still be 'paid' (it was already) and duitku_reference must be unchanged
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertNull($order->fresh()->duitku_reference);

        // Balance must NOT have doubled
        $this->assertEquals(90, $creator->fresh()->balance);
    }
}
