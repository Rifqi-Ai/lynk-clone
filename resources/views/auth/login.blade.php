@extends('layouts.app')

@section('title', 'Masuk — Linka')
@section('description', 'Login ke akun Linka kamu.')

@section('content')
<div class="min-h-[calc(100vh-4rem)] grid lg:grid-cols-2">

    {{-- ─── Left: Form ─── --}}
    <div class="flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24">
        <div class="mx-auto w-full max-w-md">

            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-brand-600 transition-colors mb-8">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Kembali ke beranda
            </a>

            <h1 class="text-3xl md:text-4xl font-bold text-ink-900 tracking-tight">Selamat datang kembali</h1>
            <p class="mt-2 text-ink-500">Masuk untuk manage halaman dan produk kamu.</p>

            {{-- Demo accounts helper --}}
            <div class="mt-6 p-4 bg-brand-50 border border-brand-200 rounded-2xl">
                <div class="flex items-center gap-2 text-sm font-semibold text-brand-800">
                    <x-heroicon-s-bolt class="w-4 h-4" />
                    <span>Akun demo (klik untuk isi otomatis)</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @php
                        $demos = [
                            ['email' => 'alice@demo.linka.id', 'name' => 'Alice'],
                            ['email' => 'bob@demo.linka.id', 'name' => 'Bob'],
                            ['email' => 'eko@demo.linka.id', 'name' => 'Eko'],
                        ];
                    @endphp
                    @foreach($demos as $d)
                        <button type="button" onclick="document.getElementById('email').value='{{ $d['email'] }}'; document.getElementById('password').value='password123';" class="text-xs font-semibold px-3 py-1.5 bg-white border border-brand-200 hover:border-brand-400 hover:bg-brand-100 text-brand-800 rounded-lg transition-all">
                            {{ $d['name'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email" autofocus
                           value="{{ old('email') }}"
                           class="form-input @error('email') form-input-error border-error @enderror"
                           placeholder="kamu@email.com">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="form-input @error('password') form-input-error border-error @enderror pr-12"
                               placeholder="••••••••">
                        <button type="button" onclick="const p=document.getElementById('password'); p.type=p.type==='password'?'text':'password'; this.querySelector('svg').classList.toggle('hidden'); this.querySelector('svg:last-child').classList.toggle('hidden');"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-ink-400 hover:text-ink-700">
                            <x-heroicon-o-eye class="w-5 h-5" />
                            <x-heroicon-o-eye-slash class="w-5 h-5 hidden" />
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox" class="w-4 h-4 rounded border-ink-300 text-brand-500 focus:ring-brand-200">
                    <label for="remember" class="text-sm text-ink-700">Ingat saya</label>
                </div>

                <button type="submit" class="btn-cta btn-block">
                    Masuk
                    <x-heroicon-s-arrow-right class="w-4 h-4" />
                </button>
            </form>

            {{-- Divider --}}
            <div class="mt-8 flex items-center gap-4">
                <div class="flex-1 h-px bg-ink-200"></div>
                <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">atau</span>
                <div class="flex-1 h-px bg-ink-200"></div>
            </div>

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google') }}" class="mt-6 inline-flex items-center justify-center gap-3 w-full h-12 rounded-xl border border-ink-200 bg-white hover:bg-ink-50 text-ink-900 font-semibold transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Lanjut dengan Google
            </a>

            <p class="mt-8 text-center text-sm text-ink-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Daftar gratis</a>
            </p>
        </div>
    </div>

    {{-- ─── Right: Hero visual ─── --}}
    <div class="hidden lg:flex relative bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 items-center justify-center p-12 overflow-hidden">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
        <div class="absolute top-1/4 -right-20 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -left-20 w-96 h-96 bg-accent/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-md text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur text-xs font-semibold mb-6">
                <x-heroicon-s-star class="w-3.5 h-3.5" /> 5,000+ creators on Linka
            </div>
            <h2 class="text-4xl xl:text-5xl font-bold tracking-tight text-balance">Mulai monetize audiens kamu hari ini.</h2>
            <p class="mt-4 text-lg text-brand-100 text-pretty">Setup 5 menit. Bayar fee hanya kalau jualan. Tanpa komitmen.</p>

            {{-- Mini stat row --}}
            <div class="mt-10 grid grid-cols-3 gap-4">
                <div>
                    <div class="text-3xl font-bold">5K+</div>
                    <div class="text-xs text-brand-200 mt-1">Creators</div>
                </div>
                <div>
                    <div class="text-3xl font-bold">50M+</div>
                    <div class="text-xs text-brand-200 mt-1">Followers</div>
                </div>
                <div>
                    <div class="text-3xl font-bold">Rp 12B</div>
                    <div class="text-xs text-brand-200 mt-1">Transaksi</div>
                </div>
            </div>

            {{-- Floating testimonial --}}
            <div class="mt-10 p-5 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl">
                <div class="flex gap-1 text-amber-300 mb-3">
                    @for($i=0;$i<5;$i++)<x-heroicon-s-star class="w-4 h-4" />@endfor
                </div>
                <p class="text-white/95 leading-relaxed">"Cuma 30 menit setup, closing Rp 2 juta di hari pertama. Conversion rate naik 3x sejak pindah ke Linka."</p>
                <div class="mt-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white font-bold text-sm">R</div>
                    <div>
                        <div class="text-sm font-semibold">Rina Maharani</div>
                        <div class="text-xs text-brand-200">Content Creator</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
