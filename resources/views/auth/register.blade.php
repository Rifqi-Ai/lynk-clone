@extends('layouts.app')

@section('title', 'Daftar Gratis — Linka')
@section('description', 'Buat halaman Linka kamu dalam 5 menit. Gratis, tanpa kartu kredit.')

@section('content')
<div class="min-h-[calc(100vh-4rem)] grid lg:grid-cols-2">

    {{-- ─── Left: Hero visual ─── --}}
    <div class="hidden lg:flex relative bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 items-center justify-center p-12 overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-accent/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-md text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur text-xs font-semibold mb-6">
                <x-heroicon-s-sparkles class="w-3.5 h-3.5" /> Gratis selamanya
            </div>
            <h2 class="text-4xl xl:text-5xl font-bold tracking-tight text-balance">7 cara monetize audiens kamu. 1 link.</h2>
            <p class="mt-4 text-lg text-brand-100 text-pretty">Produk digital, course, event, donation, sampai toko fisik. Semua dalam satu halaman.</p>

            {{-- Module showcase --}}
            <div class="mt-10 grid grid-cols-2 gap-3">
                @php
                    $modules = [
                        ['icon' => 'arrow-down-tray', 'label' => 'Digital', 'color' => 'bg-white/20'],
                        ['icon' => 'academic-cap', 'label' => 'Course', 'color' => 'bg-white/20'],
                        ['icon' => 'calendar-days', 'label' => 'Event', 'color' => 'bg-white/20'],
                        ['icon' => 'heart', 'label' => 'Donasi', 'color' => 'bg-white/20'],
                    ];
                @endphp
                @foreach($modules as $m)
                    <div class="flex items-center gap-3 p-3 {{ $m['color'] }} backdrop-blur border border-white/20 rounded-xl">
                        <x-dynamic-component :component="'heroicon-s-' . $m['icon']" class="w-5 h-5" />
                        <span class="text-sm font-semibold">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex items-center gap-3 text-sm text-brand-100">
                <x-heroicon-s-shield-check class="w-5 h-5" />
                <span>Tanpa kartu kredit. Batal kapan saja.</span>
            </div>
        </div>
    </div>

    {{-- ─── Right: Form ─── --}}
    <div class="flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24">
        <div class="mx-auto w-full max-w-md">

            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-brand-600 transition-colors mb-8 lg:hidden">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali
            </a>

            <h1 class="text-3xl md:text-4xl font-bold text-ink-900 tracking-tight">Buat halaman kamu 🎉</h1>
            <p class="mt-2 text-ink-500">5 menit lagi, audiens kamu bisa belanja langsung dari bio.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="name" class="form-label">Nama</label>
                    <input id="name" name="name" type="text" required autocomplete="name" autofocus
                           value="{{ old('name') }}"
                           class="form-input @error('name') border-error @enderror"
                           placeholder="Nama lengkap kamu">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="form-label">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-ink-500 font-semibold">linka.id/</span>
                        <input id="username" name="username" type="text" required pattern="[a-z0-9_-]+" autocomplete="username"
                               value="{{ old('username') }}"
                               class="form-input pl-20 @error('username') border-error @enderror"
                               placeholder="namakamu">
                    </div>
                    @error('username')<p class="form-error">{{ $message }}</p>@enderror
                    <p class="form-help">Hanya huruf kecil, angka, dash, dan underscore.</p>
                </div>

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email"
                           value="{{ old('email') }}"
                           class="form-input @error('email') border-error @enderror"
                           placeholder="kamu@email.com">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                           class="form-input @error('password') border-error @enderror"
                           placeholder="Minimal 8 karakter">
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-start gap-2 pt-2">
                    <input id="terms" name="terms" type="checkbox" required
                           class="mt-1 w-4 h-4 rounded border-ink-300 text-brand-500 focus:ring-brand-200">
                    <label for="terms" class="text-sm text-ink-600">
                        Saya setuju dengan
                        <a href="{{ route('terms') }}" class="font-semibold text-brand-600 hover:text-brand-700">Syarat & Ketentuan</a>
                        dan
                        <a href="{{ route('privacy') }}" class="font-semibold text-brand-600 hover:text-brand-700">Kebijakan Privasi</a>
                    </label>
                </div>

                <button type="submit" class="btn-cta btn-block">
                    Buat Akun Gratis
                    <x-heroicon-s-arrow-right class="w-4 h-4" />
                </button>
            </form>

            <div class="mt-8 flex items-center gap-4">
                <div class="flex-1 h-px bg-ink-200"></div>
                <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">atau</span>
                <div class="flex-1 h-px bg-ink-200"></div>
            </div>

            <a href="{{ route('auth.google') }}" class="mt-6 inline-flex items-center justify-center gap-3 w-full h-12 rounded-xl border border-ink-200 bg-white hover:bg-ink-50 text-ink-900 font-semibold transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Daftar dengan Google
            </a>

            <p class="mt-8 text-center text-sm text-ink-500">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Masuk</a>
            </p>
        </div>
    </div>
</div>
@endsection
