@extends('layouts.dashboard')

@section('title', 'Overview')
@section('header', 'Overview')
@section('subheader', 'Ringkasan performa toko kamu.')

@section('actions')
    <a href="{{ route('dashboard.products.create') }}" class="btn-cta text-sm h-10">
        <x-heroicon-o-plus class="w-4 h-4" />
        Produk Baru
    </a>
@endsection

@section('content')

{{-- ─── Stats row ─── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 stagger">
    {{-- Products --}}
    <div class="stat-card group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-ink-500 uppercase tracking-wider">Products</p>
                <p class="mt-2 text-3xl font-bold text-ink-900 tracking-tight">{{ $stats['total_products'] }}</p>
                <p class="text-xs text-ink-500 mt-1">{{ $stats['published_products'] }} published</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                <x-heroicon-o-cube class="w-5 h-5" />
            </div>
        </div>
    </div>

    {{-- Sales --}}
    <div class="stat-card group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-ink-500 uppercase tracking-wider">Sales</p>
                <p class="mt-2 text-3xl font-bold text-ink-900 tracking-tight">{{ $stats['total_sales'] }}</p>
                <p class="text-xs text-ink-500 mt-1">{{ $stats['pending_orders'] }} pending</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-success/10 text-success flex items-center justify-center group-hover:scale-110 transition-transform">
                <x-heroicon-o-shopping-bag class="w-5 h-5" />
            </div>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="stat-card group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-ink-500 uppercase tracking-wider">Revenue</p>
                <p class="mt-2 text-2xl font-bold text-ink-900 tracking-tight">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                <p class="text-xs text-ink-500 mt-1">After {{ auth()->user()->transaction_fee_pct }}% fee</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                <x-heroicon-s-banknotes class="w-5 h-5" />
            </div>
        </div>
    </div>

    {{-- Views --}}
    <div class="stat-card group">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-ink-500 uppercase tracking-wider">Views</p>
                <p class="mt-2 text-3xl font-bold text-ink-900 tracking-tight">{{ number_format($stats['profile_views']) }}</p>
                <p class="text-xs text-ink-500 mt-1">All products</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                <x-heroicon-o-eye class="w-5 h-5" />
            </div>
        </div>
    </div>
</div>

{{-- ─── Charts ─── --}}
<div class="grid lg:grid-cols-2 gap-4 mt-6">
    {{-- Revenue chart --}}
    <div class="bg-white rounded-2xl border border-ink-200 p-5 md:p-6 shadow-card">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                        <x-heroicon-s-banknotes class="w-4 h-4" />
                    </div>
                    <h2 class="font-bold text-ink-900">Revenue</h2>
                </div>
                <p class="text-xs text-ink-500 mt-1">Daily net earnings, last 30 days</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-brand-600">Rp {{ number_format(collect($revenueChart)->sum('amount'), 0, ',', '.') }}</p>
                <p class="text-[10px] text-ink-500 uppercase tracking-wider">Last 30d</p>
            </div>
        </div>
        <div class="relative" style="height: 200px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Sales chart --}}
    <div class="bg-white rounded-2xl border border-ink-200 p-5 md:p-6 shadow-card">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-success/10 text-success flex items-center justify-center">
                        <x-heroicon-o-shopping-cart class="w-4 h-4" />
                    </div>
                    <h2 class="font-bold text-ink-900">Sales</h2>
                </div>
                <p class="text-xs text-ink-500 mt-1">Daily order count, last 30 days</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-ink-900">{{ collect($salesChart)->sum('count') }}</p>
                <p class="text-[10px] text-ink-500 uppercase tracking-wider">Total orders</p>
            </div>
        </div>
        <div class="relative" style="height: 200px;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>

{{-- ─── Top products + Sales by type ─── --}}
<div class="grid lg:grid-cols-2 gap-4 mt-4">
    {{-- Top products --}}
    <div class="bg-white rounded-2xl border border-ink-200 p-5 md:p-6 shadow-card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-ink-900 flex items-center gap-2">
                <x-heroicon-s-trophy class="w-5 h-5 text-amber-500" />
                Top Products
            </h2>
            <a href="{{ route('dashboard.products.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat semua →</a>
        </div>
        @if ($topProducts->isEmpty() || $topProducts->sum('paid_orders_sum_creator_payout') == 0)
            <div class="empty-state !py-10">
                <div class="empty-state-icon !w-12 !h-12">
                    <x-heroicon-o-chart-bar class="w-6 h-6 text-ink-400" />
                </div>
                <p class="mt-3 text-sm text-ink-500">Belum ada penjualan</p>
                <p class="text-xs text-ink-400 mt-1">Mulai jualan untuk lihat top products di sini</p>
            </div>
        @else
            <div class="space-y-2.5">
                @foreach ($topProducts as $product)
                    @php $rev = $product->paid_orders_sum_creator_payout ?? 0; @endphp
                    @if ($rev > 0)
                        <a href="{{ $product->url }}" class="flex items-center gap-3 p-2 -mx-2 rounded-xl hover:bg-ink-50 transition-colors group">
                            <div class="w-10 h-10 flex-shrink-0 rounded-xl overflow-hidden bg-gradient-to-br from-brand-50 to-brand-100">
                                @if ($product->thumbnail_url)
                                    <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-brand-600">
                                        <x-heroicon-o-cube class="w-5 h-5" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-ink-900 truncate group-hover:text-brand-600 transition-colors">{{ $product->title }}</p>
                                <p class="text-xs text-ink-500">{{ $product->sales_count }} sales</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-brand-600">Rp {{ number_format($rev, 0, ',', '.') }}</p>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Sales by type --}}
    <div class="bg-white rounded-2xl border border-ink-200 p-5 md:p-6 shadow-card">
        <h2 class="font-bold text-ink-900 mb-4 flex items-center gap-2">
            <x-heroicon-o-chart-pie class="w-5 h-5 text-brand-600" />
            Sales by Type
        </h2>
        @if (empty($salesByType) || collect($salesByType)->sum('count') == 0)
            <div class="empty-state !py-10">
                <div class="empty-state-icon !w-12 !h-12">
                    <x-heroicon-o-inbox class="w-6 h-6 text-ink-400" />
                </div>
                <p class="mt-3 text-sm text-ink-500">Belum ada data</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($salesByType as $row)
                    @php
                        $total = collect($salesByType)->sum('count');
                        $pct = $total > 0 ? ($row['count'] / $total) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="font-semibold text-ink-900">{{ $row['label'] }}</span>
                            <span class="text-ink-500"><span class="font-bold text-ink-900">{{ $row['count'] }}</span> · {{ round($pct) }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-brand-500 to-brand-700 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ─── Recent orders ─── --}}
@if (!empty($recentOrders) && $recentOrders->count() > 0)
    <div class="bg-white rounded-2xl border border-ink-200 p-5 md:p-6 shadow-card mt-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-ink-900 flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-ink-500" />
                Recent Orders
            </h2>
            <a href="{{ route('dashboard.fulfillment.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto -mx-5 md:-mx-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-ink-500 uppercase tracking-wider border-b border-ink-100">
                        <th class="text-left font-semibold px-5 md:px-6 py-3">Order</th>
                        <th class="text-left font-semibold px-3 py-3">Product</th>
                        <th class="text-left font-semibold px-3 py-3">Buyer</th>
                        <th class="text-right font-semibold px-3 py-3">Total</th>
                        <th class="text-right font-semibold px-5 md:px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($recentOrders as $order)
                        <tr class="hover:bg-ink-50 transition-colors">
                            <td class="px-5 md:px-6 py-3">
                                <span class="font-mono text-xs text-ink-700">#{{ $order->order_number ?? substr($order->id, 0, 8) }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <a href="{{ $order->product?->url ?? '#' }}" class="font-semibold text-ink-900 hover:text-brand-600 truncate inline-block max-w-[200px]">{{ $order->product?->title ?? '(deleted)' }}</a>
                            </td>
                            <td class="px-3 py-3 text-ink-700 text-xs">{{ $order->buyer_email }}</td>
                            <td class="px-3 py-3 text-right font-bold text-ink-900 whitespace-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-5 md:px-6 py-3 text-right">
                                @php
                                    $statusColors = [
                                        'paid' => 'bg-success/10 text-success',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'failed' => 'bg-error/10 text-error',
                                        'refunded' => 'bg-ink-100 text-ink-700',
                                        'shipped' => 'bg-blue-100 text-blue-700',
                                        'delivered' => 'bg-brand-100 text-brand-700',
                                    ];
                                    $cls = $statusColors[$order->status] ?? 'bg-ink-100 text-ink-700';
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $cls }}">{{ $order->status }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ─── Setup checklist (if new account) ─── --}}
@if ($stats['total_products'] == 0)
    <div class="mt-6 bg-gradient-to-br from-brand-50 to-accent/20 rounded-2xl border border-brand-200 p-6 md:p-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white flex-shrink-0">
                <x-heroicon-s-rocket-launch class="w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-ink-900">Selamat datang! 🚀</h3>
                <p class="text-sm text-ink-600 mt-1">Yuk mulai dengan 3 langkah cepat ini:</p>
                <ol class="mt-4 space-y-2.5">
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-white border-2 border-brand-300 text-brand-700 font-bold text-xs flex items-center justify-center">1</span>
                        <span class="text-sm text-ink-700"><a href="{{ route('dashboard.profile') }}" class="font-semibold text-brand-700 hover:underline">Lengkapi profil</a> kamu (bio, avatar, sosial)</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-white border-2 border-brand-300 text-brand-700 font-bold text-xs flex items-center justify-center">2</span>
                        <span class="text-sm text-ink-700"><a href="{{ route('dashboard.products.create') }}" class="font-semibold text-brand-700 hover:underline">Buat produk pertama</a> kamu</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-white border-2 border-brand-300 text-brand-700 font-bold text-xs flex items-center justify-center">3</span>
                        <span class="text-sm text-ink-700">Share link {{ '@' . auth()->user()->username }} di bio Instagram/TikTok</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const brandColor = '#FF6B35';
    const successColor = '#10B981';

    // Revenue chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json(collect($revenueChart)->map(fn($r) => (array) $r)->pluck('date')),
                datasets: [{
                    label: 'Revenue',
                    data: @json(collect($revenueChart)->map(fn($r) => (array) $r)->pluck('amount')),
                    borderColor: brandColor,
                    backgroundColor: brandColor + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: brandColor,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1C1917',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: (ctx) => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#78716C', font: { size: 10 } } },
                    y: { grid: { color: '#F5F5F4' }, ticks: { color: '#78716C', font: { size: 10 }, callback: (v) => 'Rp ' + (v/1000) + 'K' } }
                }
            }
        });
    }

    // Sales chart
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: @json(collect($salesChart)->map(fn($r) => (array) $r)->pluck('date')),
                datasets: [{
                    label: 'Sales',
                    data: @json(collect($salesChart)->map(fn($r) => (array) $r)->pluck('count')),
                    backgroundColor: successColor,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1C1917',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#78716C', font: { size: 10 } } },
                    y: { grid: { color: '#F5F5F4' }, beginAtZero: true, ticks: { color: '#78716C', font: { size: 10 }, stepSize: 1 } }
                }
            }
        });
    }
});
</script>
@endpush
