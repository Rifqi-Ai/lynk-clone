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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ $creator->avatar_url }}">

    {{-- JSON-LD structured data --}}
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
<body class="bg-ink-50 antialiased">
    <section class="py-10 px-4 min-h-screen">
        <div class="max-w-2xl mx-auto">
            {{-- Breadcrumb --}}
            <div class="text-center mb-4">
                <a href="{{ $creator->profile_url }}" class="inline-flex items-center gap-1 text-xs text-ink-500 hover:text-brand-500">
                    ← {{ '@' . $creator->username }}
                </a>
            </div>

            <div class="card overflow-hidden">
                {{-- Thumbnail --}}
                <div class="aspect-video bg-ink-100 overflow-hidden relative">
                    @if ($product->thumbnail_url)
                        <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $product->title }}">
                    @endif
                    <div class="absolute top-3 left-3">
                        <span class="badge bg-white/90 text-ink-900 backdrop-blur">
                            {{ $product->typeIcon }} {{ $product->typeLabel }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <h1 class="text-2xl font-black text-ink-900">{{ $product->title }}</h1>

                    {{-- Price + discount --}}
                    <div class="mt-3 flex items-baseline gap-2 flex-wrap">
                        @if ($product->type === 'donation')
                            <span class="text-lg font-bold text-ink-700">Choose amount below</span>
                        @else
                            <span class="text-3xl font-black text-brand-500">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            @if ($product->has_discount)
                                <span class="text-base text-ink-400 line-through">Rp {{ number_format($product->compare_at_price, 0, ',', '.') }}</span>
                                <span class="badge-danger">-{{ $product->discountPercentage }}%</span>
                            @endif
                        @endif
                    </div>

                    {{-- Type-specific meta info --}}
                    <div class="mt-4 flex flex-wrap gap-3 text-xs">
                        @if ($product->type === 'appointment')
                            <span class="badge-neutral">⏱️ {{ $product->durationFormatted }}</span>
                            @if ($product->meta('location_type'))
                                <span class="badge-neutral">📍 {{ ucfirst($product->meta('location_type')) }}</span>
                            @endif
                        @elseif ($product->type === 'event')
                            @if ($product->eventDate)
                                <span class="badge-neutral">📅 {{ \Carbon\Carbon::parse($product->eventDate)->format('d M Y H:i') }}</span>
                            @endif
                            @if ($product->meta('capacity'))
                                <span class="badge-neutral">👥 {{ $product->meta('capacity') }} seats</span>
                            @endif
                        @elseif ($product->type === 'course')
                            @if ($product->courseDuration > 0)
                                <span class="badge-neutral">🎓 {{ round($product->courseDuration / 60, 1) }}h total</span>
                            @endif
                            @if ($product->courseModules > 0)
                                <span class="badge-neutral">📚 {{ $product->courseModules }} modules</span>
                            @endif
                            @if ($product->meta('level'))
                                <span class="badge-neutral">📊 {{ ucfirst($product->meta('level')) }}</span>
                            @endif
                        @elseif ($product->type === 'physical')
                            @if ($product->stockQuantity !== null)
                                @if ($product->inStock)
                                    <span class="badge-success">✅ In stock ({{ $product->stockQuantity }})</span>
                                @else
                                    <span class="badge-danger">❌ Out of stock</span>
                                @endif
                            @endif
                        @elseif ($product->type === 'donation' && $product->donationGoal)
                            <span class="badge-neutral">🎯 Goal: Rp {{ number_format($product->donationGoal, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    {{-- Description (Markdown rendered for blog) --}}
                    @if ($product->description)
                        <div class="mt-4 text-sm text-ink-700 leading-relaxed
                            @if ($product->type === 'blog') prose prose-sm max-w-none @else whitespace-pre-line @endif">
                            @if ($product->type === 'blog')
                                {!! \Illuminate\Support\Str::markdown($product->description) !!}
                            @else
                                {{ $product->description }}
                            @endif
                        </div>
                    @endif

                    {{-- BLOG: paywall preview snippet --}}
                    @if ($product->type === 'blog' && $product->isPaywalled && $product->meta('preview_length'))
                        @php
                            $fullLength = mb_strlen(strip_tags($product->description));
                            $previewLength = (int) $product->meta('preview_length', 300);
                            $previewText = mb_substr($product->description, 0, $previewLength);
                            $remaining = $fullLength - mb_strlen($previewText);
                        @endphp
                        <div class="mt-6 p-4 rounded-lg bg-gradient-to-b from-transparent to-ink-50 border-t-2 border-dashed border-ink-200">
                            <p class="text-xs text-ink-500 mb-3">🔒 {{ $remaining }} more characters — unlock to read</p>
                            <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}"
                               class="btn-primary btn-block">
                                🔓 Unlock for Rp {{ number_format($product->price, 0, ',', '.') }}
                            </a>
                        </div>
                    @endif

                    {{-- COURSE: curriculum + video preview --}}
                    @if ($product->type === 'course')
                        @if ($product->meta('video_preview_url'))
                            <div class="mt-6 rounded-xl overflow-hidden bg-black aspect-video">
                                @php $v = $product->meta('video_preview_url'); @endphp
                                @if (str_contains($v, 'youtube.com') || str_contains($v, 'youtu.be'))
                                    @php
                                        preg_match('/(?:youtu\.be\/|v=|\/embed\/)([\w-]{11})/', $v, $m);
                                        $ytId = $m[1] ?? null;
                                    @endphp
                                    @if ($ytId)
                                        <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $ytId }}" frameborder="0" allowfullscreen></iframe>
                                    @endif
                                @elseif (str_contains($v, 'vimeo.com'))
                                    @php
                                        preg_match('/vimeo\.com\/(\d+)/', $v, $m);
                                        $vimeoId = $m[1] ?? null;
                                    @endphp
                                    @if ($vimeoId)
                                        <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ $vimeoId }}" frameborder="0" allowfullscreen></iframe>
                                    @endif
                                @else
                                    <video controls class="w-full h-full"><source src="{{ $v }}"></video>
                                @endif
                            </div>
                        @endif
                        @if (is_array($product->meta('curriculum')) && count($product->meta('curriculum')) > 0)
                            <div class="mt-6">
                                <h3 class="font-bold text-sm mb-2">📚 Curriculum</h3>
                                <ol class="space-y-2">
                                    @foreach ($product->meta('curriculum') as $i => $module)
                                        <li class="flex items-start gap-2 text-xs">
                                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-100 text-brand-700 text-[10px] font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                            <span class="text-ink-700">{{ $module['title'] ?? 'Module ' . ($i + 1) }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif
                    @endif

                    {{-- EVENT: countdown + share --}}
                    @if ($product->type === 'event' && $product->eventDate)
                        @php
                            $eventTime = \Carbon\Carbon::parse($product->eventDate);
                            $isPast = $eventTime->isPast();
                        @endphp
                        <div class="mt-6 p-4 rounded-lg bg-ink-900 text-white">
                            <div class="text-xs opacity-70 mb-1">{{ $isPast ? 'Event ended' : 'Starts in' }}</div>
                            <div class="text-2xl font-black">{{ $eventTime->format('D, d M Y · H:i') }} WIB</div>
                            @if (!$isPast)
                                @php
                                    $diff = now()->diff($eventTime);
                                @endphp
                                <div class="mt-2 flex gap-2 text-xs">
                                    <span class="bg-white/10 px-2 py-1 rounded">{{ $diff->d }}d</span>
                                    <span class="bg-white/10 px-2 py-1 rounded">{{ $diff->h }}h</span>
                                    <span class="bg-white/10 px-2 py-1 rounded">{{ $diff->i }}m</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- PHYSICAL: shipping info --}}
                    @if ($product->type === 'physical')
                        @if ($product->meta('weight_grams'))
                            <div class="mt-4 p-3 rounded-lg bg-ink-50 text-xs text-ink-600">
                                📦 Ships from {{ $product->meta('ships_from', 'Indonesia') }} ·
                                Weight: {{ number_format($product->meta('weight_grams'), 0, ',', '.') }}g
                                @if ($product->meta('dimensions'))
                                    · {{ $product->meta('dimensions') }}
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- Type-specific CTA --}}
                    @php
                        $hasCourseAccess = false;
                        if ($product->type === 'course' && auth()->check()) {
                            $hasCourseAccess = \App\Models\Order::where('product_id', $product->id)
                                ->where('buyer_email', auth()->user()->email)
                                ->where('payment_status', 'paid')
                                ->exists();
                        }
                    @endphp
                    @if ($product->type === 'donation')
                        <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}" class="btn-primary btn-lg btn-block mt-6">
                            ☕ Support Now
                        </a>
                    @elseif ($product->type === 'appointment')
                        <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}" class="btn-primary btn-lg btn-block mt-6">
                            📅 Book Appointment
                        </a>
                    @elseif ($product->type === 'event')
                        <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}" class="btn-primary btn-lg btn-block mt-6">
                            🎟️ Get Ticket — Rp {{ number_format($product->price, 0, ',', '.') }}
                        </a>
                    @elseif ($product->type === 'course')
                        @if ($hasCourseAccess)
                            <a href="{{ route('course.show', [$creator->username, $product->id]) }}?email={{ auth()->user()->email }}" class="btn-primary btn-lg btn-block mt-6">
                                📚 Continue Learning →
                            </a>
                        @else
                            <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}" class="btn-primary btn-lg btn-block mt-6">
                                🎓 Enroll Now — Rp {{ number_format($product->price, 0, ',', '.') }}
                            </a>
                        @endif
                    @elseif ($product->type === 'blog' && !$product->isPaywalled)
                        <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}" class="btn-primary btn-lg btn-block mt-6">
                            📖 Read Full Post
                        </a>
                    @elseif ($product->type === 'blog' && $product->isPaywalled)
                        <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}" class="btn-primary btn-lg btn-block mt-6">
                            🔓 Unlock for Rp {{ number_format($product->price, 0, ',', '.') }}
                        </a>
                    @elseif ($product->type === 'physical' && !$product->inStock)
                        <button disabled class="btn-secondary btn-lg btn-block mt-6 opacity-50 cursor-not-allowed">Out of Stock</button>
                    @else
                        {{-- Buy Now + Add to Cart --}}
                        <div class="grid grid-cols-3 gap-2 mt-6">
                            <a href="{{ route('checkout.show', [$creator->username, $product->id]) }}"
                               class="btn-primary col-span-2">
                                Buy Now
                            </a>
                            <form method="POST" action="{{ route('cart.add', [$creator->username, $product->id]) }}">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-secondary w-full">🛒 Add</button>
                            </form>
                        </div>
                    @endif

                    <div class="mt-3 text-xs text-ink-500 text-center">
                        🔒 Secure payment powered by Duitku
                    </div>
                </div>
            </div>

            {{-- Creator info --}}
            <div class="mt-6 flex items-center gap-3 p-4 card">
                <img src="{{ $creator->avatar_url }}" class="w-12 h-12 rounded-full">
                <div class="flex-1">
                    <div class="font-bold">{{ '@' . $creator->username }}</div>
                    <div class="text-xs text-ink-500">{{ $creator->display_title }}</div>
                </div>
                <a href="{{ $creator->profile_url }}" class="btn-secondary btn-sm">View page</a>
            </div>
        </div>
    </section>
</body>
</html>