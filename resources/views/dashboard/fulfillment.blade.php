@extends('layouts.dashboard')

@section('title', 'Shipping & Fulfillment')
@section('header', 'Shipping & Fulfillment')
@section('subheader', 'Kelola pesanan produk fisik — packing, tracking, delivery.')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="card-warm p-4 hover:shadow-card transition-shadow">
        <div class="flex items-center gap-2 text-xs text-ink-500 uppercase font-bold tracking-wider">
            <span class="w-2 h-2 rounded-full bg-ink-400"></span>
            Pending
        </div>
        <div class="text-3xl font-black mt-2 text-ink-700">{{ $stats['pending'] }}</div>
    </div>
    <div class="card-warm p-4 hover:shadow-card transition-shadow">
        <div class="flex items-center gap-2 text-xs text-ink-500 uppercase font-bold tracking-wider">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Packed
        </div>
        <div class="text-3xl font-black mt-2 text-amber-700">{{ $stats['packed'] }}</div>
    </div>
    <div class="card-warm p-4 hover:shadow-card transition-shadow">
        <div class="flex items-center gap-2 text-xs text-ink-500 uppercase font-bold tracking-wider">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            Shipped
        </div>
        <div class="text-3xl font-black mt-2 text-blue-700">{{ $stats['shipped'] }}</div>
    </div>
    <div class="card-warm p-4 hover:shadow-card transition-shadow bg-gradient-to-br from-success/5 to-emerald-50">
        <div class="flex items-center gap-2 text-xs text-ink-500 uppercase font-bold tracking-wider">
            <span class="w-2 h-2 rounded-full bg-success"></span>
            Delivered
        </div>
        <div class="text-3xl font-black mt-2 text-success">{{ $stats['delivered'] }}</div>
    </div>
</div>

{{-- Orders list --}}
<div class="card-warm overflow-hidden">
    <div class="px-5 py-4 border-b border-ink-100 flex items-center justify-between">
        <h2 class="font-black text-base text-ink-900">All Physical Orders</h2>
        <span class="badge badge-soft">{{ $orders->total() }} total</span>
    </div>

    @if ($orders->count() > 0)
        <ul class="divide-y divide-ink-100">
            @foreach ($orders as $order)
                @php
                    $status = data_get($order->metadata, 'shipping_status', 'pending');
                    $statusConfig = [
                        'pending' => ['bg-ink-100 text-ink-700', '⏳ Pending', 'bg-ink-400'],
                        'packed' => ['bg-amber-100 text-amber-700', '📦 Packed', 'bg-amber-500'],
                        'shipped' => ['bg-blue-100 text-blue-700', '🚚 Shipped', 'bg-blue-500'],
                        'delivered' => ['bg-success/15 text-success', '✅ Delivered', 'bg-success'],
                    ];
                    $cfg = $statusConfig[$status] ?? $statusConfig['pending'];
                    $ship = data_get($order->metadata, 'shipping_address', []);
                @endphp
                <li>
                    <a href="{{ route('dashboard.fulfillment.show', $order->id) }}" class="block p-4 sm:p-5 hover:bg-brand-50/30 transition-colors group">
                        <div class="flex items-start gap-4">
                            {{-- Status indicator --}}
                            <div class="flex-shrink-0 w-1 self-stretch rounded-full {{ $cfg[2] }} opacity-60 group-hover:opacity-100 transition-opacity"></div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                    <span class="font-bold text-sm text-ink-900 truncate max-w-xs">{{ $order->product->title }}</span>
                                    <span class="badge badge-sm {{ $cfg[0] }}">{{ $cfg[1] }}</span>
                                </div>
                                <div class="text-xs text-ink-600 flex items-center gap-1.5 flex-wrap">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                    {{ $ship['name'] ?? 'N/A' }}
                                    <span class="text-ink-300">·</span>
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    {{ $ship['city'] ?? '' }}
                                    <span class="text-ink-300">·</span>
                                    <span class="font-semibold">{{ $order->quantity }}×</span>
                                </div>
                                <div class="text-xs text-ink-400 mt-1.5 font-mono">
                                    {{ $order->id }} · {{ $order->paid_at->format('d M Y') }}
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0 flex flex-col items-end gap-1">
                                <div class="font-black text-base text-ink-900">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                                <div class="text-xs text-brand-600 font-bold flex items-center gap-1 group-hover:gap-2 transition-all">
                                    View
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="p-4 border-t border-ink-100">
            {{ $orders->links() }}
        </div>
    @else
        <div class="p-12 sm:p-16 text-center">
            <div class="inline-flex w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 items-center justify-center mb-4">
                <svg class="w-10 h-10 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
            </div>
            <h3 class="font-black text-lg text-ink-900">Belum ada pesanan fisik</h3>
            <p class="text-sm text-ink-500 mt-1.5 max-w-sm mx-auto">Pesanan akan muncul di sini setelah buyer checkout produk fisik kamu.</p>
        </div>
    @endif
</div>
@endsection