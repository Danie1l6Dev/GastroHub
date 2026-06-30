@props(['label', 'name', 'value' => null, 'rows' => 4, 'help' => null])

<div>
    <label class="gh-label" for="{{ $name }}">{{ $label }}</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'gh-field min-h-28']) }}
    >{{ old($name, $value) }}</textarea>
    @if ($help)
        <p class="gh-help">{{ $help }}</p>
    @endif
    @error($name)
        <p class="gh-error">{{ $message }}</p>
    @enderror
</div>
