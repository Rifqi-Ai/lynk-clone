@extends('layouts.app')

@section('title', 'Linka — Satu link, jual apapun. Powering the Creator Economy.')
@section('description', 'Buat halaman kamu, jual produk digital, course, event, appointment, dan lainnya. Gratis untuk mulai, upgrade saat berkembang.')

@section('content')
{{-- ============================================================
     HERO SECTION
     ============================================================ --}}
<section class="relative overflow-hidden">
    {{-- Background: warm radial gradient + grid pattern --}}
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50/60 via-white to-white"></div>
    <div class="absolute inset-0 -z-10 opacity-30" style="background-image: radial-gradient(circle at 1px 1px, #FF6B35 1px, transparent 0); background-size: 32px 32px;"></div>

    {{-- Floating blur orbs --}}
    <div class="absolute -top-20 -right-20 w-96 h-96 bg-brand-300/20 rounded-full blur-3xl -z-10"></div>
    <div class="absolute top-1/2 -left-32 w-72 h-72 bg-accent/20 rounded-full blur-3xl -z-10"></div>

    <div class="container-linka section relative">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- ─── Copy ─── --}}
            <div class="text-center lg:text-left">
                {{-- Eyebrow badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-brand-200 shadow-sm text-xs font-semibold text-brand-700 mb-6">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex w-full h-full rounded-full bg-brand-400 opacity-75 animate-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-brand-500"></span>
                    </span>
                    Powering 5,000+ Indonesian creators
                </div>

                <h1 class="text-display-xl text-ink-900 text-balance">
                    Satu link di bio.
                    <span class="block text-gradient">Jual apapun.</span>
                </h1>

                <p class="mt-6 text-lg md:text-xl text-ink-600 leading-relaxed max-w-xl mx-auto lg:mx-0 text-pretty">
                    Produk digital, course, booking, event, donasi, sampai toko fisik.
                    Mulai gratis, tanpa kartu kredit, langsung terima pembayaran.
                </p>

                {{-- CTAs --}}
                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="btn-cta group">
                        Mulai Gratis Sekarang
                        <x-heroicon-s-arrow-right class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                    </a>
                    <a href="/demo_bob" class="btn-outline-ink group">
                        <x-heroicon-o-play class="w-4 h-4" />
                        Lihat Demo
                    </a>
                </div>

                {{-- Trust line --}}
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 justify-center lg:justify-start text-sm text-ink-500">
                    <div class="flex items-center gap-1.5">
                        <x-heroicon-s-check class="w-4 h-4 text-success" />
                        <span>Gratis selamanya</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-heroicon-s-check class="w-4 h-4 text-success" />
                        <span>Tanpa kartu kredit</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-heroicon-s-check class="w-4 h-4 text-success" />
                        <span>Setup 5 menit</span>
                    </div>
                </div>
            </div>

            {{-- ─── Right: Phone + floating UI cards ─── --}}
            <div class="relative lg:pl-8">
                {{-- Main phone mockup --}}
                <div class="relative mx-auto" style="max-width: 340px;">
                    <div class="relative bg-ink-900 rounded-[2.5rem] p-2.5 shadow-2xl">
                        <div class="bg-white rounded-[2rem] overflow-hidden aspect-[9/19.5]">
                            {{-- Status bar --}}
                            <div class="bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 pt-6 pb-20 relative">
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-5 bg-ink-900 rounded-b-2xl"></div>
                            </div>
                            <div class="-mt-16 px-5 pb-6 relative">
                                {{-- Avatar --}}
                                <div class="flex justify-center">
                                    <div class="w-20 h-20 rounded-full p-0.5 bg-white shadow-lg">
                                        <div class="w-full h-full rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-2xl font-bold">@</div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <div class="font-bold text-base text-ink-900">@yourname</div>
                                    <div class="text-xs text-ink-500 mt-0.5">Creator & Educator</div>
                                </div>
                                {{-- Mini link cards --}}
                                <div class="space-y-2 mt-5">
                                    <div class="flex items-center gap-2.5 p-2.5 bg-ink-50 rounded-xl">
                                        <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600">
                                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-ink-900 truncate">E-Book Photography</div>
                                            <div class="text-[10px] text-ink-500">Rp 99K</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2.5 p-2.5 bg-ink-50 rounded-xl">
                                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                                            <x-heroicon-o-academic-cap class="w-4 h-4" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-ink-900 truncate">Premium Course</div>
                                            <div class="text-[10px] text-ink-500">Rp 499K</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2.5 p-2.5 bg-ink-50 rounded-xl">
                                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                                            <x-heroicon-o-clock class="w-4 h-4" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-ink-900 truncate">1:1 Coaching</div>
                                            <div class="text-[10px] text-ink-500">Rp 299K</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating: sale notification --}}
                    <div class="hidden sm:flex absolute -left-12 lg:-left-20 top-1/4 bg-white rounded-2xl shadow-xl p-3 border border-ink-100 items-center gap-3 animate-bounce-subtle">
                        <div class="w-9 h-9 rounded-xl bg-success-100 flex items-center justify-center text-success-600">
                            <x-heroicon-s-banknotes class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-[10px] text-ink-500 font-medium">Penjualan baru</div>
                            <div class="text-sm font-bold text-ink-900">+Rp 1.250.000</div>
                        </div>
                    </div>

                    {{-- Floating: followers --}}
                    <div class="hidden sm:flex absolute -right-8 lg:-right-12 bottom-1/4 bg-white rounded-2xl shadow-xl p-3 border border-ink-100 items-center gap-3 animate-bounce-subtle" style="animation-delay: 1s;">
                        <div class="w-9 h-9 rounded-xl bg-brand-100 flex items-center justify-center text-brand-600">
                            <x-heroicon-s-user-plus class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-[10px] text-ink-500 font-medium">Follower baru</div>
                            <div class="text-sm font-bold text-ink-900">+127 hari ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     SOCIAL PROOF / TRUSTED BY
     ============================================================ --}}
<section class="py-10 border-y border-ink-100 bg-white/60 backdrop-blur-sm">
    <div class="container-linka px-5">
        <p class="text-center text-xs font-semibold uppercase tracking-wider text-ink-400 mb-6">Dipercaya oleh kreator dan brand Indonesia</p>
        <div class="flex flex-wrap justify-center items-center gap-x-10 gap-y-3">
            @foreach(['Traveloka', 'GoPay', 'AJAIB', 'ASUS', 'TCL', 'Hyundai'] as $brand)
                <span class="text-lg font-bold text-ink-400 hover:text-ink-700 transition">{{ $brand }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     7 MODULES — what you can sell
     ============================================================ --}}
<section id="features" class="section">
    <div class="container-linka">
        {{-- Section header --}}
        <div class="text-center max-w-2xl mx-auto mb-14">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-bold mb-4">
                <x-heroicon-s-sparkles class="w-3.5 h-3.5" /> 7 Powerful Modules
            </div>
            <h2 class="text-display-lg text-ink-900 text-balance">Satu platform. Tujuh cara monetize.</h2>
            <p class="mt-4 text-lg text-ink-600 text-pretty">
                Dari produk digital sampai toko fisik. Semua dalam satu link yang elegan.
            </p>
        </div>

        {{-- Modules grid --}}
        @php
            $modules = [
                ['icon' => 'arrow-down-tray', 'title' => 'Digital Product', 'desc' => 'Ebook, preset, template, software — file apa pun, sekali bayar.', 'color' => 'brand', 'url' => '/demo_alice'],
                ['icon' => 'newspaper', 'title' => 'Blog & Article', 'desc' => 'Tulis cerita, tutorial, atau insight. Gratis atau berbayar.', 'color' => 'amber', 'url' => '/demo_bob'],
                ['icon' => 'calendar-days', 'title' => 'Appointment', 'desc' => 'Jadwalkan 1:1 coaching, mentoring, atau fan meet.', 'color' => 'blue', 'url' => '/demo_alice'],
                ['icon' => 'academic-cap', 'title' => 'Online Course', 'desc' => 'Upload video course. Track progress, kasih sertifikat.', 'color' => 'purple', 'url' => '/demo_diana'],
                ['icon' => 'ticket', 'title' => 'Event / Webinar', 'desc' => 'Jual tiket online + QR check-in di venue.', 'color' => 'pink', 'url' => '/demo_bob'],
                ['icon' => 'heart', 'title' => 'Donation', 'desc' => 'Terima dukungan sekali klik dari fans. Tanpa minimum.', 'color' => 'rose', 'url' => '/demo_alice'],
                ['icon' => 'gift', 'title' => 'Physical Store', 'desc' => 'Jual merchandise, kelola ongkir, fulfillment dashboard.', 'color' => 'emerald', 'url' => '/demo_eko'],
                ['icon' => 'link', 'title' => 'Link in Bio', 'desc' => 'Satu link untuk semua konten & produk kamu di Instagram/TikTok.', 'color' => 'cyan', 'url' => '/'],
            ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 stagger">
            @foreach ($modules as $mod)
                <a href="{{ $mod['url'] }}" class="group relative block bg-white rounded-2xl border border-ink-200 p-6 shadow-card hover:shadow-card-hover hover:border-ink-300 hover:-translate-y-1 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-{{ $mod['color'] }}-100 flex items-center justify-center text-{{ $mod['color'] }}-600 group-hover:scale-110 transition-transform">
                            <x-dynamic-component :component="'heroicon-o-' . $mod['icon']" class="w-6 h-6" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-ink-900 group-hover:text-brand-600 transition-colors">{{ $mod['title'] }}</h3>
                            <p class="text-sm text-ink-500 mt-1 leading-relaxed">{{ $mod['desc'] }}</p>
                        </div>
                    </div>
                    <x-heroicon-s-arrow-right class="absolute top-6 right-6 w-4 h-4 text-ink-300 group-hover:text-brand-500 group-hover:translate-x-1 transition-all" />
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     HOW IT WORKS
     ============================================================ --}}
<section class="section bg-ink-50">
    <div class="container-linka">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-display-lg text-ink-900 text-balance">Setup 5 menit. Mulai jualan malam ini juga.</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @php
                $steps = [
                    ['num' => '01', 'title' => 'Buat halaman', 'desc' => 'Daftar gratis, pilih username, customize tema.'],
                    ['num' => '02', 'title' => 'Upload produk', 'desc' => 'Tambah produk digital, course, event, atau apapun.'],
                    ['num' => '03', 'title' => 'Mulai terima bayar', 'desc' => 'Share link di bio. Duitku handle pembayaran otomatis.'],
                ];
            @endphp
            @foreach ($steps as $i => $step)
                <div class="relative">
                    <div class="text-7xl font-black text-brand-100 leading-none mb-4">{{ $step['num'] }}</div>
                    <h3 class="text-xl font-bold text-ink-900">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-ink-600 leading-relaxed">{{ $step['desc'] }}</p>
                    @if ($i < 2)
                        <x-heroicon-s-arrow-right class="hidden md:block absolute top-1/2 -right-4 w-6 h-6 text-ink-300" />
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     TESTIMONIALS
     ============================================================ --}}
<section class="section">
    <div class="container-linka">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-display-lg text-ink-900 text-balance">Kata mereka yang sudah jualan di Linka</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-5">
            @php
                $testimonials = [
                    ['quote' => 'Cuma modal 30 menit setup, langsung closing Rp 2 juta di hari pertama. Conversion rate naik 3x lipat sejak pindah ke Linka.', 'name' => 'Rina Maharani', 'role' => 'Content Creator, 125K followers', 'avatar' => 'R'],
                    ['quote' => 'Course Lightroom saya laku 400+ kopi dalam 2 bulan. Yang paling keren: buyer langsung dapat akses tanpa ribet.', 'name' => 'Andika Wiratama', 'role' => 'Photography Educator', 'avatar' => 'A'],
                    ['quote' => 'Donation dari fans jadi sumber income kedua terbesar saya. Terima kasih Linka!', 'name' => 'Sari Indah', 'role' => 'Music Artist', 'avatar' => 'S'],
                ];
            @endphp
            @foreach ($testimonials as $t)
                <figure class="bg-white rounded-2xl border border-ink-200 p-6 shadow-card hover:shadow-card-hover transition-shadow">
                    <div class="flex gap-1 text-amber-400 mb-4">
                        @for($i=0; $i<5; $i++)
                            <x-heroicon-s-star class="w-4 h-4" />
                        @endfor
                    </div>
                    <blockquote class="text-ink-700 leading-relaxed">"{{ $t['quote'] }}"</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold">{{ $t['avatar'] }}</div>
                        <div>
                            <div class="font-semibold text-ink-900 text-sm">{{ $t['name'] }}</div>
                            <div class="text-xs text-ink-500">{{ $t['role'] }}</div>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     PRICING
     ============================================================ --}}
<section id="pricing" class="section bg-gradient-to-b from-white via-brand-50/30 to-white">
    <div class="container-linka">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-display-lg text-ink-900 text-balance">Harga simpel. Tanpa kejutan.</h2>
            <p class="mt-4 text-lg text-ink-600">Mulai gratis. Bayar fee hanya kalau jualan.</p>
        </div>

        @php
            $plans = [
                ['name' => 'Starter', 'price' => 'Gratis', 'period' => 'selamanya', 'fee' => '10% fee per transaksi', 'desc' => 'Untuk coba-coba & kreator baru mulai', 'features' => ['Unlimited produk', 'Semua 7 modul', 'Statistik dasar', 'Tema custom', 'Email support'], 'cta' => 'Mulai Gratis', 'highlight' => false, 'icon' => 'rocket-launch'],
                ['name' => 'Pro', 'price' => 'Rp 99K', 'period' => '/bulan', 'fee' => '5% fee per transaksi', 'desc' => 'Untuk kreator serius yang mau scale', 'features' => ['Semua di Starter', 'Custom domain', 'Remove branding', 'WA notifikasi', 'Analytics advanced', '20 GB storage', 'Priority support'], 'cta' => 'Coba Pro 14 Hari', 'highlight' => true, 'icon' => 'star'],
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

                    {{-- Icon --}}
                    <div class="w-11 h-11 rounded-xl {{ $plan['highlight'] ? 'bg-brand-500 text-white' : 'bg-ink-100 text-ink-700' }} flex items-center justify-center mb-4">
                        <x-dynamic-component :component="'heroicon-s-' . $plan['icon']" class="w-5 h-5" />
                    </div>

                    <h3 class="text-xl font-bold text-ink-900">{{ $plan['name'] }}</h3>
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

                    <a href="{{ route('register') }}" class="mt-7 {{ $plan['highlight'] ? 'btn-cta' : 'btn-outline-ink' }}">
                        {{ $plan['cta'] }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     FINAL CTA
     ============================================================ --}}
<section class="section">
    <div class="container-linka">
        <div class="relative bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 rounded-3xl p-10 md:p-16 text-center text-white overflow-hidden shadow-cta">
            {{-- Background pattern --}}
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;"></div>
            <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>

            <div class="relative max-w-2xl mx-auto">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur text-xs font-semibold mb-4">
                    <x-heroicon-s-bolt class="w-3.5 h-3.5" /> Setup 5 menit
                </div>
                <h2 class="text-display-lg text-balance">Siap monetize audiens kamu?</h2>
                <p class="mt-4 text-lg text-brand-100 text-pretty">
                    Gabung 5,000+ kreator Indonesia yang sudah jualan di Linka. Gratis, tanpa kartu kredit.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 h-14 px-8 rounded-xl bg-white text-brand-700 font-bold hover:bg-brand-50 hover:-translate-y-0.5 shadow-lg transition-all duration-150">
                        Buat Halaman Gratis
                        <x-heroicon-s-arrow-right class="w-4 h-4" />
                    </a>
                    <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center gap-2 h-14 px-8 rounded-xl bg-white/10 backdrop-blur text-white font-semibold hover:bg-white/20 border border-white/20 transition-all">
                        Lihat Pricing
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
