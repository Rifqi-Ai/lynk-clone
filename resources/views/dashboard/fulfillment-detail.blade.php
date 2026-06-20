@extends('layouts.dashboard')

@section('title', 'Order ' . $order->id)
@section('header', 'Order ' . $order->id)
@section('subheader', $order->product->title . ' · ' . $order->paid_at->format('d M Y, H:i'))

@section('content')
@if (session('success'))
    <div class="alert alert-success mb-6">
        <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- Breadcrumb --}}
<div class="mb-4">
    <a href="{{ route('dashboard.fulfillment.index') }}" class="text-xs font-semibold text-ink-500 hover:text-brand-600 inline-flex items-center gap-1 transition-colors">
        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to fulfillment
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Buyer + Shipping info --}}
    <div class="space-y-4">
        {{-- Buyer --}}
        <div class="card-warm p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </div>
                <h2 class="font-black text-base text-ink-900">Buyer</h2>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-2">
                    <dt class="text-ink-500 flex-shrink-0">Email:</dt>
                    <dd><a href="mailto:{{ $order->buyer_email }}" class="text-brand-600 hover:underline font-semibold truncate">{{ $order->buyer_email }}</a></dd>
                </div>
                @if ($order->buyer)
                <div class="flex justify-between gap-2">
                    <dt class="text-ink-500 flex-shrink-0">Name:</dt>
                    <dd class="font-semibold text-ink-900">{{ $order->buyer->name }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <dt class="text-ink-500 flex-shrink-0">Quantity:</dt>
                    <dd><span class="badge badge-soft">{{ $order->quantity }}×</span></dd>
                </div>
                <div class="flex justify-between gap-2 pt-2 border-t border-ink-100 mt-2">
                    <dt class="text-ink-500 flex-shrink-0">Total:</dt>
                    <dd class="font-black text-lg text-gradient-brand">Rp {{ number_format($order->total, 0, ',', '.') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Shipping Address --}}
        @php $ship = data_get($order->metadata, 'shipping_address', []); @endphp
        <div class="card-warm p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                </div>
                <h2 class="font-black text-base text-ink-900">Shipping Address</h2>
            </div>
            <div class="text-sm space-y-1.5 leading-relaxed">
                <div class="font-bold text-base text-ink-900">{{ $ship['name'] ?? '-' }}</div>
                <div class="text-ink-600 font-mono">{{ $ship['phone'] ?? '-' }}</div>
                <div class="text-ink-700 mt-2">{{ $ship['address'] ?? '-' }}</div>
                <div class="text-ink-700">
                    {{ $ship['city'] ?? '' }}{{ isset($ship['province']) ? ', ' . $ship['province'] : '' }}
                    {{ isset($ship['postal_code']) ? ' ' . $ship['postal_code'] : '' }}
                </div>
                <div class="text-ink-500">{{ $ship['country'] ?? 'Indonesia' }}</div>
                @if (!empty($ship['notes']))
                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs">
                        <span class="font-black text-amber-800 uppercase tracking-wider text-[10px] block mb-1">Buyer Notes</span>
                        <span class="text-amber-900">{{ $ship['notes'] }}</span>
                    </div>
                @endif
            </div>
            @if (!empty($ship['phone']))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ship['phone']) }}"
                   target="_blank" rel="noopener" class="btn btn-success btn-block mt-4">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
                    Chat Buyer via WhatsApp
                </a>
            @endif
        </div>
    </div>

    {{-- Update status --}}
    <div class="space-y-4">
        <div class="card-warm p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                </div>
                <h2 class="font-black text-base text-ink-900">Update Shipping</h2>
            </div>

            @php
                $currentStatus = data_get($order->metadata, 'shipping_status', 'pending');
                $currentTracking = data_get($order->metadata, 'tracking_number');
                $currentCourier = data_get($order->metadata, 'courier');
            @endphp

            <form method="POST" action="{{ route('dashboard.fulfillment.update', $order->id) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-ink-900 mb-1.5" for="shipping_status">Status</label>
                    <select id="shipping_status" name="shipping_status" class="select select-bordered w-full" required>
                        <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>⏳ Pending — belum diproses</option>
                        <option value="packed" {{ $currentStatus === 'packed' ? 'selected' : '' }}>📦 Packed — siap dikirim</option>
                        <option value="shipped" {{ $currentStatus === 'shipped' ? 'selected' : '' }}>🚚 Shipped — dalam pengiriman</option>
                        <option value="delivered" {{ $currentStatus === 'delivered' ? 'selected' : '' }}>✅ Delivered — sudah sampai</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-ink-900 mb-1.5" for="courier">Kurir</label>
                    <select id="courier" name="courier" class="select select-bordered w-full">
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
                    <label class="block text-sm font-bold text-ink-900 mb-1.5" for="tracking_number">Nomor Resi / Tracking</label>
                    <input id="tracking_number" type="text" name="tracking_number" value="{{ $currentTracking }}" class="input input-bordered w-full font-mono" placeholder="JP1234567890">
                </div>

                <div>
                    <label class="block text-sm font-bold text-ink-900 mb-1.5" for="shipping_notes">Notes (optional)</label>
                    <textarea id="shipping_notes" name="shipping_notes" rows="2" class="textarea textarea-bordered w-full" placeholder="Catatan internal">{{ data_get($order->metadata, 'shipping_notes') }}</textarea>
                </div>

                <button type="submit" class="btn-cta w-full shadow-cta">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    Update Status
                </button>
            </form>
        </div>

        {{-- Status timeline --}}
        <div class="card-warm p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-ink-700 to-ink-900 text-white flex items-center justify-center">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <h2 class="font-black text-base text-ink-900">Timeline</h2>
            </div>

            <ol class="space-y-3 text-sm">
                <li class="flex gap-3 items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-success text-white flex items-center justify-center mt-0.5">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    <div>
                        <div class="font-bold">Order paid</div>
                        <div class="text-xs text-ink-500">{{ $order->paid_at->format('d M Y, H:i') }}</div>
                    </div>
                </li>
                @if (in_array($currentStatus, ['packed', 'shipped', 'delivered']))
                <li class="flex gap-3 items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center mt-0.5">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    <div>
                        <div class="font-bold">Packed</div>
                        <div class="text-xs text-ink-500">Siap dikirim</div>
                    </div>
                </li>
                @endif
                @if (in_array($currentStatus, ['shipped', 'delivered']))
                <li class="flex gap-3 items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-500 text-white flex items-center justify-center mt-0.5">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    <div>
                        <div class="font-bold">Shipped</div>
                        @if (data_get($order->metadata, 'shipped_at'))
                            <div class="text-xs text-ink-500">{{ \Carbon\Carbon::parse(data_get($order->metadata, 'shipped_at'))->format('d M Y, H:i') }}</div>
                        @endif
                    </div>
                </li>
                @endif
                @if ($currentStatus === 'delivered')
                <li class="flex gap-3 items-start">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-success text-white flex items-center justify-center mt-0.5">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    <div>
                        <div class="font-bold">Delivered 🎉</div>
                        @if (data_get($order->metadata, 'delivered_at'))
                            <div class="text-xs text-ink-500">{{ \Carbon\Carbon::parse(data_get($order->metadata, 'delivered_at'))->format('d M Y, H:i') }}</div>
                        @endif
                    </div>
                </li>
                @endif
            </ol>
        </div>
    </div>
</div>
@endsection