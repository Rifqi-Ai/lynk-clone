@extends('layouts.app')

@section('title', 'Akses ditolak — Linka')
@section('description', 'Kamu tidak punya akses ke halaman ini.')

@section('content')
<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-rose-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-narrow section relative text-center">
        <div class="animate-fade-up">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-card-hover mb-6">
                <svg class="w-12 h-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>

            <div class="text-7xl sm:text-9xl font-black leading-none tracking-tighter mb-4">
                <span class="text-gradient-warm">403</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-ink-900 text-balance">
                Akses ditolak
            </h1>
            <p class="mt-3 text-lg text-ink-600 max-w-md mx-auto text-pretty">
                Kamu tidak punya izin untuk mengakses halaman ini. Kalau ini kesalahan, hubungi support.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('home') }}" class="btn-cta">Ke Beranda</a>
                <a href="{{ route('login') }}" class="btn-outline-ink">Login</a>
            </div>
        </div>
    </div>
</section>
@endsection