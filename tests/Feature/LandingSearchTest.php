<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 17 Task #6 — Landing search feature.
 *
 * Closes docs/ux-audit-2026-06-21.md Trunk Test item:
 *   "✓ Search? Not present (gap — Phase 14)"
 *
 * Contract:
 *  - /search?q=foo returns a 200 page with matching creators + products
 *  - Empty query returns 200 with empty state (or 302 to landing)
 *  - Search results page has accessible markup (h1, role attributes)
 *  - Landing nav has a search form with input name="q" pointing to /search
 */
class LandingSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $creatorAlice;

    private User $creatorBob;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creatorAlice = User::factory()->create([
            'username' => 'alice',
            'name' => 'Alice Pratama',
            'bio' => 'Membantu kreator Indonesia monetize audiens.',
        ]);
        $this->creatorBob = User::factory()->create([
            'username' => 'bobby',
            'name' => 'Bobby Santoso',
            'bio' => 'Tech educator dan course creator.',
        ]);
    }

    public function test_search_route_exists_and_responds_200(): void
    {
        $response = $this->get('/search?q=alice');
        $response->assertStatus(200);
    }

    public function test_search_finds_creator_by_username(): void
    {
        // Creator must have at least one published product to show up in search
        // (filters noise — accounts that never published anything aren't useful results).
        Product::factory()->create([
            'user_id' => $this->creatorAlice->id,
            'status' => 'published',
            'type' => 'digital',
        ]);

        $response = $this->get('/search?q=alice');
        $response->assertStatus(200);
        $response->assertSee('alice', false);
        $response->assertSee('Alice Pratama', false);
    }

    public function test_search_finds_creator_by_name(): void
    {
        Product::factory()->create([
            'user_id' => $this->creatorAlice->id,
            'status' => 'published',
            'type' => 'digital',
        ]);

        $response = $this->get('/search?q=Pratama');
        $response->assertStatus(200);
        $response->assertSee('Alice Pratama', false);
    }

    public function test_search_finds_creator_by_bio_keyword(): void
    {
        Product::factory()->create([
            'user_id' => $this->creatorBob->id,
            'status' => 'published',
            'type' => 'digital',
        ]);

        $response = $this->get('/search?q=tech');
        $response->assertStatus(200);
        // Bobby's bio mentions 'tech'
        $response->assertSee('bobby', false);
    }

    public function test_search_finds_product_by_title(): void
    {
        Product::factory()->create([
            'user_id' => $this->creatorAlice->id,
            'title' => 'Notion Template Pack',
            'status' => 'published',
            'type' => 'digital',
        ]);

        $response = $this->get('/search?q=Notion');
        $response->assertStatus(200);
        $response->assertSee('Notion Template Pack', false);
    }

    public function test_search_only_returns_published_products(): void
    {
        Product::factory()->create([
            'user_id' => $this->creatorAlice->id,
            'title' => 'Draft Course',
            'status' => 'draft',
            'type' => 'course',
        ]);
        Product::factory()->create([
            'user_id' => $this->creatorAlice->id,
            'title' => 'Published Course',
            'status' => 'published',
            'type' => 'course',
        ]);

        $response = $this->get('/search?q=Course');
        $response->assertStatus(200);
        $response->assertSee('Published Course', false);
        $response->assertDontSee('Draft Course', false);
    }

    public function test_empty_search_returns_200_with_helpful_state(): void
    {
        $response = $this->get('/search?q=');
        $response->assertStatus(200);
        // Should show some guidance (not crash on empty query)
        $content = $response->getContent();
        $this->assertNotEmpty($content);
    }

    public function test_search_results_page_has_accessible_heading(): void
    {
        $response = $this->get('/search?q=alice');
        $response->assertStatus(200);
        // h1 announces page purpose (Indonesian: "Hasil pencarian")
        // The h1 may contain nested <span> for gradient text — use a permissive regex.
        $content = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<h1\b[^>]*>[\s\S]*?Hasil pencarian[\s\S]*?<\/h1>/',
            $content
        );
    }

    public function test_landing_page_has_search_form_in_nav(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        // The nav should contain a search form
        $this->assertStringContainsString('<form', $response->getContent());
        $this->assertStringContainsString('action="'.route('search').'"', $response->getContent());
        $this->assertStringContainsString('name="q"', $response->getContent());
    }

    public function test_search_input_has_accessible_label(): void
    {
        $response = $this->get('/');
        $content = $response->getContent();
        // aria-label or <label> for the search input
        $this->assertTrue(
            str_contains($content, 'aria-label="') || str_contains($content, '<label'),
            'Search input should have accessible label (aria-label or <label>)'
        );
    }
}
