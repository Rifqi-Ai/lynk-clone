<?php

use App\Http\Middleware\RequestId;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude payment gateway callbacks from CSRF (they come from external servers)
        $middleware->validateCsrfTokens(except: [
            'payment/callback',
            'payment/*/callback',
        ]);

        // Add security headers (X-Frame-Options, X-Content-Type-Options, etc.)
        $middleware->append(SecurityHeaders::class);

        // Add X-Request-ID to every request for tracing / log correlation
        $middleware->prepend(RequestId::class);

        // SECURITY: Apply global throttle (240 req/min per IP) to mitigate DoS / scraping.
        // Laravel's built-in 'throttle' middleware uses config('cache') so works without extra setup.
        $middleware->web(append: [
            ThrottleRequests::class.':240,1',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
