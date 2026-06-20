<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart — {{ '@' . $creator->username }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 antialiased">
    <section class="py-10 px-4 min-h-screen">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-4">
                <a href="{{ $creator->profile_url }}" class="inline-flex items-center gap-1 text-xs text-ink-500 hover:text-brand-500">
                    ← {{ '@' . $creator->username }}
                </a>
            </div>

            <h1 class="text-2xl font-black text-center mb-6">Your Cart</h1>

            @if (session('success'))
                <div class="rounded-lg bg-brand-100 border border-brand-500/30 text-brand-800 px-4 py-3 mb-4">✅ {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 border border-danger/30 text-danger px-4 py-3 mb-4">⚠️ {{ session('error') }}</div>
            @endif

            @if ($cart->items->isEmpty())
                <div class="card p-12 text-center">
                    <div class="text-5xl mb-3">🛒</div>
                    <h3 class="font-black text-lg">Your cart is empty</h3>
                    <a href="{{ $creator->profile_url }}" class="btn-primary mt-4">Browse products</a>
                </div>
            @else
                {{-- Items --}}
                <div class="card divide-y divide-ink-100 mb-4">
                    @foreach ($cart->items as $item)
                        <div class="p-4 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg {{ \App\Models\Product::TYPES[$item->product->type]['color'] ?? 'bg-ink-100' }} flex items-center justify-center text-xl flex-shrink-0">
                                {{ $item->product->typeIcon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $item->product->url }}" class="font-bold text-sm hover:text-brand-500 truncate block">{{ $item->product->title }}</a>
                                <div class="text-xs text-ink-500">{{ $item->product->typeLabel }} · Rp {{ number_format($item->unit_price, 0, ',', '.') }} each</div>
                            </div>
                            <form method="POST" action="{{ route('cart.update', [$creator->username, $item->product->id]) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="10"
                                       class="input w-16 text-center" onchange="this.form.submit()">
                            </form>
                            <div class="text-right">
                                <div class="font-black text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                                <form method="POST" action="{{ route('cart.remove', [$creator->username, $item->product->id]) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-danger hover:underline">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Voucher --}}
                <div class="card p-4 mb-4">
                    @if ($cart->voucher)
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="badge-success">{{ $cart->voucher->code }}</span>
                                <span class="text-sm ml-2">-Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</span>
                            </div>
                            <form method="POST" action="{{ route('cart.voucher.remove', $creator->username) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-danger hover:underline">Remove</button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('cart.voucher.apply', $creator->username) }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="voucher_code" placeholder="Enter voucher code"
                                   class="input flex-1 @error('voucher_code') input-error @enderror"
                                   value="{{ old('voucher_code') }}">
                            <button type="submit" class="btn-secondary">Apply</button>
                        </form>
                        @error('voucher_code')<div class="error">{{ $message }}</div>@enderror
                    @endif
                </div>

                {{-- Totals --}}
                <div class="card p-5 mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-ink-500">Subtotal</span>
                        <span class="font-bold">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ($cart->voucher_discount > 0)
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-ink-500">Voucher ({{ $cart->voucher->code }})</span>
                            <span class="font-bold text-brand-500">-Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center pt-3 border-t border-ink-100">
                        <span class="font-black">Total</span>
                        <span class="text-2xl font-black text-brand-500">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('cart.checkout', $creator->username) }}" class="btn-primary btn-lg btn-block">
                    Proceed to Checkout
                </a>

                <div class="text-center mt-4">
                    <a href="{{ $creator->profile_url }}" class="text-sm text-ink-500 hover:text-brand-500">← Continue shopping</a>
                </div>
            @endif
        </div>
    </section>
</body>
</html>