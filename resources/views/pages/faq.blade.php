@extends('layouts.app')

@section('title', 'FAQ — Linka')
@section('description', 'Pertanyaan yang sering ditanyakan tentang Linka, platform monetize audiens.')

@section('content')

<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-brand-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-linka section relative">
        <div class="text-center max-w-2xl mx-auto animate-fade-up">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 backdrop-blur border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-5">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
                FAQ
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-ink-900 text-balance leading-[1.05]">
                Pertanyaan <span class="text-gradient-warm">umum</span>.
            </h1>
            <p class="mt-5 text-lg text-ink-600 text-pretty">
                Semua yang perlu kamu tahu tentang Linka. Tidak ketemu jawabannya? Chat kita di email.
            </p>
        </div>
    </div>
</section>

<section class="section bg-white">
    <div class="container-narrow">
        @php
            $categories = [
                ['icon' => 'rocket-launch', 'gradient' => 'from-brand-500 to-amber-500', 'title' => 'Memulai', 'faqs' => [
                    ['q' => 'Apa itu Linka?', 'a' => 'Linka adalah platform link-in-bio + storefront untuk kreator Indonesia. Buat satu halaman, jual apapun (digital, course, event, donation, toko fisik), terima pembayaran via Duitku.'],
                    ['q' => 'Berapa biaya untuk mulai?', 'a' => 'Gratis. Kamu bisa daftar dan membuat halaman tanpa kartu kredit. Bayar fee hanya kalau ada transaksi sukses (10% untuk Starter, 5% untuk Pro).'],
                    ['q' => 'Apakah saya perlu skill coding?', 'a' => 'Tidak. Setup halaman cukup 5 menit tanpa coding. Pilih tema, upload foto, tambah produk, publish.'],
                ]],
                ['icon' => 'credit-card', 'gradient' => 'from-blue-500 to-cyan-600', 'title' => 'Pembayaran', 'faqs' => [
                    ['q' => 'Metode pembayaran buyer?', 'a' => 'Duitku — support QRIS, virtual account (BCA, BNI, BRI, Mandiri, CIMB), e-wallet (GoPay, OVO, DANA, LinkAja), dan kartu kredit Visa/Mastercard.'],
                    ['q' => 'Kapan uang masuk ke rekening saya?', 'a' => 'Dalam 1-3 hari kerja setelah transaksi sukses. Settlement otomatis ke rekening yang kamu daftarkan di dashboard Settings.'],
                    ['q' => 'Bagaimana cara withdraw?', 'a' => 'Otomatis. Tidak perlu withdraw manual — setiap transaksi sukses akan otomatis di-settle ke rekening kamu.'],
                    ['q' => 'Fee apa saja selain transaction fee?', 'a' => 'Tidak ada. Tanpa biaya setup, tanpa biaya bulanan (untuk Starter), tanpa biaya withdraw.'],
                ]],
                ['icon' => 'shield-check', 'gradient' => 'from-emerald-500 to-teal-600', 'title' => 'Keamanan & Legal', 'faqs' => [
                    ['q' => 'Apakah data saya aman?', 'a' => 'Ya. Kami menggunakan enkripsi SSL, password di-hash dengan bcrypt, dan best-practice security Laravel. PCI compliance untuk payment diproses oleh Duitku.'],
                    ['q' => 'Saya bisa refund buyer?', 'a' => 'Ya, dari dashboard kamu bisa refund transaksi. Refund otomatis dikurangi fee yang sudah dipotong.'],
                    ['q' => 'Apakah ada KYC/verifikasi identitas?', 'a' => 'Untuk transaksi di atas Rp 10 juta/bulan, kami butuh verifikasi KTP & NPWP sesuai regulasi BI.'],
                ]],
                ['icon' => 'cog-6-tooth', 'gradient' => 'from-violet-500 to-indigo-600', 'title' => 'Fitur & Modul', 'faqs' => [
                    ['q' => 'Apa saja 7 modul yang tersedia?', 'a' => 'Digital Product, Course, Event/Webinar, Donation, Blog, Appointment, dan Toko Fisik (dengan ongkir). Semua bisa diaktifkan bersamaan.'],
                    ['q' => 'Bisa pakai custom domain?', 'a' => 'Bisa, di plan Pro dan Business. Cukup tambah CNAME record di DNS kamu, setup sekali di dashboard.'],
                    ['q' => 'Integrasi WhatsApp notifikasi?', 'a' => 'Setiap ada penjualan baru, kamu otomatis dapat notifikasi WA ke nomor yang didaftarkan di Settings > Profile.'],
                    ['q' => 'Apakah ada API?', 'a' => 'REST API tersedia di plan Business. Dokumentasi lengkap di docs.linka.id.'],
                ]],
            ];
        @endphp

        <div class="space-y-10 stagger">
            @foreach ($categories as $cat)
                <div>
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $cat['gradient'] }} flex items-center justify-center shadow-sm">
                            <x-dynamic-component :component="'heroicon-s-' . $cat['icon']" class="w-5 h-5 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold text-ink-900 text-balance">{{ $cat['title'] }}</h2>
                    </div>

                    <div class="space-y-3">
                        @foreach ($cat['faqs'] as $faq)
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
                </div>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="mt-16 text-center p-8 bg-gradient-to-br from-brand-50 to-amber-50 rounded-3xl border border-brand-200/60">
            <h3 class="text-xl font-bold text-ink-900">Masih punya pertanyaan?</h3>
            <p class="mt-2 text-sm text-ink-600">Tim kami siap membantu kamu via email atau WhatsApp.</p>
            <a href="mailto:hello@linka.id" class="mt-5 inline-flex items-center gap-2 btn-cta">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                Email kami
            </a>
        </div>
    </div>
</section>
@endsection