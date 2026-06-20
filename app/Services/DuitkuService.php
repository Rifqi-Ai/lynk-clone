<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Duitku payment gateway integration.
 *
 * Docs: https://docs.duitku.com/pop/en/
 *
 * MVP behavior:
 * - In non-production mode (dev), returns a mock payment URL for testing.
 * - In production mode, calls the real Duitku API.
 *
 * Swap DUITKU_MOCK=false and set real credentials to go live.
 */
class DuitkuService
{
    protected string $apiKey;
    protected string $merchantCode;
    protected bool $production;
    protected string $baseUrl;
    protected string $callbackUrl;
    protected string $returnUrl;

    public function __construct()
    {
        $this->apiKey = config('services.duitku.api_key', 'mock_api_key');
        $this->merchantCode = config('services.duitku.merchant_code', 'mock_merchant');
        $this->production = (bool) config('services.duitku.production', false);
        $this->baseUrl = $this->production
            ? 'https://api-prod.duitku.com/api/merchant'
            : 'https://api-sandbox.duitku.com/api/merchant';
        $this->callbackUrl = config('services.duitku.callback_url', url('/payment/callback'));
        $this->returnUrl = config('services.duitku.return_url', url('/payment/success'));
    }

    /**
     * Create a Duitku transaction and return the payment URL.
     */
    public function createTransaction(Order $order, Product $product, User $creator, string $payerEmail): string
    {
        // Dev mode: return mock URL for testing
        if (!$this->production) {
            return $this->mockPaymentUrl($order);
        }

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => (int) $order->total,
            'paymentMethod' => $this->detectPaymentMethod(), // could be from form
            'merchantOrderId' => $order->id,
            'productDetails' => mb_substr($product->title, 0, 100),
            'additionalParam' => '',
            'merchantUserInfo' => '',
            'customerVaName' => mb_substr($payerEmail, 0, 20),
            'email' => $payerEmail,
            'phoneNumber' => '',
            'itemDetails' => [
                [
                    'name' => $product->title,
                    'price' => (int) $order->subtotal,
                    'quantity' => $order->quantity,
                ],
            ],
            'customerDetail' => [
                'firstName' => $payerEmail,
                'lastName' => '',
                'email' => $payerEmail,
                'phoneNumber' => '',
            ],
            'callbackUrl' => $this->callbackUrl,
            'returnUrl' => route('payment.success', $order),
            'signature' => $this->generateSignature((int) $order->total, $order->id),
            'expiryPeriod' => 1440, // 24 hours in minutes
        ];

        $response = Http::post("{$this->baseUrl}/createInvoice", $payload);

        if (!$response->successful()) {
            Log::error('Duitku createInvoice failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Duitku API error: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['paymentUrl'])) {
            throw new \RuntimeException('Duitku response missing paymentUrl: ' . json_encode($data));
        }

        // Save reference
        $order->update([
            'duitku_invoice_id' => $data['reference'] ?? null,
            'duitku_reference' => $data['reference'] ?? null,
        ]);

        return $data['paymentUrl'];
    }

    /**
     * Verify Duitku callback signature.
     * Duitku signature = sha256(merchantCode + amount + merchantOrderId + apiKey)
     */
    public function verifyCallback(array $payload): bool
    {
        $signature = $payload['signature'] ?? null;
        if (!$signature) return false;

        $expected = hash('sha256', $this->merchantCode . ($payload['amount'] ?? '') . ($payload['merchantOrderId'] ?? '') . $this->apiKey);
        return hash_equals($expected, $signature);
    }

    protected function generateSignature(int $amount, string $orderId): string
    {
        return hash('sha256', $this->merchantCode . $amount . $orderId . $this->apiKey);
    }

    protected function detectPaymentMethod(): string
    {
        // In a real impl, this would come from checkout form
        return config('services.duitku.default_method', 'VC');
    }

    /**
     * Generate a mock payment URL for local dev/testing.
     */
    protected function mockPaymentUrl(Order $order): string
    {
        // For MVP dev: redirect to a mock payment page
        return route('payment.success', $order) . '?mock=1';
    }
}