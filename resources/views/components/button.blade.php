@props(['variant' => 'primary'])

@php
    $classes = [
        'primary' => 'gh-btn-primary',
        'secondary' => 'gh-btn-secondary',
        'accent' => 'gh-btn-accent',
        'outline' => 'gh-btn-outline',
        'danger' => 'gh-btn-danger',
    ][$variant] ?? 'gh-btn-primary';
@endphp

<button {{ $attributes->merge(['class' => 'gh-btn '.$classes]) }}>
    {{ $slot }}
</button>
