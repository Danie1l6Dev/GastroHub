@extends('layouts.admin', ['title' => 'Productos'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Catalogo</p>
            <h1 class="gh-page-title">Productos</h1>
            <p class="gh-page-copy">Administra platos, precios, fotos, disponibilidad y destacados del menu.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="gh-btn gh-btn-primary">Nuevo producto</a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="gh-panel mt-6 grid gap-3 lg:grid-cols-[1.4fr_1fr_1fr_auto_auto]">
        <label class="block">
            <span class="gh-label">Buscar</span>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre o descripcion" class="gh-field">
        </label>
        <label class="block">
            <span class="gh-label">Categoria</span>
            <select name="category_id" class="gh-field">
                <option value="">Todas las categorias</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="gh-label">Disponibilidad</span>
            <select name="availability" class="gh-field">
                <option value="">Todas</option>
                <option value="available" @selected(($filters['availability'] ?? '') === 'available')>Disponible</option>
                <option value="sold_out" @selected(($filters['availability'] ?? '') === 'sold_out')>Agotado</option>
            </select>
        </label>
        <button class="gh-btn gh-btn-primary self-end">Filtrar</button>
        <a href="{{ route('admin.products.index') }}" class="gh-btn gh-btn-secondary self-end">Limpiar</a>
    </form>

    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($products as $product)
            <article class="group overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-950/[0.04] transition duration-200 hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-950/[0.08]">
                <div class="relative overflow-hidden">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-105">
                    <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                        <x-badge :tone="$product->is_available ? 'success' : 'neutral'">{{ $product->is_available ? 'Disponible' : 'Agotado' }}</x-badge>
                        @if ($product->is_featured)
                            <x-badge tone="warning">Destacado</x-badge>
                        @endif
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">{{ $product->category->name }}</p>
                            <h2 class="mt-1 truncate text-lg font-semibold">{{ $product->name }}</h2>
                        </div>
                        <p class="shrink-0 text-lg gh-price">{{ $product->formattedPrice() }}</p>
                    </div>
                    <p class="mt-3 line-clamp-2 min-h-12 text-sm leading-6 text-zinc-600">{{ $product->description ?: 'Sin descripcion' }}</p>
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs text-zinc-500">Orden {{ $product->sort_order }}</span>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="gh-btn gh-btn-secondary min-h-10 px-3">Editar</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <button class="gh-btn gh-btn-danger min-h-10 px-3" data-confirm="Eliminar este producto?">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <x-empty-state title="No hay productos" description="Crea un plato o ajusta los filtros para ver resultados." class="md:col-span-2 xl:col-span-3">
                <a href="{{ route('admin.products.create') }}" class="gh-btn gh-btn-primary">Nuevo producto</a>
            </x-empty-state>
        @endforelse
    </div>
@endsection
