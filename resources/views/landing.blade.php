@extends('layouts.app')

@section('title', 'Linka — Satu link. Tak terhingga kemungkinan.')
@section('description', 'Buat halaman Linka kamu, jual produk digital, course, event, donation, sampai toko fisik. Gratis untuk mulai, upgrade saat berkembang.')

@section('content')

{{-- ============================================================
     HERO — animated mesh gradient + animated text
     ============================================================ --}}
<section class="relative overflow-hidden mesh-gradient">
    {{-- Floating orbs --}}
    <div class="absolute top-1/4 -right-32 w-[500px] h-[500px] bg-brand-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-[500px] h-[500px] bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>
    <div class="absolute top-1/2 left-1/3 w-96 h-96 bg-rose-400/20 rounded-full blur-3xl animate-float"></div>

    {{-- Subtle grid overlay --}}
    <div class="absolute inset-0 opacity-40 bg-grid"></div>

    <div class="container-linka section relative">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">

            {{-- ─── Copy (7 cols) ─── --}}
            <div class="lg:col-span-7 text-center lg:text-left">
                {{-- Eyebrow badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/80 backdrop-blur border border-brand-200 shadow-sm text-xs font-bold text-brand-700 mb-6 animate-fade-up">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex w-full h-full rounded-full bg-brand-400 opacity-75 animate-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-brand-500"></span>
                    </span>
                    Powering 5,000+ kreator Indonesia
                </div>

                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-ink-900 text-balance leading-[1.05] animate-fade-up">
                    Satu link.<br>
                    <span class="text-gradient-warm">Tak terhingga</span><br>
                    kemungkinan.
                </h1>

                <p class="mt-6 text-lg md:text-xl text-ink-600 leading-relaxed max-w-xl mx-auto lg:mx-0 text-pretty animate-fade-up">
                    Produk digital, course, event, donation, sampai toko fisik. Mulai gratis, tanpa kartu kredit, langsung terima pembayaran via Duitku.
                </p>

                {{-- CTAs --}}
                <div class="mt-9 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start animate-fade-up">
                    <a href="{{ route('register') }}" class="btn-cta group text-base h-13 px-7 py-3.5 shadow-cta animate-pulse-glow">
                        Mulai Gratis Sekarang
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="/demo_bob" class="btn-outline-ink group">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                        Lihat Demo
                    </a>
                </div>

                {{-- Trust line --}}
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 justify-center lg:justify-start text-sm text-ink-600 animate-fade-up">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span class="font-medium">Gratis selamanya</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span class="font-medium">Tanpa kartu kredit</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span class="font-medium">Setup 5 menit</span>
                    </div>
                </div>
            </div>

            {{-- ─── Right: Interactive phone mockup (5 cols) ─── --}}
            <div class="lg:col-span-5 relative">
                {{-- Floating stat cards behind phone --}}
                <div class="absolute -top-4 -left-4 lg:-left-8 z-20 hidden md:block animate-float-slow">
                    <div class="bg-white rounded-2xl shadow-card-hover p-3.5 border border-ink-200 flex items-center gap-3 max-w-[220px]">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c.1-.621.504-1.125 1.125-1.125H20.25"/></svg>
                        </div>
                        <div>
                            <div class="text-[10px] text-ink-500 font-semibold uppercase tracking-wider">Penjualan</div>
                            <div class="text-sm font-black text-ink-900">+Rp 2.4jt</div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-4 -right-4 lg:-right-8 z-20 hidden md:block animate-float">
                    <div class="bg-white rounded-2xl shadow-card-hover p-3.5 border border-ink-200 max-w-[240px]">
                        <div class="flex items-center gap-2 mb-1.5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 text-white text-xs font-bold flex items-center justify-center">A</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold truncate">Alice baru checkout!</div>
                            </div>
                        </div>
                        <div class="text-[10px] text-ink-500">E-Book Photography · Rp 99K</div>
                    </div>
                </div>

                {{-- Phone mockup --}}
                <div class="relative mx-auto animate-fade-up" style="max-width: 320px;">
                    {{-- Glow behind phone --}}
                    <div class="absolute inset-0 bg-gradient-to-br from-brand-500/40 to-amber-500/40 rounded-[3rem] blur-2xl scale-110 -z-10"></div>

                    <div class="relative bg-ink-900 rounded-[2.75rem] p-2.5 shadow-2xl">
                        <div class="bg-white rounded-[2.25rem] overflow-hidden aspect-[9/19.5] relative">
                            {{-- Status bar --}}
                            <div class="bg-gradient-to-br from-brand-500 via-brand-600 to-amber-600 pt-8 pb-24 relative">
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-28 h-6 bg-ink-900 rounded-b-2xl z-10"></div>
                                <div class="absolute top-2 right-4 text-[10px] font-bold text-white">9:41</div>
                            </div>

                            {{-- Content overlay --}}
                            <div class="-mt-20 px-5 pb-6 relative z-10">
                                {{-- Avatar --}}
                                <div class="flex justify-center">
                                    <div class="w-20 h-20 rounded-full p-0.5 bg-gradient-to-br from-white via-brand-200 to-amber-200 shadow-xl">
                                        <div class="w-full h-full rounded-full bg-gradient-to-br from-brand-500 to-amber-500 flex items-center justify-center text-white text-2xl font-bold">@</div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <div class="font-bold text-base text-ink-900">@yourname</div>
                                    <div class="text-xs text-ink-500 mt-0.5">Creator & Educator</div>
                                </div>

                                {{-- Stats row --}}
                                <div class="flex justify-center gap-4 mt-3 text-[10px]">
                                    <div class="text-center">
                                        <div class="font-black text-sm text-ink-900">12</div>
                                        <div class="text-ink-500">Products</div>
                                    </div>
                                    <div class="w-px h-6 bg-ink-200"></div>
                                    <div class="text-center">
                                        <div class="font-black text-sm text-ink-900">2.4K</div>
                                        <div class="text-ink-500">Followers</div>
                                    </div>
                                    <div class="w-px h-6 bg-ink-200"></div>
                                    <div class="text-center">
                                        <div class="font-black text-sm text-ink-900">847</div>
                                        <div class="text-ink-500">Sold</div>
                                    </div>
                                </div>

                                {{-- Mini link cards --}}
                                <div class="space-y-2 mt-5">
                                    <div class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-ink-200 shadow-xs">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-ink-900 truncate">E-Book Photography</div>
                                            <div class="text-[10px] text-ink-500">Rp 99K</div>
                                        </div>
                                        <svg class="w-3 h-3 text-ink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </div>
                                    <div class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-ink-200 shadow-xs">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-400 to-indigo-500 flex items-center justify-center text-white">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-ink-900 truncate">Course: Lightroom</div>
                                            <div class="text-[10px] text-ink-500">Rp 299K</div>
                                        </div>
                                        <svg class="w-3 h-3 text-ink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </div>
                                    <div class="flex items-center gap-2.5 p-2.5 bg-gradient-to-br from-rose-50 to-pink-50 rounded-xl border border-rose-200">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-400 to-pink-500 flex items-center justify-center text-white">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-ink-900 truncate">Dukung karya saya ☕</div>
                                            <div class="text-[10px] text-ink-500">Donasi</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     SOCIAL PROOF BAR — creator logos / stats
     ============================================================ --}}
<section class="py-10 border-y border-ink-200 bg-white">
    <div class="container-linka">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8 text-center">
            @php
                $stats = [
                    ['val' => '5,000+', 'label' => 'Kreator aktif'],
                    ['val' => 'Rp 12M+', 'label' => 'Transaksi diproses'],
                    ['val' => '847K', 'label' => 'Pembeli dilayani'],
                    ['val' => '4.9/5', 'label' => 'Rating kepuasan'],
                ];
            @endphp
            @foreach($stats as $s)
                <div class="animate-fade-up">
                    <div class="text-3xl md:text-4xl font-black text-ink-900 text-balance">{{ $s['val'] }}</div>
                    <div class="text-xs md:text-sm text-ink-500 mt-1.5 font-medium uppercase tracking-wider">{{ $s['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     MODULES BENTO GRID — 7 ways to monetize
     ============================================================ --}}
<section class="section">
    <div class="container-linka">
        <div class="max-w-2xl mb-12 md:mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-4 animate-fade-up">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                7 cara monetize
            </div>
            <h2 class="text-4xl md:text-5xl font-bold text-ink-900 text-balance leading-[1.1] animate-fade-up">
                Satu halaman.<br>
                <span class="text-gradient">Tujuh</span> sumber income.
            </h2>
            <p class="mt-4 text-lg text-ink-600 text-pretty animate-fade-up">
                Pilih salah satu atau kombinasikan semuanya. Setiap modul berdiri sendiri tapi saling menguatkan.
            </p>
        </div>

        {{-- Bento grid layout --}}
        <div class="bento-grid stagger">

            {{-- Large: Digital Product (2x2) --}}
            <a href="/demo_alice" class="bento-item-large group relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-500 via-brand-500 to-amber-500 text-white p-7 md:p-9 hover:-translate-y-1 transition-all duration-300 shadow-card hover:shadow-card-hover">
                <div class="absolute -top-12 -right-12 w-64 h-64 bg-white/15 rounded-full blur-2xl group-hover:scale-110 transition-transform"></div>
                <div class="relative h-full flex flex-col">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center mb-5">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold text-balance">Digital Product</h3>
                    <p class="mt-2 text-white/85 leading-relaxed max-w-sm">Ebook, preset, template, software — file apapun, sekali bayar. Auto-deliver ke email pembeli.</p>
                    <div class="mt-auto pt-6 flex items-center gap-2 text-sm font-bold">
                        Lihat contoh
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </div>
                </div>
            </a>

            {{-- Course (wide) --}}
            <a href="/demo_alice" class="bento-item-wide group relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white p-6 hover:-translate-y-1 transition-all duration-300 shadow-card hover:shadow-card-hover">
                <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-white/15 rounded-full blur-2xl group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                    </div>
                    <h3 class="text-xl font-bold">Online Course</h3>
                    <p class="mt-1.5 text-sm text-white/85 leading-relaxed">Video lessons dengan progress tracking.</p>
                </div>
            </a>

            {{-- Event --}}
            <a href="/demo_bob" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-500 to-rose-500 text-white p-6 hover:-translate-y-1 transition-all duration-300 shadow-card hover:shadow-card-hover">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/></svg>
                </div>
                <h3 class="text-lg font-bold">Event Ticket</h3>
                <p class="mt-1 text-xs text-white/85">Webinar, workshop + QR check-in</p>
            </a>

            {{-- Donation --}}
            <a href="/demo_alice" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-500 to-red-500 text-white p-6 hover:-translate-y-1 transition-all duration-300 shadow-card hover:shadow-card-hover">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                </div>
                <h3 class="text-lg font-bold">Donation</h3>
                <p class="mt-1 text-xs text-white/85">Dukung karya fans</p>
            </a>

            {{-- Blog --}}
            <a href="/demo_bob" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-6 hover:-translate-y-1 transition-all duration-300 shadow-card hover:shadow-card-hover">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.875C4.254 3.75 3.75 4.254 3.75 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/></svg>
                </div>
                <h3 class="text-lg font-bold">Blog & Article</h3>
                <p class="mt-1 text-xs text-white/85">Tulis cerita, tutorial, insight</p>
            </a>

            {{-- Appointment --}}
            <a href="/demo_alice" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white p-6 hover:-translate-y-1 transition-all duration-300 shadow-card hover:shadow-card-hover">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </div>
                <h3 class="text-lg font-bold">Appointment</h3>
                <p class="mt-1 text-xs text-white/85">1:1 coaching & mentoring</p>
            </a>

            {{-- Physical --}}
            <a href="/demo_bob" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500 to-orange-600 text-white p-6 hover:-translate-y-1 transition-all duration-300 shadow-card hover:shadow-card-hover">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                </div>
                <h3 class="text-lg font-bold">Toko Fisik</h3>
                <p class="mt-1 text-xs text-white/85">Produk fisik + ongkir</p>
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     HOW IT WORKS — 3 steps
     ============================================================ --}}
<section class="section bg-ink-50">
    <div class="container-linka">
        <div class="max-w-2xl mb-12 md:mb-16 mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold uppercase tracking-wider mb-4 animate-fade-up">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd"/></svg>
                Setup 5 menit
            </div>
            <h2 class="text-4xl md:text-5xl font-bold text-ink-900 text-balance animate-fade-up">
                Dari nol ke <span class="text-gradient">jualan pertama</span><br>dalam 5 menit.
            </h2>
        </div>

        <div class="grid md:grid-cols-3 gap-6 stagger">
            @php
                $steps = [
                    ['num' => '01', 'title' => 'Buat halaman', 'desc' => 'Daftar gratis, pilih username, customize bio kamu. Tanpa kartu kredit.'],
                    ['num' => '02', 'title' => 'Upload produk', 'desc' => 'Pilih tipe produk (digital, course, event, dll). Upload file atau set detail. Publish.'],
                    ['num' => '03', 'title' => 'Terima pembayaran', 'desc' => 'Bagikan link di bio. Pembeli checkout via Duitku (QRIS, VA, e-wallet). Uang masuk ke rekening kamu.'],
                ];
            @endphp
            @foreach($steps as $s)
                <div class="card-warm-hover p-7 group relative overflow-hidden">
                    <div class="absolute -top-8 -right-8 text-7xl font-black text-brand-100/50 group-hover:text-brand-200/70 transition-colors">{{ $s['num'] }}</div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-500 to-amber-500 text-white flex items-center justify-center mb-5 shadow-cta">
                            <span class="font-black">{{ $s['num'] }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-ink-900 text-balance">{{ $s['title'] }}</h3>
                        <p class="mt-2 text-sm text-ink-600 leading-relaxed">{{ $s['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     TESTIMONIAL
     ============================================================ --}}
<section class="section">
    <div class="container-linka">
        <div class="max-w-4xl mx-auto text-center animate-fade-up">
            <div class="inline-flex gap-1 text-amber-400 mb-6">
                @for($i=0;$i<5;$i++)
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z"/></svg>
                @endfor
            </div>

            <blockquote class="text-2xl md:text-3xl font-bold text-ink-900 text-balance leading-tight">
                "Cuma 30 menit setup, <span class="text-gradient-warm">closing Rp 2 juta</span> di hari pertama. Conversion rate naik 3x sejak pindah ke Linka."
            </blockquote>

            <div class="mt-8 flex items-center justify-center gap-4">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-xl ring-4 ring-white shadow-md">R</div>
                <div class="text-left">
                    <div class="font-bold text-ink-900">Rina Maharani</div>
                    <div class="text-sm text-ink-500">Content Creator · 250K followers</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     FINAL CTA
     ============================================================ --}}
<section class="section">
    <div class="container-narrow">
        <div class="relative overflow-hidden rounded-3xl mesh-gradient-dark p-10 md:p-14 text-center text-white animate-fade-up">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-300/20 rounded-full blur-3xl"></div>

            <div class="relative">
                <h2 class="text-4xl md:text-5xl font-bold text-balance leading-tight">
                    Siap monetize audiens kamu?
                </h2>
                <p class="mt-4 text-lg text-white/85 max-w-xl mx-auto text-pretty">
                    Setup dalam 5 menit. Gratis tanpa kartu kredit. Bayar fee hanya kalau jualan.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 h-14 px-8 rounded-xl bg-white text-brand-700 font-bold text-base shadow-2xl hover:bg-amber-50 hover:-translate-y-0.5 transition-all duration-200 group">
                        Buat Halaman Gratis
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center gap-2 h-14 px-8 rounded-xl bg-white/15 backdrop-blur border border-white/30 text-white font-bold text-base hover:bg-white/25 hover:-translate-y-0.5 transition-all duration-200">
                        Lihat Pricing
                    </a>
                </div>

                <p class="mt-6 text-sm text-white/70">
                    Sudah dipakai 5,000+ kreator Indonesia
                </p>
            </div>
        </div>
    </div>
</section>
@endsection