<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Behavior tests for DashboardController.
 *
 * Coverage: index() (stats, charts, top products, sales by type)
 * and updateProfile() (validation, phone normalization, avatar handling).
 *
 * The original PaymentCallbackBehaviorTest pattern — cover the positive paths
 * the security test never reached. Here we cover the dashboard data the
 * view template relies on.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
    }

    public function test_dashboard_shows_zero_stats_for_new_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertViewHas('stats.total_products', 0);
        $response->assertViewHas('stats.published_products', 0);
        $response->assertViewHas('stats.total_sales', 0);
        $response->assertViewHas('stats.total_revenue', 0.0);
        $response->assertViewHas('stats.pending_orders', 0);
        $response->assertViewHas('stats.profile_views', 0);
    }

    public function test_dashboard_aggregates_paid_orders_revenue(): void
    {
        $creator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'status' => 'published',
        ]);

        // 3 paid orders
        Order::factory()->count(3)->paid()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'creator_payout' => 100000,
        ]);

        // 1 pending order (should NOT count)
        Order::factory()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
            'creator_payout' => 999999,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($creator);
        $response = $this->get('/dashboard');

        $response->assertViewHas('stats.total_sales', 3);
        $response->assertViewHas('stats.total_revenue', 300000.0);
        $response->assertViewHas('stats.pending_orders', 1);
    }

    public function test_dashboard_counts_only_own_products_and_orders(): void
    {
        $creator = User::factory()->create();
        $otherCreator = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $creator->id,
            'type' => 'digital',
            'status' => 'published',
        ]);

        // Creator's own orders
        Order::factory()->count(2)->paid()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
        ]);

        // Other creator's orders (must NOT be counted)
        $otherProduct = Product::factory()->create([
            'user_id' => $otherCreator->id,
            'type' => 'digital',
        ]);
        Order::factory()->count(5)->paid()->create([
            'product_id' => $otherProduct->id,
            'creator_user_id' => $otherCreator->id,
        ]);

        $this->actingAs($creator);
        $response = $this->get('/dashboard');

        $response->assertViewHas('stats.total_sales', 2);
    }

    public function test_dashboard_revenue_chart_always_has_30_days(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $revenueChart = $response->viewData('revenueChart');
        $this->assertCount(30, $revenueChart, 'Revenue chart should always have 30 days');

        $salesChart = $response->viewData('salesChart');
        $this->assertCount(30, $salesChart, 'Sales chart should always have 30 days');
    }

    public function test_dashboard_chart_fills_missing_days_with_zero(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $revenueChart = $response->viewData('revenueChart');

        // Every day should have an 'amount' key (defaulting to 0).
        foreach ($revenueChart as $day) {
            $this->assertArrayHasKey('amount', $day);
            $this->assertArrayHasKey('date', $day);
            $this->assertArrayHasKey('label', $day);
            $this->assertEquals(0.0, $day['amount']);
        }
    }

    public function test_dashboard_returns_top_5_products_by_revenue(): void
    {
        $creator = User::factory()->create();

        // 7 products with varying revenue
        $products = collect();
        for ($i = 1; $i <= 7; $i++) {
            $product = Product::factory()->create([
                'user_id' => $creator->id,
                'type' => 'digital',
            ]);
            Order::factory()->count($i)->paid()->create([
                'product_id' => $product->id,
                'creator_user_id' => $creator->id,
                'creator_payout' => 10000,
            ]);
            $products->push($product);
        }

        $this->actingAs($creator);
        $response = $this->get('/dashboard');

        $topProducts = $response->viewData('topProducts');
        $this->assertCount(5, $topProducts, 'Top products should be limited to 5');
    }

    public function test_dashboard_returns_sales_by_type_breakdown(): void
    {
        $creator = User::factory()->create();
        $digital = Product::factory()->create(['user_id' => $creator->id, 'type' => 'digital']);
        $course = Product::factory()->create(['user_id' => $creator->id, 'type' => 'course']);

        Order::factory()->count(3)->paid()->create([
            'product_id' => $digital->id,
            'creator_user_id' => $creator->id,
        ]);
        Order::factory()->count(2)->paid()->create([
            'product_id' => $course->id,
            'creator_user_id' => $creator->id,
        ]);

        $this->actingAs($creator);
        $response = $this->get('/dashboard');

        $salesByType = $response->viewData('salesByType');
        $types = $salesByType->pluck('type')->toArray();
        $this->assertContains('digital', $types);
        $this->assertContains('course', $types);
    }

    public function test_dashboard_recent_orders_eager_loads_product_and_buyer(): void
    {
        // Regression: N+1 prevention. View template should NOT trigger
        // extra queries per recent order.
        $creator = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $creator->id, 'type' => 'digital']);
        Order::factory()->count(5)->paid()->create([
            'product_id' => $product->id,
            'creator_user_id' => $creator->id,
        ]);

        $this->actingAs($creator);

        // Enable query log.
        \DB::enableQueryLog();
        $this->get('/dashboard');
        $queries = \DB::getQueryLog();

        // Should be a reasonable number — definitely NOT 5+ queries to
        // load product/buyer for each of 5 orders.
        $selectQueries = collect($queries)->filter(fn ($q) => str_starts_with(trim($q['query']), 'select'));
        // If N+1 were present, we'd see 5+ separate SELECTs on orders/products/users.
        $this->assertLessThan(
            40,
            $selectQueries->count(),
            "Dashboard triggered {$selectQueries->count()} queries — possible N+1",
        );
    }

    // ─── updateProfile tests ─────────────────────────────────────

    public function test_update_profile_normalizes_phone_with_zero_prefix(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->actingAs($user);

        $this->patch('/settings/profile', [
            'name' => 'Test User',
            'phone' => '08123456789',
        ])->assertRedirect();

        $this->assertEquals('628123456789', $user->fresh()->phone);
    }

    public function test_update_profile_normalizes_phone_strips_non_digits(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->actingAs($user);

        $this->patch('/settings/profile', [
            'name' => 'Test User',
            'phone' => '+62 812-3456-789',
        ])->assertRedirect();

        // + and - and spaces stripped, then 0→62 prefix.
        $this->assertEquals('628123456789', $user->fresh()->phone);
    }

    public function test_update_profile_keeps_phone_already_in_international_format(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->actingAs($user);

        $this->patch('/settings/profile', [
            'name' => 'Test User',
            'phone' => '62812345678',
        ])->assertRedirect();

        $this->assertEquals('62812345678', $user->fresh()->phone);
    }

    public function test_update_profile_handles_avatar_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['avatar_path' => null]);
        $this->actingAs($user);

        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $this->patch('/settings/profile', [
            'name' => 'Test User',
            'avatar' => $avatar,
        ])->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_update_profile_deletes_old_avatar_when_uploading_new(): void
    {
        Storage::fake('public');
        $oldAvatarPath = 'avatars/old/test.jpg';
        Storage::disk('public')->put($oldAvatarPath, 'old content');

        $user = User::factory()->create(['avatar_path' => $oldAvatarPath]);
        $this->actingAs($user);

        $newAvatar = UploadedFile::fake()->image('new.jpg', 500, 500);

        $this->patch('/settings/profile', [
            'name' => 'Test User',
            'avatar' => $newAvatar,
        ])->assertRedirect();

        Storage::disk('public')->assertMissing($oldAvatarPath);
    }

    public function test_update_profile_rejects_oversized_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        // 3MB file > 2MB limit
        $oversized = UploadedFile::fake()->image('big.jpg')->size(3000);

        $this->patch('/settings/profile', [
            'name' => 'Test User',
            'avatar' => $oversized,
        ])->assertSessionHasErrors('avatar');
    }

    public function test_update_profile_rejects_invalid_phone_format(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->patch('/settings/profile', [
            'name' => 'Test User',
            'phone' => 'abc-not-a-phone',
        ])->assertSessionHasErrors('phone');
    }

    public function test_update_profile_validates_required_name(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->patch('/settings/profile', [
            // No name field
        ])->assertSessionHasErrors('name');
    }
}
