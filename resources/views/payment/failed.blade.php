@extends('layouts.app')

@section('title', 'Pembayaran Gagal — Linka')

@section('content')
<section class="section flex items-center justify-center">
    <div class="container-narrow text-center">
        <div class="mx-auto w-20 h-20 rounded-full bg-error/10 flex items-center justify-center mb-6 animate-fade-up">
            <x-heroicon-s-exclamation-triangle class="w-10 h-10 text-error" />
        </div>

        <h1 class="text-display-md text-ink-900 text-balance animate-fade-up" style="animation-delay: 100ms">Pembayaran gagal</h1>
        <p class="mt-3 text-lg text-ink-600 text-pretty animate-fade-up" style="animation-delay: 200ms">
            Maaf, transaksi kamu tidak bisa diproses. Dana belum terpotong (atau sudah dikembalikan otomatis).
        </p>

        @if(isset($order) && $order)
        <div class="mt-8 bg-white rounded-2xl border border-ink-200 p-6 shadow-card animate-fade-up text-left" style="animation-delay: 300ms">
            <h2 class="font-bold text-ink-900 mb-3 flex items-center gap-2">
                <x-heroicon-s-receipt-refund class="w-5 h-5 text-error" />
                Detail Pesanan
            </h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                    <dt class="text-ink-500">Order ID</dt>
                    <dd class="font-mono text-ink-900">{{ $order->order_number ?? $order->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink-500">Produk</dt>
                    <dd class="font-semibold text-ink-900">{{ $order->product?->title }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink-500">Total</dt>
                    <dd class="font-bold text-ink-900">Rp {{ number_format($order->total, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink-500">Status</dt>
                    <dd><span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-error/10 text-error uppercase tracking-wider">Failed</span></dd>
                </div>
            </dl>
        </div>
        @endif

        <div class="mt-8 p-5 bg-amber-50 border border-amber-200 rounded-2xl text-left animate-fade-up" style="animation-delay: 350ms">
            <h3 class="font-bold text-amber-900 flex items-center gap-2">
                <x-heroicon-s-light-bulb class="w-4 h-4" />
                Kemungkinan penyebab
            </h3>
            <ul class="text-sm text-amber-800 mt-2 space-y-1 list-disc list-inside">
                <li>Saldo tidak cukup atau limit transaksi habis</li>
                <li>Koneksi terputus saat proses pembayaran</li>
                <li>Bank sedang maintenance</li>
                <li>Waktu pembayaran habis</li>
            </ul>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center animate-fade-up" style="animation-delay: 400ms">
            @if(isset($order) && $order->product)
            <a href="{{ $order->product->checkout_url }}" class="btn-cta">
                <x-heroicon-s-arrow-path class="w-4 h-4" />
                Coba Lagi
            </a>
            @endif
            <a href="{{ $creator->profile_url ?? '/' }}" class="btn-outline-ink">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali
            </a>
            <a href="#" class="btn-ghost-ink">
                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4" />
                Hubungi Support
            </a>
        </div>
    </div>
</section>
@endsection
