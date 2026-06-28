@extends('layouts.admin', ['title' => 'Productos'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold">Productos</h1>
            <p class="mt-1 text-sm text-zinc-600">Administra los platos visibles en el menu.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Nuevo producto</a>
    </div>

    <div class="mt-6 overflow-hidden rounded-md border border-zinc-200 bg-white">
        @forelse ($products as $product)
            <div class="grid gap-3 border-b border-zinc-100 px-5 py-4 last:border-b-0 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <p class="font-semibold">{{ $product->name }} <span class="text-sm font-normal text-zinc-500">({{ $product->category->name }})</span></p>
                    <p class="mt-1 text-sm text-zinc-600">{{ $product->description ?: 'Sin descripcion' }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ $product->formattedPrice() }} · Posicion {{ $product->position }} · {{ $product->is_available ? 'Disponible' : 'No disponible' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-md border border-zinc-200 px-3 py-2 text-sm font-semibold hover:bg-zinc-50">Editar</a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="p-5 text-sm text-zinc-600">No hay productos.</p>
        @endforelse
    </div>
@endsection
