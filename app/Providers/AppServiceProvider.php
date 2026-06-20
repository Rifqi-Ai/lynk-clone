<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    /**
     * Configure the named rate limiters for the application.
     *
     * Named limiters (Laravel 11+ idiom) replace ad-hoc `RateLimiter::tooManyAttempts($key, N)`
     * checks scattered across controllers. Centralizing here gives:
     * - Single place to tune throttle limits
     * - Auto-applied via `throttle:<name>` middleware
     * - Clear documentation of why each limit exists
     */
    protected function configureRateLimiters(): void
    {
        // ───── Auth ─────

        // Login: 5 attempts/min per (IP + login-field) — prevents both generic brute force
        // and targeted account takeover via distributed sources.
        RateLimiter::for('login', function (Request $request) {
            $login = strtolower((string) $request->input('login', ''));

            return [
                Limit::perMinute(5)->by($request->ip().'|'.$login)->response(function () {
                    return back()->withErrors(['login' => 'Too many login attempts. Please try again in a minute.']);
                }),
                // Secondary limit by IP only — stops attackers from using random usernames
                // to evade the per-account limit.
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        // Register: 3 attempts/hour per IP — bot protection (real users rarely re-register).
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(3)->by($request->ip())->response(function () {
                return back()->withErrors(['email' => 'Too many registration attempts. Please try again later.']);
            });
        });

        // ───── Product CRUD ─────

        // Product creation: 20/hour per user — DoS prevention (real creators don't spam-create).
        RateLimiter::for('product-create', function (Request $request) {
            return Limit::perHour(20)->by($request->user()?->id ?: $request->ip())
                ->response(fn () => back()->withErrors(['title' => 'Too many products created. Please slow down.']));
        });

        // ───── Checkout ─────

        // Single-product checkout: 5/min per IP — stops checkout-spam abuse.
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())
                ->response(fn () => back()->withErrors(['payer_email' => 'Too many attempts. Please try again later.']));
        });

        // Cart checkout: 5/min per IP — same protection.
        RateLimiter::for('cart-checkout', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())
                ->response(fn () => back()->withErrors(['payer_email' => 'Too many attempts. Please try again later.']));
        });

        // ───── General ─────

        // Global web throttle: 240/min per IP — DoS mitigation. Already applied via bootstrap/app.php
        // but exposing it here as a named limiter so it can be referenced in route definitions
        // (`->middleware('throttle:web')`) if needed.
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(240)->by($request->ip());
        });
    }
}
