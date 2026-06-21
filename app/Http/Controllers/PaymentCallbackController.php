<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderNotification;
use App\Models\EventTicket;
use App\Models\Order;
use App\Services\DuitkuService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PaymentCallbackController extends Controller
{
    /**
     * Duitku payment status codes (per Duitku POP API docs).
     * '00' = success, anything else = failure.
     */
    private const DUITKU_RESULT_SUCCESS = '00';

    /**
     * Order statuses that mean the callback has already been processed
     * and must be a no-op (idempotency).
     */
    private const TERMINAL_PAYMENT_STATUSES = ['paid', 'failed', 'expired', 'refunded'];

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
    public function callback(Request $request, DuitkuService $duitku): HttpResponse
    {
        $payload = $request->all();

        if ($rejection = $this->verifyCallbackSignature($duitku, $payload, $request)) {
            return $rejection;
        }

        $this->logCallbackReceived($payload);

        $merchantOrderId = $this->extractMerchantOrderId($payload);
        if (! $merchantOrderId) {
            return response('Missing order id', Response::HTTP_BAD_REQUEST);
        }

        $order = $this->lockAndProcessOrder($merchantOrderId, $payload);

        if (! $order) {
            return response('Order not found', Response::HTTP_NOT_FOUND);
        }

        $this->dispatchNotificationIfPaid($order);

        return response('OK', Response::HTTP_OK);
    }

    /**
     * Verify the Duitku signature. Returns a 400 response on failure, null on success.
     */
    private function verifyCallbackSignature(
        DuitkuService $duitku,
        array $payload,
        Request $request,
    ): ?HttpResponse {
        if ($duitku->verifyCallback($payload)) {
            return null;
        }

        Log::warning('Duitku callback signature mismatch', [
            'ip' => $request->ip(),
            'payload' => $payload,
        ]);

        return response('Invalid signature', Response::HTTP_BAD_REQUEST);
    }

    /**
     * Log the incoming callback for observability.
     */
    private function logCallbackReceived(array $payload): void
    {
        Log::info('Duitku callback received', [
            'merchant_order_id' => $payload['merchantOrderId'] ?? null,
            'result_code' => $payload['resultCode'] ?? null,
        ]);
    }

    /**
     * Extract the merchant order ID from the callback payload.
     * Duitku's official field is `merchantOrderId`; we fall back to `orderId` for
     * some payment channels that send the alternate key. The signature is bound
     * to the merchantOrderId value, so the fallback is safe.
     */
    private function extractMerchantOrderId(array $payload): ?string
    {
        return $payload['merchantOrderId'] ?? $payload['orderId'] ?? null;
    }

    /**
     * Lock the order row and process the payment result. Returns the order on
     * success/no-op, or null if the order doesn't exist.
     *
     * Concurrent callbacks serialize on the row lock — the second callback sees
     * the terminal status and returns without double-crediting.
     */
    private function lockAndProcessOrder(string $merchantOrderId, array $payload): ?Order
    {
        $resultCode = (string) ($payload['resultCode'] ?? '');

        return DB::transaction(function () use ($merchantOrderId, $payload, $resultCode) {
            $order = Order::lockForUpdate()->find($merchantOrderId);

            if (! $order) {
                Log::warning('Duitku callback: order not found', ['id' => $merchantOrderId]);

                return null;
            }

            if (in_array($order->payment_status, self::TERMINAL_PAYMENT_STATUSES, true)) {
                Log::info('Duitku callback: already processed', [
                    'order_id' => $order->id,
                    'status' => $order->payment_status,
                ]);

                return $order;
            }

            if ($resultCode === self::DUITKU_RESULT_SUCCESS) {
                $this->markOrderAsPaid($order, $payload);
            } else {
                $this->markOrderAsFailed($order, $payload, $resultCode);
            }

            return $order;
        });
    }

    /**
     * Mark the order as paid, credit the creator, and bump product sales.
     * MUST be called inside a DB transaction with the order row locked.
     */
    private function markOrderAsPaid(Order $order, array $payload): void
    {
        // Direct attribute assignment (bypasses mass-assignment protection).
        // This is the ONLY legitimate path to mark an order paid — verified above by signature check.
        $order->payment_status = 'paid';
        $order->payment_method = $payload['paymentCode'] ?? $payload['paymentMethod'] ?? null;
        $order->duitku_reference = $payload['reference'] ?? null;
        $order->duitku_response = $payload;
        $order->paid_at = now();
        $order->save();

        // Credit creator + bump sales in the same transaction (atomic).
        $order->creator->increment('balance', $order->creator_payout);
        $order->creator->increment('total_earnings', $order->creator_payout);
        $order->product->increment('sales_count', $order->quantity);

        $this->createEventTicketIfNeeded($order);

        Log::info('Order paid', ['order_id' => $order->id, 'amount' => $order->total]);
    }

    /**
     * Auto-generate an event ticket for event-type products. Uses firstOrCreate
     * so re-callbacks (or pre-existing tickets) don't violate the unique constraint.
     */
    private function createEventTicketIfNeeded(Order $order): void
    {
        if ($order->product->type !== 'event') {
            return;
        }

        EventTicket::firstOrCreate(
            ['order_id' => $order->id],
            [
                'product_id' => $order->product_id,
                'buyer_email' => $order->buyer_email,
                'attendee_name' => data_get($order->metadata, 'attendee_name')
                    ?? $this->defaultAttendeeNameFromEmail($order->buyer_email),
                'ticket_code' => EventTicket::generateCode(),
            ],
        );
    }

    /**
     * Extract a sensible attendee name from a buyer email.
     * Falls back to "Guest" if the email is malformed (no @).
     */
    private function defaultAttendeeNameFromEmail(string $email): string
    {
        $local = explode('@', $email, 2)[0] ?? '';

        return $local !== '' ? $local : 'Guest';
    }

    /**
     * Mark the order as failed. MUST be called inside a DB transaction.
     */
    private function markOrderAsFailed(Order $order, array $payload, string $resultCode): void
    {
        $order->payment_status = 'failed';
        $order->duitku_response = $payload;
        $order->save();

        Log::info('Order payment failed', [
            'order_id' => $order->id,
            'result_code' => $resultCode,
        ]);
    }

    /**
     * Dispatch the buyer notification job after the transaction commits.
     * Async so Duitku gets its 200 within ~50ms.
     */
    private function dispatchNotificationIfPaid(Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            return;
        }

        SendOrderNotification::dispatch($order->id);
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
