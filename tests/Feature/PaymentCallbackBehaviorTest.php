<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotification;
use App\Models\EventTicket;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Characterization + behavior tests for PaymentCallbackController.
 *
 * The existing PaymentCallbackSecurityTest only covers the negative
 * (signature rejection, mass-assignment blocking). This suite covers
 * the POSITIVE paths the security test never reaches:
 *
 *   - Happy path: order marked paid, creator balance credited,
 *     product sales_count incremented
 *   - Failure path: order marked failed, NO balance credit
 *   - Event ticket auto-generation (event products only)
 *   - Idempotency: re-callback for already-paid order is a true no-op
 *   - Notification job dispatch (only on paid)
 *   - Missing-order-id rejection
 */
class PaymentCallbackBehaviorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.duitku.api_key', 'test_api_key_12345');
        config()->set('services.duitku.merchant_code', 'TESTMERCHANT');
        config()->set('services.duitku.production', true);
    }

    /**
     * Build a valid Duitku callback signature.
     * Duitku signature = sha256(merchantCode + amount + merchantOrderId + apiKey)
     */
    private function validSignature(string $merchantOrderId, string $amount): string
    {
        return hash('sha256', 'TESTMERCHANT'.$amount.$merchantOrderId.'test_api_key_12345');
    }

    public function test_valid_callback_marks_order_as_paid(): void
    {
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '00',
            'reference' => 'DUI12345678',
            'paymentCode' => 'VC',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('VC', $order->payment_method);
        $this->assertEquals('DUI12345678', $order->duitku_reference);
        $this->assertNotNull($order->paid_at);
    }

    public function test_valid_callback_credits_creator_balance(): void
    {
        $creator = User::factory()->create(['balance' => 0, 'total_earnings' => 0]);
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'sales_count' => 0,
        ]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'creator_payout' => 50000,
            'payment_status' => 'pending',
        ]);

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '00',
            'reference' => 'DUI12345678',
            'paymentCode' => 'VC',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        $this->assertEquals(50000, $creator->fresh()->balance);
        $this->assertEquals(50000, $creator->fresh()->total_earnings);
    }

    public function test_valid_callback_increments_product_sales_count(): void
    {
        $product = Product::factory()->create(['type' => 'digital', 'sales_count' => 5]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'payment_status' => 'pending',
        ]);

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '00',
            'reference' => 'DUI12345678',
            'paymentCode' => 'VC',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        $this->assertEquals(8, $product->fresh()->sales_count, 'sales_count should grow by order quantity');
    }

    public function test_valid_callback_creates_event_ticket_for_event_product(): void
    {
        $order = Order::factory()->create(['payment_status' => 'pending']);
        $order->product->update(['type' => 'event']);

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '00',
            'reference' => 'DUI12345678',
            'paymentCode' => 'VC',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        $this->assertDatabaseHas('event_tickets', [
            'order_id' => $order->id,
            'product_id' => $order->product_id,
            'buyer_email' => $order->buyer_email,
        ]);

        $ticket = EventTicket::where('order_id', $order->id)->first();
        $this->assertNotEmpty($ticket->ticket_code);
    }

    public function test_valid_callback_does_not_create_event_ticket_for_digital(): void
    {
        $order = Order::factory()->create(['payment_status' => 'pending']);
        // Default factory type is digital.

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '00',
            'reference' => 'DUI12345678',
            'paymentCode' => 'VC',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        $this->assertDatabaseMissing('event_tickets', [
            'order_id' => $order->id,
        ]);
    }

    public function test_failed_result_code_marks_order_as_failed(): void
    {
        $creator = User::factory()->create(['balance' => 0]);
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'sales_count' => 0,
        ]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'payment_status' => 'pending',
        ]);

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '01', // 01 = failed in Duitku convention
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);
        $this->assertEquals(0, $creator->fresh()->balance, 'failed orders must NOT credit creator');
        $this->assertEquals(0, $product->fresh()->sales_count, 'failed orders must NOT increment sales');
    }

    public function test_missing_merchant_order_id_returns_400(): void
    {
        $this->postJson('/payment/callback', [
            'amount' => '10000',
            'resultCode' => '00',
            'signature' => 'whatever',
        ])->assertStatus(400);
    }

    public function test_already_paid_callback_is_idempotent(): void
    {
        $creator = User::factory()->create(['balance' => 100, 'total_earnings' => 100]);
        $product = Product::factory()->create(['type' => 'digital', 'sales_count' => 1]);
        $order = Order::factory()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'creator_payout' => 50000,
            'payment_status' => 'paid',
            'paid_at' => now()->subHour(),
        ]);

        // Replay the callback.
        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '00',
            'reference' => 'DUI99999999',
            'paymentCode' => 'VC',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        // Balance must NOT double.
        $this->assertEquals(100, $creator->fresh()->balance);
        $this->assertEquals(100, $creator->fresh()->total_earnings);
        $this->assertEquals(1, $product->fresh()->sales_count, 'sales_count must NOT double');
    }

    public function test_notification_job_is_dispatched_on_paid(): void
    {
        Queue::fake();
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '00',
            'reference' => 'DUI12345678',
            'paymentCode' => 'VC',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        Queue::assertPushed(SendOrderNotification::class, fn ($job) => $job->orderId === $order->id);
    }

    public function test_notification_job_is_no_t_dispatched_on_failed(): void
    {
        Queue::fake();
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $this->postJson('/payment/callback', [
            'merchantOrderId' => $order->id,
            'amount' => (string) $order->total,
            'resultCode' => '01',
            'signature' => $this->validSignature($order->id, (string) $order->total),
        ])->assertStatus(200);

        Queue::assertNotPushed(SendOrderNotification::class);
    }
}
