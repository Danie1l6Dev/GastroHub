@props(['variant' => 'primary'])

@php
    $classes = [
        'primary' => 'gh-btn-primary',
        'secondary' => 'gh-btn-secondary',
        'danger' => 'gh-btn-danger',
    ][$variant] ?? 'gh-btn-primary';
@endphp

<button {{ $attributes->merge(['class' => 'gh-btn '.$classes]) }}>
    {{ $slot }}
</button>
