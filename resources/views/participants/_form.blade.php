<div class="field">
    <label for="name">Jméno</label>
    <input type="text" id="name" name="name" value="{{ old('name', $participant->name) }}" required>
    @error('name')<p class="field__error">{{ $message }}</p>@enderror
</div>

<div class="field">
    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" value="{{ old('email', $participant->email) }}" required>
    @error('email')<p class="field__error">{{ $message }}</p>@enderror
</div>
