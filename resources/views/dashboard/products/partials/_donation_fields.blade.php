{{-- Donation fields --}}
<div class="card p-6 space-y-4">
    <h2 class="font-black text-lg">Donation Settings</h2>
    <div>
        <label class="label">Preset Amounts (Rp)</label>
        @php
            $presets = old('preset_amounts', $product->donationPresets);
        @endphp
        <div class="grid grid-cols-3 gap-2">
            @foreach ([10000, 25000, 50000, 100000, 250000, 500000] as $default)
                <label class="flex items-center gap-2 p-2 border border-ink-200 rounded-lg cursor-pointer hover:bg-ink-50">
                    <input type="checkbox" name="preset_amounts[]" value="{{ $default }}"
                           {{ in_array($default, $presets) ? 'checked' : '' }}
                           class="rounded text-brand-500 focus:ring-brand-500">
                    <span class="text-sm">Rp {{ number_format($default / 1000, 0) }}K</span>
                </label>
            @endforeach
        </div>
        <div class="help">Buyers can click these quick-amount buttons.</div>
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="allow_custom" id="allow_custom" value="1"
               {{ old('allow_custom', $product->meta('allow_custom', true)) ? 'checked' : '' }}
               class="rounded text-brand-500 focus:ring-brand-500">
        <label for="allow_custom" class="text-sm">Allow custom amount</label>
    </div>
    <div>
        <label class="label" for="goal_amount">Goal Amount (optional)</label>
        <input id="goal_amount" name="goal_amount" type="number" min="0" step="1000"
               value="{{ old('goal_amount', $product->donationGoal) }}" class="input" placeholder="e.g. 5000000">
        <div class="help">Show a progress bar to motivate supporters.</div>
    </div>
    <div>
        <label class="label" for="message_label">Message Field Label</label>
        <input id="message_label" name="message_label" type="text" maxlength="100"
               value="{{ old('message_label', $product->meta('message_label', 'Send a message (optional)')) }}"
               class="input" placeholder="Send a message (optional)">
    </div>
</div>