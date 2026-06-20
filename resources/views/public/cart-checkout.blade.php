@extends('layouts.app')

@section('title', 'Checkout — @' . $creator->username)
@section('description', 'Complete your order from ' . $creator->display_name)
    @push('head')
        <link rel="canonical" href="{{ url('/' . $creator->username . '/cart-checkout') }}">
    @endpush

@section('content')
    <section class="max-w-2xl mx-auto px-4 py-8 sm:py-12">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-ink-500 mb-6">
            <a href="{{ route('cart.show', $creator->username) }}" class="hover:text-brand-600 transition-colors inline-flex items-center gap-1">
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5" /> Back to cart
            </a>
            <x-heroicon-o-chevron-right class="w-3 h-3 text-ink-300" />
            <span class="font-semibold text-ink-700">Checkout</span>
        </nav>

        {{-- Header --}}
        <header class="mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-3">
                <x-heroicon-o-credit-card class="w-3.5 h-3.5" /> Checkout
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-balance">Selesaikan order kamu</h1>
            <p class="mt-2 text-ink-600">Konfirmasi item dan masukkan email untuk menerima akses produk.</p>
        </header>

        {{-- Order summary card --}}
        <div class="card-warm p-5 sm:p-6 mb-6">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-ink-100">
                <h2 class="font-bold text-ink-900 flex items-center gap-2">
                    <x-heroicon-o-shopping-bag class="w-4 h-4 text-brand-500" />
                    {{ $cart->items->count() }} {{ $cart->items->count() === 1 ? 'item' : 'items' }}
                </h2>
                <a href="{{ route('cart.show', $creator->username) }}" class="text-xs text-brand-600 hover:underline font-semibold">
                    Edit cart
                </a>
            </div>

            <ul class="space-y-3">
                @foreach ($cart->items as $item)
                    @php $p = $item->product; @endphp
                    <li class="flex items-center gap-3">
                        @if ($p->thumbnail_url)
                            <img src="{{ $p->thumbnail_url }}" alt="{{ $p->title }}" class="w-12 h-12 rounded-lg object-cover ring-1 ring-ink-200 flex-shrink-0">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center text-xl flex-shrink-0">
                                {{ $p->typeIcon }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm line-clamp-2 break-words">{{ $p->title }}</div>
                            <div class="text-xs text-ink-500 mt-0.5">
                                {{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="font-bold text-sm flex-shrink-0">
                            Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Voucher applied --}}
        @if ($cart->voucher_id)
            <div class="card-warm p-4 mb-6 flex items-center justify-between gap-3 bg-gradient-to-r from-brand-50 to-amber-50 border-brand-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-brand-500 text-white flex items-center justify-center flex-shrink-0">
                        <x-heroicon-o-gift class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="font-bold text-sm">Voucher {{ $cart->voucher->code }} applied</div>
                        <div class="text-xs text-ink-600">Saved Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</div>
                    </div>
                </div>
                <form action="{{ route('cart.voucher.remove', $creator->username) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-ghost-ink btn-sm">Remove</button>
                </form>
            </div>
        @endif

        {{-- Totals --}}
        <div class="card-warm p-5 sm:p-6 mb-6">
            <h2 class="text-sm font-bold text-ink-700 uppercase tracking-wider mb-4">Ringkasan Pembayaran</h2>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-ink-500">Subtotal</dt>
                    <dd class="font-semibold">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</dd>
                </div>
                @if ($cart->voucher_discount > 0)
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Voucher discount</dt>
                        <dd class="font-semibold text-brand-600">− Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</dd>
                    </div>
                @endif
                <div class="pt-3 mt-3 border-t border-ink-200 flex justify-between items-baseline">
                    <dt class="font-black text-base">Total Bayar</dt>
                    <dd class="font-black text-2xl text-gradient-brand">
                        Rp {{ number_format($cart->total, 0, ',', '.') }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Buyer form --}}
        <form action="{{ route('cart.process', $creator->username) }}" method="POST" class="card-warm p-5 sm:p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold text-ink-900 mb-2" for="payer_email">
                    Email untuk konfirmasi <span class="text-danger">*</span>
                </label>
                <input id="payer_email" name="payer_email" type="email" required
                       placeholder="kamu@email.com"
                       value="{{ old('payer_email', auth()->user()?->email) }}"
                       class="input input-bordered w-full @error('payer_email') input-error @enderror">
                @error('payer_email')
                    <div class="mt-1.5 text-xs text-danger font-semibold">{{ $message }}</div>
                @else
                    <p class="mt-1.5 text-xs text-ink-500">
                        Kami kirim invoice dan link akses produk ke email ini.
                    </p>
                @enderror
            </div>

            @if (auth()->check())
                <div class="flex items-center gap-2 p-3 rounded-xl bg-brand-50 border border-brand-200 text-sm">
                    <x-heroicon-o-user-circle class="w-5 h-5 text-brand-600 flex-shrink-0" />
                    <div>
                        Login sebagai <span class="font-bold">{{ '@' . auth()->user()->username }}</span> — produk akan otomatis masuk ke dashboard kamu.
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0" />
                    <div class="text-sm">
                        <a href="{{ route('login') }}" class="font-bold underline">Login</a> dulu untuk akses lebih cepat ke produk yang dibeli.
                    </div>
                </div>
            @endif

            <button type="submit" class="btn-cta btn-lg w-full shadow-cta">
                <x-heroicon-o-credit-card class="w-5 h-5" />
                Bayar Rp {{ number_format($cart->total, 0, ',', '.') }}
            </button>

            {{-- Trust line --}}
            <div class="flex items-center justify-center gap-4 text-xs text-ink-500 pt-2">
                <span class="inline-flex items-center gap-1">
                    <x-heroicon-o-shield-check class="w-3.5 h-3.5 text-brand-500" /> Pembayaran aman
                </span>
                <span class="inline-flex items-center gap-1">
                    <x-heroicon-o-bolt class="w-3.5 h-3.5 text-brand-500" /> QRIS, VA, E-Wallet
                </span>
                <span class="inline-flex items-center gap-1">
                    Powered by Duitku
                </span>
            </div>
        </form>
    </section>
@endsection