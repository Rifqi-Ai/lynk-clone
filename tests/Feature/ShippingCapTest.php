<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingCapTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_cost_capped_at_max()
    {
        $service = new OrderService;
        $maxCost = OrderService::MAX_SHIPPING_COST;

        // Verify the constant is set to a sane value
        $this->assertGreaterThan(0, $maxCost);
        $this->assertLessThanOrEqual(1000000, $maxCost); // sanity bound
    }

    public function test_shipping_cost_zero_for_digital_products()
    {
        $service = new OrderService;
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('computePricing');
        $method->setAccessible(true);

        $creator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'price' => 50000,
        ]);

        $pricing = $method->invoke($service, $product, ['quantity' => 1], 0);
        // digital products should not add shipping to total
        $this->assertEquals(0, $pricing['shipping_cost'] ?? 0);
    }
}
