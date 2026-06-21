@extends('layouts.app')

@section('title', $query ? "Hasil pencarian: \"{$query}\" — Linka" : 'Cari kreator & produk — Linka')
@section('description', 'Cari kreator dan produk digital, course, event, donation di Linka.')

@section('content')

<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-brand-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-linka section relative">
        <div class="text-center max-w-2xl mx-auto animate-fade-up">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 backdrop-blur border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-5">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                Search
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-ink-900 text-balance leading-[1.1]">
                @if ($query)
                    Hasil pencarian <span class="text-gradient-warm">"{{ $query }}"</span>
                @else
                    Cari <span class="text-gradient-warm">kreator & produk</span>
                @endif
            </h1>

            {{-- Re-search form so users can refine --}}
            <form action="{{ route('search') }}" method="GET" class="mt-6 max-w-xl mx-auto">
                <label for="search-input" class="sr-only">Cari kreator atau produk</label>
                <div class="flex items-center gap-2 bg-white rounded-2xl border border-ink-200 shadow-sm p-1.5 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-200 transition-all">
                    <div class="pl-3 text-ink-400" aria-hidden="true">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </div>
                    <input id="search-input" name="q" type="search" autocomplete="off" autofocus
                           value="{{ $query }}"
                           placeholder="Cari kreator, produk, course, atau event…"
                           class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-ink-900 placeholder:text-ink-400 px-2 py-2.5"
                           aria-label="Cari kreator atau produk">
                    <button type="submit" class="btn-cta !h-10 !px-5 !text-sm">
                        Cari
                    </button>
                </div>
            </form>

            @if ($query)
                <p class="mt-4 text-sm text-ink-500" role="status" aria-live="polite">
                    {{ $totalResults }} {{ $totalResults === 1 ? 'hasil' : 'hasil' }} ditemukan
                </p>
            @endif
        </div>
    </div>
</section>

@if ($query && $totalResults > 0)
    <section class="section bg-white">
        <div class="container-linka">

            {{-- Creators --}}
            @if ($creators->count() > 0)
                <div class="mb-10">
                    <h2 class="text-2xl font-bold text-ink-900 flex items-center gap-2 mb-5">
                        <span class="text-3xl" aria-hidden="true">👤</span>
                        Kreator
                        <span class="text-sm font-normal text-ink-500">({{ $creators->count() }})</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($creators as $creator)
                            <a href="{{ $creator->profile_url ?? url('/' . $creator->username) }}"
                               class="card-warm p-5 hover:shadow-card transition-all group block">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-500 to-amber-500 flex items-center justify-center text-white font-black text-base flex-shrink-0">
                                        {{ mb_strtoupper(mb_substr($creator->name ?: $creator->username, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-ink-900 group-hover:text-brand-600 transition-colors truncate">{{ $creator->display_name }}</p>
                                        <p class="text-xs text-ink-500 truncate">@{{ $creator->username }}</p>
                                        @if ($creator->bio)
                                            <p class="mt-2 text-sm text-ink-600 line-clamp-2">{{ $creator->bio }}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Products --}}
            @if ($products->count() > 0)
                <div>
                    <h2 class="text-2xl font-bold text-ink-900 flex items-center gap-2 mb-5">
                        <span class="text-3xl" aria-hidden="true">📦</span>
                        Produk
                        <span class="text-sm font-normal text-ink-500">({{ $products->count() }})</span>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($products as $product)
                            <a href="{{ $product->url ?? url('/' . $product->owner->username . '/' . $product->id) }}"
                               class="card-warm p-5 hover:shadow-card transition-all group block">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-100 to-amber-100 flex items-center justify-center text-brand-600 text-lg flex-shrink-0" aria-hidden="true">
                                        {{ $product->type_icon ?? '📦' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-ink-900 group-hover:text-brand-600 transition-colors truncate">{{ $product->title }}</p>
                                        <p class="text-xs text-ink-500 truncate">{{ $product->type_label ?? ucfirst($product->type) }} · @{{ $product->owner->username }}</p>
                                        @if ($product->price > 0)
                                            <p class="mt-2 text-sm font-bold text-brand-600">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@elseif ($query && $totalResults === 0)
    {{-- Empty state: query but no results --}}
    <section class="section bg-white">
        <div class="container-narrow text-center" role="status" aria-live="polite">
            <div class="inline-flex w-20 h-20 rounded-2xl bg-gradient-to-br from-ink-100 to-ink-200 items-center justify-center mb-6" aria-hidden="true">
                <svg class="w-10 h-10 text-ink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-ink-900 mb-2">Tidak ada hasil untuk "{{ $query }}"</h2>
            <p class="text-sm text-ink-500 mb-6">Coba kata kunci lain, atau <a href="{{ route('home') }}" class="font-semibold text-brand-600 hover:underline">lihat landing page</a>.</p>
        </div>
    </section>
@endif

@endsection