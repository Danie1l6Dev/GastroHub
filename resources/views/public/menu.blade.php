@extends('layouts.app', ['title' => 'Menu | '.($restaurant->name ?? 'GastroHub')])

@section('content')
    @php
        $primaryColor = $restaurant?->safePrimaryColor() ?? '#059669';
    @endphp

    <section class="mx-auto max-w-6xl px-4 py-10">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: {{ $primaryColor }};">Menu digital</p>
            <h1 class="mt-2 text-4xl font-semibold">Elige lo que quieres pedir</h1>
            <p class="mt-3 text-zinc-600">Productos disponibles y agotados se muestran para que la mesa tenga informacion clara.</p>
        </div>

        @if ($categories->isNotEmpty())
            <nav class="mt-6 flex gap-2 overflow-x-auto pb-2">
                @foreach ($categories as $category)
                    <a href="#{{ $category->slug }}" class="shrink-0 rounded-md border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-zinc-50">{{ $category->name }}</a>
                @endforeach
            </nav>
        @endif

        <div class="mt-8 space-y-10">
            @forelse ($categories as $category)
                <section id="{{ $category->slug }}" class="scroll-mt-6">
                    <div class="border-b border-zinc-200 pb-3">
                        <h2 class="text-2xl font-semibold">{{ $category->name }}</h2>
                        @if ($category->description)
                            <p class="mt-1 text-sm text-zinc-600">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @forelse ($category->visibleProducts as $product)
                            <article class="overflow-hidden rounded-md border border-zinc-200 bg-white">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-video w-full object-cover">
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-semibold">{{ $product->name }}</h3>
                                            <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $product->description }}</p>
                                        </div>
                                        <p class="shrink-0 text-base font-semibold">{{ $product->formattedPrice() }}</p>
                                    </div>
                                    @unless ($product->is_available)
                                        <span class="mt-4 inline-flex rounded-md bg-zinc-200 px-3 py-1 text-xs font-semibold text-zinc-700">Agotado</span>
                                    @endunless
                                </div>
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-zinc-300 bg-white p-5 text-sm text-zinc-600">No hay productos en esta categoria.</p>
                        @endforelse
                    </div>
                </section>
            @empty
                <p class="rounded-md border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">Aun no hay categorias activas.</p>
            @endforelse
        </div>
    </section>
@endsection
