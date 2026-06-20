@extends('layouts.app')

@section('title', 'Tentang Linka')
@section('description', 'Misi kami: empower kreator Indonesia untuk monetize audiens mereka.')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden mesh-gradient-dark">
    <div class="absolute top-1/4 -left-32 w-96 h-96 bg-white/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-amber-300/40 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-linka section relative">
        <div class="max-w-3xl mx-auto text-center text-white animate-fade-up">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/25 backdrop-blur border border-white/30 text-white text-xs font-bold uppercase tracking-wider mb-5">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                About
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-balance leading-[1.05]">
                Kami percaya<br>
                <span class="text-amber-200">kreator Indonesia</span><br>
                layak dibayar layak.
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/85 max-w-2xl mx-auto text-pretty leading-relaxed">
                Linka adalah platform yang membantu kreator Indonesia mengubah audiens mereka menjadi income — tanpa harus jago coding, tanpa biaya tersembunyi.
            </p>
        </div>
    </div>
</section>

{{-- Mission --}}
<section class="section bg-white">
    <div class="container-linka">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="animate-fade-up">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-4">
                    Our mission
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-ink-900 text-balance leading-[1.1]">
                    Monetize audiens,<br>
                    <span class="text-gradient-warm">tanpa ribet</span>.
                </h2>
                <p class="mt-5 text-lg text-ink-600 leading-relaxed text-pretty">
                    Kami melihat kreator Indonesia berbakat punya audiens besar tapi struggle untuk monetize. Stripe + Gumroad ribet, lokal marketplace potong margin besar, custom website mahal & lama.
                </p>
                <p class="mt-3 text-lg text-ink-600 leading-relaxed text-pretty">
                    Linka lahir untuk jadi jawabannya: setup 5 menit, fee transparan, pembayaran lokal. Fokus bikin karya, biar kami yang urus sisanya.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4 stagger">
                @php
                    $stats = [
                        ['val' => '5K+', 'label' => 'Kreator aktif', 'gradient' => 'from-brand-500 to-amber-500'],
                        ['val' => '50M+', 'label' => 'Followers dijangkau', 'gradient' => 'from-pink-500 to-rose-500'],
                        ['val' => 'Rp 12B+', 'label' => 'Transaksi diproses', 'gradient' => 'from-violet-500 to-indigo-600'],
                        ['val' => '4.9/5', 'label' => 'Rating kreator', 'gradient' => 'from-emerald-500 to-teal-600'],
                    ];
                @endphp
                @foreach ($stats as $s)
                    <div class="card-warm-hover p-6 group">
                        <div class="text-4xl md:text-5xl font-black text-gradient-warm mb-2 tracking-tight">{{ $s['val'] }}</div>
                        <div class="text-sm text-ink-600 font-medium">{{ $s['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Values --}}
<section class="section bg-ink-50">
    <div class="container-linka">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-4xl md:text-5xl font-bold text-ink-900 text-balance animate-fade-up">Nilai yang kami pegang.</h2>
            <p class="mt-4 text-lg text-ink-600 text-pretty animate-fade-up">Bukan cuma fitur, tapi prinsip yang mengarahkan setiap keputusan produk.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-5 stagger">
            @php
                $values = [
                    ['icon' => 'heart', 'title' => 'Kreator-first', 'desc' => 'Setiap fitur, setiap baris kode — untuk kreator. Bukan untuk platform, bukan untuk brand kami.', 'gradient' => 'from-rose-500 to-pink-600'],
                    ['icon' => 'beaker', 'title' => 'Transparan', 'desc' => 'Fee jelas, tidak ada hidden cost. Dashboard analytics real-time, bukan black box.', 'gradient' => 'from-blue-500 to-cyan-600'],
                    ['icon' => 'bolt', 'title' => 'Cepat & simpel', 'desc' => 'Setup 5 menit. Checkout 30 detik. Tidak ada langkah yang tidak perlu.', 'gradient' => 'from-brand-500 to-amber-500'],
                ];
            @endphp
            @foreach ($values as $v)
                <div class="card-warm-hover p-7 group">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $v['gradient'] }} flex items-center justify-center mb-5 shadow-sm group-hover:scale-110 transition-transform">
                        <x-dynamic-component :component="'heroicon-s-' . $v['icon']" class="w-6 h-6 text-white" />
                    </div>
                    <h3 class="text-xl font-bold text-ink-900 text-balance">{{ $v['title'] }}</h3>
                    <p class="mt-2 text-sm text-ink-600 leading-relaxed">{{ $v['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Story --}}
<section class="section bg-white">
    <div class="container-narrow">
        <div class="prose prose-lg max-w-none text-ink-700">
            <h2 class="text-3xl md:text-4xl font-bold text-ink-900 text-balance">Cerita di balik Linka</h2>
            <p class="text-pretty leading-relaxed">
                Linka dibuat pada 2026 di Jakarta, oleh tim kecil yang pernah menjadi kreator dan merasakan sendiri frustrasinya: platform global tidak paham konteks Indonesia, platform lokal UI/UX-nya jadul, dan custom website makan waktu berminggu-minggu.
            </p>
            <p class="text-pretty leading-relaxed">
                Kami mulai dengan MVP sederhana: link-in-bio + jual 1 produk digital. Dari situ, kami iterasi berdasarkan feedback 5,000+ kreator yang sekarang pakai Linka. Setiap modul (course, event, donation, dll) lahir dari kebutuhan nyata kreator Indonesia, bukan dari asumsi kami.
            </p>
            <p class="text-pretty leading-relaxed">
                Hari ini, Linka dipakai oleh kreator dari Sabang sampai Merauke — dari content creator, edukator, musisi, sampai pemilik bisnis kecil. Kami masih kecil, masih belajar, tapi kami committed untuk terus jadi rumah yang aman dan menguntungkan bagi kreator Indonesia.
            </p>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="section">
    <div class="container-narrow">
        <div class="text-center p-10 bg-gradient-to-br from-brand-50 via-amber-50 to-brand-50 rounded-3xl border border-brand-200 animate-fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-ink-900 text-balance">Mulai monetize audiens kamu hari ini.</h2>
            <p class="mt-3 text-ink-600">Setup 5 menit. Gratis tanpa kartu kredit.</p>
            <a href="{{ route('register') }}" class="mt-6 inline-flex items-center gap-2 btn-cta">
                Buat Halaman Gratis
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection