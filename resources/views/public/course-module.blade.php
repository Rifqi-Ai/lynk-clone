<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $module->title }} — {{ $product->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50">
    <header class="bg-white border-b border-ink-100 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('course.show', [$username, $product->id]) }}?email={{ $order->buyer_email }}" class="flex items-center gap-2 text-xs text-ink-500 hover:text-brand-500">
                ← Kembali ke course player
            </a>
            <div class="text-sm font-bold text-center flex-1 truncate">{{ $product->title }}</div>
            <div class="text-xs text-ink-500">{{ $product->owner->username }}</div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="rounded-xl overflow-hidden bg-black aspect-video">
                @if ($module->youtube_id)
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $module->youtube_id }}?rel=0&autoplay=1" frameborder="0" allowfullscreen></iframe>
                @elseif ($module->vimeo_id)
                    <iframe class="w-full h-full" src="https://player.vimeo.com/video/{{ $module->vimeo_id }}?autoplay=1" frameborder="0" allowfullscreen></iframe>
                @elseif ($module->video_url)
                    <video controls autoplay class="w-full h-full"><source src="{{ $module->video_url }}"></video>
                @endif
            </div>
            <div class="mt-4">
                <div class="text-xs text-ink-500 mb-1">Module {{ $module->position + 1 }} of {{ $modules->count() }}</div>
                <h1 class="text-2xl font-black">{{ $module->title }}</h1>
                @if ($module->description)
                    <p class="mt-2 text-sm text-ink-700 whitespace-pre-line">{{ $module->description }}</p>
                @endif

                <div class="mt-6 flex flex-wrap gap-2 items-center">
                    <form method="POST" action="{{ route('course.complete', [$username, $product->id, $module->id]) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm">
                            @if (in_array($module->id, $completed)) ✓ Sudah selesai @else Tandai selesai @endif
                        </button>
                    </form>

                    @if ($nextModule)
                        <a href="{{ route('course.watch', [$username, $product->id, $nextModule->id]) }}" class="btn-primary btn-sm">
                            Next: {{ $nextModule->title }} →
                        </a>
                    @endif

                    <span class="text-xs text-ink-500 ml-auto">⏱ {{ $module->duration_minutes }} min</span>
                </div>
            </div>
        </div>

        <aside>
            <div class="card p-4 sticky top-24">
                <div class="text-xs font-bold text-ink-500 uppercase mb-3">Course modules</div>
                <ol class="space-y-1">
                    @foreach ($modules as $i => $m)
                        @php $done = in_array($m->id, $completed); $current = $m->id === $module->id; @endphp
                        <li>
                            <a href="{{ route('course.watch', [$username, $product->id, $m->id]) }}"
                               class="flex items-start gap-3 p-2 rounded-lg hover:bg-ink-50 {{ $current ? 'bg-brand-50 border border-brand-200' : '' }} {{ $done ? 'opacity-60' : '' }}">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full {{ $done ? 'bg-brand-500 text-white' : ($current ? 'bg-brand-100 text-brand-700' : 'bg-ink-100 text-ink-700') }} text-xs font-bold flex items-center justify-center">
                                    @if ($done) ✓ @else {{ $i + 1 }} @endif
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-xs font-bold {{ $current ? 'text-brand-700' : 'text-ink-900' }} truncate">{{ $m->title }}</span>
                                    <span class="block text-[10px] text-ink-500">⏱ {{ $m->duration_minutes }} min</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</body>
</html>