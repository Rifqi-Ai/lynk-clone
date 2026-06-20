@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('header', 'Overview')

@section('content')
{{-- Top stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <div class="card p-4 sm:p-5">
        <div class="text-xs text-ink-500 font-bold uppercase tracking-wider">Products</div>
        <div class="text-2xl sm:text-3xl font-black mt-2">{{ $stats['total_products'] }}</div>
        <div class="text-xs text-ink-500 mt-1">{{ $stats['published_products'] }} published</div>
    </div>
    <div class="card p-4 sm:p-5">
        <div class="text-xs text-ink-500 font-bold uppercase tracking-wider">Sales</div>
        <div class="text-2xl sm:text-3xl font-black mt-2">{{ $stats['total_sales'] }}</div>
        <div class="text-xs text-ink-500 mt-1">{{ $stats['pending_orders'] }} pending</div>
    </div>
    <div class="card p-4 sm:p-5">
        <div class="text-xs text-ink-500 font-bold uppercase tracking-wider">Revenue</div>
        <div class="text-xl sm:text-3xl font-black mt-2 text-brand-500">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
        <div class="text-xs text-ink-500 mt-1">After {{ auth()->user()->transaction_fee_pct }}% fee</div>
    </div>
    <div class="card p-4 sm:p-5">
        <div class="text-xs text-ink-500 font-bold uppercase tracking-wider">Views</div>
        <div class="text-2xl sm:text-3xl font-black mt-2">{{ number_format($stats['profile_views']) }}</div>
        <div class="text-xs text-ink-500 mt-1">All products</div>
    </div>
</div>

{{-- Charts --}}
<div class="grid lg:grid-cols-2 gap-4 mt-6">
    {{-- Revenue chart (last 30 days) --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-black text-lg">💰 Revenue (30 days)</h2>
                <p class="text-xs text-ink-500 mt-0.5">Daily net earnings</p>
            </div>
            <div class="text-right">
                <div class="text-lg font-black text-brand-500">Rp {{ number_format(array_sum(array_column($revenueChart, 'amount')), 0, ',', '.') }}</div>
                <div class="text-xs text-ink-500">Last 30 days</div>
            </div>
        </div>
        <div class="relative" style="height: 180px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Sales chart --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-black text-lg">📈 Sales (30 days)</h2>
                <p class="text-xs text-ink-500 mt-0.5">Daily order count</p>
            </div>
            <div class="text-right">
                <div class="text-lg font-black">{{ array_sum(array_column($salesChart, 'count')) }}</div>
                <div class="text-xs text-ink-500">orders total</div>
            </div>
        </div>
        <div class="relative" style="height: 180px;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>

{{-- Top products + Sales by type --}}
<div class="grid lg:grid-cols-2 gap-4 mt-4">
    {{-- Top products --}}
    <div class="card p-5">
        <h2 class="font-black text-lg mb-4">🏆 Top Products</h2>
        @if ($topProducts->isEmpty() || $topProducts->sum('paid_orders_sum_creator_payout') == 0)
            <div class="text-center py-8 text-sm text-ink-500">
                <div class="text-3xl mb-2">📊</div>
                Belum ada penjualan.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($topProducts as $product)
                    @php $rev = $product->paid_orders_sum_creator_payout ?? 0; @endphp
                    @if ($rev > 0)
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-ink-100 flex items-center justify-center text-base flex-shrink-0">
                                @if ($product->thumbnail_url)<img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover rounded-lg">@else{{ $product->typeIcon }}@endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm truncate">{{ $product->title }}</div>
                                <div class="text-xs text-ink-500">{{ $product->sales_count }} sales</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-brand-500">Rp {{ number_format($rev, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Sales by type --}}
    <div class="card p-5">
        <h2 class="font-black text-lg mb-4">🎯 Sales by Type</h2>
        @if ($salesByType->isEmpty())
            <div class="text-center py-8 text-sm text-ink-500">
                <div class="text-3xl mb-2">📊</div>
                Belum ada penjualan.
            </div>
        @else
            <div class="space-y-3">
                @php $maxRev = max(1, $salesByType->max('revenue')); @endphp
                @foreach ($salesByType as $type)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-bold">{{ $type['icon'] }} {{ $type['label'] }}</span>
                            <span class="font-black text-brand-500">Rp {{ number_format($type['revenue'], 0, ',', '.') }} <span class="text-xs text-ink-500 font-normal">({{ $type['count'] }})</span></span>
                        </div>
                        <div class="h-2 bg-ink-100 rounded-full overflow-hidden">
                            <div class="h-full bg-brand-500 rounded-full transition-all" style="width: {{ ($type['revenue'] / $maxRev) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Recent products + orders --}}
<div class="grid lg:grid-cols-2 gap-4 mt-4">
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-black text-lg">Recent Products</h2>
            <a href="{{ route('dashboard.products.create') }}" class="btn-primary btn-sm">+ New</a>
        </div>
        @if ($recentProducts->isEmpty())
            <div class="text-center py-8 text-sm text-ink-500">
                <div class="text-3xl mb-2">📦</div>
                No products yet. <a href="{{ route('dashboard.products.create') }}" class="text-brand-500 font-bold">Create your first one</a>.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($recentProducts as $product)
                    <a href="{{ route('dashboard.products.edit', $product) }}" class="flex items-center gap-3 p-3 rounded-lg border border-ink-100 hover:border-ink-200 transition">
                        <div class="w-10 h-10 rounded-lg bg-ink-100 flex items-center justify-center text-xl flex-shrink-0">
                            @if ($product->thumbnail_url)<img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover rounded-lg">@else📦@endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm truncate">{{ $product->title }}</div>
                            <div class="text-xs text-ink-500">Rp {{ number_format($product->price, 0, ',', '.') }} · {{ $product->sales_count }} sales</div>
                        </div>
                        @if ($product->status === 'published')
                            <span class="badge-success">Live</span>
                        @else
                            <span class="badge-neutral">Draft</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-black text-lg">Recent Orders</h2>
        </div>
        @if ($recentOrders->isEmpty())
            <div class="text-center py-8 text-sm text-ink-500">
                <div class="text-3xl mb-2">💰</div>
                No orders yet. Share your page to start selling.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($recentOrders as $order)
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-ink-100">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm truncate">{{ $order->product->title }}</div>
                            <div class="text-xs text-ink-500 truncate">{{ $order->buyer_email }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-black text-brand-500">+Rp {{ number_format($order->creator_payout, 0, ',', '.') }}</div>
                            @if ($order->payment_status === 'paid')
                                <span class="badge-success">Paid</span>
                            @elseif ($order->payment_status === 'pending')
                                <span class="badge-warning">Pending</span>
                            @else
                                <span class="badge-danger">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Quick share card --}}
<div class="card p-5 mt-6 bg-gradient-to-br from-brand-50 to-brand-100/40 border-brand-200">
    <h3 class="font-black">Your public page</h3>
    <p class="text-sm text-ink-700 mt-1">Share this link with your audience:</p>
    <div class="mt-3 flex items-center gap-2">
        <input type="text" readonly value="{{ auth()->user()->profile_url }}"
               class="input flex-1 font-mono text-sm bg-white" onclick="this.select()">
        <a href="{{ auth()->user()->profile_url }}" target="_blank" class="btn-secondary btn-sm">Open</a>
    </div>
</div>
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1f2937',
                padding: 10,
                cornerRadius: 6,
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 8 } },
            y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: '#f3f4f6' } }
        }
    };

    // Revenue chart
    const revenueData = @json($revenueChart);
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(d => d.label),
                datasets: [{
                    label: 'Revenue',
                    data: revenueData.map(d => d.amount),
                    borderColor: '#2AB57D',
                    backgroundColor: 'rgba(42, 181, 125, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                ...chartConfig,
                scales: {
                    ...chartConfig.scales,
                    y: {
                        ...chartConfig.scales.y,
                        ticks: {
                            ...chartConfig.scales.y.ticks,
                            callback: v => 'Rp ' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v)
                        }
                    }
                }
            }
        });
    }

    // Sales chart
    const salesData = @json($salesChart);
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: salesData.map(d => d.label),
                datasets: [{
                    label: 'Orders',
                    data: salesData.map(d => d.count),
                    backgroundColor: 'rgba(42, 181, 125, 0.7)',
                    borderRadius: 4,
                }]
            },
            options: chartConfig
        });
    }
});
</script>
@endpush