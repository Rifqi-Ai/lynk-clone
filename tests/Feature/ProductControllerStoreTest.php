<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Characterization tests for ProductController::store().
 *
 * These tests pin down the current behavior of the create-product flow
 * so the Phase 16 refactor (extracting helpers, naming magic numbers)
 * cannot regress observable behavior.
 *
 * Covers: auth, validation, rate limiting, slug collision retry,
 * file/thumbnail uploads, and type-specific metadata extraction.
 */
class ProductControllerStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Reset rate limiter between tests so 20/hour budget is fresh per test.
        RateLimiter::clear('product-create:'.($GLOBALS['__store_user_id'] ?? 0));
    }

    private function makeCreator(): User
    {
        $user = User::factory()->create([
            'username' => 'creator_'.uniqid(),
        ]);
        $GLOBALS['__store_user_id'] = $user->id;
        RateLimiter::clear('product-create:'.$user->id);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'digital',
            'title' => 'My Test Product',
            'description' => 'A test description',
            'price' => 25000,
        ], $overrides);
    }

    // ───── Happy path ─────

    public function test_creates_draft_product_when_publish_not_set(): void
    {
        $creator = $this->makeCreator();

        $response = $this->actingAs($creator)->post('/dashboard/products', $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'user_id' => $creator->id,
            'type' => 'digital',
            'title' => 'My Test Product',
            'price' => 25000,
            'status' => 'draft',
        ]);
    }

    public function test_creates_published_product_when_publish_flag_set(): void
    {
        $creator = $this->makeCreator();

        $response = $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload(['publish' => '1'])
        );

        $response->assertRedirect(route('dashboard.products.index'));
        $product = Product::where('user_id', $creator->id)->first();
        $this->assertSame('published', $product->status);
        $response->assertSessionHas('success');
    }

    public function test_generates_slug_from_title(): void
    {
        $creator = $this->makeCreator();

        $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload(['title' => 'Hello World Product'])
        );

        $this->assertDatabaseHas('products', [
            'user_id' => $creator->id,
            'slug' => 'hello-world-product',
        ]);
    }

    // ───── Validation failures ─────

    public function test_rejects_missing_title(): void
    {
        $creator = $this->makeCreator();
        $payload = $this->validPayload();
        unset($payload['title']);

        $this->actingAs($creator)
            ->post('/dashboard/products', $payload)
            ->assertSessionHasErrors('title');
    }

    public function test_rejects_invalid_type(): void
    {
        $creator = $this->makeCreator();

        $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload(['type' => 'invalid_type'])
        )->assertSessionHasErrors('type');
    }

    public function test_rejects_negative_price(): void
    {
        $creator = $this->makeCreator();

        $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload(['price' => -100])
        )->assertSessionHasErrors('price');
    }

    // ───── Rate limit ─────

    public function test_rate_limits_at_20_products_per_hour(): void
    {
        $creator = $this->makeCreator();
        // Burn the budget with 20 successful requests.
        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($creator)->post('/dashboard/products',
                $this->validPayload(['title' => "Product {$i}"])
            );
        }

        $this->assertSame(20, Product::where('user_id', $creator->id)->count());

        // 21st request must be blocked.
        $this->actingAs($creator)
            ->post('/dashboard/products', $this->validPayload(['title' => 'One Too Many']))
            ->assertSessionHasErrors('title');

        $this->assertSame(20, Product::where('user_id', $creator->id)->count());
    }

    // ───── Slug uniqueness ─────

    public function test_appends_suffix_on_slug_collision(): void
    {
        $creator = $this->makeCreator();

        // Suppress exception handling so we see the real error.
        $this->withoutExceptionHandling([]);

        try {
            // First product: 'duplicate-title' → stored as 'duplicate-title'
            $r1 = $this->actingAs($creator)->post('/dashboard/products',
                $this->validPayload(['title' => 'Duplicate Title'])
            );
            // Second product with same title → slug gets '-1' suffix.
            $r2 = $this->actingAs($creator)->post('/dashboard/products',
                $this->validPayload(['title' => 'Duplicate Title'])
            );
        } catch (\Throwable $t) {
            $this->fail('Exception thrown: '.get_class($t).': '.$t->getMessage().
                "\n\nFile: ".$t->getFile().':'.$t->getLine().
                "\n\nTrace: ".substr($t->getTraceAsString(), 0, 2000)
            );
        }

        $products = Product::where('user_id', $creator->id)->get();
        $slugs = $products->pluck('slug')->all();
        sort($slugs);
        $this->assertSame(['duplicate-title', 'duplicate-title-1'], $slugs,
            'DB products: '.json_encode($slugs).
            ' | r1 status: '.$r1->status().
            ' | r2 status: '.$r2->status()
        );
    }

    // ───── File uploads ─────

    public function test_stores_thumbnail_when_uploaded(): void
    {
        Storage::fake('public');
        $creator = $this->makeCreator();
        $thumbnail = UploadedFile::fake()->image('thumb.jpg', 800, 600);

        $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload(['thumbnail' => $thumbnail])
        );

        $product = Product::where('user_id', $creator->id)->first();
        $this->assertNotNull($product->thumbnail_path);
        Storage::disk('public')->assertExists($product->thumbnail_path);
    }

    public function test_stores_digital_file_when_uploaded(): void
    {
        Storage::fake('public');
        $creator = $this->makeCreator();
        $file = UploadedFile::fake()->create('ebook.pdf', 1024, 'application/pdf');

        $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload([
                'type' => 'digital',
                'file' => $file,
            ])
        );

        $product = Product::where('user_id', $creator->id)->first();
        $this->assertSame('ebook.pdf', $product->file_name);
        $this->assertSame(1024 * 1024, $product->file_size);
        Storage::disk('public')->assertExists($product->file_path);
    }

    public function test_rejects_oversized_thumbnail(): void
    {
        Storage::fake('public');
        $creator = $this->makeCreator();
        // 2001×2001 exceeds 2000×2000 limit.
        $thumbnail = UploadedFile::fake()->image('huge.jpg', 2001, 2001);

        $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload(['thumbnail' => $thumbnail])
        )->assertSessionHasErrors('thumbnail');
    }

    public function test_rejects_executable_file(): void
    {
        Storage::fake('public');
        $creator = $this->makeCreator();
        // .php is in the blocklist.
        $file = UploadedFile::fake()->create('malware.php', 100, 'application/x-php');

        $this->actingAs($creator)->post('/dashboard/products',
            $this->validPayload(['file' => $file])
        )->assertSessionHasErrors('file');
    }

    // ───── Type-specific metadata ─────

    public function test_extracts_donation_metadata(): void
    {
        $creator = $this->makeCreator();

        $this->actingAs($creator)->post('/dashboard/products', [
            'type' => 'donation',
            'title' => 'Support My Work',
            'price' => 10000,
            'preset_amounts' => ['10000', '25000', '50000'],
            'allow_custom' => '1',
            'goal_amount' => '1000000',
            'message_label' => 'Leave a message',
        ]);

        $product = Product::where('user_id', $creator->id)->first();
        $this->assertSame([10000, 25000, 50000], $product->metadata['preset_amounts']);
        $this->assertTrue($product->metadata['allow_custom']);
        $this->assertSame(1000000, $product->metadata['goal_amount']);
        $this->assertSame('Leave a message', $product->metadata['message_label']);
    }

    public function test_extracts_course_metadata(): void
    {
        $creator = $this->makeCreator();

        $this->actingAs($creator)->post('/dashboard/products', [
            'type' => 'course',
            'title' => 'Laravel 101',
            'price' => 99000,
            'course_modules' => ['Intro', 'Routing', 'Eloquent', ''],
            'total_duration_minutes' => '180',
            'level' => 'intermediate',
            'certificate' => '1',
        ]);

        $product = Product::where('user_id', $creator->id)->first();
        $this->assertSame(['Intro', 'Routing', 'Eloquent'], $product->metadata['modules']);
        $this->assertSame(180, $product->metadata['total_duration_minutes']);
        $this->assertSame('intermediate', $product->metadata['level']);
        $this->assertTrue($product->metadata['certificate']);
    }

    public function test_extracts_appointment_metadata(): void
    {
        $creator = $this->makeCreator();

        $this->actingAs($creator)->post('/dashboard/products', [
            'type' => 'appointment',
            'title' => 'Mentoring Session',
            'price' => 150000,
            'duration_minutes' => '45',
            'buffer_minutes' => '10',
            'location_type' => 'in-person',
            'location_details' => 'Jakarta',
        ]);

        $product = Product::where('user_id', $creator->id)->first();
        $this->assertSame(45, $product->metadata['duration_minutes']);
        $this->assertSame(10, $product->metadata['buffer_minutes']);
        $this->assertSame('in-person', $product->metadata['location_type']);
    }

    public function test_extracts_event_metadata(): void
    {
        $creator = $this->makeCreator();

        $this->actingAs($creator)->post('/dashboard/products', [
            'type' => 'event',
            'title' => 'Laravel Conf 2026',
            'price' => 50000,
            'event_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'capacity' => '200',
            'location' => 'Bandung',
        ]);

        $product = Product::where('user_id', $creator->id)->first();
        $this->assertSame('2026-09-15', $product->metadata['event_date']);
        $this->assertSame(200, $product->metadata['capacity']);
        $this->assertSame('Bandung', $product->metadata['location']);
    }

    // ───── Authorization ─────

    public function test_unauthenticated_users_redirected_to_login(): void
    {
        $response = $this->post('/dashboard/products', $this->validPayload());

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('products', 0);
    }
}
