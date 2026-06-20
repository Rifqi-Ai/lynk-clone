<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $module->title }} — {{ $product->title }}</title>
    <meta name="description" content="{{ Str::limit($module->description ?? $module->title, 160) }}">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><defs><linearGradient id='g' x1='0' x2='1' y1='0' y2='1'><stop offset='0' stop-color='%23FF5722'/><stop offset='1' stop-color='%23BF360C'/></linearGradient></defs><rect width='32' height='32' rx='8' fill='url(%23g)'/><text x='16' y='22' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900' font-size='18'>L</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 antialiased">
    {{-- Header --}}
    <header class="bg-white/95 backdrop-blur-md border-b border-ink-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('course.show', [$username, $product->id]) }}" class="flex items-center gap-1.5 text-xs font-semibold text-ink-600 hover:text-brand-600 transition-colors">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Course player
            </a>
            <div class="text-sm font-bold text-center flex-1 truncate text-ink-900">{{ $product->title }}</div>
            <a href="{{ $product->owner->profile_url }}" class="hidden sm:flex items-center gap-2 text-xs font-semibold text-ink-600 hover:text-brand-600 transition-colors flex-shrink-0">
                <img src="{{ $product->owner->avatar_url }}" alt="" class="w-6 h-6 rounded-full ring-1 ring-ink-200">
                {{ '@' . $product->owner->username }}
            </a>
        </div>

        {{-- Progress bar --}}
        @php
            $completedCount = count($completed);
            $progressPct = $modules->count() > 0 ? round(($completedCount / $modules->count()) * 100) : 0;
        @endphp
        <div class="h-1 bg-ink-100">
            <div class="h-full bg-gradient-to-r from-brand-500 to-brand-700 transition-all duration-500" style="width: {{ $progressPct }}%"></div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main video player --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl overflow-hidden bg-black aspect-video shadow-card ring-1 ring-ink-900/5">
                @if ($module->youtube_id)
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $module->youtube_id }}?rel=0&autoplay=1" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                @elseif ($module->vimeo_id)
                    <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ $module->vimeo_id }}?autoplay=1" frameborder="0" allowfullscreen></iframe>
                @elseif ($module->video_url)
                    <video controls autoplay class="w-full h-full"><source src="{{ $module->video_url }}"></video>
                @else
                    <div class="w-full h-full flex items-center justify-center text-white/60 text-sm">Video belum tersedia</div>
                @endif
            </div>

            <div class="card-warm p-5 sm:p-6">
                <div class="flex items-center gap-2 text-xs text-ink-500 font-semibold uppercase tracking-wider mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-brand-50 text-brand-700">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        Module {{ $module->position + 1 }} of {{ $modules->count() }}
                    </span>
                    <span>·</span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ $module->duration_minutes }} min
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-balance mt-2">{{ $module->title }}</h1>

                @if ($module->description)
                    <div class="prose prose-sm max-w-none mt-4 text-ink-700">
                        {!! nl2br(e($module->description)) !!}
                    </div>
                @endif

                <div class="mt-6 pt-6 border-t border-ink-100 flex flex-wrap gap-3 items-center">
                    <form method="POST" action="{{ route('course.complete', [$username, $product->id, $module->id]) }}">
                        @csrf
                        <button type="submit" class="btn {{ in_array($module->id, $completed) ? 'btn-secondary' : 'btn-primary' }}">
                            @if (in_array($module->id, $completed))
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                Sudah selesai
                            @else
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                Tandai selesai
                            @endif
                        </button>
                    </form>

                    @if ($nextModule)
                        <a href="{{ route('course.watch', [$username, $product->id, $nextModule->id]) }}" class="btn-cta">
                            Next: {{ Str::limit($nextModule->title, 25) }}
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-success/10 text-success font-bold text-sm">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Course selesai! 🎉
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar: module list --}}
        <aside>
            <div class="card-warm p-4 sticky top-24">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-ink-100">
                    <h2 class="text-xs font-black text-ink-700 uppercase tracking-wider">Modules</h2>
                    <span class="text-xs font-bold text-brand-600">{{ $completedCount }}/{{ $modules->count() }}</span>
                </div>

                <ol class="space-y-1.5">
                    @foreach ($modules as $i => $m)
                        @php
                            $done = in_array($m->id, $completed);
                            $current = $m->id === $module->id;
                        @endphp
                        <li>
                            <a href="{{ route('course.watch', [$username, $product->id, $m->id]) }}"
                               class="flex items-start gap-3 p-2.5 rounded-xl transition-all
                                   {{ $current ? 'bg-gradient-to-r from-brand-50 to-amber-50 ring-1 ring-brand-300 shadow-sm' : 'hover:bg-ink-50' }}
                                   {{ $done && !$current ? 'opacity-70' : '' }}">
                                <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-black
                                    {{ $done ? 'bg-success text-white' : ($current ? 'bg-brand-500 text-white' : 'bg-ink-100 text-ink-700') }}">
                                    @if ($done)
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-xs font-bold {{ $current ? 'text-brand-700' : 'text-ink-900' }} line-clamp-2 break-words">{{ $m->title }}</span>
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