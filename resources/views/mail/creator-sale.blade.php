@component('mail::message')
# 💰 Penjualan Baru!

Anda mendapat order baru dari **{{ $order->buyer_email }}**.

## Order Details

**Produk:** {{ $product->title }}
**Order ID:** `{{ $order->id }}`
**Tanggal:** {{ $order->paid_at->format('d M Y, H:i') }}
**Quantity:** {{ $order->quantity }}x
**Total Bayar:** Rp {{ number_format($order->total, 0, ',', '.') }}

@php $fee = $order->fee_pct; @endphp
**Platform Fee ({{ $fee }}%):** -Rp {{ number_format($order->fee_amount, 0, ',', '.') }}

@component('mail::panel')
## 🎯 Payout Anda: **Rp {{ number_format($order->creator_payout, 0, ',', '.') }}**
@endcomponent

Saldo sudah ditambahkan ke wallet Anda. Total earnings sekarang: **Rp {{ number_format($creator->total_earnings ?? 0, 0, ',', '.') }}**

@if($product->type === 'event')
@php $ticket = \App\Models\EventTicket::where('order_id', $order->id)->first(); @endphp
@if($ticket)
**Tiket baru:** `{{ $ticket->ticket_code }}` ({{ $ticket->attendee_name ?? $ticket->buyer_email }})
Siap untuk check-in di [dashboard](/dashboard/events/{{ $creator->username }}/{{ $product->id }}/checkin).
@endif

@elseif($product->type === 'appointment')
@php
    $date = data_get($order->metadata, 'appointment_date');
    $time = data_get($order->metadata, 'appointment_time');
@endphp
**Appointment:** {{ $date }} jam {{ $time }} WIB
Buyer akan menunggu konfirmasi meeting link dari Anda.

@elseif($product->type === 'physical')
@php $ship = data_get($order->metadata, 'shipping_address', []); @endphp
## 📦 Segera Packing!

Buyer: **{{ $ship['name'] ?? '-' }}** ({{ $ship['phone'] ?? '-' }})
Alamat: {{ $ship['address'] ?? '-' }}, {{ $ship['city'] ?? '' }} {{ $ship['postal_code'] ?? '' }}

[Update status pengiriman](/dashboard/fulfillment/{{ $order->id }})

@elseif($product->type === 'donation')
@if(data_get($order->metadata, 'donor_message'))
> _{{ data_get($order->metadata, 'donor_message') }}_
>
> — {{ data_get($order->metadata, 'donor_name', 'Anonim') }}
@endif

@endif

@component('mail::button', ['url' => url('/dashboard'), 'color' => 'green'])
Buka Dashboard
@endcomponent

@component('mail::subcopy')
Powering your creator journey 💚  
**{{ config('app.name') }}**
@endcomponent

@endcomponent