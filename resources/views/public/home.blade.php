@extends('layouts.app', ['title' => ($restaurant->name ?? 'GastroHub').' | Restaurante'])

@section('content')
    @php
        $primaryColor = $restaurant?->safePrimaryColor() ?? '#059669';
        $secondaryColor = $restaurant?->safeSecondaryColor() ?? '#111827';
    @endphp

    <section class="relative overflow-hidden text-white" style="--brand-primary: {{ $primaryColor }}; --brand-secondary: {{ $secondaryColor }}; background-color: var(--brand-secondary);">
        <img src="{{ $restaurant?->coverImageUrl() ?? asset('images/restaurant-hero.png') }}" alt="Portada de {{ $restaurant?->name ?? 'GastroHub' }}" class="absolute inset-0 h-full w-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-r from-zinc-950/90 via-zinc-950/65 to-zinc-950/20"></div>
        <div class="relative mx-auto grid min-h-[calc(100vh-4.25rem)] max-w-6xl content-center gap-8 px-4 py-12 lg:min-h-[38rem]">
            <div class="max-w-3xl gh-reveal">
                @if ($restaurant?->logoUrl())
                    <img src="{{ $restaurant->logoUrl() }}" alt="Logo de {{ $restaurant->name }}" class="mb-6 h-16 w-16 rounded-2xl object-cover ring-1 ring-white/25">
                @endif
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-100 ring-1 ring-white/15">{{ $restaurant->tagline ?? 'Restaurante digital' }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/15" style="background-color: {{ $restaurant?->is_open ? $primaryColor : '#71717a' }};">
                        {{ $restaurant?->is_open ? 'Abierto ahora' : 'Cerrado' }}
                    </span>
                </div>
                <h1 class="mt-5 text-5xl font-semibold tracking-tight sm:text-6xl">{{ $restaurant->name ?? 'GastroHub' }}</h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-zinc-100 sm:text-lg">
                    {{ $restaurant->description ?? 'Cocina casual con menu digital y atencion sencilla desde la mesa.' }}
                </p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('menu') }}" class="gh-btn min-h-12 rounded-2xl px-6 text-white" style="background-color: var(--brand-primary);">Ver menu</a>
                    <a href="#contacto" class="gh-btn rounded-2xl border border-white/20 bg-white/10 px-6 text-white backdrop-blur hover:bg-white/15">Ubicacion y contacto</a>
                </div>
            </div>
        </div>
    </section>

    <section id="contacto" class="mx-auto max-w-6xl px-4 py-10 sm:py-14">
        <div class="grid gap-4 md:grid-cols-3">
            <article class="gh-panel gh-card-hover">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Horario</p>
                <p class="mt-3 text-lg font-semibold leading-7">{{ $restaurant?->opening_hours ?? 'Por confirmar' }}</p>
            </article>
            <article class="gh-panel gh-card-hover">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Direccion</p>
                <p class="mt-3 text-lg font-semibold leading-7">{{ $restaurant?->address ?? 'Por confirmar' }}</p>
            </article>
            <article class="gh-panel gh-card-hover">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">Telefono</p>
                <p class="mt-3 text-lg font-semibold leading-7">{{ $restaurant?->phone ?? 'Por confirmar' }}</p>
            </article>
        </div>

        <div class="mt-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="gh-page-kicker" style="color: {{ $primaryColor }};">Favoritos</p>
                <h2 class="gh-page-title">Platos destacados</h2>
                <p class="gh-page-copy">Una seleccion visual para abrir el apetito antes de escanear la mesa.</p>
            </div>
            <a href="{{ route('menu') }}" class="gh-btn gh-btn-secondary">Ver menu completo</a>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredProducts as $product)
                <article class="group overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-950/[0.04] transition duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-950/[0.08]">
                    <div class="relative overflow-hidden">
                        <img loading="lazy" src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-105">
                        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-zinc-800 shadow-sm">{{ $product->category->name }}</span>
                        @unless ($product->is_available)
                            <span class="absolute inset-x-3 bottom-3 rounded-xl bg-zinc-950/85 px-3 py-2 text-center text-sm font-semibold text-white">Agotado</span>
                        @endunless
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-semibold">{{ $product->name }}</h3>
                        <p class="mt-2 min-h-12 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>
                        <p class="mt-4 text-xl gh-price">{{ $product->formattedPrice() }}</p>
                    </div>
                </article>
            @empty
                <x-empty-state title="Aun no hay productos destacados" description="Marca productos como destacados desde el panel para llenar esta seccion." class="sm:col-span-2 lg:col-span-3" />
            @endforelse
        </div>

        <section class="mt-12 overflow-hidden rounded-3xl bg-zinc-950 p-6 text-white shadow-xl shadow-zinc-950/10 sm:p-8">
            <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-300">Listo para ordenar</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight">Escanea la mesa, elige platos y divide la cuenta sin enredos.</h2>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row md:flex-col">
                    <a href="{{ route('menu') }}" class="gh-btn rounded-2xl bg-white text-zinc-950 hover:bg-zinc-100">Explorar menu</a>
                    @if ($restaurant?->instagram_url)
                        <a href="{{ $restaurant->instagram_url }}" target="_blank" rel="noopener noreferrer" class="gh-btn rounded-2xl border border-white/15 text-white hover:bg-white/10">Instagram</a>
                    @endif
                </div>
            </div>
        </section>
    </section>
@endsection
