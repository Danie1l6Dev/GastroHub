@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'rounded-md border border-zinc-200 bg-white p-5']) }}>
    <p class="text-sm text-zinc-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-semibold">{{ $value }}</p>
</div>
