@extends('layouts.app')

@section('title', 'Pricing')
@section('content')
<section class="py-16 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-black">Simple, fair pricing</h1>
            <p class="mt-3 text-ink-500">Start free. Upgrade when you grow.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            @php
                $plans = [
                    ['name' => 'Starter', 'price' => 'Free', 'fee' => '10% transaction fee', 'features' => ['Unlimited links', 'Digital store', 'Statistics', 'Templates', 'Custom fonts/buttons', 'E-course up to 10 min'], 'cta' => 'Start Free', 'highlight' => false],
                    ['name' => 'Pro', 'price' => 'Rp 99K', 'period' => '/month', 'fee' => '5% transaction fee', 'features' => ['Everything in Starter', 'Custom domain', 'Remove branding', 'Facebook Pixel + GA', 'UTM parameters', 'E-course up to 480 min', '20 GB storage', 'WhatsApp notifications'], 'cta' => 'Go Pro', 'highlight' => true],
                    ['name' => 'Brandpreneur', 'price' => 'Custom', 'fee' => 'Up to 0% transaction fee', 'features' => ['Everything in Pro', 'Custom pages', 'Custom email domain', 'Free consultation', 'Negotiable fee'], 'cta' => 'Contact Sales', 'highlight' => false],
                ];
            @endphp
            @foreach ($plans as $plan)
                <div class="card {{ $plan['highlight'] ? 'border-brand-500 border-2 shadow-xl relative' : '' }} p-8">
                    @if ($plan['highlight'])
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand-500 text-white px-4 py-1 rounded-full text-xs font-black uppercase">Popular</div>
                    @endif
                    <h2 class="font-black text-xl">{{ $plan['name'] }}</h2>
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-4xl font-black">{{ $plan['price'] }}</span>
                        @if (!empty($plan['period']))<span class="text-ink-500 text-sm">{{ $plan['period'] }}</span>@endif
                    </div>
                    <div class="text-sm text-brand-500 font-bold mt-1">{{ $plan['fee'] }}</div>
                    <ul class="mt-6 space-y-2 text-sm text-ink-700">
                        @foreach ($plan['features'] as $f)
                            <li class="flex gap-2"><svg class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>{{ $f }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="btn {{ $plan['highlight'] ? 'btn-primary' : 'btn-secondary' }} btn-block mt-8">{{ $plan['cta'] }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection