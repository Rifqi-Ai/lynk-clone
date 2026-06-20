<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_name_returns_name_when_present()
    {
        $user = User::factory()->create([
            'name' => 'Alice Pratama',
            'username' => 'alice',
        ]);

        $this->assertEquals('Alice Pratama', $user->display_name);
    }

    public function test_display_name_falls_back_to_username_when_name_empty()
    {
        $user = User::factory()->create([
            'name' => '',
            'username' => 'bob',
        ]);

        $this->assertEquals('@bob', $user->display_name);
    }

    public function test_is_verified_aliases_verified_column()
    {
        $verifiedUser = User::factory()->create(['verified' => true]);
        $unverifiedUser = User::factory()->create(['verified' => false]);

        $this->assertTrue($verifiedUser->is_verified);
        $this->assertFalse($unverifiedUser->is_verified);
    }

    public function test_followers_count_defaults_to_zero()
    {
        $user = User::factory()->create();

        $this->assertEquals(0, $user->followers_count);
    }

    public function test_total_sales_count_sums_paid_orders_as_creator()
    {
        $creator = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $creator->id]);

        Order::factory()->count(3)->create([
            'creator_user_id' => $creator->id,
            'product_id' => $product->id,
            'payment_status' => 'paid',
        ]);
        Order::factory()->create([
            'creator_user_id' => $creator->id,
            'product_id' => $product->id,
            'payment_status' => 'pending',
        ]);
        Order::factory()->create([
            'creator_user_id' => $creator->id,
            'product_id' => $product->id,
            'payment_status' => 'failed',
        ]);

        $this->assertEquals(3, $creator->total_sales_count);
    }

    public function test_total_sales_count_returns_zero_for_new_user()
    {
        $user = User::factory()->create();

        $this->assertEquals(0, $user->total_sales_count);
    }
}
