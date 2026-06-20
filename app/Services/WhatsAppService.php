<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp notification gateway integration.
 *
 * Supports two providers:
 * - Wablas (https://wablas.com) — popular in Indonesia
 * - Fonnte (https://fonnte.com) — simpler API
 *
 * MVP: Logs to file (dev mode). Switch to production by setting:
 *   WHATSAPP_PROVIDER=wablas
 *   WHATSAPP_API_KEY=your_key
 *   WHATSAPP_SENDER=6281234567890
 */
class WhatsAppService
{
    protected string $provider;
    protected string $apiKey;
    protected string $sender;
    protected bool $production;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', 'log');
        $this->apiKey = config('services.whatsapp.api_key', '');
        $this->sender = config('services.whatsapp.sender', '');
        $this->production = config('services.whatsapp.production', false);
    }

    /**
     * Send WhatsApp message.
     *
     * @param string $phone E.164 format (e.g., "6281234567890")
     * @param string $message Message body (supports line breaks)
     * @return bool Success
     */
    public function send(string $phone, string $message): bool
    {
        // Normalize phone: strip non-digits, ensure starts with 62
        $phone = $this->normalizePhone($phone);
        if (!$phone) {
            Log::warning('WhatsApp: invalid phone', ['phone' => $phone]);
            return false;
        }

        // Log mode (dev) — just write to log
        if (!$this->production || $this->provider === 'log') {
            Log::info('WhatsApp message (dev log mode)', [
                'to' => $phone,
                'message' => $message,
            ]);
            return true;
        }

        try {
            return match ($this->provider) {
                'wablas' => $this->sendWablas($phone, $message),
                'fonnte' => $this->sendFonnte($phone, $message),
                default => $this->logOnly($phone, $message),
            };
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send order confirmation to buyer via WhatsApp.
     */
    public function sendOrderConfirmation(\App\Models\Order $order): bool
    {
        $creator = $order->creator;
        $product = $order->product;

        $message = "✅ *Order Confirmed!*\n\n"
            . "Halo! Pembayaran Anda untuk *{$product->title}* sudah kami terima.\n\n"
            . "📦 Order ID: `{$order->id}`\n"
            . "💰 Total: Rp " . number_format($order->total, 0, ',', '.') . "\n"
            . "🏪 Creator: @{$creator->username}\n\n";

        // Type-specific
        switch ($product->type) {
            case 'event':
                $ticketUrl = route('event.ticket', [$creator->username, $product->id, $order->id]);
                $message .= "🎟️ Tiket event: {$ticketUrl}\n";
                break;
            case 'course':
                $message .= "📚 Course link: " . route('course.show', [$creator->username, $product->id]) . "\n";
                break;
            case 'appointment':
                $date = data_get($order->metadata, 'appointment_date');
                $time = data_get($order->metadata, 'appointment_time');
                $message .= "📅 Janji temu: {$date} jam {$time}\n";
                break;
            case 'physical':
                $message .= "📦 Pesanan fisik Anda akan diproses dalam 1-2 hari.\n";
                break;
        }

        $message .= "\nTerima kasih! 🙏";

        return $this->send($order->buyer_email, $message); // buyer_email fallback (in real app use phone)
    }

    /**
     * Send sale notification to creator via WhatsApp.
     */
    public function sendCreatorSaleNotification(\App\Models\Order $order): bool
    {
        $creator = $order->creator;
        $product = $order->product;

        $message = "💰 *New Sale!*\n\n"
            . "Anda mendapat penjualan baru:\n\n"
            . "📦 {$product->title}\n"
            . "💵 Earnings: Rp " . number_format($order->creator_payout, 0, ',', '.') . "\n"
            . "📧 Buyer: {$order->buyer_email}\n\n"
            . "Cek dashboard: " . route('dashboard.index');

        // Use creator's phone if available
        $phone = $creator->phone ?? '';
        if (!$phone) return false;

        return $this->send($phone, $message);
    }

    /**
     * Send shipping update to buyer.
     */
    public function sendShippingUpdate(\App\Models\Order $order, string $newStatus, ?string $trackingNumber = null): bool
    {
        $creator = $order->creator;

        $message = "📦 *Shipping Update*\n\n"
            . "Order *{$order->product->title}* status: *{$newStatus}*\n";

        if ($trackingNumber) {
            $message .= "🔢 Resi: `{$trackingNumber}`\n";
        }

        $message .= "\nCek: " . route('dashboard.fulfillment.show', $order->id);

        return $this->send($order->buyer_email, $message);
    }

    // ───── Helpers ─────

    protected function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone)) return null;

        // Convert 08xx to 628xx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Validate Indonesian mobile (08xx, 628xx, +628xx)
        if (!preg_match('/^62[0-9]{9,13}$/', $phone)) {
            return null;
        }

        return $phone;
    }

    protected function sendWablas(string $phone, string $message): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->post('https://api.wablas.com/api/send-message', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }

    protected function sendFonnte(string $phone, string $message): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
        ]);

        return $response->successful();
    }

    protected function logOnly(string $phone, string $message): bool
    {
        Log::info('WhatsApp message', ['to' => $phone, 'message' => $message]);
        return true;
    }
}