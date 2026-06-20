<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dashboard_without_500()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'testdash',
            'email' => 'dash@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // CRITICAL: must be 200, not 500
        $response->assertStatus(200);
    }

    public function test_dashboard_has_welcome_message_for_new_user()
    {
        $user = User::factory()->create([
            'username' => 'newbie',
            'email' => 'newbie@test.com',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat datang', false);
    }

    public function test_dashboard_has_setup_steps_with_correct_links()
    {
        $user = User::factory()->create([
            'username' => 'setupuser',
            'email' => 'setup@test.com',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        // Verify the profile setup link points to settings.profile (not broken route)
        $response->assertSee('href="http://localhost/settings/profile"', false);
    }
}
