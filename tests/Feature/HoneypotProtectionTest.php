<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HoneypotProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_has_hidden_honeypot_field(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        // Bots read this attribute and dutifully fill it; humans never see/fill it.
        $response->assertSee('name="website"', false);
    }

    public function test_register_form_has_hidden_honeypot_field(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('name="website"', false);
    }

    public function test_login_rejects_request_with_filled_honeypot(): void
    {
        // Even with REAL credentials, a filled honeypot means "bot".
        User::factory()->create([
            'email' => 'real@test.com',
            'username' => 'realuser',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => 'real@test.com',
            'password' => 'password123',
            'website' => 'http://spam.com', // Bot filled the hidden field
        ]);

        // Bot must NOT be logged in — this is the security guarantee.
        $this->assertGuest();

        // Bot must NOT be redirected to the protected dashboard.
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
    }

    public function test_register_rejects_request_with_filled_honeypot(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Bot User',
            'username' => 'botuser',
            'email' => 'bot@spam.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'website' => 'http://spam.com', // Bot filled the hidden field
        ]);

        // Bot must NOT be logged in.
        $this->assertGuest();

        // Bot must NOT be redirected to the protected dashboard.
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors();

        // User must NOT be created in DB.
        $this->assertDatabaseMissing('users', ['email' => 'bot@spam.com']);
    }

    public function test_login_succeeds_without_honeypot_filled(): void
    {
        $user = User::factory()->create([
            'email' => 'human@test.com',
            'username' => 'humanuser',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'human@test.com',
            'password' => 'password123',
            'website' => '', // Humans leave the hidden field empty.
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
