@extends('layouts.admin', ['title' => 'Categorias'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="gh-page-kicker">Menu</p>
            <h1 class="gh-page-title">Categorias</h1>
            <p class="gh-page-copy">Organiza el menu por secciones claras para que el cliente encuentre rapido lo que quiere.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="gh-btn gh-btn-primary">Nueva categoria</a>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse ($categories as $category)
            <article class="gh-panel gh-card-hover">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-semibold">{{ $category->name }}</h2>
                        <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $category->description ?: 'Sin descripcion' }}</p>
                    </div>
                    <x-badge :tone="$category->is_active ? 'success' : 'neutral'">{{ $category->is_active ? 'Activa' : 'Inactiva' }}</x-badge>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 font-semibold text-zinc-700">{{ $category->products_count }} productos</span>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 font-semibold text-zinc-700">Orden {{ $category->sort_order }}</span>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="gh-btn gh-btn-secondary min-h-10 px-3">Editar</a>
                    @if ($category->products_count === 0)
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                            @csrf
                            @method('DELETE')
                            <button class="gh-btn gh-btn-danger min-h-10 px-3" data-confirm="Eliminar esta categoria?">Eliminar</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <x-empty-state title="No hay categorias" description="Crea secciones para organizar el menu publico." class="lg:col-span-2">
                <a href="{{ route('admin.categories.create') }}" class="gh-btn gh-btn-primary">Nueva categoria</a>
            </x-empty-state>
        @endforelse
    </div>
@endsection
