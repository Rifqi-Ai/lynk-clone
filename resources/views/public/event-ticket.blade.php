@extends('layouts.app')

@section('title', 'Tiket — ' . $product->title)
@section('description', 'Ticket for ' . $product->title)
    @push('head')
        <meta name="robots" content="noindex, nofollow">
        <style>
            @media print {
                body { background: white !important; }
                .no-print { display: none !important; }
                .ticket-card { box-shadow: none !important; border: 2px solid #FF6B35 !important; }
                .ticket-header { background: #FF6B35 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
    @endpush

@section('content')
    <section class="max-w-md mx-auto px-4 py-8 sm:py-12">
        {{-- Success header --}}
        <div class="text-center mb-6">
            <div class="inline-flex w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white items-center justify-center mb-4 shadow-cta animate-bounce-once">
                <x-heroicon-o-check class="w-10 h-10" />
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-success/10 border border-success/20 text-success text-xs font-bold uppercase tracking-wider mb-3">
                <x-heroicon-o-sparkles class="w-3.5 h-3.5" /> Confirmed
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-balance">You're going!</h1>
            <p class="mt-2 text-ink-600 text-sm">Simpan tiket ini. Tunjukkan di pintu masuk.</p>
        </div>

        {{-- Ticket --}}
        <div class="ticket-card card-warm overflow-hidden shadow-card-hover">
            {{-- Header: event info --}}
            <div class="ticket-header bg-gradient-to-br from-brand-500 to-brand-700 text-white p-6">
                <div class="text-xs uppercase font-bold opacity-80 mb-1 tracking-wider flex items-center gap-1.5">
                    <x-heroicon-o-calendar-days class="w-3.5 h-3.5" /> Event Ticket
                </div>
                <h2 class="text-xl sm:text-2xl font-black mb-3 text-balance">{{ $product->title }}</h2>

                @if ($product->eventDate)
                    <div class="flex items-center gap-2 text-sm bg-white/10 rounded-lg p-2 mb-1.5">
                        <x-heroicon-o-calendar class="w-4 h-4 flex-shrink-0" />
                        <span class="font-semibold">{{ \Carbon\Carbon::parse($product->eventDate)->format('D, d M Y · H:i') }} WIB</span>
                    </div>
                @endif
                @if ($product->meta('event_location'))
                    <div class="flex items-center gap-2 text-sm bg-white/10 rounded-lg p-2">
                        <x-heroicon-o-map-pin class="w-4 h-4 flex-shrink-0" />
                        <span class="font-semibold">{{ $product->meta('event_location') }}</span>
                    </div>
                @endif
            </div>

            {{-- Middle: attendee + QR --}}
            <div class="p-6">
                <div class="mb-5 pb-5 border-b border-dashed border-ink-200">
                    <div class="text-[10px] text-ink-500 uppercase font-black mb-1 tracking-wider">Attendee</div>
                    <div class="font-bold text-base text-ink-900">{{ $ticket->attendee_name ?? 'Anonymous' }}</div>
                    <div class="text-xs text-ink-500 mt-0.5">{{ $ticket->buyer_email }}</div>
                </div>

                {{-- Ticket code + QR --}}
                <div class="rounded-2xl border-2 border-dashed border-brand-300 bg-gradient-to-br from-brand-50/50 to-amber-50/30 p-5 text-center">
                    <div class="text-[10px] text-ink-500 uppercase font-black mb-2 tracking-wider">Ticket Code</div>
                    <div class="font-mono font-black text-2xl sm:text-3xl text-gradient-brand tracking-wider mb-3">
                        {{ $ticket->ticket_code }}
                    </div>
                    <div class="inline-block p-3 bg-white rounded-xl ring-1 ring-ink-200">
                        {!! QrCode::size(160)->generate($ticket->ticket_code) !!}
                    </div>
                    <div class="mt-3 text-xs text-ink-600">
                        Scan QR atau ketik kode ini saat check-in
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-ink-50 rounded-xl p-3">
                        <div class="text-ink-500 uppercase font-black text-[10px] mb-1">Order ID</div>
                        <div class="font-mono mt-0.5 font-semibold text-ink-700 truncate">{{ $order->id }}</div>
                    </div>
                    <div class="bg-ink-50 rounded-xl p-3">
                        <div class="text-ink-500 uppercase font-black text-[10px] mb-1">Paid at</div>
                        <div class="mt-0.5 font-semibold text-ink-700">{{ $order->paid_at->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Bottom: organizer --}}
            <div class="border-t border-ink-100 p-4 bg-gradient-to-r from-ink-50 to-white flex items-center gap-3">
                <img src="{{ $product->owner->avatar_url }}" alt="" class="w-10 h-10 rounded-full ring-2 ring-white shadow-sm">
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] text-ink-500 uppercase font-bold tracking-wider">Hosted by</div>
                    <a href="{{ $product->owner->profile_url }}" class="font-bold text-sm text-ink-900 hover:text-brand-600 transition-colors truncate block">
                        {{ '@' . $product->owner->username }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 flex gap-3 no-print">
            <button onclick="window.print()" class="btn-secondary flex-1">
                <x-heroicon-o-printer class="w-4 h-4" /> Print
            </button>
            <a href="{{ $product->url }}" class="btn-primary flex-1">
                View Event
                <x-heroicon-o-arrow-right class="w-4 h-4" />
            </a>
        </div>

        {{-- Help --}}
        <div class="mt-6 no-print text-center text-xs text-ink-500 flex items-center justify-center gap-1.5">
            <x-heroicon-o-question-mark-circle class="w-4 h-4" />
            Butuh bantuan? Hubungi <a href="{{ $product->owner->profile_url }}" class="font-bold text-brand-600 hover:underline">{{ '@' . $product->owner->username }}</a>
        </div>
    </section>
@endsection