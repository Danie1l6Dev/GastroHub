@props(['label', 'value', 'hint' => null, 'tone' => 'neutral'])

@php
    $toneClasses = [
        'neutral' => 'bg-zinc-100 text-zinc-700',
        'success' => 'bg-emerald-100 text-emerald-800',
        'warning' => 'bg-amber-100 text-amber-900',
        'info' => 'bg-sky-100 text-sky-800',
        'dark' => 'bg-zinc-950 text-white',
    ][$tone] ?? 'bg-zinc-100 text-zinc-700';
@endphp

<div {{ $attributes->merge(['class' => 'gh-panel gh-card-hover']) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">{{ $label }}</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-zinc-950">{{ $value }}</p>
        </div>
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-semibold {{ $toneClasses }}">
            {{ mb_substr($label, 0, 1) }}
        </span>
    </div>
    @if ($hint)
        <p class="mt-3 text-sm leading-6 text-zinc-500">{{ $hint }}</p>
    @endif
</div>
