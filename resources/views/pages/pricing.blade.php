@extends('layouts.app')

@section('title', 'Harga Simpel — Linka')
@section('description', 'Mulai gratis. Bayar fee hanya kalau jualan. Tanpa komitmen.')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-brand-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-linka section relative">
        <div class="text-center max-w-2xl mx-auto animate-fade-up">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 backdrop-blur border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-5">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                Pricing
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-ink-900 text-balance leading-[1.05]">
                Harga simpel.<br>
                <span class="text-gradient-warm">Tanpa kejutan.</span>
            </h1>
            <p class="mt-5 text-lg text-ink-600 text-pretty">
                Mulai gratis. Bayar fee hanya kalau jualan. Tanpa komitmen, tanpa kontrak.
            </p>
        </div>
    </div>
</section>

{{-- Pricing cards --}}
<section class="section bg-white">
    <div class="container-linka">
        @php
            $plans = [
                ['name' => 'Starter', 'price' => 'Gratis', 'period' => 'selamanya', 'fee' => '10% per transaksi', 'desc' => 'Untuk coba-coba & kreator baru mulai', 'features' => ['Unlimited produk', 'Semua 7 modul', 'Statistik dasar', 'Tema custom', 'Email support'], 'cta' => 'Mulai Gratis', 'highlight' => false, 'icon' => 'rocket-launch', 'gradient' => 'from-slate-500 to-slate-700'],
                ['name' => 'Pro', 'price' => 'Rp 99K', 'period' => '/bulan', 'fee' => '5% per transaksi', 'desc' => 'Untuk kreator serius yang mau scale', 'features' => ['Semua di Starter', 'Custom domain', 'Remove branding', 'WA notifikasi', 'Analytics advanced', '20 GB storage', 'Priority support'], 'cta' => 'Coba Pro 14 Hari', 'highlight' => true, 'icon' => 'star', 'gradient' => 'from-brand-500 to-amber-500'],
                ['name' => 'Business', 'price' => 'Custom', 'period' => '', 'fee' => 'Hingga 0% fee', 'desc' => 'Untuk bisnis & agency', 'features' => ['Semua di Pro', 'Multi-user access', 'API integration', 'Custom email domain', 'Account manager'], 'cta' => 'Hubungi Sales', 'highlight' => false, 'icon' => 'building-office', 'gradient' => 'from-violet-500 to-indigo-600'],
            ];
        @endphp

        <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto items-stretch stagger">
            @foreach ($plans as $plan)
                <div class="relative flex flex-col bg-white rounded-3xl border-2 p-7 md:p-8 transition-all duration-300 hover:-translate-y-1 {{ $plan['highlight'] ? 'border-brand-500 shadow-cta md:scale-105 md:-mt-2' : 'border-ink-200 shadow-card hover:shadow-card-hover hover:border-ink-300' }}">

                    @if ($plan['highlight'])
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-brand-500 to-amber-500 text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                            <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006Z"/></svg>
                            Paling Populer
                        </div>
                    @endif

                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $plan['gradient'] }} flex items-center justify-center mb-5 shadow-sm">
                        <x-dynamic-component :component="'heroicon-s-' . $plan['icon']" class="w-6 h-6 text-white" />
                    </div>

                    <h2 class="text-2xl font-bold text-ink-900 text-balance">{{ $plan['name'] }}</h2>
                    <p class="text-sm text-ink-600 mt-1.5 leading-relaxed">{{ $plan['desc'] }}</p>

                    <div class="mt-6 flex items-baseline gap-1.5">
                        <span class="text-4xl md:text-5xl font-bold text-ink-900 tracking-tight">{{ $plan['price'] }}</span>
                        @if (!empty($plan['period']))
                            <span class="text-sm text-ink-500 font-medium">{{ $plan['period'] }}</span>
                        @endif
                    </div>
                    <div class="inline-flex items-center gap-1 mt-1.5 text-sm font-bold {{ $plan['highlight'] ? 'text-brand-600' : 'text-ink-600' }}">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                        {{ $plan['fee'] }}
                    </div>

                    <ul class="mt-7 space-y-3 text-sm text-ink-700 flex-1">
                        @foreach ($plan['features'] as $feat)
                            <li class="flex gap-2.5 items-start">
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-success/15 text-success flex items-center justify-center mt-0.5">
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                                <span>{{ $feat }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ $plan['name'] === 'Business' ? '#' : route('register') }}" class="mt-7 {{ $plan['highlight'] ? 'btn-cta' : 'btn-outline-ink' }}">
                        {{ $plan['cta'] }}
                        @if ($plan['name'] !== 'Business')
                            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ inline --}}
<section class="section bg-ink-50">
    <div class="container-linka">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-ink-900 text-balance">Pertanyaan yang sering ditanya</h2>
            </div>

            <div class="space-y-3 stagger">
                @php
                    $faqs = [
                        ['q' => 'Apa beda Starter dan Pro?', 'a' => 'Starter gratis dengan fee 10% per transaksi. Pro Rp 99K/bulan dengan fee 5% + fitur premium (custom domain, remove branding, WA notif, analytics, dll).'],
                        ['q' => 'Bisa cancel kapan saja?', 'a' => 'Ya, tidak ada kontrak. Cancel langsung dari dashboard, tidak ada pertanyaan. Pro rate tetap pro-rated di bulan terakhir.'],
                        ['q' => 'Bagaimana fee dihitung?', 'a' => 'Fee hanya dipotong dari SETIAP transaksi yang sukses. Tidak ada fee bulanan untuk Starter, tidak ada fee tersembunyi, tidak ada minimum.'],
                        ['q' => 'Metode pembayaran buyer?', 'a' => 'Duitku — support QRIS, virtual account (BCA, BNI, BRI, Mandiri), e-wallet (GoPay, OVO, DANA), dan kartu kredit.'],
                        ['q' => 'Kapan uang masuk ke rekening saya?', 'a' => 'Otomatis dalam 1-3 hari kerja setelah transaksi sukses, ditransfer ke rekening yang kamu daftarkan di dashboard.'],
                    ];
                @endphp
                @foreach ($faqs as $faq)
                    <details class="group bg-white rounded-2xl border border-ink-200 overflow-hidden hover:border-ink-300 hover:shadow-card transition-all">
                        <summary class="flex items-center justify-between gap-4 p-5 cursor-pointer hover:bg-ink-50 transition-colors">
                            <span class="font-bold text-ink-900 text-balance">{{ $faq['q'] }}</span>
                            <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-ink-100 group-open:bg-brand-500 group-open:text-white flex items-center justify-center transition-all">
                                <svg class="w-4 h-4 group-open:rotate-180 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </span>
                        </summary>
                        <div class="px-5 pb-5 text-ink-600 text-sm leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </details>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('faq') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 hover:text-brand-700">
                    Lihat semua FAQ
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection