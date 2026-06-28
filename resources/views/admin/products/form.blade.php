@extends('layouts.admin', ['title' => $product->exists ? 'Editar producto' : 'Nuevo producto'])

@section('content')
    <h1 class="text-3xl font-semibold">{{ $product->exists ? 'Editar producto' : 'Nuevo producto' }}</h1>

    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="mt-6 max-w-2xl space-y-5 rounded-md border border-zinc-200 bg-white p-5">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div>
            <label class="text-sm font-medium" for="category_id">Categoria</label>
            <select id="category_id" name="category_id" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
                <option value="">Selecciona una categoria</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium" for="name">Nombre</label>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
            @error('name') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium" for="description">Descripcion</label>
            <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2">{{ old('description', $product->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium" for="price">Precio</label>
                <input id="price" name="price" type="number" min="0" value="{{ old('price', $product->price ?? 0) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
                @error('price') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium" for="position">Posicion</label>
                <input id="position" name="position" type="number" min="0" value="{{ old('position', $product->position ?? 0) }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2" required>
                @error('position') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available ?? true)) class="rounded border-zinc-300">
            Disponible
        </label>

        <div class="flex gap-2">
            <button class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Guardar</button>
            <a href="{{ route('admin.products.index') }}" class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Cancelar</a>
        </div>
    </form>
@endsection
