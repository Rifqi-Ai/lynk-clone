<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ '@' . $creator->username }}{{ $creator->title ? ' — ' . $creator->title : '' }}</title>
    <meta name="description" content="{{ Str::limit($creator->bio ?? $creator->display_title, 160) }}">
    <link rel="canonical" href="{{ url('/' . $creator->username) }}">

    {{-- OG / Twitter --}}
    <meta property="og:title" content="{{ '@' . $creator->username }}">
    <meta property="og:description" content="{{ Str::limit($creator->bio ?? $creator->display_title, 200) }}">
    <meta property="og:type" content="profile">
    <meta property="og:image" content="{{ $creator->avatar_url }}">
    <meta property="og:url" content="{{ url('/' . $creator->username) }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ '@' . $creator->username }}">
    <meta name="twitter:description" content="{{ Str::limit($creator->bio ?? $creator->display_title, 200) }}">
    <meta name="twitter:image" content="{{ $creator->avatar_url }}">

    <link rel="icon" type="image/png" href="{{ $creator->avatar_url }}">

    {{-- JSON-LD: Person structured data for Google rich results --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Person",
        "name": {{ json_encode($creator->name) }},
        "alternateName": {{ json_encode('@' . $creator->username) }},
        "description": {{ json_encode(Str::limit($creator->bio ?? $creator->display_title, 500)) }},
        "image": {{ json_encode($creator->avatar_url) }},
        "url": {{ json_encode(url('/' . $creator->username)) }}
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen mesh-gradient">

    {{-- Skip link for accessibility --}}
    <a href="#main" class="skip-link">Skip to content</a>

    {{-- Floating orbs for depth --}}
    <div class="fixed top-1/4 -right-32 w-96 h-96 bg-brand-400/20 rounded-full blur-3xl animate-float-slow pointer-events-none -z-10"></div>
    <div class="fixed bottom-1/4 -left-32 w-96 h-96 bg-amber-400/20 rounded-full blur-3xl animate-float pointer-events-none -z-10"></div>

    <main id="main" class="max-w-xl mx-auto py-10 md:py-16 px-5 relative">

        {{-- ─── Profile header ─── --}}
        <header class="text-center stagger">

            {{-- Avatar with optional verified badge --}}
            <div class="relative inline-block">
                <div class="w-28 h-28 md:w-32 md:h-32 rounded-full p-1 bg-gradient-to-br from-brand-400 to-brand-600 shadow-cta">
                    <img src="{{ $creator->avatar_url }}" alt="{{ $creator->username }}"
                         class="w-full h-full rounded-full object-cover ring-4 ring-white bg-white">
                </div>
                @if ($creator->is_verified ?? false)
                    <span class="absolute -bottom-1 -right-1 w-9 h-9 bg-success rounded-full ring-4 ring-white flex items-center justify-center shadow-md">
                        <x-heroicon-s-check class="w-5 h-5 text-white" />
                    </span>
                @endif
            </div>

            {{-- Name + handle --}}
            <h1 class="mt-5 text-2xl md:text-3xl font-bold text-ink-900 tracking-tight">
                {{ $creator->name }}
            </h1>
            <p class="mt-1 text-sm font-semibold text-brand-600">{{ '@' . $creator->username }}</p>

            {{-- Title (occupation) --}}
            @if ($creator->title)
                <p class="mt-3 text-sm md:text-base text-ink-600 max-w-md mx-auto text-balance">
                    {{ $creator->title }}
                </p>
            @endif

            {{-- Bio --}}
            @if ($creator->bio)
                <p class="mt-3 text-sm text-ink-500 max-w-md mx-auto leading-relaxed whitespace-pre-line">
                    {{ $creator->bio }}
                </p>
            @endif

            {{-- Social links --}}
            @if (!empty($creator->social_links))
                <div class="mt-5 flex justify-center flex-wrap gap-2">
                    @foreach ($creator->social_links as $link)
                        @if (!empty($link['url']))
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                               class="w-10 h-10 inline-flex items-center justify-center rounded-full bg-white border border-ink-200 text-ink-500 hover:text-brand-600 hover:border-brand-300 hover:-translate-y-0.5 transition-all duration-150 shadow-sm"
                               title="{{ $link['platform'] }}">
                                @include('components.social-icon', ['platform' => $link['platform']])
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

        {{-- ─── Stats row ─── --}}
        <div class="mt-6 flex justify-center gap-4 md:gap-8 text-xs">
            @if (($creator->products_count ?? 0) > 0)
                <div class="flex items-center gap-1.5 text-ink-500">
                    <x-heroicon-o-cube class="w-4 h-4 text-brand-500" />
                    <span><span class="font-bold text-ink-900">{{ $creator->products_count }}</span> produk</span>
                </div>
            @endif
            @if (($creator->followers_count ?? 0) > 0)
                <div class="flex items-center gap-1.5 text-ink-500">
                    <x-heroicon-o-users class="w-4 h-4 text-brand-500" />
                    <span><span class="font-bold text-ink-900">{{ number_format($creator->followers_count) }}</span> pengikut</span>
                </div>
            @endif
            @if (($creator->total_sales_count ?? 0) > 0)
                <div class="flex items-center gap-1.5 text-ink-500">
                    <x-heroicon-o-shopping-bag class="w-4 h-4 text-brand-500" />
                    <span><span class="font-bold text-ink-900">{{ number_format($creator->total_sales_count) }}</span> terjual</span>
                </div>
            @endif
        </div>
    </header>

        {{-- ─── Search + filter ─── --}}
        @if ($creator->products()->where('status', 'published')->count() > 2)
            <form method="GET" action="{{ $creator->profile_url }}" class="mt-8 bg-white/80 backdrop-blur-sm rounded-2xl border border-ink-200 p-3 shadow-sm space-y-3">

                {{-- Search box --}}
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-400" />
                    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari produk atau course..."
                           class="w-full h-11 pl-10 pr-4 rounded-xl border border-ink-200 bg-white text-sm text-ink-900 placeholder:text-ink-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none">
                    @if ($typeFilter)<input type="hidden" name="type" value="{{ $typeFilter }}">@endif
                    @if (($sort ?? 'latest') !== 'latest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                </div>

                {{-- Type filter pills --}}
                @if (!empty($typeCounts) && count($typeCounts) > 1)
                    <div class="flex flex-wrap gap-1.5 -mx-1 px-1 pb-1 overflow-x-auto scrollbar-hide">
                        @php
                            // Compute type counts from ACTUALLY displayed products (excludes featured)
                            $displayedTypeCounts = $products->groupBy('type')->map->count()->toArray();
                        @endphp
                        <a href="{{ $creator->profile_url . ($search ? '?q=' . urlencode($search) : '') }}"
                           class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all {{ !$typeFilter ? 'bg-brand-500 text-white shadow-sm' : 'bg-ink-100 text-ink-700 hover:bg-ink-200' }}">
                            All ({{ $products->count() }})
                        </a>
                        @foreach (\App\Models\Product::TYPES as $key => $info)
                            @if (!empty($displayedTypeCounts[$key]))
                                <a href="{{ $creator->profile_url . '?type=' . $key . ($search ? '&q=' . urlencode($search) : '') }}"
                                   class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all {{ $typeFilter === $key ? 'bg-brand-500 text-white shadow-sm' : 'bg-ink-100 text-ink-700 hover:bg-ink-200' }}">
                                    {{ $info['label'] }} ({{ $typeCounts[$key] }})
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Results count + sort --}}
                <div class="flex items-center justify-between text-xs pt-1 px-1">
                    <div class="text-ink-500">
                        @if ($search || $typeFilter)
                            <span class="font-bold text-ink-900">{{ $products->count() }}</span> hasil
                            @if ($search)untuk "<span class="font-bold text-ink-900">{{ $search }}</span>"@endif
                            <a href="{{ $creator->profile_url }}" class="ml-2 text-brand-600 hover:underline font-semibold">Reset</a>
                        @else
                            <span class="font-bold text-ink-900">{{ $products->count() }}</span> produk
                        @endif
                    </div>
                    @if ($products->count() > 1)
                        <label class="flex items-center gap-1.5 text-ink-600 font-semibold">
                            <span>Urutkan</span>
                            <select name="sort" onchange="this.form.submit()" class="bg-transparent border-0 focus:ring-0 text-ink-900 font-bold cursor-pointer pr-1 py-0 h-auto text-xs">
                                <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="popular" {{ ($sort ?? '') === 'popular' ? 'selected' : '' }}>Terpopuler</option>
                                <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>Harga ↑</option>
                                <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>Harga ↓</option>
                            </select>
                        </label>
                    @endif
                </div>
            </form>
        @endif

        {{-- ─── Featured product (if any) ─── --}}
        @if (!empty($featured))
            <a href="{{ $featured->url }}" class="group block mt-8 relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 p-6 md:p-8 shadow-cta hover:shadow-cta-hover hover:-translate-y-1 transition-all duration-200 animate-fade-up">
                {{-- Background pattern --}}
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 16px 16px;"></div>

                <div class="relative flex items-center gap-4 md:gap-6">
                    {{-- Thumbnail --}}
                    <div class="flex-shrink-0 w-20 h-20 md:w-28 md:h-28 rounded-2xl overflow-hidden bg-white/20 ring-2 ring-white/30 shadow-lg">
                        @if ($featured->thumbnail_url)
                            <img src="{{ $featured->thumbnail_url }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white">
                                <x-heroicon-o-sparkles class="w-10 h-10" />
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0 text-white">
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-s-star class="w-3.5 h-3.5 text-yellow-300" />
                            <span class="text-[10px] font-bold uppercase tracking-wider text-yellow-100">Featured</span>
                        </div>
                        <h2 class="mt-1 text-lg md:text-xl font-bold leading-tight line-clamp-2">
                            {{ $featured->title }}
                        </h2>
                        <p class="mt-2 text-sm text-white/80 line-clamp-1">
                            {{ $featured->subtitle ?? $featured->typeLabel }}
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            @if ($featured->type !== 'donation')
                                <span class="text-base md:text-lg font-bold">Rp {{ number_format($featured->price, 0, ',', '.') }}</span>
                                @if ($featured->has_discount)
                                    <span class="text-xs text-white/60 line-through">Rp {{ number_format($featured->compare_at_price, 0, ',', '.') }}</span>
                                @endif
                            @else
                                <span class="text-sm font-semibold">Dukung creator →</span>
                            @endif
                            <x-heroicon-s-arrow-right class="w-4 h-4 ml-auto group-hover:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </div>
            </a>
        @endif

        {{-- ─── Products grid (single column — link-in-bio aesthetic) ─── --}}
        @if (!empty($products) && $products->count() > 0)
            <div class="mt-6 space-y-3">

                @forelse ($products as $product)
                    <a href="{{ $product->url }}" class="linka-link group animate-fade-up" style="text-align: left;">
                        <div class="flex items-center gap-3.5">
                            {{-- Thumbnail (fixed size) --}}
                            <div class="relative w-14 h-14 flex-shrink-0 rounded-xl overflow-hidden bg-gradient-to-br from-brand-50 to-brand-100">
                                @if ($product->thumbnail_url)
                                    <img src="{{ $product->thumbnail_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-brand-600">
                                        @switch($product->type)
                                            @case('digital')       <x-heroicon-o-arrow-down-tray class="w-6 h-6" />
                                            @case('course')        <x-heroicon-o-academic-cap class="w-6 h-6" />
                                            @case('event')         <x-heroicon-o-calendar-days class="w-6 h-6" />
                                            @case('appointment')   <x-heroicon-o-clock class="w-6 h-6" />
                                            @case('donation')      <x-heroicon-o-heart class="w-6 h-6" />
                                            @case('blog')          <x-heroicon-o-newspaper class="w-6 h-6" />
                                            @case('physical')      <x-heroicon-o-gift class="w-6 h-6" />
                                            @default               <x-heroicon-o-cube class="w-6 h-6" />
                                        @endswitch
                                    </div>
                                @endif
                                @if ($product->has_discount)
                                    <span class="absolute -top-1 -right-1 px-1.5 py-0.5 rounded-md bg-error text-white text-[9px] font-bold leading-tight">
                                        -{{ $product->discountPercentage }}%
                                    </span>
                                @endif
                            </div>

                            {{-- Content (flex-1, takes available space) --}}
                            <div class="flex-1 min-w-0 self-center">
                                <p class="text-[10px] font-bold text-brand-600 uppercase tracking-wider leading-none truncate">
                                    {{ $product->typeLabel }}
                                </p>
                                <h3 class="mt-1 font-semibold text-sm text-ink-900 leading-snug group-hover:text-brand-600 transition-colors truncate">
                                    {{ $product->title }}
                                </h3>
                                <p class="text-xs text-ink-500 mt-0.5 flex items-center gap-1">
                                    @switch($product->type)
                                        @case('donation')
                                            <x-heroicon-o-heart class="w-3 h-3 flex-shrink-0" /> <span class="truncate">Support creator</span>
                                            @break
                                        @case('appointment')
                                            <x-heroicon-o-clock class="w-3 h-3 flex-shrink-0" /> <span class="truncate">{{ $product->durationFormatted }}</span>
                                            @break
                                        @case('event')
                                            <x-heroicon-o-calendar class="w-3 h-3 flex-shrink-0" /> <span class="truncate">{{ \Carbon\Carbon::parse($product->eventDate)->format('d M Y') }}</span>
                                            @break
                                        @case('course')
                                            <x-heroicon-o-play class="w-3 h-3 flex-shrink-0" /> <span class="truncate">{{ $product->courseModules }} modules</span>
                                            @break
                                        @case('blog')
                                            <x-heroicon-o-document-text class="w-3 h-3 flex-shrink-0" /> <span class="truncate">{{ $product->readTime ?? '5 min' }} baca</span>
                                            @break
                                        @default
                                            <x-heroicon-o-shopping-bag class="w-3 h-3 flex-shrink-0" /> <span class="truncate">{{ $product->sales_count ?? 0 }} terjual</span>
                                    @endswitch
                                </p>
                            </div>

                            {{-- Right: price (fixed width, never wraps) --}}
                            <div class="flex-shrink-0 text-right self-center pl-1">
                                @if ($product->type === 'donation')
                                    <x-heroicon-o-heart class="w-5 h-5 text-brand-500 ml-auto" />
                                @else
                                    <p class="text-sm font-bold text-ink-900 whitespace-nowrap">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                    @if ($product->has_discount)
                                        <p class="text-[10px] text-ink-400 line-through whitespace-nowrap">Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <x-heroicon-o-inbox class="w-8 h-8 text-ink-400" />
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-ink-900">Belum ada produk</h3>
                        <p class="mt-1 text-sm text-ink-500">{{ '@' . $creator->username }} belum menambahkan produk.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ─── Floating action: share button (actual FAB) --}}
        <button onclick="navigator.share ? navigator.share({title: '{{ '@' . $creator->username }}', url: window.location.href}).catch(() => copyLink()) : copyLink()"
                class="fixed bottom-6 right-6 z-30 inline-flex items-center gap-2 px-5 py-3 bg-ink-900 hover:bg-brand-500 text-white text-sm font-bold rounded-full shadow-card-hover hover:scale-105 active:scale-95 transition-all duration-200">
            <x-heroicon-o-share class="w-4 h-4" />
            <span class="hidden sm:inline">Bagikan</span>
        </button>
        <script>
        function copyLink() {
            const btn = event.currentTarget;
            const orig = btn.innerHTML;
            navigator.clipboard.writeText(window.location.href).then(() => {
                btn.innerHTML = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg><span class="hidden sm:inline">Link disalin!</span>';
                setTimeout(() => btn.innerHTML = orig, 2000);
            });
        }
        </script>

        {{-- ─── Branding footer ─── --}}
        @if ($creator->show_branding ?? true)
            <footer class="mt-12 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs text-ink-400 hover:text-brand-600 transition">
                    <span class="w-4 h-4 rounded bg-gradient-to-br from-brand-500 to-brand-700"></span>
                    <span class="font-semibold">Made with {{ config('app.name') }}</span>
                </a>
            </footer>
        @endif
    </main>
</body>
</html>
