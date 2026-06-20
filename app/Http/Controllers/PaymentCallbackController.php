<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\DuitkuService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    /**
     * Duitku callback (server-to-server notification).
     * Duitku posts transaction details here after payment.
     */
    public function callback(Request $request, DuitkuService $duitku, WhatsAppService $whatsapp)
    {
        $payload = $request->all();
        Log::info('Duitku callback received', $payload);

        // Verify signature (production only)
        if (config('services.duitku.production') && !$duitku->verifyCallback($payload)) {
            Log::warning('Duitku callback signature mismatch', $payload);
            return response('Invalid signature', 400);
        }

        $merchantOrderId = $payload['merchantOrderId'] ?? $payload['order_id'] ?? null;
        $resultCode = $payload['resultCode'] ?? $payload['status_code'] ?? null;

        if (!$merchantOrderId) {
            return response('Missing order id', 400);
        }

        $order = Order::find($merchantOrderId);
        if (!$order) {
            Log::warning('Duitku callback: order not found', ['id' => $merchantOrderId]);
            return response('Order not found', 404);
        }

        // 00 = success in Duitku convention
        if ($resultCode === '00') {
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $payload['paymentCode'] ?? $payload['payment_method'] ?? null,
                'duitku_reference' => $payload['reference'] ?? null,
                'duitku_response' => $payload,
                'paid_at' => now(),
            ]);

            // Increment creator balance + product sales_count
            $order->creator->increment('balance', $order->creator_payout);
            $order->creator->increment('total_earnings', $order->creator_payout);
            $order->product->increment('sales_count', $order->quantity);

            // Auto-generate event ticket for event products
            if ($order->product->type === 'event') {
                \App\Models\EventTicket::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'product_id' => $order->product_id,
                        'buyer_email' => $order->buyer_email,
                        'attendee_name' => data_get($order->metadata, 'attendee_name') ?? explode('@', $order->buyer_email)[0],
                        'ticket_code' => \App\Models\EventTicket::generateCode(),
                    ],
                );
            }

            // Send order confirmation email to buyer
            try {
                \Illuminate\Support\Facades\Mail::to($order->buyer_email)
                    ->send(new \App\Mail\OrderConfirmation($order));
                Log::info('Order confirmation email sent', ['order_id' => $order->id, 'to' => $order->buyer_email]);
            } catch (\Throwable $e) {
                Log::warning('Failed to send order confirmation email', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            // Send creator sale notification via email
            try {
                \Illuminate\Support\Facades\Mail::to($order->creator->email)
                    ->send(new \App\Mail\CreatorSaleNotification($order));
                Log::info('Creator sale notification sent', ['order_id' => $order->id, 'to' => $order->creator->email]);
            } catch (\Throwable $e) {
                Log::warning('Failed to send creator sale notification', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            // Send WhatsApp notification to creator (if phone available)
            try {
                if ($order->creator->phone ?? null) {
                    $whatsapp->sendCreatorSaleNotification($order);
                }
            } catch (\Throwable $e) {
                Log::warning('WhatsApp creator notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            Log::info('Order paid', ['order_id' => $order->id, 'amount' => $order->total]);
        } else {
            $order->update([
                'payment_status' => 'failed',
                'duitku_response' => $payload,
            ]);
            Log::info('Order payment failed', ['order_id' => $order->id, 'result_code' => $resultCode]);
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