@extends('layouts.dashboard')

@section('title', 'Check-in: ' . $product->title)
@section('header', 'Event Check-in')
@section('subheader', $product->title)

@section('content')
{{-- Breadcrumb --}}
<div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
    <a href="{{ route('dashboard.products.index') }}" class="text-xs font-semibold text-ink-500 hover:text-brand-600 inline-flex items-center gap-1 transition-colors">
        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back to products
    </a>
    <a href="{{ $product->url }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">
        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
        View Public Page
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
    <div class="card-warm p-4 sm:p-5 text-center hover:shadow-card transition-shadow">
        <div class="text-xs text-ink-500 uppercase font-bold tracking-wider">Total</div>
        <div class="text-3xl sm:text-4xl font-black mt-2 text-ink-900">{{ $stats['total'] }}</div>
        <div class="text-xs text-ink-500 mt-1">tickets sold</div>
    </div>
    <div class="card-warm p-4 sm:p-5 text-center bg-gradient-to-br from-success/5 to-emerald-50 hover:shadow-card transition-shadow">
        <div class="text-xs text-ink-500 uppercase font-bold tracking-wider">Checked In</div>
        <div class="text-3xl sm:text-4xl font-black mt-2 text-success">{{ $stats['checked_in'] }}</div>
        @if ($stats['total'] > 0)
            <div class="text-xs text-success/80 mt-1 font-bold">{{ round(($stats['checked_in'] / $stats['total']) * 100) }}% attended</div>
        @endif
    </div>
    <div class="card-warm p-4 sm:p-5 text-center hover:shadow-card transition-shadow">
        <div class="text-xs text-ink-500 uppercase font-bold tracking-wider">Pending</div>
        <div class="text-3xl sm:text-4xl font-black mt-2 text-ink-700">{{ $stats['pending'] }}</div>
        <div class="text-xs text-ink-500 mt-1">not yet arrived</div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success mb-6">
        <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-error mb-6">
        <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Check-in form --}}
    <div class="space-y-4">
        <div class="card-warm p-5 sm:p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
                <h2 class="font-black text-base text-ink-900">Quick Check-in</h2>
            </div>
            <form method="POST" action="{{ route('event.checkin.post', [$product->owner->username, $product->id]) }}">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="ticket_code" required autofocus
                           placeholder="TKT-XXXXXX"
                           class="input input-bordered flex-1 font-mono uppercase tracking-wider">
                    <button type="submit" class="btn-cta flex-shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Check in
                    </button>
                </div>
                <p class="mt-2 text-xs text-ink-500 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/></svg>
                    Masukkan kode tiket atau scan QR. Auto-uppercase.
                </p>
            </form>
        </div>

        <div class="card-warm p-5 sm:p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                </div>
                <h2 class="font-black text-base text-ink-900">Add Walk-in</h2>
            </div>
            <form method="POST" action="{{ route('event.walkin', [$product->owner->username, $product->id]) }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <input type="text" name="attendee_name" required placeholder="Nama lengkap" class="input input-bordered w-full">
                    </div>
                    <div>
                        <input type="email" name="attendee_email" required placeholder="Email" class="input input-bordered w-full">
                    </div>
                    <div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-ink-500">Rp</span>
                            <input type="number" name="amount" required min="0" placeholder="Amount paid" class="input input-bordered w-full pl-10">
                        </div>
                    </div>
                    <button type="submit" class="btn-secondary w-full">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Buat tiket + catat cash
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Attendee list --}}
    <div class="card-warm overflow-hidden">
        <div class="px-5 py-4 border-b border-ink-100 flex items-center justify-between bg-gradient-to-r from-ink-50/50 to-white">
            <h2 class="font-black text-base text-ink-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                Attendees
            </h2>
            <span class="badge badge-soft">{{ $tickets->count() }} total</span>
        </div>

        <div class="max-h-[600px] overflow-y-auto">
            @forelse ($tickets as $ticket)
                <div class="px-4 py-3 border-b border-ink-100 last:border-b-0 {{ $ticket->is_checked_in ? 'bg-gradient-to-r from-success/5 to-emerald-50/30' : '' }} transition-colors hover:bg-ink-50/50">
                    <div class="flex items-start gap-3">
                        {{-- Check-in indicator --}}
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                            {{ $ticket->is_checked_in ? 'bg-success text-white' : 'bg-ink-100 text-ink-400' }}">
                            @if ($ticket->is_checked_in)
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            @else
                                <span class="text-xs font-bold">⏱</span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm text-ink-900 truncate">{{ $ticket->attendee_name ?? 'Anonymous' }}</div>
                            <div class="text-xs text-ink-500 truncate">{{ $ticket->buyer_email }}</div>
                            <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs font-bold {{ $ticket->is_checked_in ? 'text-success' : 'text-ink-700' }}">
                                    {{ $ticket->ticket_code }}
                                </span>
                                @if ($ticket->is_checked_in)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-success">
                                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ $ticket->checked_in_at->format('H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 4.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                    </div>
                    <h3 class="font-black text-base text-ink-900">Belum ada attendee</h3>
                    <p class="text-sm text-ink-500 mt-1 max-w-xs mx-auto">Tiket akan dibuat otomatis setelah order paid.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection