<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>@yield('title', config('app.name', 'Linka') . ' — Powering Creators Economy')</title>
    <meta name="description" content="@yield('description', 'Create your page to sell digital products, courses, and services. Free to start.')">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- OG / Facebook --}}
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('description', 'Create your page to sell digital products.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:image" content="@yield('og_image', asset('og-default.png'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name', 'Linka') }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', config('app.name'))">
    <meta name="twitter:description" content="@yield('description', 'Create your page to sell digital products.')">
    <meta name="twitter:image" content="@yield('og_image', asset('og-default.png'))">

    {{-- Favicon (gradient + L) --}}
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><defs><linearGradient id='g' x1='0' x2='1' y1='0' y2='1'><stop offset='0' stop-color='%23FF5722'/><stop offset='1' stop-color='%23BF360C'/></linearGradient></defs><rect width='32' height='32' rx='8' fill='url(%23g)'/><text x='16' y='22' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900' font-size='18'>L</text></svg>">

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page-specific head --}}
    @stack('head')
</head>
<body class="antialiased min-h-screen flex flex-col bg-ink-50 text-ink-900">

    {{-- Skip link --}}
    <a href="#main" class="skip-link">Skip to content</a>

    {{-- Navigation --}}
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-ink-100">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-sm group-hover:shadow-cta transition-shadow">
                        <span class="text-white font-black text-base">L</span>
                    </div>
                    <span class="font-bold text-lg text-ink-900 tracking-tight">{{ config('app.name', 'Linka') }}</span>
                </a>

                {{-- Center nav (desktop) --}}
                <div class="hidden md:flex items-center gap-7 text-sm font-semibold text-ink-700">
                    <a href="{{ route('home') }}#features" class="hover:text-brand-600 transition-colors">Features</a>
                    <a href="{{ route('pricing') }}" class="hover:text-brand-600 transition-colors">Pricing</a>
                    <a href="{{ route('faq') }}" class="hover:text-brand-600 transition-colors">FAQ</a>
                    <a href="{{ route('about') }}" class="hover:text-brand-600 transition-colors">About</a>
                </div>

                {{-- Right actions --}}
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard.index') }}" class="btn-ghost-ink">Dashboard</a>
                        <a href="{{ auth()->user()->profile_url }}" class="hidden sm:inline-flex items-center gap-2 h-10 px-3 rounded-xl bg-ink-50 hover:bg-ink-100 text-sm font-semibold text-ink-900 transition">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-[10px] font-bold">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span>{{ '@' . auth()->user()->username }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost-ink hidden sm:inline-flex">Sign In</a>
                        <a href="{{ route('register') }}" class="btn-cta text-sm h-10 px-4">Sign Up Free</a>
                    @endauth

                    {{-- Mobile menu toggle --}}
                    <button type="button" id="mobile-menu-toggle" class="md:hidden p-2 -mr-2 text-ink-700" aria-label="Open menu">
                        <x-heroicon-o-bars-3 class="w-6 h-6" />
                    </button>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div id="mobile-menu" class="md:hidden hidden border-t border-ink-100 py-3 space-y-1">
                <a href="{{ route('home') }}#features" class="block px-3 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100 rounded-lg">Features</a>
                <a href="{{ route('pricing') }}" class="block px-3 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100 rounded-lg">Pricing</a>
                <a href="{{ route('faq') }}" class="block px-3 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100 rounded-lg">FAQ</a>
                <a href="{{ route('about') }}" class="block px-3 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100 rounded-lg">About</a>
                @guest
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100 rounded-lg">Sign In</a>
                @endguest
            </div>
        </nav>
    </header>

    {{-- Flash messages --}}
    @if (session('success') || session('error') || session('info') || $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-2">
            @if (session('success'))
                <div class="flex items-center gap-3 rounded-xl bg-success/10 border border-success/30 text-success px-4 py-3 animate-fade-in">
                    <x-heroicon-s-check-circle class="w-5 h-5 flex-shrink-0" />
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('info'))
                <div class="flex items-center gap-3 rounded-xl bg-info/10 border border-info/30 text-info px-4 py-3 animate-fade-in">
                    <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0" />
                    <p class="text-sm font-semibold">{{ session('info') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center gap-3 rounded-xl bg-error/10 border border-error/30 text-error px-4 py-3 animate-fade-in">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5 flex-shrink-0" />
                    <p class="text-sm font-semibold">{{ session('error') }}</p>
                </div>
            @endif
            @if ($errors->any() && !request()->routeIs('login', 'register'))
                <div class="rounded-xl bg-error/10 border border-error/30 text-error px-4 py-3 animate-fade-in">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- Main content --}}
    <main id="main" class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-ink-100 bg-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-1">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                            <span class="text-white font-black text-sm">L</span>
                        </div>
                        <span class="font-bold text-lg text-ink-900">{{ config('app.name', 'Linka') }}</span>
                    </a>
                    <p class="text-sm text-ink-500 leading-relaxed">Powering the Indonesian creator economy. Satu link, jual apapun.</p>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-ink-900 mb-3">Product</h4>
                    <ul class="space-y-2 text-sm text-ink-500">
                        <li><a href="{{ route('home') }}#features" class="hover:text-brand-600 transition-colors">Features</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-brand-600 transition-colors">Pricing</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-brand-600 transition-colors">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-ink-900 mb-3">Company</h4>
                    <ul class="space-y-2 text-sm text-ink-500">
                        <li><a href="{{ route('about') }}" class="hover:text-brand-600 transition-colors">About</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-brand-600 transition-colors">Terms</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-brand-600 transition-colors">Privacy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-ink-900 mb-3">Get Started</h4>
                    <ul class="space-y-2 text-sm text-ink-500">
                        <li><a href="{{ route('register') }}" class="hover:text-brand-600 transition-colors">Sign up free</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-brand-600 transition-colors">Sign in</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 pt-6 border-t border-ink-100 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-ink-500">
                <div>&copy; {{ date('Y') }} {{ config('app.name', 'Linka') }}. All rights reserved.</div>
                <div class="flex items-center gap-1">Made with <x-heroicon-s-heart class="w-3 h-3 text-brand-500" /> for creators.</div>
            </div>
        </div>
    </footer>

    {{-- Mobile menu JS --}}
    <script>
    document.getElementById('mobile-menu-toggle')?.addEventListener('click', () => {
        document.getElementById('mobile-menu')?.classList.toggle('hidden');
    });
    </script>
</body>
</html>
