@extends('layouts.app', ['title' => 'Menu | '.($restaurant->name ?? 'GastroHub')])

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-10">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Menu digital</p>
            <h1 class="mt-2 text-4xl font-semibold">Elige lo que quieres pedir</h1>
            <p class="mt-3 text-zinc-600">Estos productos estan disponibles para el flujo de mesa que se construira en la siguiente fase.</p>
        </div>

        <div class="mt-8 space-y-8">
            @forelse ($categories as $category)
                <section>
                    <div class="border-b border-zinc-200 pb-3">
                        <h2 class="text-2xl font-semibold">{{ $category->name }}</h2>
                        @if ($category->description)
                            <p class="mt-1 text-sm text-zinc-600">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @forelse ($category->availableProducts as $product)
                            <article class="rounded-md border border-zinc-200 bg-white p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ $product->name }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>
                                    </div>
                                    <p class="shrink-0 text-base font-semibold">{{ $product->formattedPrice() }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-zinc-300 bg-white p-5 text-sm text-zinc-600">No hay productos disponibles en esta categoria.</p>
                        @endforelse
                    </div>
                </section>
            @empty
                <p class="rounded-md border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">Aun no hay categorias activas.</p>
            @endforelse
        </div>
    </section>
@endsection
