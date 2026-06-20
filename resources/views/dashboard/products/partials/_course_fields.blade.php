{{-- Course fields --}}
<div class="card p-6 space-y-4">
    <h2 class="font-black text-lg">Course Details</h2>
    <div>
        <label class="label" for="total_duration_minutes">Total Duration (minutes)</label>
        <input id="total_duration_minutes" name="total_duration_minutes" type="number" min="0"
               value="{{ old('total_duration_minutes', $product->courseDuration) }}" class="input"
               placeholder="e.g. 480 for 8-hour course">
    </div>
    <div>
        <label class="label" for="level">Level</label>
        <select id="level" name="level" class="input">
            <option value="beginner" {{ old('level', $product->meta('level', 'beginner')) == 'beginner' ? 'selected' : '' }}>Beginner</option>
            <option value="intermediate" {{ old('level', $product->meta('level')) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
            <option value="advanced" {{ old('level', $product->meta('level')) == 'advanced' ? 'selected' : '' }}>Advanced</option>
        </select>
    </div>
    <div>
        <label class="label">Modules / Lessons</label>
        @php $modules = old('course_modules', $product->meta('modules', [])); @endphp
        <div class="space-y-2" id="modules-list">
            @for ($i = 0; $i < max(3, count($modules)); $i++)
                <input type="text" name="course_modules[]" value="{{ $modules[$i] ?? '' }}"
                       class="input" placeholder="Module {{ $i + 1 }} title">
            @endfor
        </div>
        <button type="button" id="add-module" class="btn-ghost btn-sm mt-2">+ Add Module</button>
        <div class="help">Add up to 20 modules. Just titles for now (videos uploaded separately).</div>
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="certificate" id="certificate" value="1"
               {{ old('certificate', $product->meta('certificate')) ? 'checked' : '' }}
               class="rounded text-brand-500 focus:ring-brand-500">
        <label for="certificate" class="text-sm">Issue certificate on completion</label>
    </div>
</div>

<script>
document.getElementById('add-module').addEventListener('click', () => {
    const list = document.getElementById('modules-list');
    if (list.children.length >= 20) return alert('Maximum 20 modules');
    const idx = list.children.length + 1;
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'course_modules[]';
    input.className = 'input';
    input.placeholder = `Module ${idx} title`;
    list.appendChild(input);
});
</script>