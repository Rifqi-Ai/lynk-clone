{{-- Appointment fields --}}
<div class="card p-6 space-y-4">
    <h2 class="font-black text-lg">Booking Settings</h2>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="label" for="duration_minutes">Duration *</label>
            <select id="duration_minutes" name="duration_minutes" class="input">
                @foreach ([15, 30, 45, 60, 90, 120, 180] as $m)
                    <option value="{{ $m }}" {{ old('duration_minutes', $product->durationMinutes) == $m ? 'selected' : '' }}>
                        {{ $m }} minutes
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="buffer_minutes">Buffer Time</label>
            <select id="buffer_minutes" name="buffer_minutes" class="input">
                @foreach ([0, 15, 30, 60] as $m)
                    <option value="{{ $m }}" {{ old('buffer_minutes', $product->meta('buffer_minutes', 15)) == $m ? 'selected' : '' }}>
                        {{ $m }} minutes
                    </option>
                @endforeach
            </select>
            <div class="help">Gap between bookings.</div>
        </div>
    </div>
    <div>
        <label class="label" for="location_type">Meeting Type</label>
        <select id="location_type" name="location_type" class="input">
            <option value="online" {{ old('location_type', $product->meta('location_type', 'online')) == 'online' ? 'selected' : '' }}>📹 Online (Zoom/Meet)</option>
            <option value="in_person" {{ old('location_type', $product->meta('location_type')) == 'in_person' ? 'selected' : '' }}>📍 In-Person</option>
            <option value="phone" {{ old('location_type', $product->meta('location_type')) == 'phone' ? 'selected' : '' }}>📞 Phone Call</option>
        </select>
    </div>
    <div>
        <label class="label" for="location_details">Location / Link Details</label>
        <input id="location_details" name="location_details" type="text" maxlength="500"
               value="{{ old('location_details', $product->meta('location_details')) }}"
               class="input" placeholder="e.g. Zoom link will be sent via email">
        <div class="help">Shown to buyer after successful booking.</div>
    </div>
</div>