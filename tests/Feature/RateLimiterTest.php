<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Tests for the named rate limiters in AppServiceProvider.
 *
 * Phase 9 improvement: rate limits moved from ad-hoc controller checks
 * to named limiters via RateLimiter::for() + throttle:<name> middleware.
 */
class RateLimiterTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_named_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('login');
        $this->assertNotNull($limiter, 'login named limiter should be registered');

        // Invoke with a fake request
        $request = Request::create('/login', 'POST', ['login' => 'test@example.com']);
        $request->server->set('REMOTE_ADDR', '10.0.0.1');

        $result = $limiter($request);
        // Should return Limit array (2 limits: per-IP+login + per-IP only)
        $this->assertIsArray($result);
        $this->assertCount(2, $result, 'login limiter should have 2 limits: per-(IP+login) and per-IP');
    }

    public function test_register_named_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('register');
        $this->assertNotNull($limiter, 'register named limiter should be registered');

        $request = Request::create('/register', 'POST');
        $result = $limiter($request);

        $this->assertNotNull($result);
        $this->assertEquals(3, $result->maxAttempts, 'register should be limited to 3/hour');
    }

    public function test_checkout_named_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('checkout');
        $this->assertNotNull($limiter, 'checkout named limiter should be registered');

        $request = Request::create('/checkout', 'POST');
        $result = $limiter($request);

        $this->assertNotNull($result);
        $this->assertEquals(5, $result->maxAttempts, 'checkout should be limited to 5/min');
    }

    public function test_cart_checkout_named_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('cart-checkout');
        $this->assertNotNull($limiter, 'cart-checkout named limiter should be registered');
    }

    public function test_product_create_named_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('product-create');
        $this->assertNotNull($limiter, 'product-create named limiter should be registered');
    }

    public function test_web_named_limiter_is_registered(): void
    {
        $limiter = RateLimiter::limiter('web');
        $this->assertNotNull($limiter, 'web named limiter should be registered');
    }

    public function test_login_limiter_uses_ip_and_login_field_for_keying(): void
    {
        $limiter = RateLimiter::limiter('login');

        $reqA = Request::create('/login', 'POST', ['login' => 'alice@example.com']);
        $reqA->server->set('REMOTE_ADDR', '10.0.0.1');
        $reqB = Request::create('/login', 'POST', ['login' => 'bob@example.com']);
        $reqB->server->set('REMOTE_ADDR', '10.0.0.1');
        $reqC = Request::create('/login', 'POST', ['login' => 'alice@example.com']);
        $reqC->server->set('REMOTE_ADDR', '10.0.0.2');

        $limitsA = $limiter($reqA);
        $limitsB = $limiter($reqB);
        $limitsC = $limiter($reqC);

        // First limit of each is keyed by (IP + login)
        $this->assertNotEquals($limitsA[0]->key, $limitsB[0]->key, 'Different login → different key');
        $this->assertNotEquals($limitsA[0]->key, $limitsC[0]->key, 'Different IP → different key');
    }
}
