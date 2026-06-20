@extends('layouts.dashboard')

@section('title', 'New Product')
@section('header', 'What do you want to sell?')
@section('subheader', 'Choose a product type to get started. You can always edit details later.')

@section('content')
<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @foreach (\App\Models\Product::TYPES as $typeKey => $info)
        <a href="{{ route('dashboard.products.create', ['type' => $typeKey]) }}"
           class="card-warm p-6 text-left group hover:shadow-card-hover hover:-translate-y-0.5 transition-all">
            <div class="w-14 h-14 rounded-xl {{ $info['color'] }} flex items-center justify-center text-3xl mb-3 group-hover:scale-110 transition-transform">
                {{ $info['icon'] }}
            </div>
            <h3 class="font-black text-lg text-ink-900 group-hover:text-brand-600 transition-colors">{{ $info['label'] }}</h3>
            <p class="text-sm text-ink-600 mt-1.5 leading-relaxed">
                @switch($typeKey)
                    @case('digital') PDF, presets, templates, software — any file. @break
                    @case('donation') Accept support from your fans with custom amounts. @break
                    @case('appointment') Paid calendar booking for coaching or consultation. @break
                    @case('event') Sell tickets to online events, webinars, workshops. @break
                    @case('course') Upload video lessons and sell course access. @break
                    @case('blog') Publish posts, free or behind a paywall. @break
                    @case('physical') Sell physical products that need shipping. @break
                @endswitch
            </p>
            <div class="mt-4 text-sm font-bold text-brand-600 inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                Get started
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </div>
        </a>
    @endforeach
</div>

<div class="mt-8 text-center">
    <a href="{{ route('dashboard.products.index') }}" class="btn-ghost-ink">
        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to products
    </a>
</div>
@endsection