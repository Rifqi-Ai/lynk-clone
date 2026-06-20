<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><defs><linearGradient id='g' x1='0' x2='1' y1='0' y2='1'><stop offset='0' stop-color='%23FF6B35'/><stop offset='1' stop-color='%23B8380F'/></linearGradient></defs><rect width='32' height='32' rx='8' fill='url(%23g)'/><text x='16' y='22' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='900' font-size='18'>L</text></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-ink-50 text-ink-900 antialiased">
    <div class="flex min-h-screen">

        {{-- ─── Sidebar (desktop) ─── --}}
        <aside class="hidden lg:flex flex-col w-64 bg-white border-r border-ink-100 sticky top-0 h-screen">
            {{-- Logo --}}
            <div class="px-5 py-5 border-b border-ink-100">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-sm">
                        <span class="text-white font-black text-base">L</span>
                    </div>
                    <span class="font-bold text-lg text-ink-900 tracking-tight">{{ config('app.name') }}</span>
                </a>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 text-sm">
                @php
                    $navItem = function($route, $label, $icon, $pattern = null) use (&$navItem) {
                        $pattern = $pattern ?? $route;
                        $isActive = request()->routeIs($pattern);
                        $cls = $isActive
                            ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl bg-brand-50 text-brand-700 font-bold'
                            : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-ink-700 hover:bg-ink-50 transition-colors';
                        return "<a href=\"" . route($route) . "\" class=\"$cls\"><span class=\"w-5 h-5 flex items-center justify-center\">$icon</span>$label</a>";
                    };
                @endphp

                <p class="px-3 pt-2 pb-1 text-[10px] font-bold text-ink-400 uppercase tracking-wider">Main</p>
                {!! $navItem('dashboard.index', 'Overview', '<x-heroicon-o-squares-2x2 class="w-5 h-5" />') !!}
                {!! $navItem('dashboard.products.index', 'Products', '<x-heroicon-o-cube class="w-5 h-5" />', 'dashboard.products.*') !!}
                {!! $navItem('dashboard.fulfillment.index', 'Shipping', '<x-heroicon-o-truck class="w-5 h-5" />', 'dashboard.fulfillment.*') !!}

                <p class="px-3 pt-5 pb-1 text-[10px] font-bold text-ink-400 uppercase tracking-wider">Account</p>
                {!! $navItem('settings.profile', 'Profile', '<x-heroicon-o-user-circle class="w-5 h-5" />') !!}
                <a href="{{ auth()->user()->profile_url }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-ink-700 hover:bg-ink-50 transition-colors">
                    <x-heroicon-o-arrow-top-right-on-square class="w-5 h-5" />
                    <span>View public page</span>
                </a>
            </nav>

            {{-- User --}}
            <div class="p-4 border-t border-ink-100">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-ink-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-ink-500 truncate">{{ '@' . auth()->user()->username }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 h-9 px-3 rounded-lg text-sm font-semibold text-ink-700 hover:text-error hover:bg-error/5 transition-colors">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        {{-- ─── Main content ─── --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar (mobile) --}}
            <header class="lg:hidden bg-white border-b border-ink-100 px-4 py-3 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2 -ml-2 text-ink-700 hover:bg-ink-50 rounded-lg" aria-label="Open menu">
                        <x-heroicon-o-bars-3 class="w-6 h-6" />
                    </button>
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                            <span class="text-white font-black text-sm">L</span>
                        </div>
                        <span class="font-bold text-ink-900">{{ config('app.name') }}</span>
                    </a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-ink-500 hover:text-error transition-colors" title="Sign out">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                    </button>
                </form>
            </header>

            {{-- Mobile menu drawer --}}
            <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-ink-100 sticky top-14 z-20 shadow-sm">
                <nav class="p-3 space-y-0.5 text-sm">
                    <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard.index') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5" /> Overview
                    </a>
                    <a href="{{ route('dashboard.products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard.products.*') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <x-heroicon-o-cube class="w-5 h-5" /> Products
                    </a>
                    <a href="{{ route('dashboard.fulfillment.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard.fulfillment.*') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <x-heroicon-o-truck class="w-5 h-5" /> Shipping
                    </a>
                    <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('settings.profile') ? 'bg-brand-50 text-brand-700 font-bold' : 'text-ink-700' }}">
                        <x-heroicon-o-user-circle class="w-5 h-5" /> Profile
                    </a>
                    <a href="{{ auth()->user()->profile_url }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-ink-700">
                        <x-heroicon-o-arrow-top-right-on-square class="w-5 h-5" /> View public page
                    </a>
                </nav>
            </div>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-6xl w-full mx-auto">
                {{-- Page header --}}
                @hasSection('header')
                    <div class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-ink-900 tracking-tight">@yield('header')</h1>
                            @hasSection('subheader')
                                <p class="text-sm md:text-base text-ink-500 mt-1">@yield('subheader')</p>
                            @endif
                        </div>
                        @hasSection('actions')
                            <div class="flex items-center gap-2">@yield('actions')</div>
                        @endif
                    </div>
                @endif

                {{-- Flash messages --}}
                @if (session('success'))
                    <div class="flex items-center gap-3 rounded-xl bg-success/10 border border-success/30 text-success px-4 py-3 mb-5 animate-fade-in">
                        <x-heroicon-s-check-circle class="w-5 h-5 flex-shrink-0" />
                        <p class="text-sm font-semibold">{{ session('success') }}</p>
                    </div>
                @endif
                @if (session('error'))
                    <div class="flex items-center gap-3 rounded-xl bg-error/10 border border-error/30 text-error px-4 py-3 mb-5 animate-fade-in">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 flex-shrink-0" />
                        <p class="text-sm font-semibold">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
