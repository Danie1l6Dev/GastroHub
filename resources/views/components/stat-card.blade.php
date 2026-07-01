@props(['label', 'value', 'hint' => null, 'tone' => 'neutral'])

@php
    $toneClasses = [
        'neutral' => 'bg-brand-white text-brand-ink ring-1 ring-brand-brown/15',
        'success' => 'bg-brand-brown-soft text-brand-brown-dark',
        'warning' => 'bg-brand-orange-soft text-brand-ink',
        'info' => 'bg-brand-red-soft text-brand-red',
        'dark' => 'bg-brand-ink text-white',
    ][$tone] ?? 'bg-brand-white text-brand-ink ring-1 ring-brand-brown/15';
@endphp

<div {{ $attributes->merge(['class' => 'gh-panel gh-card-hover relative overflow-hidden']) }}>
    <span class="absolute inset-x-0 top-0 h-1 bg-brand-red"></span>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">{{ $label }}</p>
            <p class="mt-3 text-3xl font-semibold tabular-nums text-brand-ink">{{ $value }}</p>
        </div>
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-semibold {{ $toneClasses }}">
            {{ mb_substr($label, 0, 1) }}
        </span>
    </div>
    @if ($hint)
        <p class="mt-3 text-sm leading-6 text-zinc-500">{{ $hint }}</p>
    @endif
</div>
