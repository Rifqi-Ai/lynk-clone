<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart Checkout — {{ '@' . $creator->username }}</title>
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

            <h1 class="text-2xl font-black text-center mb-6">🛒 Cart Checkout</h1>

            {{-- Cart items --}}
            <div class="card p-5 mb-4">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-ink-100">
                    <div class="font-bold">{{ $cart->items->count() }} item(s) in cart</div>
                    <a href="{{ route('cart.show', $creator->username) }}" class="text-xs text-brand-500 hover:underline">← Edit cart</a>
                </div>

                <div class="space-y-3">
                    @foreach ($cart->items as $item)
                        @php $p = $item->product; @endphp
                        <div class="flex items-center gap-3">
                            @if ($p->thumbnail_url)
                                <img src="{{ $p->thumbnail_url }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-brand-50 flex items-center justify-center text-xl">{{ $p->typeIcon }}</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm truncate">{{ $p->title }}</div>
                                <div class="text-xs text-ink-500">{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                            </div>
                            <div class="font-bold text-sm">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Voucher --}}
            @if ($cart->voucher_id)
                <div class="card p-4 mb-4 flex items-center justify-between">
                    <div class="text-sm">
                        <span class="font-bold">🎟️ Voucher applied</span>
                        <span class="text-ink-500">— saved Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</span>
                    </div>
                    <form action="{{ route('cart.voucher.remove', $creator->username) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="text-xs text-danger hover:underline">Remove</button>
                    </form>
                </div>
            @endif

            {{-- Totals --}}
            <div class="card p-5 mb-4">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-500">Subtotal</span>
                        <span>Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ($cart->voucher_discount > 0)
                        <div class="flex justify-between text-brand-600">
                            <span>Voucher discount</span>
                            <span>− Rp {{ number_format($cart->voucher_discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-ink-100 text-lg font-black">
                        <span>Total</span>
                        <span class="text-brand-500">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Buyer info + payment --}}
            <form action="{{ route('cart.process', $creator->username) }}" method="POST" class="card p-5 space-y-4">
                @csrf

                <div>
                    <label class="label" for="payer_email">Email untuk konfirmasi *</label>
                    <input id="payer_email" name="payer_email" type="email" required
                           value="{{ old('payer_email', auth()->user()?->email) }}"
                           class="input @error('payer_email') input-error @enderror">
                    @error('payer_email')
                        <div class="text-xs text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                @if (auth()->check())
                    <div class="text-xs text-ink-500">
                        Logged in sebagai <span class="font-bold">{{ '@' . auth()->user()->username }}</span>
                    </div>
                @else
                    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2 text-xs">
                        💡 <a href="{{ route('login') }}" class="font-bold underline">Login</a> untuk akses lebih cepat ke produk yang dibeli.
                    </div>
                @endif

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    💳 Bayar Rp {{ number_format($cart->total, 0, ',', '.') }}
                </button>

                <div class="text-center text-xs text-ink-500">
                    Pembayaran aman via Duitku · QRIS, VA, E-Wallet
                </div>
            </form>
        </div>
    </section>
</body>
</html>