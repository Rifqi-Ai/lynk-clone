@extends('layouts.dashboard')

@section('title', 'Check-in: ' . $product->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <a href="{{ route('dashboard.products.index') }}" class="text-xs text-ink-500 hover:text-brand-500">← Back to products</a>
            <h1 class="text-2xl font-black mt-1">{{ $product->title }}</h1>
            <p class="text-sm text-ink-500">Event check-in dashboard</p>
        </div>
        <a href="{{ $product->url }}" target="_blank" class="btn-secondary btn-sm">View Public Page →</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="card p-4 text-center">
            <div class="text-xs text-ink-500 uppercase font-bold">Total</div>
            <div class="text-2xl font-black mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="card p-4 text-center bg-brand-50">
            <div class="text-xs text-ink-500 uppercase font-bold">Checked In</div>
            <div class="text-2xl font-black mt-1 text-brand-700">{{ $stats['checked_in'] }}</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-xs text-ink-500 uppercase font-bold">Pending</div>
            <div class="text-2xl font-black mt-1 text-ink-700">{{ $stats['pending'] }}</div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-brand-50 border border-brand-200 rounded-lg text-sm text-brand-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Check-in form --}}
        <div class="card p-6">
            <h2 class="font-bold mb-4">🔍 Quick Check-in</h2>
            <form method="POST" action="{{ route('event.checkin.post', [$product->owner->username, $product->id]) }}">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="ticket_code" required autofocus
                           placeholder="TKT-XXXXXX"
                           class="input flex-1 font-mono uppercase">
                    <button type="submit" class="btn-primary">Check in</button>
                </div>
                <p class="mt-2 text-xs text-ink-500">Masukkan kode tiket dari attendee. Auto-uppercase.</p>
            </form>

            <hr class="my-6">

            <h3 class="font-bold text-sm mb-3">➕ Add Walk-in</h3>
            <form method="POST" action="{{ route('event.walkin', [$product->owner->username, $product->id]) }}">
                @csrf
                <div class="space-y-2">
                    <input type="text" name="attendee_name" required placeholder="Nama lengkap" class="input w-full">
                    <input type="email" name="attendee_email" required placeholder="Email" class="input w-full">
                    <input type="number" name="amount" required min="0" placeholder="Amount paid (Rp)" class="input w-full">
                    <button type="submit" class="btn-secondary btn-block">Buat tiket + catat cash</button>
                </div>
            </form>
        </div>

        {{-- Attendee list --}}
        <div class="card overflow-hidden">
            <div class="px-4 py-3 border-b border-ink-100 flex items-center justify-between">
                <h2 class="font-bold">Attendees ({{ $tickets->count() }})</h2>
            </div>
            <div class="max-h-[600px] overflow-y-auto">
                @forelse ($tickets as $ticket)
                    <div class="px-4 py-3 border-b border-ink-100 {{ $ticket->is_checked_in ? 'bg-brand-50/50' : '' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm truncate">{{ $ticket->attendee_name ?? 'Anonymous' }}</div>
                                <div class="text-xs text-ink-500 truncate">{{ $ticket->buyer_email }}</div>
                                <div class="text-xs mt-1 font-mono {{ $ticket->is_checked_in ? 'text-brand-700' : 'text-ink-700' }}">
                                    {{ $ticket->ticket_code }}
                                    @if ($ticket->is_checked_in)
                                        <span class="ml-2 text-[10px] text-brand-600">✓ {{ $ticket->checked_in_at->format('H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-ink-500">
                        Belum ada attendee. Tiket akan dibuat otomatis setelah order paid.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection