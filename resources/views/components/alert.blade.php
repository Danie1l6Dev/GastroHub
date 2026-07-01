@props(['type' => 'success'])

@php
    $classes = [
        'success' => 'border-brand-ink/15 bg-brand-white text-brand-ink',
        'error' => 'border-brand-red/25 bg-brand-red-soft text-brand-red',
        'warning' => 'border-brand-orange/40 bg-brand-orange-soft text-brand-ink',
    ][$type] ?? 'border-brand-brown/15 bg-white text-brand-ink';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border px-4 py-3 text-sm '.$classes]) }}>
    {{ $slot }}
</div>
