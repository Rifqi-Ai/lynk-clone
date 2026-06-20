@extends('layouts.dashboard')

@section('title', 'Products')
@section('header', 'Products')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-ink-500">{{ $products->total() }} total · {{ $products->where('status', 'published')->count() }} live</p>
    <a href="{{ route('dashboard.products.create') }}" class="btn-primary">+ New Product</a>
</div>

@if ($products->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">📦</div>
        <h3 class="font-black text-lg">No products yet</h3>
        <p class="text-sm text-ink-500 mt-1">Create your first product to start selling on your page.</p>
        <a href="{{ route('dashboard.products.create') }}" class="btn-primary mt-6">+ Create Product</a>
    </div>
@else
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-ink-50 text-xs font-bold text-ink-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Price</th>
                    <th class="px-5 py-3 text-left">Sales</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($products as $product)
                    <tr class="hover:bg-ink-50/50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg {{ \App\Models\Product::TYPES[$product->type]['color'] ?? 'bg-ink-100' }} overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if ($product->thumbnail_url)
                                        <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xl">{{ $product->typeIcon }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold truncate">{{ $product->title }}</div>
                                    <div class="text-xs text-ink-500 font-mono truncate">{{ $product->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs">{{ $product->typeIcon }} {{ $product->typeLabel }}</span>
                        </td>
                        <td class="px-5 py-3 font-bold">
                            @if ($product->type === 'donation')
                                <span class="text-ink-500">~Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            @else
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-5 py-3">{{ $product->sales_count }}</td>
                        <td class="px-5 py-3">
                            @if ($product->status === 'published')<span class="badge-success">Live</span>
                            @elseif ($product->status === 'draft')<span class="badge-neutral">Draft</span>
                            @else<span class="badge-neutral">{{ ucfirst($product->status) }}</span>@endif
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ $product->url }}" target="_blank" class="btn-ghost btn-sm">View</a>
                            <a href="{{ route('dashboard.products.edit', $product) }}" class="btn-ghost btn-sm">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endif
@endsection