<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FulfillmentController extends Controller
{
    /**
     * List all physical orders that need fulfillment.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $orders = Order::where('creator_user_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
            ->with('product')
            ->orderByRaw("JSON_EXTRACT(metadata, '$.shipping_status') ASC")
            ->orderBy('paid_at', 'desc')
            ->paginate(20);

        $stats = [
            'pending' => Order::where('creator_user_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
                ->whereJsonContains('metadata->shipping_status', 'pending')
                ->count(),
            'packed' => Order::where('creator_user_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
                ->whereJsonContains('metadata->shipping_status', 'packed')
                ->count(),
            'shipped' => Order::where('creator_user_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
                ->whereJsonContains('metadata->shipping_status', 'shipped')
                ->count(),
            'delivered' => Order::where('creator_user_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
                ->whereJsonContains('metadata->shipping_status', 'delivered')
                ->count(),
        ];

        return view('dashboard.fulfillment', compact('orders', 'stats'));
    }

    /**
     * Show single order detail with shipping info.
     */
    public function show(Request $request, string $orderId)
    {
        $user = Auth::user();
        $order = Order::where('creator_user_id', $user->id)
            ->where('id', $orderId)
            ->with('product', 'buyer')
            ->firstOrFail();

        return view('dashboard.fulfillment-detail', compact('order'));
    }

    /**
     * Update shipping status + tracking number.
     */
    public function update(Request $request, string $orderId)
    {
        $user = Auth::user();
        $order = Order::where('creator_user_id', $user->id)
            ->where('id', $orderId)
            ->where('payment_status', 'paid')
            ->whereHas('product', fn ($q) => $q->where('type', 'physical'))
            ->firstOrFail();

        $data = $request->validate([
            'shipping_status' => ['required', 'in:pending,packed,shipped,delivered'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'courier' => ['nullable', 'string', 'max:50'],
            'shipping_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $metadata = $order->metadata ?? [];
        $metadata['shipping_status'] = $data['shipping_status'];
        $metadata['tracking_number'] = $data['tracking_number'] ?? null;
        $metadata['courier'] = $data['courier'] ?? null;
        $metadata['shipping_notes'] = $data['shipping_notes'] ?? null;
        $metadata['shipping_updated_at'] = now()->toIso8601String();
        if ($data['shipping_status'] === 'shipped' && empty($metadata['shipped_at'])) {
            $metadata['shipped_at'] = now()->toIso8601String();
        }
        if ($data['shipping_status'] === 'delivered' && empty($metadata['delivered_at'])) {
            $metadata['delivered_at'] = now()->toIso8601String();
        }

        $order->update(['metadata' => $metadata]);

        return back()->with('success', '✅ Status pengiriman diupdate: '.strtoupper($data['shipping_status']));
    }
}
