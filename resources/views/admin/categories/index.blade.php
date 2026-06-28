@extends('layouts.admin', ['title' => 'Categorias'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold">Categorias</h1>
            <p class="mt-1 text-sm text-zinc-600">Organiza el menu digital.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Nueva categoria</a>
    </div>

    <div class="mt-6 overflow-hidden rounded-md border border-zinc-200 bg-white">
        @forelse ($categories as $category)
            <div class="grid gap-3 border-b border-zinc-100 px-5 py-4 last:border-b-0 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <p class="font-semibold">{{ $category->name }}</p>
                    <p class="mt-1 text-sm text-zinc-600">{{ $category->description ?: 'Sin descripcion' }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ $category->products_count }} productos · Posicion {{ $category->position }} · {{ $category->is_active ? 'Activa' : 'Inactiva' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Editar</a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="p-5 text-sm text-zinc-600">No hay categorias.</p>
        @endforelse
    </div>
@endsection
