<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\Dashboard\FulfillmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

// ───── Landing / Marketing ─────
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/faq', 'pages.faq')->name('faq');

// ───── SEO endpoints ─────
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::view('/about', 'pages.about')->name('about');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');

// Health check (must be BEFORE catch-all /{username} route to avoid shadowing)
// Returns 200 with subsystem status if healthy, 503 if any subsystem is down.
Route::get('/health', function () {
    $checks = [
        'app' => 'ok',
        'time' => now()->toIso8601String(),
        'env' => app()->environment(),
        'version' => app()->version(),
    ];

    $allHealthy = true;

    // Database check
    try {
        $dbStart = microtime(true);
        DB::select('SELECT 1');
        $checks['database'] = [
            'status' => 'ok',
            'latency_ms' => round((microtime(true) - $dbStart) * 1000, 2),
        ];
    } catch (Throwable $e) {
        $checks['database'] = ['status' => 'down', 'error' => $e->getMessage()];
        $allHealthy = false;
    }

    // Cache check
    try {
        $cacheKey = 'health:'.uniqid();
        Cache::put($cacheKey, '1', 5);
        $cacheValue = Cache::get($cacheKey);
        Cache::forget($cacheKey);
        if ($cacheValue === '1') {
            $checks['cache'] = ['status' => 'ok', 'driver' => config('cache.default')];
        } else {
            $checks['cache'] = ['status' => 'degraded', 'driver' => config('cache.default')];
            $allHealthy = false;
        }
    } catch (Throwable $e) {
        $checks['cache'] = ['status' => 'down', 'error' => $e->getMessage()];
        $allHealthy = false;
    }

    // Queue check (skip — sync driver always healthy)
    $queueDriver = config('queue.default');
    if ($queueDriver !== 'sync') {
        try {
            // Try to dispatch a "ping" without executing
            Queue::size();
            $checks['queue'] = ['status' => 'ok', 'driver' => $queueDriver, 'pending_jobs' => Queue::size()];
        } catch (Throwable $e) {
            $checks['queue'] = ['status' => 'down', 'error' => $e->getMessage()];
            $allHealthy = false;
        }
    } else {
        $checks['queue'] = ['status' => 'ok', 'driver' => 'sync'];
    }

    // Storage (public disk) check
    try {
        Storage::disk('public')->exists('.');
        $checks['storage'] = ['status' => 'ok', 'driver' => 'public'];
    } catch (Throwable $e) {
        $checks['storage'] = ['status' => 'down', 'error' => $e->getMessage()];
        $allHealthy = false;
    }

    return response()->json($checks + ['healthy' => $allHealthy], $allHealthy ? 200 : 503);
});

// ───── SEO endpoints (handled by SeoController) ─────
// /sitemap.xml and /robots.txt are registered near the top of this file

// ───── Auth ─────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');

    // Google OAuth
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

// ───── Authenticated ─────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // Products CRUD
        Route::resource('products', ProductController::class);
    });

    // Account / Profile settings
    Route::get('/settings/profile', [DashboardController::class, 'editProfile'])->name('settings.profile');
    Route::patch('/settings/profile', [DashboardController::class, 'updateProfile'])->name('settings.profile.update');
});

// ───── Public Profile ─────
// /{username} → creator's profile page
Route::get('/{username}', [PublicProfileController::class, 'show'])
    ->name('profile.show')
    ->where('username', '[a-zA-Z0-9._-]+');

// ───── Public Product + Checkout ─────
// /{username}/{productId} → product page
Route::get('/{username}/{productId}', [PublicProfileController::class, 'showProduct'])
    ->name('product.show')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}']);

// /{username}/{productId}/checkout → checkout page
Route::get('/{username}/{productId}/checkout', [PublicProfileController::class, 'checkout'])
    ->name('checkout.show')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}']);

// POST → create order, redirect to Duitku payment URL
Route::post('/{username}/{productId}/checkout', [PublicProfileController::class, 'processCheckout'])
    ->name('checkout.process')
    ->middleware('throttle:checkout')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}']);

// /{username}/{productId}/learn → course player (only after paid)
Route::get('/{username}/{productId}/learn', [CourseController::class, 'show'])
    ->name('course.show')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}']);

// /{username}/{productId}/learn/{moduleId} → watch specific module
Route::get('/{username}/{productId}/learn/{moduleId}', [CourseController::class, 'watch'])
    ->name('course.watch')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}', 'moduleId' => '[0-9]+']);

// POST → mark module complete
Route::post('/{username}/{productId}/learn/{moduleId}/complete', [CourseController::class, 'complete'])
    ->name('course.complete')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}', 'moduleId' => '[0-9]+']);

// /{username}/{productId}/ticket/{orderId} → buyer's event ticket
Route::get('/{username}/{productId}/ticket/{orderId}', [EventController::class, 'ticket'])
    ->name('event.ticket')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}', 'orderId' => 'ORD-[A-Z0-9-]+']);

// Dashboard: event check-in (auth required, creator-only)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/events/{username}/{productId}/checkin', [EventController::class, 'checkinDashboard'])
        ->name('event.checkin');
    Route::post('/dashboard/events/{username}/{productId}/checkin', [EventController::class, 'checkin'])
        ->name('event.checkin.post');
    Route::post('/dashboard/events/{username}/{productId}/walkin', [EventController::class, 'createWalkin'])
        ->name('event.walkin');
});

// Dashboard: shipping & fulfillment (auth required)
Route::middleware('auth')->prefix('/dashboard/fulfillment')->group(function () {
    Route::get('/', [FulfillmentController::class, 'index'])
        ->name('dashboard.fulfillment.index');
    Route::get('/{orderId}', [FulfillmentController::class, 'show'])
        ->name('dashboard.fulfillment.show')
        ->where('orderId', 'ORD-[A-Z0-9-]+');
    Route::post('/{orderId}', [FulfillmentController::class, 'update'])
        ->name('dashboard.fulfillment.update')
        ->where('orderId', 'ORD-[A-Z0-9-]+');
});

// /{username}/{productId}/add-to-cart
Route::post('/{username}/{productId}/add-to-cart', [CartController::class, 'add'])
    ->name('cart.add')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}']);

// /{username}/cart → view cart
Route::get('/{username}/cart', [CartController::class, 'show'])
    ->name('cart.show')
    ->where('username', '[a-zA-Z0-9._-]+');

// /{username}/cart → checkout cart
Route::get('/{username}/cart/checkout', [CartController::class, 'checkout'])
    ->name('cart.checkout')
    ->where('username', '[a-zA-Z0-9._-]+');

// POST /{username}/cart/checkout → process cart checkout
Route::post('/{username}/cart/checkout', [CartController::class, 'processCheckout'])
    ->name('cart.process')
    ->middleware('throttle:cart-checkout')
    ->where('username', '[a-zA-Z0-9._-]+');

// Cart item updates
Route::patch('/{username}/cart/items/{productId}', [CartController::class, 'update'])
    ->name('cart.update')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}']);
Route::delete('/{username}/cart/items/{productId}', [CartController::class, 'remove'])
    ->name('cart.remove')
    ->where(['username' => '[a-zA-Z0-9._-]+', 'productId' => '[a-z0-9]{12}']);

// Voucher
Route::post('/{username}/cart/voucher', [CartController::class, 'applyVoucher'])
    ->name('cart.voucher.apply')
    ->where('username', '[a-zA-Z0-9._-]+');
Route::delete('/{username}/cart/voucher', [CartController::class, 'removeVoucher'])
    ->name('cart.voucher.remove')
    ->where('username', '[a-zA-Z0-9._-]+');

// ───── Payment Callbacks (Duitku) ─────
Route::post('/payment/callback', [PaymentCallbackController::class, 'callback'])->name('payment.callback');
Route::get('/payment/success/{order}', [PaymentCallbackController::class, 'success'])->name('payment.success');
Route::get('/payment/failed/{order}', [PaymentCallbackController::class, 'failed'])->name('payment.failed');
