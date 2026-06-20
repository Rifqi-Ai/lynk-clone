<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Dashboard overview: stats + recent products + recent orders.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_products' => $user->products()->count(),
            'published_products' => $user->products()->where('status', 'published')->count(),
            'total_sales' => $user->ordersAsCreator()->where('payment_status', 'paid')->count(),
            'total_revenue' => $user->ordersAsCreator()->where('payment_status', 'paid')->sum('creator_payout'),
            'pending_orders' => $user->ordersAsCreator()->where('payment_status', 'pending')->count(),
            'profile_views' => $user->products()->where('status', 'published')->sum('view_count'),
        ];

        // Last 30 days revenue chart (per day) — single query
        $startDate = now()->subDays(29)->startOfDay();
        $dailyRevenue = $user->ordersAsCreator()
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, SUM(creator_payout) as amount, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        $dailySales = $user->ordersAsCreator()
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $revenueChart = [];
        $salesChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $revenueChart[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d M'),
                'amount' => (float) ($dailyRevenue[$date] ?? 0),
            ];
            $salesChart[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d M'),
                'count' => (int) ($dailySales[$date] ?? 0),
            ];
        }

        // Top 5 products by revenue
        $topProducts = $user->products()
            ->withSum(['paidOrders' => fn ($q) => $q->where('payment_status', 'paid')], 'creator_payout')
            ->orderByDesc('paid_orders_sum_creator_payout')
            ->limit(5)
            ->get();

        // Sales by product type
        $salesByType = Order::where('creator_user_id', $user->id)
            ->where('payment_status', 'paid')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->selectRaw('products.type, COUNT(*) as count, SUM(orders.creator_payout) as revenue')
            ->groupBy('products.type')
            ->get()
            ->map(fn ($r) => [
                'type' => $r->type,
                'count' => (int) $r->count,
                'revenue' => (float) $r->revenue,
                'label' => Product::TYPES[$r->type]['label'] ?? ucfirst($r->type),
                'icon' => Product::TYPES[$r->type]['icon'] ?? '📦',
            ]);

        $recentProducts = $user->products()->latest()->limit(5)->get();
        $recentOrders = $user->ordersAsCreator()->with(['product', 'buyer'])->latest()->limit(5)->get();

        return view('dashboard.index', compact(
            'stats', 'recentProducts', 'recentOrders',
            'revenueChart', 'salesChart', 'topProducts', 'salesByType'
        ));
    }

    /**
     * Profile/appearance editor.
     */
    public function editProfile(Request $request)
    {
        $user = $request->user();

        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'whatsapp_opt_in' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.platform' => ['required', 'string', 'in:instagram,tiktok,twitter,youtube,facebook,linkedin,website'],
            'social_links.*.url' => ['required', 'url'],
        ]);

        // Normalize phone number
        if (! empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (str_starts_with($phone, '0')) {
                $phone = '62'.substr($phone, 1);
            }
            $data['phone'] = $phone;
        }

        $data['whatsapp_opt_in'] = $request->boolean('whatsapp_opt_in');

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store("avatars/{$user->id}", 'public');
        }
        unset($data['avatar']);

        $user->update($data);

        return redirect()->route('settings.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
