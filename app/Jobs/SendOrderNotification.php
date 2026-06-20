<?php

namespace App\Jobs;

use App\Mail\CreatorSaleNotification;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send order-related notifications (email + WhatsApp) asynchronously.
 *
 * Why queued:
 * - Payment callback is a server-to-server request from Duitku. We must respond
 *   fast (<5s) or Duitku retries — slow email/WhatsApp APIs would cause retries.
 * - Queuing decouples the response from external service latency.
 *
 * Retry strategy:
 * - 3 attempts with exponential backoff (1min → 5min → 15min).
 * - Failures are logged but never propagate to the user.
 */
class SendOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Max attempts before giving up. */
    public int $tries = 3;

    /** Backoff in seconds between attempts. */
    public array $backoff = [60, 300, 900];

    /** Timeout per attempt in seconds. */
    public int $timeout = 30;

    public function __construct(public string $orderId) {}

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsapp): void
    {
        // Re-load order from DB (avoids stale snapshot if it was mutated between dispatch and run)
        $order = Order::with(['product', 'creator', 'buyer'])->find($this->orderId);
        if (! $order) {
            Log::warning('SendOrderNotification: order not found', ['order_id' => $this->orderId]);

            return;
        }

        // 1) Buyer confirmation email
        try {
            Mail::to($order->buyer_email)
                ->send(new OrderConfirmation($order));
        } catch (\Throwable $e) {
            Log::warning('Order confirmation email failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            // Don't rethrow — continue to send creator notification
        }

        // 2) Creator sale notification email
        try {
            Mail::to($order->creator->email)
                ->send(new CreatorSaleNotification($order));
        } catch (\Throwable $e) {
            Log::warning('Creator sale notification email failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 3) WhatsApp to creator (if phone + opt-in)
        if ($order->creator->phone ?? null) {
            try {
                $whatsapp->sendCreatorSaleNotification($order);
            } catch (\Throwable $e) {
                Log::warning('WhatsApp creator notification failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Called when job fails permanently.
     */
    public function failed(\Throwable $e): void
    {
        Log::error('SendOrderNotification permanently failed', [
            'order_id' => $this->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
