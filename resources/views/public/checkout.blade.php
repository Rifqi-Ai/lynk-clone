<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout — {{ $product->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 antialiased">
    <section class="py-10 px-4 min-h-screen">
        <div class="max-w-md mx-auto">
            <div class="text-center mb-4">
                <a href="{{ $creator->profile_url }}" class="inline-flex items-center gap-1 text-xs text-ink-500 hover:text-brand-500">
                    ← {{ '@' . $creator->username }}
                </a>
            </div>

            <h1 class="text-2xl font-black text-center mb-6">Checkout</h1>

            {{-- Order summary --}}
            <div class="card p-5 mb-4">
                <div class="flex items-center gap-3">
                    @if ($product->thumbnail_url)
                        <img src="{{ $product->thumbnail_url }}" class="w-14 h-14 rounded-lg object-cover">
                    @else
                        <div class="w-14 h-14 rounded-lg bg-brand-50 flex items-center justify-center text-2xl">{{ $product->typeIcon }}</div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="font-bold truncate">{{ $product->title }}</div>
                        <div class="text-xs text-ink-500">{{ $product->typeLabel }} · {{ '@' . $creator->username }}</div>
                    </div>
                </div>

                {{-- Donation: amount picker --}}
                @if ($product->type === 'donation')
                    <div class="mt-4 pt-4 border-t border-ink-100">
                        <label class="label">Choose amount</label>
                        <div class="grid grid-cols-3 gap-2">
                            @php $first = true; @endphp
                            @foreach ($product->donationPresets as $amount)
                                <button type="button" class="btn {{ $first ? 'btn-primary' : 'btn-secondary' }} btn-sm preset-btn" data-amount="{{ $amount }}">
                                    Rp {{ number_format($amount / 1000, 0) }}K
                                </button>
                                @php $first = false; @endphp
                            @endforeach
                        </div>
                        @if ($product->meta('allow_custom'))
                            <input id="custom_amount" name="custom_amount" type="number" min="1000" placeholder="Custom amount"
                                   class="input mt-2 @error('amount') input-error @enderror">
                            <div class="help">Minimum Rp 1.000</div>
                        @endif
                        @if ($product->donationGoal)
                            <div class="mt-3 text-xs text-ink-500">
                                🎯 Goal: Rp {{ number_format($product->donationGoal, 0, ',', '.') }}
                                @if ($product->donationRaised > 0)
                                    · Raised: Rp {{ number_format($product->donationRaised, 0, ',', '.') }}
                                @endif
                            </div>
                        @endif
                    </div>
                {{-- Appointment: date/time picker --}}
                @elseif ($product->type === 'appointment')
                    <div class="mt-4 pt-4 border-t border-ink-100 space-y-3">
                        <div class="text-xs text-ink-500">
                            ⏱️ Duration: <span class="font-bold">{{ $product->durationFormatted }}</span>
                        </div>
                        <div>
                            <label class="label" for="appointment_date">Preferred Date *</label>
                            <input id="appointment_date" name="appointment_date" type="date" required
                                   min="{{ date('Y-m-d') }}"
                                   class="input @error('appointment_date') input-error @enderror">
                        </div>
                        <div>
                            <label class="label" for="appointment_time">Preferred Time *</label>
                            <input id="appointment_time" name="appointment_time" type="time" required
                                   class="input @error('appointment_time') input-error @enderror">
                        </div>
                    </div>
                {{-- Event: quantity picker --}}
                @elseif ($product->type === 'event' && $product->meta('capacity'))
                    <div class="mt-4 pt-4 border-t border-ink-100">
                        <label class="label" for="quantity">Tickets</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="10" value="1"
                               class="input">
                    </div>
                @endif

                {{-- Total --}}
                <div class="mt-4 pt-4 border-t border-ink-100 flex items-center justify-between">
                    <span class="text-sm text-ink-500">{{ $product->type === 'donation' ? 'Amount' : 'Total' }}</span>
                    <span class="text-2xl font-black text-brand-500" id="total_display">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Email form --}}
            <form method="POST" action="{{ route('checkout.process', [$creator->username, $product->id]) }}" class="card p-5 space-y-4" id="checkout-form">
                @csrf
                <input type="hidden" name="amount" id="hidden_amount" value="{{ $product->price }}">

                <div>
                    <label class="label" for="payer_email">Your Email *</label>
                    <input id="payer_email" name="payer_email" type="email" required
                           value="{{ old('payer_email', auth()->user()->email ?? '') }}"
                           class="input @error('payer_email') input-error @enderror" placeholder="you@email.com">
                    <div class="help">We'll send your purchase here.</div>
                    @error('payer_email')<div class="error">{{ $message }}</div>@enderror
                </div>

                {{-- Donation: message field --}}
                @if ($product->type === 'donation')
                    <div>
                        <label class="label" for="donor_message">{{ $product->meta('message_label', 'Send a message (optional)') }}</label>
                        <textarea id="donor_message" name="donor_message" rows="3" class="input"
                                  placeholder="Say something to {{ '@' . $creator->username }}..."></textarea>
                    </div>
                @endif

                {{-- Physical: shipping address --}}
                @if ($product->type === 'physical')
                    <div class="space-y-3 p-4 rounded-lg bg-ink-50 border border-ink-200">
                        <div class="text-xs font-bold text-ink-700 uppercase">📦 Alamat Pengiriman</div>

                        <div>
                            <label class="label" for="ship_name">Nama Penerima *</label>
                            <input id="ship_name" name="ship[name]" type="text" required
                                   value="{{ old('ship.name', auth()->user()->name ?? '') }}"
                                   class="input @error('ship.name') input-error @enderror"
                                   placeholder="Nama lengkap">
                            @error('ship.name')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="label" for="ship_phone">No. HP / WhatsApp *</label>
                            <input id="ship_phone" name="ship[phone]" type="tel" required
                                   value="{{ old('ship.phone') }}"
                                   class="input @error('ship.phone') input-error @enderror"
                                   placeholder="08xxxxxxxxxx">
                            @error('ship.phone')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label class="label" for="ship_address">Alamat Lengkap *</label>
                            <textarea id="ship_address" name="ship[address]" rows="2" required
                                      class="input @error('ship.address') input-error @enderror"
                                      placeholder="Jalan, nomor rumah, RT/RW, kelurahan">{{ old('ship.address') }}</textarea>
                            @error('ship.address')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="label" for="ship_city">Kota *</label>
                                <input id="ship_city" name="ship[city]" type="text" required
                                       value="{{ old('ship.city') }}"
                                       class="input @error('ship.city') input-error @enderror"
                                       placeholder="Jakarta">
                                @error('ship.city')<div class="error">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="label" for="ship_province">Provinsi *</label>
                                <input id="ship_province" name="ship[province]" type="text" required
                                       value="{{ old('ship.province') }}"
                                       class="input @error('ship.province') input-error @enderror"
                                       placeholder="DKI Jakarta">
                                @error('ship.province')<div class="error">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="label" for="ship_postal">Kode Pos *</label>
                                <input id="ship_postal" name="ship[postal_code]" type="text" required pattern="[0-9]{5}"
                                       value="{{ old('ship.postal_code') }}"
                                       class="input @error('ship.postal_code') input-error @enderror"
                                       placeholder="12345">
                                @error('ship.postal_code')<div class="error">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="label" for="ship_country">Negara</label>
                                <input id="ship_country" name="ship[country]" type="text"
                                       value="{{ old('ship.country', 'Indonesia') }}"
                                       class="input" readonly>
                            </div>
                        </div>

                        <div class="text-xs text-ink-500">
                            Ongkos kirim dihitung terpisah. Creator akan konfirmasi via WhatsApp.
                        </div>
                    </div>
                @endif

                <button type="submit" class="btn-primary btn-lg btn-block" id="submit-btn">
                    @if ($product->type === 'donation')
                        ☕ Support Now
                    @elseif ($product->type === 'appointment')
                        📅 Book Now — Rp {{ number_format($product->price, 0, ',', '.') }}
                    @elseif ($product->type === 'event')
                        🎟️ Get Ticket
                    @elseif ($product->type === 'course')
                        🎓 Enroll Now
                    @else
                        Buy Now — Rp {{ number_format($product->price, 0, ',', '.') }}
                    @endif
                </button>

                <div class="text-xs text-ink-500 text-center">
                    🔒 Secure payment powered by Duitku (RSA encrypted)
                </div>
            </form>

            <div class="mt-4 text-center text-xs text-ink-500">
                By purchasing you agree to share your email with the creator.
            </div>
        </div>
    </section>

    @if ($product->type === 'donation')
    <script>
        document.querySelectorAll('.preset-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.preset-btn').forEach(b => b.classList.replace('btn-primary', 'btn-secondary'));
                btn.classList.replace('btn-secondary', 'btn-primary');
                document.getElementById('hidden_amount').value = btn.dataset.amount;
                document.getElementById('total_display').textContent = 'Rp ' + Number(btn.dataset.amount).toLocaleString('id-ID');
                document.getElementById('submit-btn').textContent = '☕ Support Rp ' + Number(btn.dataset.amount).toLocaleString('id-ID');
                const custom = document.getElementById('custom_amount');
                if (custom) custom.value = '';
            });
        });
        const customInput = document.getElementById('custom_amount');
        if (customInput) {
            customInput.addEventListener('input', () => {
                const v = parseInt(customInput.value || 0);
                if (v >= 1000) {
                    document.querySelectorAll('.preset-btn').forEach(b => b.classList.replace('btn-primary', 'btn-secondary'));
                    document.getElementById('hidden_amount').value = v;
                    document.getElementById('total_display').textContent = 'Rp ' + v.toLocaleString('id-ID');
                    document.getElementById('submit-btn').textContent = '☕ Support Rp ' + v.toLocaleString('id-ID');
                }
            });
        }
    </script>
    @endif
</body>
</html>