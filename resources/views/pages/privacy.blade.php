@extends('layouts.app')

@section('title', 'Kebijakan Privasi — Linka')
@section('description', 'Bagaimana Linka melindungi data dan privasi pengguna.')

@section('content')

<section class="relative overflow-hidden mesh-gradient">
    <div class="absolute top-1/4 -right-32 w-96 h-96 bg-brand-400/30 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-1/4 -left-32 w-96 h-96 bg-amber-400/30 rounded-full blur-3xl animate-float-slow"></div>

    <div class="container-narrow section relative">
        <div class="animate-fade-up">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 backdrop-blur border border-brand-200 text-brand-700 text-xs font-bold uppercase tracking-wider mb-5">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                Privacy
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-ink-900 text-balance leading-[1.05]">
                Kebijakan <span class="text-gradient-warm">Privasi</span>.
            </h1>
            <p class="mt-5 text-sm text-ink-500 font-medium">Last updated: Juni 2026</p>
        </div>
    </div>
</section>

<section class="section bg-white pt-0">
    <div class="container-narrow">
        <article class="prose prose-lg max-w-none prose-headings:font-display prose-headings:tracking-tight prose-h2:text-2xl prose-h2:font-bold prose-h2:mt-10 prose-h2:mb-4 prose-h2:text-ink-900 prose-p:text-ink-700 prose-p:leading-relaxed prose-li:text-ink-700 prose-a:text-brand-600 prose-a:font-semibold hover:prose-a:underline prose-strong:text-ink-900 prose-strong:font-bold bg-white rounded-3xl border border-ink-200 p-8 md:p-12 shadow-card animate-fade-up">
            <h2>Informasi yang Kami Kumpulkan</h2>
            <ul>
                <li><strong>Akun:</strong> nama, email, username, foto profil</li>
                <li><strong>Pembayaran:</strong> informasi rekening untuk penarikan (disimpan aman di payment processor)</li>
                <li><strong>Produk:</strong> deskripsi, gambar, harga yang kamu upload</li>
                <li><strong>Analytics:</strong> view count, klik, konversi (untuk improve layanan)</li>
            </ul>

            <h2>Bagaimana Kami Menggunakan Informasi</h2>
            <ul>
                <li>Memproses transaksi dan penarikan</li>
                <li>Mengirim notifikasi penting (transaksi, update layanan)</li>
                <li>Meningkatkan fitur dan pengalaman pengguna</li>
                <li>Mencegah fraud dan abuse</li>
            </ul>

            <h2>Kami TIDAK Pernah</h2>
            <ul>
                <li>Menjual data kamu ke pihak ketiga</li>
                <li>Mengirim spam</li>
                <li>Menyimpan informasi kartu kredit (disimpan di payment processor terenkripsi)</li>
            </ul>

            <h2>Cookie & Tracking</h2>
            <p>Kami menggunakan cookie untuk session dan preferensi. Tidak ada iklan pihak ketiga yang tracking kamu di Linka.</p>

            <h2>Hak Kamu</h2>
            <ul>
                <li>Download semua data kamu</li>
                <li>Hapus akun dan semua data kapan saja</li>
                <li>Opt-out dari email marketing</li>
            </ul>

            <h2>Hubungi</h2>
            <p>Pertanyaan tentang privasi: <a href="mailto:privacy@linka.id">privacy@linka.id</a></p>
        </article>
    </div>
</section>
@endsection