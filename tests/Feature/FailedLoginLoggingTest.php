<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FailedLoginLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // partialMock keeps the real Log implementation for everything we don't
        // explicitly override. We need this because the RequestId middleware calls
        // Log::shareContext() on every request — a strict Mockery mock would reject it.
        Log::partialMock();
    }

    public function test_failed_login_writes_warning_log(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'auth.login.failed'
                    && ($context['event'] ?? null) === 'login.failed'
                    && ($context['login_attempted'] ?? null) === 'attacker@test.com'
                    && isset($context['ip'])
                    && array_key_exists('user_agent', $context);
            });

        $response = $this->from('/login')->post('/login', [
            'login' => 'attacker@test.com',
            'password' => 'wrong-password',
            'website' => '',
        ]);

        $response->assertSessionHasErrors('login');
    }

    public function test_successful_login_writes_info_log(): void
    {
        $user = User::factory()->create([
            'email' => 'real@test.com',
            'username' => 'realuser',
            'password' => bcrypt('password123'),
        ]);

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) use ($user) {
                return $message === 'auth.login.succeeded'
                    && ($context['event'] ?? null) === 'login.succeeded'
                    && ($context['user_id'] ?? null) === $user->id
                    && isset($context['ip']);
            });

        $response = $this->post('/login', [
            'login' => 'real@test.com',
            'password' => 'password123',
            'website' => '',
        ]);

        $response->assertStatus(302);
    }

    public function test_failed_login_log_does_not_contain_password(): void
    {
        $captured = [];

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) use (&$captured) {
                $captured[] = ['message' => $message, 'context' => $context];

                return true;
            });

        $this->from('/login')->post('/login', [
            'login' => 'spy@test.com',
            'password' => 'supersecret123',
            'website' => '',
        ]);

        $this->assertNotEmpty($captured, 'Expected a Log::warning to have been captured.');
        $serialized = serialize($captured);
        $this->assertStringNotContainsString(
            'supersecret123',
            $serialized,
            'Password value must NEVER appear in any log context (P0 security).'
        );
    }
}
