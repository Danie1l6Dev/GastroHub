@extends('layouts.app', ['title' => ($restaurant->name ?? 'GastroHub').' | Restaurante'])

@section('content')
    @php
        $primaryColor = $restaurant?->safePrimaryColor() ?? '#059669';
        $secondaryColor = $restaurant?->safeSecondaryColor() ?? '#111827';
    @endphp

    <section class="text-white" style="--brand-primary: {{ $primaryColor }}; --brand-secondary: {{ $secondaryColor }}; background-color: var(--brand-secondary);">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:grid-cols-[0.9fr_1.1fr] md:items-center lg:py-14">
            <div class="space-y-5">
                @if ($restaurant?->logoUrl())
                    <img src="{{ $restaurant->logoUrl() }}" alt="Logo de {{ $restaurant->name }}" class="h-16 w-16 rounded-md object-cover ring-1 ring-white/20">
                @endif
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--brand-primary);">{{ $restaurant->tagline ?? 'Restaurante digital' }}</p>
                    <span class="rounded-md px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $restaurant?->is_open ? $primaryColor : '#71717a' }};">
                        {{ $restaurant?->is_open ? 'Abierto ahora' : 'Cerrado' }}
                    </span>
                </div>
                <h1 class="text-4xl font-semibold leading-tight sm:text-5xl">{{ $restaurant->name ?? 'GastroHub' }}</h1>
                <p class="max-w-xl text-base leading-7 text-zinc-300">
                    {{ $restaurant->description ?? 'Cocina casual con menu digital y atencion sencilla desde la mesa.' }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('menu') }}" class="rounded-md px-5 py-3 text-sm font-semibold text-white" style="background-color: var(--brand-primary);">Ver menu</a>
                    @if ($restaurant?->instagram_url)
                        <a href="{{ $restaurant->instagram_url }}" target="_blank" rel="noopener noreferrer" class="rounded-md border border-white/20 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">Instagram</a>
                    @endif
                </div>
            </div>
            <img src="{{ $restaurant?->coverImageUrl() ?? asset('images/restaurant-hero.png') }}" alt="Portada de {{ $restaurant?->name ?? 'GastroHub' }}" class="aspect-[4/3] w-full rounded-md object-cover shadow-2xl">
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-10">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-md border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Horario</p>
                <p class="mt-2 text-lg font-semibold">{{ $restaurant?->opening_hours ?? 'Por confirmar' }}</p>
            </div>
            <div class="rounded-md border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Direccion</p>
                <p class="mt-2 text-lg font-semibold">{{ $restaurant?->address ?? 'Por confirmar' }}</p>
            </div>
            <div class="rounded-md border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Telefono</p>
                <p class="mt-2 text-lg font-semibold">{{ $restaurant?->phone ?? 'Por confirmar' }}</p>
            </div>
        </div>

        <div class="mt-10 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold">Platos destacados</h2>
                <p class="mt-1 text-sm text-zinc-600">Favoritos reales del menu para abrir la conversacion en mesa.</p>
            </div>
            <a href="{{ route('menu') }}" class="text-sm font-semibold" style="color: {{ $primaryColor }};">Ver menu</a>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredProducts as $product)
                <article class="overflow-hidden rounded-md border border-zinc-200 bg-white">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-video w-full object-cover">
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide" style="color: {{ $primaryColor }};">{{ $product->category->name }}</p>
                        <h3 class="mt-2 text-lg font-semibold">{{ $product->name }}</h3>
                        <p class="mt-2 min-h-12 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <p class="text-lg font-semibold">{{ $product->formattedPrice() }}</p>
                            @unless ($product->is_available)
                                <span class="rounded-md bg-zinc-200 px-3 py-1 text-xs font-semibold text-zinc-700">Agotado</span>
                            @endunless
                        </div>
                    </div>
                </article>
            @empty
                <p class="rounded-md border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">Aun no hay productos destacados.</p>
            @endforelse
        </div>
    </section>
@endsection
