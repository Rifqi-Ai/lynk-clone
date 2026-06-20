@extends('layouts.app')

@section('title', 'Halaman tidak ditemukan — Linka')
@section('description', 'Halaman yang kamu cari tidak ada.')

@section('content')
<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-brand-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-narrow section relative text-center">
        <div class="animate-fade-up">
            {{-- Big 404 number --}}
            <div class="relative inline-block mb-6">
                <div class="text-[8rem] sm:text-[12rem] leading-none font-black tracking-tighter">
                    <span class="text-gradient-warm">404</span>
                </div>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <svg class="w-24 h-24 text-ink-900/10 animate-float" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-ink-900 text-balance animate-fade-up">
                Halaman tidak ditemukan
            </h1>
            <p class="mt-3 text-lg text-ink-600 max-w-md mx-auto text-pretty animate-fade-up">
                Maaf, halaman yang kamu cari tidak ada atau sudah dipindahkan. Yuk balik ke beranda!
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center animate-fade-up">
                <a href="{{ route('home') }}" class="btn-cta group">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    Beranda
                </a>
                <a href="{{ route('pricing') }}" class="btn-outline-ink">
                    Lihat Pricing
                </a>
            </div>

            {{-- Quick links --}}
            <div class="mt-12 pt-8 border-t border-ink-200/60 animate-fade-up">
                <p class="text-xs text-ink-500 uppercase tracking-wider font-bold mb-4">Atau coba:</p>
                <div class="flex flex-wrap gap-2 justify-center">
                    <a href="/demo_alice" class="badge badge-soft hover:bg-brand-100 transition-colors">@demo_alice</a>
                    <a href="/demo_bob" class="badge badge-soft hover:bg-brand-100 transition-colors">@demo_bob</a>
                    <a href="/demo_eko" class="badge badge-soft hover:bg-brand-100 transition-colors">@demo_eko</a>
                    <a href="{{ route('about') }}" class="badge badge-soft hover:bg-brand-100 transition-colors">Tentang Linka</a>
                    <a href="{{ route('faq') }}" class="badge badge-soft hover:bg-brand-100 transition-colors">FAQ</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection