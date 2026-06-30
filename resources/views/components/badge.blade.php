@props(['tone' => 'neutral'])

@php
    $classes = [
        'neutral' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
        'success' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'warning' => 'bg-amber-100 text-amber-900 ring-amber-200',
        'danger' => 'bg-red-100 text-red-800 ring-red-200',
        'info' => 'bg-sky-100 text-sky-800 ring-sky-200',
        'dark' => 'bg-zinc-950 text-white ring-zinc-950',
    ][$tone] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-200';
@endphp

<span {{ $attributes->merge(['class' => 'gh-badge '.$classes]) }}>
    {{ $slot }}
</span>
