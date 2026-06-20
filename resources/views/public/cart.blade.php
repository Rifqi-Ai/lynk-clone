@extends('layouts.app')

@section('title', 'Cart — @' . $creator->username)
@section('description', 'Shopping cart for ' . $creator->display_name . ' store')
    @push('head')
        <link rel="canonical" href="{{ url('/' . $creator->username . '/cart') }}">
    @endpush

@section('content')
    <section class="max-w-2xl mx-auto px-4 py-8 sm:py-12">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-ink-500 mb-6">
            <a href="{{ $creator->profile_url }}" class="inline-flex items-center gap-1 hover:text-brand-600 transition-colors">
                <x-heroicon-o-arrow-left class="w-3.5 h-3.5" /> {{ '@' . $creator->username }}
            </a>
            <x-heroicon-o-chevron-right class="w-3 h-3 text-ink-300" />
            <span class="font-semibold text-ink-700">Cart</span>
        </nav>

        {{-- Header --}}
        <header class="mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-3">
                <x-heroicon-o-shopping-bag class="w-3.5 h-3.5" /> Your Shopping Cart
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-balance">
                @if ($cart->items->isEmpty())
                    Cart kosong
                @else
                    {{ $cart->items->count() }} {{ $cart->items->count() === 1 ? 'item' : 'items' }} siap di-checkout
                @endif
            </h1>
            <p class="mt-2 text-ink-600">
                Semua produk dari <a href="{{ $creator->profile_url }}" class="font-bold text-brand-600 hover:underline">{{ $creator->display_name }}</a>
            </p>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success mb-6">
                <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-error mb-6">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 flex-shrink-0" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($cart->items->isEmpty())
            {{-- Empty state --}}
            <div class="card-warm p-12 sm:p-16 text-center">
                <div class="inline-flex w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 items-center justify-center mb-5 shadow-sm">
                    <x-heroicon-o-shopping-cart class="w-10 h-10 text-brand-600" />
                </div>
                <h2 class="text-2xl font-black mb-2">Cart kamu masih kosong</h2>
                <p class="text-ink-600 mb-6 max-w-sm mx-auto">
                    Jelajahi produk dari {{ '@' . $creator->username }} dan tambahkan item favorit kamu ke sini.
                </p>
                <a href="{{ $creator->profile_url }}" class="btn-cta">
                    <x-heroicon-o-arrow-right class="w-4 h-4" /> Browse Products
                </a>
            </div>
        @else
            {{-- Cart items --}}
            <div class="card-warm mb-6 overflow-hidden">
                <div class="px-5 py-3 border-b border-ink-100 bg-ink-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-ink-700 uppercase tracking-wider">Items</h2>
                    <span class="text-xs text-ink-500 font-mono">{{ $cart->items->count() }} product{{ $cart->items->count() === 1 ? '' : 's' }}</span>
                </div>

                <ul class="divide-y divide-ink-100">
                    @foreach ($cart->items as $item)
                        @php $p = $item->product; @endphp
                        <li class="p-4 sm:p-5 flex items-center gap-3 sm:gap-4 hover:bg-brand-50/30 transition-colors">
                            {{-- Thumbnail --}}
                            <div class="flex-shrink-0">
                                @if ($p->thumbnail_url)
                                    <img src="{{ $p->thumbnail_url }}" alt="{{ $p->title }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover ring-1 ring-ink-200">
                                @else
                                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center text-2xl">
                                        {{ $p->typeIcon }}
                                    </div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <a href="{{ $p->url }}" class="block font-bold text-sm sm:text-base text-ink-900 hover:text-brand-600 transition-colors line-clamp-2 break-words">
                                    {{ $p->title }}
                                </a>
                                <div class="mt-1 flex items-center gap-2 text-xs text-ink-500">
                                    <span class="badge badge-sm badge-soft">{{ $p->typeLabel }}</span>
                                    <span>·</span>
                                    <span class="font-semibold">Rp {{ number_format($p->price, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            {{-- Quantity --}}
                            <form method="POST" action="{{ route('cart.update', [$creator->username, $p->id]) }}" class="hidden sm:block">
                                @csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="10"
                                       class="input input-sm input-bordered w-16 text-center font-bold"
                                       onchange="this.form.submit()"
                                       aria-label="Quantity for {{ $p->title }}">
                            </form>

                            {{-- Subtotal + Remove --}}
                            <div class="text-right flex-shrink-0">
                                <div class="font-black text-sm sm:text-base text-brand-600">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                                <form method="POST" action="{{ route('cart.remove', [$creator->username, $p->id]) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-ink-500 hover:text-danger font-semibold mt-1 transition-colors inline-flex items-center gap-1">
                                        <x-heroicon-o-trash class="w-3 h-3" /> Remove
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Voucher --}}
            <div class="card-warm p-5 mb-6">
                <h2 class="text-sm font-bold text-ink-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <x-heroicon-o-ticket class="w-4 h-4 text-brand-500" /> Voucher
                </h2>

                @if ($cart->voucher)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gradient-to-r from-brand-50 to-amber-50 border border-brand-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-brand-500 text-white flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-gift class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="font-mono font-black text-brand-700">{{ $cart->voucher->code }}</div>
                                <div class="text-xs text-ink-600">Saved Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('cart.voucher.remove', $creator->username) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-ghost-ink btn-sm">
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('cart.voucher.apply', $creator->username) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="voucher_code" placeholder="Masukkan kode voucher (e.g. WELCOME10)"
                               class="input input-bordered flex-1 font-mono uppercase @error('voucher_code') input-error @enderror"
                               value="{{ old('voucher_code') }}">
                        <button type="submit" class="btn-secondary flex-shrink-0">
                            Apply
                        </button>
                    </form>
                    @error('voucher_code')
                        <div class="mt-2 text-xs text-danger font-semibold">{{ $message }}</div>
                    @enderror
                    <div class="mt-2 text-xs text-ink-500">
                        💡 Coba <code class="px-1.5 py-0.5 rounded bg-ink-100 font-mono font-bold">WELCOME10</code> untuk diskon 10%
                    </div>
                @endif
            </div>

            {{-- Totals --}}
            <div class="card-warm p-5 sm:p-6 mb-6">
                <h2 class="text-sm font-bold text-ink-700 uppercase tracking-wider mb-4">Order Summary</h2>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-ink-500">Subtotal</dt>
                        <dd class="font-semibold">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</dd>
                    </div>
                    @if ($cart->voucher_discount > 0)
                        <div class="flex justify-between">
                            <dt class="text-ink-500 flex items-center gap-1.5">
                                <x-heroicon-o-ticket class="w-3.5 h-3.5 text-brand-500" />
                                Voucher ({{ $cart->voucher->code }})
                            </dt>
                            <dd class="font-semibold text-brand-600">− Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="pt-3 mt-3 border-t border-ink-200 flex justify-between items-baseline">
                        <dt class="font-black text-base">Total</dt>
                        <dd class="font-black text-2xl text-gradient-brand">
                            Rp {{ number_format($cart->total, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- CTA --}}
            <a href="{{ route('cart.checkout', $creator->username) }}" class="btn-cta btn-lg w-full shadow-cta">
                Lanjut ke Checkout
                <x-heroicon-o-arrow-right class="w-5 h-5" />
            </a>

            <div class="text-center mt-4">
                <a href="{{ $creator->profile_url }}" class="text-sm text-ink-500 hover:text-brand-600 transition-colors inline-flex items-center gap-1">
                    <x-heroicon-o-arrow-left class="w-3.5 h-3.5" /> Continue shopping
                </a>
            </div>

            {{-- Trust badges --}}
            <div class="mt-8 flex items-center justify-center gap-6 text-xs text-ink-500">
                <span class="inline-flex items-center gap-1.5">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-brand-500" /> Secure payment
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <x-heroicon-o-bolt class="w-4 h-4 text-brand-500" /> Instant access
                </span>
            </div>
        @endif
    </section>
@endsection