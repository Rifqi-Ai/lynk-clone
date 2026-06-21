<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /** How many days of revenue/sales data to show on the dashboard. */
    private const CHART_DAYS = 30;

    /** Number of top products to show in the leaderboard. */
    private const TOP_PRODUCTS_LIMIT = 5;

    /** Number of recent products/orders to show in the sidebar. */
    private const RECENT_ITEMS_LIMIT = 5;

    /** Storage disk for user-uploaded files. */
    private const FILE_DISK = 'public';

    /**
     * Dashboard overview: stats + recent products + recent orders + charts.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = $this->buildStats($user);
        $revenueChart = $this->buildDailyRevenueChart($user);
        $salesChart = $this->buildDailySalesChart($user);
        $topProducts = $this->getTopProducts($user);
        $salesByType = $this->getSalesByType($user);
        ['products' => $recentProducts, 'orders' => $recentOrders] = $this->getRecentItems($user);

        return view('dashboard.index', compact(
            'stats', 'recentProducts', 'recentOrders',
            'revenueChart', 'salesChart', 'topProducts', 'salesByType',
        ));
    }

    /**
     * Build the headline stats card (single pass per resource).
     */
    private function buildStats(User $user): array
    {
        $productStats = $user->products()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = 'published' THEN view_count ELSE 0 END) as total_views
            ")
            ->first();

        $orderStats = $user->ordersAsCreator()
            ->selectRaw("
                SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN payment_status = 'paid' THEN creator_payout ELSE 0 END) as paid_revenue,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count
            ")
            ->first();

        return [
            'total_products' => (int) ($productStats->total ?? 0),
            'published_products' => (int) ($productStats->published ?? 0),
            'total_sales' => (int) ($orderStats->paid_count ?? 0),
            'total_revenue' => (float) ($orderStats->paid_revenue ?? 0),
            'pending_orders' => (int) ($orderStats->pending_count ?? 0),
            'profile_views' => (int) ($productStats->total_views ?? 0),
        ];
    }

    /**
     * Build a 30-day revenue chart, filling missing days with 0.
     */
    private function buildDailyRevenueChart(User $user): array
    {
        $startDate = now()->subDays(self::CHART_DAYS - 1)->startOfDay();
        $daily = $user->ordersAsCreator()
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, SUM(creator_payout) as amount')
            ->groupBy('date')
            ->pluck('amount', 'date')
            ->toArray();

        return $this->fillMissingDays($daily, fn ($amount) => [
            'date' => $amount['date'],
            'label' => $amount['label'],
            'amount' => (float) $amount['value'],
        ]);
    }

    /**
     * Build a 30-day sales count chart, filling missing days with 0.
     */
    private function buildDailySalesChart(User $user): array
    {
        $startDate = now()->subDays(self::CHART_DAYS - 1)->startOfDay();
        $daily = $user->ordersAsCreator()
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE(paid_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return $this->fillMissingDays($daily, fn ($day) => [
            'date' => $day['date'],
            'label' => $day['label'],
            'count' => (int) $day['value'],
        ]);
    }

    /**
     * Fill in zeros for days that have no data, ensuring the chart
     * always has CHART_DAYS entries. $map transforms each day into the
     * shape the chart expects.
     */
    private function fillMissingDays(array $daily, callable $map): array
    {
        $result = [];
        for ($i = self::CHART_DAYS - 1; $i >= 0; $i--) {
            $carbon = now()->subDays($i);
            $date = $carbon->format('Y-m-d');
            $result[] = $map([
                'date' => $date,
                'label' => $carbon->format('d M'),
                'value' => $daily[$date] ?? 0,
            ]);
        }

        return $result;
    }

    /**
     * Get the top N products ranked by paid-order revenue (single JOIN query).
     */
    private function getTopProducts(User $user)
    {
        return $user->products()
            ->withSum(['paidOrders' => fn ($q) => $q->where('payment_status', 'paid')], 'creator_payout')
            ->orderByDesc('paid_orders_sum_creator_payout')
            ->limit(self::TOP_PRODUCTS_LIMIT)
            ->get();
    }

    /**
     * Group paid sales by product type with label/icon metadata.
     */
    private function getSalesByType(User $user)
    {
        return Order::where('creator_user_id', $user->id)
            ->where('payment_status', 'paid')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->selectRaw('products.type, COUNT(*) as count, SUM(orders.creator_payout) as revenue')
            ->groupBy('products.type')
            ->get()
            ->map(fn ($row) => [
                'type' => $row->type,
                'count' => (int) $row->count,
                'revenue' => (float) $row->revenue,
                'label' => Product::TYPES[$row->type]['label'] ?? ucfirst($row->type),
                'icon' => Product::TYPES[$row->type]['icon'] ?? '📦',
            ]);
    }

    /**
     * Get recent products and orders (eager-loads product+buyer to avoid N+1).
     */
    private function getRecentItems(User $user): array
    {
        return [
            'products' => $user->products()->latest()->limit(self::RECENT_ITEMS_LIMIT)->get(),
            'orders' => $user->ordersAsCreator()
                ->with(['product', 'buyer'])
                ->latest()
                ->limit(self::RECENT_ITEMS_LIMIT)
                ->get(),
        ];
    }

    /**
     * Profile/appearance editor.
     */
    public function editProfile(Request $request)
    {
        return view('dashboard.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update profile (name, bio, phone, avatar, social links).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $this->validateProfile($request);

        if (! empty($data['phone'])) {
            $data['phone'] = $this->normalizePhoneNumber($data['phone']);
        }

        $data['whatsapp_opt_in'] = $request->boolean('whatsapp_opt_in');

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $this->storeAvatar($request->file('avatar'), $user);
        }
        unset($data['avatar']);

        $user->update($data);

        return redirect()->route('settings.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Validation rules for the profile update form.
     */
    private function validateProfile(Request $request): array
    {
        return $request->validate([
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
    }

    /**
     * Normalize a user-entered phone number to international format
     * (62xxxxxxxxx for Indonesia). Strips non-digits, then replaces a
     * leading 0 with the 62 country code.
     */
    private function normalizePhoneNumber(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Store a new avatar and delete the old one. Returns the new path.
     */
    private function storeAvatar(UploadedFile $file, User $user): string
    {
        if ($user->avatar_path) {
            Storage::disk(self::FILE_DISK)->delete($user->avatar_path);
        }

        return $file->store("avatars/{$user->id}", self::FILE_DISK);
    }
}
