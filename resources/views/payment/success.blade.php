@extends('layouts.app')

@section('title', 'Pembayaran Berhasil — Linka')

@section('content')
<section class="section flex items-center justify-center">
    <div class="container-narrow text-center">
        {{-- Success animation --}}
        <div class="mx-auto w-20 h-20 rounded-full bg-success/10 flex items-center justify-center mb-6 animate-fade-up">
            <x-heroicon-s-check class="w-10 h-10 text-success" />
        </div>

        <h1 class="text-display-md text-ink-900 text-balance animate-fade-up" style="animation-delay: 100ms">Pembayaran berhasil! 🎉</h1>
        <p class="mt-3 text-lg text-ink-600 text-pretty animate-fade-up" style="animation-delay: 200ms">
            Terima kasih! Pesanan kamu sedang diproses. Kami sudah kirim detail ke email kamu.
        </p>

        <div class="mt-8 bg-white rounded-2xl border border-ink-200 p-6 shadow-card animate-fade-up text-left" style="animation-delay: 300ms">
            <h2 class="font-bold text-ink-900 mb-3 flex items-center gap-2">
                <x-heroicon-s-receipt-percent class="w-5 h-5 text-brand-600" />
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
                    <dd class="font-bold text-brand-600">Rp {{ number_format($order->total, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink-500">Status</dt>
                    <dd><span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-success/10 text-success uppercase tracking-wider"><x-heroicon-s-check class="w-3 h-3" /> Paid</span></dd>
                </div>
            </dl>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center animate-fade-up" style="animation-delay: 400ms">
            <a href="{{ $order->product?->url ?? '/' }}" class="btn-cta">
                Lihat Pesanan
                <x-heroicon-s-arrow-right class="w-4 h-4" />
            </a>
            <a href="{{ $creator->profile_url ?? '/' }}" class="btn-outline-ink">
                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                Kembali ke Halaman Creator
            </a>
        </div>
    </div>
</section>
@endsection
