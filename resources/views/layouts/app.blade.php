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

    {{-- Fonts (Lato, like lynk.id) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%232AB57D'/><text x='16' y='22' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900' font-size='18'>L</text></svg>">

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page-specific head --}}
    @stack('head')
</head>
<body class="bg-white text-ink-900 antialiased">
    {{-- Navigation --}}
    <nav class="border-b border-ink-100 bg-white/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-brand-500 flex items-center justify-center">
                        <span class="text-white font-black text-lg">L</span>
                    </div>
                    <span class="font-black text-xl text-ink-900">{{ config('app.name', 'Linka') }}</span>
                </a>

                {{-- Center nav --}}
                <div class="hidden md:flex items-center gap-7 text-sm font-bold text-ink-700">
                    <a href="{{ route('home') }}#features" class="hover:text-brand-500 transition">Features</a>
                    <a href="{{ route('pricing') }}" class="hover:text-brand-500 transition">Pricing</a>
                    <a href="{{ route('faq') }}" class="hover:text-brand-500 transition">FAQ</a>
                    <a href="{{ route('about') }}" class="hover:text-brand-500 transition">About</a>
                </div>

                {{-- Right actions --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard.index') }}" class="btn-ghost">Dashboard</a>
                        <a href="{{ auth()->user()->profile_url }}" class="btn-secondary btn-sm">{{ '@' . auth()->user()->username }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost hidden sm:inline-flex">Sign In</a>
                        <a href="{{ route('register') }}" class="btn-primary">Sign Up Free</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if (session('success') || session('error') || $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if (session('success'))
                <div class="rounded-lg bg-brand-100 border border-brand-500/30 text-brand-800 px-4 py-3 animate-fade-in">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 border border-danger/30 text-danger px-4 py-3 animate-fade-in">
                    ⚠️ {{ session('error') }}
                </div>
            @endif
            @if ($errors->any() && !request()->routeIs('login', 'register'))
                <div class="rounded-lg bg-red-50 border border-danger/30 text-danger px-4 py-3 animate-fade-in">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- Main content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-ink-100 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid md:grid-cols-4 gap-8 text-sm">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-brand-500 flex items-center justify-center">
                            <span class="text-white font-black text-sm">L</span>
                        </div>
                        <span class="font-black text-lg">{{ config('app.name', 'Linka') }}</span>
                    </div>
                    <p class="text-ink-500">Powering the creator economy.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-3">Product</h4>
                    <ul class="space-y-2 text-ink-500">
                        <li><a href="{{ route('home') }}#features" class="hover:text-brand-500">Features</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-brand-500">Pricing</a></li>
                        <li><a href="{{ route('faq') }}" class="hover:text-brand-500">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-3">Company</h4>
                    <ul class="space-y-2 text-ink-500">
                        <li><a href="{{ route('about') }}" class="hover:text-brand-500">About</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-brand-500">Terms</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-brand-500">Privacy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-3">Get Started</h4>
                    <ul class="space-y-2 text-ink-500">
                        <li><a href="{{ route('register') }}" class="hover:text-brand-500">Sign up free</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-brand-500">Sign in</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-ink-100 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-ink-500">
                <div>&copy; {{ date('Y') }} {{ config('app.name', 'Linka') }}. All rights reserved.</div>
                <div>Made with 💚 for creators.</div>
            </div>
        </div>
    </footer>
</body>
</html>