@extends('layouts.admin', ['title' => 'Panel del restaurante'])

@section('content')
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Panel interno</p>
            <h1 class="mt-2 text-3xl font-semibold">Resumen del restaurante</h1>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-md border border-zinc-200 bg-white p-5"><p class="text-sm text-zinc-500">Categorias</p><p class="mt-2 text-3xl font-semibold">{{ $categoryCount }}</p></div>
        <div class="rounded-md border border-zinc-200 bg-white p-5"><p class="text-sm text-zinc-500">Productos</p><p class="mt-2 text-3xl font-semibold">{{ $productCount }}</p></div>
        <div class="rounded-md border border-zinc-200 bg-white p-5"><p class="text-sm text-zinc-500">Mesas</p><p class="mt-2 text-3xl font-semibold">{{ $tableCount }}</p></div>
        <div class="rounded-md border border-zinc-200 bg-white p-5"><p class="text-sm text-zinc-500">Sesiones abiertas</p><p class="mt-2 text-3xl font-semibold">{{ $openSessionCount }}</p></div>
    </div>

    <section class="mt-8 rounded-md border border-zinc-200 bg-white">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="text-lg font-semibold">Productos recientes</h2>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse ($latestProducts as $product)
                <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-medium">{{ $product->name }}</p>
                        <p class="text-sm text-zinc-500">{{ $product->category->name }}</p>
                    </div>
                    <p class="font-semibold">{{ $product->formattedPrice() }}</p>
                </div>
            @empty
                <p class="px-5 py-4 text-sm text-zinc-600">No hay productos cargados.</p>
            @endforelse
        </div>
    </section>
@endsection
