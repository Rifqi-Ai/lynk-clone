@extends('layouts.app')

@section('title', 'Masuk — Linka')
@section('description', 'Login ke akun Linka kamu dengan email atau username.')

@section('content')
<div class="min-h-[calc(100vh-4rem)] grid lg:grid-cols-5">

    {{-- ─── Left: Form (3/5 width) ─── --}}
    <div class="lg:col-span-3 flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24 bg-ink-50 order-2 lg:order-1">
        <div class="mx-auto w-full max-w-lg">

            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 hover:text-brand-600 transition-colors mb-8 group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali ke beranda
            </a>

            {{-- Heading --}}
            <div class="animate-fade-up">
                <h1 class="text-4xl md:text-5xl font-bold text-ink-900 tracking-tight text-balance">
                    Selamat datang <span class="text-gradient">kembali</span>.
                </h1>
                <p class="mt-3 text-lg text-ink-600 text-pretty">
                    Masuk untuk manage halaman, produk, dan penjualan kamu.
                </p>
            </div>

            {{-- Demo accounts helper --}}
            <div class="mt-6 p-5 bg-gradient-to-br from-brand-50 to-amber-50 border border-brand-200/60 rounded-2xl animate-fade-up">
                <div class="flex items-center gap-2 text-sm font-semibold text-brand-800">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd"/></svg>
                    </div>
                    <span>Coba dengan akun demo</span>
                </div>
                <p class="mt-1.5 text-xs text-ink-600 ml-9">Klik untuk login otomatis</p>
                <div class="mt-3 flex flex-wrap gap-2 ml-9">
                    @php
                        $demos = [
                            ['login' => 'alice@demo.linka.id', 'name' => 'Alice', 'avatar' => 'A', 'color' => 'from-pink-500 to-rose-500'],
                            ['login' => 'demo_alice', 'name' => 'Alice (username)', 'avatar' => 'A', 'color' => 'from-pink-500 to-rose-500'],
                            ['login' => 'bob@demo.linka.id', 'name' => 'Bob', 'avatar' => 'B', 'color' => 'from-blue-500 to-indigo-500'],
                            ['login' => 'eko@demo.linka.id', 'name' => 'Eko', 'avatar' => 'E', 'color' => 'from-emerald-500 to-teal-500'],
                        ];
                    @endphp
                    @foreach($demos as $d)
                        <button type="button"
                                onclick="document.getElementById('login').value='{{ $d['login'] }}'; document.getElementById('password').value='password123'; showToast('Akun demo {{ $d['name'] }} dipilih', 'success');"
                                class="group inline-flex items-center gap-2 pl-1.5 pr-3 py-1.5 bg-white border border-ink-200 hover:border-brand-400 hover:bg-brand-50 hover:shadow-sm text-ink-800 rounded-full transition-all">
                            <span class="w-6 h-6 rounded-full bg-gradient-to-br {{ $d['color'] }} text-white text-xs font-bold flex items-center justify-center">{{ $d['avatar'] }}</span>
                            <span class="text-xs font-semibold">{{ $d['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Form card --}}
            <form method="POST" action="{{ route('login') }}" class="mt-6 p-6 md:p-8 bg-white rounded-3xl border border-ink-200 shadow-card space-y-5 animate-fade-up">
                @csrf

                <div>
                    <label for="login" class="form-label">Email atau Username</label>
                    <input id="login" name="login" type="text" required autocomplete="username" autofocus
                           value="{{ old('login') }}"
                           class="form-input @error('login') border-error @enderror"
                           placeholder="kamu@email.com atau username">
                    @error('login')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="form-label mb-0">Password</label>
                        <a href="#" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lupa?</a>
                    </div>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="form-input pr-12 @error('password') border-error @enderror"
                               placeholder="••••••••">
                        <button type="button" onclick="const p=document.getElementById('password'); p.type=p.type==='password'?'text':'password'; this.querySelector('svg.eye-open').classList.toggle('hidden'); this.querySelector('svg.eye-closed').classList.toggle('hidden');"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-ink-400 hover:text-ink-700 transition-colors">
                            <svg class="eye-open w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg class="eye-closed hidden w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2.5 pt-1">
                    <input id="remember" name="remember" type="checkbox" class="w-4 h-4 rounded border-ink-300 text-brand-500 focus:ring-brand-200 cursor-pointer">
                    <label for="remember" class="text-sm text-ink-700 cursor-pointer">Ingat saya</label>
                </div>

                <button type="submit" class="btn-cta w-full h-12 text-base group">
                    Masuk
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </button>
            </form>

            {{-- Divider --}}
            <div class="mt-6 flex items-center gap-4 animate-fade-up">
                <div class="flex-1 h-px bg-ink-200"></div>
                <span class="text-xs font-semibold text-ink-400 uppercase tracking-wider">atau</span>
                <div class="flex-1 h-px bg-ink-200"></div>
            </div>

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google') }}" class="mt-5 inline-flex items-center justify-center gap-3 w-full h-12 rounded-xl border border-ink-200 bg-white hover:bg-ink-50 hover:border-ink-300 text-ink-900 font-semibold transition-all hover:-translate-y-0.5 hover:shadow-sm">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Lanjut dengan Google
            </a>

            <p class="mt-8 text-center text-sm text-ink-500 animate-fade-up">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700 underline-offset-2 hover:underline">Daftar gratis</a>
            </p>
        </div>
    </div>

    {{-- ─── Right: Hero visual (2/5 width) ─── --}}
    <div class="hidden lg:flex lg:col-span-2 relative mesh-gradient-dark items-center justify-center p-10 xl:p-16 overflow-hidden order-1 lg:order-2">

        {{-- Floating orbs --}}
        <div class="absolute top-1/4 -right-32 w-96 h-96 bg-white/30 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-300/40 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute top-1/2 right-1/3 w-72 h-72 bg-brand-400/30 rounded-full blur-3xl animate-float"></div>

        <div class="relative max-w-md text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/25 backdrop-blur-md border border-white/30 text-xs font-bold mb-6 animate-fade-up">
                <svg class="w-3.5 h-3.5 text-amber-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z"/></svg>
                5,000+ kreator Indonesia
            </div>

            <h2 class="text-4xl xl:text-5xl font-bold tracking-tight text-balance animate-fade-up leading-[1.1]">
                Mulai monetize audiens kamu hari ini.
            </h2>

            <p class="mt-5 text-lg text-white/85 text-pretty animate-fade-up leading-relaxed">
                Setup 5 menit. Bayar fee hanya kalau jualan. Tanpa komitmen.
            </p>

            {{-- Stats row --}}
            <div class="mt-10 grid grid-cols-3 gap-4 stagger">
                <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl p-4">
                    <div class="text-2xl xl:text-3xl font-bold text-white">5K+</div>
                    <div class="text-xs text-white/70 mt-1 font-medium">Creators</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl p-4">
                    <div class="text-2xl xl:text-3xl font-bold text-white">50M+</div>
                    <div class="text-xs text-white/70 mt-1 font-medium">Followers</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md border border-white/20 rounded-2xl p-4">
                    <div class="text-2xl xl:text-3xl font-bold text-white">Rp 12B</div>
                    <div class="text-xs text-white/70 mt-1 font-medium">Transaksi</div>
                </div>
            </div>

            {{-- Floating testimonial card --}}
            <div class="mt-8 p-5 bg-white/15 backdrop-blur-xl border border-white/25 rounded-2xl animate-fade-up shadow-2xl">
                <div class="flex gap-0.5 text-amber-300 mb-3">
                    @for($i=0;$i<5;$i++)
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z"/></svg>
                    @endfor
                </div>
                <p class="text-white/95 leading-relaxed text-balance">"Cuma 30 menit setup, closing Rp 2 juta di hari pertama. Conversion rate naik 3x sejak pindah ke Linka."</p>
                <div class="mt-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white font-bold ring-2 ring-white/30">R</div>
                    <div>
                        <div class="text-sm font-semibold">Rina Maharani</div>
                        <div class="text-xs text-white/70">Content Creator · 250K followers</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-container" class="toast-container"></div>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success'
        ? '<svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>'
        : type === 'error'
        ? '<svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>'
        : '<svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>';
    toast.innerHTML = `${icon}<span class="font-medium">${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(20px)'; setTimeout(() => toast.remove(), 300); }, 3000);
}
window.showToast = showToast;
</script>
@endsection