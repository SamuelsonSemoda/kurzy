<div class="field">
    <label for="name">Název kurzu</label>
    <input type="text" id="name" name="name" value="{{ old('name', $course->name) }}" required>
    @error('name')<p class="field__error">{{ $message }}</p>@enderror
</div>

<div class="field">
    <label for="code">Kód kurzu</label>
    <input type="text" id="code" name="code" value="{{ old('code', $course->code) }}" required>
    <p class="hint">Musí být jedinečný.</p>
    @error('code')<p class="field__error">{{ $message }}</p>@enderror
</div>

<div class="form-row">
    <div class="field">
        <label for="capacity">Kapacita</label>
        <input type="number" id="capacity" name="capacity" min="1" step="1" value="{{ old('capacity', $course->capacity) }}" required>
        @error('capacity')<p class="field__error">{{ $message }}</p>@enderror
    </div>

    <div class="field">
        <label for="starts_at">Začátek kurzu</label>
        <input type="datetime-local" id="starts_at" name="starts_at"
               value="{{ old('starts_at', $course->starts_at?->format('Y-m-d\TH:i')) }}" required>
        @error('starts_at')<p class="field__error">{{ $message }}</p>@enderror
    </div>
</div>
