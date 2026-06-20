<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket — {{ $product->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .ticket-card { box-shadow: none !important; border: 2px solid #2AB57D !important; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-brand-50 to-ink-50 min-h-screen">
    <div class="max-w-md mx-auto px-4 py-10">
        {{-- Success header --}}
        <div class="text-center mb-6">
            <div class="inline-flex w-16 h-16 rounded-full bg-brand-500 text-white items-center justify-center text-3xl mb-3">✓</div>
            <h1 class="text-2xl font-black">You're going!</h1>
            <p class="text-sm text-ink-500">Simpan tiket ini. Tunjukkan di pintu masuk.</p>
        </div>

        {{-- Ticket --}}
        <div class="ticket-card bg-white rounded-2xl overflow-hidden shadow-xl">
            {{-- Top: event info --}}
            <div class="bg-ink-900 text-white p-6">
                <div class="text-xs uppercase opacity-70 mb-1">{{ $product->typeIcon }} {{ $product->typeLabel }}</div>
                <h2 class="text-xl font-black mb-2">{{ $product->title }}</h2>
                @if ($product->eventDate)
                    <div class="flex items-center gap-2 text-sm">
                        <span>📅</span>
                        <span>{{ \Carbon\Carbon::parse($product->eventDate)->format('D, d M Y · H:i') }} WIB</span>
                    </div>
                @endif
                @if ($product->meta('event_location'))
                    <div class="flex items-center gap-2 text-sm mt-1">
                        <span>📍</span>
                        <span>{{ $product->meta('event_location') }}</span>
                    </div>
                @endif
            </div>

            {{-- Middle: attendee + QR --}}
            <div class="p-6">
                <div class="mb-4">
                    <div class="text-xs text-ink-500 uppercase font-bold mb-1">Attendee</div>
                    <div class="font-bold">{{ $ticket->attendee_name ?? 'Anonymous' }}</div>
                    <div class="text-xs text-ink-500">{{ $ticket->buyer_email }}</div>
                </div>

                {{-- Ticket code + QR --}}
                <div class="border-2 border-dashed border-ink-200 rounded-xl p-4 text-center">
                    <div class="text-xs text-ink-500 uppercase font-bold mb-2">Ticket Code</div>
                    <div class="font-mono font-black text-3xl text-brand-700 tracking-wider">{{ $ticket->ticket_code }}</div>
                    <div class="mt-3 inline-block p-2 bg-white">
                        {!! QrCode::size(160)->generate($ticket->ticket_code) !!}
                    </div>
                    <div class="mt-3 text-xs text-ink-500">Tunjukkan kode ini atau scan QR saat check-in</div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-ink-50 rounded-lg p-3">
                        <div class="text-ink-500 uppercase font-bold text-[10px]">Order ID</div>
                        <div class="font-mono mt-1">{{ $order->id }}</div>
                    </div>
                    <div class="bg-ink-50 rounded-lg p-3">
                        <div class="text-ink-500 uppercase font-bold text-[10px]">Paid at</div>
                        <div class="mt-1">{{ $order->paid_at->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Bottom: organizer --}}
            <div class="border-t border-ink-100 p-4 bg-ink-50 flex items-center gap-3">
                <img src="{{ $product->owner->avatar_url }}" class="w-10 h-10 rounded-full">
                <div class="flex-1 min-w-0">
                    <div class="text-xs text-ink-500">Hosted by</div>
                    <div class="font-bold text-sm truncate">{{ '@' . $product->owner->username }}</div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 flex gap-2 no-print">
            <button onclick="window.print()" class="btn-secondary flex-1">🖨️ Print</button>
            <a href="{{ $product->url }}" class="btn-primary flex-1">View Event Page</a>
        </div>

        <div class="mt-4 text-center text-xs text-ink-500 no-print">
            Butuh bantuan? Hubungi {{ '@' . $product->owner->username }}
        </div>
    </div>
</body>
</html>