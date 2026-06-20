<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderNotification;
use App\Models\EventTicket;
use App\Models\Order;
use App\Services\DuitkuService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    /**
     * Duitku callback (server-to-server notification).
     * Duitku posts transaction details here after payment.
     *
     * SECURITY:
     * - Signature verification is ALWAYS performed (production AND dev) to prevent
     *   replay/exploit attacks even in non-production mode where the secret is well-known.
     * - Idempotent: re-callbacks for an already-paid order are no-ops. We use a
     *   DB transaction with `lockForUpdate()` on the order row to prevent concurrent
     *   callbacks from double-crediting creator balance.
     * - PERF: All notifications (email/WhatsApp) are dispatched to a queue job
     *   so the callback responds to Duitku within ~50ms (not 3-5s for SMTP).
     */
    public function callback(Request $request, DuitkuService $duitku, WhatsAppService $whatsapp)
    {
        $payload = $request->all();

        // ALWAYS verify signature (the dev "secret" is well-known but still enforces the
        // contract — if integration breaks in dev we want to know immediately).
        if (! $duitku->verifyCallback($payload)) {
            Log::warning('Duitku callback signature mismatch', ['ip' => $request->ip(), 'payload' => $payload]);

            return response('Invalid signature', 400);
        }

        Log::info('Duitku callback received', [
            'merchant_order_id' => $payload['merchantOrderId'] ?? null,
            'result_code' => $payload['resultCode'] ?? null,
        ]);

        $merchantOrderId = $payload['merchantOrderId'] ?? $payload['order_id'] ?? null;
        $resultCode = $payload['resultCode'] ?? $payload['status_code'] ?? null;

        if (! $merchantOrderId) {
            return response('Missing order id', 400);
        }

        // Lock the order row for the duration of this transaction so concurrent callbacks
        // serialize (the second callback will see payment_status=paid and skip work).
        $order = DB::transaction(function () use ($merchantOrderId, $payload, $resultCode) {
            $order = Order::lockForUpdate()->find($merchantOrderId);
            if (! $order) {
                Log::warning('Duitku callback: order not found', ['id' => $merchantOrderId]);

                return null;
            }

            // Idempotency: if already processed to a terminal state, no-op
            if (in_array($order->payment_status, ['paid', 'failed', 'expired'], true)) {
                Log::info('Duitku callback: already processed', [
                    'order_id' => $order->id,
                    'status' => $order->payment_status,
                ]);

                return $order;
            }

            // 00 = success in Duitku convention
            if ($resultCode === '00') {
                // Direct attribute assignment (bypasses mass-assignment protection).
                // This is the ONLY legitimate path to mark an order paid — verified above by signature check.
                $order->payment_status = 'paid';
                $order->payment_method = $payload['paymentCode'] ?? $payload['payment_method'] ?? null;
                $order->duitku_reference = $payload['reference'] ?? null;
                $order->duitku_response = $payload;
                $order->paid_at = now();
                $order->save();

                // Increment creator balance + product sales_count (atomic — same TX)
                $order->creator->increment('balance', $order->creator_payout);
                $order->creator->increment('total_earnings', $order->creator_payout);
                $order->product->increment('sales_count', $order->quantity);

                // Auto-generate event ticket for event products
                if ($order->product->type === 'event') {
                    EventTicket::firstOrCreate(
                        ['order_id' => $order->id],
                        [
                            'product_id' => $order->product_id,
                            'buyer_email' => $order->buyer_email,
                            'attendee_name' => data_get($order->metadata, 'attendee_name') ?? explode('@', $order->buyer_email)[0],
                            'ticket_code' => EventTicket::generateCode(),
                        ],
                    );
                }

                Log::info('Order paid', ['order_id' => $order->id, 'amount' => $order->total]);
            } else {
                $order->payment_status = 'failed';
                $order->duitku_response = $payload;
                $order->save();
                Log::info('Order payment failed', ['order_id' => $order->id, 'result_code' => $resultCode]);
            }

            return $order;
        });

        if (! $order) {
            return response('Order not found', 404);
        }

        // Dispatch notification job ASYNCHRONOUSLY (after DB transaction commits).
        // This ensures the callback returns to Duitku quickly while emails/WhatsApp
        // are sent in the background.
        if ($order->payment_status === 'paid') {
            SendOrderNotification::dispatch($order->id);
        }

        return response('OK', 200);
    }

    /**
     * Success redirect from Duitku payment page.
     */
    public function success(Order $order)
    {
        return view('payment.success', compact('order'));
    }

    /**
     * Failed/expired redirect.
     */
    public function failed(Order $order)
    {
        return view('payment.failed', compact('order'));
    }
}
