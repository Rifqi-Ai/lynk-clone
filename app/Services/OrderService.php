<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Centralized business logic for order creation.
 *
 * Extracted from PublicProfileController and CartController to:
 * - Single source of truth for fee/payout calculation
 * - Transactional integrity (order + side-effects atomic)
 * - Easier to test (no HTTP layer required)
 * - Easier to swap payment gateway (Duitku, Midtrans, Xendit, etc.)
 */
class OrderService
{
    /** Maximum transaction fee (defends against compromised creator accounts setting 9999%) */
    public const MAX_FEE_PCT = 50;

    /** Maximum flat shipping cost (defends against product metadata abuse) */
    public const MAX_SHIPPING_COST = 100000; // Rp 100K cap

    /** Minimum transaction fee (never negative) */
    public const MIN_FEE_PCT = 0;

    /** Default transaction fee when creator has none set */
    public const DEFAULT_FEE_PCT = 10;

    /**
     * Create a single-product order.
     *
     * @param  array{
     *     payer_email: string,
     *     quantity?: int,
     *     amount?: int,
     *     donor_message?: string,
     *     appointment_date?: string,
     *     appointment_time?: string,
     *     ship?: array<string, string>,
     * }  $data  Validated checkout form data
     */
    public function createSingleProductOrder(
        User $buyer,
        User $creator,
        Product $product,
        array $data,
        ?string $voucherCode = null,
        float $voucherDiscount = 0
    ): Order {
        $pricing = $this->computePricing($product, $data, $voucherDiscount);

        return DB::transaction(function () use ($buyer, $creator, $product, $data, $pricing, $voucherCode, $voucherDiscount) {
            $order = new Order([
                'buyer_user_id' => $buyer->id,
                'buyer_email' => $data['payer_email'],
                'product_id' => $product->id,
                'creator_user_id' => $creator->id,
                'unit_price' => $pricing['unit_price'],
                'quantity' => $pricing['quantity'],
                'subtotal' => $pricing['subtotal'],
                'fee_pct' => $pricing['fee_pct'],
                'fee_amount' => $pricing['fee_amount'],
                'total' => $pricing['total'],
                'creator_payout' => $pricing['creator_payout'],
                'voucher_code' => $voucherCode,
                'voucher_discount' => $voucherDiscount,
                'metadata' => $this->buildOrderMetadata($product, $data),
                'expired_at' => now()->addHours(24),
            ]);
            // payment_status not fillable — assign directly (bypasses mass-assignment protection)
            $order->payment_status = 'pending';
            $order->save();

            // NOTE: sales_count is incremented ONLY in PaymentCallback on actual payment,
            // not here (abandoned checkouts would otherwise inflate stats).

            return $order;
        });
    }

    /**
     * Create a multi-item cart order.
     *
     * @param  Collection<int, CartItem>  $items
     */
    public function createCartOrder(
        User $creator,
        ?User $buyer,
        $items,
        string $payerEmail,
        ?string $voucherCode,
        float $voucherDiscount
    ): Order {
        $subtotal = $items->sum(fn ($i) => $i->unit_price * $i->quantity);
        $total = max(0, $subtotal - $voucherDiscount);
        $feePct = $this->clampFee($creator->transaction_fee_pct ?? self::DEFAULT_FEE_PCT);
        $feeAmount = $total * ($feePct / 100);
        $creatorPayout = $total - $feeAmount;

        $primaryItem = $items->first();

        return DB::transaction(function () use ($creator, $buyer, $items, $payerEmail, $voucherCode, $voucherDiscount, $subtotal, $total, $feePct, $feeAmount, $creatorPayout, $primaryItem) {
            $order = new Order([
                'buyer_user_id' => $buyer?->id,
                'buyer_email' => $payerEmail,
                'product_id' => $primaryItem->product_id,
                'creator_user_id' => $creator->id,
                'unit_price' => $total,
                'quantity' => $items->sum('quantity'),
                'subtotal' => $subtotal,
                'fee_pct' => $feePct,
                'fee_amount' => $feeAmount,
                'total' => $total,
                'creator_payout' => $creatorPayout,
                'voucher_code' => $voucherCode,
                'voucher_discount' => $voucherDiscount,
                'metadata' => [
                    'items' => $items->map(fn ($i) => [
                        'product_id' => $i->product_id,
                        'title' => $i->product?->title ?? '(removed)',
                        'type' => $i->product?->type ?? 'unknown',
                        'quantity' => $i->quantity,
                        'unit_price' => $i->unit_price,
                    ])->values()->all(),
                ],
                'expired_at' => now()->addHours(24),
            ]);
            $order->payment_status = 'pending';
            $order->save();

            return $order;
        });
    }

    /**
     * Compute pricing for a single-product order.
     *
     * @return array{unit_price: float, quantity: int, subtotal: float, shipping_cost: float, total: float, fee_pct: float, fee_amount: float, creator_payout: float}
     */
    public function computePricing(Product $product, array $data, float $voucherDiscount = 0): array
    {
        $feePct = $this->clampFee($product->owner->transaction_fee_pct ?? self::DEFAULT_FEE_PCT);

        if ($product->type === 'donation') {
            $unitPrice = (int) ($data['amount'] ?? 0);
            $quantity = 1;
        } elseif ($product->type === 'event') {
            $quantity = (int) ($data['quantity'] ?? 1);
            $unitPrice = (float) $product->price;
        } else {
            $quantity = (int) ($data['quantity'] ?? 1);
            $unitPrice = (float) $product->price;
        }

        $subtotal = $unitPrice * $quantity;
        // MVP: flat shipping. Future: integrate RajaOngkir API when real rates are needed.
        $shippingCost = $product->type === 'physical' ? 15000 : 0;
        // Cap to prevent abuse if any code path allows user-controlled shipping values
        $shippingCost = min($shippingCost, self::MAX_SHIPPING_COST);

        $totalBeforeVoucher = $subtotal + $shippingCost;
        $total = max(0, $totalBeforeVoucher - $voucherDiscount);

        $feeAmount = $total * ($feePct / 100);
        $creatorPayout = $total - $feeAmount;

        return [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'fee_pct' => $feePct,
            'fee_amount' => $feeAmount,
            'creator_payout' => $creatorPayout,
        ];
    }

    /**
     * Build order metadata JSON for type-specific order data.
     */
    protected function buildOrderMetadata(Product $product, array $data): array
    {
        $metadata = [];

        if ($product->type === 'appointment') {
            $metadata['appointment_date'] = $data['appointment_date'] ?? null;
            $metadata['appointment_time'] = $data['appointment_time'] ?? null;
        }

        if ($product->type === 'donation' && ! empty($data['donor_message'])) {
            $metadata['donor_message'] = $data['donor_message'];
        }

        if ($product->type === 'physical' && ! empty($data['ship'])) {
            $metadata['shipping_address'] = $data['ship'];
            $metadata['shipping_status'] = 'pending'; // pending → packed → shipped → delivered
        }

        if ($product->type === 'event' && ! empty($data['attendee_name'])) {
            $metadata['attendee_name'] = $data['attendee_name'];
        }

        return $metadata;
    }

    /**
     * Clamp fee to a safe range. Even if a creator's transaction_fee_pct is
     * compromised (e.g. set to -50 or 9999), we clamp to 0-50%.
     */
    public function clampFee(mixed $rawFee): float
    {
        $fee = (float) ($rawFee ?? self::DEFAULT_FEE_PCT);

        return max(self::MIN_FEE_PCT, min(self::MAX_FEE_PCT, $fee));
    }
}
