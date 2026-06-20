@extends('layouts.dashboard')

@section('title', 'Shipping & Fulfillment')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-black">📦 Shipping & Fulfillment</h1>
        <p class="text-sm text-ink-500">Kelola pesanan produk fisik — packing, tracking, delivery.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="card p-4">
            <div class="text-xs text-ink-500 uppercase font-bold">Pending</div>
            <div class="text-2xl font-black mt-1 text-ink-700">{{ $stats['pending'] }}</div>
        </div>
        <div class="card p-4 bg-amber-50">
            <div class="text-xs text-ink-500 uppercase font-bold">Packed</div>
            <div class="text-2xl font-black mt-1 text-amber-700">{{ $stats['packed'] }}</div>
        </div>
        <div class="card p-4 bg-blue-50">
            <div class="text-xs text-ink-500 uppercase font-bold">Shipped</div>
            <div class="text-2xl font-black mt-1 text-blue-700">{{ $stats['shipped'] }}</div>
        </div>
        <div class="card p-4 bg-brand-50">
            <div class="text-xs text-ink-500 uppercase font-bold">Delivered</div>
            <div class="text-2xl font-black mt-1 text-brand-700">{{ $stats['delivered'] }}</div>
        </div>
    </div>

    {{-- Orders list --}}
    <div class="card overflow-hidden">
        <div class="px-4 py-3 border-b border-ink-100">
            <h2 class="font-bold">All Physical Orders ({{ $orders->total() }})</h2>
        </div>

        @if ($orders->count() > 0)
            <div class="divide-y divide-ink-100">
                @foreach ($orders as $order)
                    @php
                        $status = data_get($order->metadata, 'shipping_status', 'pending');
                        $statusColors = [
                            'pending' => 'bg-ink-100 text-ink-700',
                            'packed' => 'bg-amber-100 text-amber-700',
                            'shipped' => 'bg-blue-100 text-blue-700',
                            'delivered' => 'bg-brand-100 text-brand-700',
                        ];
                        $statusLabels = [
                            'pending' => '⏳ Pending',
                            'packed' => '📦 Packed',
                            'shipped' => '🚚 Shipped',
                            'delivered' => '✅ Delivered',
                        ];
                        $ship = data_get($order->metadata, 'shipping_address', []);
                    @endphp
                    <a href="{{ route('dashboard.fulfillment.show', $order->id) }}" class="block p-4 hover:bg-ink-50 transition">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-sm truncate">{{ $order->product->title }}</span>
                                    <span class="badge {{ $statusColors[$status] ?? '' }}">{{ $statusLabels[$status] ?? $status }}</span>
                                </div>
                                <div class="text-xs text-ink-500 truncate">
                                    {{ $ship['name'] ?? 'N/A' }} · {{ $ship['city'] ?? '' }} · {{ $order->quantity }}x
                                </div>
                                <div class="text-xs text-ink-400 mt-1">
                                    {{ $order->id }} · {{ $order->paid_at->format('d M Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                                <div class="text-xs text-brand-500 mt-1">View →</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="p-4">
                {{ $orders->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="text-5xl mb-3">📦</div>
                <h3 class="font-bold">Belum ada pesanan fisik</h3>
                <p class="text-sm text-ink-500 mt-1">Pesanan akan muncul di sini setelah buyer checkout produk fisik.</p>
            </div>
        @endif
    </div>
</div>
@endsection