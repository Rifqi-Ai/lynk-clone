@extends('layouts.app')

@section('title', 'FAQ — Pertanyaan Umum')
@section('description', 'Pertanyaan yang sering ditanyakan tentang Linka.')

@section('content')
<section class="section">
    <div class="container-narrow">
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-bold mb-4">
                <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5" /> Help Center
            </div>
            <h1 class="text-display-md text-ink-900 text-balance">Pertanyaan yang sering ditanyakan</h1>
            <p class="mt-3 text-ink-500">Belum ketemu jawabannya? <a href="#" class="text-brand-600 font-semibold hover:underline">Chat WhatsApp kami</a>.</p>
        </div>

        <div class="space-y-3">
            @php
                $faqs = [
                    ['q' => 'Apa itu Linka?', 'a' => 'Linka adalah platform link-in-bio + commerce untuk kreator Indonesia. Buat satu link yang bisa jual produk digital, course, event, appointment, donasi, sampai toko fisik.'],
                    ['q' => 'Berapa biayanya?', 'a' => 'Gratis untuk mulai (Starter). Pro Rp 99K/bulan dengan fee 5% per transaksi. Starter fee 10% per transaksi.'],
                    ['q' => 'Bagaimana cara menerima pembayaran?', 'a' => 'Kami pakai Duitku — payment gateway Indonesia. Support QRIS, virtual account semua bank, e-wallet (GoPay, OVO, DANA), dan kartu kredit.'],
                    ['q' => 'Kapan uang saya masuk?', 'a' => 'Setelah transaksi sukses, saldo langsung masuk ke dashboard Linka kamu. Penarikan ke rekening bank diproses 1-3 hari kerja.'],
                    ['q' => 'Apakah ada aplikasi mobile?', 'a' => 'Saat ini Linka adalah web app yang mobile-friendly. Aplikasi native iOS/Android sedang dalam pengembangan.'],
                    ['q' => 'Bisa pakai custom domain?', 'a' => 'Ya, di plan Pro. Tinggal pointing CNAME domain kamu ke linka.id, otomatis jadi halaman kamu.'],
                    ['q' => 'Bagaimana dengan pajak?', 'a' => 'Kamu bertanggung jawab atas pajak penghasilanmu. Kami menyediakan laporan transaksi lengkap untuk membantu pelaporan pajak tahunan.'],
                ];
            @endphp
            @foreach ($faqs as $faq)
                <details class="group bg-white rounded-2xl border border-ink-200 overflow-hidden shadow-card hover:shadow-card-hover transition-shadow">
                    <summary class="flex items-center justify-between gap-4 p-5 cursor-pointer hover:bg-ink-50 transition-colors">
                        <span class="font-semibold text-ink-900">{{ $faq['q'] }}</span>
                        <x-heroicon-o-chevron-down class="w-5 h-5 text-ink-400 group-open:rotate-180 transition-transform flex-shrink-0" />
                    </summary>
                    <div class="px-5 pb-5 text-ink-600 leading-relaxed">
                        {{ $faq['a'] }}
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endsection
