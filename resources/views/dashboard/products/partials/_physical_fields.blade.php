{{-- Physical product fields --}}
<div class="card p-6 space-y-4">
    <h2 class="font-black text-lg">Physical Product</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="label" for="stock_quantity">Stock Quantity</label>
            <input id="stock_quantity" name="stock_quantity" type="number" min="0"
                   value="{{ old('stock_quantity', $product->stockQuantity) }}" class="input"
                   placeholder="Leave empty for unlimited">
        </div>
        <div>
            <label class="label" for="weight_grams">Weight (grams)</label>
            <input id="weight_grams" name="weight_grams" type="number" min="0"
                   value="{{ old('weight_grams', $product->meta('weight_grams')) }}" class="input"
                   placeholder="e.g. 500">
        </div>
    </div>
    <div>
        <label class="label" for="dimensions">Dimensions</label>
        <input id="dimensions" name="dimensions" type="text" maxlength="50"
               value="{{ old('dimensions', $product->meta('dimensions')) }}" class="input"
               placeholder="e.g. 20x15x10 cm">
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="requires_shipping" id="requires_shipping" value="1"
               {{ old('requires_shipping', $product->meta('requires_shipping', true)) ? 'checked' : '' }}
               class="rounded text-brand-500 focus:ring-brand-500">
        <label for="requires_shipping" class="text-sm">Requires shipping</label>
    </div>
    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
        ⚠️ Physical product checkout will ask for shipping address in Phase 2.5.
    </div>
</div>