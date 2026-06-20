@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="max-w-md mx-auto py-16 px-4 text-center">
    <div class="card p-8">
        <div class="w-20 h-20 rounded-full bg-red-100 mx-auto flex items-center justify-center text-4xl">⚠️</div>
        <h1 class="text-2xl font-black mt-4">Payment Failed</h1>
        <p class="text-sm text-ink-500 mt-2">We couldn't process your payment.</p>

        <div class="mt-6 p-4 bg-ink-50 rounded-xl text-left text-sm">
            <div class="flex justify-between mb-2">
                <span class="text-ink-500">Order ID</span>
                <span class="font-mono font-bold text-xs">{{ $order->id }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-ink-500">Product</span>
                <span class="font-bold truncate ml-2">{{ $order->product->title }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-ink-500">Amount</span>
                <span class="font-bold">{{ $order->formatted_total }}</span>
            </div>
        </div>

        <div class="mt-6 text-xs text-ink-500">
            No charges were made. You can try again or contact the creator for help.
        </div>

        <a href="{{ route('checkout.show', [$order->creator->username, $order->product->id]) }}" class="btn-primary btn-block mt-6">Try Again</a>
        <a href="{{ url('/') }}" class="btn-ghost btn-block mt-2">Back to home</a>
    </div>
</div>
@endsection