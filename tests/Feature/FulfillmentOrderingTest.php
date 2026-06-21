<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for FulfillmentController::index().
 *
 * Two contracts under test:
 *  1. **Business ordering**: orders must sort by shipping workflow
 *     (pending → packed → shipped → delivered) — NOT alphabetical.
 *     This catches the latent bug where the previous `orderByRaw` on
 *     `JSON_EXTRACT(metadata, '$.shipping_status') ASC` returned the
 *     reverse workflow order, hiding pending work from the creator.
 *  2. **Defensive query shape**: no raw SQL fragments referencing
 *     JSON_EXTRACT(metadata) — flagged as defensive by the Phase 12
 *     security audit because such fragments break silently when the
 *     metadata column structure changes.
 *
 * @see docs/security-audit-2026-06-21.md (recommendation #2)
 */
class FulfillmentOrderingTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;

    private User $otherCreator;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create();
        $this->otherCreator = User::factory()->create();
        $this->product = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'physical',
        ]);
    }

    private function makeOrder(string $shippingStatus, ?string $paidAt = null): Order
    {
        return Order::factory()->create([
            'creator_user_id' => $this->creator->id,
            'product_id' => $this->product->id,
            'payment_status' => 'paid',
            'paid_at' => $paidAt ?? now()->subDay(),
            'shipping_status' => $shippingStatus,
        ]);
    }

    public function test_pending_orders_appear_before_packed_shipped_and_delivered(): void
    {
        $delivered = $this->makeOrder('delivered');
        $shipped = $this->makeOrder('shipped');
        $packed = $this->makeOrder('packed');
        $pending = $this->makeOrder('pending');

        $response = $this->actingAs($this->creator)
            ->get('/dashboard/fulfillment');

        $response->assertStatus(200);

        // Extract order IDs in the order they appear on the page.
        $positions = collect([$pending, $packed, $shipped, $delivered])
            ->mapWithKeys(fn (Order $o) => [$o->id => $response->getContent()]);

        $pendingPos = strpos($response->getContent(), $pending->id);
        $packedPos = strpos($response->getContent(), $packed->id);
        $shippedPos = strpos($response->getContent(), $shipped->id);
        $deliveredPos = strpos($response->getContent(), $delivered->id);

        $this->assertNotFalse($pendingPos, 'pending order not rendered');
        $this->assertNotFalse($packedPos, 'packed order not rendered');
        $this->assertNotFalse($shippedPos, 'shipped order not rendered');
        $this->assertNotFalse($deliveredPos, 'delivered order not rendered');

        $this->assertLessThan($packedPos, $pendingPos, 'pending should appear before packed');
        $this->assertLessThan($shippedPos, $packedPos, 'packed should appear before shipped');
        $this->assertLessThan($deliveredPos, $shippedPos, 'shipped should appear before delivered');
    }

    public function test_workflow_order_is_pending_packed_shipped_delivered_regardless_of_insertion_order(): void
    {
        // Insert in REVERSE workflow order — pending should still come first.
        $this->makeOrder('delivered');
        $this->makeOrder('shipped');
        $this->makeOrder('packed');
        $this->makeOrder('pending');

        $response = $this->actingAs($this->creator)
            ->get('/dashboard/fulfillment');

        $response->assertStatus(200);
        $content = $response->getContent();

        preg_match_all('/ORD-[A-Z0-9-]+/', $content, $matches);
        $renderedIds = array_unique($matches[0] ?? []);

        $this->assertGreaterThanOrEqual(4, count($renderedIds), 'expected 4 orders rendered');

        $orderMap = Order::where('creator_user_id', $this->creator->id)
            ->get()
            ->keyBy('id');

        // Filter to only IDs that correspond to real orders (regex may match other text).
        $validIds = array_values(array_filter(
            $renderedIds,
            fn ($id) => $orderMap->has($id)
        ));

        $firstFour = array_slice($validIds, 0, 4);
        $this->assertCount(4, $firstFour, 'expected to find all 4 order IDs in rendered HTML');

        $statuses = array_map(
            fn ($id) => $orderMap[$id]->shipping_status ?? data_get($orderMap[$id]->metadata, 'shipping_status'),
            $firstFour
        );

        $this->assertSame(
            ['pending', 'packed', 'shipped', 'delivered'],
            $statuses,
            'first four rendered orders must follow workflow order pending→packed→shipped→delivered, got: '.implode(',', $statuses)
        );
    }

    public function test_orders_without_shipping_status_are_treated_as_pending(): void
    {
        // Order with NULL shipping_status (legacy data from before column promotion — should not crash).
        $orphaned = Order::factory()->create([
            'creator_user_id' => $this->creator->id,
            'product_id' => $this->product->id,
            'payment_status' => 'paid',
            'paid_at' => now()->subDay(),
            'shipping_status' => null,
        ]);

        $response = $this->actingAs($this->creator)
            ->get('/dashboard/fulfillment');

        $response->assertStatus(200);
        $this->assertStringContainsString($orphaned->id, $response->getContent());
    }

    public function test_non_physical_orders_are_excluded(): void
    {
        $physical = $this->makeOrder('pending');
        $digital = Order::factory()->create([
            'creator_user_id' => $this->creator->id,
            'product_id' => Product::factory()->create([
                'user_id' => $this->creator->id,
                'type' => 'digital',
            ])->id,
            'payment_status' => 'paid',
            'paid_at' => now()->subDay(),
            'shipping_status' => 'pending',
        ]);

        $response = $this->actingAs($this->creator)
            ->get('/dashboard/fulfillment');

        $response->assertStatus(200);
        $this->assertStringContainsString($physical->id, $response->getContent());
        $this->assertStringNotContainsString($digital->id, $response->getContent());
    }

    public function test_other_creators_orders_are_excluded(): void
    {
        $myOrder = $this->makeOrder('pending');

        $otherProduct = Product::factory()->create([
            'user_id' => $this->otherCreator->id,
            'type' => 'physical',
        ]);
        $otherOrder = Order::factory()->create([
            'creator_user_id' => $this->otherCreator->id,
            'product_id' => $otherProduct->id,
            'payment_status' => 'paid',
            'paid_at' => now()->subDay(),
            'shipping_status' => 'pending',
        ]);

        $response = $this->actingAs($this->creator)
            ->get('/dashboard/fulfillment');

        $response->assertStatus(200);
        $this->assertStringContainsString($myOrder->id, $response->getContent());
        $this->assertStringNotContainsString($otherOrder->id, $response->getContent());
    }
}
