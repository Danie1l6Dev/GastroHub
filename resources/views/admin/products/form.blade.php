@extends('layouts.admin', ['title' => $product->exists ? 'Editar producto' : 'Nuevo producto'])

@section('content')
    <h1 class="text-3xl font-semibold">{{ $product->exists ? 'Editar producto' : 'Nuevo producto' }}</h1>

    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="mt-6 max-w-2xl space-y-5 rounded-md border border-zinc-200 bg-white p-5">
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

        <x-form-input label="Nombre" name="name" :value="$product->name" required />

        <div>
            <label class="text-sm font-medium" for="description">Descripcion</label>
            <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2">{{ old('description', $product->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form-input label="Precio en pesos" name="price" type="number" min="100" step="100" :value="$product->price ?? 0" required />
            <x-form-input label="Orden" name="sort_order" type="number" min="0" :value="$product->sort_order ?? $product->position ?? 0" required />
        </div>

        <div>
            <label class="text-sm font-medium" for="image">Imagen</label>
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
            @error('image') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            @if ($product->exists)
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="mt-3 h-24 w-24 rounded-md object-cover">
            @endif
        </div>

        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available ?? true)) class="rounded border-zinc-300">
            Disponible
        </label>

        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false)) class="rounded border-zinc-300">
            Destacado
        </label>

        <div class="flex gap-2">
            <x-button>Guardar</x-button>
            <a href="{{ route('admin.products.index') }}" class="rounded-md border border-zinc-200 px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Cancelar</a>
        </div>
    </form>
@endsection
