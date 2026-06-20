@extends('layouts.dashboard')

@section('title', 'Order ' . $order->id)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    @if (session('success'))
        <div class="mb-4 p-3 bg-brand-50 border border-brand-200 rounded-lg text-sm text-brand-700">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('dashboard.fulfillment.index') }}" class="text-xs text-ink-500 hover:text-brand-500">← Back to fulfillment</a>
        <div class="flex items-center justify-between gap-4 mt-2 flex-wrap">
            <div>
                <h1 class="text-2xl font-black">Order {{ $order->id }}</h1>
                <p class="text-sm text-ink-500">{{ $order->product->title }} · {{ $order->paid_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="text-right">
                <div class="text-xs text-ink-500">Total</div>
                <div class="text-2xl font-black text-brand-500">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Buyer + Shipping info --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="font-bold mb-3">👤 Buyer</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-500">Email:</span>
                        <a href="mailto:{{ $order->buyer_email }}" class="text-brand-500">{{ $order->buyer_email }}</a>
                    </div>
                    @if ($order->buyer)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Name:</span>
                            <span>{{ $order->buyer->name }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-ink-500">Quantity:</span>
                        <span>{{ $order->quantity }}x</span>
                    </div>
                </div>
            </div>

            @php $ship = data_get($order->metadata, 'shipping_address', []); @endphp
            <div class="card p-5">
                <h2 class="font-bold mb-3">📦 Shipping Address</h2>
                <div class="text-sm space-y-1">
                    <div class="font-bold">{{ $ship['name'] ?? '-' }}</div>
                    <div class="text-ink-600">{{ $ship['phone'] ?? '-' }}</div>
                    <div class="text-ink-700 mt-2">{{ $ship['address'] ?? '-' }}</div>
                    <div class="text-ink-700">
                        {{ $ship['city'] ?? '' }}{{ isset($ship['province']) ? ', ' . $ship['province'] : '' }}
                        {{ isset($ship['postal_code']) ? ' ' . $ship['postal_code'] : '' }}
                    </div>
                    <div class="text-ink-500">{{ $ship['country'] ?? 'Indonesia' }}</div>
                    @if (!empty($ship['notes']))
                        <div class="mt-2 p-2 bg-ink-50 rounded text-xs">
                            <span class="font-bold">Notes:</span> {{ $ship['notes'] }}
                        </div>
                    @endif
                </div>
                @if (!empty($ship['phone']))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ship['phone']) }}"
                       target="_blank" class="btn-secondary btn-sm btn-block mt-3">
                        💬 Chat via WhatsApp
                    </a>
                @endif
            </div>
        </div>

        {{-- Update status --}}
        <div class="card p-5">
            <h2 class="font-bold mb-3">🚚 Update Shipping</h2>
            @php
                $currentStatus = data_get($order->metadata, 'shipping_status', 'pending');
                $currentTracking = data_get($order->metadata, 'tracking_number');
                $currentCourier = data_get($order->metadata, 'courier');
            @endphp

            <form method="POST" action="{{ route('dashboard.fulfillment.update', $order->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="label">Status</label>
                    <select name="shipping_status" class="input" required>
                        <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>⏳ Pending — belum diproses</option>
                        <option value="packed" {{ $currentStatus === 'packed' ? 'selected' : '' }}>📦 Packed — siap dikirim</option>
                        <option value="shipped" {{ $currentStatus === 'shipped' ? 'selected' : '' }}>🚚 Shipped — dalam pengiriman</option>
                        <option value="delivered" {{ $currentStatus === 'delivered' ? 'selected' : '' }}>✅ Delivered — sudah sampai</option>
                    </select>
                </div>

                <div>
                    <label class="label">Kurir</label>
                    <select name="courier" class="input">
                        <option value="">— Pilih kurir —</option>
                        <option value="JNE" {{ $currentCourier === 'JNE' ? 'selected' : '' }}>JNE</option>
                        <option value="J&T" {{ $currentCourier === 'J&T' ? 'selected' : '' }}>J&T Express</option>
                        <option value="SiCepat" {{ $currentCourier === 'SiCepat' ? 'selected' : '' }}>SiCepat</option>
                        <option value="AnterAja" {{ $currentCourier === 'AnterAja' ? 'selected' : '' }}>AnterAja</option>
                        <option value="Pos" {{ $currentCourier === 'Pos' ? 'selected' : '' }}>Pos Indonesia</option>
                        <option value="GoSend" {{ $currentCourier === 'GoSend' ? 'selected' : '' }}>GoSend</option>
                        <option value="Grab" {{ $currentCourier === 'Grab' ? 'selected' : '' }}>GrabExpress</option>
                    </select>
                </div>

                <div>
                    <label class="label">Nomor Resi / Tracking</label>
                    <input type="text" name="tracking_number" value="{{ $currentTracking }}" class="input" placeholder="JP1234567890">
                </div>

                <div>
                    <label class="label">Notes (optional)</label>
                    <textarea name="shipping_notes" rows="2" class="input" placeholder="Catatan internal">{{ data_get($order->metadata, 'shipping_notes') }}</textarea>
                </div>

                <button type="submit" class="btn-primary btn-block">💾 Update Status</button>
            </form>

            {{-- Status timeline --}}
            <div class="mt-6 pt-4 border-t border-ink-100">
                <div class="text-xs font-bold text-ink-500 uppercase mb-3">Timeline</div>
                <div class="space-y-2 text-xs">
                    <div class="flex gap-2">
                        <span class="text-brand-500">✓</span>
                        <span>Order paid — {{ $order->paid_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if ($currentStatus !== 'pending')
                        <div class="flex gap-2">
                            <span class="text-amber-500">✓</span>
                            <span>Packed</span>
                        </div>
                    @endif
                    @if (in_array($currentStatus, ['shipped', 'delivered']))
                        <div class="flex gap-2">
                            <span class="text-blue-500">✓</span>
                            <span>Shipped {{ data_get($order->metadata, 'shipped_at') ? '— ' . \Carbon\Carbon::parse(data_get($order->metadata, 'shipped_at'))->format('d M Y, H:i') : '' }}</span>
                        </div>
                    @endif
                    @if ($currentStatus === 'delivered')
                        <div class="flex gap-2">
                            <span class="text-brand-500">✓</span>
                            <span>Delivered {{ data_get($order->metadata, 'delivered_at') ? '— ' . \Carbon\Carbon::parse(data_get($order->metadata, 'delivered_at'))->format('d M Y, H:i') : '' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection