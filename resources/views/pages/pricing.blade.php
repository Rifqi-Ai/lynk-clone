@extends('layouts.app')

@section('title', 'Harga Simpel — Linka')
@section('description', 'Mulai gratis. Bayar fee hanya kalau jualan. Tanpa komitmen.')

@section('content')
<section class="section bg-gradient-to-b from-white via-brand-50/30 to-white">
    <div class="container-linka">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-bold mb-4">
                <x-heroicon-s-sparkles class="w-3.5 h-3.5" /> Pricing
            </div>
            <h1 class="text-display-lg text-ink-900 text-balance">Harga simpel. Tanpa kejutan.</h1>
            <p class="mt-4 text-lg text-ink-600">Mulai gratis. Bayar fee hanya kalau jualan.</p>
        </div>

        @php
            $plans = [
                ['name' => 'Starter', 'price' => 'Gratis', 'period' => 'selamanya', 'fee' => '10% per transaksi', 'desc' => 'Untuk coba-coba & kreator baru mulai', 'features' => ['Unlimited produk', 'Semua 7 modul', 'Statistik dasar', 'Tema custom', 'Email support'], 'cta' => 'Mulai Gratis', 'highlight' => false, 'icon' => 'rocket-launch'],
                ['name' => 'Pro', 'price' => 'Rp 99K', 'period' => '/bulan', 'fee' => '5% per transaksi', 'desc' => 'Untuk kreator serius yang mau scale', 'features' => ['Semua di Starter', 'Custom domain', 'Remove branding', 'WA notifikasi', 'Analytics advanced', '20 GB storage', 'Priority support'], 'cta' => 'Coba Pro 14 Hari', 'highlight' => true, 'icon' => 'star'],
                ['name' => 'Business', 'price' => 'Custom', 'period' => '', 'fee' => 'Hingga 0% fee', 'desc' => 'Untuk bisnis & agency', 'features' => ['Semua di Pro', 'Multi-user access', 'API integration', 'Custom email domain', 'Account manager'], 'cta' => 'Hubungi Sales', 'highlight' => false, 'icon' => 'building-office'],
            ];
        @endphp

        <div class="grid md:grid-cols-3 gap-5 max-w-5xl mx-auto items-stretch">
            @foreach ($plans as $plan)
                <div class="relative flex flex-col bg-white rounded-3xl border-2 p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-card-hover {{ $plan['highlight'] ? 'border-brand-500 shadow-cta' : 'border-ink-200 shadow-card' }}">
                    @if ($plan['highlight'])
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-brand-500 to-brand-700 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-md">
                            ⭐ Paling Populer
                        </div>
                    @endif

                    <div class="w-11 h-11 rounded-xl {{ $plan['highlight'] ? 'bg-brand-500 text-white' : 'bg-ink-100 text-ink-700' }} flex items-center justify-center mb-4">
                        <x-dynamic-component :component="'heroicon-s-' . $plan['icon']" class="w-5 h-5" />
                    </div>

                    <h2 class="text-xl font-bold text-ink-900">{{ $plan['name'] }}</h2>
                    <p class="text-sm text-ink-500 mt-1">{{ $plan['desc'] }}</p>

                    <div class="mt-5 flex items-baseline gap-1.5">
                        <span class="text-4xl font-bold text-ink-900 tracking-tight">{{ $plan['price'] }}</span>
                        @if (!empty($plan['period']))
                            <span class="text-sm text-ink-500">{{ $plan['period'] }}</span>
                        @endif
                    </div>
                    <div class="text-sm text-brand-600 font-semibold mt-1">{{ $plan['fee'] }}</div>

                    <ul class="mt-6 space-y-3 text-sm text-ink-700 flex-1">
                        @foreach ($plan['features'] as $feat)
                            <li class="flex gap-2.5">
                                <x-heroicon-s-check class="w-4 h-4 text-success flex-shrink-0 mt-0.5" />
                                <span>{{ $feat }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ $plan['name'] === 'Business' ? '#' : route('register') }}" class="mt-7 {{ $plan['highlight'] ? 'btn-cta' : 'btn-outline-ink' }}">
                        {{ $plan['cta'] }}
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-12 max-w-3xl mx-auto">
            <h3 class="text-center text-sm font-bold text-ink-500 uppercase tracking-wider mb-6">Pertanyaan umum</h3>
            <div class="space-y-3">
                @php
                    $faqs = [
                        ['q' => 'Apa beda Starter dan Pro?', 'a' => 'Starter gratis dengan fee 10% per transaksi. Pro Rp 99K/bulan dengan fee 5% + fitur premium (custom domain, remove branding, WA notif, dll).'],
                        ['q' => 'Bisa cancel kapan saja?', 'a' => 'Ya, tidak ada kontrak. Cancel langsung dari dashboard, tidak ada pertanyaan.'],
                        ['q' => 'Bagaimana fee dihitung?', 'a' => 'Fee hanya dipotong dari SETIAP transaksi yang sukses. Tidak ada fee bulanan, tidak ada fee tersembunyi.'],
                        ['q' => 'Metode pembayaran?', 'a' => 'Duitku — support QRIS, virtual account (BCA, BNI, BRI, Mandiri), e-wallet (GoPay, OVO, DANA), dan kartu kredit.'],
                    ];
                @endphp
                @foreach ($faqs as $faq)
                    <details class="group bg-white rounded-2xl border border-ink-200 overflow-hidden">
                        <summary class="flex items-center justify-between gap-4 p-5 cursor-pointer hover:bg-ink-50 transition-colors">
                            <span class="font-semibold text-ink-900">{{ $faq['q'] }}</span>
                            <x-heroicon-o-chevron-down class="w-5 h-5 text-ink-400 group-open:rotate-180 transition-transform" />
                        </summary>
                        <div class="px-5 pb-5 text-ink-600 text-sm leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
