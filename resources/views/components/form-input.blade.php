@props(['label', 'name', 'type' => 'text', 'value' => null])

<div>
    <label class="gh-label" for="{{ $name }}">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'gh-field']) }}
    >
    @error($name)
        <p class="gh-error">{{ $message }}</p>
    @enderror
</div>
