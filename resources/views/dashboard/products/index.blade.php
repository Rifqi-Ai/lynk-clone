@extends('layouts.dashboard')

@section('title', 'Products')
@section('header', 'Products')
@section('subheader', 'Kelola semua produk yang kamu jual.')

@section('actions')
    <a href="{{ route('dashboard.products.create') }}" class="btn-cta text-sm h-10">
        <x-heroicon-o-plus class="w-4 h-4" />
        Produk Baru
    </a>
@endsection

@section('content')

@if ($products->isEmpty())
    <div class="bg-white rounded-2xl border border-ink-200 p-12 md:p-16 text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-5">
            <x-heroicon-o-cube class="w-8 h-8" />
        </div>
        <h3 class="text-xl font-bold text-ink-900">Belum ada produk</h3>
        <p class="text-sm text-ink-500 mt-1 max-w-sm mx-auto">Buat produk pertama kamu untuk mulai jualan di halaman Linka.</p>
        <a href="{{ route('dashboard.products.create') }}" class="btn-cta mt-6">
            <x-heroicon-o-plus class="w-4 h-4" />
            Buat Produk Pertama
        </a>
    </div>
@else
    <div class="bg-white rounded-2xl border border-ink-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-ink-50 text-xs font-bold text-ink-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Product</th>
                        <th class="px-3 py-3.5 text-left">Type</th>
                        <th class="px-3 py-3.5 text-right">Price</th>
                        <th class="px-3 py-3.5 text-right">Sales</th>
                        <th class="px-3 py-3.5 text-left">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($products as $product)
                        @php
                            $typeInfo = \App\Models\Product::TYPES[$product->type] ?? ['color' => 'bg-ink-100', 'label' => $product->type];
                            $colorMap = ['brand' => 'brand', 'blue' => 'blue', 'purple' => 'purple', 'amber' => 'amber', 'pink' => 'pink', 'rose' => 'rose', 'emerald' => 'emerald', 'cyan' => 'cyan'];
                            $colorKey = $colorMap[$typeInfo['color']] ?? 'ink';
                        @endphp
                        <tr class="hover:bg-ink-50/50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 flex-shrink-0 rounded-xl overflow-hidden bg-{{ $colorKey }}-100 flex items-center justify-center text-{{ $colorKey }}-600">
                                        @if ($product->thumbnail_url)
                                            <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-cover">
                                        @else
                                            @php
                                                $iconMap = [
                                                    'digital' => 'arrow-down-tray',
                                                    'course' => 'academic-cap',
                                                    'event' => 'calendar-days',
                                                    'appointment' => 'clock',
                                                    'donation' => 'heart',
                                                    'blog' => 'newspaper',
                                                    'physical' => 'gift',
                                                ];
                                                $icon = $iconMap[$product->type] ?? 'cube';
                                            @endphp
                                            <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5" />
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ $product->url }}" target="_blank" class="font-semibold text-ink-900 truncate block hover:text-brand-600 transition-colors">{{ $product->title }}</a>
                                        <div class="text-xs text-ink-400 font-mono truncate">{{ substr($product->id, 0, 8) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $colorKey }}-50 text-{{ $colorKey }}-700">
                                    {{ $typeInfo['label'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-ink-900 whitespace-nowrap">
                                @if ($product->type === 'donation')
                                    <span class="text-ink-500 font-normal text-xs">~</span>Rp {{ number_format($product->price, 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                <span class="font-semibold text-ink-900">{{ $product->sales_count }}</span>
                            </td>
                            <td class="px-3 py-3">
                                @if ($product->status === 'published')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-success/10 text-success uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span> Live
                                    </span>
                                @elseif ($product->status === 'draft')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-ink-100 text-ink-700 uppercase tracking-wider">Draft</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-ink-100 text-ink-700 uppercase tracking-wider">{{ ucfirst($product->status) }}</span>
                                @endif
                                @if ($product->is_featured)
                                    <span class="ml-1 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700 uppercase tracking-wider">
                                        <x-heroicon-s-star class="w-2.5 h-2.5" /> Featured
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ $product->url }}" target="_blank" class="p-2 text-ink-500 hover:text-brand-600 transition-colors" title="View">
                                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                                    </a>
                                    <a href="{{ route('dashboard.products.edit', $product) }}" class="p-2 text-ink-500 hover:text-brand-600 transition-colors" title="Edit">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endif
@endsection
