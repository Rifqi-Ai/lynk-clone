@extends('layouts.app')

@section('title', 'Daftar Gratis — Linka')
@section('description', 'Buat halaman Linka kamu dalam 5 menit. Gratis, tanpa kartu kredit.')

@section('content')
<div class="min-h-[calc(100vh-4rem)] grid lg:grid-cols-5">

    {{-- ─── Left: Hero visual (2/5 width on desktop) ─── --}}
    <div class="hidden lg:flex lg:col-span-2 relative mesh-gradient-dark items-center justify-center p-10 xl:p-16 overflow-hidden">

        {{-- Floating orbs --}}
        <div class="absolute top-1/4 -left-32 w-96 h-96 bg-white/30 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-amber-300/40 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute top-1/2 left-1/3 w-72 h-72 bg-brand-400/30 rounded-full blur-3xl animate-float"></div>

        {{-- Subtle grid overlay --}}
        <div class="absolute inset-0 opacity-20 bg-grid"></div>

        <div class="relative max-w-md text-white">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/25 backdrop-blur-md border border-white/30 text-xs font-bold mb-6 animate-fade-up">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                Gratis selamanya
            </div>

            <h2 class="text-4xl xl:text-5xl font-bold tracking-tight text-balance animate-fade-up leading-[1.1]">
                Satu link.<br>
                <span class="text-white drop-shadow-[0_2px_8px_rgba(0,0,0,0.3)]">Tak terhingga</span> kemungkinan.
            </h2>

            <p class="mt-5 text-lg text-white/85 text-pretty animate-fade-up leading-relaxed">
                Produk digital, course, event, donation, sampai toko fisik. Semua monetisasi audiens dalam satu halaman yang cantik.
            </p>

            {{-- Module showcase bento --}}
            <div class="mt-10 grid grid-cols-2 gap-3 stagger">
                @php
                    $modules = [
                        ['icon' => 'arrow-down-tray', 'label' => 'Digital', 'gradient' => 'from-orange-400/30 to-amber-400/20'],
                        ['icon' => 'academic-cap', 'label' => 'Course', 'gradient' => 'from-blue-400/30 to-indigo-400/20'],
                        ['icon' => 'calendar-days', 'label' => 'Event', 'gradient' => 'from-pink-400/30 to-rose-400/20'],
                        ['icon' => 'heart', 'label' => 'Donation', 'gradient' => 'from-red-400/30 to-pink-400/20'],
                        ['icon' => 'shopping-bag', 'label' => 'Toko', 'gradient' => 'from-emerald-400/30 to-teal-400/20'],
                        ['icon' => 'calendar', 'label' => 'Booking', 'gradient' => 'from-violet-400/30 to-purple-400/20'],
                    ];
                @endphp
                @foreach($modules as $m)
                    <div class="flex items-center gap-3 p-3.5 bg-white/15 backdrop-blur-md border border-white/20 rounded-xl hover:bg-white/25 hover:-translate-y-0.5 transition-all duration-200">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br {{ $m['gradient'] }} flex items-center justify-center">
                            <x-dynamic-component :component="'heroicon-s-' . $m['icon']" class="w-5 h-5 text-white" />
                        </div>
                        <span class="text-sm font-semibold">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Trust line --}}
            <div class="mt-8 flex items-center gap-2 text-sm text-white/90 animate-fade-up">
                <svg class="w-5 h-5 text-emerald-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>Tanpa kartu kredit. Batal kapan saja.</span>
            </div>
        </div>
    </div>

    {{-- ─── Right: Form (3/5 width on desktop) ─── --}}
    <div class="lg:col-span-3 flex flex-col justify-center px-6 py-12 lg:px-16 xl:px-24 bg-ink-50">
        <div class="mx-auto w-full max-w-lg">

            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 hover:text-brand-600 transition-colors mb-8 group">
                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali ke beranda
            </a>

            {{-- Heading --}}
            <div class="animate-fade-up">
                <h1 class="text-4xl md:text-5xl font-bold text-ink-900 tracking-tight text-balance">
                    Buat halaman <span class="text-gradient">pertama</span> kamu.
                </h1>
                <p class="mt-3 text-lg text-ink-600 text-pretty">
                    5 menit lagi, audiens kamu bisa belanja langsung dari bio.
                </p>
            </div>

            {{-- Form card --}}
            <form method="POST" action="{{ route('register') }}" class="mt-8 p-6 md:p-8 bg-white rounded-3xl border border-ink-200 shadow-card space-y-5 animate-fade-up">
                @csrf

                {{-- Honeypot: hidden field humans never see/fill, bots fill eagerly. Rejected in AuthController. --}}
                <input type="text" name="website" value="" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" tabindex="-1" autocomplete="off" aria-hidden="true">

                {{-- Name --}}
                <div>
                    <label for="name" class="form-label">Nama</label>
                    <input id="name" name="name" type="text" required autocomplete="name" autofocus
                           value="{{ old('name') }}"
                           @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                           class="form-input @error('name') border-error ring-2 ring-error/20 @enderror"
                           placeholder="Nama lengkap kamu">
                    @error('name')<p id="name-error" class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                {{-- Username — fixed layout with proper prefix spacing --}}
                <div>
                    <label for="username" class="form-label">Username</label>
                    <div class="relative flex items-stretch rounded-xl border border-ink-200 bg-white focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-100 transition-all overflow-hidden @error('username') border-error ring-2 ring-error/20 @enderror">
                        <span class="flex items-center pl-4 pr-3 text-sm font-semibold text-ink-500 bg-ink-50 border-r border-ink-200 select-none whitespace-nowrap">
                            linka.id/
                        </span>
                        <input id="username" name="username" type="text" required pattern="[a-z0-9_\-]+" autocomplete="username" maxlength="30"
                               value="{{ old('username') }}"
                               @error('username') aria-invalid="true" aria-describedby="username-error" @enderror
                               class="flex-1 min-w-0 px-3 py-3 bg-transparent border-0 outline-none text-ink-900 placeholder:text-ink-400 text-[15px] focus:ring-0"
                               placeholder="namakamu">
                    </div>
                    @error('username')<p id="username-error" class="form-error" role="alert">{{ $message }}</p>@enderror
                    <p class="form-help flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-ink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        Hanya huruf kecil, angka, dash, dan underscore. URL publik kamu di <strong>linka.id/username</strong>.
                    </p>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email"
                           value="{{ old('email') }}"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                           class="form-input @error('email') border-error ring-2 ring-error/20 @enderror"
                           placeholder="kamu@email.com">
                    @error('email')<p id="email-error" class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                               @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                               class="form-input pr-12 @error('password') border-error ring-2 ring-error/20 @enderror"
                               placeholder="Minimal 8 karakter">
                        <button type="button" data-toggle-password="password"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-ink-400 hover:text-ink-700 transition-colors">
                            <svg class="eye-open w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg class="eye-closed hidden w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                               @error('password_confirmation') aria-invalid="true" aria-describedby="password-confirmation-error" @enderror
                               class="form-input pr-12 @error('password_confirmation') border-error ring-2 ring-error/20 @enderror"
                               placeholder="Ketik ulang password">
                        <button type="button" data-toggle-password="password_confirmation"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-ink-400 hover:text-ink-700 transition-colors">
                            <svg class="eye-open w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg class="eye-closed hidden w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    @error('password_confirmation')<p id="password-confirmation-error" class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                {{-- Terms — Issue #8: increased gap from 2.5 to 3 for better spacing --}}
                <div class="flex items-start gap-3 pt-1">
                    <input id="terms" name="terms" type="checkbox" required
                           class="mt-0.5 w-4 h-4 rounded border-ink-300 text-brand-500 focus:ring-brand-200 cursor-pointer">
                    <label for="terms" class="text-sm text-ink-600 cursor-pointer leading-relaxed">
                        Saya setuju dengan
                        <a href="{{ route('terms') }}" class="font-semibold text-brand-600 hover:text-brand-700 underline-offset-2 hover:underline">Syarat & Ketentuan</a>
                        dan
                        <a href="{{ route('privacy') }}" class="font-semibold text-brand-600 hover:text-brand-700 underline-offset-2 hover:underline">Kebijakan Privasi</a>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-cta w-full h-12 text-base group">
                    Buat Akun Gratis
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
                Daftar dengan Google
            </a>

            {{-- Sign in link --}}
            <p class="mt-8 text-center text-sm text-ink-500 animate-fade-up">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700 underline-offset-2 hover:underline">Masuk di sini</a>
            </p>
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
        : '<svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>';
    toast.innerHTML = `${icon}<span class="font-medium">${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(20px)'; setTimeout(() => toast.remove(), 300); }, 3000);
}
window.showToast = showToast;

// Password toggle handler
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-toggle-password]');
    if (!btn) return;
    const targetId = btn.getAttribute('data-toggle-password');
    const input = document.getElementById(targetId);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.querySelector('svg.eye-open')?.classList.toggle('hidden');
    btn.querySelector('svg.eye-closed')?.classList.toggle('hidden');
});
</script>
@endsection