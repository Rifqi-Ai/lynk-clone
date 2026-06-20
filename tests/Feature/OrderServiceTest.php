<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for OrderService — the single source of truth for order business logic.
 * Refactored out of PublicProfileController + CartController in Phase 9.
 */
class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orders;

    protected User $creator;

    protected User $buyer;

    protected Product $digitalProduct;

    protected Product $donationProduct;

    protected Product $eventProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orders = new OrderService;

        $this->creator = User::factory()->create(['transaction_fee_pct' => 10]);
        $this->buyer = User::factory()->create();

        $this->digitalProduct = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'digital',
            'price' => 100000,
            'status' => 'published',
        ]);

        $this->donationProduct = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'donation',
            'price' => 0,
            'status' => 'published',
            'metadata' => ['preset_amounts' => [10000, 50000]],
        ]);

        $this->eventProduct = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'event',
            'price' => 25000,
            'status' => 'published',
            'metadata' => ['capacity' => 100, 'event_date' => now()->addWeek()->format('Y-m-d')],
        ]);
    }

    public function test_clamp_fee_returns_value_within_safe_range(): void
    {
        $this->assertEquals(0.0, $this->orders->clampFee(-50));
        $this->assertEquals(0.0, $this->orders->clampFee(0));
        $this->assertEquals(10.0, $this->orders->clampFee(10));
        $this->assertEquals(50.0, $this->orders->clampFee(50));
        $this->assertEquals(50.0, $this->orders->clampFee(9999));
        $this->assertEquals(10.0, $this->orders->clampFee(null));
    }

    public function test_compute_pricing_for_digital_product(): void
    {
        $pricing = $this->orders->computePricing($this->digitalProduct, ['quantity' => 1]);

        $this->assertEquals(100000, $pricing['unit_price']);
        $this->assertEquals(1, $pricing['quantity']);
        $this->assertEquals(100000, $pricing['subtotal']);
        $this->assertEquals(0, $pricing['shipping_cost']);
        $this->assertEquals(100000, $pricing['total']);
        $this->assertEquals(10.0, $pricing['fee_pct']);
        $this->assertEquals(10000, $pricing['fee_amount']);
        $this->assertEquals(90000, $pricing['creator_payout']);
    }

    public function test_compute_pricing_for_donation_uses_amount(): void
    {
        $pricing = $this->orders->computePricing($this->donationProduct, ['amount' => 50000]);

        $this->assertEquals(50000, $pricing['unit_price']);
        $this->assertEquals(1, $pricing['quantity']);
        $this->assertEquals(50000, $pricing['subtotal']);
        $this->assertEquals(50000, $pricing['total']);
    }

    public function test_compute_pricing_for_event_uses_quantity(): void
    {
        $pricing = $this->orders->computePricing($this->eventProduct, ['quantity' => 3]);

        $this->assertEquals(25000, $pricing['unit_price']);
        $this->assertEquals(3, $pricing['quantity']);
        $this->assertEquals(75000, $pricing['subtotal']);
        $this->assertEquals(75000, $pricing['total']);
    }

    public function test_compute_pricing_for_physical_adds_shipping(): void
    {
        $physical = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'physical',
            'price' => 50000,
            'status' => 'published',
        ]);

        $pricing = $this->orders->computePricing($physical, ['quantity' => 2]);

        $this->assertEquals(50000, $pricing['unit_price']);
        $this->assertEquals(2, $pricing['quantity']);
        $this->assertEquals(100000, $pricing['subtotal']);
        $this->assertEquals(15000, $pricing['shipping_cost']); // flat Rp 15K
        $this->assertEquals(115000, $pricing['total']);
    }

    public function test_compute_pricing_applies_voucher_discount(): void
    {
        $pricing = $this->orders->computePricing(
            $this->digitalProduct,
            ['quantity' => 1],
            voucherDiscount: 20000
        );

        $this->assertEquals(100000, $pricing['subtotal']);
        $this->assertEquals(80000, $pricing['total']); // 100K - 20K discount
    }

    public function test_create_single_product_order_sets_payment_status_pending(): void
    {
        $order = $this->orders->createSingleProductOrder(
            buyer: $this->buyer,
            creator: $this->creator,
            product: $this->digitalProduct,
            data: ['payer_email' => 'buyer@example.com', 'quantity' => 1],
        );

        $this->assertNotNull($order->id);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals(100000, $order->total);
        $this->assertEquals(90000, $order->creator_payout);
    }

    public function test_create_single_product_order_stores_appointment_metadata(): void
    {
        $appointment = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'appointment',
            'price' => 100000,
            'status' => 'published',
        ]);

        $order = $this->orders->createSingleProductOrder(
            buyer: $this->buyer,
            creator: $this->creator,
            product: $appointment,
            data: [
                'payer_email' => 'buyer@example.com',
                'appointment_date' => '2026-07-01',
                'appointment_time' => '14:00',
            ],
        );

        $this->assertEquals('2026-07-01', $order->metadata['appointment_date']);
        $this->assertEquals('14:00', $order->metadata['appointment_time']);
    }

    public function test_create_cart_order_aggregates_items(): void
    {
        $item1 = new CartItem([
            'cart_id' => 'test-cart-uuid',
            'product_id' => $this->digitalProduct->id,
            'quantity' => 2,
            'unit_price' => 100000,
        ]);
        $item1->setRelation('product', $this->digitalProduct);

        $item2 = new CartItem([
            'cart_id' => 'test-cart-uuid',
            'product_id' => $this->eventProduct->id,
            'quantity' => 1,
            'unit_price' => 25000,
        ]);
        $item2->setRelation('product', $this->eventProduct);

        $items = collect([$item1, $item2]);

        $order = $this->orders->createCartOrder(
            creator: $this->creator,
            buyer: $this->buyer,
            items: $items,
            payerEmail: 'buyer@example.com',
            voucherCode: null,
            voucherDiscount: 0,
        );

        $this->assertEquals(225000, $order->subtotal);
        $this->assertEquals(225000, $order->total);
        $this->assertEquals(3, $order->quantity);
    }

    public function test_create_cart_order_applies_voucher(): void
    {
        $item = new CartItem([
            'cart_id' => 'test-cart-uuid',
            'product_id' => $this->digitalProduct->id,
            'quantity' => 1,
            'unit_price' => 100000,
        ]);
        $item->setRelation('product', $this->digitalProduct);

        $order = $this->orders->createCartOrder(
            creator: $this->creator,
            buyer: $this->buyer,
            items: collect([$item]),
            payerEmail: 'buyer@example.com',
            voucherCode: 'PROMO10',
            voucherDiscount: 10000,
        );

        $this->assertEquals(100000, $order->subtotal);
        $this->assertEquals(90000, $order->total);
        $this->assertEquals('PROMO10', $order->voucher_code);
    }

    public function test_fee_is_clamped_even_with_compromised_creator(): void
    {
        $badCreator = User::factory()->create(['transaction_fee_pct' => 9999]);

        $pricing = $this->orders->computePricing(
            $this->digitalProduct,
            ['quantity' => 1],
        );

        // Bad creator's fee should still be clamped (because we read from product->owner)
        // But here we use digitalProduct's owner which is goodCreator (10%). Test with bad creator product:
        $badProduct = Product::factory()->create([
            'user_id' => $badCreator->id,
            'type' => 'digital',
            'price' => 100000,
            'status' => 'published',
        ]);

        $pricing = $this->orders->computePricing($badProduct, ['quantity' => 1]);

        $this->assertLessThanOrEqual(50, $pricing['fee_pct']);
        $this->assertEquals(50.0, $pricing['fee_pct']); // clamped from 9999
    }
}
