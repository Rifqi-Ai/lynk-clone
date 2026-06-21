<?php

namespace Tests\Feature;

use App\Models\Concerns\HasProductAccessors;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization test for Product accessors. Phase 17 Task #7 moves all
 * 17 accessors from Product model into HasProductAccessors trait.
 *
 * These tests pin the BEHAVIOR of every accessor so the refactor can't
 * silently change outputs. After moving to trait, all tests must still pass.
 *
 * @see docs/code-quality-audit-2026-06-21.md (Tier 2 recommendation #5)
 */
class ProductAccessorsTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create(['username' => 'testcreator']);
    }

    private function makeProduct(array $attrs = []): Product
    {
        return Product::factory()->create(array_merge(
            ['user_id' => $this->creator->id],
            $attrs
        ));
    }

    // ── URL accessors (5) ──────────────────────────────────────────────

    public function test_url_accessor_returns_creator_username_and_product_id(): void
    {
        $product = $this->makeProduct();
        $this->assertStringContainsString('testcreator', $product->url);
        $this->assertStringContainsString($product->id, $product->url);
    }

    public function test_checkout_url_accessor_includes_checkout_path(): void
    {
        $product = $this->makeProduct();
        $this->assertStringEndsWith('/checkout', $product->checkout_url);
        $this->assertStringContainsString($product->id, $product->checkout_url);
    }

    public function test_thumbnail_url_accessor_returns_null_when_no_path(): void
    {
        $product = $this->makeProduct(['thumbnail_path' => null]);
        $this->assertNull($product->thumbnail_url);
    }

    public function test_file_url_accessor_returns_null_when_no_file_path(): void
    {
        $product = $this->makeProduct(['type' => 'digital', 'file_path' => null]);
        $this->assertNull($product->file_url);
    }

    public function test_file_size_formatted_returns_null_when_no_size(): void
    {
        $product = $this->makeProduct(['type' => 'digital', 'file_size' => null]);
        $this->assertNull($product->file_size_formatted);
    }

    public function test_file_size_formatted_converts_bytes_to_human_readable(): void
    {
        $product = $this->makeProduct(['type' => 'digital', 'file_size' => 1048576]); // 1 MB
        $this->assertEquals('1 MB', $product->file_size_formatted);
    }

    // ── Pricing accessors (2) ───────────────────────────────────────────

    public function test_has_discount_accessor_returns_true_when_compare_at_price_higher(): void
    {
        $product = $this->makeProduct(['price' => 50, 'compare_at_price' => 100]);
        $this->assertTrue($product->has_discount);
    }

    public function test_has_discount_accessor_returns_false_when_no_compare_at_price(): void
    {
        $product = $this->makeProduct(['price' => 50, 'compare_at_price' => null]);
        $this->assertFalse($product->has_discount);
    }

    public function test_discount_percentage_accessor_calculates_correctly(): void
    {
        $product = $this->makeProduct(['price' => 75, 'compare_at_price' => 100]);
        $this->assertEquals(25, $product->discount_percentage);
    }

    // ── Type label/icon accessors (2) ───────────────────────────────────

    public function test_type_label_accessor_returns_localized_label(): void
    {
        $product = $this->makeProduct(['type' => 'digital']);
        $this->assertEquals('Digital Product', $product->type_label);
    }

    public function test_type_icon_accessor_returns_emoji(): void
    {
        $product = $this->makeProduct(['type' => 'course']);
        $this->assertEquals('🎓', $product->type_icon);
    }

    public function test_type_label_falls_back_to_ucfirst_for_unknown_type(): void
    {
        // We can't use an unknown type via factory (CHECK constraint), so we test
        // the fallback path by directly manipulating the type on an existing product
        // (cast removes the CHECK constraint validation in memory).
        $product = $this->makeProduct(['type' => 'digital']);
        // Re-set type without re-saving (CHECK only fires on insert/update SQL).
        $product->setRawAttributes(array_merge($product->getAttributes(), ['type' => 'custom_type']), true);
        $this->assertEquals('Custom_type', $product->type_label);
    }

    // ── Metadata helpers (2) ───────────────────────────────────────────

    public function test_meta_helper_returns_value_with_default(): void
    {
        $product = $this->makeProduct(['metadata' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $product->meta('foo'));
        $this->assertNull($product->meta('missing'));
        $this->assertEquals('default', $product->meta('missing', 'default'));
    }

    public function test_set_meta_helper_persists_value(): void
    {
        $product = $this->makeProduct(['metadata' => []]);
        $product->setMeta('key1', 'value1');
        $this->assertEquals('value1', $product->meta('key1'));
        $product->setMeta('key1', 'value2');
        $this->assertEquals('value2', $product->meta('key1'));
    }

    // ── Type-specific accessors (8) ─────────────────────────────────────

    public function test_donation_presets_accessor_returns_default_when_unset(): void
    {
        $product = $this->makeProduct(['type' => 'donation', 'metadata' => []]);
        $presets = $product->donation_presets;
        $this->assertIsArray($presets);
        $this->assertContains(10000, $presets);
    }

    public function test_donation_goal_accessor_returns_metadata_value(): void
    {
        $product = $this->makeProduct([
            'type' => 'donation',
            'metadata' => ['goal_amount' => 1000000],
        ]);
        $this->assertEquals(1000000, $product->donation_goal);
    }

    public function test_appointment_duration_minutes_accessor_uses_default(): void
    {
        $product = $this->makeProduct(['type' => 'appointment', 'metadata' => []]);
        $this->assertEquals(60, $product->duration_minutes);
    }

    public function test_appointment_duration_formatted_outputs_hours_and_minutes(): void
    {
        $product = $this->makeProduct([
            'type' => 'appointment',
            'metadata' => ['duration_minutes' => 90],
        ]);
        $this->assertEquals('1h 30m', $product->duration_formatted);
    }

    public function test_appointment_duration_formatted_outputs_hours_only(): void
    {
        $product = $this->makeProduct([
            'type' => 'appointment',
            'metadata' => ['duration_minutes' => 120],
        ]);
        $this->assertEquals('2h', $product->duration_formatted);
    }

    public function test_event_date_accessor_returns_metadata(): void
    {
        $product = $this->makeProduct([
            'type' => 'event',
            'metadata' => ['event_date' => '2026-12-31'],
        ]);
        $this->assertEquals('2026-12-31', $product->event_date);
    }

    public function test_course_modules_accessor_counts_modules_metadata(): void
    {
        $product = $this->makeProduct([
            'type' => 'course',
            'metadata' => ['modules' => [['id' => 1], ['id' => 2], ['id' => 3]]],
        ]);
        $this->assertEquals(3, $product->course_modules);
    }

    public function test_blog_body_accessor_returns_metadata(): void
    {
        $product = $this->makeProduct([
            'type' => 'blog',
            'metadata' => ['body_markdown' => '# Hello world'],
        ]);
        $this->assertEquals('# Hello world', $product->blog_body);
    }

    public function test_is_paywalled_accessor_defaults_false(): void
    {
        $product = $this->makeProduct(['type' => 'blog', 'metadata' => []]);
        $this->assertFalse($product->is_paywalled);
    }

    public function test_stock_quantity_accessor_returns_metadata(): void
    {
        $product = $this->makeProduct([
            'type' => 'physical',
            'metadata' => ['stock_quantity' => 42],
        ]);
        $this->assertEquals(42, $product->stock_quantity);
    }

    public function test_in_stock_accessor_true_when_stock_positive(): void
    {
        $product = $this->makeProduct([
            'type' => 'physical',
            'metadata' => ['stock_quantity' => 5],
        ]);
        $this->assertTrue($product->in_stock);
    }

    public function test_in_stock_accessor_true_when_stock_null_unlimited(): void
    {
        $product = $this->makeProduct([
            'type' => 'physical',
            'metadata' => [],
        ]);
        $this->assertTrue($product->in_stock);
    }

    public function test_in_stock_accessor_false_when_stock_zero(): void
    {
        $product = $this->makeProduct([
            'type' => 'physical',
            'metadata' => ['stock_quantity' => 0],
        ]);
        $this->assertFalse($product->in_stock);
    }

    public function test_track_inventory_accessor_defaults_true(): void
    {
        $product = $this->makeProduct(['type' => 'physical', 'metadata' => []]);
        $this->assertTrue($product->track_inventory);
    }

    public function test_read_time_accessor_computes_from_body(): void
    {
        $body = str_repeat('lorem ipsum dolor sit amet. ', 200); // 1000 words = 5 min
        $product = $this->makeProduct([
            'type' => 'blog',
            'metadata' => ['body_markdown' => $body],
        ]);
        $this->assertGreaterThanOrEqual(4, $product->readTime);
        $this->assertLessThanOrEqual(6, $product->readTime);
    }

    public function test_read_time_accessor_minimum_is_one(): void
    {
        $product = $this->makeProduct(['type' => 'blog', 'metadata' => ['body_markdown' => 'short']]);
        $this->assertEquals(1, $product->readTime);
    }

    // ── Helper method (not an accessor) ─────────────────────────────────

    public function test_is_published_helper_returns_correct_status(): void
    {
        $published = $this->makeProduct(['status' => 'published']);
        $draft = $this->makeProduct(['status' => 'draft']);

        $this->assertTrue($published->isPublished());
        $this->assertFalse($draft->isPublished());
    }

    // ── Trait structural assertion ─────────────────────────────────────

    public function test_product_model_uses_has_product_accessors_trait(): void
    {
        $traits = class_uses_recursive(Product::class);
        $this->assertContains(
            HasProductAccessors::class,
            $traits,
            'Product model must use HasProductAccessors trait (Phase 17 Task #7 refactor)'
        );
    }

    public function test_product_model_no_longer_defines_accessors_directly(): void
    {
        // After refactor, accessor methods should live in HasProductAccessors trait,
        // not on the Product class itself. PHP traits are merged at compile time
        // so Reflection's getDeclaringClass() returns Product for trait methods.
        // We use getFileName() to detect the actual file location.
        $reflection = new \ReflectionClass(Product::class);
        $productFile = realpath((new \ReflectionClass(Product::class))->getFileName());
        $traitFile = realpath((new \ReflectionClass(HasProductAccessors::class))->getFileName());

        $ownMethods = array_filter(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            fn ($m) => realpath($m->getFileName()) === $productFile
        );

        $ownMethodNames = array_map(fn ($m) => $m->getName(), $ownMethods);

        $accessorMethods = array_filter($ownMethodNames, fn ($name) => str_starts_with($name, 'get') && str_ends_with($name, 'Attribute')
        );

        $this->assertEmpty(
            $accessorMethods,
            'Product model must not define accessor methods directly — they should be in HasProductAccessors trait. Found: '.implode(', ', $accessorMethods)
        );

        // Also assert that trait file is NOT the same as Product file (sanity).
        $this->assertNotEquals($productFile, $traitFile, 'Trait and Product must be in different files');
    }
}
