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
     * SECURITY: Tokens bind (order_id + buyer_email) — guessing only product_id is no longer enough.
     * Authenticated users are matched by buyer_user_id OR buyer_email.
     */
    private function getAccessOrder(Request $request, Product $product): ?Order
    {
        $user = $request->user();
        if (! $user) {
            // Guest access via signed token: order_id + buyer_email both signed.
            // Format: base64url(orderId|email) '.' hmac-sha256(orderId|email, app_key)
            $token = $request->query('token');
            if (! $token) {
                return null;
            }

            $parts = explode('.', $token, 2);
            if (count($parts) !== 2) {
                return null;
            }
            [$payload, $signature] = $parts;

            $decoded = base64_decode(strtr($payload, '-_', '+/'), true);
            if ($decoded === false || ! str_contains($decoded, '|')) {
                return null;
            }

            [$orderId, $email] = explode('|', $decoded, 2);
            $orderId = trim($orderId);
            $email = strtolower(trim($email));

            // Verify HMAC of (orderId + email + productId) so tokens don't cross products
            $expected = hash_hmac('sha256', $orderId.'|'.$email.'|'.$product->id, config('app.key'));
            if (! hash_equals($expected, $signature)) {
                return null;
            }

            return Order::where('id', $orderId)
                ->where('product_id', $product->id)
                ->where('buyer_email', $email)
                ->where('payment_status', 'paid')
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
     * Binds order_id + buyer_email + product_id so a token leaked for one course
     * cannot be reused for another course, even by the same buyer.
     */
    public static function generateAccessToken(Order $order): string
    {
        $payload = base64_encode($order->id.'|'.strtolower($order->buyer_email));
        $signature = hash_hmac('sha256', $order->id.'|'.strtolower($order->buyer_email).'|'.$order->product_id, config('app.key'));

        return $payload.'.'.$signature;
    }

    /**
     * Course player page (only accessible after purchase).
     */
    public function show(Request $request, string $username, string $productId)
    {
        $product = Product::with(['modules' => fn ($q) => $q->where('is_published', true), 'owner'])
            ->where('id', $productId)
            ->where('type', 'course')
            ->firstOrFail();

        // Verify owner username matches
        if ($product->owner->username !== $username) {
            abort(404);
        }

        $order = $this->getAccessOrder($request, $product);
        if (! $order) {
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

        if ($product->owner->username !== $username) {
            abort(404);
        }

        $order = $this->getAccessOrder($request, $product);
        if (! $order) {
            abort(403, 'Akses ditolak — purchase dulu.');
        }

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
        if (! $order) {
            abort(403);
        }

        DB::table('course_progress')->updateOrInsert(
            ['order_id' => $order->id, 'module_id' => $moduleId, 'buyer_email' => $order->buyer_email],
            ['is_completed' => true, 'completed_at' => now(), 'updated_at' => now(), 'created_at' => now()],
        );

        return response()->json(['ok' => true]);
    }
}
