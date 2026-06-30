@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'gh-empty']) }}>
    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-lg font-semibold text-emerald-700">
        {{ mb_substr($title, 0, 1) }}
    </div>
    <p class="text-base font-semibold text-zinc-950">{{ $title }}</p>
    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-600">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
