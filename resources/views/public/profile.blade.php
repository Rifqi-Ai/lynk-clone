<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ '@' . $creator->username }}{{ $creator->title ? ' — ' . $creator->title : '' }}</title>
    <meta name="description" content="{{ Str::limit($creator->bio ?? $creator->display_title, 160) }}">

    {{-- OG / Twitter --}}
    <meta property="og:title" content="{{ '@' . $creator->username }}">
    <meta property="og:description" content="{{ Str::limit($creator->bio ?? $creator->display_title, 200) }}">
    <meta property="og:type" content="profile">
    <meta property="og:image" content="{{ $creator->avatar_url }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ $creator->avatar_url }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased">
    <main class="min-h-screen py-10 px-4">
        <div class="max-w-xl mx-auto">
            {{-- Avatar + Header --}}
            <div class="text-center">
                <img src="{{ $creator->avatar_url }}" alt="{{ $creator->username }}"
                     class="w-24 h-24 rounded-full mx-auto border-4 border-white shadow-lg bg-brand-100 object-cover">
                <h1 class="mt-4 text-xl font-black text-ink-900">{{ '@' . $creator->username }}</h1>
                @if ($creator->title)
                    <p class="text-sm text-ink-500 mt-1">{{ $creator->title }}</p>
                @endif
                @if ($creator->bio)
                    <p class="text-sm text-ink-700 mt-3 max-w-md mx-auto leading-relaxed whitespace-pre-line">{{ $creator->bio }}</p>
                @endif

                {{-- Social links --}}
                @if (!empty($creator->social_links))
                    <div class="mt-4 flex justify-center gap-3">
                        @foreach ($creator->social_links as $link)
                            @if (!empty($link['url']))
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                   class="text-ink-500 hover:text-brand-500 transition" title="{{ $link['platform'] }}">
                                    @include('components.social-icon', ['platform' => $link['platform']])
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Search + filter bar --}}
            @if ($creator->products()->where('status', 'published')->count() > 2)
                <form method="GET" action="{{ $creator->profile_url }}" class="mt-6 card p-3 space-y-3">
                    {{-- Search box --}}
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari produk..." class="input pl-10 text-sm">
                        @if ($typeFilter)<input type="hidden" name="type" value="{{ $typeFilter }}">@endif
                        @if (($sort ?? 'latest') !== 'latest')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                    </div>

                    {{-- Type filters --}}
                    @if (!empty($typeCounts) && count($typeCounts) > 1)
                        <div class="flex flex-wrap gap-1.5">
                            <a href="{{ $creator->profile_url . ($search ? '?q=' . urlencode($search) : '') }}"
                               class="px-3 py-1 rounded-full text-xs font-bold {{ !$typeFilter ? 'bg-brand-500 text-white' : 'bg-ink-100 text-ink-700 hover:bg-ink-200' }}">
                                All ({{ array_sum($typeCounts) }})
                            </a>
                            @foreach (\App\Models\Product::TYPES as $key => $info)
                                @if (!empty($typeCounts[$key]))
                                    <a href="{{ $creator->profile_url . '?type=' . $key . ($search ? '&q=' . urlencode($search) : '') }}"
                                       class="px-3 py-1 rounded-full text-xs font-bold {{ $typeFilter === $key ? 'bg-brand-500 text-white' : 'bg-ink-100 text-ink-700 hover:bg-ink-200' }}">
                                        {{ $info['icon'] }} {{ $info['label'] }} ({{ $typeCounts[$key] }})
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Sort + active filter indicators --}}
                    <div class="flex items-center justify-between text-xs">
                        <div class="text-ink-500">
                            @if ($search || $typeFilter)
                                <span class="font-bold">{{ $products->count() }}</span> hasil
                                @if ($search)untuk "<span class="font-bold">{{ $search }}</span>"@endif
                                · <a href="{{ $creator->profile_url }}" class="text-brand-500 hover:underline">Reset filter</a>
                            @else
                                <span class="font-bold">{{ $products->count() }}</span> produk
                            @endif
                        </div>
                        @if ($products->count() > 1)
                            <select name="sort" onchange="this.form.submit()" class="text-xs bg-transparent border-0 focus:ring-0 text-ink-700 font-bold cursor-pointer">
                                <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="popular" {{ ($sort ?? '') === 'popular' ? 'selected' : '' }}>Terpopuler</option>
                                <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>Harga ↑</option>
                                <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>Harga ↓</option>
                            </select>
                        @endif
                    </div>
                </form>
            @endif

            {{-- Products list --}}
            <div class="mt-6 space-y-3">
                @forelse ($products as $product)
                    <a href="{{ $product->url }}" class="link-card animate-fade-in" style="text-align: left;">
                        <div class="flex items-center gap-3">
                            @if ($product->thumbnail_url)
                                <img src="{{ $product->thumbnail_url }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-brand-50 flex items-center justify-center text-2xl flex-shrink-0">
                                    {{ $product->typeIcon }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm truncate">{{ $product->title }}</span>
                                    <span class="text-[10px] text-ink-400 uppercase tracking-wider">{{ $product->typeLabel }}</span>
                                </div>
                                <div class="text-xs text-ink-500 mt-0.5">
                                    @if ($product->type === 'donation')
                                        ☕ Donation
                                    @elseif ($product->type === 'appointment')
                                        📅 {{ $product->durationFormatted }}
                                    @elseif ($product->type === 'event' && $product->eventDate)
                                        📅 {{ \Carbon\Carbon::parse($product->eventDate)->format('d M Y') }}
                                    @elseif ($product->type === 'course' && $product->courseModules > 0)
                                        🎓 {{ $product->courseModules }} modules
                                    @else
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                        @if ($product->has_discount)
                                            <span class="line-through text-ink-300 ml-1">Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @if ($product->type !== 'donation')
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-black text-brand-500">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                    @if ($product->has_discount)
                                        <span class="badge-danger text-[10px]">-{{ $product->discountPercentage }}%</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="text-center py-12 text-sm text-ink-500">
                        <div class="text-4xl mb-2">📦</div>
                        No products yet.
                    </div>
                @endforelse
            </div>

            {{-- Branding footer --}}
            @if ($creator->show_branding)
                <div class="mt-12 text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs text-ink-400 hover:text-ink-700 transition">
                        <div class="w-4 h-4 rounded bg-brand-500"></div>
                        <span class="font-bold">Made with {{ config('app.name') }}</span>
                    </a>
                </div>
            @endif
        </div>
    </main>
</body>
</html>