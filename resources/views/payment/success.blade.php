@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="max-w-md mx-auto py-16 px-4 text-center">
    <div class="card p-8">
        <div class="w-20 h-20 rounded-full bg-brand-100 mx-auto flex items-center justify-center text-4xl">✓</div>
        <h1 class="text-2xl font-black mt-4">Payment Successful</h1>
        <p class="text-sm text-ink-500 mt-2">Thank you for your purchase!</p>

        <div class="mt-6 p-4 bg-ink-50 rounded-xl text-left text-sm">
            <div class="flex justify-between mb-2">
                <span class="text-ink-500">Order ID</span>
                <span class="font-mono font-bold text-xs">{{ $order->id }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-ink-500">Product</span>
                <span class="font-bold truncate ml-2">{{ $order->product->title }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-ink-500">Email</span>
                <span class="font-bold truncate ml-2">{{ $order->buyer_email }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-ink-200">
                <span class="text-ink-500">Total Paid</span>
                <span class="font-black text-brand-500">{{ $order->formatted_total }}</span>
            </div>
        </div>

        <p class="text-xs text-ink-500 mt-6">
            We've sent the download link to <strong>{{ $order->buyer_email }}</strong>.
            If you don't see it in a few minutes, check your spam folder.
        </p>

        <a href="{{ url('/') }}" class="btn-secondary btn-block mt-6">Back to home</a>
    </div>
</div>
@endsection