@component('mail::message')
# Halo! Terima kasih sudah order 🎉

Order Anda untuk **{{ $product->title }}** sudah dikonfirmasi dan pembayaran diterima.

@if($product->type === 'digital')
## 📥 Download File Anda

Klik tombol di bawah untuk download file yang Anda beli:

@component('mail::button', ['url' => $product->file_url ?? url('/storage/'.$product->file_path), 'color' => 'green'])
Download {{ $product->meta('file_format', 'File') }}
@endcomponent

@if($product->meta('file_size'))
_File size: {{ $product->meta('file_size') }}_
@endif

@elseif($product->type === 'course')
## 🎓 Akses Course Anda

Course Anda sudah siap! Klik tombol di bawah untuk mulai belajar:

@component('mail::button', ['url' => route('course.show', [$creator->username, $product->id]) . '?token=' . \App\Http\Controllers\CourseController::generateAccessToken($order), 'color' => 'green'])
📚 Mulai Belajar
@endcomponent

Akses selamanya. Belajar dengan节奏 Anda sendiri.

@elseif($product->type === 'event')
## 🎟️ Tiket Anda

@php
    $ticket = \App\Models\EventTicket::where('order_id', $order->id)->first();
@endphp

Tunjukkan tiket ini saat check-in:

@if($ticket)
**Ticket Code:** `{{ $ticket->ticket_code }}`

@component('mail::button', ['url' => route('event.ticket', [$creator->username, $product->id, $order->id]), 'color' => 'green'])
🎟️ Lihat Tiket + QR Code
@endcomponent
@endif

**Event:** {{ $product->title }}
@if($product->eventDate)
**Date:** {{ \Carbon\Carbon::parse($product->eventDate)->format('D, d M Y · H:i') }} WIB
@endif
@if($product->meta('event_location'))
**Location:** {{ $product->meta('event_location') }}
@endif

@elseif($product->type === 'appointment')
## 📅 Appointment Details

Topik Anda:
**Date:** {{ data_get($order->metadata, 'appointment_date', 'TBD') }}
**Time:** {{ data_get($order->metadata, 'appointment_time', 'TBD') }} WIB
**Duration:** {{ $product->durationFormatted }}

{{ $creator->name }} akan menghubungi Anda via email/WhatsApp untuk konfirmasi meeting link.

@elseif($product->type === 'donation')
## ☕ Terima kasih!

Donasi Anda sangat berarti. {{ '@' . $creator->username }} akan sangat terbantu.

@if(data_get($order->metadata, 'donor_message'))
> _{{ data_get($order->metadata, 'donor_message') }}_
@endif

@elseif($product->type === 'physical')
## 📦 Konfirmasi Pesanan

Pesanan Anda sedang diproses. Berikut detail pengiriman:

@php $ship = data_get($order->metadata, 'shipping_address', []); @endphp

**Nama:** {{ $ship['name'] ?? '-' }}
**Phone:** {{ $ship['phone'] ?? '-' }}
**Alamat:** {{ $ship['address'] ?? '-' }}, {{ $ship['city'] ?? '' }}, {{ $ship['province'] ?? '' }} {{ $ship['postal_code'] ?? '' }}

{{ $creator->name }} akan update tracking number setelah paket dikirim.

@elseif($product->type === 'blog')
## 📖 Baca Sekarang

Postingan Anda sudah terbuka:

@component('mail::button', ['url' => url('/' . $creator->username . '/' . $product->id), 'color' => 'green'])
📖 Baca Sekarang
@endcomponent

@endif

---

## 📋 Order Summary

**Order ID:** `{{ $order->id }}`
**Tanggal:** {{ $order->paid_at->format('d M Y, H:i') }}
**Total:** Rp {{ number_format($order->total, 0, ',', '.') }}
**Payment Method:** {{ strtoupper(str_replace('_', ' ', $order->payment_method ?? 'duitku')) }}

@if($product->type !== 'donation')
**Creator:** [{{ '@' . $creator->username }}]({{ url('/' . $creator->username) }})
@endif

@component('mail::subcopy')
Jika ada pertanyaan, hubungi {{ '@' . $creator->username }} via WhatsApp atau email.

Powering your creator journey 💚  
**{{ config('app.name') }}**
@endcomponent

@endcomponent