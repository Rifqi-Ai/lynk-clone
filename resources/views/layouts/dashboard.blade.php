<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%232AB57D'/><text x='16' y='22' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900' font-size='18'>L</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-ink-50 text-ink-900 antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="hidden lg:flex flex-col w-60 bg-white border-r border-ink-100 sticky top-0 h-screen">
            <div class="p-5 border-b border-ink-100">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center">
                        <span class="text-white font-black text-base">L</span>
                    </div>
                    <span class="font-black text-lg">{{ config('app.name') }}</span>
                </a>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                @php
                    $isActive = fn($route) => request()->routeIs($route) ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700 hover:bg-ink-50';
                @endphp
                <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isActive('dashboard.index') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Overview
                </a>
                <a href="{{ route('dashboard.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isActive('dashboard.products.*') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Products
                </a>
                <a href="{{ route('dashboard.fulfillment.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isActive('dashboard.fulfillment.*') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    Shipping
                </a>
                <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isActive('settings.profile') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <a href="{{ auth()->user()->profile_url }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-ink-700 hover:bg-ink-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View public page
                </a>
            </nav>

            <div class="p-4 border-t border-ink-100">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-9 h-9 rounded-full bg-brand-100">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-ink-500 truncate">{{ '@' . auth()->user()->username }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm btn-block text-danger">Sign out</button>
                </form>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar (mobile menu trigger area) --}}
            <header class="lg:hidden bg-white border-b border-ink-100 px-4 py-3 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2 -ml-2 text-ink-700 hover:bg-ink-50 rounded-lg" aria-label="Open menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-brand-500 flex items-center justify-center">
                            <span class="text-white font-black text-sm">L</span>
                        </div>
                        <span class="font-black">{{ config('app.name') }}</span>
                    </a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm text-danger">Sign out</button>
                </form>
            </header>

            {{-- Mobile menu drawer --}}
            <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-ink-100 sticky top-14 z-20 shadow-sm">
                <nav class="p-3 space-y-1 text-sm">
                    <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard.index') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Overview
                    </a>
                    <a href="{{ route('dashboard.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard.products.*') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Products
                    </a>
                    <a href="{{ route('dashboard.fulfillment.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard.fulfillment.*') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Shipping
                    </a>
                    <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('settings.profile') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    <a href="{{ auth()->user()->profile_url }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-ink-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        View public page
                    </a>
                </nav>
            </div>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-6xl w-full mx-auto">
                {{-- Page header --}}
                @hasSection('header')
                    <div class="mb-6">
                        <h1 class="text-2xl font-black text-ink-900">@yield('header')</h1>
                        @hasSection('subheader')
                            <p class="text-sm text-ink-500 mt-1">@yield('subheader')</p>
                        @endif
                    </div>
                @endif

                {{-- Flash messages --}}
                @if (session('success'))
                    <div class="rounded-lg bg-brand-100 border border-brand-500/30 text-brand-800 px-4 py-3 mb-4 animate-fade-in">✅ {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="rounded-lg bg-red-50 border border-danger/30 text-danger px-4 py-3 mb-4 animate-fade-in">⚠️ {{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>