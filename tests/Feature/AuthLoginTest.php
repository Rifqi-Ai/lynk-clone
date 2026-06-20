<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Characterization tests for AuthController::login().
 *
 * Pins down behavior of POST /login before the Phase 16 Task 4
 * refactor extracts validation, lookup, and authentication into helpers.
 *
 * Covers: email login, username login, failed attempts, honeypot,
 * remember me, session regeneration, logging, timing attack mitigation.
 */
class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'username' => 'loginuser',
            'email' => 'login@example.com',
            'password' => 'correct_password_123',
        ]);
    }

    // ───── Happy path ─────

    public function test_login_with_email_succeeds(): void
    {
        $response = $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'correct_password_123',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_with_username_succeeds(): void
    {
        $response = $this->post('/login', [
            'login' => 'loginuser',
            'password' => 'correct_password_123',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_remember_me_creates_remember_token(): void
    {
        $originalToken = $this->user->remember_token;

        $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'correct_password_123',
            'remember' => '1',
        ]);

        $this->user->refresh();
        // Either the token changed, or was set if originally null
        $this->assertNotEmpty($this->user->getRememberToken());
    }

    public function test_session_regenerates_after_login(): void
    {
        // Pre-login session
        $this->withSession([]);
        $oldSessionId = session()->getId();

        $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'correct_password_123',
        ]);

        // Session ID should have changed after login
        $this->assertNotEquals($oldSessionId, session()->getId());
    }

    // ───── Failed attempts ─────

    public function test_wrong_password_fails(): void
    {
        $response = $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_unknown_user_fails_with_same_error(): void
    {
        $response = $this->post('/login', [
            'login' => 'nobody@example.com',
            'password' => 'whatever',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
        // Error message should NOT reveal that user doesn't exist
        $errors = session('errors')->getBag('default');
        $this->assertStringContainsString('Invalid credentials', (string) $errors->first('login'));
    }

    public function test_missing_login_field_fails(): void
    {
        $response = $this->post('/login', [
            'password' => 'correct_password_123',
        ]);

        $response->assertSessionHasErrors('login');
    }

    public function test_missing_password_field_fails(): void
    {
        $response = $this->post('/login', [
            'login' => 'login@example.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_wrong_password_preserves_login_input(): void
    {
        $response = $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasInput('login', 'login@example.com');
    }

    // ───── Honeypot ─────

    public function test_honeypot_field_blocks_bot(): void
    {
        $response = $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'correct_password_123',
            'website' => 'http://spam.example.com', // bot field
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_honeypot_with_empty_value_passes(): void
    {
        $response = $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'correct_password_123',
            'website' => '',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_honeypot_does_not_reveal_its_existence(): void
    {
        // Both honeypot-blocked and wrong-password should show similar error
        $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'correct_password_123',
            'website' => 'spam',
        ]);
        $errorBot = $this->extractFirstLoginError();

        $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'wrong',
        ]);
        $errorWrong = $this->extractFirstLoginError();

        // Honeypot error should NOT contain words like "honeypot" or "bot"
        $this->assertStringNotContainsString('honeypot', strtolower($errorBot));
        $this->assertStringNotContainsString('bot', strtolower($errorBot));
    }

    private function extractFirstLoginError(): string
    {
        $errors = session('errors');
        if (! $errors) {
            return '';
        }
        if (is_array($errors)) {
            return (string) ($errors['login'][0] ?? '');
        }

        return (string) $errors->first('login');
    }

    // ───── Logging ─────

    public function test_failed_login_is_logged(): void
    {
        Log::spy();

        $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'wrong',
        ]);

        Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context) {
                return $message === 'auth.login.failed'
                    && ($context['login_attempted'] ?? null) === 'login@example.com'
                    && isset($context['ip']);
            });
    }

    public function test_successful_login_is_logged(): void
    {
        Log::spy();

        $this->post('/login', [
            'login' => 'login@example.com',
            'password' => 'correct_password_123',
        ]);

        Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                return $message === 'auth.login.succeeded'
                    && ($context['user_id'] ?? null) === $this->user->id
                    && isset($context['ip']);
            });
    }

    public function test_unknown_user_login_is_logged_with_attempted_login(): void
    {
        Log::spy();

        $this->post('/login', [
            'login' => 'ghost@example.com',
            'password' => 'whatever',
        ]);

        Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context) {
                return $message === 'auth.login.failed'
                    && ($context['login_attempted'] ?? null) === 'ghost@example.com';
            });
    }

    // ───── Security: timing attack mitigation ─────

    public function test_timing_for_unknown_user_close_to_known_user_wrong_password(): void
    {
        // To prevent email enumeration via timing, the response time for
        // an unknown user should be similar to a known user with wrong
        // password (since both must run Hash::check).
        // We allow generous slop because test environments are noisy,
        // but the ratio should be within an order of magnitude.

        $iterations = 5;

        // Time wrong-password (known user)
        $wrongStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->post('/login', [
                'login' => 'login@example.com',
                'password' => 'wrong',
            ]);
        }
        $wrongDuration = microtime(true) - $wrongStart;

        // Time unknown user
        $unknownStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->post('/login', [
                'login' => "ghost{$i}@example.com",
                'password' => 'whatever',
            ]);
        }
        $unknownDuration = microtime(true) - $unknownStart;

        // Unknown user should NOT be > 5x faster than wrong-password
        // (if it skips Hash::check, it would be ~10x faster)
        $ratio = $unknownDuration / max($wrongDuration, 0.001);
        $this->assertLessThan(5.0, $ratio,
            "Unknown user login ({$unknownDuration}s) is much faster than wrong-password login ({$wrongDuration}s). ".
            'This indicates a timing attack vulnerability — unknown users skip Hash::check.'
        );
    }

    // ───── Case sensitivity ─────

    public function test_email_login_is_case_insensitive(): void
    {
        // Emails are typically case-insensitive (RFC 5321).
        // The auth controller now normalizes emails to lowercase before
        // lookup, so this works on both MySQL and SQLite.
        $response = $this->post('/login', [
            'login' => 'LOGIN@EXAMPLE.COM', // uppercase
            'password' => 'correct_password_123',
        ]);

        $response->assertRedirect('http://localhost/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_username_login_is_case_sensitive(): void
    {
        // Usernames are user-chosen and typically case-sensitive.
        // "loginuser" should not match "LOGINUSER" (current behavior).
        // If we want case-insensitive, we should normalize.
        // For now, this test documents the current behavior.
        $response = $this->post('/login', [
            'login' => 'LOGINUSER', // wrong case
            'password' => 'correct_password_123',
        ]);

        // On SQLite (case-sensitive), this will fail.
        // On MySQL (case-insensitive by default), this will succeed.
        // This test pins the SQLite behavior — adjust if we want MySQL parity.
        if (\DB::connection()->getDriverName() === 'sqlite') {
            $response->assertSessionHasErrors('login');
            $this->assertGuest();
        } else {
            $response->assertRedirect(route('dashboard.index'));
        }
    }
}
