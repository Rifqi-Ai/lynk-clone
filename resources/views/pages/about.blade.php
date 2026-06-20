@extends('layouts.app')

@section('title', 'Tentang Linka')
@section('description', 'Misi kami: empower kreator Indonesia untuk monetize audiens mereka.')

@section('content')
<section class="section">
    <div class="container-narrow">
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-bold mb-4">
                <x-heroicon-o-sparkles class="w-3.5 h-3.5" /> Our Story
            </div>
            <h1 class="text-display-lg text-ink-900 text-balance">Misi kami sederhana:</h1>
            <p class="mt-3 text-2xl text-gradient font-bold text-balance">Bantu setiap kreator Indonesia monetize audiensnya.</p>
        </div>

        <div class="prose prose-lg max-w-none prose-headings:font-display prose-headings:tracking-tight prose-a:text-brand-600">
            <p>Kami percaya kreator Indonesia punya potensi luar biasa. Banyak yang sudah punya audiens loyal di Instagram, TikTok, YouTube — tapi belum punya tools yang tepat untuk monetize.</p>

            <p>Platform besar dari luar negeri tidak di-design untuk kreator Indonesia. Fee-nya tinggi, tidak support Rupiah optimal, dan tidak ada integrasi dengan payment lokal.</p>

            <h2>Karena itu, kami bangun Linka.</h2>
            <p>Satu link yang bisa jualan apa pun. Didukung payment gateway lokal (Duitku). Fee transparan, mulai gratis, dengan support dalam bahasa Indonesia.</p>

            <h2>Tim kami</h2>
            <p>Kecil tapi bertenaga. Founder + engineer + designer. Semua kerja remote dari berbagai kota di Indonesia. Kami bukan startup unicorn — kami bangun bisnis sustainable yang solve masalah nyata.</p>

            <h2>Hubungi kami</h2>
            <p>Punya pertanyaan atau masukan? <a href="#">Email kami</a> atau <a href="#">chat WhatsApp</a>. Kami baca semua pesan.</p>
        </div>

        {{-- Stats --}}
        <div class="mt-16 grid grid-cols-3 gap-4">
            <div class="text-center p-6 bg-white rounded-2xl border border-ink-200 shadow-card">
                <div class="text-3xl md:text-4xl font-bold text-ink-900 tracking-tight">5K+</div>
                <div class="text-xs text-ink-500 mt-1 font-semibold uppercase tracking-wider">Creators</div>
            </div>
            <div class="text-center p-6 bg-white rounded-2xl border border-ink-200 shadow-card">
                <div class="text-3xl md:text-4xl font-bold text-ink-900 tracking-tight">50M+</div>
                <div class="text-xs text-ink-500 mt-1 font-semibold uppercase tracking-wider">Reach</div>
            </div>
            <div class="text-center p-6 bg-white rounded-2xl border border-ink-200 shadow-card">
                <div class="text-3xl md:text-4xl font-bold text-ink-900 tracking-tight">Rp 12B</div>
                <div class="text-xs text-ink-500 mt-1 font-semibold uppercase tracking-wider">Transaksi</div>
            </div>
        </div>
    </div>
</section>
@endsection
