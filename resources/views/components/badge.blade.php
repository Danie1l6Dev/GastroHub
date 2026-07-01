@props(['tone' => 'neutral'])

@php
    $classes = [
        'neutral' => 'bg-brand-white text-brand-ink ring-brand-brown/20',
        'success' => 'bg-brand-white text-brand-ink ring-brand-ink/20',
        'warning' => 'bg-brand-orange-soft text-brand-ink ring-brand-orange/40',
        'danger' => 'bg-brand-red-soft text-brand-red ring-brand-red/25',
        'info' => 'bg-brand-brown-soft text-brand-brown-dark ring-brand-brown/25',
        'warm' => 'bg-brand-brown text-white ring-brand-brown',
        'primary' => 'bg-brand-red text-white ring-brand-red',
        'accent' => 'bg-brand-orange text-brand-ink ring-brand-orange',
        'dark' => 'bg-brand-ink text-white ring-brand-ink',
    ][$tone] ?? 'bg-brand-white text-brand-ink ring-brand-brown/20';
@endphp

<span {{ $attributes->merge(['class' => 'gh-badge '.$classes]) }}>
    {{ $slot }}
</span>
