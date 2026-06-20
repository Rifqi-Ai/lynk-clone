{{-- Event/Webinar fields --}}
<div class="card p-6 space-y-4">
    <h2 class="font-black text-lg">Event Details</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="label" for="event_date">Start Date/Time *</label>
            <input id="event_date" name="event_date" type="datetime-local" required
                   value="{{ old('event_date', $product->eventDate ? \Carbon\Carbon::parse($product->eventDate)->format('Y-m-d\TH:i') : '') }}"
                   class="input">
        </div>
        <div>
            <label class="label" for="end_date">End Date/Time</label>
            <input id="end_date" name="end_date" type="datetime-local"
                   value="{{ old('end_date', $product->meta('end_date') ? \Carbon\Carbon::parse($product->meta('end_date'))->format('Y-m-d\TH:i') : '') }}"
                   class="input">
        </div>
    </div>
    <div>
        <label class="label" for="location">Location</label>
        <input id="location" name="location" type="text" maxlength="200"
               value="{{ old('location', $product->meta('location')) }}" class="input"
               placeholder="e.g. Zoom Webinar, Jakarta Convention Center">
    </div>
    <div>
        <label class="label" for="stream_url">Stream URL</label>
        <input id="stream_url" name="stream_url" type="url"
               value="{{ old('stream_url', $product->meta('stream_url')) }}" class="input"
               placeholder="https://zoom.us/j/12345">
        <div class="help">Sent to ticket holders before event.</div>
    </div>
    <div>
        <label class="label" for="capacity">Capacity (optional)</label>
        <input id="capacity" name="capacity" type="number" min="1"
               value="{{ old('capacity', $product->meta('capacity')) }}" class="input" placeholder="Leave empty for unlimited">
        <div class="help">Max number of tickets sold.</div>
    </div>
</div>