# Linka — Creator Storefront Platform

A **link-in-bio + creator storefront** platform for Indonesian creators, built with Laravel 13. Inspired by [lynk.id](https://lynk.id).

Sell digital products, courses, appointments, events, donations, blogs, and physical products — all from one beautiful page.

## ✨ Features

### 7 Product Types
- 📦 **Digital Products** — eBooks, templates, presets, audio files
- 🎓 **Online Courses** — video modules with progress tracking
- 🎟️ **Events / Webinars** — ticket sales with QR check-in
- 📅 **Appointments** — book consultation sessions
- ☕ **Donations** — accept tips with goal tracking
- 📝 **Blog Posts** — paywalled content with markdown
- 🛍️ **Physical Products** — with shipping management

### Platform Features
- 🛒 **Shopping cart + Vouchers** — multi-item checkout
- 💳 **Duitku payment gateway** — QRIS, VA, E-Wallet
- 📊 **Dashboard analytics** — revenue/sales charts (Chart.js)
- 📧 **Email notifications** — OrderConfirmation + CreatorSaleNotification
- 📱 **WhatsApp notifications** — Wablas/Fonnte integration
- 🔍 **Search & filters** — by type, sort, keyword
- 🔒 **Auth + Security** — rate limiting, signed tokens, HMAC verification
- 📱 **Mobile responsive** — hamburger menu, mobile drawer
- 🚀 **SEO ready** — JSON-LD, sitemap.xml, robots.txt, OG tags, Twitter cards

## 🛠️ Tech Stack

- **Backend:** Laravel 13, PHP 8.3
- **Database:** SQLite (dev) / MySQL/PostgreSQL (prod)
- **Frontend:** Tailwind 4, Vanilla JS, Chart.js
- **Payment:** Duitku (mock mode in dev)
- **Email:** Log driver (dev) / SMTP (prod)
- **QR Code:** simplesoftwareio/simple-qrcode
- **Markdown:** league/commonmark

## 🚀 Quick Start

See [SETUP.md](SETUP.md) for complete installation guide.

```bash
# 1. Clone
git clone <your-repo-url> linka
cd linka

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Setup database
php artisan migrate --seed

# 5. Build assets
npm run build

# 6. Start server
php artisan serve
# → http://127.0.0.1:8000
```

## 🧪 Demo Accounts

After running `php artisan migrate --seed`, login with:

| Username | Email | Password | Tier |
|---|---|---|---|
| `alice@test.com` | `alice@demo.linka.id` | `password123` | Pro |
| `demo_alice` | `alice@demo.linka.id` | `password123` | Pro |
| `demo_bob` | `bob@demo.linka.id` | `password123` | Pro |
| `demo_charlie` | `charlie@demo.linka.id` | `password123` | Starter |
| `demo_diana` | `diana@demo.linka.id` | `password123` | Starter |
| `demo_eko` | `eko@demo.linka.id` | `password123` | Pro |

**Demo data:** 24 products (all 7 types), 8 paid orders, 3 event tickets, 6+2 course modules, 2 vouchers (`WELCOME10`, `HEMAT20K`).

## 📁 Project Structure

```
lynk-clone/
├── app/
│   ├── Http/Controllers/    # 10 controllers
│   ├── Mail/                # 2 mailables
│   ├── Models/              # 8 models
│   └── Services/            # DuitkuService, WhatsAppService
├── database/
│   ├── migrations/          # 14 migrations
│   └── seeders/             # DemoSeeder
├── public/
│   ├── robots.txt
│   └── ...
├── resources/
│   ├── css/                 # Tailwind 4
│   ├── js/                  # Vite entry
│   └── views/               # 39 Blade views
│       ├── auth/            # login, register
│       ├── components/      # reusable
│       ├── dashboard/       # creator dashboard
│       ├── layouts/         # app, dashboard
│       ├── mail/            # email templates
│       ├── pages/           # marketing pages
│       ├── payment/         # success, failed
│       └── public/          # public profile, product, cart, etc.
├── routes/web.php
├── SETUP.md                 # detailed setup guide
└── README.md                # this file
```

## 🧰 Architecture Decisions

- **Polymorphic products** — single `products` table with `type` enum + JSON `metadata` column (instead of 7 separate tables). Simplifies queries + relations.
- **Course video** — uses YouTube/Vimeo embeds (URL in metadata). Zero infra cost, no Cloudflare Stream needed.
- **Event tickets** — auto-generated `TKT-XXXXXX` codes on payment success.
- **QR codes** — server-side SVG via `SimpleSoftwareIO\QrCode`.
- **Paywall** — client-side preview snippet + server-side access check via HMAC token.
- **Donation goal** — computed live from `paidOrders()` (no metadata drift).

## 📊 Performance

- Dashboard: 8 queries (with revenue + sales + top products + type breakdown)
- Profile page: 3-5 queries (with eager loading)
- No N+1 queries in views

## 🔐 Security Features

- CSRF protection (Laravel built-in + VerifyCsrfToken)
- Rate limiting on login (5 attempts/60s) + checkout
- Course access via HMAC token (no `?email=` query param)
- Authorization checks in all dashboard controllers
- Cryptographically secure ID generation (`random_int`)
- SQL injection protection (Eloquent ORM + parameter binding)
- XSS protection (auto-escaped `{{ }}`, only `Str::markdown()` is unescaped — safe by design)

## 📝 License

MIT — for educational purposes.

## 🙏 Credits

- Inspired by [lynk.id](https://lynk.id) (Indonesian creator platform)
- Built with [Laravel](https://laravel.com)
- Icons: emoji
- Fonts: [Lato](https://fonts.google.com/specimen/Lato)

---

**Made with 💚 for creators.**
