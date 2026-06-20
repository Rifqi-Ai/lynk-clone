@extends('layouts.app')

@section('title', 'Server error — Linka')
@section('description', 'Terjadi kesalahan pada server. Tim kami sudah diberitahu.')

@section('content')
<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-rose-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-narrow section relative text-center">
        <div class="animate-fade-up">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-card-hover mb-6">
                <svg class="w-12 h-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>

            <div class="text-7xl sm:text-9xl font-black leading-none tracking-tighter mb-4">
                <span class="text-gradient-warm">500</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-ink-900 text-balance">
                Ada masalah di server kami
            </h1>
            <p class="mt-3 text-lg text-ink-600 max-w-md mx-auto text-pretty">
                Tim teknis kami sudah diberitahu. Coba lagi dalam beberapa menit, atau kembali ke beranda.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('home') }}" class="btn-cta">Ke Beranda</a>
                <button onclick="window.location.reload()" class="btn-outline-ink">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Coba Lagi
                </button>
            </div>
        </div>
    </div>
</section>
@endsection