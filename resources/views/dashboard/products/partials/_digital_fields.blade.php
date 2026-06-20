{{-- Digital Product fields --}}
<div class="card p-6 space-y-4">
    <h2 class="font-black text-lg">Digital File</h2>
    <div>
        <label class="label" for="file">{{ $product->file_path ? 'Replace File' : 'Upload File *' }}</label>
        @if ($product->file_path)
            <div class="text-sm text-ink-700 mb-2">Current: <span class="font-mono">{{ $product->file_name }}</span> ({{ $product->file_size_formatted }})</div>
        @endif
        <input id="file" name="file" type="file"
               class="input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-bold">
        <div class="help">PDF, ZIP, image, video, etc. Max 50MB.</div>
    </div>
    <div>
        <label class="label" for="download_limit_per_purchase">Download Limit</label>
        <input id="download_limit_per_purchase" name="download_limit_per_purchase" type="number" min="1" max="100"
               value="{{ old('download_limit_per_purchase', $product->download_limit_per_purchase ?? 5) }}" class="input">
        <div class="help">How many times buyer can download.</div>
    </div>
</div>