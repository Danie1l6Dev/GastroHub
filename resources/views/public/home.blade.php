@extends('layouts.app', ['title' => ($restaurant->name ?? 'GastroHub').' | Restaurante'])

@section('content')
    <section class="bg-zinc-950 text-white">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:grid-cols-[0.9fr_1.1fr] md:items-center lg:py-14">
            <div class="space-y-5">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-300">{{ $restaurant->tagline ?? 'Menu digital para la mesa' }}</p>
                <h1 class="text-4xl font-semibold leading-tight sm:text-5xl">{{ $restaurant->name ?? 'GastroHub' }}</h1>
                <p class="max-w-xl text-base leading-7 text-zinc-300">
                    {{ $restaurant->description ?? 'Explora el menu, entra desde el QR de tu mesa y pide a tu ritmo. Cada persona conserva su cuenta individual y la mesa ve el total general.' }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('menu') }}" class="rounded-md bg-emerald-400 px-5 py-3 text-sm font-semibold text-zinc-950 hover:bg-emerald-300">Ver menu</a>
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md border border-white/20 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">Panel interno</a>
                </div>
            </div>
            <img src="{{ asset('images/restaurant-hero.png') }}" alt="Mesa de restaurante con menu digital" class="aspect-[4/3] w-full rounded-md object-cover shadow-2xl">
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-10">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-md border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Mesas activas</p>
                <p class="mt-2 text-3xl font-semibold">{{ $activeTables }}</p>
            </div>
            <div class="rounded-md border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Pedidos</p>
                <p class="mt-2 text-3xl font-semibold">Por persona</p>
            </div>
            <div class="rounded-md border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Pagos</p>
                <p class="mt-2 text-3xl font-semibold">Simulados</p>
            </div>
        </div>

        <div class="mt-10 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold">Platos destacados</h2>
                <p class="mt-1 text-sm text-zinc-600">Una muestra rapida del menu disponible.</p>
            </div>
            <a href="{{ route('menu') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">Ver todo</a>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredProducts as $product)
                <article class="rounded-md border border-zinc-200 bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $product->category->name }}</p>
                    <h3 class="mt-2 text-lg font-semibold">{{ $product->name }}</h3>
                    <p class="mt-2 min-h-12 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>
                    <p class="mt-4 text-lg font-semibold">{{ $product->formattedPrice() }}</p>
                </article>
            @empty
                <p class="rounded-md border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">Aun no hay productos cargados.</p>
            @endforelse
        </div>
    </section>
@endsection
