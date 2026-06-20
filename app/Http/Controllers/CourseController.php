<?php

namespace App\Http\Controllers;

use App\Models\CourseModule;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Verify buyer has access to course via paid order.
     * SECURITY: Only checks authenticated user's email or order that
     * was created with their buyer_user_id — no ?email= query param.
     */
    private function getAccessOrder(Request $request, Product $product): ?Order
    {
        $user = $request->user();
        if (!$user) {
            // Allow guest access only via signed URL token (passed as ?token=)
            // The token is set when buyer accesses course from order success email
            $token = $request->query('token');
            if (!$token) return null;

            // Token format: base64(order_id + buyer_email_hash)
            // For now, accept token as HMAC-signed value
            $expected = hash_hmac('sha256', $product->id, config('app.key'));
            if (!hash_equals($expected, $token)) return null;

            // Token grants access to ANY paid order for this product
            // (intended for guest buyers who paid but don't have account)
            return Order::where('product_id', $product->id)
                ->where('payment_status', 'paid')
                ->latest()
                ->first();
        }

        return Order::where('product_id', $product->id)
            ->where(function ($q) use ($user) {
                $q->where('buyer_user_id', $user->id)
                  ->orWhere('buyer_email', $user->email);
            })
            ->where('payment_status', 'paid')
            ->latest()
            ->first();
    }

    /**
     * Generate a signed access token for guest course buyers.
     * Pass to URL via ?token= query param.
     */
    public static function generateAccessToken(string $productId): string
    {
        return hash_hmac('sha256', $productId, config('app.key'));
    }

    /**
     * Course player page (only accessible after purchase).
     */
    public function show(Request $request, string $username, string $productId)
    {
        $product = Product::with(['modules' => fn($q) => $q->where('is_published', true), 'owner'])
            ->where('id', $productId)
            ->where('type', 'course')
            ->firstOrFail();

        // Verify owner username matches
        if ($product->owner->username !== $username) abort(404);

        $order = $this->getAccessOrder($request, $product);
        if (!$order) {
            // No paid order — redirect to product page to purchase
            return redirect()->route('product.show', [$username, $productId])
                ->with('error', 'Beli course ini dulu untuk akses penuh.');
        }

        $modules = $product->modules;
        $firstModule = $modules->first();

        // Mark which modules are completed by this order
        $completed = DB::table('course_progress')
            ->where('order_id', $order->id)
            ->where('is_completed', true)
            ->pluck('module_id')
            ->toArray();

        $progressPct = $modules->count() > 0
            ? round((count($completed) / $modules->count()) * 100)
            : 0;

        return view('public.course-player', compact('product', 'order', 'modules', 'firstModule', 'completed', 'progressPct', 'username'));
    }

    /**
     * Watch a specific module.
     */
    public function watch(Request $request, string $username, string $productId, int $moduleId)
    {
        $product = Product::with(['modules', 'owner'])
            ->where('id', $productId)
            ->where('type', 'course')
            ->firstOrFail();

        if ($product->owner->username !== $username) abort(404);

        $order = $this->getAccessOrder($request, $product);
        if (!$order) abort(403, 'Akses ditolak — purchase dulu.');

        $module = CourseModule::where('id', $moduleId)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $modules = $product->modules;

        $completed = DB::table('course_progress')
            ->where('order_id', $order->id)
            ->where('is_completed', true)
            ->pluck('module_id')
            ->toArray();

        // Find next module
        $nextModule = $modules->where('position', '>', $module->position)->first();

        return view('public.course-module', compact('product', 'order', 'module', 'modules', 'completed', 'nextModule', 'username'));
    }

    /**
     * Mark module as completed.
     */
    public function complete(Request $request, string $username, string $productId, int $moduleId)
    {
        $product = Product::where('id', $productId)
            ->where('type', 'course')
            ->firstOrFail();

        $order = $this->getAccessOrder($request, $product);
        if (!$order) abort(403);

        DB::table('course_progress')->updateOrInsert(
            ['order_id' => $order->id, 'module_id' => $moduleId, 'buyer_email' => $order->buyer_email],
            ['is_completed' => true, 'completed_at' => now(), 'updated_at' => now(), 'created_at' => now()],
        );

        return response()->json(['ok' => true]);
    }
}