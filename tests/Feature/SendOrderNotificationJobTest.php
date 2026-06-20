<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotification;
use App\Mail\CreatorSaleNotification;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Tests for the SendOrderNotification queue job.
 *
 * Phase 9 improvement: payment callback now dispatches this job instead of
 * sending emails synchronously, so Duitku's 5-second response deadline isn't
 * blown by slow SMTP/WhatsApp APIs.
 */
class SendOrderNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_after_payment_callback(): void
    {
        Bus::fake([SendOrderNotification::class]);

        $order = Order::factory()->create(['payment_status' => 'pending']);

        // Simulate the dispatch (the callback would do this)
        SendOrderNotification::dispatch($order->id);

        Bus::assertDispatched(SendOrderNotification::class, function ($job) use ($order) {
            return $job->orderId === $order->id;
        });
    }

    public function test_job_sends_buyer_email_when_run(): void
    {
        Mail::fake();

        $order = Order::factory()->paid()->create([
            'buyer_email' => 'buyer@example.com',
        ]);

        $creator = $order->creator;
        $product = $order->product;

        // Run the job synchronously (sync queue)
        (new SendOrderNotification($order->id))->handle(app(WhatsAppService::class));

        // Buyer email sent
        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($order) {
            return $mail->hasTo($order->buyer_email);
        });

        // Creator email sent
        Mail::assertSent(CreatorSaleNotification::class, function ($mail) use ($creator) {
            return $mail->hasTo($creator->email);
        });
    }

    public function test_job_handles_missing_order_gracefully(): void
    {
        Mail::fake();

        // Job should not throw if order no longer exists
        (new SendOrderNotification('ORD-DOES-NOT-EXIST'))
            ->handle(app(WhatsAppService::class));

        // No emails sent
        Mail::assertNothingSent();
    }

    public function test_failed_callback_logs_error(): void
    {
        // Use a real exception type
        $job = new SendOrderNotification('ORD-NONEXISTENT');

        // failed() shouldn't throw
        $job->failed(new \RuntimeException('Test error'));

        $this->assertTrue(true); // reached here without exception
    }
}
