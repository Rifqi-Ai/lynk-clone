@extends('layouts.dashboard')

@section('title', $product->exists ? 'Edit ' . $product->typeLabel : 'New Product')
@section('header', $product->exists ? 'Edit ' . $product->typeLabel : 'New ' . ($type === 'digital' ? 'Digital Product' : \App\Models\Product::TYPES[$type]['label'] ?? 'Product'))
@section('subheader', $product->exists ? 'ID: ' . $product->id . ' · ' . $product->typeLabel : 'Create a new ' . (\App\Models\Product::TYPES[$type]['label'] ?? 'product') . ' on your page')

@section('content')
@php
    $typeInfo = \App\Models\Product::TYPES[$type] ?? ['label' => 'Product', 'icon' => '📦', 'color' => 'bg-ink-100'];
@endphp

<form method="POST" action="{{ $product->exists ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}"
      enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($product->exists) @method('PATCH') @endif
    <input type="hidden" name="type" value="{{ $type }}">

    {{-- Type badge --}}
    <div class="card-warm p-5 bg-gradient-to-r from-brand-50 via-amber-50 to-brand-50 border-brand-200 flex items-center gap-4">
        <div class="w-14 h-14 rounded-xl {{ $typeInfo['color'] }} flex items-center justify-center text-2xl shadow-sm flex-shrink-0">
            {{ $typeInfo['icon'] }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-black text-base text-ink-900">{{ $typeInfo['label'] }}</div>
            <div class="text-xs text-ink-600 mt-0.5">{{ match($type) {
                'digital' => 'Sell files like PDFs, presets, templates, software.',
                'donation' => 'Accept support from fans. Buyers choose amount.',
                'appointment' => 'Paid calendar booking for coaching or consultation.',
                'event' => 'Sell tickets to online events, webinars, workshops.',
                'course' => 'Upload video lessons and sell course access.',
                'blog' => 'Publish posts, free or behind a paywall.',
                'physical' => 'Sell physical products that need shipping.',
                default => 'Create a new product on your page.'
            } }}</div>
        </div>
        @if (!$product->exists)
        <a href="{{ route('dashboard.products.create') }}" class="btn-ghost-ink btn-sm flex-shrink-0">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Change type
        </a>
        @endif
    </div>

    {{-- Basic Info --}}
    <div class="card-warm p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-ink-100">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
            </div>
            <h2 class="font-black text-lg text-ink-900">Basic Info</h2>
        </div>

        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="title">Title <span class="text-danger">*</span></label>
            <input id="title" name="title" type="text" required value="{{ old('title', $product->title) }}"
                   class="input input-bordered w-full @error('title') input-error @enderror" placeholder="e.g. My Amazing E-Book">
            @error('title')<div class="mt-1.5 text-xs text-danger font-semibold">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="description">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="textarea textarea-bordered w-full @error('description') textarea-error @enderror"
                      placeholder="What buyers will get...">{{ old('description', $product->description) }}</textarea>
            @error('description')<div class="mt-1.5 text-xs text-danger font-semibold">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="block text-sm font-bold text-ink-900 mb-1.5" for="thumbnail">Thumbnail</label>
            @if ($product->thumbnail_url)
                <div class="mb-3 flex items-center gap-3">
                    <img src="{{ $product->thumbnail_url }}" class="w-24 h-24 rounded-xl object-cover ring-2 ring-ink-200" alt="Current thumbnail">
                    <span class="text-xs text-ink-500">Current image. Upload a new one to replace.</span>
                </div>
            @endif
            <input id="thumbnail" name="thumbnail" type="file" accept="image/*"
                   class="file-input file-input-bordered w-full">
            <div class="mt-1.5 text-xs text-ink-500">JPG/PNG, max 2MB. Recommended 1:1 or 16:9.</div>
        </div>
    </div>

    {{-- Pricing (skip for donation) --}}
    @if ($type !== 'donation')
    <div class="card-warm p-6 space-y-5">
        <div class="flex items-center gap-2 pb-3 border-b border-ink-100">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
            </div>
            <h2 class="font-black text-lg text-ink-900">Pricing</h2>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-ink-900 mb-1.5" for="price">Price (IDR) <span class="text-danger">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-ink-500">Rp</span>
                    <input id="price" name="price" type="number" required min="0" step="1000"
                           value="{{ old('price', $product->price ?: 0) }}" class="input input-bordered w-full pl-10 @error('price') input-error @enderror">
                </div>
                @error('price')<div class="mt-1.5 text-xs text-danger font-semibold">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-ink-900 mb-1.5" for="compare_at_price">Compare-at Price</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-ink-500">Rp</span>
                    <input id="compare_at_price" name="compare_at_price" type="number" min="0" step="1000"
                           value="{{ old('compare_at_price', $product->compare_at_price) }}" class="input input-bordered w-full pl-10">
                </div>
                <div class="mt-1.5 text-xs text-ink-500">Original price shown strikethrough.</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Type-specific fields --}}
    @if ($type === 'donation')
        @include('dashboard.products.partials._donation_fields', ['product' => $product])
    @elseif ($type === 'appointment')
        @include('dashboard.products.partials._appointment_fields', ['product' => $product])
    @elseif ($type === 'event')
        @include('dashboard.products.partials._event_fields', ['product' => $product])
    @elseif ($type === 'course')
        @include('dashboard.products.partials._course_fields', ['product' => $product])
    @elseif ($type === 'blog')
        @include('dashboard.products.partials._blog_fields', ['product' => $product])
    @elseif ($type === 'physical')
        @include('dashboard.products.partials._physical_fields', ['product' => $product])
    @else
        @include('dashboard.products.partials._digital_fields', ['product' => $product])
    @endif

    {{-- Actions --}}
    <div class="card-warm p-5 flex items-center justify-between flex-wrap gap-3">
        @if ($product->exists)
            <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}" onsubmit="return confirm('Delete this {{ strtolower($product->typeLabel) }}? This cannot be undone.')" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-error btn-outline btn-sm">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    Delete
                </button>
            </form>
            <div class="flex items-center gap-2">
                <button type="submit" name="publish" value="0" class="btn-secondary">Save</button>
                @if ($product->status === 'published')
                    <button type="submit" name="unpublish" value="1" class="btn-primary">Unpublish</button>
                @else
                    <button type="submit" name="publish" value="1" class="btn-cta">Publish Now</button>
                @endif
            </div>
        @else
            <a href="{{ route('dashboard.products.index') }}" class="btn-ghost-ink">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Cancel
            </a>
            <div class="flex items-center gap-2">
                <button type="submit" name="publish" value="0" class="btn-secondary">Save as Draft</button>
                <button type="submit" name="publish" value="1" class="btn-cta">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    Publish Now
                </button>
            </div>
        @endif
    </div>
</form>
@endsection