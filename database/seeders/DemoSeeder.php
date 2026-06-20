<?php

namespace Database\Seeders;

use App\Models\CourseModule;
use App\Models\EventTicket;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Seed the application with realistic demo data.
     *
     * Creates:
     * - 5 demo creators with varied profiles (different titles, bios, plan tiers)
     * - 15+ digital products across different creators (some draft, most published)
     * - A few sample orders (paid + pending)
     */
    public function run(): void
    {
        // Clean existing demo data
        User::whereIn('username', ['demo_alice', 'demo_bob', 'demo_charlie', 'demo_diana', 'demo_eko'])->delete();

        $creators = [
            [
                'username' => 'demo_alice',
                'name' => 'Alice Pratama',
                'email' => 'alice@demo.linka.id',
                'title' => 'Storyteller | Lightroom Presets',
                'bio' => "📸 Photographer turned educator.\n\nHelping beginners master mobile photography through simple presets and courses.\n\n🎓 5,000+ students\n✨ Featured by Apple Indonesia",
                'plan_tier' => 'pro',
                'transaction_fee_pct' => 5.00,
                'verified' => true,
            ],
            [
                'username' => 'demo_bob',
                'name' => 'Bob Nugroho',
                'email' => 'bob@demo.linka.id',
                'title' => 'Finance Coach',
                'bio' => "💰 Personal finance coach.\n\nBelajar kelola uang, investasi reksa dana, dan budgeting. Diskusi terbuka untuk semua kalangan.",
                'plan_tier' => 'pro',
                'transaction_fee_pct' => 5.00,
                'verified' => true,
            ],
            [
                'username' => 'demo_charlie',
                'name' => 'Charlie Wijaya',
                'email' => 'charlie@demo.linka.id',
                'title' => 'Productivity Coach | Habit Expert',
                'bio' => "🧠 Atomic Habits certified coach.\n\nSharing practical frameworks for better productivity, focus, and habit building.",
                'plan_tier' => 'starter',
                'transaction_fee_pct' => 10.00,
                'verified' => false,
            ],
            [
                'username' => 'demo_diana',
                'name' => 'Diana Sari',
                'email' => 'diana@demo.linka.id',
                'title' => 'Writer & Content Creator',
                'bio' => "✍️ Indonesian novelist.\n\nCerpen, novel, dan journaling. Suka bikin worksheet buat bantu kamu proses emosi lewat menulis.",
                'plan_tier' => 'starter',
                'transaction_fee_pct' => 10.00,
                'verified' => false,
            ],
            [
                'username' => 'demo_eko',
                'name' => 'Eko Budiman',
                'email' => 'eko@demo.linka.id',
                'title' => 'Web Developer & Educator',
                'bio' => "💻 Full-stack developer.\n\nNgajar web dev dari dasar sampai production. Laravel, React, dan DevOps.",
                'plan_tier' => 'pro',
                'transaction_fee_pct' => 5.00,
                'verified' => true,
            ],
        ];

        foreach ($creators as $data) {
            User::create(array_merge($data, [
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]));
        }

        // Add products for each creator
        $alice = User::where('username', 'demo_alice')->first();
        $bob = User::where('username', 'demo_bob')->first();
        $charlie = User::where('username', 'demo_charlie')->first();
        $diana = User::where('username', 'demo_diana')->first();
        $eko = User::where('username', 'demo_eko')->first();

        $products = [
            // Alice's products (photography)
            [
                'user_id' => $alice->id, 'title' => 'Sunset Lightroom Preset Pack',
                'description' => "5 preset cinematic untuk foto golden hour.\n\nCocok untuk:\n- Sunset & sunrise\n- Portrait warm tone\n- Beach & outdoor\n\nFormat: .xmp + .dng (mobile + desktop compatible)",
                'price' => 79000, 'compare_at_price' => 149000, 'status' => 'published', 'type' => 'digital',
                'meta' => ['file_format' => 'XMP + DNG', 'file_size' => '24 MB', 'preview_length' => null],
            ],
            [
                'user_id' => $alice->id, 'title' => 'Mobile Photography Masterclass',
                'description' => "Kelas video 90 menit lengkap dengan worksheet.\n\n## Yang Akan Dipelajari\n- Composition rules\n- Lighting basics\n- Mobile editing workflow\n- Building your visual style",
                'price' => 299000, 'compare_at_price' => 499000, 'status' => 'published', 'type' => 'digital',
                'meta' => ['file_format' => 'MP4 + PDF', 'file_size' => '1.2 GB'],
            ],
            [
                'user_id' => $alice->id, 'title' => '1:1 Portfolio Review (60 min)',
                'description' => 'Konsultasi portfolio foto dengan feedback personal via Zoom atau Google Meet.',
                'price' => 500000, 'status' => 'published', 'type' => 'appointment',
                'meta' => ['duration_minutes' => 60, 'location_type' => 'online', 'buffer_minutes' => 15],
            ],
            [
                'user_id' => $alice->id, 'title' => 'Coffee Chat — Buy Me a Coffee',
                'description' => "Support my work with a small donation. Every coffee helps me create more free content!\n\nYou can also write a personal message — I read every one of them ☕",
                'price' => 25000, 'status' => 'published', 'type' => 'donation',
                'meta' => ['goal' => 5000000, 'preset_amounts' => [25000, 50000, 100000, 250000]],
            ],
            [
                'user_id' => $alice->id, 'title' => 'Bali Lightroom Pack (Coming Soon)',
                'description' => 'Coming soon.', 'price' => 99000, 'status' => 'draft', 'type' => 'digital',
            ],

            // Bob's products (finance)
            ['user_id' => $bob->id, 'title' => 'Budget Tracker Spreadsheet (Excel)', 'description' => 'Template budgeting 50/30/20 dengan dashboard otomatis.', 'price' => 49000, 'status' => 'published', 'type' => 'digital', 'meta' => ['file_format' => 'XLSX', 'file_size' => '8 MB']],
            ['user_id' => $bob->id, 'title' => 'Reksa Dana untuk Pemula (Ebook)', 'description' => 'Panduan investasi reksa dana dari nol.', 'price' => 99000, 'compare_at_price' => 199000, 'status' => 'published', 'type' => 'digital', 'meta' => ['file_format' => 'PDF', 'file_size' => '15 MB']],
            [
                'user_id' => $bob->id, 'title' => 'Konsultasi Keuangan Pribadi',
                'description' => "1:1 coaching 60 menit via Zoom.\n\nTopik yang bisa dibahas:\n- Budgeting & cash flow\n- Dana darurat\n- Investasi pemula (reksa dana, obligasi)\n- Financial goals planning",
                'price' => 750000, 'status' => 'published', 'type' => 'appointment',
                'meta' => ['duration_minutes' => 60, 'location_type' => 'online', 'buffer_minutes' => 30],
            ],
            [
                'user_id' => $bob->id, 'title' => 'Webinar: Financial Freedom untuk Milenial',
                'description' => "Webinar 2 jam membahas langkah konkret menuju kebebasan finansial di usia 30-an.\n\n**Bonus**: 1 bulan akses komunitas eksklusif + rekaman webinar selamanya.",
                'price' => 150000, 'status' => 'published', 'type' => 'event',
                'meta' => [
                    'event_date' => now()->addDays(14)->setTime(19, 0)->toIso8601String(),
                    'capacity' => 200, 'event_location' => 'Zoom',
                ],
            ],

            // Charlie's products (productivity)
            ['user_id' => $charlie->id, 'title' => 'Atomic Habits Rangkuman PDF', 'description' => 'Rangkuman 35 halaman buku best seller James Clear.', 'price' => 29000, 'compare_at_price' => 99000, 'status' => 'published', 'type' => 'digital', 'meta' => ['file_format' => 'PDF', 'file_size' => '12 MB']],
            ['user_id' => $charlie->id, 'title' => 'Habit Tracker Bundle', 'description' => '5 template tracking untuk berbagai kebiasaan.', 'price' => 39000, 'status' => 'published', 'type' => 'digital', 'meta' => ['file_format' => 'PDF', 'file_size' => '4 MB']],
            [
                'user_id' => $charlie->id, 'title' => 'Webinar: Build Better Habits',
                'description' => "Webinar 2 jam + Q&A. Tiket terbatas.\n\nTopik:\n1. Habit stacking framework\n2. Environment design\n3. Identity-based habits",
                'price' => 50000, 'status' => 'published', 'type' => 'event',
                'meta' => [
                    'event_date' => now()->addDays(7)->setTime(20, 0)->toIso8601String(),
                    'capacity' => 100, 'event_location' => 'Zoom',
                ],
            ],
            [
                'user_id' => $charlie->id, 'title' => 'Weekly Coaching Call',
                'description' => 'Group coaching setiap Jumat.',
                'price' => 99000, 'status' => 'published', 'type' => 'appointment',
                'meta' => ['duration_minutes' => 45, 'location_type' => 'online', 'buffer_minutes' => 15],
            ],

            // Diana's products (writing)
            ['user_id' => $diana->id, 'title' => 'Journaling Worksheet Set', 'description' => '30 worksheet untuk daily journaling.', 'price' => 39000, 'status' => 'published', 'type' => 'digital', 'meta' => ['file_format' => 'PDF', 'file_size' => '18 MB']],
            ['user_id' => $diana->id, 'title' => 'Cerpen: "Senja di Ujung Jalan"', 'description' => 'Cerpen original 15 halaman.', 'price' => 15000, 'status' => 'published', 'type' => 'digital', 'meta' => ['file_format' => 'PDF', 'file_size' => '2 MB']],
            [
                'user_id' => $diana->id, 'title' => 'Belajar Menulis Cerpen',
                'description' => "Kelas menulis 4 modul — dari ide sampai cerpen siap publish.\n\n**Untuk siapa**: pemula yang mau mulai menulis, intermediate yang mau refine skill.\n\n**Total durasi**: ~6 jam video. Akses selamanya.",
                'price' => 199000, 'compare_at_price' => 399000, 'status' => 'published', 'type' => 'course',
                'meta' => [
                    'duration_minutes' => 360, 'modules_count' => 4, 'level' => 'beginner',
                    'video_preview_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'curriculum' => [
                        ['title' => 'Module 1: Anatomy of a Short Story', 'duration' => 90],
                        ['title' => 'Module 2: Building Characters that Breathe', 'duration' => 90],
                        ['title' => 'Module 3: Plot Structure & Pacing', 'duration' => 90],
                        ['title' => 'Module 4: Polish & Publication', 'duration' => 90],
                    ],
                ],
            ],
            [
                'user_id' => $diana->id, 'title' => 'Senja — Koleksi Cerpen Pilihan',
                'description' => "# Senja: Tujuh Cerpen tentang Cahaya yang Memudar\n\nKoleksi 7 cerpen original yang pernah tayang di berbagai jurnal sastra.\n\n## Daftar Cerpen\n1. Senja di Ujung Jalan\n2. Kopi Pagi untuk Ibu\n3. Surat yang Tak Terkirim\n4. Tentang Laki-laki yang Menunggu\n5. Hujan Pertama di Musim Kemarau\n6. Playlist untuk Musim yang Pergi\n7. Catatan dari Pinggir Kota\n\n---\n\n> *\"Setiap cerita di sini dimulai dengan satu kalimat yang tidak bisa saya hapus dari kepala.\"* — Diana",
                'price' => 49000, 'status' => 'published', 'type' => 'blog',
                'meta' => ['preview_length' => 400, 'is_paywalled' => true],
            ],
            [
                'user_id' => $diana->id, 'title' => 'Gratis: Cara Mulai Journaling',
                'description' => "# Cara Mulai Journaling (Panduan Gratis)\n\nJournaling bukan cuma nulis diary. Ini tools untuk:\n- Proses emosi\n- Cari clarity\n- Track growth\n\n## 3 Cara Mulai\n\n### 1. Morning Pages (3 halaman stream of consciousness)\nTulis apapun yang ada di kepala. Tidak perlu rapi.\n\n### 2. Evening Reflection (5 pertanyaan)\n- Apa yang saya syukuri hari ini?\n- Apa yang saya pelajari?\n- Apa yang bisa saya lakukan lebih baik?\n- Bagaimana perasaan saya sekarang?\n- Apa fokus untuk besok?\n\n### 3. Gratitude List\nTulis 3 hal yang membuat Anda bersyukur. Detail.\n\n---\n\nSelamat mencoba! ✨",
                'price' => 0, 'status' => 'published', 'type' => 'blog',
                'meta' => ['preview_length' => null, 'is_paywalled' => false],
            ],

            // Eko's products (dev)
            [
                'user_id' => $eko->id, 'title' => 'Laravel Starter Template',
                'description' => "Boilerplate Laravel dengan auth, payments, dan admin panel.\n\n## Stack\n- Laravel 11\n- Tailwind 4\n- SQLite (dev) / MySQL (prod)\n- Duitku payment gateway\n\n## Features\n- Multi-auth (creator + buyer)\n- Product CRUD dengan 7 modul\n- Order management + payouts\n- Voucher system\n- Email notifications",
                'price' => 299000, 'status' => 'published', 'type' => 'digital',
                'meta' => ['file_format' => 'ZIP', 'file_size' => '45 MB'],
            ],
            [
                'user_id' => $eko->id, 'title' => 'Full-Stack Web Dev Bootcamp',
                'description' => "Kelas 12 minggu, Laravel + React + deployment.\n\n**Untuk siapa**: pemula sampai intermediate yang mau production-ready skills.\n\n**Include**:\n- 60+ video lessons\n- 12 weekly projects\n- Code review\n- 1-on-1 mentorship session (1x)",
                'price' => 1999000, 'compare_at_price' => 2999000, 'status' => 'published', 'type' => 'course',
                'meta' => [
                    'duration_minutes' => 1800, 'modules_count' => 12, 'level' => 'intermediate',
                    'video_preview_url' => 'https://www.youtube.com/watch?v=Ke90Tje7VS0',
                    'curriculum' => [
                        ['title' => 'Week 1: PHP & Laravel Fundamentals', 'duration' => 180],
                        ['title' => 'Week 2: Database Design & Eloquent', 'duration' => 180],
                        ['title' => 'Week 3: Auth & Authorization', 'duration' => 180],
                        ['title' => 'Week 4: REST API Design', 'duration' => 180],
                        ['title' => 'Week 5: React Fundamentals', 'duration' => 180],
                        ['title' => 'Week 6: API Integration', 'duration' => 180],
                        ['title' => 'Week 7: State Management', 'duration' => 180],
                        ['title' => 'Week 8: Real-time Features', 'duration' => 180],
                        ['title' => 'Week 9: Payment Integration', 'duration' => 180],
                        ['title' => 'Week 10: Testing & QA', 'duration' => 180],
                        ['title' => 'Week 11: DevOps & Deployment', 'duration' => 180],
                        ['title' => 'Week 12: Capstone Project', 'duration' => 180],
                    ],
                ],
            ],
            ['user_id' => $eko->id, 'title' => 'Code Review 1:1', 'description' => 'Review code project Anda, 90 menit.', 'price' => 500000, 'status' => 'published', 'type' => 'appointment', 'meta' => ['duration_minutes' => 90, 'location_type' => 'online', 'buffer_minutes' => 15]],
            ['user_id' => $eko->id, 'title' => 'Corporate Training (per batch)', 'description' => 'Training in-house untuk tim developer perusahaan.', 'price' => 25000000, 'status' => 'published', 'type' => 'appointment', 'meta' => ['duration_minutes' => 480, 'location_type' => 'in_person', 'buffer_minutes' => 60]],
            [
                'user_id' => $eko->id, 'title' => 'Donasi: Open Source Indonesia',
                'description' => 'Donasi untuk mendukung saya membuat konten open source gratis (tutorial, starter templates, dan library Indonesia).',
                'price' => 50000, 'status' => 'published', 'type' => 'donation',
                'meta' => ['goal' => 10000000, 'preset_amounts' => [50000, 100000, 500000, 1000000]],
            ],
            [
                'user_id' => $eko->id, 'title' => 'Sticker Pack: Lynk Dev Edition',
                'description' => "Sticker vinyl berkualitas tinggi untuk laptop Anda. Limited edition.\n\nBerisi 5 sticker:\n- 🐘 Laravel logo\n- ⚛️ React logo\n- 🐍 Python logo\n- 🦀 Rust logo\n- 💜 Lynk.id clone",
                'price' => 75000, 'status' => 'published', 'type' => 'physical',
                'meta' => ['stock_quantity' => 50, 'weight_grams' => 80, 'dimensions' => '15×10cm', 'ships_from' => 'Jakarta'],
            ],
        ];

        foreach ($products as $prodData) {
            $meta = $prodData['meta'] ?? [];
            unset($prodData['meta']);
            $product = Product::create(array_merge($prodData, [
                'id' => Product::generateId(),
                'slug' => Str::slug($prodData['title']),
                'metadata' => $meta,
            ]));
        }

        // Add some sample orders (paid)
        $aliceSunset = $alice->products()->where('slug', 'sunset-lightroom-preset-pack')->first();
        $bobBudget = $bob->products()->where('slug', 'budget-tracker-spreadsheet-excel')->first();
        $dianaCourse = Product::where('slug', 'belajar-menulis-cerpen')->first();
        $ekoCourse = Product::where('slug', 'full-stack-web-dev-bootcamp')->first();
        $aliceDonation = $alice->products()->where('slug', 'coffee-chat-buy-me-a-coffee')->first();
        $charlieWebinar = Product::where('slug', 'webinar-build-better-habits')->first();
        $bobWebinar = Product::where('slug', 'webinar-financial-freedom-untuk-milenial')->first();

        // Add modules to course products (for testing course player)
        if ($dianaCourse) {
            $dianaModules = [
                ['title' => 'Pengenalan & Mindset Penulis', 'description' => 'Mengapa menulis itu penting dan bagaimana memulai dengan benar.', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_minutes' => 15, 'position' => 0, 'is_free_preview' => true],
                ['title' => 'Anatomi Cerpen: Premis & Premise', 'description' => 'Membangun fondasi cerita yang kuat.', 'video_url' => 'https://www.youtube.com/watch?v=Ke90Tje7VS0', 'duration_minutes' => 22, 'position' => 1],
                ['title' => 'Karakter yang Hidup', 'description' => 'Teknik membuat karakter yang memorable.', 'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4', 'duration_minutes' => 28, 'position' => 2],
                ['title' => 'Plot, Konflik, Klimaks', 'description' => 'Struktur naratif yang membuat pembaca terpaku.', 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'duration_minutes' => 25, 'position' => 3],
                ['title' => 'Dialog & Voice', 'description' => 'Cara menulis dialog yang natural.', 'video_url' => 'https://www.youtube.com/watch?v=Ke90Tje7VS0', 'duration_minutes' => 18, 'position' => 4],
                ['title' => 'Revision & Editing', 'description' => 'Membedah naskah sendiri untuk hasil terbaik.', 'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4', 'duration_minutes' => 20, 'position' => 5],
            ];
            foreach ($dianaModules as $m) {
                CourseModule::create(array_merge($m, ['product_id' => $dianaCourse->id, 'is_published' => true]));
            }
        }

        $ekoCourse = Product::where('slug', 'full-stack-web-dev-bootcamp')->first();
        if ($ekoCourse) {
            $ekoModules = [
                ['title' => 'Week 1: PHP & Laravel Fundamentals', 'description' => 'Setup, routes, controllers, views.', 'video_url' => 'https://www.youtube.com/watch?v=ImtZ5yENswo', 'duration_minutes' => 95, 'position' => 0, 'is_free_preview' => true],
                ['title' => 'Week 2: Database Design & Eloquent', 'description' => 'Migrations, models, relationships.', 'video_url' => 'https://www.youtube.com/watch?v=EUjf0JxxLCc', 'duration_minutes' => 110, 'position' => 1],
            ];
            foreach ($ekoModules as $m) {
                CourseModule::create(array_merge($m, ['product_id' => $ekoCourse->id, 'is_published' => true]));
            }
        }

        if ($aliceSunset) {
            Order::create([
                'id' => Order::generateId(),
                'buyer_email' => 'buyer1@example.com',
                'product_id' => $aliceSunset->id,
                'creator_user_id' => $alice->id,
                'unit_price' => $aliceSunset->price,
                'quantity' => 1,
                'subtotal' => $aliceSunset->price,
                'fee_pct' => $alice->transaction_fee_pct,
                'fee_amount' => $aliceSunset->price * ($alice->transaction_fee_pct / 100),
                'total' => $aliceSunset->price,
                'creator_payout' => $aliceSunset->price * (1 - $alice->transaction_fee_pct / 100),
                'payment_status' => 'paid',
                'payment_method' => 'duitku_gopay',
                'paid_at' => now()->subDays(2),
            ]);
            Order::create([
                'id' => Order::generateId(),
                'buyer_email' => 'buyer2@example.com',
                'product_id' => $aliceSunset->id,
                'creator_user_id' => $alice->id,
                'unit_price' => $aliceSunset->price,
                'quantity' => 1,
                'subtotal' => $aliceSunset->price,
                'fee_pct' => $alice->transaction_fee_pct,
                'fee_amount' => $aliceSunset->price * ($alice->transaction_fee_pct / 100),
                'total' => $aliceSunset->price,
                'creator_payout' => $aliceSunset->price * (1 - $alice->transaction_fee_pct / 100),
                'payment_status' => 'paid',
                'payment_method' => 'duitku_va_bca',
                'paid_at' => now()->subHours(5),
            ]);
            $aliceSunset->increment('sales_count', 2);
            $alice->increment('balance', $aliceSunset->price * 2 * (1 - $alice->transaction_fee_pct / 100));
            $alice->increment('total_earnings', $aliceSunset->price * 2 * (1 - $alice->transaction_fee_pct / 100));
        }

        if ($bobBudget) {
            Order::create([
                'id' => Order::generateId(),
                'buyer_email' => 'buyer3@example.com',
                'product_id' => $bobBudget->id,
                'creator_user_id' => $bob->id,
                'unit_price' => $bobBudget->price,
                'quantity' => 1,
                'subtotal' => $bobBudget->price,
                'fee_pct' => $bob->transaction_fee_pct,
                'fee_amount' => $bobBudget->price * ($bob->transaction_fee_pct / 100),
                'total' => $bobBudget->price,
                'creator_payout' => $bobBudget->price * (1 - $bob->transaction_fee_pct / 100),
                'payment_status' => 'paid',
                'payment_method' => 'duitku_qris',
                'paid_at' => now()->subDay(),
            ]);
            $bobBudget->increment('sales_count');
            $bob->increment('balance', $bobBudget->price * (1 - $bob->transaction_fee_pct / 100));
            $bob->increment('total_earnings', $bobBudget->price * (1 - $bob->transaction_fee_pct / 100));
        }

        // Course purchase (for testing course player)
        if ($dianaCourse) {
            Order::create([
                'id' => Order::generateId(),
                'buyer_email' => 'student@example.com',
                'product_id' => $dianaCourse->id,
                'creator_user_id' => $diana->id,
                'unit_price' => $dianaCourse->price,
                'quantity' => 1,
                'subtotal' => $dianaCourse->price,
                'fee_pct' => $diana->transaction_fee_pct,
                'fee_amount' => $dianaCourse->price * ($diana->transaction_fee_pct / 100),
                'total' => $dianaCourse->price,
                'creator_payout' => $dianaCourse->price * (1 - $diana->transaction_fee_pct / 100),
                'payment_status' => 'paid',
                'payment_method' => 'duitku_va',
                'paid_at' => now()->subDays(3),
            ]);
            $dianaCourse->increment('sales_count');
            $diana->increment('balance', $dianaCourse->price * (1 - $diana->transaction_fee_pct / 100));
            $diana->increment('total_earnings', $dianaCourse->price * (1 - $diana->transaction_fee_pct / 100));
        }

        // Donation order (for testing)
        if ($aliceDonation) {
            Order::create([
                'id' => Order::generateId(),
                'buyer_email' => 'supporter@example.com',
                'product_id' => $aliceDonation->id,
                'creator_user_id' => $alice->id,
                'unit_price' => 50000,
                'quantity' => 1,
                'subtotal' => 50000,
                'fee_pct' => $alice->transaction_fee_pct,
                'fee_amount' => 50000 * ($alice->transaction_fee_pct / 100),
                'total' => 50000,
                'creator_payout' => 50000 * (1 - $alice->transaction_fee_pct / 100),
                'payment_status' => 'paid',
                'payment_method' => 'duitku_qris',
                'paid_at' => now()->subHours(2),
                'metadata' => ['message' => 'Keep up the great work! Love your presets ☕', 'donor_name' => 'Anonim'],
            ]);
            $aliceDonation->increment('sales_count');
            $alice->increment('balance', 50000 * (1 - $alice->transaction_fee_pct / 100));
            $alice->increment('total_earnings', 50000 * (1 - $alice->transaction_fee_pct / 100));
        }

        // Event orders (for testing ticket flow)
        if ($charlieWebinar) {
            for ($i = 0; $i < 3; $i++) {
                $order = Order::create([
                    'id' => Order::generateId(),
                    'buyer_email' => 'attendee'.($i + 1).'@example.com',
                    'product_id' => $charlieWebinar->id,
                    'creator_user_id' => $charlie->id,
                    'unit_price' => $charlieWebinar->price,
                    'quantity' => 1,
                    'subtotal' => $charlieWebinar->price,
                    'fee_pct' => $charlie->transaction_fee_pct,
                    'fee_amount' => $charlieWebinar->price * ($charlie->transaction_fee_pct / 100),
                    'total' => $charlieWebinar->price,
                    'creator_payout' => $charlieWebinar->price * (1 - $charlie->transaction_fee_pct / 100),
                    'payment_status' => 'paid',
                    'payment_method' => 'duitku_qris',
                    'paid_at' => now()->subDays(rand(1, 5)),
                    'metadata' => ['attendee_name' => 'Participant '.($i + 1)],
                ]);
                EventTicket::create([
                    'order_id' => $order->id,
                    'product_id' => $charlieWebinar->id,
                    'buyer_email' => $order->buyer_email,
                    'attendee_name' => 'Participant '.($i + 1),
                    'ticket_code' => EventTicket::generateCode(),
                    'is_checked_in' => $i === 0, // First one already checked in
                    'checked_in_at' => $i === 0 ? now()->subHour() : null,
                    'checked_in_by' => $i === 0 ? $charlie->name : null,
                ]);
                $charlieWebinar->increment('sales_count');
                $charlie->increment('balance', $charlieWebinar->price * (1 - $charlie->transaction_fee_pct / 100));
                $charlie->increment('total_earnings', $charlieWebinar->price * (1 - $charlie->transaction_fee_pct / 100));
            }
        }

        $ekoSticker = Product::where('slug', 'sticker-pack-lynk-dev-edition')->first();
        if ($ekoSticker) {
            $order = Order::create([
                'id' => Order::generateId(),
                'buyer_email' => 'buyer.physical@example.com',
                'product_id' => $ekoSticker->id,
                'creator_user_id' => $eko->id,
                'unit_price' => $ekoSticker->price,
                'quantity' => 2,
                'subtotal' => $ekoSticker->price * 2,
                'fee_pct' => $eko->transaction_fee_pct,
                'fee_amount' => $ekoSticker->price * 2 * ($eko->transaction_fee_pct / 100),
                'total' => $ekoSticker->price * 2,
                'creator_payout' => $ekoSticker->price * 2 * (1 - $eko->transaction_fee_pct / 100),
                'payment_status' => 'paid',
                'payment_method' => 'duitku_va',
                'paid_at' => now()->subDay(),
                'metadata' => [
                    'shipping_address' => [
                        'name' => 'Budi Santoso',
                        'phone' => '081234567890',
                        'address' => 'Jl. Sudirman No. 45, RT 003/RW 005',
                        'city' => 'Bandung',
                        'province' => 'Jawa Barat',
                        'postal_code' => '40115',
                        'country' => 'Indonesia',
                        'notes' => 'Tolong packing bubble wrap ya, ini untuk hadiah.',
                    ],
                    'shipping_cost' => 15000,
                    'shipping_status' => 'packed',
                ],
            ]);
            $ekoSticker->increment('sales_count', 2);
            $eko->increment('balance', $ekoSticker->price * 2 * (1 - $eko->transaction_fee_pct / 100));
            $eko->increment('total_earnings', $ekoSticker->price * 2 * (1 - $eko->transaction_fee_pct / 100));
        }

        $this->command->info('✅ Seeded '.count($creators).' creators with '.count($products).' products and '.Order::count().' orders');

        // Add sample vouchers
        Voucher::create([
            'creator_user_id' => $alice->id,
            'code' => 'WELCOME10',
            'type' => 'percent',
            'value' => 10,
            'min_purchase' => 50000,
            'max_discount' => 25000,
            'usage_limit' => 100,
            'is_active' => true,
        ]);
        Voucher::create([
            'creator_user_id' => $bob->id,
            'code' => 'HEMAT20K',
            'type' => 'fixed',
            'value' => 20000,
            'min_purchase' => 100000,
            'usage_limit' => 50,
            'is_active' => true,
        ]);

        $this->command->info('✅ Seeded '.Voucher::count().' vouchers');
    }
}
