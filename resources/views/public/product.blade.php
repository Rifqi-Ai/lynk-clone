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
                        <div class="w-full h-full flex items-center justify-center text-ink-300">
                            <x-heroicon-o-photo class="w-24 h-24" />
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
