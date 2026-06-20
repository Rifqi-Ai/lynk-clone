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
    <div class="card p-4 bg-gradient-to-r from-brand-50 to-brand-100/40 border-brand-200 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl {{ $typeInfo['color'] }} flex items-center justify-center text-2xl">
            {{ $typeInfo['icon'] }}
        </div>
        <div class="flex-1">
            <div class="font-black">{{ $typeInfo['label'] }}</div>
            <div class="text-xs text-ink-500">{{ match($type) {
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
        <a href="{{ route('dashboard.products.create') }}" class="btn-ghost btn-sm">← Change type</a>
        @endif
    </div>

    {{-- Basic Info --}}
    <div class="card p-6 space-y-4">
        <h2 class="font-black text-lg">Basic Info</h2>
        <div>
            <label class="label" for="title">Title *</label>
            <input id="title" name="title" type="text" required value="{{ old('title', $product->title) }}"
                   class="input @error('title') input-error @enderror" placeholder="e.g. My Amazing E-Book">
            @error('title')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="label" for="description">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="input @error('description') input-error @enderror"
                      placeholder="What buyers will get...">{{ old('description', $product->description) }}</textarea>
            @error('description')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="label" for="thumbnail">Thumbnail</label>
            @if ($product->thumbnail_url)
                <img src="{{ $product->thumbnail_url }}" class="w-24 h-24 rounded-lg mb-2 object-cover">
            @endif
            <input id="thumbnail" name="thumbnail" type="file" accept="image/*"
                   class="input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-bold">
            <div class="help">JPG/PNG, max 2MB. Recommended 1:1 or 16:9.</div>
        </div>
    </div>

    {{-- Pricing (skip for donation) --}}
    @if ($type !== 'donation')
    <div class="card p-6 space-y-4">
        <h2 class="font-black text-lg">Pricing</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="label" for="price">Price (IDR) *</label>
                <input id="price" name="price" type="number" required min="0" step="1000"
                       value="{{ old('price', $product->price ?: 0) }}" class="input @error('price') input-error @enderror">
                @error('price')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="label" for="compare_at_price">Compare-at Price</label>
                <input id="compare_at_price" name="compare_at_price" type="number" min="0" step="1000"
                       value="{{ old('compare_at_price', $product->compare_at_price) }}" class="input">
                <div class="help">Original price shown strikethrough.</div>
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
    <div class="flex items-center justify-between">
        @if ($product->exists)
            <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}" onsubmit="return confirm('Delete this {{ strtolower($product->typeLabel) }}?')" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">Delete</button>
            </form>
            <div class="flex items-center gap-2">
                @if ($product->status === 'published')
                    <button type="submit" name="unpublish" value="1" class="btn-secondary">Unpublish</button>
                @else
                    <button type="submit" name="publish" value="1" class="btn-primary">Publish</button>
                @endif
                <button type="submit" name="publish" value="0" class="btn-secondary">Save</button>
            </div>
        @else
            <a href="{{ route('dashboard.products.index') }}" class="btn-ghost">Cancel</a>
            <div class="flex items-center gap-2">
                <button type="submit" name="publish" value="0" class="btn-secondary">Save as Draft</button>
                <button type="submit" name="publish" value="1" class="btn-primary">Publish Now</button>
            </div>
        @endif
    </div>
</form>
@endsection