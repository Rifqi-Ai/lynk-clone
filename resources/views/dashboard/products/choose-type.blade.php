@extends('layouts.dashboard')

@section('title', 'New Product')
@section('header', 'What do you want to sell?')
@section('subheader', 'Choose a product type to get started.')

@section('content')
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach (\App\Models\Product::TYPES as $typeKey => $info)
        <a href="{{ route('dashboard.products.create', ['type' => $typeKey]) }}"
           class="card card-hover p-6 text-left group">
            <div class="w-14 h-14 rounded-xl {{ $info['color'] }} flex items-center justify-center text-3xl mb-3 group-hover:scale-110 transition">
                {{ $info['icon'] }}
            </div>
            <h3 class="font-black text-lg">{{ $info['label'] }}</h3>
            <p class="text-sm text-ink-500 mt-1 leading-relaxed">
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
            <div class="mt-4 text-sm font-bold text-brand-500 flex items-center gap-1">
                Get started
                <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
    @endforeach
</div>

<div class="mt-8 text-center">
    <a href="{{ route('dashboard.products.index') }}" class="btn-ghost">← Back to products</a>
</div>
@endsection