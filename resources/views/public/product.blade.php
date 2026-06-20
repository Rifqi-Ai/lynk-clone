<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->title }} — {{ '@' . $creator->username }}</title>
    <meta name="description" content="{{ Str::limit($product->description ?? $product->title, 160) }}">
    <meta property="og:title" content="{{ $product->title }}">
    <meta property="og:description" content="{{ Str::limit($product->description ?? '', 200) }}">
    <meta property="og:image" content="{{ $product->thumbnail_url ?? $creator->avatar_url }}">
    <meta property="og:type" content="product">

    <link rel="icon" type="image/png" href="{{ $creator->avatar_url }}">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Product",
        "name": {{ json_encode($product->title) }},
        "description": {{ json_encode(Str::limit($product->description ?? '', 500)) }},
        "image": {{ json_encode($product->thumbnail_url ?? $creator->avatar_url) }},
        "brand": {
            "@@type": "Brand",
            "name": {{ json_encode('@' . $creator->username) }}
        },
        "offers": {
            "@@type": "Offer",
            "price": {{ json_encode((float) $product->price) }},
            "priceCurrency": "IDR",
            "availability": "https://schema.org/InStock",
            "url": {{ json_encode(url()->current()) }},
            "seller": {
                "@@type": "Person",
                "name": {{ json_encode($creator->name) }}
            }
        }
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 antialiased min-h-screen">

    {{-- Skip link --}}
    <a href="#main" class="skip-link">Skip to content</a>

    {{-- Sticky top nav --}}
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-ink-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <a href="{{ $creator->profile_url }}" class="inline-flex items-center gap-2 text-sm font-semibold text-ink-700 hover:text-brand-600 transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                <span class="hidden sm:inline">{{ '@' . $creator->username }}</span>
                <span class="sm:hidden">Back</span>
            </a>
            <div class="flex items-center gap-2">
                <button onclick="navigator.share ? navigator.share({title: '{{ addslashes($product->title) }}', url: window.location.href}) : navigator.clipboard.writeText(window.location.href)" class="p-2 text-ink-500 hover:text-brand-600 transition-colors" title="Share">
                    <x-heroicon-o-share class="w-5 h-5" />
                </button>
            </div>
        </div>
    </header>

    <main id="main" class="max-w-6xl mx-auto px-4 sm:px-6 py-6 md:py-10">

        {{-- ─── Breadcrumb --}}
        <nav class="text-sm mb-6">
            <ol class="flex items-center gap-1.5 text-ink-500">
                <li><a href="{{ $creator->profile_url }}" class="hover:text-brand-600 transition-colors">{{ '@' . $creator->username }}</a></li>
                <li><x-heroicon-o-chevron-right class="w-3.5 h-3.5" /></li>
                <li class="text-ink-900 font-semibold truncate max-w-[200px] sm:max-w-xs">{{ $product->title }}</li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- ─── Left: Image + content (2 cols on desktop) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Hero image --}}
                <div class="relative aspect-video bg-gradient-to-br from-ink-100 to-ink-200 rounded-3xl overflow-hidden shadow-card">
                    @if ($product->thumbnail_url)
                        <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $product->title }}">
                    @else
                        {{-- Default gradient thumbnail by product type --}}
                        <div class="w-full h-full flex items-center justify-center relative overflow-hidden
                            @switch($product->type)
                                @case('digital')       bg-gradient-to-br from-brand-400 via-brand-500 to-amber-500 @break
                                @case('course')        bg-gradient-to-br from-violet-500 via-purple-500 to-indigo-600 @break
                                @case('event')         bg-gradient-to-br from-pink-400 via-rose-500 to-red-500 @break
                                @case('appointment')   bg-gradient-to-br from-blue-400 via-cyan-500 to-teal-500 @break
                                @case('donation')      bg-gradient-to-br from-rose-400 via-pink-500 to-fuchsia-500 @break
                                @case('blog')          bg-gradient-to-br from-emerald-400 via-teal-500 to-cyan-600 @break
                                @case('physical')      bg-gradient-to-br from-amber-400 via-orange-500 to-red-500 @break
                                @default               bg-gradient-to-br from-ink-400 via-ink-500 to-ink-600 @break
                            @endswitch">
                            {{-- Decorative orbs --}}
                            <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/20 rounded-full blur-2xl"></div>
                            <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/15 rounded-full blur-xl"></div>
                            {{-- Type icon --}}
                            <div class="relative z-10 text-white drop-shadow-lg">
                                @switch($product->type)
                                    @case('digital')       <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg> @break
                                    @case('course')        <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg> @break
                                    @case('event')         <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/></svg> @break
                                    @case('appointment')   <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg> @break
                                    @case('donation')      <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg> @break
                                    @case('blog')          <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.875C4.254 3.75 3.75 4.254 3.75 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/></svg> @break
                                    @case('physical')      <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg> @break
                                    @default               <svg class="w-24 h-24" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg> @break
                                @endswitch
                            </div>
                            {{-- Brand mark --}}
                            <div class="absolute bottom-4 right-4 text-white/30 text-xs font-black uppercase tracking-widest">
                                {{ config('app.name', 'Linka') }}
                            </div>
                        </div>
                    @endif
                    {{-- Type badge --}}
                    <div class="absolute top-4 left-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/95 backdrop-blur text-xs font-bold text-ink-900 shadow-sm">
                            @switch($product->type)
                                @case('digital')       <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 text-brand-600" />
                                @case('course')        <x-heroicon-o-academic-cap class="w-3.5 h-3.5 text-purple-600" />
                                @case('event')         <x-heroicon-o-calendar-days class="w-3.5 h-3.5 text-pink-600" />
                                @case('appointment')   <x-heroicon-o-clock class="w-3.5 h-3.5 text-blue-600" />
                                @case('donation')      <x-heroicon-o-heart class="w-3.5 h-3.5 text-rose-600" />
                                @case('blog')          <x-heroicon-o-newspaper class="w-3.5 h-3.5 text-amber-600" />
                                @case('physical')      <x-heroicon-o-gift class="w-3.5 h-3.5 text-emerald-600" />
                                @default               <x-heroicon-o-cube class="w-3.5 h-3.5 text-ink-600" />
                            @endswitch
                            {{ $product->typeLabel }}
                        </span>
                    </div>
                </div>

                {{-- Title + meta (mobile only — desktop shows in sidebar) --}}
                <div class="lg:hidden">
                    <h1 class="text-2xl md:text-3xl font-bold text-ink-900 tracking-tight">{{ $product->title }}</h1>
                </div>

                {{-- Description (markdown) --}}
                @if ($product->description)
                    <div class="bg-white rounded-2xl border border-ink-200 p-6 md:p-8 shadow-card">
                        <h2 class="text-sm font-bold text-ink-500 uppercase tracking-wider mb-3">Tentang produk ini</h2>
                        <div class="prose prose-sm md:prose-base prose-ink max-w-none prose-headings:font-display prose-headings:tracking-tight prose-a:text-brand-600 prose-a:no-underline hover:prose-a:underline prose-img:rounded-xl">
                            {!! \Str::of($product->description)->markdown(['html_input' => 'strip']) !!}
                        </div>
                    </div>
                @endif

                {{-- Type-specific content --}}
                @if ($product->type === 'course' && $product->courseModules > 0)
                    <div class="bg-white rounded-2xl border border-ink-200 p-6 md:p-8 shadow-card">
                        <h2 class="text-lg font-bold text-ink-900 mb-4 flex items-center gap-2">
                            <x-heroicon-o-academic-cap class="w-5 h-5 text-brand-600" />
                            Kurikulum ({{ $product->courseModules }} modules)
                        </h2>
                        <ol class="space-y-2">
                            @foreach($product->modules()->orderBy('position')->get() as $i => $module)
                                <li class="flex items-start gap-3 p-3 rounded-xl hover:bg-ink-50 transition-colors">
                                    <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-brand-100 text-brand-700 font-bold text-sm flex items-center justify-center">{{ $i + 1 }}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-ink-900 text-sm">{{ $module->title }}</p>
                                        @if ($module->duration_seconds)
                                            <p class="text-xs text-ink-500 mt-0.5">{{ gmdate('H:i:s', $module->duration_seconds) }}</p>
                                        @endif
                                    </div>
                                    @if ($module->is_free_preview)
                                        <span class="text-xs font-bold text-success">FREE</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                @if ($product->type === 'event')
                    <div class="bg-white rounded-2xl border border-ink-200 p-6 md:p-8 shadow-card space-y-4">
                        <h2 class="text-lg font-bold text-ink-900 flex items-center gap-2">
                            <x-heroicon-o-calendar-days class="w-5 h-5 text-pink-600" />
                            Detail Event
                        </h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @if ($product->eventDate)
                                <div class="flex items-start gap-3 p-4 bg-pink-50 rounded-xl">
                                    <x-heroicon-o-calendar class="w-5 h-5 text-pink-600 mt-0.5" />
                                    <div>
                                        <p class="text-xs font-bold text-pink-900 uppercase tracking-wider">Tanggal</p>
                                        <p class="font-semibold text-ink-900 mt-0.5">{{ \Carbon\Carbon::parse($product->eventDate)->format('d M Y, H:i') }} WIB</p>
                                    </div>
                                </div>
                            @endif
                            @if ($product->meta('location_type'))
                                <div class="flex items-start gap-3 p-4 bg-blue-50 rounded-xl">
                                    <x-heroicon-o-map-pin class="w-5 h-5 text-blue-600 mt-0.5" />
                                    <div>
                                        <p class="text-xs font-bold text-blue-900 uppercase tracking-wider">Lokasi</p>
                                        <p class="font-semibold text-ink-900 mt-0.5">{{ ucfirst($product->meta('location_type')) }}{{ $product->meta('venue_name') ? ' • ' . $product->meta('venue_name') : '' }}</p>
                                    </div>
                                </div>
                            @endif
                            @if ($product->meta('capacity'))
                                <div class="flex items-start gap-3 p-4 bg-amber-50 rounded-xl">
                                    <x-heroicon-o-users class="w-5 h-5 text-amber-600 mt-0.5" />
                                    <div>
                                        <p class="text-xs font-bold text-amber-900 uppercase tracking-wider">Kapasitas</p>
                                        <p class="font-semibold text-ink-900 mt-0.5">{{ $product->meta('capacity') }} orang</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Creator card --}}
                <a href="{{ $creator->profile_url }}" class="block bg-white rounded-2xl border border-ink-200 p-5 shadow-card hover:shadow-card-hover hover:border-ink-300 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="relative w-14 h-14 flex-shrink-0">
                            <div class="w-14 h-14 rounded-full p-0.5 bg-gradient-to-br from-brand-400 to-brand-700">
                                <img src="{{ $creator->avatar_url }}" class="w-full h-full rounded-full object-cover ring-2 ring-white">
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-ink-500">Dijual oleh</p>
                            <p class="font-bold text-ink-900 truncate group-hover:text-brand-600 transition-colors">{{ $creator->name }}</p>
                            <p class="text-sm text-ink-500">{{ '@' . $creator->username }}</p>
                        </div>
                        <x-heroicon-o-arrow-right class="w-5 h-5 text-ink-400 group-hover:text-brand-600 group-hover:translate-x-1 transition-all" />
                    </div>
                </a>
            </div>

            {{-- ─── Right: Sticky buy sidebar (1 col on desktop) --}}
            <div class="lg:col-span-1">
                <div class="lg:sticky lg:top-20 space-y-4">

                    {{-- Price + buy card --}}
                    <div class="bg-white rounded-2xl border border-ink-200 p-6 shadow-card">
                        {{-- Title (desktop only) --}}
                        <h1 class="hidden lg:block text-2xl font-bold text-ink-900 tracking-tight text-balance">{{ $product->title }}</h1>

                        {{-- Price --}}
                        <div class="mt-4 flex items-baseline gap-2 flex-wrap">
                            @if ($product->type === 'donation')
                                <span class="text-3xl font-bold text-ink-900">Pilih nominal</span>
                            @else
                                <span class="text-3xl font-bold text-ink-900 tracking-tight">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                @if ($product->has_discount)
                                    <span class="text-base text-ink-400 line-through">Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}</span>
                                    <span class="text-xs font-bold text-white bg-error px-2 py-0.5 rounded-md">-{{ $product->discountPercentage }}%</span>
                                @endif
                            @endif
                        </div>

                        {{-- Meta badges --}}
                        @if ($product->type === 'appointment')
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                    <x-heroicon-o-clock class="w-3.5 h-3.5" /> {{ $product->durationFormatted }}
                                </span>
                                @if ($product->meta('location_type'))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                        <x-heroicon-o-map-pin class="w-3.5 h-3.5" /> {{ ucfirst($product->meta('location_type')) }}
                                    </span>
                                @endif
                            </div>
                        @elseif ($product->type === 'event')
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($product->eventDate)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-pink-50 text-pink-700 text-xs font-semibold">
                                        <x-heroicon-o-calendar class="w-3.5 h-3.5" /> {{ \Carbon\Carbon::parse($product->eventDate)->format('d M Y') }}
                                    </span>
                                @endif
                                @if ($product->meta('capacity'))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">
                                        <x-heroicon-o-users class="w-3.5 h-3.5" /> {{ $product->meta('capacity') }} seats
                                    </span>
                                @endif
                            </div>
                        @elseif ($product->type === 'course')
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-semibold">
                                    <x-heroicon-o-academic-cap class="w-3.5 h-3.5" /> {{ $product->courseModules }} modules
                                </span>
                                @if ($product->courseDuration > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-semibold">
                                        <x-heroicon-o-play class="w-3.5 h-3.5" /> {{ round($product->courseDuration / 60) }} min video
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Stock indicator for physical --}}
                        @if ($product->type === 'physical' && $product->track_inventory)
                            <div class="mt-4 flex items-center gap-2 text-sm">
                                @if ($product->inStock)
                                    <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                                    <span class="text-ink-600">Tersedia — <span class="font-bold text-ink-900">{{ $product->stock_quantity }}</span> tersisa</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-error"></span>
                                    <span class="text-error font-semibold">Stok habis</span>
                                @endif
                            </div>
                        @endif

                        {{-- Buy button (different per type) --}}
                        <div class="mt-6 space-y-3">
                            @if ($product->type === 'donation')
                                <a href="{{ $product->checkout_url }}" class="btn-cta btn-block">
                                    <x-heroicon-s-heart class="w-4 h-4" />
                                    Dukung Creator
                                </a>
                            @elseif ($product->type === 'cart-eligible' || in_array($product->type, ['digital', 'course', 'blog', 'physical']))
                                <a href="{{ $product->checkout_url }}" class="btn-cta btn-block">
                                    Beli Sekarang
                                    <x-heroicon-s-arrow-right class="w-4 h-4" />
                                </a>
                                @if (in_array($product->type, ['digital', 'course', 'physical']))
                                    <form action="{{ url('/cart/add') }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="btn-outline-ink btn-block">
                                            <x-heroicon-o-shopping-cart class="w-4 h-4" />
                                            Tambah ke Keranjang
                                        </button>
                                    </form>
                                @endif
                            @elseif ($product->type === 'appointment' || $product->type === 'event')
                                <a href="{{ $product->checkout_url }}" class="btn-cta btn-block">
                                    {{ $product->type === 'event' ? 'Beli Tiket' : 'Book Sekarang' }}
                                    <x-heroicon-s-arrow-right class="w-4 h-4" />
                                </a>
                            @endif
                        </div>

                        {{-- Trust signals --}}
                        <div class="mt-6 pt-6 border-t border-ink-100 space-y-2.5 text-xs text-ink-500">
                            <div class="flex items-center gap-2">
                                <x-heroicon-s-shield-check class="w-4 h-4 text-success" />
                                <span>Pembayaran aman via Duitku</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-s-bolt class="w-4 h-4 text-brand-600" />
                                <span>Akses instan setelah bayar</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-heroicon-s-chat-bubble-left-right class="w-4 h-4 text-blue-600" />
                                <span>Support via WhatsApp</span>
                            </div>
                        </div>
                    </div>

                    {{-- Social proof --}}
                    @if (($product->sales_count ?? 0) > 0)
                        <div class="bg-white rounded-2xl border border-ink-200 p-5 shadow-card text-center">
                            <div class="flex items-center justify-center gap-1 text-amber-500">
                                @for($i=0;$i<5;$i++)<x-heroicon-s-star class="w-4 h-4" />@endfor
                            </div>
                            <p class="mt-2 text-sm font-bold text-ink-900">{{ $product->sales_count }}+ sudah membeli</p>
                            <p class="text-xs text-ink-500 mt-0.5">dan puas dengan produk ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>
