<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCheckoutUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_url_returns_valid_route_for_product(): void
    {
        $creator = User::factory()->create([
            'username' => 'demo_alice',
            'transaction_fee_pct' => 10,
        ]);

        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'title' => 'Test Ebook',
            'price' => 50000,
            'status' => 'published',
        ]);

        // Accessing the accessor should NOT throw "Property [checkout_url] does not exist"
        $url = $product->checkout_url;

        $this->assertNotEmpty($url, 'checkout_url must not be empty');
        $this->assertIsString($url);
        $this->assertStringContainsString("/{$creator->username}/{$product->id}/checkout", $url);
    }

    public function test_checkout_url_format(): void
    {
        $creator = User::factory()->create(['username' => 'shop_bob']);
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'title' => 'Format Test',
            'price' => 1000,
        ]);

        $url = $product->checkout_url;

        // Must end with /{username}/{productId}/checkout
        $this->assertStringEndsWith("/{$creator->username}/{$product->id}/checkout", $url);

        // Must contain a scheme (http or https)
        $this->assertMatchesRegularExpression('#^https?://#', $url);
    }

    public function test_checkout_url_with_special_username(): void
    {
        $creator = User::factory()->create([
            'username' => 'alice-123_xyz',
        ]);
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'course',
            'title' => 'Special Username Course',
            'price' => 100,
        ]);

        $url = $product->checkout_url;

        // Accessor must encode/preserve the username verbatim
        $this->assertStringContainsString("/{$creator->username}/{$product->id}/checkout", $url);
        $this->assertStringNotContainsString('//checkout', $url, 'must not produce double slash');
    }
}
