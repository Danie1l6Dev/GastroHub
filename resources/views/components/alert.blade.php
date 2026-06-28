@props(['type' => 'success'])

@php
    $classes = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'error' => 'border-red-200 bg-red-50 text-red-900',
    ][$type] ?? 'border-zinc-200 bg-white text-zinc-900';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border px-4 py-3 text-sm '.$classes]) }}>
    {{ $slot }}
</div>
