@extends('layouts.app')

@section('title', 'Kebijakan Privasi')
@section('content')
<section class="section">
    <div class="container-narrow">
        <div class="mb-8">
            <p class="text-sm text-ink-500 font-semibold">Last updated: June 2026</p>
            <h1 class="text-display-md text-ink-900 mt-2">Kebijakan Privasi</h1>
        </div>

        <div class="prose prose-ink max-w-none prose-headings:font-display prose-headings:tracking-tight prose-a:text-brand-600 bg-white rounded-2xl border border-ink-200 p-8 shadow-card">
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
        </div>
    </div>
</section>
@endsection
