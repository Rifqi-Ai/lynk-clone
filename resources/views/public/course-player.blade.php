<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->title }} — Course</title>
    <meta name="description" content="{{ Str::limit($product->description ?? $product->title, 160) }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><defs><linearGradient id='g' x1='0' x2='1' y1='0' y2='1'><stop offset='0' stop-color='%23FF5722'/><stop offset='1' stop-color='%23BF360C'/></linearGradient></defs><rect width='32' height='32' rx='8' fill='url(%23g)'/><text x='16' y='22' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900' font-size='18'>L</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 antialiased">
    {{-- Header --}}
    <header class="bg-white/95 backdrop-blur-md border-b border-ink-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ $product->url }}" class="flex items-center gap-1.5 text-xs font-semibold text-ink-600 hover:text-brand-600 transition-colors">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Course page
            </a>
            <div class="text-sm font-bold text-center flex-1 truncate text-ink-900">{{ $product->title }}</div>
            <div class="text-xs font-bold text-brand-600 flex-shrink-0">{{ $progressPct }}% selesai</div>
        </div>
        <div class="h-1 bg-ink-100">
            <div class="h-full bg-gradient-to-r from-brand-500 to-brand-700 transition-all duration-500" style="width: {{ $progressPct }}%"></div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main player --}}
        <div class="lg:col-span-2 space-y-4">
            @if ($firstModule)
                <div class="rounded-2xl overflow-hidden bg-black aspect-video shadow-card ring-1 ring-ink-900/5">
                    @if ($firstModule->youtube_id)
                        <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $firstModule->youtube_id }}?rel=0" frameborder="0" allowfullscreen></iframe>
                    @elseif ($firstModule->vimeo_id)
                        <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ $firstModule->vimeo_id }}" frameborder="0" allowfullscreen></iframe>
                    @elseif ($firstModule->video_url)
                        <video controls class="w-full h-full"><source src="{{ $firstModule->video_url }}"></video>
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white/60 text-sm">Video belum tersedia</div>
                    @endif
                </div>

                <div class="card-warm p-5 sm:p-6">
                    <div class="flex items-center gap-2 text-xs text-ink-500 font-semibold uppercase tracking-wider mb-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-brand-50 text-brand-700">Module 1 of {{ $modules->count() }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-balance">{{ $firstModule->title }}</h1>
                    @if ($firstModule->description)
                        <div class="prose prose-sm max-w-none mt-3 text-ink-700">
                            {!! nl2br(e($firstModule->description)) !!}
                        </div>
                    @endif

                    <div class="mt-6 pt-6 border-t border-ink-100 flex flex-wrap gap-3">
                        <a href="{{ route('course.watch', [$username, $product->id, $firstModule->id]) }}" class="btn-cta">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                            Tonton module
                        </a>
                        <form method="POST" action="{{ route('course.complete', [$username, $product->id, $firstModule->id]) }}">
                            @csrf
                            <button type="submit" class="btn-secondary">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                Tandai selesai
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card-warm p-12 text-center">
                    <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                    </div>
                    <h1 class="text-xl font-black mb-2">{{ $product->title }}</h1>
                    <p class="text-sm text-ink-500 max-w-md mx-auto">Modules belum tersedia. Creator akan segera menambahkan materi course-nya.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar: module list --}}
        <aside>
            <div class="card-warm p-4 sticky top-24">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-ink-100">
                    <h2 class="text-xs font-black text-ink-700 uppercase tracking-wider">Modules</h2>
                    <span class="text-xs font-bold text-brand-600">{{ count($completed) }}/{{ $modules->count() }}</span>
                </div>

                <ol class="space-y-1.5">
                    @foreach ($modules as $i => $m)
                        @php $done = in_array($m->id, $completed); @endphp
                        <li>
                            <a href="{{ route('course.watch', [$username, $product->id, $m->id]) }}"
                               class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-ink-50 transition-colors {{ $done ? 'opacity-70' : '' }}">
                                <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-black
                                    {{ $done ? 'bg-success text-white' : 'bg-ink-100 text-ink-700' }}">
                                    @if ($done)
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-xs font-bold text-ink-900 line-clamp-2 break-words">{{ $m->title }}</span>
                                    <span class="block text-[10px] text-ink-500 mt-0.5">{{ $m->duration_minutes }} min</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-6 pt-4 border-t border-ink-100 space-y-2 text-xs">
                    <div>
                        <div class="text-ink-500 uppercase font-bold text-[10px] mb-0.5">Order ID</div>
                        <div class="font-mono text-ink-700">{{ $order->id }}</div>
                    </div>
                    <div>
                        <div class="text-ink-500 uppercase font-bold text-[10px] mb-0.5">Email</div>
                        <div class="text-ink-700 truncate">{{ $order->buyer_email }}</div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</body>
</html>