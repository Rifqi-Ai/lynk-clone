@extends('layouts.app')

@section('title', 'Layanan tidak tersedia — Linka')
@section('description', 'Server sedang maintenance atau overload.')

@section('content')
<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-orange-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-narrow section relative text-center">
        <div class="animate-fade-up">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-card-hover mb-6">
                <svg class="w-12 h-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.072a9.033 9.033 0 0 1-2.71-.69m-13.295-2.58a2 2 0 0 0 1.286.43m11.04-2.587a2 2 0 0 1 1.287.43"/></svg>
            </div>

            <div class="text-7xl sm:text-9xl font-black leading-none tracking-tighter mb-4">
                <span class="text-gradient-warm">503</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-ink-900 text-balance">
                Sedang maintenance
            </h1>
            <p class="mt-3 text-lg text-ink-600 max-w-md mx-auto text-pretty">
                Kami sedang merawat server. Akan kembali sebentar lagi. Terima kasih atas kesabarannya!
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="window.location.reload()" class="btn-cta">
                    Coba Lagi
                </button>
                <a href="{{ route('home') }}" class="btn-outline-ink">Ke Beranda</a>
            </div>
        </div>
    </div>
</section>
@endsection