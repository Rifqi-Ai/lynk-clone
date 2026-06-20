<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_is_accessible(): void
    {
        $user = User::factory()->create(['username' => 'testcreator']);

        $response = $this->get('/testcreator');
        $response->assertOk();
        $response->assertSee('testcreator');
    }

    public function test_nonexistent_profile_returns_404(): void
    {
        $response = $this->get('/nonexistent-user-12345');
        $response->assertStatus(404);
    }

    public function test_draft_products_are_hidden_from_public(): void
    {
        $user = User::factory()->create(['username' => 'creator']);
        Product::factory()->create([
            'user_id' => $user->id,
            'type' => 'digital',
            'title' => 'Published One',
            'status' => 'published',
        ]);
        Product::factory()->create([
            'user_id' => $user->id,
            'type' => 'digital',
            'title' => 'Draft Secret',
            'status' => 'draft',
        ]);

        $response = $this->get('/creator');
        $response->assertOk();
        $response->assertSee('Published One');
        $response->assertDontSee('Draft Secret');
    }

    public function test_filter_count_matches_actual_displayed_products(): void
    {
        $user = User::factory()->create(['username' => 'creator']);
        Product::factory()->create([
            'user_id' => $user->id,
            'type' => 'digital',
            'status' => 'published',
            'title' => 'Digital 1',
        ]);
        Product::factory()->create([
            'user_id' => $user->id,
            'type' => 'course',
            'status' => 'published',
            'title' => 'Course 1',
        ]);

        // No filter → all 2 products
        $response = $this->get('/creator');
        $response->assertOk();
        $response->assertSee('Digital 1');
        $response->assertSee('Course 1');

        // Filter by type=digital → only 1 product
        $response = $this->get('/creator?type=digital');
        $response->assertOk();
        $response->assertSee('Digital 1');
        $response->assertDontSee('Course 1');
    }

    public function test_search_uses_like_query_safely(): void
    {
        $user = User::factory()->create(['username' => 'creator']);
        Product::factory()->create([
            'user_id' => $user->id,
            'type' => 'digital',
            'status' => 'published',
            'title' => 'Laravel Ebook',
        ]);
        Product::factory()->create([
            'user_id' => $user->id,
            'type' => 'digital',
            'status' => 'published',
            'title' => 'Python Ebook',
        ]);

        $response = $this->get('/creator?q=Laravel');
        $response->assertOk();
        $response->assertSee('Laravel Ebook');
        $response->assertDontSee('Python Ebook');
    }

    public function test_sql_injection_attempt_in_search_is_handled_safely(): void
    {
        $user = User::factory()->create(['username' => 'creator']);
        Product::factory()->create([
            'user_id' => $user->id,
            'type' => 'digital',
            'status' => 'published',
            'title' => 'Test Product',
        ]);

        // SQL injection attempt — should be treated as literal text
        $response = $this->get("/creator?q=' OR 1=1--");
        $response->assertOk();
        // Should not crash; either returns no results or normal results
    }
}
