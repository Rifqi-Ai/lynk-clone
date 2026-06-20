@extends('layouts.app')

@section('title', config('app.name', 'Linka') . ' — Sell Your Knowledge, Build Your Brand')
@section('description', 'Create your page to sell digital products, courses, appointments, and more. Free to start.')

@section('content')
{{-- Hero Section --}}
<section class="relative overflow-hidden">
    {{-- Background pattern --}}
    <div class="absolute inset-0 -z-10" style="background-image: radial-gradient(circle at 1px 1px, #2AB57D 1px, transparent 0); background-size: 24px 24px; opacity: 0.04;"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pt-24 lg:pb-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            {{-- Left: Copy --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-bold mb-6">
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                    Powering the Creator Economy
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-ink-900 leading-tight text-balance">
                    Create your page.
                    <span class="text-brand-500">Sell your knowledge.</span>
                </h1>
                <p class="mt-6 text-lg text-ink-500 leading-relaxed max-w-lg">
                    One link in your bio. Sell digital products, courses, appointments, and services. Start free, upgrade when you grow.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('register') }}" class="btn-primary btn-lg">
                        Start Free
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="#features" class="btn-secondary btn-lg">See features</a>
                </div>
                <div class="mt-8 flex items-center gap-6 text-sm text-ink-500">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                        Free forever plan
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                        No credit card needed
                    </div>
                </div>
            </div>

            {{-- Right: Phone mockup --}}
            <div class="relative lg:pl-12">
                <div class="relative mx-auto" style="max-width: 360px;">
                    {{-- Phone frame --}}
                    <div class="bg-ink-900 rounded-[2.5rem] p-3 shadow-2xl">
                        <div class="bg-white rounded-[2rem] overflow-hidden aspect-[9/19]">
                            {{-- Profile preview --}}
                            <div class="bg-gradient-to-br from-brand-500 to-brand-600 h-36 relative">
                                <div class="absolute -bottom-10 left-1/2 -translate-x-1/2">
                                    <div class="w-20 h-20 rounded-full bg-white p-1 shadow-lg">
                                        <div class="w-full h-full rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-2xl font-black">@</div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-14 px-5 pb-6 space-y-3">
                                <div class="text-center">
                                    <div class="font-black text-base">@yourname</div>
                                    <div class="text-xs text-ink-500 mt-0.5">Creator & Educator</div>
                                </div>
                                <div class="space-y-2 mt-4">
                                    <div class="link-card text-sm">📚 My E-Book — IDR 99K</div>
                                    <div class="link-card text-sm">🎓 Premium Course — IDR 499K</div>
                                    <div class="link-card text-sm">📅 1:1 Coaching — IDR 299K</div>
                                    <div class="link-card text-sm">☕ Buy me a coffee</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Floating card --}}
                    <div class="absolute -left-6 lg:-left-16 top-1/3 bg-white rounded-2xl shadow-xl p-4 border border-ink-100 hidden sm:block animate-fade-in">
                        <div class="text-xs text-ink-500">Today</div>
                        <div class="text-lg font-black text-brand-500">+Rp 1.250.000</div>
                        <div class="text-xs text-ink-700">3 sales</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Trusted by --}}
<section class="bg-ink-50 py-10 border-y border-ink-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center text-xs font-bold uppercase tracking-wider text-ink-500 mb-6">Trusted by creators and brands</div>
        <div class="flex flex-wrap justify-center items-center gap-x-10 gap-y-4 opacity-60">
            <span class="text-xl font-black text-ink-700">Traveloka</span>
            <span class="text-xl font-black text-ink-700">GoPay</span>
            <span class="text-xl font-black text-ink-700">AJAIB</span>
            <span class="text-xl font-black text-ink-700">Asus</span>
            <span class="text-xl font-black text-ink-700">TCL</span>
            <span class="text-xl font-black text-ink-700">Hyundai</span>
        </div>
    </div>
</section>

{{-- Features: 7 modules --}}
<section id="features" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl sm:text-4xl font-black text-ink-900 text-balance">Everything you need to monetize</h2>
            <p class="mt-3 text-ink-500 max-w-2xl mx-auto">Seven powerful modules to sell anything. One simple link.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @php
                $modules = [
                    ['icon' => '📦', 'title' => 'Digital Product', 'desc' => 'Sell ebooks, presets, templates, software — any file.', 'color' => 'bg-brand-100'],
                    ['icon' => '📝', 'title' => 'Blog', 'desc' => 'Publish posts and stories. Free or behind a paywall.', 'color' => 'bg-amber-100'],
                    ['icon' => '📅', 'title' => 'Appointment', 'desc' => 'Paid calendar booking for coaching or fan meet.', 'color' => 'bg-blue-100'],
                    ['icon' => '🎓', 'title' => 'Course', 'desc' => 'Upload video courses and sell access.', 'color' => 'bg-purple-100'],
                    ['icon' => '🎟️', 'title' => 'Event / Webinar', 'desc' => 'Sell tickets to online events.', 'color' => 'bg-pink-100'],
                    ['icon' => '☕', 'title' => 'Donation', 'desc' => 'Accept one-off support from your fans.', 'color' => 'bg-orange-100'],
                    ['icon' => '🛍️', 'title' => 'Store', 'desc' => 'Sell physical products with shipping.', 'color' => 'bg-cyan-100'],
                    ['icon' => '🔗', 'title' => 'Link in Bio', 'desc' => 'One link for all your content & products.', 'color' => 'bg-rose-100'],
                ];
            @endphp

            @foreach ($modules as $mod)
                <div class="card-hover p-6">
                    <div class="w-12 h-12 rounded-xl {{ $mod['color'] }} flex items-center justify-center text-2xl mb-4">
                        {{ $mod['icon'] }}
                    </div>
                    <h3 class="font-black text-ink-900 mb-1.5">{{ $mod['title'] }}</h3>
                    <p class="text-sm text-ink-500 leading-relaxed">{{ $mod['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Pricing --}}
<section id="pricing" class="py-20 bg-gradient-to-b from-white to-brand-50/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl sm:text-4xl font-black text-ink-900">Simple, fair pricing</h2>
            <p class="mt-3 text-ink-500">Start free. Upgrade when you grow.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto items-stretch">
                    @php
                        $plans = [
                            ['name' => 'Starter', 'price' => 'Free', 'fee' => '10% fee', 'desc' => 'For trying out the platform', 'features' => ['Unlimited links', 'Digital store', 'Statistics', 'Templates', 'Custom fonts/buttons', 'E-course up to 10 min'], 'cta' => 'Start Free', 'highlight' => false],
                            ['name' => 'Pro', 'price' => 'Rp 99K', 'period' => '/month', 'fee' => '5% fee', 'desc' => 'For serious creators', 'features' => ['Everything in Starter', 'Custom domain', 'Remove branding', 'Facebook Pixel + GA', 'UTM parameters', 'E-course up to 480 min', '20 GB storage', 'WhatsApp notifications'], 'cta' => 'Go Pro', 'highlight' => true],
                            ['name' => 'Brandpreneur', 'price' => 'Custom', 'fee' => 'Up to 0% fee', 'desc' => 'For businesses and agencies', 'features' => ['Everything in Pro', 'Custom pages', 'Custom email domain', 'Free consultation', 'Negotiable transaction fee'], 'cta' => 'Contact Us', 'highlight' => false],
                        ];
                    @endphp

                    @foreach ($plans as $plan)
                        <div class="card flex flex-col {{ $plan['highlight'] ? 'border-brand-500 border-2 shadow-xl relative' : '' }} p-8">
                            @if ($plan['highlight'])
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand-500 text-white px-4 py-1 rounded-full text-xs font-black uppercase tracking-wider z-10">Most Popular</div>
                            @endif
                            <h3 class="font-black text-xl text-ink-900">{{ $plan['name'] }}</h3>
                            <div class="mt-4 flex items-baseline gap-1">
                                <span class="text-4xl font-black text-ink-900">{{ $plan['price'] }}</span>
                                @if (!empty($plan['period']))
                                    <span class="text-ink-500 text-sm">{{ $plan['period'] }}</span>
                                @endif
                            </div>
                            <div class="text-sm text-brand-500 font-bold mt-1">{{ $plan['fee'] }}</div>
                            <p class="text-sm text-ink-500 mt-3">{{ $plan['desc'] }}</p>
                            <ul class="mt-6 space-y-2.5 text-sm text-ink-700 flex-1">
                                @foreach ($plan['features'] as $feat)
                                    <li class="flex gap-2">
                                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                        {{ $feat }}
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('register') }}" class="btn {{ $plan['highlight'] ? 'btn-primary' : 'btn-secondary' }} btn-block mt-8">
                                {{ $plan['cta'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-gradient-to-br from-brand-500 to-brand-700 rounded-3xl p-12 lg:p-16 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;"></div>
            <div class="relative">
                <h2 class="text-3xl sm:text-4xl font-black text-balance">Ready to start selling?</h2>
                <p class="mt-3 text-brand-100 max-w-xl mx-auto">Join thousands of creators building their business on Linka.</p>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 mt-8 px-8 py-3.5 bg-white text-brand-700 font-black rounded-lg hover:bg-brand-50 hover:scale-[1.02] transition">
                    Create Your Page
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection