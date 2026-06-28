@props(['variant' => 'primary'])

@php
    $classes = [
        'primary' => 'bg-zinc-950 text-white hover:bg-zinc-800',
        'secondary' => 'border border-zinc-200 text-zinc-800 hover:bg-zinc-50',
        'danger' => 'border border-red-200 text-red-700 hover:bg-red-50',
    ][$variant] ?? 'bg-zinc-950 text-white hover:bg-zinc-800';
@endphp

<button {{ $attributes->merge(['class' => 'rounded-md px-4 py-2 text-sm font-semibold transition '.$classes]) }}>
    {{ $slot }}
</button>
