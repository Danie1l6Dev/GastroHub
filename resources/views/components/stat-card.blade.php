@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'rounded-md border border-zinc-200 bg-white p-5 shadow-sm shadow-zinc-950/[0.03]']) }}>
    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-zinc-500">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold tabular-nums text-zinc-950">{{ $value }}</p>
</div>
