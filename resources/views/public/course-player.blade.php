<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->title }} — Course Player</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50">
    {{-- Header --}}
    <header class="bg-white border-b border-ink-100 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ $product->url }}" class="flex items-center gap-2 text-xs text-ink-500 hover:text-brand-500">
                ← Kembali ke course page
            </a>
            <div class="text-sm font-bold text-center flex-1 truncate">{{ $product->title }}</div>
            <div class="text-xs text-ink-500">{{ $progressPct }}% selesai</div>
        </div>
        {{-- Progress bar --}}
        <div class="h-1 bg-ink-100">
            <div class="h-full bg-brand-500 transition-all duration-300" style="width: {{ $progressPct }}%"></div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main video player --}}
        <div class="lg:col-span-2">
            @if ($firstModule)
                <div class="rounded-xl overflow-hidden bg-black aspect-video">
                    @if ($firstModule->youtube_id)
                        <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $firstModule->youtube_id }}?rel=0" frameborder="0" allowfullscreen></iframe>
                    @elseif ($firstModule->vimeo_id)
                        <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ $firstModule->vimeo_id }}" frameborder="0" allowfullscreen></iframe>
                    @elseif ($firstModule->video_url)
                        <video controls class="w-full h-full"><source src="{{ $firstModule->video_url }}"></video>
                    @endif
                </div>
                <div class="mt-4">
                    <div class="text-xs text-ink-500 mb-1">{{ $product->typeIcon }} {{ $product->typeLabel }} · Module 1 of {{ $modules->count() }}</div>
                    <h1 class="text-2xl font-black">{{ $firstModule->title }}</h1>
                    @if ($firstModule->description)
                        <p class="mt-2 text-sm text-ink-700">{{ $firstModule->description }}</p>
                    @endif
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('course.watch', [$username, $product->id, $firstModule->id]) }}" class="btn-primary btn-sm">
                            ▶ Tonton module ini
                        </a>
                        <form method="POST" action="{{ route('course.complete', [$username, $product->id, $firstModule->id]) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-secondary btn-sm">
                                ✓ Tandai selesai
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card p-8 text-center">
                    <div class="text-4xl mb-3">📚</div>
                    <h1 class="text-xl font-bold mb-2">{{ $product->title }}</h1>
                    <p class="text-sm text-ink-500">Modules belum tersedia. Creator akan segera menambah materi.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar: module list --}}
        <aside>
            <div class="card p-4 sticky top-24">
                <div class="text-xs font-bold text-ink-500 uppercase mb-3">Modules ({{ $modules->count() }})</div>
                <ol class="space-y-1">
                    @foreach ($modules as $i => $module)
                        @php $done = in_array($module->id, $completed); @endphp
                        <li>
                            <a href="{{ route('course.watch', [$username, $product->id, $module->id]) }}"
                               class="flex items-start gap-3 p-2 rounded-lg hover:bg-ink-50 {{ $done ? 'opacity-60' : '' }}">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full {{ $done ? 'bg-brand-500 text-white' : 'bg-ink-100 text-ink-700' }} text-xs font-bold flex items-center justify-center">
                                    @if ($done) ✓ @else {{ $i + 1 }} @endif
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-xs font-bold text-ink-900 truncate">{{ $module->title }}</span>
                                    <span class="block text-[10px] text-ink-500">⏱ {{ $module->duration_minutes }} min</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-6 pt-4 border-t border-ink-100">
                    <div class="text-xs text-ink-500 mb-2">Order</div>
                    <div class="font-mono text-xs">{{ $order->id }}</div>
                    <div class="mt-1 text-xs text-ink-500">Email: {{ $order->buyer_email }}</div>
                </div>
            </div>
        </aside>
    </div>
</body>
</html>