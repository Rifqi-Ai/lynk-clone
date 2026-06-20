<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTypeAccessorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_read_time_estimates_from_word_count()
    {
        $creator = User::factory()->create(['username' => 'blogger']);
        $body = str_repeat('lorem ipsum dolor sit amet consectetur ', 167); // ~1000 words
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'blog',
            'metadata' => ['body_markdown' => $body],
        ]);

        $this->assertGreaterThanOrEqual(4, $product->readTime);
        $this->assertLessThanOrEqual(6, $product->readTime);
    }

    public function test_blog_read_time_handles_empty_body()
    {
        $creator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'blog',
            'metadata' => ['body_markdown' => ''],
        ]);

        $this->assertEquals(1, $product->readTime); // min 1
    }

    public function test_digital_file_url_returns_storage_url()
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/test/file.pdf', 'fake content');

        $creator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'file_path' => 'products/test/file.pdf',
        ]);

        $this->assertStringContainsString('products/test/file.pdf', $product->file_url);
    }

    public function test_digital_file_url_returns_null_when_no_file()
    {
        $creator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'file_path' => null,
        ]);

        $this->assertNull($product->file_url);
    }

    public function test_physical_track_inventory_from_metadata()
    {
        $creator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'physical',
            'metadata' => ['track_inventory' => true, 'stock_quantity' => 5],
        ]);

        $this->assertTrue($product->track_inventory);
    }

    public function test_physical_track_inventory_defaults_true_when_missing()
    {
        $creator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'physical',
            'metadata' => [],
        ]);

        $this->assertTrue($product->track_inventory);
    }
}
